import fs from "fs";
import path from "path";
import net from "net";
import childProcess from "child_process";
import chalk from "chalk";
import { fetch } from "undici";
import type { Models } from "@appwrite.io/console";
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

export function showConsoleLink(
  serviceName: string,
  action: string,
  ...ids: string[]
): void {
  const projectId = localConfig.getProject().projectId;

  const url = new URL(globalConfig.getEndpoint().replace("/v1", "/console"));
  url.pathname += `/project-${projectId}`;
  action = action.toLowerCase();

  switch (serviceName) {
    case "account":
      url.pathname = url.pathname.replace(`/project-${projectId}`, "");
      url.pathname += getAccountPath(action);
      break;
    case "databases":
      url.pathname += getDatabasePath(action, ids);
      break;
    case "functions":
      url.pathname += getFunctionsPath(action, ids);
      break;
    case "messaging":
      url.pathname += getMessagingPath(action, ids);
      break;
    case "projects":
      url.pathname = url.pathname.replace(`/project-${projectId}`, "");
      url.pathname += getProjectsPath(action, ids);
      break;
    case "storage":
      url.pathname += getBucketsPath(action, ids);
      break;
    case "teams":
      url.pathname += getTeamsPath(action, ids);
      break;
    case "organizations":
      url.pathname += getOrganizationsPath(action, ids);
      break;
    case "users":
      url.pathname += getUsersPath(action, ids);
      break;
    default:
      return;
  }

  console.log(
    `${chalk.green.bold("✓ Success:")} ${chalk.green(url.toString())}`,
  );
}

function getAccountPath(action: string): string {
  let path = "/account";

  if (action === "listsessions") {
    path += "/sessions";
  }

  return path;
}

function getDatabasePath(action: string, ids: string[]): string {
  let path = "/databases";

  if (
    [
      "get",
      "listcollections",
      "getcollection",
      "listattributes",
      "listdocuments",
      "getdocument",
      "listindexes",
      "getdatabaseusage",
    ].includes(action)
  ) {
    path += `/database-${ids[0]}`;
  }

  if (action === "getdatabaseusage") {
    path += `/usage`;
  }

  if (
    [
      "getcollection",
      "listattributes",
      "listdocuments",
      "getdocument",
      "listindexes",
    ].includes(action)
  ) {
    path += `/collection-${ids[1]}`;
  }

  if (action === "listattributes") {
    path += "/attributes";
  }
  if (action === "listindexes") {
    path += "/indexes";
  }
  if (action === "getdocument") {
    path += `/document-${ids[2]}`;
  }

  return path;
}

function getFunctionsPath(action: string, ids: string[]): string {
  let path = "/functions";

  if (action !== "list") {
    path += `/function-${ids[0]}`;
  }

  if (action === "getdeployment") {
    path += `/deployment-${ids[1]}`;
  }

  if (action === "getexecution" || action === "listexecution") {
    path += `/executions`;
  }
  if (action === "getfunctionusage") {
    path += `/usage`;
  }

  return path;
}

function getMessagingPath(action: string, ids: string[]): string {
  let path = "/messaging";

  if (["getmessage", "listmessagelogs"].includes(action)) {
    path += `/message-${ids[0]}`;
  }

  if (["listproviders", "getprovider"].includes(action)) {
    path += `/providers`;
  }

  if (action === "getprovider") {
    path += `/provider-${ids[0]}`;
  }

  if (["listtopics", "gettopic"].includes(action)) {
    path += `/topics`;
  }

  if (action === "gettopic") {
    path += `/topic-${ids[0]}`;
  }

  return path;
}

function getProjectsPath(action: string, ids: string[]): string {
  let path = "";

  if (action !== "list") {
    path += `/project-${ids[0]}`;
  }

  if (["listkeys", "getkey"].includes(action)) {
    path += "/overview/keys";
  }

  if (["listplatforms", "getplatform"].includes(action)) {
    path += "/overview/platforms";
  }

  if (["listwebhooks", "getwebhook"].includes(action)) {
    path += "/settings/webhooks";
  }

  if (["getplatform", "getkey", "getwebhook"].includes(action)) {
    path += `/${ids[1]}`;
  }

  return path;
}

function getBucketsPath(action: string, ids: string[]): string {
  let path = "/storage";

  if (action !== "listbuckets") {
    path += `/bucket-${ids[0]}`;
  }

  if (action === "getbucketusage") {
    path += `/usage`;
  }

  if (action === "getfile") {
    path += `/file-${ids[1]}`;
  }

  return path;
}

function getTeamsPath(action: string, ids: string[]): string {
  let path = "/auth/teams";

  if (action !== "list") {
    path += `/team-${ids[0]}`;
  }

  return path;
}

function getOrganizationsPath(action: string, ids: string[]): string {
  let path = `/organization-${ids[0]}`;

  if (action === "list") {
    path = "/account/organizations";
  }

  return path;
}

function getUsersPath(action: string, ids: string[]): string {
  let path = "/auth";

  if (action !== "list") {
    path += `/user-${ids[0]}`;
  }

  if (action === "listsessions") {
    path += "sessions";
  }

  return path;
}

export function isCloud(): boolean {
  const endpoint = globalConfig.getEndpoint() || DEFAULT_ENDPOINT;
  const hostname = new URL(endpoint).hostname;
  return hostname.endsWith("appwrite.io");
}

export function toPascalCase(str: string): string {
  return str
    .replace(/[-_\s]+(.)?/g, (_, char) => (char ? char.toUpperCase() : ""))
    .replace(/^(.)/, (char) => char.toUpperCase());
}

export function toUpperSnakeCase(str: string): string {
  return str
    .replace(/([a-z])([A-Z])/g, "$1_$2")
    .replace(/[-\s]+/g, "_")
    .toUpperCase();
}
