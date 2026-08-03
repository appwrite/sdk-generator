// CLI-level conformance harness, shared by every CLI implementation.
//
// tests/e2e/languages/cli/test.js is roughly half shell-out assertions and half
// `require("./lib/*.ts")` unit assertions against TypeScript internals. Only the
// first half describes the CLI's observable behaviour, and only the first half
// can be run against another implementation. That half lives here.
//
// The Go CLI's equivalent of the TypeScript-internal half is its own
// `go test ./internal/...` suite. What both implementations must agree on is
// what the binary prints, which is exactly what this file checks.
//
// The binary under test comes from APPWRITE_CLI_BIN, so one harness drives both
// and a divergence cannot hide in an implementation-specific copy.

const { execFileSync } = require("child_process");
const fs = require("fs");
const os = require("os");
const path = require("path");

const binary = process.env.APPWRITE_CLI_BIN;
if (!binary) {
  throw new Error("APPWRITE_CLI_BIN must name the CLI binary under test");
}

// A sandboxed home keeps the run from reading or writing the developer's real
// credentials, and keeps repeat runs independent of each other.
const sandboxHome = fs.mkdtempSync(path.join(os.tmpdir(), "appwrite-cli-conformance-"));
process.on("exit", () => fs.rmSync(sandboxHome, { recursive: true, force: true }));

const environment = {
  ...process.env,
  HOME: sandboxHome,
  USERPROFILE: sandboxHome,
  NODE_ENV: "test",
};

/** Run the CLI and return its stdout, trimmed. */
function cli(...args) {
  return execFileSync(binary, args, {
    encoding: "utf8",
    env: environment,
    cwd: __dirname,
  }).trim();
}

/** Run the CLI expecting a non-zero exit, and return stdout+stderr. */
function cliExpectingFailure(...args) {
  try {
    execFileSync(binary, args, { encoding: "utf8", env: environment, cwd: __dirname });
  } catch (error) {
    return `${error.stdout ?? ""}${error.stderr ?? ""}`;
  }

  throw new Error(`expected \`${args.join(" ")}\` to fail`);
}

// Point the CLI at the mock API. Every subsequent command reads this.
cli(
  "client",
  "--endpoint", "http://mockapi/v1",
  "--project-id", "console",
  "--key", "35y3h5h345",
  "--self-signed", "true",
);

// Base.php asserts on these lines, so they are printed rather than compared
// here: one set of expectations, checked identically for every implementation.
for (const method of ["get", "post", "put", "patch", "delete"]) {
  console.log(cli("foo", method, "--x", "string", "--y", "123", "--z", "string in array"));
}

for (const method of ["get", "post", "put", "patch", "delete"]) {
  console.log(cli("bar", method, "--required", "string", "--xdefault", "123", "--z", "string in array"));
}

console.log(cli("general", "redirect"));
console.log(cli("general", "empty"));
console.log(cli("general", "headers"));

console.log(
  cli("general", "upload", "--x", "string", "--y", "123", "--z", "string in array",
    "--file", "../../../resources/file.png"),
);
console.log(
  cli("general", "upload", "--x", "string", "--y", "123", "--z", "string in array",
    "--file", "../../../resources/large_file.mp4"),
);

// Numeric filters must be rejected locally rather than sent as Infinity. The
// TypeScript raises InvalidArgumentError; any implementation must fail rather
// than pass a non-finite number to the API.
for (const flag of ["--filter", "--where"]) {
  const output = cliExpectingFailure("general", "list-rows", flag, "count>1e999");
  if (!/finite/i.test(output)) {
    throw new Error(`${flag} with a non-finite value should be rejected, got: ${output}`);
  }
}

console.log("CLI_CONFORMANCE:passed");
