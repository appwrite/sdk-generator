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
    entityName: string,
  ): string {
    let type = "";

    switch (attribute.type) {
      case "string":
      case "datetime":
        type = "string";
        if (attribute.format === "enum") {
          type = toPascalCase(entityName) + toPascalCase(attribute.key);
        }
        break;
      case "integer":
      case "double":
        type = "number";
        break;
      case "boolean":
        type = "boolean";
        break;
      case "relationship": {
        // Handle both collections (relatedCollection) and tables (relatedTable)
        const relatedId = attribute.relatedCollection ?? attribute.relatedTable;
        const relatedEntity = collections.find(
          (c) => c.$id === relatedId || c.name === relatedId,
        );
        if (!relatedEntity) {
          throw new Error(
            `Related entity with ID '${relatedId}' not found.`,
          );
        }
        type = toPascalCase(relatedEntity.name);
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
      }
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
      .map((attr) => `    ${attr.key}${attr.required ? '' : '?'}: ${this.getType(attr, entities as any, entity.name)};`)
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
          const enumName = toPascalCase(entity.name) + toPascalCase(field.key);
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
        const deps = packageJson.dependencies ?? {};

        if (deps["@appwrite.io/console"]) {
          return "@appwrite.io/console";
        }
        if (deps["react-native-appwrite"]) {
          return "react-native-appwrite";
        }
        if (deps["appwrite"]) {
          return "appwrite";
        }
        if (deps["node-appwrite"]) {
          return "node-appwrite";
        }
      } catch {
        // Fallback if package.json is invalid
      }
    }

    if (fs.existsSync(path.resolve(cwd, "deno.json"))) {
      return "npm:node-appwrite";
    }

    return "appwrite";
  }

  private supportsBulkMethods(appwriteDep: string): boolean {
    return appwriteDep === "node-appwrite" || appwriteDep === "npm:node-appwrite" || appwriteDep === "@appwrite.io/console";
  }

  private generateQueryBuilderType(): string {
    return `export type QueryValue = string | number | boolean;

export type ExtractQueryValue<T> = T extends (infer U)[]
  ? U extends QueryValue ? U : never
  : T extends QueryValue | null ? NonNullable<T> : never;

export type QueryableKeys<T> = {
  [K in keyof T]: ExtractQueryValue<T[K]> extends never ? never : K;
}[keyof T];

export type QueryBuilder<T> = {
  equal: <K extends QueryableKeys<T>>(field: K, value: ExtractQueryValue<T[K]>) => string;
  notEqual: <K extends QueryableKeys<T>>(field: K, value: ExtractQueryValue<T[K]>) => string;
  lessThan: <K extends QueryableKeys<T>>(field: K, value: ExtractQueryValue<T[K]>) => string;
  lessThanEqual: <K extends QueryableKeys<T>>(field: K, value: ExtractQueryValue<T[K]>) => string;
  greaterThan: <K extends QueryableKeys<T>>(field: K, value: ExtractQueryValue<T[K]>) => string;
  greaterThanEqual: <K extends QueryableKeys<T>>(field: K, value: ExtractQueryValue<T[K]>) => string;
  contains: <K extends QueryableKeys<T>>(field: K, value: ExtractQueryValue<T[K]>) => string;
  search: <K extends QueryableKeys<T>>(field: K, value: string) => string;
  isNull: <K extends QueryableKeys<T>>(field: K) => string;
  isNotNull: <K extends QueryableKeys<T>>(field: K) => string;
  startsWith: <K extends QueryableKeys<T>>(field: K, value: string) => string;
  endsWith: <K extends QueryableKeys<T>>(field: K, value: string) => string;
  between: <K extends QueryableKeys<T>>(field: K, start: ExtractQueryValue<T[K]>, end: ExtractQueryValue<T[K]>) => string;
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

  private generateDatabaseTablesType(entitiesByDb: Map<string, Entity[]>, appwriteDep: string): string {
    const hasBulkMethods = this.supportsBulkMethods(appwriteDep);
    const dbReturnTypes = Array.from(entitiesByDb.entries())
      .map(([dbId, dbEntities]) => {
        const tableTypes = dbEntities
          .map((entity) => {
            const typeName = toPascalCase(entity.name);
            const baseMethods = `      create: (data: Omit<${typeName}, keyof Models.Row>, options?: { rowId?: string; permissions?: Permission[]; transactionId?: string }) => Promise<${typeName}>;
      get: (id: string) => Promise<${typeName}>;
      update: (id: string, data: Partial<Omit<${typeName}, keyof Models.Row>>, options?: { permissions?: Permission[]; transactionId?: string }) => Promise<${typeName}>;
      delete: (id: string, options?: { transactionId?: string }) => Promise<void>;
      list: (options?: { queries?: (q: QueryBuilder<${typeName}>) => string[] }) => Promise<{ total: number; rows: ${typeName}[] }>;`;

            const bulkMethods = hasBulkMethods ? `
      createMany: (rows: Array<{ data: Omit<${typeName}, keyof Models.Row>; rowId?: string; permissions?: Permission[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: ${typeName}[] }>;
      updateMany: (rows: Array<{ rowId: string; data: Partial<Omit<${typeName}, keyof Models.Row>>; permissions?: Permission[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: ${typeName}[] }>;
      deleteMany: (rowIds: string[], options?: { transactionId?: string }) => Promise<void>;` : '';

            return `    '${entity.name}': {\n${baseMethods}${bulkMethods}\n    }`;
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
    parts.push(this.generateDatabaseTablesType(entitiesByDb, appwriteDep));
    parts.push("");

    return parts.join("\n");
  }

  private generateQueryBuilder(): string {
    return `const createQueryBuilder = <T>(): QueryBuilder<T> => ({
  equal: (field, value) => Query.equal(String(field), value),
  notEqual: (field, value) => Query.notEqual(String(field), value),
  lessThan: (field, value) => Query.lessThan(String(field), value),
  lessThanEqual: (field, value) => Query.lessThanEqual(String(field), value),
  greaterThan: (field, value) => Query.greaterThan(String(field), value),
  greaterThanEqual: (field, value) => Query.greaterThanEqual(String(field), value),
  contains: (field, value) => Query.contains(String(field), value),
  search: (field, value) => Query.search(String(field), value),
  isNull: (field) => Query.isNull(String(field)),
  isNotNull: (field) => Query.isNotNull(String(field)),
  startsWith: (field, value) => Query.startsWith(String(field), value),
  endsWith: (field, value) => Query.endsWith(String(field), value),
  between: (field, start, end) => Query.between(String(field), start, end),
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

  private generateTableHelpers(dbId: string, dbEntities: Entity[], appwriteDep: string): string {
    const hasBulkMethods = this.supportsBulkMethods(appwriteDep);

    return dbEntities
      .map((entity) => {
        const entityName = entity.name;
        const typeName = toPascalCase(entity.name);

        const baseMethods = `      create: (data: Omit<${typeName}, keyof Models.Row>, options?: { rowId?: string; permissions?: Permission[]; transactionId?: string }) =>
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
        }),`;

        const bulkMethods = hasBulkMethods ? `
      createMany: (rows: Array<{ data: Omit<${typeName}, keyof Models.Row>; rowId?: string; permissions?: Permission[] }>, options?: { transactionId?: string }) =>
        tablesDB.createRows<${typeName}>({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rows: rows.map((row) => ({
            rowId: row.rowId ?? ID.unique(),
            data: row.data,
            permissions: row.permissions?.map((p) => p.toString()),
          })),
          transactionId: options?.transactionId,
        }),
      updateMany: (rows: Array<{ rowId: string; data: Partial<Omit<${typeName}, keyof Models.Row>>; permissions?: Permission[] }>, options?: { transactionId?: string }) =>
        tablesDB.updateRows<${typeName}>({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rows: rows.map((row) => ({
            rowId: row.rowId,
            data: row.data,
            permissions: row.permissions?.map((p) => p.toString()),
          })),
          transactionId: options?.transactionId,
        }),
      deleteMany: async (rowIds: string[], options?: { transactionId?: string }) => {
        await tablesDB.deleteRows({
          databaseId: '${dbId}',
          tableId: '${entity.$id}',
          rows: rowIds.map((rowId) => ({ rowId })),
          transactionId: options?.transactionId,
        });
      },` : '';

        return `    '${entityName}': {\n${baseMethods}${bulkMethods}\n    }`;
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
        return `  '${dbId}': {\n${this.generateTableHelpers(dbId, dbEntities, appwriteDep)}\n  }`;
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
