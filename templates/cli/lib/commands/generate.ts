import * as path from "path";
import { Command } from "commander";
import { ConfigType } from "./config.js";
import { localConfig } from "../config.js";
import { success, error, log, actionRunner } from "../parser.js";
import { DatabasesGenerator } from "./generators/databases.js";
import { SDK_TITLE, SDK_TITLE_LOWER, EXECUTABLE_NAME, NPM_PACKAGE_NAME } from "../constants.js";

export interface GenerateCommandOptions {
  output: string;
}

const generateAction = async (
  options: GenerateCommandOptions,
): Promise<void> => {
  const generator = new DatabasesGenerator();
  const project = localConfig.getProject();

  if (!project.projectId) {
    error(`No project found. Please run '${EXECUTABLE_NAME} init project' first.`);
    process.exit(1);
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

  log(`Generating type-safe SDK to ${absoluteOutputDir}...`);

  try {
    const result = await generator.generate(config);
    await generator.writeFiles(absoluteOutputDir, result);

    success(`Generated files:`);
    console.log(`  - ${path.join(outputDir, `${SDK_TITLE_LOWER}/index.ts`)}`);
    console.log(`  - ${path.join(outputDir, `${SDK_TITLE_LOWER}/databases.ts`)}`);
    console.log(`  - ${path.join(outputDir, `${SDK_TITLE_LOWER}/types.ts`)}`);
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
  .action(actionRunner(generateAction));
