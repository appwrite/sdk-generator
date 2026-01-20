import * as path from "path";
import { Command } from "commander";
import { ConfigType } from "./config.js";
import { localConfig } from "../config.js";
import { success, error, log, warn, actionRunner } from "../parser.js";
import {
  createGeneratorFromDetection,
  createGenerator,
  getSupportedLanguages,
  LanguageDetector,
  SupportedLanguage,
} from "./generators/index.js";
import {
  SDK_TITLE,
  SDK_TITLE_LOWER,
  EXECUTABLE_NAME,
  NPM_PACKAGE_NAME,
} from "../constants.js";

export interface GenerateCommandOptions {
  output: string;
  language?: string;
}

const generateAction = async (
  options: GenerateCommandOptions,
): Promise<void> => {
  const project = localConfig.getProject();

  if (!project.projectId) {
    error(
      `No project found. Please run '${EXECUTABLE_NAME} init project' first.`,
    );
    process.exit(1);
  }

  // Determine the generator to use
  let generator;
  let detectedLanguage: string;

  if (options.language) {
    // User explicitly specified a language
    if (!LanguageDetector.isSupported(options.language)) {
      const supported = getSupportedLanguages().join(", ");
      error(
        `Unsupported language: ${options.language}. Supported languages: ${supported}`,
      );
      process.exit(1);
    }
    generator = createGenerator(options.language as SupportedLanguage);
    detectedLanguage = options.language;
    log(`Using specified language: ${detectedLanguage}`);
  } else {
    // Auto-detect language
    try {
      const { generator: detectedGenerator, detection } =
        createGeneratorFromDetection();
      generator = detectedGenerator;
      detectedLanguage = detection.language;

      if (detection.confidence === "low") {
        warn(
          `Detected language '${detectedLanguage}' with low confidence (${detection.reason}). ` +
            `Use --language to specify explicitly.`,
        );
      } else {
        log(`Detected language: ${detectedLanguage} (${detection.reason})`);
      }
    } catch (err: any) {
      const supported = getSupportedLanguages().join(", ");
      error(
        `${err.message}\nUse --language to specify the target language. Supported: ${supported}`,
      );
      process.exit(1);
    }
  }

  const config: ConfigType = {
    projectId: project.projectId,
    projectName: project.projectName,
    tablesDB: localConfig.getTablesDBs(),
    tables: localConfig.getTables(),
    databases: localConfig.getDatabases(),
    collections: localConfig.getCollections(),
  };

  const outputDir = options.output;
  const absoluteOutputDir = path.isAbsolute(outputDir)
    ? outputDir
    : path.join(process.cwd(), outputDir);

  log(
    `Generating type-safe ${detectedLanguage} SDK to ${absoluteOutputDir}...`,
  );

  try {
    const result = await generator.generate(config);
    await generator.writeFiles(absoluteOutputDir, result);

    const generatedFiles = generator.getGeneratedFilePaths(result);
    success(`Generated files:`);
    for (const file of generatedFiles) {
      console.log(`  - ${path.join(outputDir, file)}`);
    }

    // Show language-specific usage instructions
    if (detectedLanguage === "typescript") {
      console.log("");
      log(`Import the generated SDK in your project:`);
      console.log(
        `  import { createDatabases } from "./${outputDir}/${SDK_TITLE_LOWER}/index.js";`,
      );
      console.log("");
      log(`Usage:`);
      console.log(`  import { Client } from '${NPM_PACKAGE_NAME}';`);
      console.log(
        `  const client = new Client().setEndpoint('...').setProject('...').setKey('...');`,
      );
      console.log(`  const databases = createDatabases(client);`);
      console.log(`  const db = databases.from('your-database-id');`);
      console.log(`  await db.tableName.create({ ... });`);
    }
  } catch (err: any) {
    error(`Failed to generate SDK: ${err.message}`);
    process.exit(1);
  }
};

export const generate = new Command("generate")
  .description(
    `Generate a type-safe SDK from your ${SDK_TITLE} project configuration`,
  )
  .option(
    "-o, --output <directory>",
    "Output directory for generated files (default: generated)",
    "generated",
  )
  .option(
    "-l, --language <language>",
    `Target language for SDK generation (supported: ${getSupportedLanguages().join(", ")})`,
  )
  .action(actionRunner(generateAction));
