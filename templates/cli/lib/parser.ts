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
  force: false,
  all: false,
  ids: [],
  report: false,
  reportData: {},
};

export const parse = (data: Record<string, any>): void => {
  if (cliConfig.json) {
    drawJSON(data);
    return;
  }

  for (const key in data) {
    if (data[key] === null) {
      console.log(`${chalk.yellow.bold(key)} : null`);
    } else if (Array.isArray(data[key])) {
      console.log(`${chalk.yellow.bold.underline(key)}`);
      if (typeof data[key][0] === "object") {
        drawTable(data[key]);
      } else {
        drawJSON(data[key]);
      }
    } else if (typeof data[key] === "object") {
      if (data[key]?.constructor?.name === "BigNumber") {
        console.log(`${chalk.yellow.bold(key)} : ${data[key]}`);
      } else {
        console.log(`${chalk.yellow.bold.underline(key)}`);
        drawTable([data[key]]);
      }
    } else {
      console.log(`${chalk.yellow.bold(key)} : ${data[key]}`);
    }
  }
};

export const drawTable = (
  data: Array<Record<string, any> | null | undefined>,
): void => {
  if (data.length == 0) {
    console.log("[]");
    return;
  }

  const rows = data.map((item) =>
    item && typeof item === "object" && !Array.isArray(item) ? item : {},
  );

  // Create an object with all the keys in it
  const obj = rows.reduce((res, item) => ({ ...res, ...item }), {});
  // Get those keys as an array
  const keys = Object.keys(obj);
  if (keys.length === 0) {
    drawJSON(data);
    return;
  }
  // Create an object with all keys set to the default value ''
  const def = keys.reduce((result: Record<string, string>, key) => {
    result[key] = "-";
    return result;
  }, {});
  // Use object destructuring to replace all default values with the ones we have
  const normalizedData = rows.map((item) => ({ ...def, ...item }));

  const columns = Object.keys(normalizedData[0]);

  const table = new Table({
    head: columns.map((c) => chalk.cyan.italic.bold(c)),
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
    const rowValues: any[] = [];
    for (const key of columns) {
      if (row[key] == null) {
        rowValues.push("-");
      } else if (Array.isArray(row[key])) {
        rowValues.push(JSON.stringify(row[key]));
      } else if (typeof row[key] === "object") {
        rowValues.push(JSON.stringify(row[key]));
      } else {
        rowValues.push(row[key]);
      }
    }
    table.push(rowValues);
  });
  console.log(table.toString());
};

export const drawJSON = (data: any): void => {
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
      const stepsToReproduce = `Running \`${EXECUTABLE_NAME} ${(cliConfig.reportData as any).data.args.join(" ")}\``;
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

export const actionRunner = <T extends (...args: any[]) => Promise<any>>(
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
    return fn(...args).catch(parseError);
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
  teams: `The teams command allows you to group users of your project to enable them to share read and write access to your project resources.`,
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
  main: chalk.redBright(`${logo}${description}`),
};

export { cliConfig };
