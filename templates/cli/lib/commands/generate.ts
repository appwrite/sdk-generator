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
import { globalConfig } from "../config.js";
import {
  SDK_TITLE,
  SDK_TITLE_LOWER,
  EXECUTABLE_NAME,
  DEFAULT_ENDPOINT,
} from "../constants.js";
import {
  getAppwriteDependency,
  supportsServerSideMethods,
} from "../shared/typescript-type-utils.js";

type ServerSideOverride = "auto" | "true" | "false";

export interface GenerateCommandOptions {
  output: string;
  language?: string;
  serverSide?: ServerSideOverride;
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

  const serverSideOverride: ServerSideOverride = options.serverSide ?? "auto";
  if (!["auto", "true", "false"].includes(serverSideOverride)) {
    error(`Invalid --server-side value: ${serverSideOverride}`);
    process.exit(1);
  }

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

  if (typeof (generator as any).setServerSideOverride === "function") {
    (generator as any).setServerSideOverride(serverSideOverride);
  }

  const config: ConfigType = {
    projectId: project.projectId,
    projectName: project.projectName,
    endpoint:
      localConfig.getEndpoint() ||
      globalConfig.getEndpoint() ||
      DEFAULT_ENDPOINT,
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
      const entities = config.tables?.length
        ? config.tables
        : config.collections;
      const firstEntity = entities?.[0];
      const dbId = firstEntity?.databaseId ?? "databaseId";
      const tableName = firstEntity?.name ?? "tableName";
      const formatAccessor = (value: string): string =>
        /^[A-Za-z_$][A-Za-z0-9_$]*$/.test(value)
          ? `.${value}`
          : `[${JSON.stringify(value)}]`;
      const dbAccessor = formatAccessor(dbId);
      const tableAccessor = formatAccessor(tableName);

      console.log("");
      log(`Import the generated SDK in your project:`);
      console.log(
        `  import { databases } from "./${outputDir}/${SDK_TITLE_LOWER}/index.js";`,
      );
      console.log("");
      log(`Configure your client constants:`);
      console.log(
        `  set values in ./${outputDir}/${SDK_TITLE_LOWER}/constants.ts`,
      );
      console.log("");
      const appwriteDep = getAppwriteDependency();
      const supportsServerSide = supportsServerSideMethods(
        appwriteDep,
        serverSideOverride,
      );

      log(`Usage:`);
      if (supportsServerSide) {
        console.log(`  const mydb = databases.use(${JSON.stringify(dbId)});`);
        console.log(
          `  await mydb.use(${JSON.stringify(tableName)}).create({ ... });`,
        );
      } else {
        console.log(`  const mydb = databases${dbAccessor};`);
        console.log(`  await mydb${tableAccessor}.create({ ... });`);
      }
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
  .option(
    "--server-side <mode>",
    "Override server-side generation (auto|true|false)",
    "auto",
  )
  .action(actionRunner(generateAction));
