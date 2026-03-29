// @ts-expect-error BigInt toJSON polyfill for JSON.stringify support
BigInt.prototype.toJSON = function () {
  return this.toString();
};

import chalk from "chalk";
import { InvalidArgumentError } from "commander";
import Table from "cli-table3";
import packageJson from "../package.json" with { type: "json" };
const { description } = packageJson;
import { globalConfig } from "./config.js";
import os from "os";
import { Client } from "@appwrite.io/console";
import { isCloud } from "./utils.js";
import type { CliConfig } from "./types.js";
import {
  SDK_VERSION,
  SDK_TITLE,
  SDK_LOGO,
  EXECUTABLE_NAME,
} from "./constants.js";

const cliConfig: CliConfig = {
  verbose: false,
  json: false,
  raw: false,
  showSecrets: false,
  force: false,
  all: false,
  ids: [],
  report: false,
  reportData: {},
  commandPath: [],
};

type JsonObject = Record<string, unknown>;

const PROJECT_LIST_FIELDS = new Set(["name", "$id", "region", "status"]);
const HIDDEN_VALUE = "[hidden]";
let renderDepth = 0;
let redactionApplied = false;

interface ReportDataPayload {
  data?: {
    args?: string[];
  };
}

const toJsonObject = (value: unknown): JsonObject | null => {
  if (value && typeof value === "object" && !Array.isArray(value)) {
    return value as JsonObject;
  }

  return null;
};

const extractReportCommandArgs = (value: unknown): string[] => {
  if (!value || typeof value !== "object") {
    return [];
  }

  const reportData = value as ReportDataPayload;
  if (!Array.isArray(reportData.data?.args)) {
    return [];
  }

  return reportData.data.args;
};

const beginRender = (): void => {
  if (renderDepth === 0) {
    redactionApplied = false;
  }

  renderDepth++;
};

const endRender = (): void => {
  renderDepth = Math.max(0, renderDepth - 1);

  if (renderDepth === 0 && redactionApplied && !cliConfig.showSecrets) {
    hint("Sensitive values were redacted. Pass --show-secrets to display them.");
  }
};

const isSensitiveKey = (key: string): boolean => {
  const normalizedKey = key.toLowerCase().replace(/[^a-z0-9]/g, "");

  return (
    normalizedKey === "jwt" ||
    normalizedKey === "secret" ||
    normalizedKey.endsWith("secret") ||
    normalizedKey === "password" ||
    normalizedKey.endsWith("password") ||
    normalizedKey === "token" ||
    normalizedKey.endsWith("token") ||
    normalizedKey === "apikey" ||
    normalizedKey.endsWith("apikey")
  );
};

const maskSensitiveString = (value: string): string => {
  if (value.length <= 8) {
    return HIDDEN_VALUE;
  }

  const separatorIndex = value.indexOf("_");
  if (separatorIndex > 0 && separatorIndex < value.length - 5) {
    const prefix = value.slice(0, separatorIndex + 1);
    const head = value.slice(separatorIndex + 1, separatorIndex + 5);
    const tail = value.slice(-4);
    return `${prefix}${head}...${tail}`;
  }

  return `${value.slice(0, 4)}...${value.slice(-4)}`;
};

const maskSensitiveData = (value: unknown, key?: string): unknown => {
  if (key && isSensitiveKey(key) && !cliConfig.showSecrets) {
    redactionApplied = true;

    if (typeof value === "string") {
      return maskSensitiveString(value);
    }

    if (value == null) {
      return value;
    }

    return HIDDEN_VALUE;
  }

  if (Array.isArray(value)) {
    return value.map((item) => maskSensitiveData(item));
  }

  if (value && typeof value === "object") {
    if (value?.constructor?.name === "BigNumber") {
      return String(value);
    }

    const result: JsonObject = {};
    for (const childKey of Object.keys(value as JsonObject)) {
      const childValue = (value as JsonObject)[childKey];
      if (typeof childValue === "function") continue;
      result[childKey] = maskSensitiveData(childValue, childKey);
    }

    return result;
  }

  return value;
};

const filterObject = (obj: JsonObject): JsonObject => {
  const sanitizedObject = maskSensitiveData(obj) as JsonObject;
  const result: JsonObject = {};
  for (const key of Object.keys(sanitizedObject)) {
    const value = sanitizedObject[key];
    if (typeof value === "function") continue;
    if (value == null) continue;
    if (value?.constructor?.name === "BigNumber") {
      result[key] = String(value);
      continue;
    }
    if (typeof value === "object") continue;
    if (typeof value === "string" && value.trim() === "") continue;
    result[key] = value;
  }
  return result;
};

const isProjectsListCommand = (): boolean =>
  cliConfig.commandPath.length >= 2 &&
  cliConfig.commandPath[0] === "projects" &&
  cliConfig.commandPath[1] === "list";

const filterProjectsListRow = (row: JsonObject): JsonObject => {
  const filtered: JsonObject = {};

  for (const key of Object.keys(row)) {
    if (!PROJECT_LIST_FIELDS.has(key)) continue;
    filtered[key] = row[key];
  }

  return filtered;
};

const applyDisplayFilter = (rows: JsonObject[]): JsonObject[] => {
  if (isProjectsListCommand()) {
    return rows.map(filterProjectsListRow);
  }

  return rows;
};

const filterData = (data: JsonObject): JsonObject => {
  const sanitizedData = maskSensitiveData(data) as JsonObject;
  const result: JsonObject = {};
  for (const key of Object.keys(sanitizedData)) {
    const value = sanitizedData[key];
    if (typeof value === "function") continue;
    if (value == null) continue;
    if (value?.constructor?.name === "BigNumber") {
      result[key] = String(value);
      continue;
    }
    if (Array.isArray(value)) {
      result[key] = value.map((item) => {
        if (item?.constructor?.name === "BigNumber") return String(item);
        return item && typeof item === "object" && !Array.isArray(item)
          ? filterObject(item as JsonObject)
          : item;
      });
    } else if (typeof value === "object") {
      continue;
    } else if (typeof value === "string" && value.trim() === "") {
      continue;
    } else {
      result[key] = value;
    }
  }
  return result;
};

export const parse = (data: JsonObject): void => {
  beginRender();

  try {
    const sanitizedData = maskSensitiveData(data) as JsonObject;

    if (cliConfig.raw) {
      drawJSON(sanitizedData);
      return;
    }

    if (cliConfig.json) {
      drawJSON(filterData(sanitizedData));
      return;
    }

    const keys = Object.keys(sanitizedData).filter(
      (k) => typeof sanitizedData[k] !== "function",
    );
    let printedScalar = false;

    for (const key of keys) {
      const value = sanitizedData[key];
      if (value === null) {
        console.log(`${chalk.yellow.bold(key)} : null`);
        printedScalar = true;
      } else if (Array.isArray(value)) {
        console.log(`${chalk.yellow.bold.underline(key)}`);
        if (typeof value[0] === "object") {
          drawTable(value);
        } else {
          drawJSON(value);
        }
        printedScalar = false;
      } else if (typeof value === "object") {
        if (value?.constructor?.name === "BigNumber") {
          console.log(`${chalk.yellow.bold(key)} : ${value}`);
          printedScalar = true;
        } else {
          console.log(`${chalk.yellow.bold.underline(key)}`);
          const tableRow = toJsonObject(value) ?? {};
          drawTable([tableRow]);
          printedScalar = false;
        }
      } else {
        console.log(`${chalk.yellow.bold(key)} : ${value}`);
        printedScalar = true;
      }
    }
  } finally {
    endRender();
  }
};

const MAX_COL_WIDTH = 40;

const formatCellValue = (value: unknown): string => {
  if (value == null) return "-";
  if (Array.isArray(value)) {
    if (value.length === 0) return "[]";
    return `[${value.length} items]`;
  }
  if (typeof value === "object") {
    if (value?.constructor?.name === "BigNumber") return String(value);
    const keys = Object.keys(value as Record<string, unknown>);
    if (keys.length === 0) return "{}";
    return `{${keys.length} keys}`;
  }
  const str = String(value);
  if (str.length > MAX_COL_WIDTH) {
    return str.slice(0, MAX_COL_WIDTH - 1) + "…";
  }
  return str;
};

export const drawTable = (data: Array<JsonObject | null | undefined>): void => {
  beginRender();

  try {
    if (data.length == 0) {
      console.log("[]");
      return;
    }

    const rows = applyDisplayFilter(
      data.map((item): JsonObject => {
        const maskedItem = maskSensitiveData(item);
        return toJsonObject(maskedItem) ?? {};
      }),
    );

    // Create an object with all the keys in it
    const obj = rows.reduce((res, item) => ({ ...res, ...item }), {});
    // Get those keys as an array
    const allKeys = Object.keys(obj);
    if (allKeys.length === 0) {
      drawJSON(data);
      return;
    }

    // If too many columns, show condensed key-value output with only scalar, non-empty fields
    const maxColumns = 6;
    if (allKeys.length > maxColumns) {
      // Collect visible entries per row to compute alignment
      const rowEntries = rows.map((row) => {
        const entries: Array<[string, string]> = [];
        for (const key of Object.keys(row)) {
          const value = row[key];
          if (typeof value === "function") continue;
          if (value == null) continue;
          if (value?.constructor?.name === "BigNumber") {
            entries.push([key, String(value)]);
            continue;
          }
          if (typeof value === "object") continue;
          if (typeof value === "string" && value.trim() === "") continue;
          entries.push([key, String(value)]);
        }
        return entries;
      });

      const flatEntries = rowEntries.flat();
      if (flatEntries.length === 0) {
        drawJSON(data);
        return;
      }

      const maxKeyLen = Math.max(...flatEntries.map(([key]) => key.length));

      const separatorLen = Math.min(
        maxKeyLen + 2 + MAX_COL_WIDTH,
        process.stdout.columns || 80,
      );

      rowEntries.forEach((entries, idx) => {
        if (idx > 0) console.log(chalk.cyan("─".repeat(separatorLen)));
        for (const [key, value] of entries) {
          const paddedKey = key.padEnd(maxKeyLen);
          console.log(`${chalk.yellow.bold(paddedKey)}  ${value}`);
        }
      });
      return;
    }

    const columns = allKeys;

    // Create an object with all keys set to the default value ''
    const def = allKeys.reduce((result: Record<string, string>, key) => {
      result[key] = "-";
      return result;
    }, {});
    // Use object destructuring to replace all default values with the ones we have
    const normalizedData = rows.map((item) => ({ ...def, ...item }));

    const table = new Table({
      head: columns.map((c) => chalk.cyan.italic.bold(c)),
      colWidths: columns.map(() => null) as (number | null)[],
      wordWrap: false,
      chars: {
        top: " ",
        "top-mid": " ",
        "top-left": " ",
        "top-right": " ",
        bottom: " ",
        "bottom-mid": " ",
        "bottom-left": " ",
        "bottom-right": " ",
        left: " ",
        "left-mid": " ",
        mid: chalk.cyan("─"),
        "mid-mid": chalk.cyan("┼"),
        right: " ",
        "right-mid": " ",
        middle: chalk.cyan("│"),
      },
    });

    normalizedData.forEach((row) => {
      const rowValues: string[] = [];
      for (const key of columns) {
        rowValues.push(formatCellValue(row[key]));
      }
      table.push(rowValues);
    });
    console.log(table.toString());
  } finally {
    endRender();
  }
};

export const drawJSON = (data: unknown): void => {
  console.log(JSON.stringify(data, null, 2));
};

export const parseError = (err: Error): void => {
  if (cliConfig.report) {
    void (async () => {
      let appwriteVersion = "unknown";
      const endpoint = globalConfig.getEndpoint();

      try {
        const client = new Client().setEndpoint(endpoint);
        const res = (await client.call(
          "get",
          new URL("/health/version", endpoint),
        )) as { version: string };
        appwriteVersion = res.version;
      } catch {
        // Silently fail
      }

      const version = SDK_VERSION;
      const commandArgs = extractReportCommandArgs(cliConfig.reportData);
      const stepsToReproduce = `Running \`${EXECUTABLE_NAME} ${commandArgs.join(" ")}\``;
      const yourEnvironment = `CLI version: ${version}\nOperation System: ${os.type()}\nAppwrite version: ${appwriteVersion}\nIs Cloud: ${isCloud()}`;

      const stack = "```\n" + (err.stack || err.message) + "\n```";

      const githubIssueUrl = new URL(
        "https://github.com/appwrite/appwrite/issues/new",
      );
      githubIssueUrl.searchParams.append("labels", "bug");
      githubIssueUrl.searchParams.append("template", "bug.yaml");
      githubIssueUrl.searchParams.append(
        "title",
        `🐛 Bug Report: ${err.message}`,
      );
      githubIssueUrl.searchParams.append(
        "actual-behavior",
        `CLI Error:\n${stack}`,
      );
      githubIssueUrl.searchParams.append(
        "steps-to-reproduce",
        stepsToReproduce,
      );
      githubIssueUrl.searchParams.append("environment", yourEnvironment);

      log(
        `To report this error you can:\n - Create a support ticket in our Discord server https://appwrite.io/discord \n - Create an issue in our Github\n   ${githubIssueUrl.href}\n`,
      );

      error("\n Stack Trace: \n");
      console.error(err);
      process.exit(1);
    })();
  } else {
    if (cliConfig.verbose) {
      console.error(err);
    } else {
      log("For detailed error pass the --verbose or --report flag");
      error(err.message);
    }
    process.exit(1);
  }
};

export const actionRunner = <
  T extends (...args: unknown[]) => Promise<unknown>,
>(
  fn: T,
): ((...args: Parameters<T>) => Promise<void>) => {
  return (...args: Parameters<T>) => {
    if (
      cliConfig.all &&
      Array.isArray(cliConfig.ids) &&
      cliConfig.ids.length !== 0
    ) {
      error(`The '--all' and '--id' flags cannot be used together.`);
      process.exit(1);
    }
    return fn(...args)
      .then(() => undefined)
      .catch(parseError);
  };
};

export const parseInteger = (value: string): number => {
  const parsedValue = parseInt(value, 10);
  if (isNaN(parsedValue)) {
    throw new InvalidArgumentError("Not a number.");
  }
  return parsedValue;
};

export const parseBool = (value: string): boolean => {
  if (value === "true") return true;
  if (value === "false") return false;
  throw new InvalidArgumentError("Not a boolean.");
};

export const log = (message?: string): void => {
  console.log(`${chalk.cyan.bold("ℹ Info:")} ${chalk.cyan(message ?? "")}`);
};

export const warn = (message?: string): void => {
  console.log(
    `${chalk.yellow.bold("ℹ Warning:")} ${chalk.yellow(message ?? "")}`,
  );
};

export const hint = (message?: string): void => {
  console.log(`${chalk.cyan.bold("♥ Hint:")} ${chalk.cyan(message ?? "")}`);
};

export const success = (message?: string): void => {
  console.log(
    `${chalk.green.bold("✓ Success:")} ${chalk.green(message ?? "")}`,
  );
};

export const error = (message?: string): void => {
  console.error(`${chalk.red.bold("✗ Error:")} ${chalk.red(message ?? "")}`);
};

export const logo = SDK_LOGO;

export const commandDescriptions: Record<string, string> = {
  account: `The account command allows you to authenticate and manage a user account.`,
  graphql: `The graphql command allows you to query and mutate any resource type on your Appwrite server.`,
  avatars: `The avatars command aims to help you complete everyday tasks related to your app image, icons, and avatars.`,
  databases: `(Legacy) The databases command allows you to create structured collections of documents and query and filter lists of documents.`,
  "tables-db": `The tables-db command allows you to create structured tables of columns and query and filter lists of rows.`,
  init: `The init command provides a convenient wrapper for creating and initializing projects, functions, collections, buckets, teams, messaging-topics, and agent skills in ${SDK_TITLE}.`,
  push: `The push command provides a convenient wrapper for pushing your functions, collections, buckets, teams, and messaging-topics.`,
  run: `The run command allows you to run the project locally to allow easy development and quick debugging.`,
  functions: `The functions command allows you to view, create, and manage your Cloud Functions.`,
  generate: `The generate command allows you to generate a type-safe SDK from your ${SDK_TITLE} project configuration.`,
  health: `The health command allows you to both validate and monitor your ${SDK_TITLE} server's health.`,
  pull: `The pull command helps you pull your ${SDK_TITLE} project, functions, collections, buckets, teams, and messaging-topics`,
  locale: `The locale command allows you to customize your app based on your users' location.`,
  sites: `The sites command allows you to view, create and manage your Appwrite Sites.`,
  storage: `The storage command allows you to manage your project files.`,
  teams: `The teams command allows you to group users of your project to enable them to share read and write access to your project resources. Requires a linked project. To manage console-level teams, use the 'organizations' command instead.`,
  update: `The update command allows you to update the ${SDK_TITLE} CLI to the latest version.`,
  users: `The users command allows you to manage your project users.`,
  projects: `The projects command allows you to manage your projects, add platforms, manage API keys, Dev Keys etc.`,
  project: `The project command allows you to manage project related resources like usage, variables, etc.`,
  client: `The client command allows you to configure your CLI`,
  login: `The login command allows you to authenticate and manage a user account.`,
  logout: `The logout command allows you to log out of your ${SDK_TITLE} account.`,
  whoami: `The whoami command gives information about the currently logged-in user.`,
  register: `Outputs the link to create an ${SDK_TITLE} account.`,
  console: `The console command gives you access to the APIs used by the Appwrite Console.`,
  messaging: `The messaging command allows you to manage topics and targets and send messages.`,
  migrations: `The migrations command allows you to migrate data between services.`,
  vcs: `The vcs command allows you to interact with VCS providers and manage your code repositories.`,
  webhooks: `The webhooks command allows you to manage your project webhooks.`,
  main: chalk.redBright(`${logo}${description}`),
};

export { cliConfig };
