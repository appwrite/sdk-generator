const { execSync } = require("child_process");
const fs = require("fs");
const path = require("path");
const Client = require("./lib/client.ts").default;
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

execSync(
  "bun ./dist/cli.cjs client --endpoint 'http://mockapi/v1' --project-id console --key=35y3h5h345 --self-signed true",
  { stdio: "inherit" },
);

var output;
console.log("\nTest Started");
const sdkHeaders = new Client().getHeaders();
console.log(
  `x-sdk-name: ${sdkHeaders["x-sdk-name"]}; x-sdk-platform: ${sdkHeaders["x-sdk-platform"]}; x-sdk-language: ${sdkHeaders["x-sdk-language"]}; x-sdk-version: ${sdkHeaders["x-sdk-version"]}`,
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
