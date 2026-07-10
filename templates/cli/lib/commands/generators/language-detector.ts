import * as fs from "fs";
import * as path from "path";
import { SupportedLanguage } from "./base.js";

/**
 * Language detection result with confidence level.
 */
export interface LanguageDetectionResult {
  language: SupportedLanguage;
  confidence: "high" | "medium" | "low";
  reason: string;
}

/**
 * Configuration for detecting a specific language.
 */
interface LanguageConfig {
  language: SupportedLanguage;
  /** Files that indicate this language with high confidence */
  primaryIndicators: string[];
  /** Files that indicate this language with medium confidence */
  secondaryIndicators: string[];
  /** File extensions to scan for */
  fileExtensions: string[];
}

/**
 * Language detection configurations.
 * Add new languages here as they are supported.
 */
const languageConfigs: LanguageConfig[] = [
  {
    language: "typescript",
    primaryIndicators: ["tsconfig.json", "package.json", "deno.json"],
    secondaryIndicators: [
      ".nvmrc",
      "package-lock.json",
      "yarn.lock",
      "pnpm-lock.yaml",
      "bun.lockb",
    ],
    fileExtensions: [".ts", ".tsx", ".js", ".jsx"],
  },
  // Future languages can be added here:
  // {
  //   language: "python",
  //   primaryIndicators: ["requirements.txt", "pyproject.toml", "setup.py", "Pipfile"],
  //   secondaryIndicators: [".python-version", "poetry.lock"],
  //   fileExtensions: [".py"],
  // },
  // {
  //   language: "go",
  //   primaryIndicators: ["go.mod"],
  //   secondaryIndicators: ["go.sum"],
  //   fileExtensions: [".go"],
  // },
  // {
  //   language: "dart",
  //   primaryIndicators: ["pubspec.yaml"],
  //   secondaryIndicators: ["pubspec.lock"],
  //   fileExtensions: [".dart"],
  // },
];

/**
 * Detects the programming language of a project based on its directory contents.
 */
export class LanguageDetector {
  private cwd: string;

  constructor(cwd: string = process.cwd()) {
    this.cwd = cwd;
  }

  /**
   * Detect the primary language of the project.
   * @returns The detected language and confidence level, or null if no supported language detected
   */
  detect(): LanguageDetectionResult | null {
    for (const config of languageConfigs) {
      const result = this.checkLanguage(config);
      if (result) {
        return result;
      }
    }

    return null;
  }

  /**
   * Check if a specific language is detected in the project.
   */
  private checkLanguage(
    config: LanguageConfig,
  ): LanguageDetectionResult | null {
    // Check primary indicators first
    for (const indicator of config.primaryIndicators) {
      if (this.fileExists(indicator)) {
        return {
          language: config.language,
          confidence: "high",
          reason: `Found ${indicator}`,
        };
      }
    }

    // Check secondary indicators
    for (const indicator of config.secondaryIndicators) {
      if (this.fileExists(indicator)) {
        return {
          language: config.language,
          confidence: "medium",
          reason: `Found ${indicator}`,
        };
      }
    }

    // Check for file extensions in the current directory
    const hasMatchingFiles = this.hasFilesWithExtensions(config.fileExtensions);
    if (hasMatchingFiles) {
      return {
        language: config.language,
        confidence: "low",
        reason: `Found files with extensions: ${config.fileExtensions.join(", ")}`,
      };
    }

    return null;
  }

  /**
   * Check if a file exists in the project directory.
   */
  private fileExists(filename: string): boolean {
    return fs.existsSync(path.resolve(this.cwd, filename));
  }

  /**
   * Check if files with specific extensions exist in the current directory.
   */
  private hasFilesWithExtensions(extensions: string[]): boolean {
    try {
      const files = fs.readdirSync(this.cwd);
      return files.some((file) => extensions.some((ext) => file.endsWith(ext)));
    } catch {
      return false;
    }
  }

  /**
   * Get all supported languages.
   */
  static getSupportedLanguages(): SupportedLanguage[] {
    return languageConfigs.map((c) => c.language);
  }

  /**
   * Check if a language is supported.
   */
  static isSupported(language: string): language is SupportedLanguage {
    return languageConfigs.some((c) => c.language === language);
  }
}
