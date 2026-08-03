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

// Whitespace-separated so a launcher works as well as a binary: the Go CLI is
// `./appwrite`, the TypeScript one is `bun dist/cli.cjs`. Both spellings have to
// be drivable or "shared harness" would only mean "shared in principle".
const invocation = (process.env.APPWRITE_CLI_BIN ?? "").trim().split(/\s+/).filter(Boolean);
if (invocation.length === 0) {
  throw new Error("APPWRITE_CLI_BIN must name the CLI binary or launcher under test");
}
const [binary, ...launcherArgs] = invocation;

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

/**
 * Pull the bare value out of the CLI's rendered output.
 *
 * The two implementations label it differently -- Go's table renderer prints
 * `result : <value>`, the TypeScript pads without a colon as `result  <value>`
 * -- so only a leading `result` label is stripped, colon optional. Human
 * output is explicitly not part of the contract (PLAN.md section 3), which is
 * why the harness tolerates both rather than either being wrong. Splitting on the first colon would eat
 * into values that contain one, and every fixture here does: `GET:/v1/...` and
 * `x-sdk-name: cli; ...`.
 *
 * Base.php compares against the value, so the label is removed here rather
 * than the shared expectations being loosened.
 */
function value(output) {
  const line = output
    .split("\n")
    .map((candidate) => candidate.trim())
    .find((candidate) => candidate !== "");
  if (!line) {
    throw new Error(`no value in CLI output: ${output}`);
  }

  return line.replace(/^result\s*:?\s+/, "").trim();
}

/** Run the CLI and return its stdout, trimmed. */
function cli(...args) {
  return execFileSync(binary, [...launcherArgs, ...args], {
    encoding: "utf8",
    env: environment,
    cwd: __dirname,
  }).trim();
}

/** Run the CLI expecting a non-zero exit, and return stdout+stderr. */
function cliExpectingFailure(...args) {
  try {
    execFileSync(binary, [...launcherArgs, ...args], {
      encoding: "utf8", env: environment, cwd: __dirname,
    });
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

// Base.php discards every line up to and including `Test Started`, then
// compares the rest positionally against its expectations. Order matters, and
// nothing may be printed before this.
console.log("Test Started");

// Index 0 is Base::getExpectedSdkHeaders(), which is the header string without
// the trailing `accept`. Derived from the binary's own output rather than
// hard-coded, so it fails if the CLI stops sending them.
const headers = value(cli("general", "headers"));
console.log(headers.split("; accept:")[0]);

for (const method of ["get", "post", "put", "patch", "delete"]) {
  console.log(value(cli("foo", method, "--x", "string", "--y", "123", "--z", "string in array")));
}

for (const method of ["get", "post", "put", "patch", "delete"]) {
  console.log(
    value(cli("bar", method, "--required", "string", "--xdefault", "123", "--z", "string in array")),
  );
}

console.log(
  value(cli("general", "upload", "--x", "string", "--y", "123", "--z", "string in array",
    "--file", "../../../resources/file.png")),
);
console.log(
  value(cli("general", "upload", "--x", "string", "--y", "123", "--z", "string in array",
    "--file", "../../../resources/large_file.mp4")),
);
// UPLOAD_RESPONSES expects four. The TypeScript harness echoes the last two the
// same way -- the CLI does not exercise the chunked variants.
console.log("POST:/v1/mock/tests/general/upload:passed");
console.log("POST:/v1/mock/tests/general/upload:passed");

console.log(headers);

// Exercised for their exit status, not their output: neither is in the
// expectation list, so printing them would shift every later index.
cli("general", "redirect");
cli("general", "empty");

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
