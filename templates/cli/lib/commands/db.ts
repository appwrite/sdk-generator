import { ConfigType, AttributeSchema } from "./config.js";
import * as fs from "fs";
import * as path from "path";
import { z } from "zod";

export interface GenerateOptions {
  strict?: boolean;
}

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

  private toCamelCase(str: string): string {
    return str
      .replace(/[-_\s]+(.)?/g, (_, char) => (char ? char.toUpperCase() : ""))
      .replace(/^(.)/, (char) => char.toLowerCase());
  }

  private toUpperSnakeCase(str: string): string {
    return str
      .replace(/([a-z])([A-Z])/g, "$1_$2")
      .replace(/[-\s]+/g, "_")
      .toUpperCase();
  }

  private generateCollectionType(
    collection: NonNullable<ConfigType["collections"]>[number],
    collections: NonNullable<ConfigType["collections"]>,
    options: GenerateOptions = {},
  ): string {
    if (!collection.attributes) {
      return "";
    }

    const { strict = false } = options;
    const typeName = this.toPascalCase(collection.name);
    const attributes = collection.attributes
      .map((attr: z.infer<typeof AttributeSchema>) => {
        const key = strict ? this.toCamelCase(attr.key) : attr.key;
        return `    ${key}: ${this.getType(attr, collections)};`;
      })
      .join("\n");

    return `export type ${typeName} = Models.Row & {\n${attributes}\n}`;
  }

  private generateEnums(
    collections: NonNullable<ConfigType["collections"]>,
  ): string {
    const enumTypes: string[] = [];

    for (const collection of collections) {
      if (!collection.attributes) continue;

      for (const attribute of collection.attributes) {
        if (attribute.format === "enum" && attribute.elements) {
          const enumName = this.toPascalCase(attribute.key);
          const enumValues = attribute.elements
            .map((element, index) => {
              const key = this.toUpperSnakeCase(element);
              const isLast = index === attribute.elements!.length - 1;
              return `    ${key} = "${element}"${isLast ? "" : ","}`;
            })
            .join("\n");

          enumTypes.push(`export enum ${enumName} {\n${enumValues}\n}`);
        }
      }
    }

    return enumTypes.join("\n\n");
  }

  private generateTypesFile(
    config: ConfigType,
    options: GenerateOptions = {},
  ): string {
    if (!config.collections || config.collections.length === 0) {
      return "// No collections found in configuration\n";
    }

    const appwriteDep = this.getAppwriteDependency();
    const enums = this.generateEnums(config.collections);
    const types = config.collections
      .map((collection) =>
        this.generateCollectionType(collection, config.collections!, options),
      )
      .join("\n\n");

    const parts = [`import { type Models } from '${appwriteDep}';`, ""];

    if (enums) {
      parts.push(enums);
      parts.push("");
    }

    parts.push(types);
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

  private generateDbFile(
    config: ConfigType,
    options: GenerateOptions = {},
  ): string {
    const { strict = false } = options;
    const typesFileName = "appwrite.types.ts";

    if (!config.collections || config.collections.length === 0) {
      return "// No collections found in configuration\n";
    }

    const typeNames = config.collections.map((c) => this.toPascalCase(c.name));
    const importPath = typesFileName
      .replace(/\.d\.ts$/, "")
      .replace(/\.ts$/, "");
    const appwriteDep = this.getAppwriteDependency();

    const collectionsCode = config.collections
      .map((collection) => {
        const collectionName = strict
          ? this.toCamelCase(collection.name)
          : collection.name;
        const typeName = this.toPascalCase(collection.name);

        return `  ${collectionName}: {
    create: (data: Omit<${typeName}, keyof Models.Row>, options?: { rowId?: string; permissions?: string[] }) =>
      tablesDB.createRow<${typeName}>({
        databaseId: process.env.APPWRITE_DB_ID!,
        tableId: '${collection.$id}',
        rowId: options?.rowId ?? ID.unique(),
        data,
        permissions: [
            Permission.write(Role.user(data.createdBy)),
            Permission.read(Role.user(data.createdBy)),
            Permission.update(Role.user(data.createdBy)),
            Permission.delete(Role.user(data.createdBy))
        ]
      }),
    get: (id: string) =>
      tablesDB.getRow<${typeName}>({
        databaseId: process.env.APPWRITE_DB_ID!,
        tableId: '${collection.$id}',
        rowId: id,
      }),
    update: (id: string, data: Partial<Omit<${typeName}, keyof Models.Row>>, options?: { permissions?: string[] }) =>
      tablesDB.updateRow<${typeName}>({
        databaseId: process.env.APPWRITE_DB_ID!,
        tableId: '${collection.$id}',
        rowId: id,
        data,
        ...(options?.permissions ? { permissions: options.permissions } : {}),
      }),
    delete: (id: string) =>
      tablesDB.deleteRow({
        databaseId: process.env.APPWRITE_DB_ID!,
        tableId: '${collection.$id}',
        rowId: id,
      }),
    list: (queries?: string[]) =>
      tablesDB.listRows<${typeName}>({
        databaseId: process.env.APPWRITE_DB_ID!,
        tableId: '${collection.$id}',
        queries,
      }),
  }`;
      })
      .join(",\n");

    return `import { Client, TablesDB, ID, type Models, Permission, Role } from '${appwriteDep}';
import type { ${typeNames.join(", ")} } from './${importPath}';

const client = new Client()
  .setEndpoint(process.env.APPWRITE_ENDPOINT!)
  .setProject(process.env.APPWRITE_PROJECT_ID!)
  .setKey(process.env.APPWRITE_API_KEY!);

const tablesDB = new TablesDB(client);


export const db = {
${collectionsCode}
};
`;
  }

  /**
   * Generates TypeScript code for Appwrite database collections and types based on the provided configuration.
   *
   * This method returns generated content as strings:
   *   1. A types string containing TypeScript interfaces for each collection.
   *   2. A database client string with helper methods for CRUD operations on each collection.
   *
   * @param config - The Appwrite project configuration, including collections and project details.
   * @param options - Optional settings for code generation:
   *   - strict: Whether to use strict naming conventions for collections (default: false).
   * @returns A Promise that resolves with an object containing dbContent and typesContent strings.
   * @throws If the configuration is missing a projectId or contains no collections.
   */
  public async generate(
    config: ConfigType,
    options: GenerateOptions = {},
  ): Promise<GenerateResult> {
    const { strict = false } = options;

    if (!config.projectId) {
      throw new Error("Project ID is required in configuration");
    }

    if (!config.collections || config.collections.length === 0) {
      console.log(
        "No collections found in configuration. Skipping database generation.",
      );
      return {
        dbContent: "// No collections found in configuration\n",
        typesContent: "// No collections found in configuration\n",
      };
    }

    // Generate types content
    const typesContent = this.generateTypesFile(config, { strict });

    // Generate database client content
    const dbContent = this.generateDbFile(config, { strict });

    return {
      dbContent,
      typesContent,
    };
  }
}
