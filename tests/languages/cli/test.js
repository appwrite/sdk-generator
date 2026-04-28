const { execFileSync, execSync } = require("child_process");
const fs = require("fs");
const os = require("os");
const path = require("path");
const Client = require("./lib/client.ts").default;
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
  getFunctionDeploymentConsoleUrl,
  getSiteDeploymentConsoleUrl,
} = require("./lib/utils.ts");
const { EXECUTABLE_NAME } = require("./lib/constants.ts");
const { isCompletionInvocation } = require("./lib/completions.ts");

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

const extractHelpCommands = (helpOutput) => {
  const commandsIndex = helpOutput.indexOf("Commands:");

  if (commandsIndex === -1) {
    throw new Error(
      `Expected help output to include a Commands section.\n${helpOutput}`,
    );
  }

  return helpOutput
    .slice(commandsIndex)
    .split("\n")
    .map((line) => line.match(/^\s{2}([a-zA-Z0-9-]+)\b/)?.[1])
    .filter((commandName) => commandName && commandName !== "help");
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
  "bun ./dist/cli.cjs general upload --x string  --y 123 --z string in array --file ../../resources/file.png",
  { stdio: "pipe" },
).toString();
console.log(extractFirstValue(output));

output = execSync(
  "bun ./dist/cli.cjs general upload --x string  --y 123 --z string in array --file ../../resources/large_file.mp4",
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

output = execFileSync(
  "bun",
  [
    "./dist/cli.cjs",
    "general",
    "list-rows",
    "--queries",
    '{"method":"orderDesc","attribute":"rawName"}',
    "--where",
    "published=true",
    "--where",
    "score>=10",
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
    ["./dist/cli.cjs", "general", "list-rows", "--where", "count>1e999"],
    { stdio: "pipe" },
  );
  throw new Error("Expected non-finite numeric where values to be rejected.");
} catch (error) {
  if (!String(error.stderr ?? error.message).includes("finite numbers")) {
    throw error;
  }
}

try {
  execFileSync(
    "bun",
    ["./dist/cli.cjs", "general", "list-rows", "--where", "count=[1e999]"],
    { stdio: "pipe" },
  );
  throw new Error("Expected non-finite array where values to be rejected.");
} catch (error) {
  if (!String(error.stderr ?? error.message).includes("finite numbers")) {
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
  console.log("CLI_TYPEGEN:passed");
})().catch((error) => {
  throw error;
});
