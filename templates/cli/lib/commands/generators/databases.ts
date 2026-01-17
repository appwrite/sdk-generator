import * as fs from "fs";
import * as path from "path";
import { z } from "zod";
import { ConfigType, AttributeSchema } from "../config.js";
import { toPascalCase, toUpperSnakeCase } from "../../utils.js";

export interface GenerateResult {
  databasesContent: string;
  typesContent: string;
  indexContent: string;
}

type Entity = NonNullable<ConfigType["tables"]>[number] | NonNullable<ConfigType["collections"]>[number];
type Entities = NonNullable<ConfigType["tables"]> | NonNullable<ConfigType["collections"]>;

export class DatabasesGenerator {
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
          type = toPascalCase(attribute.key);
        }
        break;
      case "integer":
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
        type = toPascalCase(relatedCollection.name);
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

  private getFields(entity: Entity): z.infer<typeof AttributeSchema>[] | undefined {
    return "columns" in entity
      ? (entity as NonNullable<ConfigType["tables"]>[number]).columns
      : (entity as NonNullable<ConfigType["collections"]>[number]).attributes;
  }

  private generateTableType(entity: Entity, entities: Entities): string {
    const fields = this.getFields(entity);
    if (!fields) return "";

    const typeName = toPascalCase(entity.name);
    const attributes = fields
      .map((attr) => `    ${attr.key}: ${this.getType(attr, entities as any)};`)
      .join("\n");

    return `export type ${typeName} = Models.Row & {\n${attributes}\n}`;
  }

  private generateEnums(entities: Entities): string {
    const enumTypes: string[] = [];

    for (const entity of entities) {
      const fields = this.getFields(entity);
      if (!fields) continue;

      for (const field of fields) {
        if (field.format === "enum" && field.elements) {
          const enumName = toPascalCase(field.key);
          const enumValues = field.elements
            .map((element: string, index: number) => {
              const key = toUpperSnakeCase(element);
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

  private groupEntitiesByDb(entities: Entities): Map<string, Entity[]> {
    const entitiesByDb = new Map<string, Entity[]>();
    for (const entity of entities) {
      const dbId = entity.databaseId;
      if (!entitiesByDb.has(dbId)) {
        entitiesByDb.set(dbId, []);
      }
      entitiesByDb.get(dbId)!.push(entity);
    }
    return entitiesByDb;
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

  private generateQueryBuilderType(): string {
    return `export type QueryBuilder<T> = {
  equal: <K extends keyof T>(field: K, value: T[K]) => string;
  notEqual: <K extends keyof T>(field: K, value: T[K]) => string;
  lessThan: <K extends keyof T>(field: K, value: T[K]) => string;
  lessThanEqual: <K extends keyof T>(field: K, value: T[K]) => string;
  greaterThan: <K extends keyof T>(field: K, value: T[K]) => string;
  greaterThanEqual: <K extends keyof T>(field: K, value: T[K]) => string;
  contains: <K extends keyof T>(field: K, value: T[K] extends (infer U)[] ? U : T[K]) => string;
  search: <K extends keyof T>(field: K, value: string) => string;
  isNull: <K extends keyof T>(field: K) => string;
  isNotNull: <K extends keyof T>(field: K) => string;
  startsWith: <K extends keyof T>(field: K, value: string) => string;
  endsWith: <K extends keyof T>(field: K, value: string) => string;
  between: <K extends keyof T>(field: K, start: T[K], end: T[K]) => string;
  select: <K extends keyof T>(fields: K[]) => string;
  orderAsc: <K extends keyof T>(field: K) => string;
  orderDesc: <K extends keyof T>(field: K) => string;
  limit: (value: number) => string;
  offset: (value: number) => string;
  cursorAfter: (documentId: string) => string;
  cursorBefore: (documentId: string) => string;
  or: (...queries: string[]) => string;
  and: (...queries: string[]) => string;
}`;
  }

  private generateDatabaseTablesType(entitiesByDb: Map<string, Entity[]>): string {
    const dbReturnTypes = Array.from(entitiesByDb.entries())
      .map(([dbId, dbEntities]) => {
        const tableTypes = dbEntities
          .map((entity) => {
            const typeName = toPascalCase(entity.name);
            return `    ${entity.name}: {
      create: (data: Omit<${typeName}, keyof Models.Row>, options?: { rowId?: string; permissions?: Permission[]; transactionId?: string }) => Promise<${typeName}>;
      get: (id: string) => Promise<${typeName}>;
      update: (id: string, data: Partial<Omit<${typeName}, keyof Models.Row>>, options?: { permissions?: Permission[]; transactionId?: string }) => Promise<${typeName}>;
      delete: (id: string, options?: { transactionId?: string }) => Promise<void>;
      list: (options?: { queries?: (q: QueryBuilder<${typeName}>) => string[] }) => Promise<{ total: number; rows: ${typeName}[] }>;
    }`;
          })
          .join(";\n");
        return `  '${dbId}': {\n${tableTypes}\n  }`;
      })
      .join(";\n");

    return `export type DatabaseTables = {\n${dbReturnTypes}\n}`;
  }

  generateTypesFile(config: ConfigType): string {
    const entities = config.tables?.length ? config.tables : config.collections;

    if (!entities || entities.length === 0) {
      return "// No tables or collections found in configuration\n";
    }

    const appwriteDep = this.getAppwriteDependency();
    const enums = this.generateEnums(entities);
    const types = entities
      .map((entity) => this.generateTableType(entity, entities))
      .join("\n\n");
    const entitiesByDb = this.groupEntitiesByDb(entities);
    const dbIds = Array.from(entitiesByDb.keys());
    const dbIdType = dbIds.map((id) => `'${id}'`).join(" | ");

    const parts = [`import { type Models, Permission } from '${appwriteDep}';`, ""];

    if (enums) {
      parts.push(enums);
      parts.push("");
    }

    parts.push(types);
    parts.push("");
    parts.push(this.generateQueryBuilderType());
    parts.push("");
    parts.push(`export type DatabaseId = ${dbIdType};`);
    parts.push("");
    parts.push(this.generateDatabaseTablesType(entitiesByDb));
    parts.push("");

    return parts.join("\n");
  }

  private generateQueryBuilder(): string {
    return `const createQueryBuilder = <T>(): QueryBuilder<T> => ({
  equal: (field, value) => Query.equal(String(field), value as any),
  notEqual: (field, value) => Query.notEqual(String(field), value as any),
  lessThan: (field, value) => Query.lessThan(String(field), value as any),
  lessThanEqual: (field, value) => Query.lessThanEqual(String(field), value as any),
  greaterThan: (field, value) => Query.greaterThan(String(field), value as any),
  greaterThanEqual: (field, value) => Query.greaterThanEqual(String(field), value as any),
  contains: (field, value) => Query.contains(String(field), value as any),
  search: (field, value) => Query.search(String(field), value),
  isNull: (field) => Query.isNull(String(field)),
  isNotNull: (field) => Query.isNotNull(String(field)),
  startsWith: (field, value) => Query.startsWith(String(field), value),
  endsWith: (field, value) => Query.endsWith(String(field), value),
  between: (field, start, end) => Query.between(String(field), start as any, end as any),
  select: (fields) => Query.select(fields.map(String)),
  orderAsc: (field) => Query.orderAsc(String(field)),
  orderDesc: (field) => Query.orderDesc(String(field)),
  limit: (value) => Query.limit(value),
  offset: (value) => Query.offset(value),
  cursorAfter: (documentId) => Query.cursorAfter(documentId),
  cursorBefore: (documentId) => Query.cursorBefore(documentId),
  or: (...queries) => Query.or(queries),
  and: (...queries) => Query.and(queries),
})`;
  }

  private generateTableHelpers(dbId: string, dbEntities: Entity[]): string {
    return dbEntities
      .map((entity) => {
        const entityName = entity.name;
        const typeName = toPascalCase(entity.name);

        return `    ${entityName}: {
      create: (data: Omit<${typeName}, keyof Models.Row>, options?: { rowId?: string; permissions?: Permission[]; transactionId?: string }) =>
        tablesDB.createRow<${typeName}>({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rowId: options?.rowId ?? ID.unique(),
          data,
          permissions: options?.permissions?.map((p) => p.toString()),
          transactionId: options?.transactionId,
        }),
      get: (id: string) =>
        tablesDB.getRow<${typeName}>({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rowId: id,
        }),
      update: (id: string, data: Partial<Omit<${typeName}, keyof Models.Row>>, options?: { permissions?: Permission[]; transactionId?: string }) =>
        tablesDB.updateRow<${typeName}>({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rowId: id,
          data,
          ...(options?.permissions ? { permissions: options.permissions.map((p) => p.toString()) } : {}),
          transactionId: options?.transactionId,
        }),
      delete: async (id: string, options?: { transactionId?: string }) => {
        await tablesDB.deleteRow({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rowId: id,
          transactionId: options?.transactionId,
        });
      },
      list: (options?: { queries?: (q: QueryBuilder<${typeName}>) => string[] }) =>
        tablesDB.listRows<${typeName}>({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          queries: options?.queries?.(createQueryBuilder<${typeName}>()),
        }),
    }`;
      })
      .join(",\n");
  }

  generateDatabasesFile(config: ConfigType): string {
    const entities = config.tables?.length ? config.tables : config.collections;

    if (!entities || entities.length === 0) {
      return "// No tables or collections found in configuration\n";
    }

    const entitiesByDb = this.groupEntitiesByDb(entities);
    const typeNames = entities.map((e) => toPascalCase(e.name));
    const appwriteDep = this.getAppwriteDependency();

    const databasesMap = Array.from(entitiesByDb.entries())
      .map(([dbId, dbEntities]) => {
        return `  '${dbId}': {\n${this.generateTableHelpers(dbId, dbEntities)}\n  }`;
      })
      .join(",\n");

    return `import { Client, TablesDB, ID, Query, type Models, Permission } from '${appwriteDep}';
import type { ${typeNames.join(", ")}, DatabaseId, DatabaseTables, QueryBuilder } from './types.js';

${this.generateQueryBuilder()};

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

  generateIndexFile(): string {
    return `/**
 * Appwrite Generated SDK
 *
 * This file is auto-generated. Do not edit manually.
 * Re-run \`appwrite generate\` to regenerate.
 */

export { createDatabases } from "./databases.js";
export * from "./types.js";
`;
  }

  async generate(config: ConfigType): Promise<GenerateResult> {
    if (!config.projectId) {
      throw new Error("Project ID is required in configuration");
    }

    const hasEntities =
      (config.tables && config.tables.length > 0) ||
      (config.collections && config.collections.length > 0);

    if (!hasEntities) {
      console.log(
        "No tables or collections found in configuration. Skipping database generation.",
      );
      return {
        databasesContent: "// No tables or collections found in configuration\n",
        typesContent: "// No tables or collections found in configuration\n",
        indexContent: this.generateIndexFile(),
      };
    }

    return {
      typesContent: this.generateTypesFile(config),
      databasesContent: this.generateDatabasesFile(config),
      indexContent: this.generateIndexFile(),
    };
  }

  async writeFiles(outputDir: string, result: GenerateResult): Promise<void> {
    const appwriteDir = path.join(outputDir, "appwrite");
    if (!fs.existsSync(appwriteDir)) {
      fs.mkdirSync(appwriteDir, { recursive: true });
    }

    fs.writeFileSync(path.join(appwriteDir, "databases.ts"), result.databasesContent, "utf-8");
    fs.writeFileSync(path.join(appwriteDir, "types.ts"), result.typesContent, "utf-8");
    fs.writeFileSync(path.join(appwriteDir, "index.ts"), result.indexContent, "utf-8");
  }
}
