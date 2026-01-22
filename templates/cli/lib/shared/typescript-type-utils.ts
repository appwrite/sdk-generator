import * as fs from "fs";
import * as path from "path";
import { LanguageMeta } from "../type-generation/languages/language.js";
import type {
  AttributeType,
  ColumnType,
  CollectionType,
  TableType,
} from "../commands/config.js";

/**
 * Union type for attributes from both collections and tables.
 */
export type TypeAttribute = AttributeType | ColumnType;

/**
 * Common entity interface (collection or table).
 */
export type TypeEntity = Pick<CollectionType | TableType, "$id" | "name">;

/**
 * Converts an attribute to its TypeScript type representation.
 *
 * @param attribute - The attribute to convert
 * @param entities - List of all entities for resolving relationships
 * @param entityName - Name of the entity containing this attribute (for enum naming)
 * @returns The TypeScript type string
 */
export function getTypeScriptType(
  attribute: TypeAttribute,
  entities: TypeEntity[],
  entityName: string,
): string {
  let type = "";

  switch (attribute.type) {
    case "string":
    case "datetime":
      type = "string";
      if (attribute.format === "enum") {
        type =
          LanguageMeta.toPascalCase(entityName) +
          LanguageMeta.toPascalCase(attribute.key);
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
      // AttributeType has relatedCollection, ColumnType has relatedTable
      const relatedId =
        ("relatedCollection" in attribute
          ? attribute.relatedCollection
          : undefined) ?? attribute.relatedTable;
      const relatedEntity = entities.find(
        (e) => e.$id === relatedId || e.name === relatedId,
      );
      if (!relatedEntity) {
        throw new Error(`Related entity with ID '${relatedId}' not found.`);
      }
      type = LanguageMeta.toPascalCase(relatedEntity.name);
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
    case "point":
      type = "Array<number>";
      break;
    case "linestring":
      type = "Array<Array<number>>";
      break;
    case "polygon":
      type = "Array<Array<Array<number>>>";
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

/**
 * Detects the Appwrite dependency to use based on the project's package.json or deno.json.
 *
 * @returns The appropriate Appwrite import path
 */
export function getAppwriteDependency(): string {
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

/**
 * Checks if the Appwrite dependency supports bulk methods.
 *
 * @param appwriteDep - The Appwrite dependency string
 * @returns True if bulk methods are supported
 */
export function supportsBulkMethods(appwriteDep: string): boolean {
  return (
    appwriteDep === "node-appwrite" ||
    appwriteDep === "npm:node-appwrite" ||
    appwriteDep === "@appwrite.io/console"
  );
}
