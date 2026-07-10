/**
 * Central registry of CLI feature flags.
 *
 * Each flag is an opt-in environment variable, disabled unless set to a truthy
 * value ("1", "true", or "yes"). To add a flag, add one entry to FLAG_ENV_VARS
 * and read it anywhere with isFlagEnabled("<name>").
 */
const FLAG_ENV_VARS = {
  // Browser-based OAuth2 device login for Cloud (otherwise email/password).
  oauthLogin: "APPWRITE_CLI_OAUTH_LOGIN",
  // Treat localhost/loopback endpoints as Cloud for OAuth login testing.
  devCloudLogin: "APPWRITE_CLI_DEV_CLOUD_LOGIN",
} as const;

export type FlagName = keyof typeof FLAG_ENV_VARS;

const isTruthy = (value: string | undefined): boolean =>
  ["1", "true", "yes"].includes((value ?? "").toLowerCase());

export const isFlagEnabled = (flag: FlagName): boolean =>
  isTruthy(process.env[FLAG_ENV_VARS[flag]]);
