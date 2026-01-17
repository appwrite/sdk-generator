import { ConfigType, AttributeSchema } from "./config.js";
import * as fs from "fs";
import * as path from "path";
import { z } from "zod";
import { Command } from "commander";
import { localConfig } from "../config.js";
import { success, error, log, actionRunner } from "../parser.js";

export interface GenerateResult {
  dbContent: string;
  typesContent: string;
}

export class Db {
  private getType(
    attribute: z.infer<typeof AttributeSchema>,
    collections: NonNullable<ConfigType["collections"]>,
  ): string {
    let type = "";

    switch (attribute.type) {
      case "string":
      case "datetime":
        type = "string";
        if (attribute.format === "enum") {
          type = this.toPascalCase(attribute.key);
        }
        break;
      case "integer":
        type = "number";
        break;
      case "double":
        type = "number";
        break;
      case "boolean":
        type = "boolean";
        break;
      case "relationship":
        const relatedCollection = collections.find(
          (c) => c.$id === attribute.relatedCollection,
        );
        if (!relatedCollection) {
          throw new Error(
            `Related collection with ID '${attribute.relatedCollection}' not found.`,
          );
        }
        type = this.toPascalCase(relatedCollection.name);
        if (
          (attribute.relationType === "oneToMany" &&
            attribute.side === "parent") ||
          (attribute.relationType === "manyToOne" &&
            attribute.side === "child") ||
          attribute.relationType === "manyToMany"
        ) {
          type = `${type}[]`;
        }
        break;
      default:
        throw new Error(`Unknown attribute type: ${attribute.type}`);
    }

    if (attribute.array) {
      type += "[]";
    }

    if (!attribute.required && attribute.default === null) {
      type += " | null";
    }

    return type;
  }

  private toPascalCase(str: string): string {
    return str
      .replace(/[-_\s]+(.)?/g, (_, char) => (char ? char.toUpperCase() : ""))
      .replace(/^(.)/, (char) => char.toUpperCase());
  }

  private toUpperSnakeCase(str: string): string {
    return str
      .replace(/([a-z])([A-Z])/g, "$1_$2")
      .replace(/[-\s]+/g, "_")
      .toUpperCase();
  }

  private generateTableType(
    entity: NonNullable<ConfigType["tables"]>[number] | NonNullable<ConfigType["collections"]>[number],
    entities: NonNullable<ConfigType["tables"]> | NonNullable<ConfigType["collections"]>,
  ): string {
    // Handle both tables (columns) and collections (attributes)
    const fields = "columns" in entity
      ? (entity as NonNullable<ConfigType["tables"]>[number]).columns
      : (entity as NonNullable<ConfigType["collections"]>[number]).attributes;

    if (!fields) {
      return "";
    }

    const typeName = this.toPascalCase(entity.name);
    const attributes = fields
      .map((attr: z.infer<typeof AttributeSchema>) => {
        return `    ${attr.key}: ${this.getType(attr, entities as any)};`;
      })
      .join("\n");

    return `export type ${typeName} = Models.Row & {\n${attributes}\n}`;
  }

  private generateEnums(
    entities: NonNullable<ConfigType["tables"]> | NonNullable<ConfigType["collections"]>,
  ): string {
    const enumTypes: string[] = [];

    for (const entity of entities) {
      // Handle both tables (columns) and collections (attributes)
      const fields = "columns" in entity
        ? (entity as NonNullable<ConfigType["tables"]>[number]).columns
        : (entity as NonNullable<ConfigType["collections"]>[number]).attributes;
      if (!fields) continue;

      for (const field of fields) {
        if (field.format === "enum" && field.elements) {
          const enumName = this.toPascalCase(field.key);
          const enumValues = field.elements
            .map((element: string, index: number) => {
              const key = this.toUpperSnakeCase(element);
              const isLast = index === field.elements!.length - 1;
              return `    ${key} = "${element}"${isLast ? "" : ","}`;
            })
            .join("\n");

          enumTypes.push(`export enum ${enumName} {\n${enumValues}\n}`);
        }
      }
    }

    return enumTypes.join("\n\n");
  }

  private generateTypesFile(config: ConfigType): string {
    // Use tables if available, fall back to collections
    const entities = config.tables?.length ? config.tables : config.collections;

    if (!entities || entities.length === 0) {
      return "// No tables or collections found in configuration\n";
    }

    const appwriteDep = this.getAppwriteDependency();
    const enums = this.generateEnums(entities);
    const types = entities
      .map((entity) => this.generateTableType(entity, entities))
      .join("\n\n");

    // Group entities by databaseId for DatabaseTables type
    const entitiesByDb = new Map<string, Array<(typeof entities)[number]>>();
    for (const entity of entities) {
      const dbId = entity.databaseId;
      if (!entitiesByDb.has(dbId)) {
        entitiesByDb.set(dbId, []);
      }
      entitiesByDb.get(dbId)!.push(entity);
    }

    // Generate DatabaseId type
    const dbIds = Array.from(entitiesByDb.keys());
    const dbIdType = dbIds.map((id) => `'${id}'`).join(" | ");

    // Generate DatabaseTables type
    const dbReturnTypes = Array.from(entitiesByDb.entries())
      .map(([dbId, dbEntities]) => {
        const tableTypes = dbEntities
          .map((entity) => {
            const typeName = this.toPascalCase(entity.name);
            return `    ${entity.name}: {
      create: (data: Omit<${typeName}, keyof Models.Row>, options?: { rowId?: string; permissions?: Permission[] }) => Promise<${typeName}>;
      get: (id: string) => Promise<${typeName}>;
      update: (id: string, data: Partial<Omit<${typeName}, keyof Models.Row>>, options?: { permissions?: Permission[] }) => Promise<${typeName}>;
      delete: (id: string) => Promise<void>;
      list: (queries?: string[]) => Promise<{ total: number; rows: ${typeName}[] }>;
    }`;
          })
          .join(";\n");
        return `  '${dbId}': {\n${tableTypes}\n  }`;
      })
      .join(";\n");

    const parts = [`import { type Models, Permission } from '${appwriteDep}';`, ""];

    if (enums) {
      parts.push(enums);
      parts.push("");
    }

    parts.push(types);
    parts.push("");

    // Add database types
    parts.push(`export type DatabaseId = ${dbIdType};`);
    parts.push("");
    parts.push(`export type DatabaseTables = {\n${dbReturnTypes}\n};`);
    parts.push("");

    return parts.join("\n");
  }

  private getAppwriteDependency(): string {
    const cwd = process.cwd();

    if (fs.existsSync(path.resolve(cwd, "package.json"))) {
      try {
        const packageJsonRaw = fs.readFileSync(
          path.resolve(cwd, "package.json"),
          "utf-8",
        );
        const packageJson = JSON.parse(packageJsonRaw);
        return packageJson.dependencies?.["appwrite"]
          ? "appwrite"
          : "node-appwrite";
      } catch {
        // Fallback if package.json is invalid
      }
    }

    if (fs.existsSync(path.resolve(cwd, "deno.json"))) {
      return "https://deno.land/x/appwrite/mod.ts";
    }

    return "appwrite";
  }

  private generateDbFile(config: ConfigType): string {
    // Use tables if available, fall back to collections
    const entities = config.tables?.length ? config.tables : config.collections;

    if (!entities || entities.length === 0) {
      return "// No tables or collections found in configuration\n";
    }

    // Group entities by databaseId
    const entitiesByDb = new Map<string, Array<(typeof entities)[number]>>();
    for (const entity of entities) {
      const dbId = entity.databaseId;
      if (!entitiesByDb.has(dbId)) {
        entitiesByDb.set(dbId, []);
      }
      entitiesByDb.get(dbId)!.push(entity);
    }

    const typeNames = entities.map((e) => this.toPascalCase(e.name));
    const appwriteDep = this.getAppwriteDependency();

    // Generate table helpers for each database
    const generateTableHelpers = (dbId: string, dbEntities: Array<(typeof entities)[number]>) => {
      return dbEntities
        .map((entity) => {
          const entityName = entity.name;
          const typeName = this.toPascalCase(entity.name);

          return `    ${entityName}: {
      create: (data: Omit<${typeName}, keyof Models.Row>, options?: { rowId?: string; permissions?: Permission[] }) =>
        tablesDB.createRow<${typeName}>({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rowId: options?.rowId ?? ID.unique(),
          data,
          permissions: options?.permissions?.map((p) => p.toString()),
        }),
      get: (id: string) =>
        tablesDB.getRow<${typeName}>({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rowId: id,
        }),
      update: (id: string, data: Partial<Omit<${typeName}, keyof Models.Row>>, options?: { permissions?: Permission[] }) =>
        tablesDB.updateRow<${typeName}>({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rowId: id,
          data,
          ...(options?.permissions ? { permissions: options.permissions.map((p) => p.toString()) } : {}),
        }),
      delete: async (id: string) => {
        await tablesDB.deleteRow({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rowId: id,
        });
      },
      list: (queries?: string[]) =>
        tablesDB.listRows<${typeName}>({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          queries,
        }),
    }`;
        })
        .join(",\n");
    };

    // Generate the database map
    const databasesMap = Array.from(entitiesByDb.entries())
      .map(([dbId, dbEntities]) => {
        return `  '${dbId}': {\n${generateTableHelpers(dbId, dbEntities)}\n  }`;
      })
      .join(",\n");

    return `import { Client, TablesDB, ID, type Models, Permission } from '${appwriteDep}';
import type { ${typeNames.join(", ")}, DatabaseId, DatabaseTables } from './types.js';

export const createDatabases = (client: Client) => {
  const tablesDB = new TablesDB(client);

  const _databases: { [K in DatabaseId]: DatabaseTables[K] } = {
${databasesMap}
  };

  return {
    from: <T extends DatabaseId>(databaseId: T): DatabaseTables[T] => _databases[databaseId],
  };
};
`;
  }

  /**
   * Generates TypeScript code for Appwrite database collections and types based on the provided configuration.
   *
   * This method returns generated content as strings:
   *   1. A types string containing TypeScript interfaces for each collection.
   *   2. A database client string with helper methods for CRUD operations on each table/collection.
   *
   * @param config - The Appwrite project configuration, including tables/collections and project details.
   * @returns A Promise that resolves with an object containing dbContent and typesContent strings.
   * @throws If the configuration is missing a projectId or contains no tables/collections.
   */
  public async generate(config: ConfigType): Promise<GenerateResult> {
    if (!config.projectId) {
      throw new Error("Project ID is required in configuration");
    }

    // Use tables if available, fall back to collections
    const hasEntities =
      (config.tables && config.tables.length > 0) ||
      (config.collections && config.collections.length > 0);

    if (!hasEntities) {
      console.log(
        "No tables or collections found in configuration. Skipping database generation.",
      );
      return {
        dbContent: "// No tables or collections found in configuration\n",
        typesContent: "// No tables or collections found in configuration\n",
      };
    }

    // Generate types content
    const typesContent = this.generateTypesFile(config);

    // Generate database client content
    const dbContent = this.generateDbFile(config);

    return {
      dbContent,
      typesContent,
    };
  }

  /**
   * Writes generated files to the specified output directory.
   *
   * @param outputDir - The directory to write the generated files to.
   * @param result - The generated content from the generate method.
   */
  public async writeFiles(
    outputDir: string,
    result: GenerateResult,
  ): Promise<void> {
    // Create appwrite subdirectory
    const appwriteDir = path.join(outputDir, "appwrite");
    if (!fs.existsSync(appwriteDir)) {
      fs.mkdirSync(appwriteDir, { recursive: true });
    }

    // Write db.ts
    const dbFilePath = path.join(appwriteDir, "db.ts");
    fs.writeFileSync(dbFilePath, result.dbContent, "utf-8");

    // Write types.ts
    const typesFilePath = path.join(appwriteDir, "types.ts");
    fs.writeFileSync(typesFilePath, result.typesContent, "utf-8");

    // Write index.ts (main entry point)
    const mainContent = this.generateMainEntryFile();
    const mainFilePath = path.join(appwriteDir, "index.ts");
    fs.writeFileSync(mainFilePath, mainContent, "utf-8");
  }

  /**
   * Generates the main entry file that exports the db module.
   */
  private generateMainEntryFile(): string {
    return `/**
 * Appwrite Generated SDK
 *
 * This file is auto-generated. Do not edit manually.
 * Re-run \`appwrite generate\` to regenerate.
 */

export { createDatabases } from "./db.js";
export * from "./types.js";
`;
  }
}

export interface GenerateCommandOptions {
  output: string;
}

const generateAction = async (options: GenerateCommandOptions): Promise<void> => {
  const db = new Db();
  const project = localConfig.getProject();

  if (!project.projectId) {
    error("No project found. Please run 'appwrite init project' first.");
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
    const result = await db.generate(config);
    await db.writeFiles(absoluteOutputDir, result);

    success(`Generated files:`);
    console.log(`  - ${path.join(outputDir, "appwrite/index.ts")}`);
    console.log(`  - ${path.join(outputDir, "appwrite/db.ts")}`);
    console.log(`  - ${path.join(outputDir, "appwrite/types.ts")}`);
    console.log("");
    log(`Import the generated SDK in your project:`);
    console.log(`  import { createDatabases } from "./${outputDir}/appwrite/index.js";`);
    console.log("");
    log(`Usage:`);
    console.log(`  import { Client } from 'node-appwrite';`);
    console.log(`  const client = new Client().setEndpoint('...').setProject('...').setKey('...');`);
    console.log(`  const databases = createDatabases(client);`);
    console.log(`  const db = databases.from('your-database-id');`);
    console.log(`  await db.tableName.create({ ... });`);
  } catch (err: any) {
    error(`Failed to generate SDK: ${err.message}`);
    process.exit(1);
  }
};

export const generate = new Command("generate")
  .description("Generate a type-safe SDK from your Appwrite project configuration")
  .option("-o, --output <directory>", "Output directory for generated files (default: generated)", "generated")
  .action(actionRunner(generateAction));
