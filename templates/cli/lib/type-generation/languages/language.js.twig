/** @typedef {import('../attribute').Attribute} Attribute */
/** @typedef {import('../collection').Collection} Collection */

const fs = require("fs");
const path = require("path");

class LanguageMeta {
  constructor() {
    if (new.target === LanguageMeta) {
      throw new TypeError("Abstract classes can't be instantiated.");
    }
  }

  static toKebabCase(string) {
    return string
      .replace(/[^a-zA-Z0-9\s-_]/g, "") // Remove invalid characters
      .replace(/([a-z])([A-Z])/g, "$1-$2") // Add hyphen between camelCase
      .replace(/([A-Z])([A-Z][a-z])/g, "$1-$2") // Add hyphen between PascalCase
      .replace(/[_\s]+/g, "-") // Replace spaces and underscores with hyphens
      .replace(/^-+|-+$/g, "") // Remove leading and trailing hyphens
      .replace(/--+/g, "-") // Replace multiple hyphens with a single hyphen
      .toLowerCase();
  }

  static toSnakeCase(string) {
    return this.toKebabCase(string).replace(/-/g, "_");
  }

  static toUpperSnakeCase(string) {
    return this.toSnakeCase(string).toUpperCase();
  }

  static toCamelCase(string) {
    return this.toKebabCase(string).replace(/-([a-z0-9])/g, (g) =>
      g[1].toUpperCase()
    );
  }

  static toPascalCase(string) {
    return this.toCamelCase(string).replace(/^./, (g) => g.toUpperCase());
  }

  /**
   * Get the type literal of the given attribute.
   *
   * @abstract
   * @param {Attribute} attribute
   * @return {string}
   */
  getType(attribute) {
    throw new TypeError("Stub.");
  }

  /**
   * Returns true if the language uses a single file for all types.
   *
   * @returns {boolean}
   */
  isSingleFile() {
    return false;
  }

  /**
   * Get the EJS template used to generate the types for this language.
   *
   * @abstract
   * @returns {string}
   */
  getTemplate() {
    throw new TypeError("Stub.");
  }

  /**
   * Get the file extension used by files of this language.
   *
   * @abstract
   * @param {Collection|undefined} collection
   * @returns {string}
   */
  getFileName(collection) {
    throw new TypeError("Stub.");
  }
}

const existsFiles = (...files) =>
  files.some((file) => fs.existsSync(path.join(process.cwd(), file)));

/**
 * @returns {string}
 */
function detectLanguage() {
  if (existsFiles("tsconfig.json", "deno.json")) {
    return "ts";
  }
  if (existsFiles("package.json")) {
    return "js";
  }
  if (existsFiles("composer.json")) {
    return "php";
  }
  if (existsFiles("requirements.txt", "Pipfile", "pyproject.toml")) {
    return "python";
  }
  if (existsFiles("Gemfile", "Rakefile")) {
    return "ruby";
  }
  if (existsFiles("build.gradle.kts")) {
    return "kotlin";
  }
  if (existsFiles("build.gradle", "pom.xml")) {
    return "java";
  }
  if (existsFiles("*.csproj")) {
    return "dotnet";
  }
  if (existsFiles("Package.swift")) {
    return "swift";
  }
  if (existsFiles("pubspec.yaml")) {
    return "dart";
  }
  throw new Error("Could not detect language, please specify with -l");
}

module.exports = { LanguageMeta, detectLanguage };
