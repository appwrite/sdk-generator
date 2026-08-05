// Recapture the ignore-matcher baselines in
// templates/go-cli/internal/ignore/testdata/cases.json.
//
// internal/ignore ports the `ignore` npm package, which decides which files
// reach a function build and a deployment. Getting it wrong ships a secret or
// drops a source file, so it is pinned to what the package actually does rather
// than to a reading of gitignore's documentation.
//
// Run it from a generated CLI, which is where `ignore` is installed:
//
//     php example.php cli
//     cp scripts/go-cli/capture-ignore-baselines.ts examples/cli/
//     cd examples/cli && bun run capture-ignore-baselines.ts \
//         ../../templates/go-cli/internal/ignore/testdata/cases.json
//     rm examples/cli/capture-ignore-baselines.ts
//
// The file it writes holds the expected verdicts; the Go test only reads them.
// To add coverage, add a group below and recapture -- do not hand-edit the JSON.
import fs from "fs";
import ignoreModule from "ignore";

const ignore: typeof ignoreModule =
  (ignoreModule as unknown as { default?: typeof ignoreModule }).default ??
  ignoreModule;

const destination = process.argv[2];
if (!destination) {
  throw new Error("usage: capture-ignore-baselines.ts <cases.json>");
}

// Each group is one set of patterns tested against one set of paths. The paths
// are deliberately shared across groups where possible, so a diff between two
// groups isolates the pattern rather than the input.
const groups: Array<{ name: string; patterns: string[]; paths: string[] }> = [
  {
    name: "the CLI's own defaults",
    patterns: [".appwrite", "code.tar.gz"],
    paths: [
      ".appwrite",
      ".appwrite/build.tar.gz",
      "src/.appwrite/x",
      "code.tar.gz",
      "src/code.tar.gz",
      "codeXtar.gz",
      "src/index.js",
    ],
  },
  {
    name: "a representative .gitignore",
    patterns: [
      "node_modules",
      "*.log",
      "!important.log",
      "build/",
      "/root-only.txt",
      "dist/**",
      "**/generated",
      ".env*",
      "!.env.example",
    ],
    paths: [
      "node_modules",
      "node_modules/pkg/index.js",
      "src/node_modules/pkg/index.js",
      "debug.log",
      "logs/debug.log",
      "important.log",
      "logs/important.log",
      "build",
      "build/out.js",
      "src/build/out.js",
      "root-only.txt",
      "nested/root-only.txt",
      "dist/a/b.js",
      "dist",
      "generated",
      "a/generated",
      "a/generated/file.ts",
      ".env",
      ".env.local",
      ".env.example",
      "config/.env",
      "src/index.js",
    ],
  },
  {
    name: "anchoring and depth",
    patterns: ["a/b", "/c", "d/", "e/**/f"],
    paths: [
      "a/b",
      "a/b/c",
      "x/a/b",
      "c",
      "c/d",
      "x/c",
      "d",
      "d/e",
      "x/d/e",
      "e/f",
      "e/x/f",
      "e/x/y/f",
      "x/e/f",
    ],
  },
  {
    name: "character classes, escapes and negation order",
    patterns: [
      "file?.txt",
      "[abc].md",
      "[!x].bin",
      "temp/",
      "!temp/keep.txt",
      "\\#literal",
      "trailing   ",
    ],
    paths: [
      "file1.txt",
      "file10.txt",
      "file.txt",
      "a.md",
      "x.md",
      "a.bin",
      "x.bin",
      "temp/drop.txt",
      "temp/keep.txt",
      "#literal",
      "literal",
      "trailing",
      "trailing   ",
    ],
  },
  {
    name: "comments and blanks are not patterns",
    patterns: ["# a comment", "", "   ", "real.txt"],
    paths: ["a comment", "# a comment", "real.txt", "other.txt"],
  },
];

// Every pattern below is also tested ALONE against every path below, as its
// own group. The combined groups above check rule interaction; this checks the
// translation of each pattern in isolation, which is where a glob-to-regexp
// bug hides. One pattern per group keeps a failure pointing at one pattern.
const singlePatterns = [
  "a",
  "a/",
  "/a",
  "a/b",
  "a/b/",
  "*.txt",
  "a*",
  "*a",
  "a?c",
  "**",
  "**/a",
  "a/**",
  "a/**/b",
  "**/a/**",
  ".*",
  "[ab]c",
  "[!ab]c",
  "[a-c]d",
  "a\\*b",
  "a b",
  "!a",
  "src/*.ts",
  "*/a",
  "a/*/b",
  "node_modules/",
];

const singlePaths = [
  "a",
  "a/b",
  "a/b/c",
  "b/a",
  "b/a/c",
  "x/y/a",
  "ac",
  "abc",
  "a*b",
  "a b",
  "a.txt",
  "b.txt",
  "a/b.txt",
  "src/a.ts",
  "src/nested/a.ts",
  ".hidden",
  "a/.hidden",
  "bc",
  "cc",
  "ad",
  "bd",
  "dd",
  "node_modules",
  "node_modules/pkg",
  "a/node_modules/pkg",
  // No empty path: `ignore` throws on one ("path must not be empty"). The Go
  // port returns false instead, since every caller feeds it a walk result and
  // crashing there would be worse than a verdict nobody asks for.
];

for (const pattern of singlePatterns) {
  groups.push({
    name: `single pattern: ${JSON.stringify(pattern)}`,
    patterns: [pattern],
    paths: singlePaths,
  });
}

const cases = groups.map((group) => {
  const matcher = ignore().add(group.patterns);

  return {
    name: group.name,
    patterns: group.patterns,
    // Recorded as verdicts rather than a filtered list so a path that flips in
    // either direction shows up, not just one that disappears.
    expected: Object.fromEntries(
      group.paths.map((path) => [path, matcher.ignores(path)]),
    ),
  };
});

fs.writeFileSync(destination, JSON.stringify(cases, null, 2) + "\n");
console.log(
  `captured ${cases.length} groups, ` +
    `${cases.reduce((n, c) => n + Object.keys(c.expected).length, 0)} verdicts, ` +
    `into ${destination}`,
);
