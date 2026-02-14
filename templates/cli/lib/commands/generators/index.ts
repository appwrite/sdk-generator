import { IDatabasesGenerator, SupportedLanguage } from "./base.js";
import {
  LanguageDetector,
  LanguageDetectionResult,
} from "./language-detector.js";
import { TypeScriptDatabasesGenerator } from "./typescript/databases.js";

export {
  IDatabasesGenerator,
  SupportedLanguage,
  GenerateResult,
} from "./base.js";
export {
  LanguageDetector,
  LanguageDetectionResult,
} from "./language-detector.js";

/**
 * Factory function type for creating database generators.
 */
type GeneratorFactory = () => IDatabasesGenerator;

/**
 * Registry of generator factories by language.
 * Add new language generators here as they are implemented.
 */
const generatorRegistry: Record<SupportedLanguage, GeneratorFactory> = {
  typescript: () => new TypeScriptDatabasesGenerator(),
  // Future languages:
  // python: () => new PythonDatabasesGenerator(),
  // go: () => new GoDatabasesGenerator(),
  // dart: () => new DartDatabasesGenerator(),
};

/**
 * Create a database generator for the specified language.
 * @param language - The target language
 * @returns The appropriate generator instance
 * @throws Error if the language is not supported
 */
export function createGenerator(
  language: SupportedLanguage,
): IDatabasesGenerator {
  const factory = generatorRegistry[language];
  if (!factory) {
    throw new Error(
      `No generator available for language: ${language}. ` +
        `Supported languages: ${Object.keys(generatorRegistry).join(", ")}`,
    );
  }
  return factory();
}

/**
 * Create a database generator by auto-detecting the project language.
 * @param cwd - The working directory to detect language from (defaults to process.cwd())
 * @returns Object containing the generator and detection result
 * @throws Error if no supported language is detected
 */
export function createGeneratorFromDetection(cwd?: string): {
  generator: IDatabasesGenerator;
  detection: LanguageDetectionResult;
} {
  const detector = new LanguageDetector(cwd);
  const detection = detector.detect();

  if (!detection) {
    const supported = LanguageDetector.getSupportedLanguages().join(", ");
    throw new Error(
      `Could not detect project language. ` +
        `Supported languages: ${supported}. ` +
        `Please ensure your project has the appropriate configuration files (e.g., package.json for TypeScript).`,
    );
  }

  return {
    generator: createGenerator(detection.language),
    detection,
  };
}

/**
 * Get all supported languages.
 */
export function getSupportedLanguages(): SupportedLanguage[] {
  return Object.keys(generatorRegistry) as SupportedLanguage[];
}
