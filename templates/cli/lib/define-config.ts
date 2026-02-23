import type { ConfigType } from "./commands/config.js";

/**
 * Helper function to define Appwrite configuration with type safety.
 * This function simply returns the config object as-is, but provides
 * TypeScript IntelliSense and type checking.
 *
 * @example
 * // appwrite.config.ts
 * import { defineConfig } from "appwrite-cli";
 *
 * export default defineConfig({
 *   projectId: "my-project",
 *   endpoint: "https://cloud.appwrite.io/v1",
 *   functions: [
 *     {
 *       $id: "my-function",
 *       name: "My Function",
 *       runtime: "node-18.0",
 *     },
 *   ],
 * });
 */
export function defineConfig(config: ConfigType): ConfigType {
  return config;
}
