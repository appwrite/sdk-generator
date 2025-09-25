const ejs = require("ejs");
const fs = require("fs");
const path = require("path");
const { LanguageMeta, detectLanguage } = require("../type-generation/languages/language");
const { Command, Option, Argument } = require("commander");
const { localConfig } = require("../config");
const { success, log, warn, actionRunner } = require("../parser");
const { PHP } = require("../type-generation/languages/php");
const { TypeScript } = require("../type-generation/languages/typescript");
const { Kotlin } = require("../type-generation/languages/kotlin");
const { Swift } = require("../type-generation/languages/swift");
const { Java } = require("../type-generation/languages/java");
const { Dart } = require("../type-generation/languages/dart");
const { JavaScript } = require("../type-generation/languages/javascript");
const { CSharp } = require("../type-generation/languages/csharp");

/**
 * @param {string} language
 * @returns {import("../type-generation/languages/language").LanguageMeta}
 */
function createLanguageMeta(language) {
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
  toUpperSnakeCase: LanguageMeta.toUpperSnakeCase
}

const typesOutputArgument = new Argument(
  "<output-directory>",
  "The directory to write the types to"
);

const typesLanguageOption = new Option(
  "-l, --language <language>",
  "The language of the types"
)
  .choices(["auto", "ts", "js", "php", "kotlin", "swift", "java", "dart", "cs"])
  .default("auto");

const typesStrictOption = new Option(
  "-s, --strict",
  "Enable strict mode to automatically convert field names to follow language conventions"
)
  .default(false);

const typesCommand = actionRunner(async (rawOutputDirectory, {language, strict}) => {
  if (language === "auto") {
    language = detectLanguage();
    log(`Detected language: ${language}`);
  }

  if (strict) {
    warn(`Strict mode enabled: Field names will be converted to follow ${language} conventions`);
  }

  const meta = createLanguageMeta(language);

  const rawOutputPath = rawOutputDirectory;
  const outputExt = path.extname(rawOutputPath);
  const isFileOutput = !!outputExt;
  let outputDirectory = rawOutputPath;
  let singleFileDestination = null;

  if (isFileOutput) {
    if (meta.isSingleFile()) {
      // Use the file path directly for single file languages
      outputDirectory = path.dirname(rawOutputPath);
      singleFileDestination = rawOutputPath;
    } else {
      throw new Error(`Invalid output path: ${rawOutputPath}. Output path must be a directory for languages that generate multiple files.`);
    }
  }

  if (!fs.existsSync(outputDirectory)) {
    log(`Directory: ${outputDirectory} does not exist, creating...`);
    fs.mkdirSync(outputDirectory, { recursive: true });
  }

  // Try tables first, fallback to collections
  let tables = localConfig.getTables();
  let collections = [];
  let dataSource = 'tables';
  
  if (tables.length === 0) {
    collections = localConfig.getCollections();
    dataSource = 'collections';
    
    if (collections.length === 0) {
      const configFileName = path.basename(localConfig.path);
      throw new Error(`No tables or collections found in configuration. Make sure ${configFileName} exists and contains tables or collections.`);
    }
  }

  // Use tables if available, otherwise use collections
  let dataItems = tables.length > 0 ? tables : collections;
  const itemType = tables.length > 0 ? 'tables' : 'collections';

  // Normalize tables data: rename 'columns' to 'attributes' for template compatibility
  if (tables.length > 0) {
    dataItems = dataItems.map(table => {
      const { columns, ...rest } = table;
      return {
        ...rest,
        attributes: (columns || []).map(column => {
          if (column.relatedTable) {
            const { relatedTable, ...columnRest } = column;
            return {
              ...columnRest,
              relatedCollection: relatedTable
            };
          }
          return column;
        })
      };
    });
  }

  log(`Found ${dataItems.length} ${itemType}: ${dataItems.map(c => c.name).join(", ")}`);

  // Use columns if available, otherwise use attributes
  const resourceType = tables.length > 0 ? 'columns' : 'attributes';

  const totalAttributes = dataItems.reduce((count, item) => count + (item.attributes || []).length, 0);
  log(`Found ${totalAttributes} ${resourceType} across all ${itemType}`);

  const templater = ejs.compile(meta.getTemplate());

  if (meta.isSingleFile()) {
    const content = templater({
      collections: dataItems,
      strict,
      ...templateHelpers,
      getType: meta.getType,
    });

    const destination = singleFileDestination || path.join(outputDirectory, meta.getFileName());

    fs.writeFileSync(destination, content);
    log(`Added types to ${destination}`);
  } else {
    for (const item of dataItems) {
      const content = templater({
        collections: dataItems,
        collection: item,
        strict,
        ...templateHelpers,
        getType: meta.getType,
      });
  
      const destination = path.join(outputDirectory, meta.getFileName(item));
  
      fs.writeFileSync(destination, content);
      log(`Added types for ${item.name} to ${destination}`);
    }
  }
  
  success(`Generated types for all the listed ${itemType}`);
});

const types = new Command("types")
  .description("Generate types for your Appwrite project")
  .addArgument(typesOutputArgument)
  .addOption(typesLanguageOption)
  .addOption(typesStrictOption)
  .action(actionRunner(typesCommand));

module.exports = { types };
