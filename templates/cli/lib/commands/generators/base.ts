import * as fs from "fs";
import * as path from "path";
import { ConfigType } from "../config.js";
import { SDK_TITLE_LOWER } from "../../constants.js";

/**
 * Supported programming languages for SDK generation.
 * Add new languages here as they are implemented.
 */
export type SupportedLanguage = "typescript";

/**
 * Result of the generation process.
 * Each language generator should return named content fields with fixed output filenames
 * under `<output>/<sdk-title-lower>/`.
 *
 * - `dbContent` -> `databases.ts`
 * - `typesContent` -> `types.ts`
 * - `indexContent` -> `index.ts` (entrypoint to import `databases` and exported types)
 * - `constantsContent` -> `constants.ts`
 */
export interface GenerateResult {
  dbContent: string;
  typesContent: string;
  indexContent: string;
  constantsContent: string;
}

/**
 * Options for overriding auto-detected generation settings.
 */
export interface GenerateOptions {
  /**
   * Override the Appwrite import source used in generated files.
   * Auto-detected from package.json/deno.json if not provided.
   * Examples: "node-appwrite", "appwrite", "@appwrite.io/console"
   */
  appwriteImportSource?: string;
  /**
   * Override the import file extension used in generated files.
   * Auto-detected from package.json "type" field / deno.json if not provided.
   * Use ".js" for ESM projects or "" for CJS/unknown projects.
   */
  importExtension?: string;
}

/**
 * Base interface for all language-specific database generators.
 * Implement this interface to add support for new languages.
 */
export interface IDatabasesGenerator {
  /**
   * The language this generator produces code for.
   */
  readonly language: SupportedLanguage;

  /**
   * File extension for the generated files (e.g., "ts", "py", "go").
   */
  readonly fileExtension: string;

  /**
   * Generate the SDK files from the configuration.
   * @param config - The project configuration containing tables/collections
   * @param options - Optional overrides for auto-detected settings (e.g. import source, extension)
   * @returns Promise resolving to named file contents:
   * `dbContent` (`databases.ts`), `typesContent` (`types.ts`),
   * `indexContent` (`index.ts`), and `constantsContent` (`constants.ts`).
   * Import your generated SDK from `index.ts` (or `index.js` after transpilation).
   */
  generate(
    config: ConfigType,
    options?: GenerateOptions,
  ): Promise<GenerateResult>;

  /**
   * Write the generated files to disk.
   * @param outputDir - The base output directory
   * @param result - The generation result containing files to write
   */
  writeFiles(outputDir: string, result: GenerateResult): Promise<void>;

  /**
   * Get the list of generated file paths (relative to output directory).
   * Used for displaying success messages.
   * @param result - The generation result
   */
  getGeneratedFilePaths(result: GenerateResult): string[];

  /**
   * Optional method to override server-side generation behavior.
   * @param override - The override value
   */
  setServerSideOverride?(override: "auto" | "true" | "false"): void;
}

/**
 * Abstract base class providing common functionality for database generators.
 * Extend this class to implement language-specific generators.
 */
export abstract class BaseDatabasesGenerator implements IDatabasesGenerator {
  abstract readonly language: SupportedLanguage;
  abstract readonly fileExtension: string;

  abstract generate(
    config: ConfigType,
    options?: GenerateOptions,
  ): Promise<GenerateResult>;

  async writeFiles(outputDir: string, result: GenerateResult): Promise<void> {
    const sdkDir = path.join(outputDir, SDK_TITLE_LOWER);
    if (!fs.existsSync(sdkDir)) {
      fs.mkdirSync(sdkDir, { recursive: true });
    }

    const fileEntries: Array<[string, string]> = [
      ["databases.ts", result.dbContent],
      ["types.ts", result.typesContent],
      ["index.ts", result.indexContent],
      ["constants.ts", result.constantsContent],
    ];

    for (const [relativePath, content] of fileEntries) {
      const filePath = path.join(sdkDir, relativePath);
      const fileDir = path.dirname(filePath);

      if (!fs.existsSync(fileDir)) {
        fs.mkdirSync(fileDir, { recursive: true });
      }

      if (relativePath === "constants.ts" && fs.existsSync(filePath)) {
        continue;
      }

      fs.writeFileSync(filePath, content, "utf-8");
    }
  }

  getGeneratedFilePaths(_result: GenerateResult): string[] {
    return [
      path.join(SDK_TITLE_LOWER, "databases.ts"),
      path.join(SDK_TITLE_LOWER, "types.ts"),
      path.join(SDK_TITLE_LOWER, "index.ts"),
      path.join(SDK_TITLE_LOWER, "constants.ts"),
    ];
  }
}
