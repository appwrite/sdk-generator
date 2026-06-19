// Type-check stub for the CLI templates. The generated SDK uses
// constants.ts.twig (registered in CLI.php getFiles); this plain file exists
// only so `templates/cli` type-checks locally before generation. Keep its
// exported symbols in sync with constants.ts.twig.

// SDK
export const SDK_TITLE = "SDK for CLI";
export const SDK_TITLE_LOWER = "sdk for cli";
export const SDK_VERSION = "1.0.0";
export const SDK_NAME = "SDK for CLI";
export const SDK_PLATFORM = "CLI";
export const SDK_LANGUAGE = "cli";
export const SDK_LOGO = "";

// CLI
export const EXECUTABLE_NAME = "cli";
// 1 day
export const UPDATE_CHECK_INTERVAL_MS = 24 * 60 * 60 * 1000;

// Homebrew — fully-qualified `<owner>/<tap>/<formula>` reference
export const HOMEBREW_TAP = "appwrite/appwrite";
export const HOMEBREW_FORMULA = `${HOMEBREW_TAP}/appwrite`;

// NPM
export const NPM_PACKAGE_NAME = "sdk-for-cli";
export const NPM_REGISTRY_URL = `https://registry.npmjs.org/${NPM_PACKAGE_NAME}/latest`;

// GitHub
export const GITHUB_REPO = "appwrite/sdk-for-cli";
export const GITHUB_RELEASES_URL = `https://github.com/${GITHUB_REPO}/releases`;

// API
export const DEFAULT_ENDPOINT = "https://cloud.appwrite.io/v1";

// Config resources
export const CONFIG_RESOURCE_KEYS = [
  "databases",
  "functions",
  "topics",
  "messages",
  "sites",
  "buckets",
  "tablesDB",
  "tables",
  "teams",
  "webhooks",
  "collections",
] as const;

export const TOP_LEVEL_RESOURCE_ARRAY_KEYS = new Set<string>(
  CONFIG_RESOURCE_KEYS,
);
