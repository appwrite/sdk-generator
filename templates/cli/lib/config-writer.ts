import fs from "node:fs";
import _path from "node:path";
import { loadFile, parseModule, generateCode, builders } from "magicast";
import { format } from "prettier";
import type { ConfigType } from "./commands/config.js";

/** Recursively converts BigInt values to `BigInt("...")` constructor calls, which magicast can serialise. */
function convertBigInts(value: any): any {
  if (typeof value === "bigint") {
    return builders.functionCall("BigInt", value.toString());
  }
  if (Array.isArray(value)) {
    return value.map(convertBigInts);
  }
  if (value !== null && typeof value === "object") {
    return Object.fromEntries(
      Object.entries(value).map(([k, v]) => [k, convertBigInts(v)]),
    );
  }
  return value;
}

/**
 * Resolves the config object from a magicast module, handling both
 * `export default defineConfig({})` and `export default {}` shapes.
 */
function resolveConfigObject(mod: any): any {
  const defaultExport = mod.exports.default;

  if (defaultExport?.$type === "function-call") {
    if (!defaultExport.$args?.length) defaultExport.$args = [{}];
    if (
      typeof defaultExport.$args[0] !== "object" ||
      defaultExport.$args[0] === null
    ) {
      defaultExport.$args[0] = {};
    }
    return defaultExport.$args[0];
  }

  if (typeof defaultExport !== "object" || defaultExport == null) {
    const configObject = {};
    mod.exports.default = configObject;
    return configObject;
  }

  return defaultExport;
}

/** Formats TypeScript source code with Prettier using the project's standard style options. */
async function prettify(code: string): Promise<string> {
  return format(code, {
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
}

/** Strips blank lines between adjacent object properties for a more compact output. */
function removeBlankLinesBetweenProperties(code: string): string {
  return code
    .split("\n")
    .reduce((lines: string[], line, index, arr) => {
      const trimmed = line.trim();
      const prev = arr[index - 1]?.trim();
      const next = arr[index + 1]?.trim();

      const isBetweenProperties =
        trimmed === "" && prev?.endsWith(",") && next?.includes(":");
      const isAtBlockStart = prev === "{" || prev?.endsWith("{");

      if (
        isBetweenProperties ||
        (trimmed === "" && isAtBlockStart && next !== "}")
      ) {
        return lines;
      }

      lines.push(line);
      return lines;
    }, [])
    .join("\n");
}

/**
 * Writes a config object to a TypeScript/JavaScript file using magicast,
 * preserving the existing file structure where possible.
 * Falls back to a fresh `export default defineConfig({})` module if the file doesn't exist.
 */
export async function writeTypeScriptConfig(
  filePath: string,
  config: ConfigType,
): Promise<boolean> {
  try {
    const mod = await loadFile(filePath).catch(() =>
      parseModule(`export default defineConfig({});`),
    );

    const configObject = resolveConfigObject(mod);

    for (const key of Object.keys(config) as (keyof ConfigType)[]) {
      const value = config[key];
      if (value === undefined) {
        delete configObject[key];
      } else {
        configObject[key] = convertBigInts(value);
      }
    }

    const { code } = generateCode(mod);
    const formatted = await prettify(code);
    const cleaned = removeBlankLinesBetweenProperties(formatted);

    fs.writeFileSync(filePath, cleaned, "utf-8");
    return true;
  } catch (error) {
    console.error(`Failed to write TypeScript config to ${filePath}:`, error);
    return false;
  }
}

/**
 * Searches a directory for a config file matching the given name across common
 * TypeScript/JavaScript extensions. Returns the first match, or `null` if none is found.
 */
export function findTypeScriptConfig(
  directory: string,
  name: string,
): string | null {
  const extensions = [".ts", ".js", ".mjs", ".cjs", ".mts", ".cts"];

  for (const ext of extensions) {
    const filePath = _path.join(directory, `${name}${ext}`);
    if (fs.existsSync(filePath)) return filePath;
  }

  return null;
}
