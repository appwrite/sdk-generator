import { z } from "zod";
import Handlebars from "handlebars";
import { ConfigType, AttributeSchema } from "../../config.js";
import { LanguageMeta } from "../../../type-generation/languages/language.js";
import { SDK_TITLE, EXECUTABLE_NAME } from "../../../constants.js";
import {
  BaseDatabasesGenerator,
  GenerateResult,
  SupportedLanguage,
} from "../base.js";
import {
  getTypeScriptType,
  getAppwriteDependency,
  supportsBulkMethods,
  generateEnumCode,
  TypeAttribute,
  TypeEntity,
} from "../../../shared/typescript-type-utils.js";
import typesTemplateSource from "./templates/types.ts.hbs";
import databasesTemplateSource from "./templates/databases.ts.hbs";
import indexTemplateSource from "./templates/index.ts.hbs";

type Entity =
  | NonNullable<ConfigType["tables"]>[number]
  | NonNullable<ConfigType["collections"]>[number];
type Entities =
  | NonNullable<ConfigType["tables"]>
  | NonNullable<ConfigType["collections"]>;

const typesTemplate = Handlebars.compile(String(typesTemplateSource));
const databasesTemplate = Handlebars.compile(String(databasesTemplateSource));
const indexTemplate = Handlebars.compile(String(indexTemplateSource));

/**
 * TypeScript-specific database generator.
 * Generates type-safe SDK files for TypeScript/JavaScript projects.
 */
export class TypeScriptDatabasesGenerator extends BaseDatabasesGenerator {
  readonly language: SupportedLanguage = "typescript";
  readonly fileExtension = "ts";

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
    const typeEntities: TypeEntity[] = entities.map((e) => ({
      $id: e.$id,
      name: e.name,
    }));

    const attributes = fields
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
        return `    ${JSON.stringify(attr.key)}${attr.required ? "" : "?"}: ${getTypeScriptType(typeAttr, typeEntities, entity.name)};`;
      })
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
          enumTypes.push(
            generateEnumCode(entity.name, field.key, field.elements),
          );
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
    const supportsBulk = supportsBulkMethods(appwriteDep);
    const dbReturnTypes = Array.from(entitiesByDb.entries())
      .map(([dbId, dbEntities]) => {
        const tableTypes = dbEntities
          .map((entity) => {
            const typeName = LanguageMeta.toPascalCase(entity.name);
            const baseMethods = `      create: (data: Omit<${typeName}, keyof Models.Row>, options?: { rowId?: string; permissions?: Permission[]; transactionId?: string }) => Promise<${typeName}>;
      get: (id: string) => Promise<${typeName}>;
      update: (id: string, data: Partial<Omit<${typeName}, keyof Models.Row>>, options?: { permissions?: Permission[]; transactionId?: string }) => Promise<${typeName}>;
      delete: (id: string, options?: { transactionId?: string }) => Promise<void>;
      list: (options?: { queries?: (q: QueryBuilder<${typeName}>) => string[] }) => Promise<{ total: number; rows: ${typeName}[] }>;`;

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

    const appwriteDep = getAppwriteDependency();
    const enums = this.generateEnums(entities);
    const types = entities
      .map((entity: Entity) => this.generateTableType(entity, entities))
      .join("\n\n");
    const entitiesByDb = this.groupEntitiesByDb(entities);
    const dbIds = Array.from(entitiesByDb.keys());
    const databaseIdType = dbIds.map((id) => `'${id}'`).join(" | ");

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
          tablesWithRelationships.push(`'${dbId}:${entity.name}'`);
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
    return `const hasBulkMethods = (dbId: string, tableName: string) => !tablesWithRelationships.has(\`\${dbId}:\${tableName}\`);\n`;
  }

  private generateBulkRemoval(supportsBulk: boolean): string {
    if (!supportsBulk) return "";
    return `
        // Remove bulk methods for tables with relationships
        if (!hasBulkMethods(databaseId, tableName)) {
          delete (api as any).createMany;
          delete (api as any).updateMany;
          delete (api as any).deleteMany;
        }`;
  }

  private generateDatabasesFile(config: ConfigType): string {
    const entities = config.tables?.length ? config.tables : config.collections;

    if (!entities || entities.length === 0) {
      return "// No tables or collections found in configuration\n";
    }

    const entitiesByDb = this.groupEntitiesByDb(entities);
    const appwriteDep = getAppwriteDependency();
    const supportsBulk = supportsBulkMethods(appwriteDep);

    return databasesTemplate({
      appwriteDep,
      TABLE_ID_MAP: this.generateTableIdMap(entitiesByDb),
      TABLES_WITH_RELATIONSHIPS:
        this.generateTablesWithRelationships(entitiesByDb),
      BULK_METHODS: this.generateBulkMethods(supportsBulk),
      BULK_CHECK: this.generateBulkCheck(supportsBulk),
      BULK_REMOVAL: this.generateBulkRemoval(supportsBulk),
    });
  }

  private generateIndexFile(): string {
    return indexTemplate({
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
