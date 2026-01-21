import * as fs from "fs";
import * as path from "path";

/**
 * Common attribute interface that works with both type-generation and databases generator.
 */
export interface TypeAttribute {
  key: string;
  type: string;
  required?: boolean;
  array?: boolean;
  default?: unknown;
  format?: string;
  elements?: string[];
  relatedCollection?: string;
  relatedTable?: string;
  relationType?: string;
  side?: string;
}

/**
 * Common entity interface (collection or table).
 */
export interface TypeEntity {
  $id: string;
  name: string;
}

/**
 * Converts a string to PascalCase.
 */
export function toPascalCase(str: string): string {
  return str
    .replace(/[^a-zA-Z0-9\s-_]/g, "")
    .replace(/([a-z])([A-Z])/g, "$1-$2")
    .replace(/([A-Z])([A-Z][a-z])/g, "$1-$2")
    .replace(/[_\s]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .replace(/--+/g, "-")
    .toLowerCase()
    .replace(/-([a-z0-9])/g, (g) => g[1].toUpperCase())
    .replace(/^./, (g) => g.toUpperCase());
}

/**
 * Converts a string to UPPER_SNAKE_CASE.
 */
export function toUpperSnakeCase(str: string): string {
  return str
    .replace(/[^a-zA-Z0-9\s-_]/g, "")
    .replace(/([a-z])([A-Z])/g, "$1-$2")
    .replace(/([A-Z])([A-Z][a-z])/g, "$1-$2")
    .replace(/[_\s]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .replace(/--+/g, "-")
    .toLowerCase()
    .replace(/-/g, "_")
    .toUpperCase();
}

/**
 * Sanitizes a string for use as an enum key.
 * Handles edge cases like strings starting with numbers or containing invalid characters.
 */
export function sanitizeEnumKey(value: string): string {
  let key = toUpperSnakeCase(value);

  if (!key || /^\d/.test(key)) {
    key = `_${key}`;
  }

  return key;
}

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
    case "email":
    case "ip":
    case "url":
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
      const relatedId = attribute.relatedCollection ?? attribute.relatedTable;
      const relatedEntity = entities.find(
        (e) => e.$id === relatedId || e.name === relatedId,
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
 * Generates TypeScript enum code for an attribute with enum format.
 *
 * @param entityName - Name of the entity containing the attribute
 * @param attributeKey - The attribute key
 * @param elements - The enum elements/values
 * @returns The TypeScript enum code string
 */
export function generateEnumCode(
  entityName: string,
  attributeKey: string,
  elements: string[],
): string {
  const enumName = toPascalCase(entityName) + toPascalCase(attributeKey);
  const usedKeys = new Set<string>();

  const enumValues = elements
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
      const isLast = index === elements.length - 1;
      return `    ${key} = ${JSON.stringify(element)}${isLast ? "" : ","}`;
    })
    .join("\n");

  return `export enum ${enumName} {\n${enumValues}\n}`;
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
