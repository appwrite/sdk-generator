// @ts-expect-error BigInt toJSON polyfill for JSON.stringify support
BigInt.prototype.toJSON = function () { return this.toString(); };

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
  force: false,
  all: false,
  ids: [],
  report: false,
  reportData: {},
};

type JsonObject = Record<string, unknown>;

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

const filterObject = (obj: JsonObject): JsonObject => {
  const result: JsonObject = {};
  for (const key of Object.keys(obj)) {
    const value = obj[key];
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

const filterData = (data: JsonObject): JsonObject => {
  const result: JsonObject = {};
  for (const key of Object.keys(data)) {
    const value = data[key];
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
  if (cliConfig.raw) {
    drawJSON(data);
    return;
  }

  if (cliConfig.json) {
    drawJSON(filterData(data));
    return;
  }

  const keys = Object.keys(data).filter((k) => typeof data[k] !== "function");
  let printedScalar = false;

  for (const key of keys) {
    const value = data[key];
    if (value === null) {
      console.log(`${chalk.yellow.bold(key)} : null`);
      printedScalar = true;
    } else if (Array.isArray(value)) {
      if (printedScalar) console.log("");
      console.log(`${chalk.yellow.bold.underline(key)}`);
      if (typeof value[0] === "object") {
        drawTable(value);
      } else {
        drawJSON(value);
      }
      printedScalar = false;
    } else if (typeof value === "object") {
      if (printedScalar) console.log("");
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
  if (data.length == 0) {
    console.log("[]");
    return;
  }

  const rows = data.map((item): JsonObject => toJsonObject(item) ?? {});

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

    const separatorLen = Math.min(maxKeyLen + 2 + MAX_COL_WIDTH, process.stdout.columns || 80);

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
  init: `The init command provides a convenient wrapper for creating and initializing projects, functions, collections, buckets, teams, and messaging-topics in ${SDK_TITLE}.`,
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
