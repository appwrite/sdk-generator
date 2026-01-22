import { LanguageMeta } from "../type-generation/languages/language.js";

/**
 * Converts an arbitrary enum value into a valid, uppercase enum identifier
 * for languages that follow CONSTANT_CASE enum keys (e.g., TypeScript, Dart).
 * Ensures the identifier does not start with a digit and strips invalid characters.
 */
function sanitizeEnumKey(value: string): string {
  let key = LanguageMeta.toUpperSnakeCase(value);

  if (!key || /^\d/.test(key)) {
    key = `_${key}`;
  }

  return key;
}

/**
 * The normalized enum key and its original string value.
 */
export type EnumMember = { key: string; value: string };

/**
 * Produces Dart enum members with unique, sanitized keys.
 * When collisions occur after sanitization, appends a numeric suffix.
 */
export function generateDartEnumMembers(elements: string[]): EnumMember[] {
  const usedKeys = new Set<string>();

  return elements.map((element) => {
    let key = sanitizeEnumKey(element);
    if (usedKeys.has(key)) {
      let disambiguator = 1;
      while (usedKeys.has(`${key}_${disambiguator}`)) {
        disambiguator++;
      }
      key = `${key}_${disambiguator}`;
    }
    usedKeys.add(key);
    return { key, value: element };
  });
}

/**
 * Generates a full TypeScript enum declaration string for the provided elements.
 */
export function generateTypeScriptEnumCode(
  entityName: string,
  attributeKey: string,
  elements: string[],
): string {
  const enumName =
    LanguageMeta.toPascalCase(entityName) +
    LanguageMeta.toPascalCase(attributeKey);

  const enumValues = generateDartEnumMembers(elements)
    .map((member, index) => {
      const isLast = index === elements.length - 1;
      return `    ${member.key} = ${JSON.stringify(member.value)}${isLast ? "" : ","}`;
    })
    .join("\n");

  return `export enum ${enumName} {\n${enumValues}\n}`;
}
