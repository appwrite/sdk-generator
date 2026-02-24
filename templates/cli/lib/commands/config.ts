import { z } from "zod";
import {
  validateRequiredDefault,
  validateStringSize,
  validateContainerDuplicates,
  validateCrossDatabase,
} from "./config-validations.js";

// ============================================================================
// Internal Helpers
// ============================================================================

const INT64_MIN = BigInt("-9223372036854775808");
const INT64_MAX = BigInt("9223372036854775807");

const int64Schema = z.preprocess(
  (val) => {
    if (typeof val === "bigint") {
      return val;
    }

    if (typeof val === "object" && val !== null) {
      if (typeof val.valueOf === "function") {
        try {
          const valueOfResult = val.valueOf();
          const bigIntVal = BigInt(valueOfResult as string | number | bigint);
          return bigIntVal;
        } catch (e) {
          return undefined;
        }
      }

      const num = Number(val);
      return !isNaN(num) ? BigInt(Math.trunc(num)) : undefined;
    }

    if (typeof val === "string") {
      try {
        return BigInt(val);
      } catch (e) {
        return undefined;
      }
    }

    if (typeof val === "number") {
      return BigInt(Math.trunc(val));
    }

    return val;
  },
  z
    .bigint()
    .nullable()
    .optional()
    .superRefine((val, ctx) => {
      if (val === undefined || val === null) return;

      if (val < INT64_MIN || val > INT64_MAX) {
        ctx.addIssue({
          code: "custom",
          message: `Value must be between ${INT64_MIN} and ${INT64_MAX} (64-bit signed integer range)`,
        });
      }
    }),
);

const MockNumberSchema = z
  .object({
    phone: z.string(),
    otp: z.string(),
  })
  .strict();

// ============================================================================
// Project Settings
// ============================================================================

const SettingsSchema = z
  .object({
    services: z
      .object({
        account: z.boolean().optional(),
        avatars: z.boolean().optional(),
        databases: z.boolean().optional(),
        locale: z.boolean().optional(),
        health: z.boolean().optional(),
        storage: z.boolean().optional(),
        teams: z.boolean().optional(),
        users: z.boolean().optional(),
        sites: z.boolean().optional(),
        functions: z.boolean().optional(),
        graphql: z.boolean().optional(),
        messaging: z.boolean().optional(),
      })
      .strict()
      .optional(),
    auth: z
      .object({
        methods: z
          .object({
            jwt: z.boolean().optional(),
            phone: z.boolean().optional(),
            invites: z.boolean().optional(),
            anonymous: z.boolean().optional(),
            "email-otp": z.boolean().optional(),
            "magic-url": z.boolean().optional(),
            "email-password": z.boolean().optional(),
          })
          .strict()
          .optional(),
        security: z
          .object({
            duration: z.union([z.number(), z.bigint()]).optional(),
            limit: z.union([z.number(), z.bigint()]).optional(),
            sessionsLimit: z.union([z.number(), z.bigint()]).optional(),
            passwordHistory: z.union([z.number(), z.bigint()]).optional(),
            passwordDictionary: z.boolean().optional(),
            personalDataCheck: z.boolean().optional(),
            sessionAlerts: z.boolean().optional(),
            mockNumbers: z.array(MockNumberSchema).optional(),
          })
          .strict()
          .optional(),
      })
      .strict()
      .optional(),
  })
  .strict();

// ============================================================================
// Functions and Sites
// ============================================================================

const SiteSchema = z
  .object({
    path: z.string().optional(),
    $id: z.string(),
    name: z.string(),
    enabled: z.boolean().optional(),
    logging: z.boolean().optional(),
    timeout: z.union([z.number(), z.bigint()]).optional(),
    framework: z.string().optional(),
    buildRuntime: z.string().optional(),
    adapter: z.string().optional(),
    installCommand: z.string().optional(),
    buildCommand: z.string().optional(),
    outputDirectory: z.string().optional(),
    fallbackFile: z.string().optional(),
    specification: z.string().optional(),
    vars: z.record(z.string(), z.string()).optional(),
    ignore: z.string().optional(),
  })
  .strict();

const FunctionSchema = z
  .object({
    path: z.string().optional(),
    $id: z.string(),
    execute: z.array(z.string()).optional(),
    name: z.string(),
    enabled: z.boolean().optional(),
    logging: z.boolean().optional(),
    runtime: z.string(),
    specification: z.string().optional(),
    scopes: z.array(z.string()).optional(),
    events: z.array(z.string()).optional(),
    schedule: z.string().optional(),
    timeout: z.union([z.number(), z.bigint()]).optional(),
    entrypoint: z.string().optional(),
    commands: z.string().optional(),
    vars: z.record(z.string(), z.string()).optional(),
    ignore: z.string().optional(),
  })
  .strict();

// ============================================================================
// Databases
// ============================================================================

const DatabaseSchema = z
  .object({
    $id: z.string(),
    name: z.string(),
    enabled: z.boolean().optional(),
  })
  .strict();

// ============================================================================
// Collections (legacy)
// ============================================================================

const AttributeSchema = z
  .object({
    key: z.string(),
    type: z.enum([
      "string",
      "text",
      "varchar",
      "mediumtext",
      "longtext",
      "integer",
      "double",
      "boolean",
      "datetime",
      "relationship",
      "linestring",
      "point",
      "polygon",
    ]),
    required: z.boolean().optional(),
    array: z.boolean().optional(),
    size: z.number().optional(),
    default: z.any().optional(),
    min: int64Schema,
    max: int64Schema,
    format: z
      .union([
        z.enum(["email", "enum", "url", "ip", "datetime"]),
        z.literal(""),
      ])
      .optional(),
    elements: z.array(z.string()).optional(),
    relatedCollection: z.string().optional(),
    relatedTable: z.string().optional(),
    relationType: z.string().optional(),
    twoWay: z.boolean().optional(),
    twoWayKey: z.string().optional(),
    onDelete: z.string().optional(),
    side: z.string().optional(),
    attributes: z.array(z.string()).optional(),
    orders: z.array(z.string()).optional(),
    encrypt: z.boolean().optional(),
  })
  .strict()
  .refine(validateRequiredDefault, {
    message: "When 'required' is true, 'default' must be null",
    path: ["default"],
  })
  .refine(validateStringSize, {
    message: "When 'type' is 'string' or 'varchar', 'size' must be defined",
    path: ["size"],
  });

const IndexSchema = z
  .object({
    key: z.string(),
    type: z.string(),
    status: z.string().optional(),
    attributes: z.array(z.string()),
    orders: z.array(z.string()).optional(),
  })
  .strict();

const CollectionSchema = z
  .object({
    $id: z.string(),
    $permissions: z.array(z.string()).optional(),
    databaseId: z.string(),
    name: z.string(),
    enabled: z.boolean().optional(),
    documentSecurity: z.boolean().default(true),
    attributes: z.array(AttributeSchema).optional(),
    indexes: z.array(IndexSchema).optional(),
  })
  .strict()
  .superRefine(validateContainerDuplicates);

// ============================================================================
// Tables
// ============================================================================

const ColumnSchema = z
  .object({
    key: z.string(),
    type: z.enum([
      "string",
      "text",
      "varchar",
      "mediumtext",
      "longtext",
      "integer",
      "double",
      "boolean",
      "datetime",
      "relationship",
      "linestring",
      "point",
      "polygon",
    ]),
    required: z.boolean().optional(),
    array: z.boolean().optional(),
    size: z.number().optional(),
    default: z.any().optional(),
    min: z.union([z.number(), int64Schema]),
    max: z.union([z.number(), int64Schema]),
    format: z
      .union([
        z.enum(["email", "enum", "url", "ip", "datetime"]),
        z.literal(""),
      ])
      .optional(),
    elements: z.array(z.string()).optional(),
    // For tables, relationship uses relatedTable instead of relatedCollection
    relatedTable: z.string().optional(),
    relationType: z.string().optional(),
    twoWay: z.boolean().optional(),
    twoWayKey: z.string().optional(),
    onDelete: z.string().optional(),
    side: z.string().optional(),
    // For table indexes, uses columns instead of attributes
    columns: z.array(z.string()).optional(),
    orders: z.array(z.string()).optional(),
    encrypt: z.boolean().nullable().optional(),
  })
  .strict()
  .refine(validateRequiredDefault, {
    message: "When 'required' is true, 'default' must be null",
    path: ["default"],
  })
  .refine(validateStringSize, {
    message: "When 'type' is 'string' or 'varchar', 'size' must be defined",
    path: ["size"],
  });

const IndexTableSchema = z
  .object({
    key: z.string(),
    type: z.string(),
    status: z.string().optional(),
    columns: z.array(z.string()),
    orders: z.array(z.string()).optional(),
  })
  .strict();

const TableSchema = z
  .object({
    $id: z.string(),
    $permissions: z.array(z.string()).optional(),
    databaseId: z.string(),
    name: z.string(),
    enabled: z.boolean().optional(),
    rowSecurity: z.boolean().default(true),
    columns: z.array(ColumnSchema).optional(),
    indexes: z.array(IndexTableSchema).optional(),
  })
  .strict()
  .superRefine(validateContainerDuplicates);

// ============================================================================
// Topics
// ============================================================================

const TopicSchema = z
  .object({
    $id: z.string(),
    name: z.string(),
    subscribe: z.array(z.string()).optional(),
  })
  .strict();

// ============================================================================
// Teams
// ============================================================================

const TeamSchema = z
  .object({
    $id: z.string(),
    name: z.string(),
  })
  .strict();

// ============================================================================
// Messages
// ============================================================================

const MessageSchema = z
  .object({
    $id: z.string(),
    name: z.string(),
    emailTotal: z.number().optional(),
    smsTotal: z.number().optional(),
    pushTotal: z.number().optional(),
    subscribe: z.array(z.string()).optional(),
  })
  .strict();

// ============================================================================
// Buckets
// ============================================================================

const BucketSchema = z
  .object({
    $id: z.string(),
    $permissions: z.array(z.string()).optional(),
    fileSecurity: z.boolean().optional(),
    name: z.string(),
    enabled: z.boolean().optional(),
    maximumFileSize: z.number().optional(),
    allowedFileExtensions: z.array(z.string()).optional(),
    compression: z.string().optional(),
    encryption: z.boolean().optional(),
    antivirus: z.boolean().optional(),
  })
  .strict();

// ============================================================================
// Config Schema
// ============================================================================

const ConfigSchema = z
  .object({
    projectId: z.string(),
    projectName: z.string().optional(),
    endpoint: z.string().optional(),
    settings: z.lazy(() => SettingsSchema).optional(),
    functions: z.array(z.lazy(() => FunctionSchema)).optional(),
    sites: z.array(z.lazy(() => SiteSchema)).optional(),
    databases: z.array(z.lazy(() => DatabaseSchema)).optional(),
    collections: z.array(z.lazy(() => CollectionSchema)).optional(),
    tablesDB: z.array(z.lazy(() => DatabaseSchema)).optional(),
    tables: z.array(z.lazy(() => TableSchema)).optional(),
    topics: z.array(z.lazy(() => TopicSchema)).optional(),
    teams: z.array(z.lazy(() => TeamSchema)).optional(),
    buckets: z.array(z.lazy(() => BucketSchema)).optional(),
    messages: z.array(z.lazy(() => MessageSchema)).optional(),
  })
  .strict()
  .superRefine(validateCrossDatabase);

// ============================================================================
// Type Exports
// ============================================================================

export type ConfigType = z.infer<typeof ConfigSchema>;
export type SettingsType = z.infer<typeof SettingsSchema>;
export type SiteType = z.infer<typeof SiteSchema>;
export type FunctionType = z.infer<typeof FunctionSchema>;
export type DatabaseType = z.infer<typeof DatabaseSchema>;
export type CollectionType = z.infer<typeof CollectionSchema>;
export type AttributeType = z.infer<typeof AttributeSchema>;
export type IndexType = z.infer<typeof IndexSchema>;
export type TableType = z.infer<typeof TableSchema>;
export type ColumnType = z.infer<typeof ColumnSchema>;
export type TableIndexType = z.infer<typeof IndexTableSchema>;
export type TopicType = z.infer<typeof TopicSchema>;
export type TeamType = z.infer<typeof TeamSchema>;
export type MessageType = z.infer<typeof MessageSchema>;
export type BucketType = z.infer<typeof BucketSchema>;

// ============================================================================
// Schema Exports
// ============================================================================

export {
  /** Config */
  ConfigSchema,

  /** Project Settings */
  SettingsSchema,

  /** Functions and Sites */
  SiteSchema,
  FunctionSchema,

  /** Databases */
  DatabaseSchema,

  /** Collections (legacy) */
  CollectionSchema,
  AttributeSchema,
  IndexSchema,

  /** Tables */
  TableSchema,
  ColumnSchema,
  IndexTableSchema,

  /** Topics */
  TopicSchema,

  /** Teams */
  TeamSchema,

  /** Messages */
  MessageSchema,

  /** Buckets */
  BucketSchema,
};
