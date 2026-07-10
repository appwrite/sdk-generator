import { z } from "zod";

// ============================================================================
// Attribute Validations
// ============================================================================

/**
 * Validates that when 'required' is true, 'default' must be null
 */
export const validateRequiredDefault = (data: {
  required?: boolean;
  default?: unknown;
}) => {
  if (data.required === true && data.default !== null) {
    return false;
  }
  return true;
};

/**
 * Validates that string/varchar type attributes must have a size defined,
 * unless they have a format (email, url, ip, enum) which defines the size
 */
export const validateStringSize = (data: {
  type: string;
  size?: number | null;
  format?: string | null;
}) => {
  // Skip validation if not a string-like type that requires size
  if (data.type !== "string" && data.type !== "varchar") {
    return true;
  }

  // String columns with format don't need explicit size
  if (data.format && data.format !== "") {
    return true;
  }

  // Regular string columns need size
  if (data.size === undefined || data.size === null) {
    return false;
  }

  return true;
};

// ============================================================================
// Collection & Table Validations
// ============================================================================

interface AttributeOrColumn {
  key: string;
}

interface Index {
  key: string;
}

interface CollectionOrTableData {
  attributes?: AttributeOrColumn[];
  columns?: AttributeOrColumn[];
  indexes?: Index[];
}

/**
 * Validates duplicate keys in attributes/columns and indexes
 */
export const validateContainerDuplicates = (
  data: CollectionOrTableData,
  ctx: z.RefinementCtx,
) => {
  const items = data.attributes || data.columns || [];
  const itemType = data.attributes ? "Attribute" : "Column";
  const itemPath = data.attributes ? "attributes" : "columns";

  // Validate duplicate item keys
  if (items.length > 0) {
    const seenKeys = new Set<string>();

    items.forEach((item, index) => {
      if (seenKeys.has(item.key)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: `${itemType} with the key '${item.key}' already exists. ${itemType} keys must be unique, try again with a different key.`,
          path: [itemPath, index, "key"],
        });
      } else {
        seenKeys.add(item.key);
      }
    });
  }

  // Validate duplicate index keys
  if (data.indexes && data.indexes.length > 0) {
    const seenKeys = new Set<string>();

    data.indexes.forEach((index, indexPos) => {
      if (seenKeys.has(index.key)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: `Index with the key '${index.key}' already exists. Index keys must be unique, try again with a different key.`,
          path: ["indexes", indexPos, "key"],
        });
      } else {
        seenKeys.add(index.key);
      }
    });
  }
};

// ============================================================================
// Config Validations
// ============================================================================

interface RelationshipItem {
  key: string;
  type: string;
  relatedTable?: string;
  relatedCollection?: string;
}

interface ContainerWithDatabase {
  $id: string;
  name: string;
  databaseId: string;
  columns?: RelationshipItem[];
  attributes?: RelationshipItem[];
}

interface ConfigData {
  tables?: ContainerWithDatabase[];
  collections?: ContainerWithDatabase[];
}

/**
 * Validates cross-database relationships
 */
export const validateCrossDatabase = (
  data: ConfigData,
  ctx: z.RefinementCtx,
) => {
  // Validate cross-database relationships for tables
  if (data.tables && data.tables.length > 0) {
    // Build a map of table IDs to their database IDs
    const tableDatabaseMap = new Map<string, string>();
    for (const table of data.tables) {
      tableDatabaseMap.set(table.$id, table.databaseId);
    }

    // Check each table's relationship columns
    data.tables.forEach((table, tableIndex) => {
      const columns = table.columns || [];
      columns.forEach((column, columnIndex) => {
        if (column.type === "relationship" && column.relatedTable) {
          const relatedDatabaseId = tableDatabaseMap.get(column.relatedTable);
          if (relatedDatabaseId && relatedDatabaseId !== table.databaseId) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Invalid cross-database relationship: Table '${table.name}' (database: '${table.databaseId}') has relationship '${column.key}' pointing to table '${column.relatedTable}' which is in database '${relatedDatabaseId}'. Appwrite only supports relationships within the same database.`,
              path: [
                "tables",
                tableIndex,
                "columns",
                columnIndex,
                "relatedTable",
              ],
            });
          }
        }
      });
    });
  }

  // Validate cross-database relationships for collections (legacy)
  if (data.collections && data.collections.length > 0) {
    // Build a map of collection IDs to their database IDs
    const collectionDatabaseMap = new Map<string, string>();
    for (const collection of data.collections) {
      collectionDatabaseMap.set(collection.$id, collection.databaseId);
    }

    // Check each collection's relationship attributes
    data.collections.forEach((collection, collectionIndex) => {
      const attributes = collection.attributes || [];
      attributes.forEach((attribute, attributeIndex) => {
        if (attribute.type === "relationship" && attribute.relatedCollection) {
          const relatedDatabaseId = collectionDatabaseMap.get(
            attribute.relatedCollection,
          );
          if (
            relatedDatabaseId &&
            relatedDatabaseId !== collection.databaseId
          ) {
            ctx.addIssue({
              code: z.ZodIssueCode.custom,
              message: `Invalid cross-database relationship: Collection '${collection.name}' (database: '${collection.databaseId}') has relationship '${attribute.key}' pointing to collection '${attribute.relatedCollection}' which is in database '${relatedDatabaseId}'. Appwrite only supports relationships within the same database.`,
              path: [
                "collections",
                collectionIndex,
                "attributes",
                attributeIndex,
                "relatedCollection",
              ],
            });
          }
        }
      });
    });
  }
};
