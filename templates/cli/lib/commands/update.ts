import { spawn } from "child_process";
import { Command } from "commander";
import chalk from "chalk";
import inquirer from "inquirer";
import { success, log, warn, error, hint, actionRunner } from "../parser.js";
import { getLatestVersion, compareVersions } from "../utils.js";
import {
  GITHUB_RELEASES_URL,
  NPM_PACKAGE_NAME,
  SDK_TITLE,
} from "../constants.js";
import packageJson from "../../package.json" with { type: "json" };
const { version } = packageJson;

/**
 * Check if the CLI was installed via npm
 */
const isInstalledViaNpm = (): boolean => {
  try {
    const scriptPath = process.argv[1];

    if (
      scriptPath.includes("node_modules") &&
      scriptPath.includes(NPM_PACKAGE_NAME)
    ) {
      return true;
    }

    if (
      scriptPath.includes("/usr/local/lib/node_modules/") ||
      scriptPath.includes("/opt/homebrew/lib/node_modules/") ||
      scriptPath.includes("/.npm-global/") ||
      scriptPath.includes("/node_modules/.bin/")
    ) {
      return true;
    }

    return false;
  } catch (_e) {
    return false;
  }
};

/**
 * Check if the CLI was installed via Homebrew
 */
const isInstalledViaHomebrew = (): boolean => {
  try {
    const scriptPath = process.argv[1];
    return (
      scriptPath.includes("/opt/homebrew/") ||
      scriptPath.includes("/usr/local/Cellar/")
    );
  } catch (_e) {
    return false;
  }
};

/**
 * Execute command and return promise
 */
const execCommand = (
  command: string,
  args: string[] = [],
  options: any = {},
): Promise<void> => {
  return new Promise((resolve, reject) => {
    const child = spawn(command, args, {
      stdio: "inherit",
      shell: true,
      ...options,
    });

    child.on("close", (code) => {
      if (code === 0) {
        resolve();
      } else {
        reject(new Error(`Command failed with exit code ${code}`));
      }
    });

    child.on("error", (err) => {
      reject(err);
    });
  });
};

/**
 * Update via npm
 */
const updateViaNpm = async (): Promise<void> => {
  try {
    await execCommand("npm", ["install", "-g", `${NPM_PACKAGE_NAME}@latest`]);
    console.log("");
    success("Updated to latest version via npm!");
    hint("Run 'appwrite --version' to verify the new version.");
  } catch (e: any) {
    if (
      e.message.includes("EEXIST") ||
      e.message.includes("file already exists")
    ) {
      console.log("");
      success("Latest version is already installed via npm!");
      hint("The CLI is up to date. Run 'appwrite --version' to verify.");
    } else {
      console.log("");
      error(`Failed to update via npm: ${e.message}`);
      hint(`Try running: npm install -g ${NPM_PACKAGE_NAME}@latest --force`);
    }
  }
};

/**
 * Update via Homebrew
 */
const updateViaHomebrew = async (): Promise<void> => {
  try {
    await execCommand("brew", ["upgrade", "appwrite"]);
    console.log("");
    success("Updated to latest version via Homebrew!");
    hint("Run 'appwrite --version' to verify the new version.");
  } catch (e: any) {
    if (
      e.message.includes("already installed") ||
      e.message.includes("up-to-date")
    ) {
      console.log("");
      success("Latest version is already installed via Homebrew!");
      hint("The CLI is up to date. Run 'appwrite --version' to verify.");
    } else {
      console.log("");
      error(`Failed to update via Homebrew: ${e.message}`);
      hint("Try running: brew upgrade appwrite");
    }
  }
};

/**
 * Show manual update instructions
 */
const showManualInstructions = (latestVersion: string): void => {
  log("Manual update options:");
  console.log("");

  log(`${chalk.bold("Option 1: NPM")}`);
  console.log(`  npm install -g ${NPM_PACKAGE_NAME}@latest`);
  console.log("");

  log(`${chalk.bold("Option 2: Homebrew")}`);
  console.log(`  brew upgrade appwrite`);
  console.log("");

  log(`${chalk.bold("Option 3: Download Binary")}`);
  console.log(`  Visit: ${GITHUB_RELEASES_URL}/tag/${latestVersion}`);
};

/**
 * Show interactive menu for choosing update method
 */
const chooseUpdateMethod = async (latestVersion: string): Promise<void> => {
  const choices = [
    { name: "NPM", value: "npm" },
    { name: "Homebrew", value: "homebrew" },
    { name: "Show manual instructions", value: "manual" },
  ];

  const { method } = await inquirer.prompt([
    {
      type: "list",
      name: "method",
      message:
        "Could not detect installation method. How would you like to update?",
      choices: choices,
    },
  ]);

  switch (method) {
    case "npm":
      await updateViaNpm();
      break;
    case "homebrew":
      await updateViaHomebrew();
      break;
    case "manual":
      showManualInstructions(latestVersion);
      break;
  }
};

interface UpdateOptions {
  manual?: boolean;
}

/**
 * Main update function
 */
const updateCli = async ({ manual }: UpdateOptions = {}): Promise<void> => {
  try {
    const latestVersion = await getLatestVersion();

    const comparison = compareVersions(version, latestVersion);

    if (comparison === 0) {
      success(
        `You're already running the latest version (${chalk.bold(version)})!`,
      );
      return;
    } else if (comparison < 0) {
      warn(
        `You're running a newer version (${chalk.bold(version)}) than the latest released version (${chalk.bold(latestVersion)}).`,
      );
      hint("This might be a pre-release or development version.");
      return;
    }

    log(
      `Updating from ${chalk.blue(version)} to ${chalk.green(latestVersion)}...`,
    );
    console.log("");

    if (manual) {
      showManualInstructions(latestVersion);
      return;
    }

    if (isInstalledViaNpm()) {
      await updateViaNpm();
    } else if (isInstalledViaHomebrew()) {
      await updateViaHomebrew();
    } else {
      await chooseUpdateMethod(latestVersion);
    }
  } catch (e: any) {
    console.log("");
    error(`Failed to check for updates: ${e.message}`);
    hint(`You can manually check for updates at: ${GITHUB_RELEASES_URL}`);
  }
};

export const update = new Command("update")
  .description(`Update the ${SDK_TITLE} CLI to the latest version`)
  .option(
    "--manual",
    "Show manual update instructions instead of auto-updating",
  )
  .action(actionRunner(updateCli));
