// Recapture the typegen baselines in templates/go-cli/internal/typegen/testdata.
//
// The Go port of `appwrite types` is pinned to what the TypeScript emitters
// actually produce, not to what their EJS templates look like -- see
// docs/go-cli/README.md section 2.6. This is the script that produces the pins,
// kept in the repository so a baseline can be regenerated rather than
// hand-edited when the TypeScript changes.
//
// Run it from a generated CLI, which is where ejs and the compiled emitters
// live:
//
//     php example.php cli
//     cp docs/go-cli/capture-typegen-baselines.ts examples/cli/
//     cd examples/cli && bun run capture-typegen-baselines.ts \
//         ../../templates/go-cli/internal/typegen/testdata
//     rm examples/cli/capture-typegen-baselines.ts
//
// Then re-run `go test ./internal/typegen/...` in examples/go-cli. A diff means
// the TypeScript changed; decide whether the Go port should follow before
// committing the new baseline.
import ejs from "ejs";
import fs from "fs";
import path from "path";
import { LanguageMeta } from "./lib/type-generation/languages/language.js";
import { TypeScript } from "./lib/type-generation/languages/typescript.js";
import { JavaScript } from "./lib/type-generation/languages/javascript.js";
import { PHP } from "./lib/type-generation/languages/php.js";
import { Kotlin } from "./lib/type-generation/languages/kotlin.js";
import { Swift } from "./lib/type-generation/languages/swift.js";
import { Java } from "./lib/type-generation/languages/java.js";
import { Dart } from "./lib/type-generation/languages/dart.js";
import { CSharp } from "./lib/type-generation/languages/csharp.js";

const testdata = process.argv[2];
if (!testdata) {
  throw new Error("usage: capture-typegen-baselines.ts <testdata-directory>");
}

const collections = JSON.parse(
  fs.readFileSync(path.join(testdata, "collections.json"), "utf-8"),
);

// getTemplate() bakes `process.argv.slice(2).join(" ")` into the generated
// header. Pin it to the value language_test.go passes as baselineInvocation.
process.argv = [process.argv[0], process.argv[1], "types", "./types"];

const helpers = {
  toPascalCase: LanguageMeta.toPascalCase,
  toCamelCase: LanguageMeta.toCamelCase,
  toSnakeCase: LanguageMeta.toSnakeCase,
  toKebabCase: LanguageMeta.toKebabCase,
  toUpperSnakeCase: LanguageMeta.toUpperSnakeCase,
  getRelatedCollection: LanguageMeta.getRelatedCollection,
  getRelatedCollectionId: LanguageMeta.getRelatedCollectionId,
};

// Keys match the `-l` values `appwrite types` accepts, and the prefixes
// language_test.go looks for.
const languages: Record<string, any> = {
  ts: new TypeScript(),
  js: new JavaScript(),
  php: new PHP(),
  kotlin: new Kotlin(),
  swift: new Swift(),
  java: new Java(),
  dart: new Dart(),
  cs: new CSharp(),
};

const written: string[] = [];

for (const [id, meta] of Object.entries(languages)) {
  for (const strict of [false, true]) {
    const mode = strict ? "strict" : "loose";
    const templater = ejs.compile(meta.getTemplate());
    const locals = {
      ...helpers,
      generateEnum: meta.generateEnum.bind(meta),
      getType: meta.getType.bind(meta),
      collections,
      strict,
    };

    const emit = (name: string, content: string) => {
      fs.writeFileSync(path.join(testdata, name), content);
      written.push(name);
    };

    if (meta.isSingleFile()) {
      emit(`${id}.${mode}.${meta.getFileName()}`, templater(locals));
      continue;
    }

    for (const collection of collections) {
      emit(
        `${id}.${mode}.${meta.getFileName(collection)}`,
        templater({ ...locals, collection }),
      );
    }
  }
}

console.log(`captured ${written.length} baselines into ${testdata}`);
