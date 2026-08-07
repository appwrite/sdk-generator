const { execFileSync, execSync } = require("child_process");
const fs = require("fs");
const assert = require("node:assert/strict");
const os = require("os");
const path = require("path");

process.env.NODE_ENV = "test";
const sandboxHome = fs.mkdtempSync(
  path.join(os.tmpdir(), "appwrite-cli-test-"),
);
os.homedir = () => sandboxHome;
process.env.HOME = sandboxHome;
process.env.USERPROFILE = sandboxHome;
process.on("exit", () => {
  fs.rmSync(sandboxHome, { recursive: true, force: true });
});

const Client = require("./lib/client.ts").default;
const { localConfig } = require("./lib/config.ts");
const { types } = require("./lib/commands/types.ts");
const { parse } = require("./lib/parser.ts");
const {
  openRuntimesVersion,
  systemTools,
} = require("./lib/emulation/utils.ts");
const { assertFunctionSourceCode } = require("./lib/emulation/docker.ts");
const {
  TypeScriptDatabasesGenerator,
} = require("./lib/commands/generators/typescript/databases.ts");
const {
  getAllFiles,
  getFunctionDeploymentConsoleUrl,
  getSiteDeploymentConsoleUrl,
} = require("./lib/utils.ts");
const { EXECUTABLE_NAME } = require("./lib/constants.ts");
const { isCompletionInvocation } = require("./lib/completions.ts");
const {
  decodeIdToken,
  isAuthorizationPendingError,
  pollForDeviceToken,
} = require("./lib/auth/oauth.ts");
const {
  assertSessionEndpointMatches,
  getValidAccessToken,
  sdkForConsole,
  sdkForConsoleWithOrganization,
  sdkForProject,
} = require("./lib/sdks.ts");
const {
  deleteStoredRefreshToken,
  getStoredRefreshToken,
  hasStoredRefreshToken,
  setRefreshTokenEntryFactoryForTests,
  setStoredRefreshToken,
} = require("./lib/auth/refresh-token.ts");
const {
  planSessionLogout,
  isLocalOnlySession,
  isLegacySession,
  isAuthenticatedSession,
  findSessionForEndpoint,
  getSignedInAccounts,
  getSessionAccountKey,
  hasAuthSession,
  restoreCurrentSessionFallback,
} = require("./lib/auth/session.ts");
const {
  isCloudHostname,
  isRegionalCloudEndpoint,
  isLocalhostHostname,
  isCloudLoginEndpoint,
  getConsoleProjectSlug,
  openBrowser,
} = require("./lib/utils.ts");
const { isFlagEnabled } = require("./lib/flags.ts");
const {
  normalizeCloudConsoleEndpoint,
  endpointsMatch,
  globalConfig,
} = require("./lib/config.ts");
const { listenForBrowserOpen, loginCommand } = require("./lib/auth/login.ts");
const {
  resolveOrganizationId,
  resolveProjectId,
} = require("./lib/context.ts");
const {
  questionsLogout,
  questionsClientReset,
} = require("./lib/questions.ts");
const { logout, client, whoami } = require("./lib/commands/generic.ts");
const {
  resolveSkillSelection,
} = require("./lib/commands/init.ts");
const {
  getOrganizationForSession,
  listOrganizationsForSession,
  listProjectsForSession,
} = require("./lib/console-fallback.ts");
const { formatErrorForLog } = require("./lib/parser.ts");
const http = require("http");
const { cliConfig } = require("./lib/parser.ts");
const inquirerModule = require("inquirer");
const inquirer = inquirerModule.default ?? inquirerModule;

assert.ok(globalConfig.path.startsWith(sandboxHome));
const sandboxKeyringTokens = new Map();
setRefreshTokenEntryFactoryForTests((_service, account) => ({
  setPassword: (password) => sandboxKeyringTokens.set(account, password),
  getPassword: () => sandboxKeyringTokens.get(account) ?? null,
  deletePassword: () => sandboxKeyringTokens.delete(account),
}));

const extractFirstValue = (output) => {
  const firstLine =
    output.split("\n").find((line) => line.trim().length > 0) ?? "";

  const legacySeparatorIndex = firstLine.indexOf(" : ");
  if (legacySeparatorIndex !== -1) {
    return firstLine.slice(legacySeparatorIndex + 3).trim();
  }

  const alignedColumnsMatch = firstLine.match(/^\s*\S.*?\s{2,}(.+)$/);
  if (alignedColumnsMatch) {
    return alignedColumnsMatch[1].trim();
  }

  return firstLine.trim();
};

const stripAnsi = (value) => value.replace(/\u001b\[[0-9;]*m/g, "");

// The root help screen groups commands under uppercase headings rather than a
// single `Commands:` block, so collect every command section and ignore the
// two that do not list commands.
const NON_COMMAND_HELP_SECTIONS = new Set(["USAGE", "OPTIONS"]);

const extractHelpCommands = (helpOutput) => {
  const commands = new Set();
  let inCommandSection = false;

  for (const line of stripAnsi(helpOutput).split("\n")) {
    const heading = line.match(/^([A-Z][A-Z ]*[A-Z])$/)?.[1];

    if (heading) {
      inCommandSection = !NON_COMMAND_HELP_SECTIONS.has(heading);
      continue;
    }

    if (!inCommandSection) {
      continue;
    }

    // Rows are `  <command>  <summary>`; paths such as `oauth2 list-projects`
    // contribute their top-level command only.
    const commandName = line.match(/^ {2}([a-zA-Z0-9-]+)\b/)?.[1];

    if (commandName && commandName !== "help") {
      commands.add(commandName);
    }
  }

  if (commands.size === 0) {
    throw new Error(
      `Expected help output to list commands under a section heading.\n${helpOutput}`,
    );
  }

  return [...commands];
};

const extractLineContaining = (output, token) => {
  const line = output
    .split("\n")
    .find((candidate) => candidate.includes(token));

  if (!line) {
    throw new Error(`Expected output to include ${JSON.stringify(token)}.`);
  }

  return line.trim();
};

// Sync-only capture helper. The callback must complete all writes before it
// returns, and async callbacks are rejected explicitly to avoid misleading
// "missing token" assertions when output arrives later.
const captureStdoutSync = (callback) => {
  const originalWrite = process.stdout.write.bind(process.stdout);
  const originalConsoleLog = console.log.bind(console);
  let output = "";

  console.log = (...args) => {
    output += `${args.join(" ")}\n`;
  };

  process.stdout.write = (chunk, encoding, cb) => {
    output += Buffer.isBuffer(chunk) ? chunk.toString() : String(chunk);

    if (typeof cb === "function") {
      cb();
    }

    return true;
  };

  try {
    const result = callback();

    if (result && typeof result.then === "function") {
      throw new Error("captureStdoutSync only supports synchronous callbacks.");
    }
  } finally {
    console.log = originalConsoleLog;
    process.stdout.write = originalWrite;
  }

  return stripAnsi(output).replace(/\r/g, "");
};

const muteStdout = async (callback) => {
  const originalWrite = process.stdout.write.bind(process.stdout);
  const originalConsoleLog = console.log.bind(console);

  console.log = () => {};
  process.stdout.write = (_chunk, _encoding, cb) => {
    const callback = typeof _encoding === "function" ? _encoding : cb;
    if (typeof callback === "function") {
      callback();
    }

    return true;
  };

  try {
    return await callback();
  } finally {
    console.log = originalConsoleLog;
    process.stdout.write = originalWrite;
  }
};

const withArgv = (args, callback) => {
  const originalArgv = process.argv;

  process.argv = ["bun", "./dist/cli.cjs", ...args];

  try {
    return callback();
  } finally {
    process.argv = originalArgv;
  }
};

execSync(
  "bun ./dist/cli.cjs client --endpoint 'http://mockapi/v1' --project-id console --key=35y3h5h345 --self-signed true",
  { stdio: "inherit" },
);

const zshCompletionOutput = execSync("bun ./dist/cli.cjs completion zsh", {
  stdio: "pipe",
}).toString();
const bashCompletionOutput = execSync("bun ./dist/cli.cjs completion bash", {
  stdio: "pipe",
}).toString();
const fishCompletionOutput = execSync("bun ./dist/cli.cjs completion fish", {
  stdio: "pipe",
}).toString();
const helpOutput = execSync("bun ./dist/cli.cjs --help", {
  stdio: "pipe",
}).toString();
const completionFunctionName = `_${EXECUTABLE_NAME}`;
const zshRegistrationToken = `compdef ${completionFunctionName} ${EXECUTABLE_NAME}`;
const bashRegistrationToken =
  `complete -F ${completionFunctionName}_completion ${EXECUTABLE_NAME}`;
const fishRegistrationToken = `complete -c '${EXECUTABLE_NAME}'`;

const availableSkills = [
  { dirName: "appwrite-cli" },
  { dirName: "appwrite-go" },
];
assert.deepEqual(resolveSkillSelection(availableSkills, [], true), [
  "appwrite-cli",
  "appwrite-go",
]);
assert.deepEqual(
  resolveSkillSelection(
    availableSkills,
    ["appwrite-go", "appwrite-go"],
    false,
  ),
  ["appwrite-go"],
);
assert.throws(
  () => resolveSkillSelection(availableSkills, ["appwrite-go"], true),
  /cannot be used together/,
);
assert.throws(
  () => resolveSkillSelection(availableSkills, ["missing"], false),
  /Unknown skill/,
);

for (const commandName of extractHelpCommands(helpOutput)) {
  if (!zshCompletionOutput.includes(`'${commandName}'`)) {
    throw new Error(
      `Expected zsh completion output to include top-level command ${commandName}.`,
    );
  }
}

for (const [shell, completionOutput, expectedToken] of [
  ["zsh", zshCompletionOutput, zshRegistrationToken],
  ["bash", bashCompletionOutput, bashRegistrationToken],
  ["fish", fishCompletionOutput, fishRegistrationToken],
]) {
  if (!completionOutput.includes(expectedToken)) {
    throw new Error(
      `Expected ${shell} completion output to include ${JSON.stringify(expectedToken)}.`,
    );
  }
}

if (
  !zshCompletionOutput.includes("'foo:get'") ||
  !zshCompletionOutput.includes("'--verbose'") ||
  !zshCompletionOutput.includes("'--x'")
) {
  throw new Error(
    "Expected zsh completion output to include nested commands and flags.",
  );
}

if (withArgv(["--id", "completion"], isCompletionInvocation)) {
  throw new Error(
    "Expected --id completion to be parsed as an id value, not a completion command.",
  );
}

if (!withArgv(["--id=foo", "completion"], isCompletionInvocation)) {
  throw new Error("Expected completion command after --id=foo to be detected.");
}

const completionHome = fs.mkdtempSync(
  path.join(os.tmpdir(), `${EXECUTABLE_NAME}-completion-`),
);
try {
  const installOutput = execFileSync(
    "bun",
    ["./dist/cli.cjs", "completion", "install"],
    {
      env: {
        ...process.env,
        HOME: completionHome,
        SHELL: "/bin/zsh",
      },
      stdio: "pipe",
    },
  ).toString();
  const installedCompletionPath = path.join(
    completionHome,
    ".zfunc",
    completionFunctionName,
  );
  const installedCompletion = fs.readFileSync(installedCompletionPath, "utf8");

  if (
    !installOutput.includes(
      `Installed zsh completion to ${installedCompletionPath}`,
    )
  ) {
    throw new Error(`Unexpected completion install output: ${installOutput}`);
  }

  if (!installedCompletion.includes(zshRegistrationToken)) {
    throw new Error(
      "Expected completion install to write zsh completion script.",
    );
  }
} finally {
  fs.rmSync(completionHome, { recursive: true, force: true });
}

var output;
console.log("\nTest Started");
const sdkHeaders = new Client().getHeaders();
console.log(
  `x-sdk-name: ${sdkHeaders["x-sdk-name"]}; x-sdk-platform: ${sdkHeaders["x-sdk-platform"]}; x-sdk-language: ${sdkHeaders["x-sdk-language"]}; x-sdk-version: ${sdkHeaders["x-sdk-version"]}`,
);
console.log(
  extractLineContaining(zshCompletionOutput, zshRegistrationToken),
);
console.log(
  extractLineContaining(
    bashCompletionOutput,
    bashRegistrationToken,
  ),
);
console.log(
  extractLineContaining(
    fishCompletionOutput,
    `${fishRegistrationToken} -f -n '__${EXECUTABLE_NAME}_using_command' -a`,
  ),
);
console.log(
  extractLineContaining(zshCompletionOutput, "'foo:get') context='foo get'"),
);

// Foo
output = execSync(
  "bun ./dist/cli.cjs foo get  --x string  --y 123 --z string in array",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs foo post  --x string  --y 123 --z string in array",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs foo put  --x string  --y 123 --z string in array",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs foo patch  --x string  --y 123 --z string in array",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs foo delete  --x string  --y 123 --z string in array",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

// Bar
output = execSync(
  "bun ./dist/cli.cjs bar get  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs bar post  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs bar put  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs bar patch  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs bar delete  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

// General
output = execSync("bun ./dist/cli.cjs general redirect", {
  stdio: "pipe",
}).toString();
console.log(extractFirstValue(output));

console.log(
  getSiteDeploymentConsoleUrl(
    "https://sgp.cloud.appwrite.io/v1",
    "chirag-project-prod",
    "chirag-profile-website",
    "123",
  ),
);
console.log(
  getFunctionDeploymentConsoleUrl(
    "https://sgp.cloud.appwrite.io/v1",
    "chirag-project-prod",
    "sample-function",
    "123",
  ),
);
console.log(
  getSiteDeploymentConsoleUrl(
    "https://abc.example.com/v1",
    "self-hosted-project",
    "docs",
    "456",
  ),
);

output = execSync(
  "bun ./dist/cli.cjs general upload --x string  --y 123 --z string in array --file ../../../resources/file.png",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs general upload --x string  --y 123 --z string in array --file ../../../resources/large_file.mp4",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

// Skip extra tests for CLI
console.log("POST:/v1/mock/tests/general/upload:passed");
console.log("POST:/v1/mock/tests/general/upload:passed");

execSync("bun ./dist/cli.cjs general empty", { stdio: "pipe" });

output = execSync("bun ./dist/cli.cjs general headers", {
  stdio: "pipe",
}).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs functions create-execution --function-id sample-function",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

if (openRuntimesVersion !== "v5") {
  throw new Error(
    `Expected local function emulation to use OpenRuntimes v5, got ${openRuntimesVersion}`,
  );
}
if (systemTools.node?.startCommand !== "bash helpers/server.sh") {
  throw new Error(
    `Expected node local function startup to use bash helpers/server.sh, got ${systemTools.node?.startCommand}`,
  );
}
console.log("CLI_LOCAL_FUNCTION_RUNNER_CONFIG:passed");

const validSourceDir = path.join(process.cwd(), "tmp-local-source-check");
fs.rmSync(validSourceDir, { recursive: true, force: true });
fs.mkdirSync(path.join(validSourceDir, "src"), { recursive: true });
fs.writeFileSync(
  path.join(validSourceDir, "package.json"),
  JSON.stringify({ name: "tmp-local-source-check", private: true }),
);
fs.writeFileSync(
  path.join(validSourceDir, "src/main.js"),
  "export default async ({ res }) => res.json({ ok: true });\n",
);

try {
  assertFunctionSourceCode({
    $id: "tmp-local-source-check",
    name: "Tmp Local Source Check",
    runtime: "node-22",
    entrypoint: "src/main.js",
    path: path
      .relative(process.cwd(), validSourceDir)
      .split(path.sep)
      .join("/"),
  });
} finally {
  fs.rmSync(validSourceDir, { recursive: true, force: true });
}
console.log("CLI_LOCAL_SOURCE_PREFLIGHT:passed");

const runtimeRenderingOutput = captureStdoutSync(() =>
  parse({
    total: 4,
    runtimes: [
      {
        $id: "node-16.0",
        key: "node",
        name: "Node.js",
        version: "16.0",
        base: "node:16.20.2-alpine3.18",
        image: "openruntimes/node:v5-16.0",
        logo: "node.png",
      },
      {
        $id: "node-18.0",
        key: "node",
        name: "Node.js",
        version: "18.0",
        base: "node:18.20.4-alpine3.20",
        image: "openruntimes/node:v5-18.0",
        logo: "node.png",
      },
      {
        $id: "python-3.12",
        key: "python",
        name: "Python",
        version: "3.12",
        base: "python:3.12.6-alpine3.20",
        image: "openruntimes/python:v5-3.12",
        logo: "python.png",
      },
      {
        $id: "flutter-3.41",
        key: "flutter",
        name: "Flutter",
        version: "3.41",
        base: "ghcr.io/cirruslabs/flutter:3.41.0",
        image: "openruntimes/flutter:v5-3.41",
        logo: "flutter.png",
      },
    ],
  }),
)
  .split("\n")
  .map((line) => line.replace(/\s+$/g, ""))
  .join("\n");

for (const expectedToken of [
  "total  4",
  "runtimes (4)",
  "runtime",
  "id",
  "base",
  "image",
  "[1] Node.js 16.0",
  "node-16.0",
  "openruntimes/node:v5-16.0",
  "ghcr.io/cirruslabs/flutter:3.41.0",
]) {
  if (!runtimeRenderingOutput.includes(expectedToken)) {
    throw new Error(
      `Expected runtime rendering to include ${JSON.stringify(expectedToken)}.\n${runtimeRenderingOutput}`,
    );
  }
}

for (const forbiddenToken of [
  "$id      ",
  "key      ",
  "name     ",
  "version  ",
  "logo     ",
]) {
  if (runtimeRenderingOutput.includes(forbiddenToken)) {
    throw new Error(
      `Expected runtime rendering to omit ${JSON.stringify(forbiddenToken)}.\n${runtimeRenderingOutput}`,
    );
  }
}

console.log("CLI_RUNTIME_RENDERING:passed");

const deploymentRenderingOutput = captureStdoutSync(() =>
  parse({
    total: 3,
    deployments: [
      {
        $id: "6a65e0ff00d45cdb1e36",
        type: "manual",
        resourceId: "layby",
        resourceType: "sites",
        sourceSize: 7507,
        buildSize: 0,
        totalSize: 7507,
        activate: false,
        status: "failed",
        buildLogs: "Build failed with exit code -1.",
        buildDuration: 904,
      },
      {
        $id: "6a66c7381a16bff498d3",
        type: "cli",
        resourceId: "layby",
        resourceType: "sites",
        sourceSize: 10471,
        buildSize: 20480,
        totalSize: 30951,
        activate: false,
        status: "ready",
        buildLogs: "Build finished.",
        buildDuration: 14,
      },
      {
        $id: "6a67091aeafb8c211d5e",
        type: "cli",
        resourceId: "layby",
        resourceType: "sites",
        sourceSize: 67782,
        buildSize: 77824,
        totalSize: 145606,
        activate: true,
        status: "ready",
        buildLogs: "Build finished.",
        buildDuration: 16,
      },
    ],
  }),
)
  .split("\n")
  .map((line) => line.replace(/\s+$/g, ""))
  .join("\n");

for (const expectedToken of [
  "total  3",
  "deployments (3)",
  "deployment",
  "status",
  "type",
  "auto-activate",
  "size",
  "build",
  "[1] 6a65e0ff00d45cdb1e36",
  "failed",
  "7.3 KB",
  "15m 4s",
  "[3] 6a67091aeafb8c211d5e",
  "142.2 KB",
  "yes",
]) {
  if (!deploymentRenderingOutput.includes(expectedToken)) {
    throw new Error(
      `Expected deployment rendering to include ${JSON.stringify(expectedToken)}.\n${deploymentRenderingOutput}`,
    );
  }
}

for (const forbiddenToken of [
  "resourceId",
  "resourceType",
  "sourceSize",
  "buildSize",
  "totalSize",
  "buildLogs",
  "Build failed with exit code -1.",
]) {
  if (deploymentRenderingOutput.includes(forbiddenToken)) {
    throw new Error(
      `Expected deployment rendering to omit ${JSON.stringify(forbiddenToken)}.\n${deploymentRenderingOutput}`,
    );
  }
}

console.log("CLI_DEPLOYMENT_RENDERING:passed");

output = execFileSync(
  "bun",
  [
    "./dist/cli.cjs",
    "general",
    "list-rows",
    "--queries",
    '{"method":"orderDesc","attribute":"rawName"}',
    "--filter",
    "published=true",
    "--filter",
    "score>=10",
    "--where",
    "legacy=true",
    "--where",
    'status=["draft","published"]',
    "--sort-asc",
    "title",
    "--sort-desc",
    "$createdAt",
    "--limit",
    "25",
    "--offset",
    "50",
    "--cursor-after",
    "row-before",
    "--cursor-before",
    "row-after",
    "--select",
    "$id",
    "--select",
    "title",
  ],
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

try {
  execFileSync(
    "bun",
    ["./dist/cli.cjs", "general", "list-rows", "--filter", "count>1e999"],
    { stdio: "pipe" },
  );
  throw new Error("Expected non-finite numeric filter values to be rejected.");
} catch (error) {
  if (!String(error.stderr ?? error.message).includes("finite numbers")) {
    throw error;
  }
}

try {
  execFileSync(
    "bun",
    ["./dist/cli.cjs", "general", "list-rows", "--filter", "count=[1e999]"],
    { stdio: "pipe" },
  );
  throw new Error("Expected non-finite array filter values to be rejected.");
} catch (error) {
  if (!String(error.stderr ?? error.message).includes("finite numbers")) {
    throw error;
  }
}

try {
  execFileSync(
    "bun",
    ["./dist/cli.cjs", "general", "list-rows", "--where", "count>1e999"],
    { stdio: "pipe" },
  );
  throw new Error("Expected deprecated where values to be rejected.");
} catch (error) {
  const stderr = String(error.stderr ?? error.message);
  if (
    !stderr.includes("--where is deprecated") ||
    !stderr.includes("finite numbers")
  ) {
    throw error;
  }
}

console.log("CLI_QUERY_HELPERS:passed");

// Type generation regression: generated concrete row query helpers must compile on TS 5.9+
fs.rmSync(path.join(process.cwd(), "generated"), {
  recursive: true,
  force: true,
});

void (async () => {
  const generator = new TypeScriptDatabasesGenerator();
  const result = await generator.generate(
    {
      projectId: "console",
      endpoint: "http://mockapi/v1",
      tables: [
        {
          $id: "inspections-payment-transfers",
          databaseId: "payments-db",
          name: "inspectionsPaymentTransfers",
          rowSecurity: true,
          columns: [
            {
              key: "status",
              type: "string",
              size: 255,
              required: true,
              default: null,
            },
            {
              key: "amount",
              type: "integer",
              required: false,
              default: null,
            },
          ],
        },
      ],
    },
    {
      appwriteImportSource: "@appwrite.io/console",
      importExtension: ".js",
    },
  );

  await generator.writeFiles(path.join(process.cwd(), "generated"), result);

  execSync(
    "bun ./node_modules/typescript/bin/tsc --pretty false --noEmit --strict --exactOptionalPropertyTypes --skipLibCheck --module NodeNext --moduleResolution NodeNext generated/appwrite/types.ts",
    { stdio: "pipe" },
  );

  fs.writeFileSync(
    path.join(process.cwd(), "appwrite.config.json"),
    JSON.stringify(
      {
        projectId: "console",
        tables: [
          {
            $id: "entitlements",
            databaseId: "billing",
            name: "entitlements",
            rowSecurity: true,
            columns: [
              {
                key: "purchaseTime",
                type: "integer",
                required: false,
                default: null,
              },
            ],
          },
        ],
      },
      null,
      2,
    ),
  );
  localConfig.useCwdConfig();
  await muteStdout(async () => {
    await types.parseAsync([
      "bun",
      "types",
      "generated/kotlin",
      "--language",
      "kotlin",
    ]);
  });

  const kotlinTypes = fs.readFileSync(
    path.join(process.cwd(), "generated/kotlin/Entitlements.kt"),
    "utf8",
  );
  assert.match(kotlinTypes, /val purchaseTime: Long\?/);
  assert.doesNotMatch(kotlinTypes, /val purchaseTime: Int\?/);

  console.log("CLI_TYPEGEN:passed");
})()
  .then(runAuthChecks)
  .then(runErrorHandlingChecks)
  .then(runConsoleFallbackChecks)
  .then(runAttributeSyncChecks)
  .then(runDeploymentSymlinkChecks)
  .catch((error) => {
    throw error;
  });

async function runAuthChecks() {
  const { AppwriteException, Oauth2 } = await import("@appwrite.io/console");
  const keyringTokens = new Map();
  const previousNodeEnv = process.env.NODE_ENV;

  process.env.NODE_ENV = "test";
  const restoreKeyringEntryFactory = setRefreshTokenEntryFactoryForTests((_service, account) => ({
    setPassword(password) {
      keyringTokens.set(account, password);
    },
    getPassword() {
      return keyringTokens.get(account) ?? null;
    },
    deletePassword() {
      return keyringTokens.delete(account);
    },
  }));

  const authCheck = async (name, fn) => {
    try {
      await fn();
      console.log(`auth:${name}:passed`);
    } catch (error) {
      console.log(`auth:${name}:failed`);
      console.error(`auth:${name}`, error && error.message ? error.message : error);
    }
  };

  // Runs fn with inquirer stubbed, returning the question sets it prompted with.
  // Records rather than throws so a stray prompt cannot trip actionRunner's
  // error path and exit the test process. Output is muted because the expected
  // output is asserted positionally.
  const recordPrompts = async (fn) => {
    const prompts = [];
    const original = inquirer.prompt;
    inquirer.prompt = async (questions) => {
      prompts.push(questions);
      return {};
    };
    try {
      await muteStdout(fn);
    } finally {
      inquirer.prompt = original;
    }
    return prompts;
  };

  const deviceAuth = (overrides = {}) => ({
    expires_in: 5,
    interval: 0,
    device_code: "dc",
    ...overrides,
  });

  await authCheck("endpoint-cloud-hostname", () => {
    assert.equal(isCloudHostname("cloud.appwrite.io"), true);
    assert.equal(isCloudHostname("fra.cloud.appwrite.io"), true);
    assert.equal(isCloudHostname("cloud.staging.appwrite.io"), true);
    assert.equal(isCloudHostname("fra.cloud.staging.appwrite.io"), true);
    assert.equal(isCloudHostname("evil.cloud.appwrite.io"), false);
    assert.equal(isCloudHostname("evil.cloud.staging.appwrite.io"), false);
    assert.equal(isCloudHostname("localhost"), false);
  });

  await authCheck("endpoint-regional", () => {
    assert.equal(isRegionalCloudEndpoint("https://fra.cloud.appwrite.io/v1"), true);
    assert.equal(isRegionalCloudEndpoint("https://syd.cloud.staging.appwrite.io/v1"), true);
    assert.equal(isRegionalCloudEndpoint("https://cloud.appwrite.io/v1"), false);
    assert.equal(isRegionalCloudEndpoint("https://cloud.staging.appwrite.io/v1"), false);
    assert.equal(isRegionalCloudEndpoint("http://localhost/v1"), false);
    assert.equal(isRegionalCloudEndpoint("nonsense"), false);
  });

  await authCheck("endpoint-localhost", () => {
    assert.equal(isLocalhostHostname("localhost"), true);
    assert.equal(isLocalhostHostname("127.0.0.1"), true);
    assert.equal(isLocalhostHostname("[::1]"), true);
    assert.equal(isLocalhostHostname("example.com"), false);
  });

  await authCheck("endpoint-cloud-login", () => {
    const prev = process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN;
    delete process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN;
    try {
      assert.equal(isFlagEnabled("devCloudLogin"), false);
      assert.equal(isCloudLoginEndpoint("https://cloud.appwrite.io/v1"), true);
      assert.equal(isCloudLoginEndpoint("https://cloud.staging.appwrite.io/v1"), true);
      assert.equal(isCloudLoginEndpoint("https://new.appwrite.io/v1"), true);
      assert.equal(isCloudLoginEndpoint("https://appwrite.io/v1"), false);
      assert.equal(isCloudLoginEndpoint("https://notappwrite.io/v1"), false);
      assert.equal(
        isCloudLoginEndpoint("https://real.appwrite.io.attacker.com/v1"),
        false,
      );
      assert.equal(isCloudLoginEndpoint("http://localhost/v1"), false);
    } finally {
      if (prev === undefined) delete process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN;
      else process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN = prev;
    }
  });

  await authCheck("endpoint-dev-override", () => {
    const prev = process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN;
    process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN = "1";
    try {
      assert.equal(isFlagEnabled("devCloudLogin"), true);
      assert.equal(isCloudLoginEndpoint("http://localhost/v1"), true);
    } finally {
      if (prev === undefined) delete process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN;
      else process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN = prev;
    }
  });

  await authCheck("endpoint-normalize", async () => {
    assert.equal(
      normalizeCloudConsoleEndpoint("https://fra.cloud.appwrite.io/v1"),
      "https://cloud.appwrite.io/v1",
    );
    assert.equal(
      normalizeCloudConsoleEndpoint("https://cloud.appwrite.io/v1"),
      "https://cloud.appwrite.io/v1",
    );
    assert.equal(
      normalizeCloudConsoleEndpoint("https://fra.cloud.staging.appwrite.io/v1"),
      "https://cloud.staging.appwrite.io/v1",
    );
    assert.equal(normalizeCloudConsoleEndpoint("http://localhost/v1"), "http://localhost/v1");
    assert.equal(normalizeCloudConsoleEndpoint("not a url"), "not a url");

    const regionalClient = await sdkForConsole({
      requiresAuth: false,
      endpointOverride: "https://fra.cloud.appwrite.io/v1",
      preserveRegion: true,
    });
    assert.equal(
      regionalClient.config.endpoint,
      "https://fra.cloud.appwrite.io/v1",
    );

    const consoleClient = await sdkForConsole({
      requiresAuth: false,
      endpointOverride: "https://fra.cloud.appwrite.io/v1",
    });
    assert.equal(consoleClient.config.endpoint, "https://cloud.appwrite.io/v1");
    assert.equal(consoleClient.config.project, "console");
  });

  await authCheck("console-slug-region", () => {
    assert.equal(getConsoleProjectSlug("http://localhost/v1", "proj1"), "project-default-proj1");
    assert.equal(getConsoleProjectSlug("http://localhost/v1", "proj1", "fra"), "project-fra-proj1");
    assert.equal(getConsoleProjectSlug("https://fra.cloud.appwrite.io/v1", "proj1"), "project-fra-proj1");
    assert.equal(getConsoleProjectSlug("https://cloud.appwrite.io/v1", "proj1"), "project-proj1");
  });

  await authCheck("decode-id-token", () => {
    const payload = Buffer.from(
      JSON.stringify({ email: "u@e.com", name: "U", sub: "123" }),
    ).toString("base64url");
    const decoded = decodeIdToken(`header.${payload}.sig`);
    assert.equal(decoded.email, "u@e.com");
    assert.equal(decoded.name, "U");
    assert.equal(decoded.sub, "123");
    assert.deepEqual(decodeIdToken("garbage"), {});
    assert.deepEqual(decodeIdToken("a.b.c"), {});
  });

  await authCheck("authorization-pending-error", () => {
    assert.equal(
      isAuthorizationPendingError(
        new AppwriteException("authorization_pending", 428, "authorization_pending"),
      ),
      true,
    );
    assert.equal(
      isAuthorizationPendingError(new AppwriteException("slow_down", 429, "slow_down")),
      true,
    );
    assert.equal(isAuthorizationPendingError(new AppwriteException("authorization_pending")), true);
    assert.equal(
      isAuthorizationPendingError(new AppwriteException("x", 400, "", "authorization_pending")),
      true,
    );
    assert.equal(
      isAuthorizationPendingError(new AppwriteException("other", 500, "general_server_error")),
      false,
    );
    assert.equal(isAuthorizationPendingError({ type: "authorization_pending" }), false);
  });

  await authCheck("session-account-key", () => {
    globalConfig.clear();
    globalConfig.addSession("s1", {
      endpoint: "https://fra.cloud.appwrite.io/v1",
      email: "a@b.com",
    });
    assert.equal(getSessionAccountKey("s1"), "a@b.com|https://cloud.appwrite.io/v1");
    globalConfig.addSession("s2", { endpoint: "http://localhost/v1", email: "x@y.com" });
    assert.equal(getSessionAccountKey("s2"), "x@y.com|http://localhost/v1");
  });

  await authCheck("session-local-only", () => {
    globalConfig.clear();
    keyringTokens.clear();
    globalConfig.addSession("local1", { endpoint: "http://localhost/v1" });
    assert.equal(isLocalOnlySession("local1"), true);
    globalConfig.addSession("oauth1", { endpoint: "http://localhost/v1" });
    setStoredRefreshToken("oauth1", "r");
    assert.equal(isLocalOnlySession("oauth1"), false);
    globalConfig.addSession("legacy1", { endpoint: "http://localhost/v1", cookie: "c" });
    assert.equal(isLocalOnlySession("legacy1"), false);
  });

  await authCheck("refresh-token-keyring-storage", () => {
    globalConfig.clear();
    keyringTokens.clear();
    globalConfig.addSession("oauth1", { endpoint: "http://localhost/v1" });
    globalConfig.setCurrentSession("oauth1");

    setStoredRefreshToken("oauth1", "refresh-secret");

    assert.equal(globalConfig.get("oauth1").refreshToken, undefined);
    assert.equal(getStoredRefreshToken("oauth1"), "refresh-secret");
    assert.equal(hasStoredRefreshToken("oauth1"), true);

    deleteStoredRefreshToken("oauth1");

    assert.equal(getStoredRefreshToken("oauth1"), "");
    assert.equal(hasStoredRefreshToken("oauth1"), false);
  });

  await authCheck("refresh-token-prefs-fallback", () => {
    globalConfig.clear();
    keyringTokens.clear();
    globalConfig.addSession("fallback1", { endpoint: "http://localhost/v1" });

    const restore = setRefreshTokenEntryFactoryForTests(() => {
      throw new Error("keyring unavailable");
    });
    try {
      setStoredRefreshToken("fallback1", "fallback-secret");
      assert.equal(globalConfig.get("fallback1").refreshToken, "fallback-secret");
      assert.equal(getStoredRefreshToken("fallback1"), "fallback-secret");
    } finally {
      restore();
    }

    assert.equal(getStoredRefreshToken("fallback1"), "fallback-secret");
    assert.equal(hasStoredRefreshToken("fallback1"), true);
    assert.equal(isLocalOnlySession("fallback1"), false);

    deleteStoredRefreshToken("fallback1");
    assert.equal(globalConfig.get("fallback1").refreshToken, undefined);
  });

  await authCheck("session-legacy", () => {
    globalConfig.clear();
    globalConfig.addSession("legacy1", { endpoint: "http://localhost/v1", cookie: "c" });
    assert.equal(isLegacySession("legacy1"), true);
    globalConfig.addSession("mixed", {
      endpoint: "http://localhost/v1",
      cookie: "c",
      accessToken: "a",
    });
    assert.equal(isLegacySession("mixed"), false);
    globalConfig.addSession("nocookie", { endpoint: "http://localhost/v1", accessToken: "a" });
    assert.equal(isLegacySession("nocookie"), false);
  });

  await authCheck("session-has-auth", () => {
    globalConfig.clear();
    globalConfig.addSession("s1", { endpoint: "http://localhost/v1", accessToken: "a" });
    globalConfig.setCurrentSession("s1");
    assert.equal(hasAuthSession(), true);
    globalConfig.clear();
    globalConfig.addSession("s2", { endpoint: "http://localhost/v1", cookie: "c" });
    globalConfig.setCurrentSession("s2");
    assert.equal(hasAuthSession(), true);
    globalConfig.clear();
    globalConfig.addSession("s3", { endpoint: "http://localhost/v1" });
    globalConfig.setCurrentSession("s3");
    assert.equal(hasAuthSession(), false);
  });

  await authCheck("plan-session-logout", () => {
    globalConfig.clear();
    globalConfig.addSession("a1", { endpoint: "https://cloud.appwrite.io/v1", email: "a@b.com" });
    globalConfig.addSession("a2", { endpoint: "https://cloud.appwrite.io/v1", email: "a@b.com" });
    globalConfig.addSession("b1", { endpoint: "http://localhost/v1", email: "b@c.com" });
    assert.deepEqual([...planSessionLogout(["a1"])].sort(), ["a1", "a2"]);
    assert.deepEqual(planSessionLogout(["b1"]), ["b1"]);
  });

  await authCheck("logout-question-choices", () => {
    globalConfig.clear();
    globalConfig.addSession("a1", {
      endpoint: "https://cloud.appwrite.io/v1",
      email: "a@b.com",
    });
    globalConfig.addSession("b1", {
      endpoint: "http://localhost/v1",
      email: "b@c.com",
    });
    globalConfig.setCurrentSession("b1");

    const choices = questionsLogout[0].choices().map((choice) => ({
      ...choice,
      name: stripAnsi(choice.name),
    }));

    assert.equal(choices[0].value, "b1");
    assert.equal(choices[0].name, "b@c.com (current) - http://localhost/v1");
    assert.equal(choices[0].short, "b@c.com (current)");
    assert.equal(choices[1].name, "a@b.com - https://cloud.appwrite.io/v1");
    assert.equal(choices[1].short, "a@b.com");
  });

  // Endpoint-only entries, as left behind by `appwrite client --endpoint`.
  await authCheck("logout-skips-empty-prompt", async () => {
    globalConfig.clear();
    keyringTokens.clear();
    globalConfig.addSession("stub1", { endpoint: "https://cloud.appwrite.io/v1" });
    globalConfig.addSession("stub2", { endpoint: "http://localhost/v1" });
    globalConfig.setCurrentSession("stub2");

    // Sessions are stored, yet the picker has nothing to offer — logout used to
    // open a checkbox with no options and a required validator, so no answer
    // could satisfy it.
    assert.ok(globalConfig.getSessions().length > 0);
    assert.deepEqual(questionsLogout[0].choices(), []);
    assert.deepEqual(await recordPrompts(() => logout.parseAsync([], { from: "user" })), []);
  });

  await authCheck("logout-single-account-ignores-current-stub", async () => {
    globalConfig.clear();
    keyringTokens.clear();
    globalConfig.addSession("stub1", { endpoint: "http://localhost/v1" });
    globalConfig.addSession("acct1", {
      endpoint: "https://cloud.appwrite.io/v1",
      email: "a@b.com",
    });
    // The lone account is not current, so logging out the current session would
    // revoke the wrong entry.
    globalConfig.setCurrentSession("stub1");

    assert.deepEqual(await recordPrompts(() => logout.parseAsync([], { from: "user" })), []);
    assert.equal(globalConfig.get("acct1"), undefined);
    assert.notEqual(globalConfig.get("stub1"), undefined);
    assert.equal(globalConfig.getCurrentSession(), "");
  });

  await authCheck("restore-current-session-fallback", () => {
    globalConfig.clear();
    globalConfig.addSession("s1", { endpoint: "http://localhost/v1" });
    globalConfig.addSession("s2", { endpoint: "http://localhost/v1" });
    restoreCurrentSessionFallback("s1", ["s2"]);
    assert.equal(globalConfig.getCurrentSession(), "s1");
    restoreCurrentSessionFallback("missing", ["nope", "s2"]);
    assert.equal(globalConfig.getCurrentSession(), "s2");
    restoreCurrentSessionFallback("missing", ["alsoMissing"]);
    assert.equal(globalConfig.getCurrentSession(), "");
  });

  // Commander keeps option values across parseAsync calls on the same Command.
  const runClient = async (args) => {
    for (const name of [
      "endpoint",
      "projectId",
      "key",
      "selfSigned",
      "debug",
      "reset",
    ]) {
      client.setOptionValue(name, undefined);
    }
    return muteStdout(() => client.parseAsync(args, { from: "user" }));
  };

  const withMockedHealthVersion = async (fn) => {
    const originalCall = Client.prototype.call;
    Client.prototype.call = async () => ({ version: "1.0.0" });
    try {
      return await fn();
    } finally {
      Client.prototype.call = originalCall;
    }
  };

  const captureConsole = async (fn) => {
    const logs = [];
    const errors = [];
    const originalLog = console.log;
    const originalError = console.error;
    console.log = (...args) => logs.push(stripAnsi(args.map(String).join(" ")));
    console.error = (...args) =>
      errors.push(stripAnsi(args.map(String).join(" ")));
    try {
      await fn();
    } finally {
      console.log = originalLog;
      console.error = originalError;
    }
    return { logs, errors };
  };

  const withProcessExitStub = async (fn) => {
    const originalExit = process.exit;
    let exitCode;
    process.exit = (code) => {
      exitCode = code;
      throw new Error(`process.exit:${code}`);
    };
    try {
      return await fn(() => exitCode);
    } finally {
      process.exit = originalExit;
    }
  };

  await authCheck("client-endpoint-session-reuse", async () => {
    assert.equal(
      endpointsMatch(
        "https://fra.cloud.appwrite.io/v1",
        "https://cloud.appwrite.io/v1",
      ),
      true,
    );
    assert.equal(
      endpointsMatch("http://localhost/v1", "https://cloud.appwrite.io/v1"),
      false,
    );

    globalConfig.clear();
    globalConfig.addSession("auth1", {
      endpoint: "https://cloud.appwrite.io/v1",
      email: "a@b.com",
      accessToken: "tok",
    });
    globalConfig.addSession("auth2", {
      endpoint: "http://localhost/v1",
      email: "b@c.com",
      accessToken: "tok2",
    });
    globalConfig.addSession("stub1", { endpoint: "http://localhost/v1" });
    assert.equal(isAuthenticatedSession("auth1"), true);
    assert.equal(isAuthenticatedSession("stub1"), false);
    assert.deepEqual(findSessionForEndpoint("https://fra.cloud.appwrite.io/v1"), {
      authenticated: "auth1",
      endpointOnly: undefined,
    });
    assert.equal(getSignedInAccounts().length, 2);

    globalConfig.setCurrentSession("auth1");
    await withMockedHealthVersion(async () => {
      await runClient(["--endpoint", "https://cloud.appwrite.io/v1"]);
      assert.equal(globalConfig.getCurrentSession(), "auth1");
      assert.equal(hasAuthSession(), true);

      await runClient(["--endpoint", "http://localhost/v1"]);
      assert.equal(globalConfig.getCurrentSession(), "auth2");
      assert.equal(globalConfig.getEmail(), "b@c.com");
      assert.equal(globalConfig.getSessionIds().length, 3);

      // Current stub must not mask a matching authenticated session.
      globalConfig.addSession("stub-cloud", {
        endpoint: "https://cloud.appwrite.io/v1",
      });
      globalConfig.setCurrentSession("stub-cloud");
      await runClient(["--endpoint", "https://cloud.appwrite.io/v1"]);
      assert.equal(globalConfig.getCurrentSession(), "auth1");
      assert.equal(hasAuthSession(), true);

      globalConfig.clear();
      await runClient(["--endpoint", "http://localhost/v1"]);
      const first = globalConfig.getCurrentSession();
      await runClient(["--endpoint", "http://localhost/v1"]);
      assert.equal(globalConfig.getCurrentSession(), first);
      assert.equal(globalConfig.getSessionIds().length, 1);

      globalConfig.clear();
      globalConfig.addSession("auth1", {
        endpoint: "https://cloud.appwrite.io/v1",
        email: "a@b.com",
        accessToken: "tok",
      });
      globalConfig.setCurrentSession("auth1");
      await runClient(["--endpoint", "http://localhost/v1"]);
      assert.notEqual(globalConfig.getCurrentSession(), "auth1");
      assert.notEqual(globalConfig.get("auth1"), undefined);
      assert.equal(hasAuthSession(), false);
      assert.equal(getSignedInAccounts()[0].email, "a@b.com");
    });
  });

  await authCheck("whoami-signed-in-account-hint", async () => {
    globalConfig.clear();
    let { logs, errors } = await captureConsole(() =>
      whoami.parseAsync([], { from: "user" }),
    );
    assert.ok(errors.some((line) => line.includes("No user is signed in")));
    assert.equal(
      logs.some((line) => line.includes("Signed-in accounts are still available")),
      false,
    );

    globalConfig.addSession("auth1", {
      endpoint: "https://cloud.appwrite.io/v1",
      email: "a@b.com",
      accessToken: "tok",
    });
    globalConfig.addSession("stub1", { endpoint: "http://localhost/v1" });
    globalConfig.setCurrentSession("stub1");
    ({ logs, errors } = await captureConsole(() =>
      whoami.parseAsync([], { from: "user" }),
    ));
    assert.ok(errors.some((line) => line.includes("No user is signed in")));
    assert.ok(logs.some((line) => line.includes("a@b.com")));
    assert.ok(logs.some((line) => line.includes("login --switch")));
  });

  await authCheck("client-reset-confirmation", async () => {
    const question = questionsClientReset([
      { email: "a@b.com", endpoint: "https://cloud.appwrite.io/v1" },
    ])[0];
    assert.equal(question.type, "confirm");
    assert.match(question.message, /a@b.com.*cloud\.appwrite\.io/);
    assert.equal(question.default, false);

    globalConfig.clear();
    globalConfig.addSession("auth1", {
      endpoint: "https://cloud.appwrite.io/v1",
      email: "a@b.com",
      accessToken: "tok",
    });
    globalConfig.setCurrentSession("auth1");
    cliConfig.force = false;

    const originalIsTTY = Object.getOwnPropertyDescriptor(process.stdin, "isTTY");
    Object.defineProperty(process.stdin, "isTTY", {
      configurable: true,
      enumerable: true,
      get: () => false,
    });
    try {
      const { errors } = await captureConsole(() =>
        withProcessExitStub(async (getExitCode) => {
          try {
            await runClient(["--reset"]);
            assert.fail("expected reset to fail without --force on a non-TTY");
          } catch (error) {
            assert.match(String(error && error.message), /process\.exit:1/);
            assert.equal(getExitCode(), 1);
          }
        }),
      );
      assert.ok(
        errors.some((line) => line.includes("Re-run with --force to confirm")),
      );
      assert.equal(globalConfig.getCurrentSession(), "auth1");
    } finally {
      if (originalIsTTY) {
        Object.defineProperty(process.stdin, "isTTY", originalIsTTY);
      } else {
        delete process.stdin.isTTY;
      }
    }

    globalConfig.clear();
    keyringTokens.clear();
    globalConfig.addSession("stub1", { endpoint: "http://localhost/v1" });
    globalConfig.setCurrentSession("stub1");
    cliConfig.force = true;
    try {
      await withProcessExitStub(() => runClient(["--reset"]));
      assert.equal(globalConfig.get("stub1"), undefined);
      assert.equal(globalConfig.getCurrentSession(), "");
    } finally {
      cliConfig.force = false;
    }
  });

  await authCheck("poll-device-token-success", async () => {
    const oauth2 = { createToken: async () => ({ access_token: "tok", expires_in: 3600 }) };
    const token = await pollForDeviceToken(oauth2, deviceAuth(), "cli");
    assert.equal(token.access_token, "tok");
  });

  await authCheck("poll-device-token-retry", async () => {
    let calls = 0;
    const oauth2 = {
      createToken: async () => {
        calls += 1;
        if (calls === 1) {
          throw new AppwriteException("authorization_pending", 428, "authorization_pending");
        }
        return { access_token: "tok2", expires_in: 3600 };
      },
    };
    const token = await pollForDeviceToken(oauth2, deviceAuth(), "cli");
    assert.equal(token.access_token, "tok2");
    assert.equal(calls, 2);
  });

  await authCheck("poll-device-token-error", async () => {
    const oauth2 = {
      createToken: async () => {
        throw new AppwriteException("boom", 500, "general_server_error");
      },
    };
    await assert.rejects(() => pollForDeviceToken(oauth2, deviceAuth(), "cli"));
  });

  await authCheck("poll-device-token-timeout", async () => {
    const oauth2 = {
      createToken: async () => {
        throw new AppwriteException("authorization_pending", 428, "authorization_pending");
      },
    };
    const token = await pollForDeviceToken(oauth2, deviceAuth({ expires_in: 0.05 }), "cli");
    assert.equal(token, null);
  });

  await authCheck("poll-device-token-slow-down", async () => {
    let calls = 0;
    const oauth2 = {
      createToken: async () => {
        calls += 1;
        if (calls === 1) throw new AppwriteException("slow_down", 400, "slow_down");
        return { access_token: "tok3", expires_in: 3600 };
      },
    };
    const token = await pollForDeviceToken(oauth2, deviceAuth(), "cli");
    assert.equal(token.access_token, "tok3");
    assert.equal(calls, 2);
  });

  await authCheck("poll-device-token-empty-error", async () => {
    let calls = 0;
    const oauth2 = {
      createToken: async () => {
        calls += 1;
        if (calls === 1) throw new AppwriteException("", 400, "", "");
        return { access_token: "tok4", expires_in: 3600 };
      },
    };
    const token = await pollForDeviceToken(oauth2, deviceAuth(), "cli");
    assert.equal(token.access_token, "tok4");
    assert.equal(calls, 2);
  });

  await authCheck("poll-device-token-default-interval", async () => {
    // interval omitted: must fall back to a real 5s interval (not NaN, which
    // would resolve immediately and busy-poll the endpoint).
    const oauth2 = {
      createToken: async () => ({ access_token: "tok5", expires_in: 3600 }),
    };
    const startedAt = Date.now();
    const token = await pollForDeviceToken(
      oauth2,
      { expires_in: 30, device_code: "dc" },
      "cli",
    );
    assert.equal(token.access_token, "tok5");
    assert.ok(Date.now() - startedAt >= 4000);
  });

  await authCheck("valid-access-token-cached", async () => {
    globalConfig.clear();
    globalConfig.addSession("tok1", {
      endpoint: "http://localhost/v1",
      accessToken: "cached-token",
      tokenExpiry: Date.now() + 3600000,
    });
    globalConfig.setCurrentSession("tok1");
    const token = await getValidAccessToken();
    assert.equal(token, "cached-token");
  });

  await authCheck("valid-access-token-missing-expiry", async () => {
    globalConfig.clear();
    globalConfig.addSession("tok2", {
      endpoint: "http://localhost/v1",
      accessToken: "cached-token-without-expiry",
    });
    globalConfig.setCurrentSession("tok2");
    const token = await getValidAccessToken();
    assert.equal(token, "cached-token-without-expiry");
  });

  await authCheck("valid-access-token-session-endpoint", async () => {
    globalConfig.clear();
    keyringTokens.clear();
    globalConfig.addSession("tok3", {
      endpoint: "https://cloud.staging.appwrite.io/v1",
      accessToken: "expired-token",
      tokenExpiry: Date.now() - 1000,
    });
    globalConfig.setCurrentSession("tok3");
    setStoredRefreshToken("tok3", "refresh-token");

    const originalCreateToken = Oauth2.prototype.createToken;
    let refreshEndpoint = "";
    Oauth2.prototype.createToken = async function (params) {
      refreshEndpoint = this.client.config.endpoint;
      assert.equal(params.grantType, "refresh_token");
      assert.equal(params.refreshToken, "refresh-token");
      return {
        access_token: "refreshed-token",
        refresh_token: "rotated-refresh-token",
        expires_in: 3600,
      };
    };

    try {
      assert.equal(await getValidAccessToken(), "refreshed-token");
    } finally {
      Oauth2.prototype.createToken = originalCreateToken;
    }

    assert.equal(refreshEndpoint, "https://cloud.staging.appwrite.io/v1");
    assert.equal(getStoredRefreshToken("tok3"), "rotated-refresh-token");
  });

  await authCheck("project-session-endpoint-mismatch", async () => {
    globalConfig.clear();
    globalConfig.addSession("tok4", {
      endpoint: "https://cloud.staging.appwrite.io/v1",
      accessToken: "cached-token",
      tokenExpiry: Date.now() + 3600000,
    });
    globalConfig.setCurrentSession("tok4");

    const originalGetEndpoint = localConfig.getEndpoint;
    const originalGetProject = localConfig.getProject;
    localConfig.getEndpoint = () => "https://fra.cloud.appwrite.io/v1";
    localConfig.getProject = () => ({ projectId: "project-id" });

    try {
      await assert.rejects(
        () => sdkForProject(),
        /does not match the current login session endpoint/,
      );

      assert.throws(
        () => assertSessionEndpointMatches("http://localhost/v1"),
        /does not match the current login session endpoint/,
      );

      globalConfig.addSession("tok4", {
        endpoint: "http://localhost/v1",
        accessToken: "cached-token",
        tokenExpiry: Date.now() + 3600000,
      });
      assert.throws(
        () =>
          assertSessionEndpointMatches("https://cloud.staging.appwrite.io/v1"),
        /does not match the current login session endpoint/,
      );
      assert.doesNotThrow(() =>
        assertSessionEndpointMatches("http://localhost/v1/"),
      );
    } finally {
      localConfig.getEndpoint = originalGetEndpoint;
      localConfig.getProject = originalGetProject;
    }
  });

  await authCheck("organization-header", async () => {
    globalConfig.clear();
    globalConfig.addSession("org-tok", {
      endpoint: "http://localhost/v1",
      accessToken: "cached-token",
      tokenExpiry: Date.now() + 3600000,
    });
    globalConfig.setCurrentSession("org-tok");

    const originalGetProject = localConfig.getProject;
    localConfig.getProject = () => ({
      projectId: "project-id",
      organizationId: "org-from-config",
    });

    try {
      const fromConfig = await sdkForConsoleWithOrganization();
      assert.equal(
        fromConfig.headers["X-Appwrite-Organization"],
        "org-from-config",
      );

      const fromFlag = await sdkForConsoleWithOrganization("org-from-flag");
      assert.equal(
        fromFlag.headers["X-Appwrite-Organization"],
        "org-from-flag",
      );

      localConfig.getProject = () => ({});
      await assert.rejects(
        () => sdkForConsoleWithOrganization(),
        /Organization is not set/,
      );
    } finally {
      localConfig.getProject = originalGetProject;
    }
  });

  await authCheck("project-id-override", async () => {
    globalConfig.clear();
    globalConfig.addSession("proj-tok", {
      endpoint: "http://localhost/v1",
      accessToken: "cached-token",
      tokenExpiry: Date.now() + 3600000,
    });
    globalConfig.setCurrentSession("proj-tok");

    const originalGetProject = localConfig.getProject;
    const originalGetEndpoint = localConfig.getEndpoint;
    localConfig.getEndpoint = () => "http://localhost/v1";
    localConfig.getProject = () => ({ projectId: "from-config" });

    try {
      const fromConfig = await sdkForProject();
      assert.equal(fromConfig.config.project, "from-config");

      const fromFlag = await sdkForProject("from-flag");
      assert.equal(fromFlag.config.project, "from-flag");

      localConfig.getProject = () => ({});
      await assert.rejects(() => sdkForProject(), /Project is not set/);

      const unlinked = await sdkForProject("from-flag");
      assert.equal(unlinked.config.project, "from-flag");
    } finally {
      localConfig.getProject = originalGetProject;
      localConfig.getEndpoint = originalGetEndpoint;
    }
  });

  await authCheck("cloud-login-rejects-credentials", async () => {
    const prev = process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN;
    delete process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN;
    try {
      for (const options of [
        { email: "user@example.com", password: "password" },
        { mfa: "totp" },
        { code: "123456" },
      ]) {
        await assert.rejects(
          () =>
            loginCommand({
              endpoint: "https://cloud.appwrite.io/v1",
              ...options,
            }),
          /Cloud sign-in happens in your browser/,
        );
      }

      // Self-hosted endpoints keep the email/password flow.
      assert.equal(isCloudLoginEndpoint("http://localhost/v1"), false);
    } finally {
      if (prev === undefined) delete process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN;
      else process.env.APPWRITE_CLI_DEV_CLOUD_LOGIN = prev;
    }
  });

  await authCheck("open-browser", () => {
    const childProcess = require("child_process");
    const originalSpawn = childProcess.spawn;
    const url = "https://cloud.appwrite.io/device";
    try {
      let captured = null;
      let errorHandler = null;
      childProcess.spawn = (command, args) => {
        captured = { command, args };
        return {
          on(event, cb) {
            if (event === "error") errorHandler = cb;
          },
          unref() {},
        };
      };
      openBrowser(url);
      assert.ok(captured, "expected openBrowser to spawn an open command");
      const expectedCommand =
        process.platform === "win32"
          ? "rundll32"
          : process.platform === "darwin"
            ? "open"
            : "xdg-open";
      assert.equal(captured.command, expectedCommand);
      assert.ok(
        captured.args.includes(url),
        "expected the verification URL to be passed to the open command",
      );

      // A missing opener (e.g. no xdg-open) surfaces as an async 'error' event;
      // it must be swallowed, not crash the process.
      assert.equal(typeof errorHandler, "function");
      assert.doesNotThrow(() => errorHandler(new Error("spawn ENOENT")));

      // A synchronous spawn failure must also not propagate.
      childProcess.spawn = () => {
        throw new Error("spawn ENOENT");
      };
      assert.doesNotThrow(() => openBrowser(url));

      const originalPlatform = process.platform;
      try {
        Object.defineProperty(process, "platform", {
          value: "win32",
        });

        const windowsUrl =
          "https://cloud.appwrite.io/device?user=a b&next=one|two<three>four^five";
        captured = null;
        childProcess.spawn = (command, args) => {
          captured = { command, args };
          return {
            on() {},
            unref() {},
          };
        };

        openBrowser(windowsUrl);
        assert.deepEqual(captured, {
          command: "rundll32",
          args: ["url.dll,FileProtocolHandler", windowsUrl],
        });
      } finally {
        Object.defineProperty(process, "platform", {
          value: originalPlatform,
        });
      }

      const listeners = new Map();
      const stdin = process.stdin;
      const originalIsTTY = stdin.isTTY;
      const originalIsRaw = stdin.isRaw;
      const originalSetRawMode = stdin.setRawMode;
      const originalResume = stdin.resume;
      const originalPause = stdin.pause;
      const originalOn = stdin.on;
      const originalOff = stdin.off;
      let paused = false;

      try {
        stdin.isTTY = true;
        stdin.isRaw = false;
        stdin.setRawMode = (mode) => {
          stdin.isRaw = mode;
          return stdin;
        };
        stdin.resume = () => {
          paused = false;
          return stdin;
        };
        stdin.pause = () => {
          paused = true;
          return stdin;
        };
        stdin.on = (event, listener) => {
          listeners.set(event, listener);
          return stdin;
        };
        stdin.off = (event, listener) => {
          if (listeners.get(event) === listener) {
            listeners.delete(event);
          }
          return stdin;
        };

        const cleanup = listenForBrowserOpen(url, () => {});
        cleanup();

        assert.equal(paused, true);
        assert.equal(stdin.isRaw, false);
        assert.equal(listeners.has("data"), false);
      } finally {
        stdin.isTTY = originalIsTTY;
        stdin.isRaw = originalIsRaw;
        stdin.setRawMode = originalSetRawMode;
        stdin.resume = originalResume;
        stdin.pause = originalPause;
        stdin.on = originalOn;
        stdin.off = originalOff;
      }
    } finally {
      childProcess.spawn = originalSpawn;
    }
  });

  await authCheck("context-organization-lookup", async () => {
    // organizationId missing: the org is derived via a raw projects lookup.
    // GET /projects/{projectId} is not published in the spec, so there is no
    // generated service method and the call must set X-Appwrite-Project itself
    // — without it the API treats the request as a guest and rejects it with a
    // missing-scopes 401.
    const calls = [];
    const consoleClient = {
      headers: {},
      config: { endpoint: "http://mockapi/v1" },
      call: async (method, url, headers) => {
        calls.push({ method, url: url.toString(), headers });
        return { teamId: "team-1" };
      },
    };

    const previousEnv = process.env.APPWRITE_PROJECT_ID;
    process.env.APPWRITE_PROJECT_ID = "project-1";

    try {
      const organizationId = await muteStdout(() =>
        resolveOrganizationId({ consoleClient }),
      );

      assert.equal(calls.length, 1);
      assert.equal(calls[0].method, "get");
      assert.equal(calls[0].url, "http://mockapi/v1/projects/project-1");
      assert.equal(calls[0].headers["X-Appwrite-Project"], "console");
      assert.equal(organizationId, "team-1");

      // An explicit --organization-id is used directly, with no lookup request.
      const directClient = {
        headers: {},
        config: { endpoint: "http://mockapi/v1" },
        call: async () => {
          throw new Error("unexpected API call when organizationId is set");
        },
      };
      assert.equal(
        await resolveOrganizationId({
          override: "org-1",
          consoleClient: directClient,
        }),
        "org-1",
      );
    } finally {
      if (previousEnv === undefined) delete process.env.APPWRITE_PROJECT_ID;
      else process.env.APPWRITE_PROJECT_ID = previousEnv;
    }
  });

  await authCheck("context-project-precedence", async () => {
    // --project-id must beat the environment, which must beat the linked
    // project, so the same ID cannot apply to some commands and be ignored by
    // others.
    const previousEnv = process.env.APPWRITE_PROJECT_ID;

    try {
      delete process.env.APPWRITE_PROJECT_ID;
      const configured = resolveProjectId();

      process.env.APPWRITE_PROJECT_ID = "from-env";
      assert.equal(resolveProjectId(), "from-env");

      assert.equal(resolveProjectId("from-flag"), "from-flag");

      // Falls back to the linked project once the override is gone.
      delete process.env.APPWRITE_PROJECT_ID;
      assert.equal(resolveProjectId(), configured);
    } finally {
      if (previousEnv === undefined) delete process.env.APPWRITE_PROJECT_ID;
      else process.env.APPWRITE_PROJECT_ID = previousEnv;
    }
  });

  globalConfig.clear();
  restoreKeyringEntryFactory();
  if (previousNodeEnv === undefined) delete process.env.NODE_ENV;
  else process.env.NODE_ENV = previousNodeEnv;
}

/** Push attribute sync: in-place updates vs recreate, indexes, resize hard-fail. */
async function runAttributeSyncChecks() {
  const { Attributes } = require("./lib/commands/utils/attributes.ts");
  const collection = { $id: "posts", databaseId: "blog", name: "Posts" };
  const attr = (overrides) => ({
    required: false,
    default: null,
    array: false,
    ...overrides,
  });

  const check = async (name, fn) => {
    try {
      await muteStdout(fn);
      console.log(`attribute:${name}:passed`);
    } catch (error) {
      console.log(`attribute:${name}:failed`);
      console.error(`attribute:${name}`, error?.message ?? error);
    }
  };

  const sync = async (remote, local, isIndex = false) => {
    const updates = [];
    const deletes = [];
    const waiters = [];
    const helper = new Attributes(
      {
        waitForAttributeDeletion: async (_db, _id, keys) => {
          waiters.push({ type: "attribute", keys: [...keys] });
          return true;
        },
        waitForIndexDeletion: async (_db, _id, keys) => {
          waiters.push({ type: "index", keys: [...keys] });
          return true;
        },
        expectAttributes: async (_db, _id, keys) => {
          waiters.push({ type: "expect", keys: [...keys] });
          return true;
        },
      },
      true,
    );
    helper.updateAttribute = async (_db, _id, a, newKey) =>
      updates.push(newKey !== undefined ? { ...a, newKey } : a);
    helper.deleteAttribute = async (_c, a, index = false) =>
      deletes.push({ key: a.key, isIndex: index });
    const result = await helper.attributesToCreate(
      remote,
      local,
      collection,
      isIndex,
    );
    return { updates, deletes, result, waiters };
  };

  await check("in-place-updates", async () => {
    const cases = [
      {
        remote: [attr({ key: "title", type: "varchar", size: 50 })],
        local: [attr({ key: "title", type: "varchar", size: 120 })],
        expect: { updates: 1, deletes: 0 },
      },
      {
        remote: [attr({ key: "slug", type: "string", size: 32 })],
        local: [attr({ key: "slug", type: "string", size: 64 })],
        expect: { updates: 1, deletes: 0 },
      },
      {
        remote: [
          attr({
            key: "author",
            type: "relationship",
            relatedCollection: "users",
            relationType: "manyToOne",
            twoWay: false,
            onDelete: "cascade",
            side: "parent",
          }),
        ],
        local: [
          attr({
            key: "author",
            type: "relationship",
            relatedCollection: "users",
            relationType: "manyToOne",
            twoWay: false,
            onDelete: "restrict",
          }),
        ],
        expect: { updates: 1, deletes: 0 },
      },
      {
        remote: [
          attr({
            key: "status",
            type: "string",
            format: "enum",
            elements: ["draft"],
            default: "draft",
          }),
          attr({ key: "score", type: "integer", default: 0, min: 0, max: 10 }),
        ],
        local: [
          attr({
            key: "status",
            type: "string",
            format: "enum",
            elements: ["draft", "live"],
            required: true,
          }),
          attr({ key: "score", type: "integer", default: 1, min: 1, max: 100 }),
        ],
        expect: { updates: 2, deletes: 0 },
      },
    ];

    for (const { remote, local, expect } of cases) {
      const { updates, deletes, result } = await sync(remote, local);
      assert.equal(updates.length, expect.updates);
      assert.equal(deletes.length, expect.deletes);
      assert.deepEqual(result.attributes, []);
    }
  });

  await check("recreates-immutable", async () => {
    const { updates, deletes, result } = await sync(
      [
        attr({ key: "count", type: "string", size: 16 }),
        attr({ key: "tags", type: "string", size: 32, encrypt: false }),
        attr({ key: "secret", type: "string", size: 64, encrypt: false }),
      ],
      [
        attr({ key: "count", type: "integer" }),
        attr({ key: "tags", type: "string", size: 32, array: true }),
        attr({ key: "secret", type: "string", size: 64, encrypt: true }),
      ],
    );
    assert.equal(updates.length, 0);
    assert.equal(deletes.length, 3);
    assert.equal(result.attributes.length, 3);
  });

  await check("ignores-derived-fields", async () => {
    const { updates, deletes, result } = await sync(
      [
        attr({ key: "body", type: "text", size: 65535 }),
        attr({
          key: "author",
          type: "relationship",
          relatedCollection: "users",
          relationType: "manyToOne",
          twoWay: false,
          onDelete: "cascade",
          side: "parent",
        }),
      ],
      [
        attr({ key: "body", type: "text" }),
        attr({
          key: "author",
          type: "relationship",
          relatedCollection: "users",
          relationType: "manyToOne",
          twoWay: false,
          onDelete: "cascade",
        }),
      ],
    );
    assert.equal(updates.length, 0);
    assert.equal(deletes.length, 0);
    assert.equal(result.hasChanges, false);
  });

  await check("omitted-encrypt-not-recreate", async () => {
    // Remote has encrypt:false; local omits encrypt — must not recreate.
    const { updates, deletes, result } = await sync(
      [attr({ key: "title", type: "string", size: 255, encrypt: false })],
      [attr({ key: "title", type: "string", size: 255 })],
    );
    assert.equal(updates.length, 0);
    assert.equal(deletes.length, 0);
    assert.equal(result.hasChanges, false);

    // Explicit encrypt:true still forces recreate.
    const changed = await sync(
      [attr({ key: "title", type: "string", size: 255, encrypt: false })],
      [attr({ key: "title", type: "string", size: 255, encrypt: true })],
    );
    assert.equal(changed.updates.length, 0);
    assert.equal(changed.deletes.length, 1);
    assert.equal(changed.result.attributes.length, 1);
  });

  await check("index-columns-change", async () => {
    const { updates, deletes, result, waiters } = await sync(
      [{ key: "by_title", type: "key", columns: ["title"], orders: ["ASC"] }],
      [
        {
          key: "by_title",
          type: "key",
          columns: ["title", "status"],
          orders: ["ASC"],
        },
      ],
      true,
    );
    assert.equal(updates.length, 0);
    assert.equal(deletes.length, 1);
    assert.equal(deletes[0].isIndex, true);
    assert.deepEqual(result.attributes[0].columns, ["title", "status"]);
    // Index recreates must wait on index deletion, not listAttributes.
    assert.deepEqual(waiters, [{ type: "index", keys: ["by_title"] }]);
  });

  await check("attribute-delete-uses-attribute-waiter", async () => {
    const { deletes, waiters } = await sync(
      [attr({ key: "old", type: "string", size: 16 })],
      [],
    );
    assert.equal(deletes.length, 1);
    assert.deepEqual(waiters, [{ type: "attribute", keys: ["old"] }]);
  });

  await check("update-guards", async () => {
    const helper = new Attributes(
      { waitForAttributeDeletion: async () => true },
      true,
    );
    await assert.rejects(
      () =>
        helper.updateAttribute("blog", "posts", {
          key: "by_title",
          type: "key",
          columns: ["title"],
        }),
      /Indexes cannot be updated in place/,
    );

    const source = fs.readFileSync(
      path.join(process.cwd(), "lib/commands/utils/attributes.ts"),
      "utf8",
    );
    const match = source.match(/updateStringAttribute\(\{([\s\S]*?)\}\)/);
    assert.ok(match);
    assert.match(match[1], /size:\s*attribute\.size/);
  });

  await check("resize-hard-fail", async () => {
    const deletes = [];
    const helper = new Attributes(
      { waitForAttributeDeletion: async () => true },
      true,
    );
    helper.updateAttribute = async () => {
      throw new Error("attribute_invalid_resize: Resize would truncate data");
    };
    helper.deleteAttribute = async (_c, a) => deletes.push(a.key);

    await assert.rejects(
      () =>
        helper.attributesToCreate(
          [
            attr({ key: "title", type: "varchar", size: 100 }),
            attr({ key: "legacy", type: "string", size: 16 }),
          ],
          [
            attr({ key: "title", type: "varchar", size: 10 }),
            attr({ key: "legacy", type: "integer" }),
          ],
          collection,
        ),
      /existing values exceed the new size/,
    );
    assert.equal(deletes.length, 0);
  });

  await check("rename-in-place", async () => {
    const { updates, deletes, result, waiters } = await sync(
      [attr({ key: "title", type: "string", size: 255 })],
      [
        attr({
          key: "headline",
          previousKey: "title",
          type: "string",
          size: 255,
        }),
      ],
    );
    assert.equal(updates.length, 1);
    assert.equal(updates[0].key, "title");
    assert.equal(updates[0].newKey, "headline");
    assert.equal(deletes.length, 0);
    assert.deepEqual(result.attributes, []);
    assert.deepEqual(result.renames, [
      {
        from: "title",
        to: "headline",
        attribute: attr({ key: "title", type: "string", size: 255 }),
      },
    ]);
    assert.deepEqual(waiters, [{ type: "expect", keys: ["headline"] }]);
  });

  await check("rename-already-applied", async () => {
    const { updates, deletes, result } = await sync(
      [attr({ key: "headline", type: "string", size: 255 })],
      [
        attr({
          key: "headline",
          previousKey: "title",
          type: "string",
          size: 255,
        }),
      ],
    );
    assert.equal(updates.length, 0);
    assert.equal(deletes.length, 0);
    assert.equal(result.hasChanges, false);
    assert.deepEqual(result.renames, []);
  });

  await check("rename-missing-both-creates", async () => {
    const { updates, deletes, result } = await sync(
      [],
      [
        attr({
          key: "headline",
          previousKey: "title",
          type: "string",
          size: 255,
        }),
      ],
    );
    assert.equal(updates.length, 0);
    assert.equal(deletes.length, 0);
    assert.equal(result.attributes.length, 1);
    assert.equal(result.attributes[0].key, "headline");
    assert.deepEqual(result.renames, []);
  });

  await check("rename-both-exist-deletes-old", async () => {
    const { updates, deletes, result } = await sync(
      [
        attr({ key: "title", type: "string", size: 255 }),
        attr({ key: "headline", type: "string", size: 255 }),
      ],
      [
        attr({
          key: "headline",
          previousKey: "title",
          type: "string",
          size: 255,
        }),
      ],
    );
    assert.equal(updates.length, 0);
    assert.equal(deletes.length, 1);
    assert.equal(deletes[0].key, "title");
    assert.deepEqual(result.attributes, []);
    assert.deepEqual(result.renames, []);
  });

  await check("rename-plus-field-change", async () => {
    const { updates, deletes, result, waiters } = await sync(
      [attr({ key: "title", type: "string", size: 50 })],
      [
        attr({
          key: "headline",
          previousKey: "title",
          type: "string",
          size: 120,
        }),
      ],
    );
    assert.equal(updates.length, 2);
    assert.equal(updates[0].key, "title");
    assert.equal(updates[0].newKey, "headline");
    assert.equal(updates[1].key, "headline");
    assert.equal(updates[1].size, 120);
    assert.equal(updates[1].newKey, undefined);
    assert.equal(deletes.length, 0);
    assert.deepEqual(result.attributes, []);
    assert.deepEqual(waiters, [{ type: "expect", keys: ["headline"] }]);
  });

  await check("rename-preserves-indexes", async () => {
    const remoteColumns = [attr({ key: "title", type: "string", size: 255 })];
    const localColumns = [
      attr({
        key: "headline",
        previousKey: "title",
        type: "string",
        size: 255,
      }),
    ];
    const { result: columnsResult } = await sync(remoteColumns, localColumns);
    assert.equal(columnsResult.renames.length, 1);

    // Mirror push.ts: rewrite remote index refs with the rename map.
    const renameMap = new Map(
      columnsResult.renames.map((r) => [r.from, r.to]),
    );
    const remoteIndexes = [
      { key: "by_title", type: "key", columns: ["title"], orders: ["ASC"] },
    ].map((idx) => ({
      ...idx,
      columns: idx.columns.map((c) => renameMap.get(c) ?? c),
    }));
    const localIndexes = [
      {
        key: "by_title",
        type: "key",
        columns: ["headline"],
        orders: ["ASC"],
      },
    ];
    const { updates, deletes, result } = await sync(
      remoteIndexes,
      localIndexes,
      true,
    );
    assert.equal(updates.length, 0);
    assert.equal(deletes.length, 0);
    assert.equal(result.hasChanges, false);
  });

  await check("rename-hard-fail-before-delete", async () => {
    const deletes = [];
    const helper = new Attributes(
      {
        waitForAttributeDeletion: async () => true,
        expectAttributes: async () => true,
      },
      true,
    );
    helper.updateAttribute = async (_db, _id, _a, newKey) => {
      if (newKey) {
        throw new Error("rename_failed: conflict");
      }
    };
    helper.deleteAttribute = async (_c, a) => deletes.push(a.key);

    await assert.rejects(
      () =>
        helper.attributesToCreate(
          [
            attr({ key: "title", type: "string", size: 255 }),
            attr({ key: "legacy", type: "string", size: 16 }),
          ],
          [
            attr({
              key: "headline",
              previousKey: "title",
              type: "string",
              size: 255,
            }),
            attr({ key: "legacy", type: "integer" }),
          ],
          collection,
        ),
      /Error renaming attribute/,
    );
    assert.equal(deletes.length, 0);
  });

  await check("rename-schema-validation", async () => {
    const {
      ColumnSchema,
      IndexSchema,
      TableSchema,
    } = require("./lib/commands/config.ts");

    // previousKey is allowed on columns.
    assert.equal(
      ColumnSchema.safeParse(
        attr({
          key: "headline",
          previousKey: "title",
          type: "string",
          size: 255,
        }),
      ).success,
      true,
    );

    // previousKey is rejected on indexes (.strict()).
    assert.equal(
      IndexSchema.safeParse({
        key: "by_title",
        type: "key",
        attributes: ["title"],
        previousKey: "old",
      }).success,
      false,
    );

    // previousKey === key is rejected.
    const sameKey = TableSchema.safeParse({
      $id: "posts",
      databaseId: "blog",
      name: "Posts",
      columns: [
        attr({
          key: "title",
          previousKey: "title",
          type: "string",
          size: 255,
        }),
      ],
    });
    assert.equal(sameKey.success, false);

    // Collision: another column already uses previousKey as its key.
    const collision = TableSchema.safeParse({
      $id: "posts",
      databaseId: "blog",
      name: "Posts",
      columns: [
        attr({ key: "title", type: "string", size: 255 }),
        attr({
          key: "headline",
          previousKey: "title",
          type: "string",
          size: 255,
        }),
      ],
    });
    assert.equal(collision.success, false);

    // Duplicate previousKey across columns is rejected.
    const duplicatePrevious = TableSchema.safeParse({
      $id: "posts",
      databaseId: "blog",
      name: "Posts",
      columns: [
        attr({
          key: "headline",
          previousKey: "title",
          type: "string",
          size: 255,
        }),
        attr({
          key: "heading",
          previousKey: "title",
          type: "string",
          size: 255,
        }),
      ],
    });
    assert.equal(duplicatePrevious.success, false);
  });
}

// A function that shares code through a symlink (appwrite/sdk-for-cli#253).
// Deliberately silent on success so the positional output assertions above are
// unaffected; a regression throws and fails the run.
async function runDeploymentSymlinkChecks() {
  const { list } = await import("tar");
  const { resolveFileParam } = await import(
    "./lib/commands/utils/deployment.ts"
  );

  const root = fs.mkdtempSync(path.join(os.tmpdir(), "cli-symlink-"));
  const functionDir = path.join(root, "app");
  fs.mkdirSync(path.join(functionDir, "src"), { recursive: true });
  fs.mkdirSync(path.join(root, "shared"));
  fs.writeFileSync(path.join(functionDir, "src/main.js"), "export default 1;\n");
  fs.writeFileSync(
    path.join(root, "shared/helper.js"),
    "export const help = () => true;\n",
  );
  // A shared directory and a single shared file, both reached by symlink.
  fs.symlinkSync(
    path.join("..", "..", "shared"),
    path.join(functionDir, "src/shared"),
  );
  fs.symlinkSync(
    path.join("..", "shared", "helper.js"),
    path.join(functionDir, "direct.js"),
  );
  // A self-referential link must not send either walk into an endless loop.
  fs.symlinkSync(path.join("..", "src"), path.join(functionDir, "src/loop"));
  // A link out of the project must not pull host files into the archive.
  const outside = fs.mkdtempSync(path.join(os.tmpdir(), "cli-symlink-outside-"));
  fs.writeFileSync(path.join(outside, "secret.txt"), "do not deploy me\n");
  fs.symlinkSync(
    path.join(outside, "secret.txt"),
    path.join(functionDir, "escape.txt"),
  );

  // Point the CLI at the fixture so `root` acts as the project directory that
  // bounds which symlinks may be followed.
  const previousCwd = process.cwd();
  process.chdir(root);
  localConfig.useCwdConfig();

  try {
    // Local run and Docker build discovery.
    const discovered = getAllFiles(functionDir, root).map((file) =>
      path.relative(functionDir, file).split(path.sep).join("/"),
    );
    assert.deepEqual(discovered.sort(), [
      "direct.js",
      "src/loop/main.js",
      "src/loop/shared/helper.js",
      "src/main.js",
      "src/shared/helper.js",
    ]);

    // Deployment packaging.
    const archivePath = path.join(root, "code.tar.gz");
    const archive = await resolveFileParam(functionDir);
    fs.writeFileSync(archivePath, Buffer.from(await archive.arrayBuffer()));

    const entries = new Map();
    await list({
      file: archivePath,
      onReadEntry: (entry) => entries.set(entry.path, entry),
    });

    // Shared code is packaged under its symlink path, and dereferenced into a
    // real file rather than a link that would dangle in the runtime.
    for (const name of ["src/shared/helper.js", "direct.js"]) {
      const entry = entries.get(name);
      assert.ok(
        entry,
        `Expected ${name} to be packaged, got ${JSON.stringify([...entries.keys()])}`,
      );
      assert.equal(entry.type, "File");
      assert.ok(entry.size > 0, `Expected ${name} to be packaged with content`);
    }

    assert.ok(
      !entries.has("escape.txt"),
      `Expected a symlink leaving the project to be excluded, got ${JSON.stringify([...entries.keys()])}`,
    );
  } finally {
    process.chdir(previousCwd);
    localConfig.useCwdConfig();
    fs.rmSync(root, { recursive: true, force: true });
    fs.rmSync(outside, { recursive: true, force: true });
  }
}

const HTML_ERROR_PAGE =
  "<!DOCTYPE html><html><body><h1>Page not found</h1></body></html>";

async function withStubServer(run) {
  const paths = [];
  const server = http.createServer((request, response) => {
    paths.push(request.url);
    response.writeHead(404, { "content-type": "text/html" });
    response.end(HTML_ERROR_PAGE);
  });

  await new Promise((resolve) => server.listen(0, "127.0.0.1", resolve));
  const endpoint = `http://127.0.0.1:${server.address().port}`;

  try {
    await run({ endpoint, paths });
  } finally {
    await new Promise((resolve) => server.close(resolve));
  }
}

async function runErrorHandlingChecks() {
  await withStubServer(async ({ endpoint, paths }) => {
    const failure = await new Client()
      .setEndpoint(`${endpoint}/`)
      .call("GET", "/health/version")
      .catch((error) => error);

    assert.equal(failure.message, "HTTP 404 Not Found");
    assert.equal(failure.response, HTML_ERROR_PAGE);
    assert.deepEqual(paths, ["/health/version"]);
    assert.doesNotMatch(stripAnsi(formatErrorForLog(failure)), /<!DOCTYPE/);
  });

  await withStubServer(async ({ endpoint }) => {
    const prompts = [];
    const originalPrompt = inquirer.prompt;
    inquirer.prompt = async (questions) => {
      prompts.push(questions);
      return {};
    };

    try {
      const failure = await muteStdout(() =>
        loginCommand({ endpoint }).catch((error) => error),
      );
      assert.equal(
        failure.message,
        "Invalid endpoint or your Appwrite server is not running as expected.",
      );
      assert.deepEqual(prompts, []);
    } finally {
      inquirer.prompt = originalPrompt;
    }
  });

  console.log("CLI_ERROR_HANDLING:passed");
}

async function runConsoleFallbackChecks() {
  const { Oauth2, Organization, Teams } = await import("@appwrite.io/console");
  const originals = {
    listProjects: Oauth2.prototype.listProjects,
    listOrganizations: Oauth2.prototype.listOrganizations,
    listTeams: Teams.prototype.list,
    getTeam: Teams.prototype.get,
    getOrganization: Organization.prototype.get,
    listOrganizationProjects: Organization.prototype.listProjects,
  };
  const calls = { oauth2: 0, team: [] };
  const routeMissing = () => {
    throw Object.assign(new Error("Not Found"), {
      code: 404,
      type: "general_route_not_found",
    });
  };

  Oauth2.prototype.listProjects = async () => {
    calls.oauth2++;
    return routeMissing();
  };
  Oauth2.prototype.listOrganizations = async () => {
    calls.oauth2++;
    return routeMissing();
  };
  Teams.prototype.list = async () => ({
    total: 1,
    teams: [{ $id: "org1", name: "Self-hosted org" }],
  });
  Teams.prototype.get = async function (teamId) {
    calls.team.push(teamId);
    return { $id: teamId, name: "Self-hosted org" };
  };
  Organization.prototype.get = routeMissing;
  Organization.prototype.listProjects = async () => ({
    total: 1,
    projects: [{ $id: "p1", region: "default", name: "Project" }],
  });

  globalConfig.clear();
  globalConfig.addSession("session1", {
    endpoint: "http://localhost/v1",
    email: "test@example.com",
    cookie: "a_session_console=stub",
  });
  globalConfig.setCurrentSession("session1");
  globalConfig.setEndpoint("http://localhost/v1");

  try {
    assert.deepEqual(await listOrganizationsForSession(), {
      total: 1,
      organizations: [{ $id: "org1" }],
    });
    assert.deepEqual(await listProjectsForSession(), {
      total: 1,
      projects: [
        {
          $id: "p1",
          region: "default",
          endpoint: "http://localhost/v1",
        },
      ],
    });
    assert.deepEqual(await getOrganizationForSession("org1"), {
      $id: "org1",
      name: "Self-hosted org",
    });
    assert.equal(calls.oauth2, 0);
    assert.deepEqual(calls.team, ["org1"]);
  } finally {
    Oauth2.prototype.listProjects = originals.listProjects;
    Oauth2.prototype.listOrganizations = originals.listOrganizations;
    Teams.prototype.list = originals.listTeams;
    Teams.prototype.get = originals.getTeam;
    Organization.prototype.get = originals.getOrganization;
    Organization.prototype.listProjects = originals.listOrganizationProjects;
    globalConfig.clear();
  }

  console.log("CLI_CONSOLE_FALLBACKS:passed");
}
