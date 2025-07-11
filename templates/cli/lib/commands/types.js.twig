const ejs = require("ejs");
const fs = require("fs");
const path = require("path");
const { LanguageMeta, detectLanguage } = require("../type-generation/languages/language");
const { Command, Option, Argument } = require("commander");
const { localConfig } = require("../config");
const { success, log, actionRunner } = require("../parser");
const { PHP } = require("../type-generation/languages/php");
const { TypeScript } = require("../type-generation/languages/typescript");
const { Kotlin } = require("../type-generation/languages/kotlin");
const { Swift } = require("../type-generation/languages/swift");
const { Java } = require("../type-generation/languages/java");
const { Dart } = require("../type-generation/languages/dart");
const { JavaScript } = require("../type-generation/languages/javascript");

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
  .choices(["auto", "ts", "js", "php", "kotlin", "swift", "java", "dart"])
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
    log(`Strict mode enabled: Field names will be converted to follow ${language} conventions`);
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

  if (!fs.existsSync("appwrite.json")) {
    throw new Error("appwrite.json not found in current directory");
  }

  const collections = localConfig.getCollections();
  if (collections.length === 0) {
    throw new Error("No collections found in appwrite.json");
  }

  log(`Found ${collections.length} collections: ${collections.map(c => c.name).join(", ")}`);

  const totalAttributes = collections.reduce((count, collection) => count + collection.attributes.length, 0);
  log(`Found ${totalAttributes} attributes across all collections`);

  const templater = ejs.compile(meta.getTemplate());

  if (meta.isSingleFile()) {
    const content = templater({
      collections,
      strict,
      ...templateHelpers,
      getType: meta.getType
    });

    const destination = singleFileDestination || path.join(outputDirectory, meta.getFileName());

    fs.writeFileSync(destination, content);
    log(`Added types to ${destination}`);
  } else {
    for (const collection of collections) {
      const content = templater({
        collection,
        strict,
        ...templateHelpers,
        getType: meta.getType
      });
  
      const destination = path.join(outputDirectory, meta.getFileName(collection));
  
      fs.writeFileSync(destination, content);
      log(`Added types for ${collection.name} to ${destination}`);
    }
  }
  
  success(`Generated types for all the listed collections`);
});

const types = new Command("types")
  .description("Generate types for your Appwrite project")
  .addArgument(typesOutputArgument)
  .addOption(typesLanguageOption)
  .addOption(typesStrictOption)
  .action(actionRunner(typesCommand));

module.exports = { types };
