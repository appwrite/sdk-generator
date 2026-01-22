import ejs from "ejs";
import fs from "fs";
import path from "path";
import {
  LanguageMeta,
  detectLanguage,
  Collection,
} from "../type-generation/languages/language.js";
import { Command, Option, Argument } from "commander";
import { localConfig } from "../config.js";
import { success, log, warn, actionRunner } from "../parser.js";
import { PHP } from "../type-generation/languages/php.js";
import { TypeScript } from "../type-generation/languages/typescript.js";
import { Kotlin } from "../type-generation/languages/kotlin.js";
import { Swift } from "../type-generation/languages/swift.js";
import { Java } from "../type-generation/languages/java.js";
import { Dart } from "../type-generation/languages/dart.js";
import { JavaScript } from "../type-generation/languages/javascript.js";
import { CSharp } from "../type-generation/languages/csharp.js";

type SupportedLanguage =
  | "ts"
  | "js"
  | "php"
  | "kotlin"
  | "swift"
  | "java"
  | "dart"
  | "cs";

function createLanguageMeta(language: SupportedLanguage): LanguageMeta {
  switch (language) {
    case "ts":
      return new TypeScript();
    case "js":
      return new JavaScript();
    case "php":
      return new PHP();
    case "kotlin":
      return new Kotlin();
    case "swift":
      return new Swift();
    case "java":
      return new Java();
    case "dart":
      return new Dart();
    case "cs":
      return new CSharp();
    default:
      throw new Error(`Language '${language}' is not supported`);
  }
}

const templateHelpers = {
  toPascalCase: LanguageMeta.toPascalCase,
  toCamelCase: LanguageMeta.toCamelCase,
  toSnakeCase: LanguageMeta.toSnakeCase,
  toKebabCase: LanguageMeta.toKebabCase,
  toUpperSnakeCase: LanguageMeta.toUpperSnakeCase,
  getRelatedCollection: LanguageMeta.getRelatedCollection,
  getRelatedCollectionId: LanguageMeta.getRelatedCollectionId,
};

const typesOutputArgument = new Argument(
  "<output-directory>",
  "The directory to write the types to",
);

const typesLanguageOption = new Option(
  "-l, --language <language>",
  "The language of the types",
)
  .choices(["auto", "ts", "js", "php", "kotlin", "swift", "java", "dart", "cs"])
  .default("auto");

const typesStrictOption = new Option(
  "-s, --strict",
  "Enable strict mode to automatically convert field names to follow language conventions",
).default(false);

interface TypesOptions {
  language: string;
  strict: boolean;
}

const typesCommand = actionRunner(
  async (rawOutputDirectory: string, { language, strict }: TypesOptions) => {
    if (language === "auto") {
      language = detectLanguage();
      log(`Detected language: ${language}`);
    }

    if (strict) {
      warn(
        `Strict mode enabled: Field names will be converted to follow ${language} conventions`,
      );
    }

    const meta = createLanguageMeta(language as SupportedLanguage);
    const templatingHelpers = {
      ...templateHelpers,
      generateEnum: meta.generateEnum.bind(meta),
    };

    const rawOutputPath = rawOutputDirectory;
    const outputExt = path.extname(rawOutputPath);
    const isFileOutput = !!outputExt;
    let outputDirectory = rawOutputPath;
    let singleFileDestination: string | null = null;

    if (isFileOutput) {
      if (meta.isSingleFile()) {
        // Use the file path directly for single file languages
        outputDirectory = path.dirname(rawOutputPath);
        singleFileDestination = rawOutputPath;
      } else {
        throw new Error(
          `Invalid output path: ${rawOutputPath}. Output path must be a directory for languages that generate multiple files.`,
        );
      }
    }

    if (!fs.existsSync(outputDirectory)) {
      log(`Directory: ${outputDirectory} does not exist, creating...`);
      fs.mkdirSync(outputDirectory, { recursive: true });
    }

    // Try tables first, fallback to collections
    let tables = localConfig.getTables();
    let collections: any[] = [];
    let dataSource = "tables";

    if (tables.length === 0) {
      collections = localConfig.getCollections();
      dataSource = "collections";

      if (collections.length === 0) {
        const configFileName = path.basename(localConfig.path);
        throw new Error(
          `No tables or collections found in configuration. Make sure ${configFileName} exists and contains tables or collections.`,
        );
      }
    }

    // Use tables if available, otherwise use collections
    let dataItems: any[] = tables.length > 0 ? tables : collections;
    const itemType = tables.length > 0 ? "tables" : "collections";

    // Normalize tables data: rename 'columns' to 'attributes' for template compatibility
    if (tables.length > 0) {
      dataItems = dataItems.map((table) => {
        const { columns, ...rest } = table;
        return {
          ...rest,
          attributes: (columns || []).map((column: any) => {
            if (column.relatedTable) {
              const { relatedTable, ...columnRest } = column;
              return {
                ...columnRest,
                relatedCollection: relatedTable,
              };
            }
            return column;
          }),
        };
      });
    }

    log(
      `Found ${dataItems.length} ${itemType}: ${dataItems.map((c: any) => c.name).join(", ")}`,
    );

    // Use columns if available, otherwise use attributes
    const resourceType = tables.length > 0 ? "columns" : "attributes";

    const totalAttributes = dataItems.reduce(
      (count: number, item: any) => count + (item.attributes || []).length,
      0,
    );
    log(`Found ${totalAttributes} ${resourceType} across all ${itemType}`);

    const templater = ejs.compile(meta.getTemplate());

    if (meta.isSingleFile()) {
      const content = templater({
        collections: dataItems,
        strict,
        ...templatingHelpers,
        getType: meta.getType.bind(meta),
      });

      const destination =
        singleFileDestination || path.join(outputDirectory, meta.getFileName());

      fs.writeFileSync(destination, content);
      log(`Added types to ${destination}`);
    } else {
      for (const item of dataItems) {
        const content = templater({
          collections: dataItems,
          collection: item,
          strict,
          ...templatingHelpers,
          getType: meta.getType.bind(meta),
        });

        const destination = path.join(
          outputDirectory,
          meta.getFileName(item as Collection),
        );

        fs.writeFileSync(destination, content);
        log(`Added types for ${item.name} to ${destination}`);
      }
    }

    success(`Generated types for all the listed ${itemType}`);
  },
);

export const types = new Command("types")
  .description("Generate types for your Appwrite project")
  .addArgument(typesOutputArgument)
  .addOption(typesLanguageOption)
  .addOption(typesStrictOption)
  .action(typesCommand);
