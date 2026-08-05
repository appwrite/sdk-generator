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

type LanguageFactory = () => LanguageMeta;

/**
 * The emitters `types` can generate for, keyed by the `-l` value that selects
 * one. This table is the only list: the accepted flag values, the rejection
 * message and the alias suggestions are all read from it, so a language cannot
 * be half-added.
 */
const languages = {
  ts: () => new TypeScript(),
  js: () => new JavaScript(),
  php: () => new PHP(),
  kotlin: () => new Kotlin(),
  swift: () => new Swift(),
  java: () => new Java(),
  dart: () => new Dart(),
  cs: () => new CSharp(),
} satisfies Record<string, LanguageFactory>;

type SupportedLanguage = keyof typeof languages;

/**
 * Names a user is likely to reach for, mapped to the value the CLI accepts.
 *
 * `dotnet` is not a guess: `detectLanguage` returns it for a directory holding
 * a .csproj, so `types` in a C# project resolves to a value the table has no
 * key for, and the answer is always `cs`.
 */
const languageAliases: Record<string, SupportedLanguage> = {
  "c#": "cs",
  csharp: "cs",
  dotnet: "cs",
  javascript: "js",
  node: "js",
  nodejs: "js",
  typescript: "ts",
};

/**
 * The accepted `-l` values, minus `auto` — a mode rather than a language, and
 * so not an answer to "pass one of these instead".
 */
function languageNames(): string {
  return Object.keys(languages).join(", ");
}

/**
 * Explains a language nothing generates for.
 *
 * The list is the useful half of the message. The accepted values are short
 * names, several of which are not the language's usual spelling, so a rejection
 * naming none of them leaves the user guessing at eight possibilities — which
 * is why `typescript` gets a suggestion rather than only a list.
 *
 * Detection reaches this too, and there the list is the only thing that says
 * what to do next: a Python or Ruby project resolves to a language the CLI has
 * no emitter for, so no spelling of it would have worked.
 */
function unsupportedLanguage(requested: string): Error {
  const suggestion = languageAliases[requested.toLowerCase()];

  if (suggestion) {
    return new Error(
      `Language '${requested}' is not supported -- did you mean '${suggestion}'? The supported languages are ${languageNames()}`,
    );
  }

  return new Error(
    `Language '${requested}' is not supported. The supported languages are ${languageNames()}`,
  );
}

/**
 * Picks the emitter, detecting from the project when the user passed `auto`.
 */
function resolveLanguage(requested: string): {
  language: string;
  meta: LanguageMeta;
} {
  let language = requested;

  if (language === "auto") {
    try {
      language = detectLanguage();
    } catch (err) {
      // detectLanguage's own message is shared with anything else that calls
      // it; the list of what to pass instead is added here, where it is known.
      throw new Error(
        `${(err as Error).message}. The supported languages are ${languageNames()}`,
      );
    }

    log(`Detected language: ${language}`);
  }

  const create = languages[language as SupportedLanguage];

  if (!create) {
    throw unsupportedLanguage(language);
  }

  return { language, meta: create() };
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
  .choices(["auto", ...Object.keys(languages)])
  .default("auto");

const typesStrictOption = new Option(
  "-s, --strict",
  "Enable strict mode to automatically convert field names to follow language conventions",
).default(false);

interface TypesOptions {
  language: string;
  strict: boolean;
}

type TypeAttribute = Record<string, unknown> & {
  relatedTable?: string;
};

type TypeDataItem = Record<string, unknown> & {
  name: string;
  attributes?: TypeAttribute[];
  columns?: TypeAttribute[];
};

const typesCommand = actionRunner(
  async (rawOutputDirectory: string, { language, strict }: TypesOptions) => {
    const { language: resolved, meta } = resolveLanguage(language);

    if (strict) {
      warn(
        `Strict mode enabled: Field names will be converted to follow ${resolved} conventions`,
      );
    }

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
    const tables = localConfig.getTables();
    let collections: TypeDataItem[] = [];

    if (tables.length === 0) {
      collections = localConfig.getCollections();

      if (collections.length === 0) {
        const configFileName = path.basename(localConfig.path);
        throw new Error(
          `No tables or collections found in configuration. Make sure ${configFileName} exists and contains tables or collections.`,
        );
      }
    }

    // Use tables if available, otherwise use collections
    let dataItems: TypeDataItem[] =
      tables.length > 0 ? (tables as TypeDataItem[]) : collections;
    const itemType = tables.length > 0 ? "tables" : "collections";

    // Normalize tables data: rename 'columns' to 'attributes' for template compatibility
    if (tables.length > 0) {
      dataItems = dataItems.map((table) => {
        const { columns, ...rest } = table;
        return {
          ...rest,
          attributes: (columns || []).map((column: TypeAttribute) => {
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
      `Found ${dataItems.length} ${itemType}: ${dataItems.map((c) => c.name).join(", ")}`,
    );

    // Use columns if available, otherwise use attributes
    const resourceType = tables.length > 0 ? "columns" : "attributes";

    const totalAttributes = dataItems.reduce(
      (count: number, item: TypeDataItem) =>
        count + (item.attributes || []).length,
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
