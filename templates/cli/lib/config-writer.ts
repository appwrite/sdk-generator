import fs from "node:fs";
import _path from "node:path";
import { loadFile, parseModule, generateCode, builders } from "magicast";
import { format } from "prettier";
import type { ConfigType } from "./commands/config.js";

/**
 * Recursively convert BigInt values in an object to BigInt literals for magicast
 */
function convertBigInts(value: any): any {
  if (typeof value === "bigint") {
    // Convert BigInt to BigInt constructor call to handle negative values
    // Negative BigInt literals like -9223372036854775808n are parsed as UnaryExpression
    // which magicast doesn't support. Using BigInt("...") constructor works around this.
    const bigIntStr = value.toString();
    return builders.functionCall("BigInt", bigIntStr);
  }
  if (Array.isArray(value)) {
    return value.map(convertBigInts);
  }
  if (value !== null && typeof value === "object") {
    const result: any = {};
    for (const [key, val] of Object.entries(value)) {
      result[key] = convertBigInts(val);
    }
    return result;
  }
  return value;
}

/**
 * Write configuration to a TypeScript/JavaScript file using magicast.
 * This preserves the file structure and formatting while updating the content.
 *
 * @param filePath Path to the TypeScript/JavaScript config file
 * @param config The config data to write
 * @returns true if successful, false otherwise
 */
export async function writeTypeScriptConfig(
  filePath: string,
  config: ConfigType,
): Promise<boolean> {
  try {
    let mod;

    // Try to load existing file
    try {
      mod = await loadFile(filePath);
    } catch {
      // File doesn't exist, create a new one
      mod = parseModule(`export default defineConfig({});`);
    }

    // Get the config object from either:
    // 1. `export default defineConfig({ ... })` - function call
    // 2. `export default { ... }` - direct object
    const defaultExport = mod.exports.default;
    let configObject: any;

    if (defaultExport?.$type === "function-call") {
      // It's wrapped in defineConfig() or similar
      // Ensure we have at least one argument that is a plain object
      if (!defaultExport.$args || defaultExport.$args.length === 0) {
        defaultExport.$args = [{}];
      }
      // Guard: ensure $args[0] is a plain object
      if (
        typeof defaultExport.$args[0] !== "object" ||
        defaultExport.$args[0] === null
      ) {
        defaultExport.$args[0] = {};
      }
      configObject = defaultExport.$args[0];
    } else {
      // It's a direct object export
      // Guard: ensure configObject is a plain object
      if (
        typeof defaultExport !== "object" ||
        defaultExport === null ||
        defaultExport === undefined
      ) {
        configObject = {};
        mod.exports.default = configObject;
      } else {
        configObject = defaultExport;
      }
    }

    // Update the config object with new data
    // We merge the new config, removing keys that are undefined in the new config
    const configKeys = Object.keys(config) as (keyof ConfigType)[];

    for (const key of configKeys) {
      const value = config[key];
      if (value === undefined) {
        delete configObject[key];
      } else {
        // Convert BigInt values to proper literals
        configObject[key] = convertBigInts(value);
      }
    }

    // Generate code
    const { code } = generateCode(mod);

    // Format with Prettier for better readability
    const formattedCode = await format(code, {
      parser: "typescript",
      printWidth: 100,
      tabWidth: 2,
      useTabs: false,
      semi: true,
      singleQuote: false,
      trailingComma: "all",
      bracketSpacing: true,
      arrowParens: "always",
    });

    // Remove blank lines between properties for compact formatting
    const cleanedCode = formattedCode
      .split("\n")
      .reduce((lines: string[], line, index, arr) => {
        const trimmedLine = line.trim();
        const prevLine = arr[index - 1]?.trim();

        // Skip blank lines that are:
        // 1. Between properties (not at start/end of blocks)
        // 2. After closing brackets followed by another property
        // 3. Before opening brackets
        if (trimmedLine === "") {
          // Keep blank line only if it's at start/end of file or inside a block with content
          const nextLine = arr[index + 1]?.trim();
          const isAtBlockStart = prevLine === "{" || prevLine?.endsWith("{");
          const isAtBlockEnd = nextLine === "}" || nextLine?.startsWith("}");
          const isBetweenProperties = prevLine?.endsWith(",") && nextLine?.includes(":");

          if (isBetweenProperties || (isAtBlockStart && !isAtBlockEnd)) {
            return lines; // Skip this blank line
          }
        }

        lines.push(line);
        return lines;
      }, [])
      .join("\n");

    // Write formatted code to file
    fs.writeFileSync(filePath, cleanedCode, "utf-8");
    return true;
  } catch (error) {
    console.error(`Failed to write TypeScript config to ${filePath}:`, error);
    return false;
  }
}

/**
 * Check if a TypeScript/JavaScript config file exists
 *
 * @param directory Directory to check
 * @param name Base name (without extension)
 * @returns Path to the found config file, or null if not found
 */
export function findTypeScriptConfig(
  directory: string,
  name: string,
): string | null {
  const extensions = [".ts", ".js", ".mjs", ".cjs", ".mts", ".cts"];

  for (const ext of extensions) {
    const filePath = _path.join(directory, `${name}${ext}`);
    if (fs.existsSync(filePath)) {
      return filePath;
    }
  }

  return null;
}
