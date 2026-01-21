import * as fs from "fs";
import * as path from "path";
import { z } from "zod";
import { ConfigType, AttributeSchema } from "../../config.js";
import { toPascalCase, sanitizeEnumKey } from "../../../utils.js";
import { SDK_TITLE, EXECUTABLE_NAME } from "../../../constants.js";
import {
  BaseDatabasesGenerator,
  GenerateResult,
  SupportedLanguage,
} from "../base.js";
import { loadTemplate, renderTemplate } from "./template-loader.js";

type Entity =
  | NonNullable<ConfigType["tables"]>[number]
  | NonNullable<ConfigType["collections"]>[number];
type Entities =
  | NonNullable<ConfigType["tables"]>
  | NonNullable<ConfigType["collections"]>;

/**
 * TypeScript-specific database generator.
 * Generates type-safe SDK files for TypeScript/JavaScript projects.
 */
export class TypeScriptDatabasesGenerator extends BaseDatabasesGenerator {
  readonly language: SupportedLanguage = "typescript";
  readonly fileExtension = "ts";

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
          throw new Error(`Related entity with ID '${relatedId}' not found.`);
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

  private getFields(
    entity: Entity,
  ): z.infer<typeof AttributeSchema>[] | undefined {
    return "columns" in entity
      ? (entity as NonNullable<ConfigType["tables"]>[number]).columns
      : (entity as NonNullable<ConfigType["collections"]>[number]).attributes;
  }

  /**
   * Checks if an entity has relationship columns.
   * Used to disable bulk methods for tables with relationships.
   *
   * TODO: Remove this restriction when bulk operations support relationships.
   * To enable bulk methods for all tables, simply return false here:
   *   return false;
   */
  private hasRelationshipColumns(entity: Entity): boolean {
    const fields = this.getFields(entity);
    if (!fields) return false;
    return fields.some((field) => field.type === "relationship");
  }

  private generateTableType(entity: Entity, entities: Entities): string {
    const fields = this.getFields(entity);
    if (!fields) return "";

    const typeName = toPascalCase(entity.name);
    const attributes = fields
      .map(
        (attr) =>
          `    ${JSON.stringify(attr.key)}${attr.required ? "" : "?"}: ${this.getType(attr, entities as any, entity.name)};`,
      )
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
          const usedKeys = new Set<string>();
          const enumValues = field.elements
            .map((element: string, index: number) => {
              let key = sanitizeEnumKey(element);
              if (usedKeys.has(key)) {
                let disambiguator = 1;
                while (usedKeys.has(`${key}_${disambiguator}`)) {
                  disambiguator++;
                }
                key = `${key}_${disambiguator}`;
              }
              usedKeys.add(key);
              const isLast = index === field.elements!.length - 1;
              return `    ${key} = ${JSON.stringify(element)}${isLast ? "" : ","}`;
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
    return (
      appwriteDep === "node-appwrite" ||
      appwriteDep === "npm:node-appwrite" ||
      appwriteDep === "@appwrite.io/console"
    );
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

  private generateDatabaseTablesType(
    entitiesByDb: Map<string, Entity[]>,
    appwriteDep: string,
  ): string {
    const supportsBulk = this.supportsBulkMethods(appwriteDep);
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

            // Bulk methods not supported for tables with relationship columns (see hasRelationshipColumns)
            const canUseBulkMethods =
              supportsBulk && !this.hasRelationshipColumns(entity);
            const bulkMethods = canUseBulkMethods
              ? `
      createMany: (rows: Array<Omit<${typeName}, keyof Models.Row> & { $id?: string; $permissions?: string[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: ${typeName}[] }>;
      updateMany: (data: Partial<Omit<${typeName}, keyof Models.Row>>, options?: { queries?: (q: QueryBuilder<${typeName}>) => string[]; transactionId?: string }) => Promise<{ total: number; rows: ${typeName}[] }>;
      deleteMany: (options?: { queries?: (q: QueryBuilder<${typeName}>) => string[]; transactionId?: string }) => Promise<{ total: number; rows: ${typeName}[] }>;`
              : "";

            return `    '${entity.name}': {\n${baseMethods}${bulkMethods}\n    }`;
          })
          .join(";\n");
        return `  '${dbId}': {\n${tableTypes}\n  }`;
      })
      .join(";\n");

    return `export type DatabaseTables = {\n${dbReturnTypes}\n}`;
  }

  private generateTypesFile(config: ConfigType): string {
    const entities = config.tables?.length ? config.tables : config.collections;

    if (!entities || entities.length === 0) {
      return "// No tables or collections found in configuration\n";
    }

    const appwriteDep = this.getAppwriteDependency();
    const enums = this.generateEnums(entities);
    const types = entities
      .map((entity: Entity) => this.generateTableType(entity, entities))
      .join("\n\n");
    const entitiesByDb = this.groupEntitiesByDb(entities);
    const dbIds = Array.from(entitiesByDb.keys());
    const databaseIdType = dbIds.map((id) => `'${id}'`).join(" | ");

    const template = loadTemplate("types.ts.tpl");

    return renderTemplate(template, {
      appwriteDep,
      ENUMS: enums ? enums + "\n" : "",
      TYPES: types + "\n",
      QUERY_BUILDER_TYPE: this.generateQueryBuilderType(),
      databaseIdType,
      DATABASE_TABLES_TYPE: this.generateDatabaseTablesType(
        entitiesByDb,
        appwriteDep,
      ),
    });
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

  private generateTableIdMap(entitiesByDb: Map<string, Entity[]>): string {
    const lines: string[] = [
      "const tableIdMap: Record<string, Record<string, string>> = Object.create(null);",
    ];

    for (const [dbId, dbEntities] of entitiesByDb.entries()) {
      lines.push(`tableIdMap[${JSON.stringify(dbId)}] = Object.create(null);`);
      for (const entity of dbEntities) {
        lines.push(
          `tableIdMap[${JSON.stringify(dbId)}][${JSON.stringify(entity.name)}] = ${JSON.stringify(entity.$id)};`,
        );
      }
    }

    return lines.join("\n");
  }

  private generateTablesWithRelationships(
    entitiesByDb: Map<string, Entity[]>,
  ): string {
    const tablesWithRelationships: string[] = [];

    for (const [dbId, dbEntities] of entitiesByDb.entries()) {
      for (const entity of dbEntities) {
        if (this.hasRelationshipColumns(entity)) {
          tablesWithRelationships.push(`'${dbId}:${entity.name}'`);
        }
      }
    }

    if (tablesWithRelationships.length === 0) {
      return `const tablesWithRelationships = new Set<string>()`;
    }

    return `const tablesWithRelationships = new Set<string>([${tablesWithRelationships.join(", ")}])`;
  }

  private generateDatabasesFile(config: ConfigType): string {
    const entities = config.tables?.length ? config.tables : config.collections;

    if (!entities || entities.length === 0) {
      return "// No tables or collections found in configuration\n";
    }

    const entitiesByDb = this.groupEntitiesByDb(entities);
    const appwriteDep = this.getAppwriteDependency();
    const supportsBulk = this.supportsBulkMethods(appwriteDep);

    const bulkMethodsCode = supportsBulk
      ? `
    createMany: (rows: any[], options?: { transactionId?: string }) =>
      tablesDB.createRows({
        databaseId,
        tableId,
        rows,
        transactionId: options?.transactionId,
      }),
    updateMany: (data: any, options?: { queries?: (q: any) => string[]; transactionId?: string }) =>
      tablesDB.updateRows({
        databaseId,
        tableId,
        data,
        queries: options?.queries?.(createQueryBuilder()),
        transactionId: options?.transactionId,
      }),
    deleteMany: (options?: { queries?: (q: any) => string[]; transactionId?: string }) =>
      tablesDB.deleteRows({
        databaseId,
        tableId,
        queries: options?.queries?.(createQueryBuilder()),
        transactionId: options?.transactionId,
      }),`
      : "";

    const bulkCheck = supportsBulk
      ? `const hasBulkMethods = (dbId: string, tableName: string) => !tablesWithRelationships.has(\`\${dbId}:\${tableName}\`);`
      : "";

    const bulkRemoval = supportsBulk
      ? `
        // Remove bulk methods for tables with relationships
        if (!hasBulkMethods(databaseId, tableName)) {
          delete (api as any).createMany;
          delete (api as any).updateMany;
          delete (api as any).deleteMany;
        }`
      : "";

    const template = loadTemplate("databases.ts.tpl");

    return renderTemplate(template, {
      appwriteDep,
      QUERY_BUILDER_IMPL: this.generateQueryBuilder(),
      TABLE_ID_MAP: this.generateTableIdMap(entitiesByDb),
      TABLES_WITH_RELATIONSHIPS: this.generateTablesWithRelationships(
        entitiesByDb,
      ),
      BULK_METHODS: bulkMethodsCode,
      BULK_CHECK: bulkCheck,
      BULK_REMOVAL: bulkRemoval,
    });
  }

  private generateIndexFile(): string {
    const template = loadTemplate("index.ts.tpl");

    return renderTemplate(template, {
      sdkTitle: SDK_TITLE,
      executableName: EXECUTABLE_NAME,
    });
  }

  async generate(config: ConfigType): Promise<GenerateResult> {
    if (!config.projectId) {
      throw new Error("Project ID is required in configuration");
    }

    const files = new Map<string, string>();

    const hasEntities =
      (config.tables && config.tables.length > 0) ||
      (config.collections && config.collections.length > 0);

    if (!hasEntities) {
      console.log(
        "No tables or collections found in configuration. Skipping database generation.",
      );
      files.set(
        "databases.ts",
        "// No tables or collections found in configuration\n",
      );
      files.set(
        "types.ts",
        "// No tables or collections found in configuration\n",
      );
      files.set("index.ts", this.generateIndexFile());
      return { files };
    }

    files.set("types.ts", this.generateTypesFile(config));
    files.set("databases.ts", this.generateDatabasesFile(config));
    files.set("index.ts", this.generateIndexFile());

    return { files };
  }
}
