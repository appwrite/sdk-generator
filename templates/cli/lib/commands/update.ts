import fs from "fs";
import os from "os";
import path from "path";
import { spawn } from "child_process";
import { Command } from "commander";
import chalk from "chalk";
import inquirer from "inquirer";
import { success, log, warn, error, hint, actionRunner } from "../parser.js";
import {
  getLatestVersionForInstallation,
  compareVersions,
  getErrorMessage,
} from "../utils.js";
import {
  EXECUTABLE_NAME,
  GITHUB_RELEASES_URL,
  NPM_PACKAGE_NAME,
  SDK_TITLE,
} from "../constants.js";
import packageJson from "../../package.json" with { type: "json" };
const { version } = packageJson;

type ExecCommandOptions = Exclude<Parameters<typeof spawn>[2], undefined>;
type InstallationMethod = "npm" | "homebrew" | "standalone";

const WINDOWS_EXECUTABLE_NAME = `${EXECUTABLE_NAME.toLowerCase()}.exe`;

const getExecutablePaths = (): {
  execPath: string;
  realExecPath: string;
  scriptPath: string;
} => {
  const execPath = process.execPath;
  const scriptPath = process.argv[1] ?? "";

  try {
    return {
      execPath,
      realExecPath: fs.realpathSync.native(execPath),
      scriptPath,
    };
  } catch (_error) {
    return {
      execPath,
      realExecPath: execPath,
      scriptPath,
    };
  }
};

const isExecutableName = (candidatePath: string): boolean => {
  const basename = path.basename(candidatePath).toLowerCase();
  return (
    basename === EXECUTABLE_NAME.toLowerCase() ||
    basename === WINDOWS_EXECUTABLE_NAME
  );
};

/**
 * Check if the CLI was installed via npm
 */
const isInstalledViaNpm = (): boolean => {
  try {
    const { scriptPath } = getExecutablePaths();

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
      scriptPath.includes("/node_modules/.bin/") ||
      scriptPath.includes("/.nvm/versions/node/")
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
    const { execPath, realExecPath, scriptPath } = getExecutablePaths();
    const candidates = [scriptPath, execPath, realExecPath];

    return candidates.some(
      (candidate) =>
        candidate.includes("/opt/homebrew/") ||
        candidate.includes("/usr/local/Cellar/") ||
        candidate.includes("/home/linuxbrew/.linuxbrew/") ||
        candidate.includes("/linuxbrew/.linuxbrew/"),
    );
  } catch (_e) {
    return false;
  }
};

/**
 * Check if the CLI was installed via install script or native binary download
 */
const isInstalledViaStandaloneBinary = (): boolean => {
  if (isInstalledViaNpm() || isInstalledViaHomebrew()) {
    return false;
  }

  try {
    const { execPath, realExecPath } = getExecutablePaths();
    return [execPath, realExecPath].some(isExecutableName);
  } catch (_e) {
    return false;
  }
};

const detectInstallationMethod = (): InstallationMethod | null => {
  if (isInstalledViaNpm()) {
    return "npm";
  }

  if (isInstalledViaHomebrew()) {
    return "homebrew";
  }

  if (isInstalledViaStandaloneBinary()) {
    return "standalone";
  }

  return null;
};

const getStandaloneBinaryArtifactName = (): string => {
  const platform =
    process.platform === "win32"
      ? "win"
      : process.platform === "darwin"
        ? "darwin"
        : process.platform === "linux"
          ? "linux"
          : null;

  if (!platform) {
    throw new Error(
      `Standalone binary updates are not supported on ${process.platform}.`,
    );
  }

  const arch = os.arch();
  if (arch !== "x64" && arch !== "arm64") {
    throw new Error(
      `Standalone binary updates are not supported on ${arch} architecture.`,
    );
  }

  const extension = platform === "win" ? ".exe" : "";
  return `${NPM_PACKAGE_NAME}-${platform}-${arch}${extension}`;
};

const getStandaloneBinaryTargetPath = (): string => {
  const { execPath } = getExecutablePaths();

  try {
    if (fs.lstatSync(execPath).isSymbolicLink()) {
      return fs.realpathSync.native(execPath);
    }
  } catch (_error) {
    // Fall back to the original exec path below.
  }

  return execPath;
};

const isDirectoryWritable = (directoryPath: string): boolean => {
  try {
    fs.accessSync(directoryPath, fs.constants.W_OK);
    return true;
  } catch (_error) {
    return false;
  }
};

const quoteShellArgument = (value: string): string => {
  return `'${value.replace(/'/g, `'\\''`)}'`;
};

const downloadStandaloneBinary = async (
  latestVersion: string,
  destinationPath: string,
): Promise<void> => {
  const artifact = getStandaloneBinaryArtifactName();
  const response = await fetch(
    `${GITHUB_RELEASES_URL}/download/${latestVersion}/${artifact}`,
  );

  if (!response.ok) {
    throw new Error(
      `Failed to download standalone binary (HTTP ${response.status}).`,
    );
  }

  const body = Buffer.from(await response.arrayBuffer());
  fs.writeFileSync(destinationPath, body);

  if (process.platform !== "win32") {
    fs.chmodSync(destinationPath, 0o755);
  }
};

/**
 * Execute command and return promise
 */
const execCommand = (
  command: string,
  args: string[] = [],
  options: ExecCommandOptions = {},
): Promise<void> => {
  return new Promise((resolve, reject) => {
    const child = spawn(command, args, {
      stdio: "inherit",
      shell: process.platform === "win32",
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
  } catch (e: unknown) {
    const message = getErrorMessage(e);

    if (message.includes("EEXIST") || message.includes("file already exists")) {
      console.log("");
      success("Latest version is already installed via npm!");
      hint("The CLI is up to date. Run 'appwrite --version' to verify.");
    } else {
      console.log("");
      error(`Failed to update via npm: ${message}`);
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
  } catch (e: unknown) {
    const message = getErrorMessage(e);

    if (
      message.includes("already installed") ||
      message.includes("up-to-date")
    ) {
      console.log("");
      success("Latest version is already installed via Homebrew!");
      hint("The CLI is up to date. Run 'appwrite --version' to verify.");
    } else {
      console.log("");
      error(`Failed to update via Homebrew: ${message}`);
      hint("Try running: brew upgrade appwrite");
    }
  }
};

/**
 * Update a standalone/native binary install
 */
const updateViaStandaloneBinary = async (
  latestVersion: string,
): Promise<void> => {
  if (process.platform === "win32") {
    showManualInstructions(latestVersion);
    return;
  }

  const targetPath = getStandaloneBinaryTargetPath();
  const targetDir = path.dirname(targetPath);
  const tempName = `${path.basename(targetPath)}.tmp-${process.pid}`;
  const writableDirectory = isDirectoryWritable(targetDir);
  const tempPath = writableDirectory
    ? path.join(targetDir, tempName)
    : path.join(os.tmpdir(), tempName);

  try {
    await downloadStandaloneBinary(latestVersion, tempPath);

    if (writableDirectory) {
      fs.renameSync(tempPath, targetPath);
    } else {
      const stagedTargetPath = path.join(targetDir, tempName);
      const command = [
        `install -m 755 ${quoteShellArgument(tempPath)} ${quoteShellArgument(stagedTargetPath)}`,
        `mv -f ${quoteShellArgument(stagedTargetPath)} ${quoteShellArgument(targetPath)}`,
      ].join(" && ");

      await execCommand("sudo", ["sh", "-c", command]);
    }

    console.log("");
    success("Updated to latest version via standalone binary!");
    hint("Run 'appwrite --version' to verify the new version.");
  } catch (e: unknown) {
    const message = getErrorMessage(e);
    console.log("");
    error(`Failed to update standalone binary: ${message}`);
    hint(`Download the latest release manually at: ${GITHUB_RELEASES_URL}`);
  } finally {
    try {
      if (fs.existsSync(tempPath)) {
        fs.unlinkSync(tempPath);
      }
    } catch (_error) {
      // Ignore cleanup failures.
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

  if (process.platform !== "win32") {
    try {
      const artifact = getStandaloneBinaryArtifactName();
      const targetPath = getStandaloneBinaryTargetPath();
      const tempPath = path.join(os.tmpdir(), path.basename(targetPath));

      log(`${chalk.bold("Option 3: Install Script / Standalone Binary")}`);
      console.log(
        `  curl -fsSL ${GITHUB_RELEASES_URL}/download/${latestVersion}/${artifact} -o ${tempPath}`,
      );
      console.log(`  chmod +x ${tempPath}`);
      console.log(`  sudo mv -f ${tempPath} ${targetPath}`);
      console.log("");
    } catch (_error) {
      // Fall back to the release page below when the current platform is unsupported.
    }
  }

  log(`${chalk.bold("Option 4: Download Binary")}`);
  console.log(`  Visit: ${GITHUB_RELEASES_URL}/tag/${latestVersion}`);
};

/**
 * Show interactive menu for choosing update method
 */
const chooseUpdateMethod = async (latestVersion: string): Promise<void> => {
  const choices = [
    { name: "NPM", value: "npm" },
    { name: "Homebrew", value: "homebrew" },
    ...(process.platform === "win32"
      ? []
      : [
          {
            name: "Install script / standalone binary",
            value: "standalone",
          },
        ]),
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
    case "standalone":
      await updateViaStandaloneBinary(latestVersion);
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
    const installationMethod = detectInstallationMethod();
    const latestVersion = await getLatestVersionForInstallation(
      installationMethod,
    );

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

    switch (installationMethod) {
      case "npm":
        await updateViaNpm();
        break;
      case "homebrew":
        await updateViaHomebrew();
        break;
      case "standalone":
        await updateViaStandaloneBinary(latestVersion);
        break;
      default:
        await chooseUpdateMethod(latestVersion);
        break;
    }
  } catch (e: unknown) {
    const message = getErrorMessage(e);
    console.log("");
    error(`Failed to check for updates: ${message}`);
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
