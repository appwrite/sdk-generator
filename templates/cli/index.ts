/**
 * Library exports for programmatic use of the Appwrite CLI
 *
 * For CLI usage, run the 'appwrite' command directly.
 */

import { Push } from "./lib/commands/push.js";
import { Pull } from "./lib/commands/pull.js";
import { Schema } from "./lib/commands/schema.js";

export { Schema, Push, Pull };
export type {
  ConfigType,
  SettingsType,
  FunctionType,
  SiteType,
  DatabaseType,
  CollectionType,
  TableType,
  TopicType,
  TeamType,
  MessageType,
  BucketType,
  AttributeType,
  IndexType,
  ColumnType,
  TableIndexType,
} from "./lib/commands/config.js";
