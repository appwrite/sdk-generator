import fs from "fs";
import path from "path";
import net from "net";
import childProcess from "child_process";
import chalk from "chalk";
import { fetch } from "undici";
import type { Models } from "@appwrite.io/console";
import { z } from "zod";
import { localConfig, globalConfig } from "./config.js";
import type { SettingsType } from "./commands/config.js";
import { NPM_REGISTRY_URL, DEFAULT_ENDPOINT } from "./constants.js";

export const createSettingsObject = (project: Models.Project): SettingsType => {
  return {
    services: {
      account: project.serviceStatusForAccount,
      avatars: project.serviceStatusForAvatars,
      databases: project.serviceStatusForDatabases,
      locale: project.serviceStatusForLocale,
      health: project.serviceStatusForHealth,
      storage: project.serviceStatusForStorage,
      teams: project.serviceStatusForTeams,
      users: project.serviceStatusForUsers,
      sites: project.serviceStatusForSites,
      functions: project.serviceStatusForFunctions,
      graphql: project.serviceStatusForGraphql,
      messaging: project.serviceStatusForMessaging,
    },
    auth: {
      methods: {
        jwt: project.authJWT,
        phone: project.authPhone,
        invites: project.authInvites,
        anonymous: project.authAnonymous,
        "email-otp": project.authEmailOtp,
        "magic-url": project.authUsersAuthMagicURL,
        "email-password": project.authEmailPassword,
      },
      security: {
        duration: project.authDuration,
        limit: project.authLimit,
        sessionsLimit: project.authSessionsLimit,
        passwordHistory: project.authPasswordHistory,
        passwordDictionary: project.authPasswordDictionary,
        personalDataCheck: project.authPersonalDataCheck,
        sessionAlerts: project.authSessionAlerts,
        mockNumbers: project.authMockNumbers,
      },
    },
  };
};

/**
 * Get the latest version from npm registry
 */
export async function getLatestVersion(): Promise<string> {
  try {
    const response = await fetch(NPM_REGISTRY_URL);
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    const data = (await response.json()) as { version: string };
    return data.version;
  } catch (e) {
    throw new Error(`Failed to fetch latest version: ${(e as Error).message}`);
  }
}

/**
 * Compare versions using semantic versioning
 */
export function compareVersions(current: string, latest: string): number {
  const currentParts = current.split(".").map(Number);
  const latestParts = latest.split(".").map(Number);

  for (let i = 0; i < Math.max(currentParts.length, latestParts.length); i++) {
    const currentPart = currentParts[i] || 0;
    const latestPart = latestParts[i] || 0;

    if (latestPart > currentPart) return 1; // Latest is newer
    if (latestPart < currentPart) return -1; // Current is newer
  }

  return 0; // Same version
}

export function getAllFiles(folder: string): string[] {
  const files: string[] = [];
  for (const pathDir of fs.readdirSync(folder)) {
    const pathAbsolute = path.join(folder, pathDir);
    let stats: fs.Stats;
    try {
      stats = fs.statSync(pathAbsolute);
    } catch (error) {
      continue;
    }
    if (stats.isDirectory()) {
      files.push(...getAllFiles(pathAbsolute));
    } else {
      files.push(pathAbsolute);
    }
  }
  return files;
}

export async function isPortTaken(port: number): Promise<boolean> {
  const taken = await new Promise<boolean>((res, rej) => {
    const tester = net
      .createServer()
      .once("error", function (err: NodeJS.ErrnoException) {
        if (err.code != "EADDRINUSE") return rej(err);
        res(true);
      })
      .once("listening", function () {
        tester
          .once("close", function () {
            res(false);
          })
          .close();
      })
      .listen(port);
  });

  return taken;
}

export function systemHasCommand(command: string): boolean {
  const isUsingWindows = process.platform == "win32";

  try {
    if (isUsingWindows) {
      childProcess.execSync("where " + command, { stdio: "pipe" });
    } else {
      childProcess.execSync(
        `[[ $(${command} --version) ]] || { exit 1; } && echo "OK"`,
        {
          stdio: "pipe",
          shell: "/bin/bash",
        },
      );
    }
  } catch (error) {
    console.log(error);
    return false;
  }

  return true;
}

export const checkDeployConditions = (localConfig: any): void => {
  if (Object.keys(localConfig.data).length === 0) {
    throw new Error(
      "No appwrite.config.json file found in the current directory. Please run this command again in the folder containing your appwrite.config.json file, or run 'appwrite init project' to link current directory to an Appwrite project.",
    );
  }
};

export function isCloud(): boolean {
  const endpoint = globalConfig.getEndpoint() || DEFAULT_ENDPOINT;
  const hostname = new URL(endpoint).hostname;
  return hostname.endsWith("appwrite.io");
}

/**
 * Filters an object to only include fields defined in a Zod object schema.
 * Uses the schema's shape to determine allowed keys.
 *
 * @param data - The data to filter
 * @param schema - A Zod object schema with a shape property
 * @returns The filtered data with only schema-defined fields
 */
export function filterBySchema<T extends z.ZodObject<z.ZodRawShape>>(
  data: Record<string, unknown>,
  schema: T,
): z.infer<T> {
  const allowedKeys = Object.keys(schema.shape);
  const result: Record<string, unknown> = {};

  for (const key of allowedKeys) {
    if (key in data) {
      result[key] = data[key];
    }
  }

  return result as z.infer<T>;
}

