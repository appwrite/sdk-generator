import { z } from "zod";
import Handlebars from "handlebars";
import { ConfigType, AttributeSchema } from "../../config.js";
import { LanguageMeta } from "../../../type-generation/languages/language.js";
import { TypeScript } from "../../../type-generation/languages/typescript.js";
import { SDK_TITLE, EXECUTABLE_NAME } from "../../../constants.js";
import {
  BaseDatabasesGenerator,
  GenerateResult,
  SupportedLanguage,
} from "../base.js";
import {
  getTypeScriptType,
  getAppwriteDependency,
  supportsServerSideMethods,
  detectImportExtension,
  TypeAttribute,
  TypeEntity,
} from "../../../shared/typescript-type-utils.js";
import typesTemplateSource from "./templates/types.ts.hbs";
import databasesTemplateSource from "./templates/databases.ts.hbs";
import indexTemplateSource from "./templates/index.ts.hbs";
import constantsTemplateSource from "./templates/constants.ts.hbs";

type Entity =
  | NonNullable<ConfigType["tables"]>[number]
  | NonNullable<ConfigType["collections"]>[number];
type Entities =
  | NonNullable<ConfigType["tables"]>
  | NonNullable<ConfigType["collections"]>;

const typesTemplate = Handlebars.compile(String(typesTemplateSource));
const databasesTemplate = Handlebars.compile(String(databasesTemplateSource));
const indexTemplate = Handlebars.compile(String(indexTemplateSource));
const constantsTemplate = Handlebars.compile(String(constantsTemplateSource));

// Inline permission callback type for better IntelliSense
const PERMISSION_CALLBACK_INLINE = `(permission: { read: (role: RoleString) => string; write: (role: RoleString) => string; create: (role: RoleString) => string; update: (role: RoleString) => string; delete: (role: RoleString) => string }, role: { any: () => RoleString; user: (userId: string, status?: string) => RoleString; users: (status?: string) => RoleString; guests: () => RoleString; team: (teamId: string, role?: string) => RoleString; member: (memberId: string) => RoleString; label: (label: string) => RoleString }) => string[]`;

// Inline query callback type for better IntelliSense (with type parameter for field safety)
const getQueryCallbackInline = (typeName: string) =>
  `(q: { equal: <K extends QueryableKeys<${typeName}>>(field: K, value: ExtractQueryValue<${typeName}[K]>) => string; notEqual: <K extends QueryableKeys<${typeName}>>(field: K, value: ExtractQueryValue<${typeName}[K]>) => string; lessThan: <K extends QueryableKeys<${typeName}>>(field: K, value: ExtractQueryValue<${typeName}[K]>) => string; lessThanEqual: <K extends QueryableKeys<${typeName}>>(field: K, value: ExtractQueryValue<${typeName}[K]>) => string; greaterThan: <K extends QueryableKeys<${typeName}>>(field: K, value: ExtractQueryValue<${typeName}[K]>) => string; greaterThanEqual: <K extends QueryableKeys<${typeName}>>(field: K, value: ExtractQueryValue<${typeName}[K]>) => string; contains: <K extends QueryableKeys<${typeName}>>(field: K, value: ExtractQueryValue<${typeName}[K]>) => string; search: <K extends QueryableKeys<${typeName}>>(field: K, value: string) => string; isNull: <K extends QueryableKeys<${typeName}>>(field: K) => string; isNotNull: <K extends QueryableKeys<${typeName}>>(field: K) => string; startsWith: <K extends QueryableKeys<${typeName}>>(field: K, value: string) => string; endsWith: <K extends QueryableKeys<${typeName}>>(field: K, value: string) => string; between: <K extends QueryableKeys<${typeName}>>(field: K, start: ExtractQueryValue<${typeName}[K]>, end: ExtractQueryValue<${typeName}[K]>) => string; select: <K extends keyof ${typeName}>(fields: K[]) => string; orderAsc: <K extends keyof ${typeName}>(field: K) => string; orderDesc: <K extends keyof ${typeName}>(field: K) => string; limit: (value: number) => string; offset: (value: number) => string; cursorAfter: (documentId: string) => string; cursorBefore: (documentId: string) => string; or: (...queries: string[]) => string; and: (...queries: string[]) => string }) => string[]`;

/**
 * TypeScript-specific database generator.
 * Generates type-safe SDK files for TypeScript/JavaScript projects.
 */
export class TypeScriptDatabasesGenerator extends BaseDatabasesGenerator {
  readonly language: SupportedLanguage = "typescript";
  readonly fileExtension = "ts";
  private readonly meta = new TypeScript();
  private serverSideOverride: "auto" | "true" | "false" = "auto";

  setServerSideOverride(override: "auto" | "true" | "false"): void {
    this.serverSideOverride = override;
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
   */
  private hasRelationshipColumns(entity: Entity): boolean {
    const fields = this.getFields(entity);
    if (!fields) return false;
    return fields.some((field) => field.type === "relationship");
  }

  private generateTableType(entity: Entity, entities: Entities): string {
    const fields = this.getFields(entity);
    if (!fields) return "";

    const typeName = LanguageMeta.toPascalCase(entity.name);
    const typeEntities: TypeEntity[] = entities
      .filter((e) => e.databaseId === entity.databaseId)
      .map((e) => ({
        $id: e.$id,
        name: e.name,
      }));

    // Build attributes for Create type (input) - relationships use Create suffix
    const createAttributes = this.buildAttributes(
      entity,
      typeEntities,
      "    ",
      true,
    );
    // Build attributes for Row type (output) - relationships use full type
    const rowAttributes = this.buildAttributes(
      entity,
      typeEntities,
      "    ",
      false,
    );

    const createType =
      createAttributes.trim().length === 0
        ? `export type ${typeName}Create = Record<string, never>`
        : `export type ${typeName}Create = {\n${createAttributes}\n}`;

    const rowType =
      rowAttributes.trim().length === 0
        ? `export type ${typeName} = Models.Row`
        : `export type ${typeName} = Models.Row & {\n${rowAttributes}\n}`;

    return `${createType}\n\n${rowType}`;
  }

  private buildAttributes(
    entity: Entity,
    typeEntities: TypeEntity[],
    indent: string,
    forCreate: boolean = false,
  ): string {
    const fields = this.getFields(entity);
    if (!fields) return "";

    return fields
      .map((attr) => {
        const typeAttr: TypeAttribute = {
          key: attr.key,
          type: attr.type,
          required: attr.required,
          array: attr.array,
          default: attr.default,
          format: attr.format,
          elements: attr.elements,
          relatedCollection: attr.relatedCollection,
          relatedTable: attr.relatedTable,
          relationType: attr.relationType,
          side: attr.side,
        };
        return `${indent}${JSON.stringify(attr.key)}${attr.required ? "" : "?"}: ${getTypeScriptType(typeAttr, typeEntities, entity.name, forCreate)};`;
      })
      .join("\n");
  }

  private generateEnums(entities: Entities): string {
    const enumTypes: string[] = [];

    for (const entity of entities) {
      const fields = this.getFields(entity);
      if (!fields) continue;

      for (const field of fields) {
        if (field.format === "enum" && field.elements) {
          const enumDef = this.meta.generateEnum(
            entity.name,
            field.key,
            field.elements,
          );
          const enumValues = enumDef.members
            .map((member, index) => {
              const isLast = index === enumDef.members.length - 1;
              return `    ${member.key} = ${JSON.stringify(member.value)}${isLast ? "" : ","}`;
            })
            .join("\n");
          enumTypes.push(`export enum ${enumDef.name} {\n${enumValues}\n}`);
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

  private generateDatabaseTablesType(
    entitiesByDb: Map<string, Entity[]>,
    appwriteDep: string,
  ): string {
    const supportsServerSide = supportsServerSideMethods(
      appwriteDep,
      this.serverSideOverride,
    );
    const dbReturnTypes = Array.from(entitiesByDb.entries())
      .map(([dbId, dbEntities]) => {
        const tableTypes = dbEntities
          .map((entity) => {
            const typeName = LanguageMeta.toPascalCase(entity.name);
            const typeEntities: TypeEntity[] = entitiesByDb
              .get(entity.databaseId)!
              .map((e) => ({
                $id: e.$id,
                name: e.name,
              }));
            const createFields = this.buildAttributes(
              entity,
              typeEntities,
              "        ",
              true, // forCreate - relationships use Create suffix
            );
            const createInline =
              createFields.trim().length === 0
                ? "Record<string, never>"
                : `{\n${createFields}\n      }`;
            const queryCallback = getQueryCallbackInline(typeName);
            const baseMethods = `      create: (data: ${createInline}, options?: { rowId?: string; permissions?: ${PERMISSION_CALLBACK_INLINE}; transactionId?: string }) => Promise<${typeName}>;
      get: (id: string) => Promise<${typeName}>;
      update: (id: string, data: Partial<${createInline}>, options?: { permissions?: ${PERMISSION_CALLBACK_INLINE}; transactionId?: string }) => Promise<${typeName}>;
      delete: (id: string, options?: { transactionId?: string }) => Promise<void>;
      list: (options?: { queries?: ${queryCallback} }) => Promise<{ total: number; rows: ${typeName}[] }>;`;

            const canUseBulkMethods =
              supportsServerSide && !this.hasRelationshipColumns(entity);
            const bulkMethods = canUseBulkMethods
              ? `
      createMany: (rows: Array<${createInline} & { $id?: string; $permissions?: string[] }>, options?: { transactionId?: string }) => Promise<{ total: number; rows: ${typeName}[] }>;
      updateMany: (data: Partial<${createInline}>, options?: { queries?: ${queryCallback}; transactionId?: string }) => Promise<{ total: number; rows: ${typeName}[] }>;
      deleteMany: (options?: { queries?: ${queryCallback}; transactionId?: string }) => Promise<{ total: number; rows: ${typeName}[] }>;`
              : "";

            return `    ${JSON.stringify(entity.name)}: {\n${baseMethods}${bulkMethods}\n    }`;
          })
          .join(";\n");
        return `  ${JSON.stringify(dbId)}: {\n${tableTypes}\n  }`;
      })
      .join(";\n");

    return `export type DatabaseTableMap = {\n${dbReturnTypes}\n};

export type DatabaseHandle<D extends DatabaseId> = {
  use: <T extends keyof DatabaseTableMap[D] & string>(tableId: T) => DatabaseTableMap[D][T];
${
  supportsServerSide
    ? `  create: (tableId: string, name: string, options?: { permissions?: ${PERMISSION_CALLBACK_INLINE}; rowSecurity?: boolean; enabled?: boolean; columns?: any[]; indexes?: any[] }) => Promise<Models.Table>;
  update: <T extends keyof DatabaseTableMap[D] & string>(tableId: T, options?: { name?: string; permissions?: ${PERMISSION_CALLBACK_INLINE}; rowSecurity?: boolean; enabled?: boolean }) => Promise<Models.Table>;
  delete: <T extends keyof DatabaseTableMap[D] & string>(tableId: T) => Promise<void>;`
    : ""
}
};

export type DatabaseTables = {
  use: <D extends DatabaseId>(databaseId: D) => DatabaseHandle<D>;
${
  supportsServerSide
    ? `  create: (databaseId: string, name: string, options?: { enabled?: boolean }) => Promise<Models.Database>;
  update: <D extends DatabaseId>(databaseId: D, options?: { name?: string; enabled?: boolean }) => Promise<Models.Database>;
  delete: <D extends DatabaseId>(databaseId: D) => Promise<void>;`
    : ""
}
};`;
  }

  private generateTypesFile(config: ConfigType): string {
    const entities = config.tables?.length ? config.tables : config.collections;

    if (!entities || entities.length === 0) {
      return "// No tables or collections found in configuration\n";
    }

    const appwriteDep = getAppwriteDependency();
    const enums = this.generateEnums(entities);
    const types = entities
      .map((entity: Entity) => this.generateTableType(entity, entities))
      .join("\n\n");
    const entitiesByDb = this.groupEntitiesByDb(entities);
    const dbIds = Array.from(entitiesByDb.keys());
    const databaseIdType = dbIds.map((id) => JSON.stringify(id)).join(" | ");

    return typesTemplate({
      appwriteDep,
      ENUMS: enums ? enums + "\n" : "",
      TYPES: types + "\n",
      databaseIdType,
      DATABASE_TABLES_TYPE: this.generateDatabaseTablesType(
        entitiesByDb,
        appwriteDep,
      ),
    });
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
          tablesWithRelationships.push(
            JSON.stringify(`${dbId}:${entity.name}`),
          );
        }
      }
    }

    if (tablesWithRelationships.length === 0) {
      return `const tablesWithRelationships = new Set<string>();`;
    }

    return `const tablesWithRelationships = new Set<string>([${tablesWithRelationships.join(", ")}]);`;
  }

  private generateBulkMethods(supportsBulk: boolean): string {
    if (!supportsBulk) return "";

    return `
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
      }),`;
  }

  private generateBulkCheck(supportsBulk: boolean): string {
    if (!supportsBulk) return "";
    return `const hasBulkMethods = (dbId: string, tableId: string) => !tablesWithRelationships.has(\`\${dbId}:\${tableId}\`);\n`;
  }

  private generateBulkRemoval(supportsBulk: boolean): string {
    if (!supportsBulk) return "";
    return `
        // Remove bulk methods for tables with relationships
        if (!hasBulkMethods(databaseId, tableId)) {
          delete (api as any).createMany;
          delete (api as any).updateMany;
          delete (api as any).deleteMany;
        }`;
  }

  private generateDatabasesFile(config: ConfigType, importExt: string): string {
    const entities = config.tables?.length ? config.tables : config.collections;

    if (!entities || entities.length === 0) {
      return "// No tables or collections found in configuration\n";
    }

    const entitiesByDb = this.groupEntitiesByDb(entities);
    const appwriteDep = getAppwriteDependency();
    const supportsServerSide = supportsServerSideMethods(
      appwriteDep,
      this.serverSideOverride,
    );

    return databasesTemplate({
      appwriteDep,
      supportsServerSide,
      importExt,
      TABLE_ID_MAP: this.generateTableIdMap(entitiesByDb),
      TABLES_WITH_RELATIONSHIPS:
        this.generateTablesWithRelationships(entitiesByDb),
      BULK_METHODS: this.generateBulkMethods(supportsServerSide),
      BULK_CHECK: this.generateBulkCheck(supportsServerSide),
      BULK_REMOVAL: this.generateBulkRemoval(supportsServerSide),
    });
  }

  private generateIndexFile(importExt: string): string {
    return indexTemplate({
      sdkTitle: SDK_TITLE,
      executableName: EXECUTABLE_NAME,
      importExt,
    });
  }

  private generateConstantsFile(config: ConfigType): string {
    const appwriteDep = getAppwriteDependency();
    const supportsServerSide = supportsServerSideMethods(
      appwriteDep,
      this.serverSideOverride,
    );

    return constantsTemplate({
      sdkTitle: SDK_TITLE,
      projectId: config.projectId,
      endpoint: config.endpoint ?? "",
      requiresApiKey: supportsServerSide,
    });
  }

  async generate(config: ConfigType): Promise<GenerateResult> {
    if (!config.projectId) {
      throw new Error("Project ID is required in configuration");
    }

    const importExt = detectImportExtension();
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
      files.set("index.ts", this.generateIndexFile(importExt));
      files.set("constants.ts", this.generateConstantsFile(config));
      return { files };
    }

    files.set("types.ts", this.generateTypesFile(config));
    files.set("databases.ts", this.generateDatabasesFile(config, importExt));
    files.set("index.ts", this.generateIndexFile(importExt));
    files.set("constants.ts", this.generateConstantsFile(config));

    return { files };
  }
}
