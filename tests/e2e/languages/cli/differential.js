// Differential check: run both CLI implementations over the same commands and
// require their --json output to be byte-identical.
//
// shared-cli.js already asserts a set of fixtures, but it only proves each
// implementation independently satisfies expectations someone wrote down. This
// compares them directly, so a difference neither expectation happens to cover
// still fails. docs/go-cli/PLAN.md invariant 4: --json is scripted against, so
// the bytes are the contract.
//
// Both invocations are APPWRITE_CLI_BIN-style: whitespace-separated, so a
// launcher (`bun dist/cli.cjs`) works as well as a binary (`./appwrite`).

const { execFileSync } = require("child_process");
const fs = require("fs");
const os = require("os");
const path = require("path");

function invocation(name) {
  const raw = (process.env[name] ?? "").trim().split(/\s+/).filter(Boolean);
  if (raw.length === 0) {
    throw new Error(`${name} must name a CLI binary or launcher`);
  }

  return raw;
}

const reference = invocation("APPWRITE_CLI_REFERENCE");
const candidate = invocation("APPWRITE_CLI_CANDIDATE");

// Commands whose --json output both implementations must agree on.
//
// Read-only and side-effect free: this runs against the mock API, and a
// differential check that mutated state would compare the second run against a
// world the first one changed.
const commands = [
  ["foo", "get", "--x", "string", "--y", "123", "--z", "a"],
  ["foo", "post", "--x", "string", "--y", "123", "--z", "a"],
  ["bar", "get", "--required", "string", "--xdefault", "123", "--z", "a"],
  ["general", "headers"],
  // Query construction is where the two most plausibly diverge: the flags are
  // parsed and serialised independently in each implementation.
  ["general", "list-rows", "--filter", "count>1"],
  ["general", "list-rows", "--filter", "name=hello", "--limit", "5", "--offset", "2"],
  ["general", "list-rows", "--sort-asc", "name", "--sort-desc", "created"],
  ["general", "list-rows", "--filter", 'tags=["a","b"]'],
  ["general", "list-rows", "--filter", "flag=true", "--filter", "missing=null"],
  ["general", "list-rows", "--cursor-after", "abc"],
];

function run(command, args, home) {
  const [binary, ...prefix] = command;
  try {
    return execFileSync(binary, [...prefix, ...args], {
      encoding: "utf8",
      cwd: __dirname,
      env: { ...process.env, HOME: home, USERPROFILE: home, NODE_ENV: "test" },
    });
  } catch (error) {
    // A command that fails in both implementations is still a valid
    // comparison -- the error text is part of what users see.
    return `${error.stdout ?? ""}${error.stderr ?? ""}`;
  }
}

function configure(command, home) {
  run(command, [
    "client",
    "--endpoint", "http://mockapi/v1",
    "--project-id", "console",
    "--key", "35y3h5h345",
    "--self-signed", "true",
  ], home);
}

const referenceHome = fs.mkdtempSync(path.join(os.tmpdir(), "appwrite-diff-ref-"));
const candidateHome = fs.mkdtempSync(path.join(os.tmpdir(), "appwrite-diff-can-"));
process.on("exit", () => {
  fs.rmSync(referenceHome, { recursive: true, force: true });
  fs.rmSync(candidateHome, { recursive: true, force: true });
});

configure(reference, referenceHome);
configure(candidate, candidateHome);

const differences = [];
for (const args of commands) {
  const expected = run(reference, [...args, "--json"], referenceHome).trim();
  const actual = run(candidate, [...args, "--json"], candidateHome).trim();

  if (expected !== actual) {
    differences.push({ command: args.join(" "), expected, actual });
  }
}

if (differences.length > 0) {
  for (const difference of differences) {
    process.stderr.write(`\n--- ${difference.command}\n`);
    process.stderr.write(`reference:\n${difference.expected}\n`);
    process.stderr.write(`candidate:\n${difference.actual}\n`);
  }
  process.stderr.write(`\n${differences.length} of ${commands.length} commands differ\n`);
  process.exit(1);
}

console.log(`DIFFERENTIAL:passed (${commands.length} commands identical)`);
