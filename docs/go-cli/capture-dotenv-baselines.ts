// Recapture the .env parser baselines in
// templates/go-cli/internal/dotenv/testdata/cases.json.
//
// `run` reads a function's .env with dotenv 16.x and passes every pair into the
// container as an environment variable. A parse difference changes what the
// function sees at runtime, so the parser is pinned to the package rather than
// to its documentation. See README.md section 2.6.
//
// Run it from a generated CLI, which is where dotenv is installed:
//
//     php example.php cli
//     cp docs/go-cli/capture-dotenv-baselines.ts examples/cli/
//     cd examples/cli && bun run capture-dotenv-baselines.ts \
//         ../../templates/go-cli/internal/dotenv/testdata/cases.json
//     rm examples/cli/capture-dotenv-baselines.ts
//
// Add a case below and recapture -- do not hand-edit the JSON.
import fs from "fs";
import { parse } from "dotenv";

const destination = process.argv[2];
if (!destination) {
  throw new Error("usage: capture-dotenv-baselines.ts <cases.json>");
}

const inputs: Array<{ name: string; source: string }> = [
  { name: "plain pairs", source: "A=1\nB=two\nC=\n" },
  { name: "no trailing newline", source: "A=1" },
  { name: "crlf line endings", source: "A=1\r\nB=2\r\n" },
  {
    name: "surrounding whitespace",
    source: "  A  =  spaced  \nB=\ttabbed\t\n",
  },
  { name: "export prefix", source: "export A=1\nexport  B=2\n" },
  {
    name: "quotes",
    source: `A="double"\nB='single'\nC=\`backtick\`\nD="  padded  "\nE='  padded  '\n`,
  },
  {
    name: "escapes inside double quotes only",
    source: `A="line\\nbreak"\nB='line\\nbreak'\nC=line\\nbreak\n`,
  },
  {
    name: "multiline values",
    source: `A="first\nsecond"\nB='first\nsecond'\nC=\`first\nsecond\`\n`,
  },
  {
    name: "comments",
    source:
      "# leading comment\nA=1 # trailing\nB=2# no space\nC='quoted # hash'\nD=\"quoted # hash\"\n#full line\nE=3\n",
  },
  { name: "blank and malformed lines", source: "\n\nA=1\nnot a pair\n=novalue\nB=2\n" },
  {
    name: "values containing equals and urls",
    source: "A=a=b=c\nURL=https://cloud.appwrite.io/v1?x=1&y=2\n",
  },
  {
    name: "empty and quoted-empty",
    source: `A=\nB=""\nC=''\nD="   "\n`,
  },
  {
    name: "keys with dots, dashes and digits",
    source: "A.B=1\nA-B=2\nA_B=3\n_A=4\n1A=5\n",
  },
  {
    name: "duplicate keys keep the last",
    source: "A=first\nA=second\n",
  },
];

const cases = inputs.map(({ name, source }) => ({
  name,
  source,
  expected: parse(source),
}));

fs.writeFileSync(destination, JSON.stringify(cases, null, 2) + "\n");
console.log(`captured ${cases.length} cases into ${destination}`);
