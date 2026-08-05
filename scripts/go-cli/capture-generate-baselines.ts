// Recapture the `generate` baselines in templates/go-cli/internal/generator/testdata.
//
// Companion to capture-typegen-baselines.ts, same discipline: the Go port of
// `appwrite generate` is pinned to what TypeScriptDatabasesGenerator actually
// produces. See docs/go-cli/README.md section 2.6.
//
// Run it from a generated CLI, which is where handlebars and the compiled
// generator live:
//
//     php example.php cli
//     cp docs/go-cli/capture-generate-baselines.ts examples/cli/
//     cd examples/cli && bun run capture-generate-baselines.ts \
//         ../../templates/go-cli/internal/generator/testdata
//     rm examples/cli/capture-generate-baselines.ts
//
// Then re-run `go test ./internal/generator/...` in examples/go-cli.
import fs from "fs";
import path from "path";
import { TypeScriptDatabasesGenerator } from "./lib/commands/generators/typescript/databases.js";

const testdata = process.argv[2];
if (!testdata) {
  throw new Error("usage: capture-generate-baselines.ts <testdata-directory>");
}

const config = JSON.parse(
  fs.readFileSync(path.join(testdata, "config.json"), "utf-8"),
);

// Each case pins one server-side decision. `auto` is not captured: it resolves
// from whatever package.json happens to sit in the working directory, which is
// exactly the ambiguity a baseline must not carry.
const cases: Array<{
  name: string;
  serverSide: "true" | "false";
  importSource: string;
  importExtension: string;
}> = [
  {
    name: "server",
    serverSide: "true",
    importSource: "node-appwrite",
    importExtension: ".js",
  },
  {
    name: "client",
    serverSide: "false",
    importSource: "appwrite",
    importExtension: "",
  },
];

const written: string[] = [];

for (const testCase of cases) {
  const generator = new TypeScriptDatabasesGenerator();
  generator.setServerSideOverride(testCase.serverSide);

  const result = await generator.generate(config, {
    appwriteImportSource: testCase.importSource,
    importExtension: testCase.importExtension,
  });

  const files: Record<string, string> = {
    "databases.ts": result.dbContent,
    "types.ts": result.typesContent,
    "index.ts": result.indexContent,
    "constants.ts": result.constantsContent,
  };

  for (const [file, content] of Object.entries(files)) {
    const name = `${testCase.name}.${file}`;
    fs.writeFileSync(path.join(testdata, name), content);
    written.push(name);
  }
}

// An empty config exercises the no-entities path, which returns a notice in
// place of both generated files.
const emptyGenerator = new TypeScriptDatabasesGenerator();
emptyGenerator.setServerSideOverride("true");
const empty = await emptyGenerator.generate(
  { projectId: config.projectId, endpoint: config.endpoint } as never,
  { appwriteImportSource: "node-appwrite", importExtension: ".js" },
);
for (const [file, content] of Object.entries({
  "databases.ts": empty.dbContent,
  "types.ts": empty.typesContent,
  "index.ts": empty.indexContent,
  "constants.ts": empty.constantsContent,
})) {
  const name = `empty.${file}`;
  fs.writeFileSync(path.join(testdata, name), content);
  written.push(name);
}

console.log(`captured ${written.length} baselines into ${testdata}`);
