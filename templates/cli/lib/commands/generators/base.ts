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
 * Each language generator should return files as a map of filename to content.
 */
export interface GenerateResult {
  /** Map of relative file paths to their content */
  files: Map<string, string>;
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
   * @returns Promise resolving to the generated files
   */
  generate(config: ConfigType): Promise<GenerateResult>;

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
}

/**
 * Abstract base class providing common functionality for database generators.
 * Extend this class to implement language-specific generators.
 */
export abstract class BaseDatabasesGenerator implements IDatabasesGenerator {
  abstract readonly language: SupportedLanguage;
  abstract readonly fileExtension: string;

  abstract generate(config: ConfigType): Promise<GenerateResult>;

  async writeFiles(outputDir: string, result: GenerateResult): Promise<void> {
    const sdkDir = path.join(outputDir, SDK_TITLE_LOWER);
    if (!fs.existsSync(sdkDir)) {
      fs.mkdirSync(sdkDir, { recursive: true });
    }

    for (const [relativePath, content] of result.files) {
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

  getGeneratedFilePaths(result: GenerateResult): string[] {
    return Array.from(result.files.keys()).map((relativePath) =>
      path.join(SDK_TITLE_LOWER, relativePath),
    );
  }
}
