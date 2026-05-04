import fs from "fs";
import path from "path";
import readline from "node:readline";
import { stripVTControlCharacters } from "node:util";
import { parse as parseDotenv } from "dotenv";
import chalk from "chalk";
import inquirer from "inquirer";
import { Command } from "commander";
import ID from "../id.js";
import { EXECUTABLE_NAME } from "../constants.js";
import {
  localConfig,
  globalConfig,
  KeysFunction,
  KeysSite,
  KeysTopics,
  KeysStorage,
  KeysTeams,
  KeysWebhooks,
  KeysCollection,
  KeysTable,
} from "../config.js";
import {
  ConfigSchema,
  type SettingsType,
  type ConfigType,
  type CollectionType,
} from "./config.js";
import { parseWithBetterErrors } from "./utils/error-formatter.js";
import {
  createSettingsObject,
  checkDeployConditions,
  arrayEqualsUnordered,
  getFunctionDeploymentConsoleUrl,
  getSiteDeploymentConsoleUrl,
  siteRequiresBuildCommand,
} from "../utils.js";
import { Spinner, SPINNER_DOTS } from "../spinner.js";
import { paginate } from "../paginate.js";
import {
  createDeploymentLogPrinter,
  pushDeployment,
  watchDeploymentUpdates,
} from "./utils/deployment.js";
import {
  questionsPushBuckets,
  questionsPushTeams,
  questionsPushFunctions,
  questionsPushFunctionsCode,
  questionsPushFunctionsActivate,
  questionsPushSites,
  questionsPushSitesActivate,
  questionsPushSitesCode,
  questionsGetBuildCommand,
  questionsGetEntrypoint,
  questionsPushCollections,
  questionsPushTables,
  questionsPushMessagingTopics,
  questionsPushWebhooks,
  questionsPushResources,
} from "../questions.js";
import {
  cliConfig,
  actionRunner,
  success,
  warn,
  log,
  hint,
  error,
  commandDescriptions,
  drawTable,
  parseBool,
} from "../parser.js";
import {
  getProxyService,
  getConsoleService,
  getFunctionsService,
  getSitesService,
  getDatabasesService,
  getTablesDBService,
  getStorageService,
  getMessagingService,
  getTeamsService,
  getWebhooksService,
  getProjectService,
  getProjectsService,
} from "../services.js";
import { sdkForProject, sdkForConsole } from "../sdks.js";
import {
  ServiceId,
  ProtocolId,
  MethodId,
  AppwriteException,
  Client,
  ImageFormat,
  Query,
} from "@appwrite.io/console";
import { Pools } from "./utils/pools.js";
import { Attributes, Collection } from "./utils/attributes.js";
import {
  getConfirmation,
  approveChanges,
  getObjectChanges,
} from "./utils/change-approval.js";
import { checkAndApplyTablesDBChanges } from "./utils/database-sync.js";

const POLL_DEBOUNCE = 2000; // Milliseconds
const POLL_DEFAULT_VALUE = 30;
const DEPLOYMENT_TIMEOUT_MS = 10 * 60 * 1000; // 10 minutes
const DEPLOYMENT_TIMEOUT_MINUTES = Math.round(
  DEPLOYMENT_TIMEOUT_MS / (60 * 1000),
);
const SITE_SCREENSHOT_FINALIZATION_TIMEOUT_MS = 30 * 1000; // 30 seconds
const SITE_SCREENSHOT_BUCKET_ID = "screenshots";
const SITE_SCREENSHOT_PREVIEW_WIDTH = 480;
const SITE_SCREENSHOT_PREVIEW_HEIGHT = 270;
const SITE_TERMINAL_PREVIEW_TARGET_WIDTH = 72;
const SITE_TERMINAL_PREVIEW_MAX_WIDTH = 80;
const SITE_TERMINAL_PREVIEW_MAX_HEIGHT = 22;
const SITE_TERMINAL_PREVIEW_MIN_HEIGHT = 8;
const WAITING_JOKE_THRESHOLD_MS = 30 * 1000; // 30 seconds
const WAITING_JOKE_URL = "https://xkcd.com/303/";
const ANSI_RESET = "\u001B[0m";

type TerminalImageModule = typeof import("terminal-image");

let terminalImageModulePromise:
  | Promise<TerminalImageModule["default"]>
  | undefined;

function getDeploymentProgressText(
  status: string,
  waitingSince: number | null,
): string {
  if (
    status === "waiting" &&
    waitingSince !== null &&
    Date.now() - waitingSince >= WAITING_JOKE_THRESHOLD_MS
  ) {
    return `Still waiting... ${WAITING_JOKE_URL}`;
  }

  return `Status: ${status}`;
}

function getDeploymentTimeoutErrorMessage(): string {
  return `Deployment got stuck for more than ${DEPLOYMENT_TIMEOUT_MINUTES} minutes`;
}

function getDeploymentProgressSignature(
  deployment: Record<string, unknown>,
): string {
  const status =
    typeof deployment["status"] === "string" ? deployment["status"] : "";
  const buildLogs =
    typeof deployment["buildLogs"] === "string"
      ? deployment["buildLogs"]
      : "";
  const updatedAt =
    typeof deployment["$updatedAt"] === "string"
      ? deployment["$updatedAt"]
      : "";
  const { screenshotLight, screenshotDark } =
    getSiteDeploymentScreenshots(deployment);

  return JSON.stringify({
    status,
    updatedAt,
    buildLogsLength: buildLogs.length,
    buildLogsTail: buildLogs.slice(-200),
    screenshotLight: screenshotLight ?? "",
    screenshotDark: screenshotDark ?? "",
  });
}

function createDeploymentTimeoutTracker(
  deployment: Record<string, unknown>,
): {
  touch: (deployment: Record<string, unknown>) => void;
  hasTimedOut: () => boolean;
} {
  let lastActivityAt = Date.now();
  let lastSignature: string | null = null;

  const touch = (nextDeployment: Record<string, unknown>): void => {
    const nextSignature = getDeploymentProgressSignature(nextDeployment);
    if (nextSignature === lastSignature) {
      return;
    }

    lastSignature = nextSignature;
    lastActivityAt = Date.now();
  };

  touch(deployment);

  return {
    touch,
    hasTimedOut: () => Date.now() - lastActivityAt > DEPLOYMENT_TIMEOUT_MS,
  };
}

async function getTerminalImage() {
  terminalImageModulePromise ??= import("terminal-image").then(
    (module) => module.default,
  );

  return terminalImageModulePromise;
}

function getErrorMessage(error: unknown): string {
  if (error instanceof Error && error.message.trim().length > 0) {
    return error.message.trim();
  }

  if (Array.isArray(error)) {
    const messages = error
      .map((entry) => getErrorMessage(entry))
      .filter((entry) => entry !== "Unknown error");

    if (messages.length > 0) {
      return messages.join("; ");
    }
  }

  if (typeof error === "string" && error.trim().length > 0) {
    return error.trim();
  }

  if (error && typeof error === "object") {
    const objectMessage = Reflect.get(error, "message");

    if (typeof objectMessage === "string" && objectMessage.trim().length > 0) {
      return objectMessage.trim();
    }

    try {
      return JSON.stringify(error);
    } catch (_serializationError) {
      // Ignore serialization failures and fall through to a generic message.
    }
  }

  return "Unknown error";
}

function getSiteDeploymentScreenshots(deployment: Record<string, unknown>): {
  screenshotLight?: string;
  screenshotDark?: string;
} {
  const screenshotLight =
    typeof deployment["screenshotLight"] === "string" &&
    deployment["screenshotLight"].trim().length > 0
      ? deployment["screenshotLight"]
      : undefined;
  const screenshotDark =
    typeof deployment["screenshotDark"] === "string" &&
    deployment["screenshotDark"].trim().length > 0
      ? deployment["screenshotDark"]
      : undefined;

  return {
    screenshotLight,
    screenshotDark,
  };
}

function hasSiteDeploymentScreenshots(
  deployment: Record<string, unknown>,
): boolean {
  const { screenshotLight, screenshotDark } =
    getSiteDeploymentScreenshots(deployment);

  return Boolean(screenshotLight || screenshotDark);
}

function shouldRenderSiteTerminalPreview(): boolean {
  return Boolean(process.stdout.isTTY) && !cliConfig.json && !cliConfig.raw;
}

function getSiteTerminalPreviewWidth(): number {
  const columns = process.stdout.columns ?? 80;
  return Math.max(
    16,
    Math.min(
      columns - 4,
      SITE_TERMINAL_PREVIEW_TARGET_WIDTH,
      SITE_TERMINAL_PREVIEW_MAX_WIDTH,
    ),
  );
}

function getSiteTerminalPreviewHeight(): number {
  const rows = process.stdout.rows ?? 24;
  return Math.max(
    SITE_TERMINAL_PREVIEW_MIN_HEIGHT,
    Math.min(rows - 10, SITE_TERMINAL_PREVIEW_MAX_HEIGHT),
  );
}

async function renderImageBufferToTerminalPreview(
  buffer: Buffer,
): Promise<string> {
  const terminalImage = await getTerminalImage();
  const originalTermProgram = process.env.TERM_PROGRAM;
  const originalTermProgramVersion = process.env.TERM_PROGRAM_VERSION;
  const originalKonsoleVersion = process.env.KONSOLE_VERSION;
  const originalKittyWindowId = process.env.KITTY_WINDOW_ID;

  // Force terminal-image's ANSI fallback so the preview is portable and can
  // be framed consistently in the deploy summary.
  delete process.env.TERM_PROGRAM;
  delete process.env.TERM_PROGRAM_VERSION;
  delete process.env.KONSOLE_VERSION;
  delete process.env.KITTY_WINDOW_ID;

  try {
    return await terminalImage.buffer(buffer, {
      width: getSiteTerminalPreviewWidth(),
      height: getSiteTerminalPreviewHeight(),
      preserveAspectRatio: true,
    });
  } finally {
    if (originalTermProgram === undefined) {
      delete process.env.TERM_PROGRAM;
    } else {
      process.env.TERM_PROGRAM = originalTermProgram;
    }

    if (originalTermProgramVersion === undefined) {
      delete process.env.TERM_PROGRAM_VERSION;
    } else {
      process.env.TERM_PROGRAM_VERSION = originalTermProgramVersion;
    }

    if (originalKonsoleVersion === undefined) {
      delete process.env.KONSOLE_VERSION;
    } else {
      process.env.KONSOLE_VERSION = originalKonsoleVersion;
    }

    if (originalKittyWindowId === undefined) {
      delete process.env.KITTY_WINDOW_ID;
    } else {
      process.env.KITTY_WINDOW_ID = originalKittyWindowId;
    }
  }
}

async function fetchSiteScreenshotPreview(params: {
  renderer: SiteTerminalPreviewRenderer;
  fileId: string;
}): Promise<string> {
  const previewUrl = params.renderer.storageService.getFilePreview({
    bucketId: SITE_SCREENSHOT_BUCKET_ID,
    fileId: params.fileId,
    width: SITE_SCREENSHOT_PREVIEW_WIDTH,
    height: SITE_SCREENSHOT_PREVIEW_HEIGHT,
    output: ImageFormat.Png,
  });

  const imageData = await params.renderer.consoleClient.call(
    "get",
    new URL(previewUrl),
    {},
    {},
    "arrayBuffer",
  );

  if (!(imageData instanceof ArrayBuffer)) {
    throw new Error("Failed to download the site screenshot preview.");
  }

  return renderImageBufferToTerminalPreview(Buffer.from(imageData));
}

function frameTerminalPreview(preview: string): string {
  const lines = preview.split("\n").map((line) => line.replace(/\s+$/, ""));

  while (
    lines.length > 0 &&
    stripVTControlCharacters(lines[0]).trim().length === 0
  ) {
    lines.shift();
  }

  while (
    lines.length > 0 &&
    stripVTControlCharacters(lines[lines.length - 1]).trim().length === 0
  ) {
    lines.pop();
  }

  if (lines.length === 0) {
    return "";
  }

  const contentWidth = Math.max(
    ...lines.map((line) => stripVTControlCharacters(line).length),
  );
  const border = `+-${"-".repeat(contentWidth)}-+`;

  return [
    border,
    ...lines.map((line) => {
      const visibleWidth = stripVTControlCharacters(line).length;

      return `| ${line}${ANSI_RESET}${" ".repeat(contentWidth - visibleWidth)} |`;
    }),
    border,
  ].join("\n");
}

async function renderSiteTerminalPreview(params: {
  renderer: SiteTerminalPreviewRenderer;
  screenshotLight?: string;
  screenshotDark?: string;
}): Promise<SiteTerminalPreviewResult> {
  const warnings = new Set<string>();
  const candidates: Array<{
    fileId?: string;
    label: "dark" | "light";
  }> = [
    { fileId: params.screenshotDark, label: "dark" },
    { fileId: params.screenshotLight, label: "light" },
  ];

  for (const candidate of candidates) {
    const { fileId, label } = candidate;

    if (!fileId) {
      continue;
    }

    try {
      const preview = await fetchSiteScreenshotPreview({
        renderer: params.renderer,
        fileId,
      });

      return {
        preview: frameTerminalPreview(preview),
        warnings: [],
      };
    } catch (previewError) {
      warnings.add(
        `${label === "dark" ? "Dark mode" : "Light mode"} screenshot: ${getErrorMessage(previewError)}`,
      );
    }
  }

  return {
    preview: undefined,
    warnings: [...warnings],
  };
}

interface FailedDeployment {
  name: string;
  $id: string;
  deployment: string;
  reason?: "failed" | "timeout";
  consoleUrl?: string;
}

interface SiteDeploymentSummary {
  name: string;
  url: string;
  consoleUrl: string;
  screenshotLight?: string;
  screenshotDark?: string;
  screenshotsPending?: boolean;
}

interface SiteTerminalPreviewRenderer {
  consoleClient: Client;
  storageService: Awaited<ReturnType<typeof getStorageService>>;
}

interface SiteTerminalPreviewResult {
  preview?: string;
  warnings: string[];
}

type DeploymentLogPrinterHandle = ReturnType<typeof createDeploymentLogPrinter>;

interface DeploymentLogsController {
  isVisible: () => boolean;
  getToggleHint: () => string;
  onToggle: (listener: () => void) => () => void;
  registerPrinter: (printer: DeploymentLogPrinterHandle) => void;
  close: () => void;
}

function withDeploymentLogsHint(
  message: string,
  deploymentLogsController: DeploymentLogsController,
): string {
  const hint = deploymentLogsController.getToggleHint();
  return hint.length > 0 ? `${message} • ${hint}` : message;
}

function createDeploymentLogsController(
  enabled: boolean,
): DeploymentLogsController {
  const printers = new Set<DeploymentLogPrinterHandle>();
  const listeners = new Set<() => void>();
  let isVisible = enabled;

  if (!enabled || !process.stdin.isTTY) {
    return {
      isVisible: () => isVisible,
      getToggleHint: () => "",
      onToggle: () => () => {},
      registerPrinter: () => {},
      close: () => {},
    };
  }

  const stdin = process.stdin as NodeJS.ReadStream & {
    isRaw?: boolean;
    setRawMode?: (mode: boolean) => void;
  };

  if (typeof stdin.setRawMode !== "function") {
    return {
      isVisible: () => isVisible,
      getToggleHint: () => "",
      onToggle: () => () => {},
      registerPrinter: () => {},
      close: () => {},
    };
  }

  let isClosed = false;

  const cleanup = (): void => {
    if (isClosed) {
      return;
    }

    isClosed = true;
    stdin.off("keypress", onKeypress);
    if (shouldRestoreRawMode) {
      stdin.setRawMode(false);
    }
    stdin.pause();
  };

  const onKeypress = (
    input: string,
    key: { ctrl?: boolean; meta?: boolean; name?: string } | undefined,
  ): void => {
    if (!key) {
      return;
    }

    if (key.ctrl && key.name === "c") {
      cleanup();
      process.kill(process.pid, "SIGINT");
      return;
    }

    const pressedKey = (key.name ?? input ?? "").toLowerCase();
    if (pressedKey !== "l" || key.ctrl || key.meta) {
      return;
    }

    isVisible = !isVisible;

    for (const listener of listeners) {
      listener();
    }

    if (isVisible) {
      for (const printer of printers) {
        printer.flush();
      }
    }
  };

  readline.emitKeypressEvents(stdin);
  stdin.resume();

  const shouldRestoreRawMode = stdin.isRaw !== true;
  if (shouldRestoreRawMode) {
    stdin.setRawMode(true);
  }

  stdin.on("keypress", onKeypress);

  return {
    isVisible: () => isVisible,
    getToggleHint: (): string =>
      isVisible
        ? "Press l to pause log stream"
        : "Press l to resume log stream",
    onToggle: (listener: () => void): (() => void) => {
      listeners.add(listener);
      return () => {
        listeners.delete(listener);
      };
    },
    registerPrinter: (printer: DeploymentLogPrinterHandle): void => {
      printers.add(printer);
      if (isVisible) {
        printer.flush();
      }
    },
    close: cleanup,
  };
}

export interface PushOptions {
  all?: boolean;
  settings?: boolean;
  functions?: boolean;
  sites?: boolean;
  collections?: boolean;
  tables?: boolean;
  buckets?: boolean;
  teams?: boolean;
  webhooks?: boolean;
  topics?: boolean;
  skipDeprecated?: boolean;
  skipConfirmation?: boolean;
  force?: boolean;
  functionOptions?: {
    async?: boolean;
    code?: boolean;
    activate?: boolean;
    withVariables?: boolean;
    logs?: boolean;
  };
  siteOptions?: {
    async?: boolean;
    code?: boolean;
    activate?: boolean;
    withVariables?: boolean;
    logs?: boolean;
  };
  tableOptions?: {
    attempts?: number;
  };
}

interface PushSiteOptions {
  siteId?: string;
  async?: boolean;
  code?: boolean;
  activate?: boolean;
  withVariables?: boolean;
  logs?: boolean;
}

interface PushFunctionOptions {
  functionId?: string;
  async?: boolean;
  code?: boolean;
  activate?: boolean;
  withVariables?: boolean;
  logs?: boolean;
}

interface PushTableOptions {
  attempts?: number;
  skipConfirmation?: boolean;
}

interface PushCollectionInput extends CollectionType {
  databaseName?: string;
}

interface PushCollectionState extends PushCollectionInput {
  remoteVersion?: {
    name: string;
    attributes: object[];
    indexes: object[];
  };
  isExisted?: boolean;
  isNewlyCreated?: boolean;
}

const normalizeIgnoreRules = (value: unknown): string[] => {
  if (Array.isArray(value)) {
    return value.filter(
      (rule): rule is string => typeof rule === "string" && rule.length > 0,
    );
  }

  if (typeof value === "string" && value.length > 0) {
    return value
      .split(/\r?\n/)
      .map((rule) => rule.trim())
      .filter((rule) => rule.length > 0);
  }

  return [];
};

export class Push {
  private projectClient: Client;
  private consoleClient: Client;
  private silent: boolean;

  constructor(projectClient: Client, consoleClient: Client, silent = false) {
    this.projectClient = projectClient;
    this.consoleClient = consoleClient;
    this.silent = silent;
  }

  /**
   * Log a message (respects silent mode)
   */
  private log(message: string): void {
    if (!this.silent) {
      log(message);
    }
  }

  /**
   * Log a success message (respects silent mode)
   */
  private success(message: string): void {
    if (!this.silent) {
      success(message);
    }
  }

  /**
   * Log a warning message (respects silent mode)
   */
  private warn(message: string): void {
    if (!this.silent) {
      warn(message);
    }
  }

  /**
   * Log an error message (respects silent mode)
   */
  private error(message: string): void {
    if (!this.silent) {
      error(message);
    }
  }

  public async pushResources(
    config: ConfigType,
    options: PushOptions = { all: true, skipDeprecated: true },
  ): Promise<{
    results: Record<string, any>;
    errors: any[];
  }> {
    const previousForce = cliConfig.force;
    if (options.force !== undefined) {
      cliConfig.force = options.force;
    }

    try {
      const { skipDeprecated = true } = options;
      const results: Record<string, any> = {};
      const allErrors: any[] = [];
      const shouldPushAll = options.all === true;

      // Push settings
      if (
        (shouldPushAll || options.settings) &&
        (config.projectName || config.settings)
      ) {
        try {
          this.log("Pushing settings ...");
          await this.pushSettings({
            projectId: config.projectId,
            projectName: config.projectName,
            settings: config.settings,
          });
          this.success(
            `Successfully pushed ${chalk.bold("all")} project settings.`,
          );
          results.settings = { success: true };
        } catch (e: any) {
          allErrors.push(e);
          results.settings = { success: false, error: e.message };
        }
      }

      // Push buckets
      if (
        (shouldPushAll || options.buckets) &&
        config.buckets &&
        config.buckets.length > 0
      ) {
        try {
          this.log("Pushing buckets ...");
          const result = await this.pushBuckets(config.buckets);
          this.success(
            `Successfully pushed ${chalk.bold(result.successfullyPushed)} buckets.`,
          );
          results.buckets = result;
          allErrors.push(...result.errors);
        } catch (e: any) {
          allErrors.push(e);
          results.buckets = { successfullyPushed: 0, errors: [e] };
        }
      }

      // Push teams
      if (
        (shouldPushAll || options.teams) &&
        config.teams &&
        config.teams.length > 0
      ) {
        try {
          this.log("Pushing teams ...");
          const result = await this.pushTeams(config.teams);
          this.success(
            `Successfully pushed ${chalk.bold(result.successfullyPushed)} teams.`,
          );
          results.teams = result;
          allErrors.push(...result.errors);
        } catch (e: any) {
          allErrors.push(e);
          results.teams = { successfullyPushed: 0, errors: [e] };
        }
      }

      // Push webhooks
      if (
        (shouldPushAll || options.webhooks) &&
        config.webhooks &&
        config.webhooks.length > 0
      ) {
        try {
          this.log("Pushing webhooks ...");
          const result = await this.pushWebhooks(config.webhooks);
          this.success(
            `Successfully pushed ${chalk.bold(result.successfullyPushed)} webhooks.`,
          );
          results.webhooks = result;
          allErrors.push(...result.errors);
        } catch (e: any) {
          allErrors.push(e);
          results.webhooks = { successfullyPushed: 0, errors: [e] };
        }
      }

      // Push messaging topics
      if (
        (shouldPushAll || options.topics) &&
        config.topics &&
        config.topics.length > 0
      ) {
        try {
          this.log("Pushing topics ...");
          const result = await this.pushMessagingTopics(config.topics);
          this.success(
            `Successfully pushed ${chalk.bold(result.successfullyPushed)} topics.`,
          );
          results.topics = result;
          allErrors.push(...result.errors);
        } catch (e: any) {
          allErrors.push(e);
          results.topics = { successfullyPushed: 0, errors: [e] };
        }
      }

      // Push functions
      if (
        (shouldPushAll || options.functions) &&
        config.functions &&
        config.functions.length > 0
      ) {
        try {
          this.log("Pushing functions ...");
          const result = await this.pushFunctions(
            config.functions,
            options.functionOptions,
          );
          this.success(
            `Successfully pushed ${chalk.bold(result.successfullyPushed)} functions.`,
          );
          results.functions = result;
          allErrors.push(...result.errors);
        } catch (e: any) {
          allErrors.push(e);
          results.functions = {
            successfullyPushed: 0,
            successfullyDeployed: 0,
            failedDeployments: [],
            errors: [e],
          };
        }
      }

      // Push sites
      if (
        (shouldPushAll || options.sites) &&
        config.sites &&
        config.sites.length > 0
      ) {
        try {
          this.log("Pushing sites ...");
          const result = await this.pushSites(
            config.sites,
            options.siteOptions,
          );
          this.success(
            `Successfully pushed ${chalk.bold(result.successfullyPushed)} sites.`,
          );
          results.sites = result;
          allErrors.push(...result.errors);
        } catch (e: any) {
          allErrors.push(e);
          results.sites = {
            successfullyPushed: 0,
            successfullyDeployed: 0,
            failedDeployments: [],
            errors: [e],
          };
        }
      }

      // Push tables
      if (
        (shouldPushAll || options.tables) &&
        config.tables &&
        config.tables.length > 0
      ) {
        try {
          this.log("Pushing tables ...");
          const result = await this.pushTables(config.tables, {
            attempts: options.tableOptions?.attempts,
            skipConfirmation: options.skipConfirmation,
          });
          this.success(
            `Successfully pushed ${chalk.bold(result.successfullyPushed)} tables.`,
          );
          results.tables = result;
          allErrors.push(...result.errors);
        } catch (e: any) {
          allErrors.push(e);
          results.tables = { successfullyPushed: 0, errors: [e] };
        }
      }

      // Push collections (skipDeprecated only applies when pushing all, explicit collections option takes precedence)
      if (
        (options.collections || (shouldPushAll && !skipDeprecated)) &&
        config.collections &&
        config.collections.length > 0
      ) {
        try {
          this.log("Pushing collections ...");
          // Add database names to collections
          const collectionsWithDbNames = config.collections.map(
            (collection: any) => {
              const database = config.databases?.find(
                (db: any) => db.$id === collection.databaseId,
              );
              return {
                ...collection,
                databaseName: database?.name ?? collection.databaseId,
              };
            },
          );
          const result = await this.pushCollections(collectionsWithDbNames, {
            skipConfirmation: options.skipConfirmation,
          });
          this.success(
            `Successfully pushed ${chalk.bold(result.successfullyPushed)} collections.`,
          );
          results.collections = result;
          allErrors.push(...result.errors);
        } catch (e: any) {
          allErrors.push(e);
          results.collections = { successfullyPushed: 0, errors: [e] };
        }
      }

      return {
        results,
        errors: allErrors,
      };
    } finally {
      cliConfig.force = previousForce;
    }
  }

  public async pushSettings(config: {
    projectId: string;
    projectName?: string;
    settings?: SettingsType;
  }): Promise<void> {
    const projectsService = await getProjectsService(this.consoleClient);
    const projectId = config.projectId;
    const projectService = await getProjectService();
    const projectName = config.projectName;
    const settings = config.settings ?? {};

    if (projectName) {
      this.log("Applying project name ...");
      await projectsService.update({
        projectId: projectId,
        name: projectName,
      });
    }

    if (settings.services) {
      this.log("Applying service statuses ...");
      for (const [service, status] of Object.entries(settings.services)) {
        await projectService.updateService({
          serviceId: service as ServiceId,
          enabled: status,
        });
      }
    }

    if (settings.protocols) {
      this.log("Applying protocol statuses ...");
      for (const [protocol, status] of Object.entries(settings.protocols)) {
        await projectService.updateProtocol({
          protocolId: protocol as ProtocolId,
          enabled: status,
        });
      }
    }

    if (settings.auth) {
      if (settings.auth.security) {
        this.log("Applying auth security settings ...");
        await projectService.updateSessionDurationPolicy({
          duration: Number(settings.auth.security.duration),
        });
        await projectService.updateUserLimitPolicy({
          total: Number(settings.auth.security.limit),
        });
        await projectService.updateSessionLimitPolicy({
          total: Number(settings.auth.security.sessionsLimit),
        });
        await projectService.updatePasswordDictionaryPolicy({
          enabled: settings.auth.security.passwordDictionary,
        });
        await projectService.updatePasswordHistoryPolicy({
          total: Number(settings.auth.security.passwordHistory),
        });
        await projectService.updatePasswordPersonalDataPolicy({
          enabled: settings.auth.security.personalDataCheck,
        });
        await projectService.updateSessionAlertPolicy({
          enabled: settings.auth.security.sessionAlerts,
        });
        if (settings.auth.security.mockNumbers !== undefined) {
          const remoteMockNumbers = await projectService.listMockPhones({
            queries: [Query.limit(100)],
          });
          const desiredMockNumbersByPhone = new Map(
            settings.auth.security.mockNumbers.map((mockNumber) => [
              mockNumber.phone,
              mockNumber.otp,
            ]),
          );

          for (const remoteMockNumber of remoteMockNumbers.mockNumbers) {
            const desiredOtp = desiredMockNumbersByPhone.get(
              remoteMockNumber.number,
            );

            if (desiredOtp === undefined) {
              await projectService.deleteMockPhone({
                number: remoteMockNumber.number,
              });
              continue;
            }

            if (remoteMockNumber.otp !== desiredOtp) {
              await projectService.updateMockPhone({
                number: remoteMockNumber.number,
                otp: desiredOtp,
              });
            }

            desiredMockNumbersByPhone.delete(remoteMockNumber.number);
          }

          for (const [phone, otp] of desiredMockNumbersByPhone) {
            await projectService.createMockPhone({
              number: phone,
              otp,
            });
          }
        }
      }

      if (settings.auth.methods) {
        this.log("Applying auth methods statuses ...");
        for (const [method, status] of Object.entries(settings.auth.methods)) {
          await projectService.updateAuthMethod({
            methodId: method as MethodId,
            enabled: status,
          });
        }
      }
    }
  }

  public async pushBuckets(buckets: any[]): Promise<{
    successfullyPushed: number;
    errors: any[];
  }> {
    let successfullyPushed = 0;
    const errors: any[] = [];

    const hasBucketChanges = (remoteBucket: any, localBucket: any): boolean => {
      const scalarFields = [
        "name",
        "fileSecurity",
        "enabled",
        "maximumFileSize",
        "encryption",
        "antivirus",
        "compression",
      ] as const;

      if (
        scalarFields.some((field) => remoteBucket[field] !== localBucket[field])
      ) {
        return true;
      }

      if (
        !arrayEqualsUnordered(
          remoteBucket["$permissions"],
          localBucket["$permissions"],
        )
      ) {
        return true;
      }

      return !arrayEqualsUnordered(
        remoteBucket.allowedFileExtensions,
        localBucket.allowedFileExtensions,
      );
    };

    for (const bucket of buckets) {
      try {
        this.log(`Pushing bucket ${chalk.bold(bucket["name"])} ...`);
        const storageService = await getStorageService(this.projectClient);

        try {
          const remoteBucket = await storageService.getBucket(bucket["$id"]);
          const hasChanges = hasBucketChanges(remoteBucket, bucket);

          if (!hasChanges) {
            this.log(
              `No changes detected for bucket ${chalk.bold(bucket["name"])}. Skipping.`,
            );
            continue;
          }

          await storageService.updateBucket({
            bucketId: bucket["$id"],
            name: bucket.name,
            permissions: bucket["$permissions"],
            fileSecurity: bucket.fileSecurity,
            enabled: bucket.enabled,
            maximumFileSize: bucket.maximumFileSize,
            allowedFileExtensions: bucket.allowedFileExtensions,
            encryption: bucket.encryption,
            antivirus: bucket.antivirus,
            compression: bucket.compression,
          });
          successfullyPushed++;
        } catch (e: unknown) {
          if (e instanceof AppwriteException && Number(e.code) === 404) {
            await storageService.createBucket({
              bucketId: bucket["$id"],
              name: bucket.name,
              permissions: bucket["$permissions"],
              fileSecurity: bucket.fileSecurity,
              enabled: bucket.enabled,
              maximumFileSize: bucket.maximumFileSize,
              allowedFileExtensions: bucket.allowedFileExtensions,
              compression: bucket.compression,
              encryption: bucket.encryption,
              antivirus: bucket.antivirus,
            });
            successfullyPushed++;
          } else {
            throw e;
          }
        }
      } catch (e: any) {
        errors.push(e);
        this.error(`Failed to push bucket ${bucket["name"]}: ${e.message}`);
      }
    }

    return {
      successfullyPushed,
      errors,
    };
  }

  public async pushTeams(teams: any[]): Promise<{
    successfullyPushed: number;
    errors: any[];
  }> {
    let successfullyPushed = 0;
    const errors: any[] = [];

    for (const team of teams) {
      try {
        this.log(`Pushing team ${chalk.bold(team["name"])} ...`);
        const teamsService = await getTeamsService(this.projectClient);

        try {
          await teamsService.get(team["$id"]);
          await teamsService.updateName({
            teamId: team["$id"],
            name: team.name,
          });
        } catch (e: unknown) {
          if (e instanceof AppwriteException && Number(e.code) === 404) {
            await teamsService.create({
              teamId: team["$id"],
              name: team.name,
            });
          } else {
            throw e;
          }
        }

        successfullyPushed++;
      } catch (e: any) {
        errors.push(e);
        this.error(`Failed to push team ${team["name"]}: ${e.message}`);
      }
    }

    return {
      successfullyPushed,
      errors,
    };
  }

  public async pushWebhooks(webhooks: any[]): Promise<{
    successfullyPushed: number;
    errors: any[];
  }> {
    let successfullyPushed = 0;
    const errors: any[] = [];

    for (const webhook of webhooks) {
      try {
        this.log(`Pushing webhook ${chalk.bold(webhook["name"])} ...`);
        const webhooksService = await getWebhooksService(this.projectClient);

        try {
          await webhooksService.get({ webhookId: webhook["$id"] });
          await webhooksService.update({
            webhookId: webhook["$id"],
            name: webhook.name,
            url: webhook.url,
            events: webhook.events,
            enabled: webhook.enabled,
            tls: webhook.tls,
          });
        } catch (e: unknown) {
          if (e instanceof AppwriteException && Number(e.code) === 404) {
            await webhooksService.create({
              webhookId: webhook["$id"],
              url: webhook.url,
              name: webhook.name,
              events: webhook.events,
              enabled: webhook.enabled,
              tls: webhook.tls,
            });
          } else {
            throw e;
          }
        }

        successfullyPushed++;
      } catch (e: any) {
        errors.push(e);
        this.error(`Failed to push webhook ${webhook["name"]}: ${e.message}`);
      }
    }

    return {
      successfullyPushed,
      errors,
    };
  }

  public async pushMessagingTopics(topics: any[]): Promise<{
    successfullyPushed: number;
    errors: any[];
  }> {
    let successfullyPushed = 0;
    const errors: any[] = [];

    for (const topic of topics) {
      try {
        this.log(`Pushing topic ${chalk.bold(topic["name"])} ...`);
        const messagingService = await getMessagingService(this.projectClient);

        try {
          await messagingService.getTopic(topic["$id"]);
          await messagingService.updateTopic({
            topicId: topic["$id"],
            name: topic.name,
            subscribe: topic.subscribe,
          });
        } catch (e: unknown) {
          if (e instanceof AppwriteException && Number(e.code) === 404) {
            await messagingService.createTopic({
              topicId: topic["$id"],
              name: topic.name,
              subscribe: topic.subscribe,
            });
          } else {
            throw e;
          }
        }

        this.success(`Pushed ${topic.name} ( ${topic["$id"]} )`);
        successfullyPushed++;
      } catch (e: any) {
        errors.push(e);
        this.error(`Failed to push topic ${topic["name"]}: ${e.message}`);
      }
    }

    return {
      successfullyPushed,
      errors,
    };
  }

  public async pushFunctions(
    functions: any[],
    options: {
      async?: boolean;
      code?: boolean;
      activate?: boolean;
      withVariables?: boolean;
      logs?: boolean;
    } = {},
  ): Promise<{
    successfullyPushed: number;
    successfullyDeployed: number;
    failedDeployments: FailedDeployment[];
    errors: any[];
  }> {
    const {
      async: asyncDeploy,
      code,
      activate = true,
      withVariables,
      logs = true,
    } = options;

    Spinner.start(false);
    const deploymentLogsController = createDeploymentLogsController(
      logs && !asyncDeploy,
    );
    let successfullyPushed = 0;
    let successfullyDeployed = 0;
    const failedDeployments: FailedDeployment[] = [];
    const errors: any[] = [];
    const deploymentLogs: {
      url: string;
      consoleUrl: string;
    }[] = [];

    try {
      await Promise.all(
        functions.map(async (func: any) => {
          let response: any = {};

          const ignore = func.ignore ? "appwrite.config.json" : ".gitignore";
          let functionExists = false;
          let deploymentCreated = false;

          const updaterRow = new Spinner({
            status: "",
            resource: func.name,
            id: func["$id"],
            end: `Ignoring using: ${ignore}`,
          });

          updaterRow.update({ status: "Getting" }).startSpinner(SPINNER_DOTS);
          const functionsService = await getFunctionsService(
            this.projectClient,
          );
          try {
            response = await functionsService.get({ functionId: func["$id"] });
            functionExists = true;
            if (response.runtime !== func.runtime) {
              updaterRow.fail({
                errorMessage: `Runtime mismatch! (local=${func.runtime},remote=${response.runtime}) Please delete remote function or update your appwrite.config.json`,
              });
              return;
            }

            updaterRow
              .update({ status: "Updating" })
              .replaceSpinner(SPINNER_DOTS);

            response = await functionsService.update({
              functionId: func["$id"],
              name: func.name,
              runtime: func.runtime,
              execute: func.execute,
              events: func.events,
              schedule: func.schedule,
              timeout: func.timeout,
              enabled: func.enabled,
              logging: func.logging,
              entrypoint: func.entrypoint,
              commands: func.commands,
              scopes: func.scopes,
              buildSpecification: func.buildSpecification,
              runtimeSpecification: func.runtimeSpecification,
              deploymentRetention: func.deploymentRetention,
            });
          } catch (e: any) {
            if (Number(e.code) === 404) {
              functionExists = false;
            } else {
              errors.push(e);
              updaterRow.fail({
                errorMessage:
                  e.message ?? "General error occurs please try again",
              });
              return;
            }
          }

          if (!functionExists) {
            updaterRow
              .update({ status: "Creating" })
              .replaceSpinner(SPINNER_DOTS);

            try {
              response = await functionsService.create({
                functionId: func.$id,
                name: func.name,
                runtime: func.runtime,
                execute: func.execute,
                events: func.events,
                schedule: func.schedule,
                timeout: func.timeout,
                enabled: func.enabled,
                logging: func.logging,
                entrypoint: func.entrypoint,
                commands: func.commands,
                scopes: func.scopes,
                buildSpecification: func.buildSpecification,
                runtimeSpecification: func.runtimeSpecification,
                deploymentRetention: func.deploymentRetention,
              });

              let domain = "";
              try {
                const consoleService = await getConsoleService(
                  this.consoleClient,
                );
                const variables = await consoleService.variables();
                domain = ID.unique() + "." + variables["_APP_DOMAIN_FUNCTIONS"];
              } catch (err) {
                this.error("Error fetching console variables.");
                throw err;
              }

              try {
                const proxyService = await getProxyService(this.projectClient);
                await proxyService.createFunctionRule(domain, func.$id);
              } catch (err) {
                this.error("Error creating function rule.");
                throw err;
              }

              updaterRow.update({ status: "Created" });
            } catch (e: any) {
              errors.push(e);
              updaterRow.fail({
                errorMessage:
                  e.message ?? "General error occurs please try again",
              });
              return;
            }
          }

          if (withVariables) {
            updaterRow
              .update({ status: "Updating variables" })
              .replaceSpinner(SPINNER_DOTS);

            const functionsServiceForVars = await getFunctionsService(
              this.projectClient,
            );
            const { variables } = await functionsServiceForVars.listVariables({
              functionId: func["$id"],
            });

            await Promise.all(
              variables.map(async (variable: any) => {
                const functionsServiceDel = await getFunctionsService(
                  this.projectClient,
                );
                await functionsServiceDel.deleteVariable({
                  functionId: func["$id"],
                  variableId: variable["$id"],
                });
              }),
            );

            const envFileLocation = `${func["path"]}/.env`;
            let envVariables: Array<{ key: string; value: string }> = [];
            try {
              if (fs.existsSync(envFileLocation)) {
                const envObject = parseDotenv(
                  fs.readFileSync(envFileLocation, "utf8"),
                );
                envVariables = Object.entries(envObject || {}).map(
                  ([key, value]) => ({ key, value }),
                );
              }
            } catch (_error) {
              envVariables = [];
            }
            await Promise.all(
              envVariables.map(async (variable) => {
                const functionsServiceCreate = await getFunctionsService(
                  this.projectClient,
                );
                await functionsServiceCreate.createVariable({
                  functionId: func["$id"],
                  key: variable.key,
                  value: variable.value,
                  secret: false,
                });
              }),
            );
          }

          if (code === false) {
            successfullyPushed++;
            successfullyDeployed++;
            updaterRow.update({ status: "Pushed" });
            updaterRow.stopSpinner();
            return;
          }

          if (!func.path) {
            errors.push(
              new Error(`Function '${func.name}' has no path configured`),
            );
            updaterRow.fail({
              errorMessage: `No path configured for function`,
            });
            return;
          }

          if (
            !fs.existsSync(func.path) ||
            fs.readdirSync(func.path).length === 0
          ) {
            errors.push(
              new Error(`Deployment not found or empty at path: ${func.path}`),
            );
            updaterRow.fail({
              errorMessage: `path not found or empty: ${path.relative(process.cwd(), path.resolve(func.path))}`,
            });
            return;
          }

          try {
            updaterRow
              .update({ status: "Pushing" })
              .replaceSpinner(SPINNER_DOTS);
            const functionsServiceDeploy = await getFunctionsService(
              this.projectClient,
            );

            const result = await pushDeployment({
              resourcePath: func.path,
              extraIgnoreRules: normalizeIgnoreRules(func.ignore),
              createDeployment: async (codeFile) => {
                return await functionsServiceDeploy.createDeployment({
                  functionId: func["$id"],
                  entrypoint: func.entrypoint,
                  commands: func.commands,
                  code: codeFile,
                  activate,
                });
              },
              pollForStatus: false,
            });

            response = result.deployment;
            updaterRow.update({ status: "Pushed" });

            deploymentCreated = true;
            successfullyPushed++;
          } catch (e: any) {
            errors.push(e);

            switch (e.code) {
              case "ENOENT":
                updaterRow.fail({
                  errorMessage: `Deployment not found at path: ${path.resolve(func.path)}`,
                });
                break;
              default:
                updaterRow.fail({
                  errorMessage:
                    e.message ?? "An unknown error occurred. Please try again.",
                });
            }
          }

          if (deploymentCreated && !asyncDeploy) {
            const deploymentId = response["$id"];
            const endpoint =
              localConfig.getEndpoint() || globalConfig.getEndpoint();
            const projectId = localConfig.getProject().projectId;
            const consoleUrl = getFunctionDeploymentConsoleUrl(
              endpoint,
              projectId,
              func["$id"],
              deploymentId,
            );
            let waitingSince: number | null = null;
            const deploymentTimeoutTracker =
              createDeploymentTimeoutTracker(response);
            const deploymentLogPrinter = createDeploymentLogPrinter({
              label: `function:${func.name}`,
              showPrefix: functions.length > 1,
              isVisible: deploymentLogsController.isVisible,
            });
            deploymentLogsController.registerPrinter(deploymentLogPrinter);
            const deploymentWatcher = logs
              ? await watchDeploymentUpdates({
                  endpoint:
                    localConfig.getEndpoint() || globalConfig.getEndpoint(),
                  event: `functions.${func["$id"]}.deployments.${deploymentId}.update`,
                  onDeploymentUpdate: (deployment) => {
                    deploymentTimeoutTracker.touch(deployment);
                    deploymentLogPrinter.ingest(deployment);
                  },
                  onClose: () => {
                    currentDeploymentEnd =
                      "Log stream disconnected; polling status...";
                    updaterRow.update({
                      end: withDeploymentLogsHint(
                        currentDeploymentEnd,
                        deploymentLogsController,
                      ),
                    });
                  },
                })
              : null;
            let currentDeploymentEnd = deploymentWatcher
              ? "Streaming build logs..."
              : "Checking deployment status...";
            const unsubscribeToggle = deploymentLogsController.onToggle(() => {
              updaterRow.update({
                end: withDeploymentLogsHint(
                  currentDeploymentEnd,
                  deploymentLogsController,
                ),
              });
            });

            try {
              updaterRow.update({
                status: "Deploying",
                end: withDeploymentLogsHint(
                  currentDeploymentEnd,
                  deploymentLogsController,
                ),
              });

              while (true) {
                if (deploymentTimeoutTracker.hasTimedOut()) {
                  deploymentLogPrinter.complete();
                  failedDeployments.push({
                    name: func["name"],
                    $id: func["$id"],
                    deployment: deploymentId,
                    reason: "timeout",
                    consoleUrl,
                  });
                  if (deploymentLogPrinter.hasPrintedLogs()) {
                    Spinner.log("");
                  }
                  updaterRow.fail({
                    errorMessage: getDeploymentTimeoutErrorMessage(),
                  });
                  break;
                }

                const functionsServicePoll = await getFunctionsService(
                  this.projectClient,
                );
                response = await functionsServicePoll.getDeployment({
                  functionId: func["$id"],
                  deploymentId: deploymentId,
                });
                deploymentTimeoutTracker.touch(response);
                deploymentLogPrinter.ingest(response);

                const status = response["status"];
                if (status === "waiting") {
                  waitingSince ??= Date.now();
                } else {
                  waitingSince = null;
                }

                if (status === "ready") {
                  if (activate) {
                    currentDeploymentEnd = "Setting active deployment...";
                    updaterRow.update({
                      status: "Activating",
                      end: withDeploymentLogsHint(
                        currentDeploymentEnd,
                        deploymentLogsController,
                      ),
                    });

                    const functionsServiceActivate = await getFunctionsService(
                      this.projectClient,
                    );
                    await functionsServiceActivate.updateFunctionDeployment({
                      functionId: func["$id"],
                      deploymentId,
                    });
                  }

                  successfullyDeployed++;

                  let url = "";
                  const proxyServiceUrl = await getProxyService(
                    this.projectClient,
                  );
                  const res = await proxyServiceUrl.listRules({
                    queries: [
                      Query.limit(1),
                      Query.equal("deploymentResourceType", "function"),
                      Query.equal("deploymentResourceId", func["$id"]),
                      Query.equal("trigger", "manual"),
                    ],
                  });

                  if (Number(res.total) === 1) {
                    url = `https://${res.rules[0].domain}`;
                  }

                  deploymentLogPrinter.complete();
                  if (deploymentLogPrinter.hasPrintedLogs()) {
                    Spinner.log("");
                  }
                  updaterRow.stopSpinner();
                  updaterRow.update({
                    status: activate ? "Deployed" : "Built",
                    end: "",
                  });

                  deploymentLogs.push({ url, consoleUrl });

                  break;
                } else if (status === "failed") {
                  deploymentLogPrinter.complete();
                  failedDeployments.push({
                    name: func["name"],
                    $id: func["$id"],
                    deployment: response["$id"],
                    reason: "failed",
                    consoleUrl,
                  });
                  if (deploymentLogPrinter.hasPrintedLogs()) {
                    Spinner.log("");
                  }
                  updaterRow.fail({ errorMessage: `Failed to deploy` });

                  break;
                } else {
                  currentDeploymentEnd = getDeploymentProgressText(
                    status,
                    waitingSince,
                  );
                  updaterRow.update({
                    status: "Deploying",
                    end: withDeploymentLogsHint(
                      currentDeploymentEnd,
                      deploymentLogsController,
                    ),
                  });
                }

                await new Promise((resolve) =>
                  setTimeout(resolve, POLL_DEBOUNCE),
                );
              }
            } catch (e: any) {
              errors.push(e);
              deploymentLogPrinter.complete();
              if (deploymentLogPrinter.hasPrintedLogs()) {
                Spinner.log("");
              }
              updaterRow.fail({
                errorMessage:
                  e.message ?? "Unknown error occurred. Please try again",
              });
            } finally {
              unsubscribeToggle();
              await deploymentWatcher?.close();
            }
          }

          updaterRow.stopSpinner();
        }),
      );
    } finally {
      deploymentLogsController.close();
      Spinner.stop();
    }

    if (deploymentLogs.length > 0) {
      process.stdout.write("\n");
      deploymentLogs.forEach((dl, index) => {
        if (index > 0) {
          process.stdout.write("\n");
        }

        if (dl.url) {
          this.log(`Preview link: ${chalk.cyan(dl.url)}`);
        }
        this.log(`Deployment page: ${chalk.cyan(dl.consoleUrl)}`);
      });
      process.stdout.write("\n");
    }

    return {
      successfullyPushed,
      successfullyDeployed,
      failedDeployments,
      errors,
    };
  }

  public async pushSites(
    sites: any[],
    options: {
      async?: boolean;
      code?: boolean;
      activate?: boolean;
      withVariables?: boolean;
      logs?: boolean;
    } = {},
  ): Promise<{
    successfullyPushed: number;
    successfullyDeployed: number;
    failedDeployments: FailedDeployment[];
    errors: any[];
  }> {
    const {
      async: asyncDeploy,
      code,
      activate = true,
      withVariables,
      logs = true,
    } = options;

    Spinner.start(false);
    const deploymentLogsController = createDeploymentLogsController(
      logs && !asyncDeploy,
    );
    let successfullyPushed = 0;
    let successfullyDeployed = 0;
    const failedDeployments: FailedDeployment[] = [];
    const errors: any[] = [];
    const deploymentLogs: SiteDeploymentSummary[] = [];

    try {
      await Promise.all(
        sites.map(async (site: any) => {
          let response: any = {};

          let siteExists = false;
          let deploymentCreated = false;

          const updaterRow = new Spinner({
            status: "",
            resource: site.name,
            id: site["$id"],
            end: "Ignoring using: .gitignore",
          });

          updaterRow.update({ status: "Getting" }).startSpinner(SPINNER_DOTS);

          const sitesService = await getSitesService(this.projectClient);
          try {
            response = await sitesService.get({ siteId: site["$id"] });
            siteExists = true;
            if (response.framework !== site.framework) {
              updaterRow.fail({
                errorMessage: `Framework mismatch! (local=${site.framework},remote=${response.framework}) Please delete remote site or update your appwrite.config.json`,
              });
              return;
            }

            updaterRow
              .update({ status: "Updating" })
              .replaceSpinner(SPINNER_DOTS);

            response = await sitesService.update({
              siteId: site["$id"],
              name: site.name,
              framework: site.framework,
              logging: site.logging,
              timeout: site.timeout,
              installCommand: site.installCommand,
              buildCommand: site.buildCommand,
              outputDirectory: site.outputDirectory,
              buildRuntime: site.buildRuntime,
              adapter: site.adapter,
              startCommand: site.startCommand,
              buildSpecification: site.buildSpecification,
              runtimeSpecification: site.runtimeSpecification,
              deploymentRetention: site.deploymentRetention,
            });
          } catch (e: any) {
            if (Number(e.code) === 404) {
              siteExists = false;
            } else {
              errors.push(e);
              updaterRow.fail({
                errorMessage:
                  e.message ?? "General error occurs please try again",
              });
              return;
            }
          }

          if (!siteExists) {
            updaterRow
              .update({ status: "Creating" })
              .replaceSpinner(SPINNER_DOTS);

            try {
              response = await sitesService.create({
                siteId: site.$id,
                name: site.name,
                framework: site.framework,
                logging: site.logging,
                timeout: site.timeout,
                installCommand: site.installCommand,
                buildCommand: site.buildCommand,
                outputDirectory: site.outputDirectory,
                buildRuntime: site.buildRuntime,
                adapter: site.adapter,
                startCommand: site.startCommand,
                buildSpecification: site.buildSpecification,
                runtimeSpecification: site.runtimeSpecification,
                deploymentRetention: site.deploymentRetention,
              });

              let domain = "";
              try {
                const consoleService = await getConsoleService(
                  this.consoleClient,
                );
                const variables = await consoleService.variables();
                const domains = variables["_APP_DOMAIN_SITES"].split(",");
                domain = ID.unique() + "." + domains[0].trim();
              } catch (err) {
                this.error("Error fetching console variables.");
                throw err;
              }

              try {
                const proxyService = await getProxyService(this.projectClient);
                await proxyService.createSiteRule(domain, site.$id);
              } catch (err) {
                this.error("Error creating site rule.");
                throw err;
              }

              updaterRow.update({ status: "Created" });
            } catch (e: any) {
              errors.push(e);
              updaterRow.fail({
                errorMessage:
                  e.message ?? "General error occurs please try again",
              });
              return;
            }
          }

          if (withVariables) {
            updaterRow
              .update({ status: "Creating variables" })
              .replaceSpinner(SPINNER_DOTS);

            const sitesServiceForVars = await getSitesService(
              this.projectClient,
            );
            const { variables } = await sitesServiceForVars.listVariables({
              siteId: site["$id"],
            });

            await Promise.all(
              variables.map(async (variable: any) => {
                const sitesServiceDel = await getSitesService(
                  this.projectClient,
                );
                await sitesServiceDel.deleteVariable({
                  siteId: site["$id"],
                  variableId: variable["$id"],
                });
              }),
            );

            const envFileLocation = `${site["path"]}/.env`;
            let envVariables: Array<{ key: string; value: string }> = [];
            try {
              if (fs.existsSync(envFileLocation)) {
                const envObject = parseDotenv(
                  fs.readFileSync(envFileLocation, "utf8"),
                );
                envVariables = Object.entries(envObject || {}).map(
                  ([key, value]) => ({ key, value }),
                );
              }
            } catch (_error) {
              envVariables = [];
            }
            await Promise.all(
              envVariables.map(async (variable) => {
                const sitesServiceCreate = await getSitesService(
                  this.projectClient,
                );
                await sitesServiceCreate.createVariable({
                  siteId: site["$id"],
                  key: variable.key,
                  value: variable.value,
                  secret: false,
                });
              }),
            );
          }

          if (code === false) {
            successfullyPushed++;
            successfullyDeployed++;
            updaterRow.update({ status: "Pushed" });
            updaterRow.stopSpinner();
            return;
          }

          if (!site.path) {
            errors.push(
              new Error(`Site '${site.name}' has no path configured`),
            );
            updaterRow.fail({
              errorMessage: `No path configured for site`,
            });
            return;
          }

          if (
            !fs.existsSync(site.path) ||
            fs.readdirSync(site.path).length === 0
          ) {
            errors.push(
              new Error(`Deployment not found or empty at path: ${site.path}`),
            );
            updaterRow.fail({
              errorMessage: `path not found or empty: ${path.relative(process.cwd(), path.resolve(site.path))}`,
            });
            return;
          }

          try {
            updaterRow
              .update({ status: "Pushing" })
              .replaceSpinner(SPINNER_DOTS);
            const sitesServiceDeploy = await getSitesService(
              this.projectClient,
            );

            const result = await pushDeployment({
              resourcePath: site.path,
              createDeployment: async (codeFile) => {
                return await sitesServiceDeploy.createDeployment({
                  siteId: site["$id"],
                  installCommand: site.installCommand,
                  buildCommand: site.buildCommand,
                  outputDirectory: site.outputDirectory,
                  code: codeFile,
                  activate,
                });
              },
              pollForStatus: false,
            });

            response = result.deployment;
            updaterRow.update({ status: "Pushed" });
            deploymentCreated = true;
            successfullyPushed++;
          } catch (e: any) {
            errors.push(e);

            switch (e.code) {
              case "ENOENT":
                updaterRow.fail({
                  errorMessage: `Deployment not found at path: ${path.resolve(site.path)}`,
                });
                break;
              default:
                updaterRow.fail({
                  errorMessage:
                    e.message ?? "An unknown error occurred. Please try again.",
                });
            }
          }

          if (deploymentCreated && !asyncDeploy) {
            const deploymentId = response["$id"];
            const endpoint =
              localConfig.getEndpoint() || globalConfig.getEndpoint();
            const projectId = localConfig.getProject().projectId;
            const consoleUrl = getSiteDeploymentConsoleUrl(
              endpoint,
              projectId,
              site["$id"],
              deploymentId,
            );
            let waitingSince: number | null = null;
            let readyWithoutScreenshotsSince: number | null = null;
            let activationApplied = false;
            const deploymentTimeoutTracker =
              createDeploymentTimeoutTracker(response);
            const deploymentLogPrinter = createDeploymentLogPrinter({
              label: `site:${site.name}`,
              showPrefix: sites.length > 1,
              isVisible: deploymentLogsController.isVisible,
            });
            deploymentLogsController.registerPrinter(deploymentLogPrinter);
            const deploymentWatcher = logs
              ? await watchDeploymentUpdates({
                  endpoint:
                    localConfig.getEndpoint() || globalConfig.getEndpoint(),
                  event: `sites.${site["$id"]}.deployments.${deploymentId}.update`,
                  onDeploymentUpdate: (deployment) => {
                    deploymentTimeoutTracker.touch(deployment);
                    deploymentLogPrinter.ingest(deployment);
                  },
                  onClose: () => {
                    currentDeploymentEnd =
                      "Log stream disconnected; polling status...";
                    updaterRow.update({
                      end: withDeploymentLogsHint(
                        currentDeploymentEnd,
                        deploymentLogsController,
                      ),
                    });
                  },
                })
              : null;
            let currentDeploymentEnd = deploymentWatcher
              ? "Streaming build logs..."
              : "Checking deployment status...";
            const unsubscribeToggle = deploymentLogsController.onToggle(() => {
              updaterRow.update({
                end: withDeploymentLogsHint(
                  currentDeploymentEnd,
                  deploymentLogsController,
                ),
              });
            });

            try {
              updaterRow.update({
                status: "Deploying",
                end: withDeploymentLogsHint(
                  currentDeploymentEnd,
                  deploymentLogsController,
                ),
              });

              while (true) {
                if (deploymentTimeoutTracker.hasTimedOut()) {
                  deploymentLogPrinter.complete();
                  failedDeployments.push({
                    name: site["name"],
                    $id: site["$id"],
                    deployment: deploymentId,
                    reason: "timeout",
                    consoleUrl,
                  });
                  if (deploymentLogPrinter.hasPrintedLogs()) {
                    Spinner.log("");
                  }
                  updaterRow.fail({
                    errorMessage: getDeploymentTimeoutErrorMessage(),
                  });
                  break;
                }

                const sitesServicePoll = await getSitesService(
                  this.projectClient,
                );
                response = await sitesServicePoll.getDeployment({
                  siteId: site["$id"],
                  deploymentId: deploymentId,
                });
                deploymentTimeoutTracker.touch(response);
                deploymentLogPrinter.ingest(response);

                const status = response["status"];
                if (status === "waiting") {
                  waitingSince ??= Date.now();
                } else {
                  waitingSince = null;
                }

                if (status === "ready") {
                  const { screenshotLight, screenshotDark } =
                    getSiteDeploymentScreenshots(response);
                  const screenshotsReady =
                    hasSiteDeploymentScreenshots(response);

                  if (activate && !activationApplied) {
                    currentDeploymentEnd = "Setting active deployment...";
                    updaterRow.update({
                      status: "Activating",
                      end: withDeploymentLogsHint(
                        currentDeploymentEnd,
                        deploymentLogsController,
                      ),
                    });

                    const sitesServiceActivate = await getSitesService(
                      this.projectClient,
                    );
                    await sitesServiceActivate.updateSiteDeployment({
                      siteId: site["$id"],
                      deploymentId,
                    });
                    activationApplied = true;
                  }

                  if (!screenshotsReady) {
                    readyWithoutScreenshotsSince ??= Date.now();

                    if (
                      Date.now() - readyWithoutScreenshotsSince <
                      SITE_SCREENSHOT_FINALIZATION_TIMEOUT_MS
                    ) {
                      currentDeploymentEnd = "Finalizing deployment preview...";
                      updaterRow.update({
                        status: "Finalizing",
                        end: withDeploymentLogsHint(
                          currentDeploymentEnd,
                          deploymentLogsController,
                        ),
                      });

                      await new Promise((resolve) =>
                        setTimeout(resolve, POLL_DEBOUNCE),
                      );
                      continue;
                    }
                  }

                  successfullyDeployed++;

                  let url = "";
                  const proxyServiceUrl = await getProxyService(
                    this.projectClient,
                  );
                  const res = await proxyServiceUrl.listRules({
                    queries: [
                      Query.limit(1),
                      Query.equal("deploymentResourceType", "site"),
                      Query.equal("deploymentResourceId", site["$id"]),
                      Query.equal("trigger", "manual"),
                    ],
                  });

                  if (Number(res.total) === 1) {
                    url = `https://${res.rules[0].domain}`;
                  }

                  deploymentLogPrinter.complete();
                  if (deploymentLogPrinter.hasPrintedLogs()) {
                    Spinner.log("");
                  }
                  updaterRow.stopSpinner();
                  updaterRow.update({
                    status: activate ? "Deployed" : "Built",
                    end: "",
                  });

                  deploymentLogs.push({
                    name: site.name,
                    url,
                    consoleUrl,
                    screenshotLight,
                    screenshotDark,
                    screenshotsPending: !screenshotsReady,
                  });

                  break;
                } else if (status === "failed") {
                  deploymentLogPrinter.complete();
                  failedDeployments.push({
                    name: site["name"],
                    $id: site["$id"],
                    deployment: response["$id"],
                    reason: "failed",
                    consoleUrl,
                  });
                  if (deploymentLogPrinter.hasPrintedLogs()) {
                    Spinner.log("");
                  }
                  updaterRow.fail({ errorMessage: `Failed to deploy` });

                  break;
                } else {
                  currentDeploymentEnd = getDeploymentProgressText(
                    status,
                    waitingSince,
                  );
                  updaterRow.update({
                    status: "Deploying",
                    end: withDeploymentLogsHint(
                      currentDeploymentEnd,
                      deploymentLogsController,
                    ),
                  });
                }

                await new Promise((resolve) =>
                  setTimeout(resolve, POLL_DEBOUNCE),
                );
              }
            } catch (e: any) {
              errors.push(e);
              deploymentLogPrinter.complete();
              if (deploymentLogPrinter.hasPrintedLogs()) {
                Spinner.log("");
              }
              updaterRow.fail({
                errorMessage:
                  e.message ?? "Unknown error occurred. Please try again",
              });
            } finally {
              unsubscribeToggle();
              await deploymentWatcher?.close();
            }
          }

          updaterRow.stopSpinner();
        }),
      );
    } finally {
      deploymentLogsController.close();
      Spinner.stop();
    }

    if (deploymentLogs.length > 0) {
      let sitePreviewRenderer: SiteTerminalPreviewRenderer | null = null;
      let sitePreviewSetupWarning: string | null = null;
      const emittedPreviewWarnings = new Set<string>();

      if (
        shouldRenderSiteTerminalPreview() &&
        deploymentLogs.some(
          (deploymentLog) =>
            deploymentLog.screenshotLight || deploymentLog.screenshotDark,
        )
      ) {
        try {
          const consoleClient = await sdkForConsole(
            true,
            localConfig.getEndpoint() || globalConfig.getEndpoint(),
          );
          sitePreviewRenderer = {
            consoleClient,
            storageService: await getStorageService(consoleClient),
          };
        } catch (previewSetupError) {
          sitePreviewSetupWarning = getErrorMessage(previewSetupError);
        }
      }

      process.stdout.write("\n");
      for (const [index, dl] of deploymentLogs.entries()) {
        if (index > 0) {
          process.stdout.write("\n");
        }

        if (deploymentLogs.length > 1) {
          process.stdout.write(`${chalk.cyan.bold(`Site: ${dl.name}`)}\n`);
        }

        if (dl.screenshotsPending) {
          hint(
            "Deployment is ready, but screenshot generation is still finalizing. Open the deployment page to view it once it is available.",
          );
        }

        if (sitePreviewRenderer && (dl.screenshotLight || dl.screenshotDark)) {
          const preview = await renderSiteTerminalPreview({
            renderer: sitePreviewRenderer,
            screenshotLight: dl.screenshotLight,
            screenshotDark: dl.screenshotDark,
          });

          if (preview.preview) {
            process.stdout.write(
              `\n${chalk.cyan.bold("Screenshot preview")}\n\n`,
            );
            process.stdout.write(`${preview.preview}\n\n`);
          }

          for (const previewWarning of preview.warnings) {
            const warningMessage = `Screenshot preview unavailable: ${previewWarning}`;

            if (emittedPreviewWarnings.has(warningMessage)) {
              continue;
            }

            emittedPreviewWarnings.add(warningMessage);
            hint(warningMessage);
          }
        } else if (
          sitePreviewSetupWarning &&
          (dl.screenshotLight || dl.screenshotDark)
        ) {
          const warningMessage = `Screenshot preview unavailable: ${sitePreviewSetupWarning}`;

          if (!emittedPreviewWarnings.has(warningMessage)) {
            emittedPreviewWarnings.add(warningMessage);
            hint(warningMessage);
          }
        }

        if (dl.url) {
          this.log(`Preview link: ${chalk.cyan(dl.url)}`);
        }
        this.log(`Deployment page: ${chalk.cyan(dl.consoleUrl)}`);
      }
      process.stdout.write("\n");
    }

    return {
      successfullyPushed,
      successfullyDeployed,
      failedDeployments,
      errors,
    };
  }

  public async pushTables(
    tables: any[],
    options: PushTableOptions = {},
  ): Promise<{
    successfullyPushed: number;
    errors: any[];
  }> {
    const { attempts, skipConfirmation = false } = options;
    const pollMaxDebounces = attempts ?? POLL_DEFAULT_VALUE;
    const pools = new Pools(pollMaxDebounces, this.projectClient);
    const attributes = new Attributes(
      pools,
      skipConfirmation,
      this.projectClient,
    );

    const tablesChanged = new Set();
    const errors: any[] = [];

    // Parallel tables actions
    await Promise.all(
      tables.map(async (table: any) => {
        try {
          const tablesService = await getTablesDBService(this.projectClient);
          const remoteTable = await tablesService.getTable({
            databaseId: table["databaseId"],
            tableId: table["$id"],
          });

          const changes: string[] = [];
          if (remoteTable.name !== table.name) changes.push("name");
          if (remoteTable.rowSecurity !== table.rowSecurity)
            changes.push("rowSecurity");
          if (remoteTable.enabled !== table.enabled) changes.push("enabled");
          if (
            JSON.stringify(remoteTable["$permissions"]) !==
            JSON.stringify(table["$permissions"])
          )
            changes.push("permissions");

          if (changes.length > 0) {
            await tablesService.updateTable({
              databaseId: table["databaseId"],
              tableId: table["$id"],
              name: table.name,
              rowSecurity: table.rowSecurity,
              permissions: table["$permissions"],
            });

            this.success(
              `Updated ${table.name} ( ${table["$id"]} ) - ${changes.join(", ")}`,
            );
            tablesChanged.add(table["$id"]);
          }
          table.remoteVersion = remoteTable;

          table.isExisted = true;
        } catch (e: any) {
          if (Number(e.code) === 404) {
            this.log(
              `Table ${table.name} does not exist in the project. Creating ... `,
            );
            const tablesService = await getTablesDBService(this.projectClient);
            await tablesService.createTable({
              databaseId: table["databaseId"],
              tableId: table["$id"],
              name: table.name,
              rowSecurity: table.rowSecurity,
              permissions: table["$permissions"]
                ? [...table["$permissions"]]
                : undefined,
            });

            this.success(`Created ${table.name} ( ${table["$id"]} )`);
            tablesChanged.add(table["$id"]);
          } else {
            errors.push(e);
            throw e;
          }
        }
      }),
    );

    // Serialize attribute actions
    for (const table of tables) {
      let columns = table.columns;
      let indexes = table.indexes;
      let hadChanges = false;

      if (table.isExisted) {
        const columnsResult = await attributes.attributesToCreate(
          table.remoteVersion.columns,
          table.columns,
          table as Collection,
        );
        const indexesResult = await attributes.attributesToCreate(
          table.remoteVersion.indexes,
          table.indexes,
          table as Collection,
          true,
        );

        columns = columnsResult.attributes;
        indexes = indexesResult.attributes;
        hadChanges = columnsResult.hasChanges || indexesResult.hasChanges;

        if (!hadChanges && columns.length <= 0 && indexes.length <= 0) {
          if (!tablesChanged.has(table["$id"])) {
            this.log(
              `No changes detected for table ${chalk.bold(table["name"])}. Skipping.`,
            );
          }
          continue;
        }
      }

      this.log(
        `Pushing table ${table.name} ( ${table["databaseId"]} - ${table["$id"]} ) attributes`,
      );

      try {
        await attributes.createColumns(columns, table as Collection);
      } catch (e) {
        errors.push(e);
        throw e;
      }

      try {
        await attributes.createIndexes(indexes, table as Collection);
      } catch (e) {
        errors.push(e);
        throw e;
      }
      tablesChanged.add(table["$id"]);
      this.success(`Successfully pushed ${table.name} ( ${table["$id"]} )`);
    }

    return {
      successfullyPushed: tablesChanged.size,
      errors,
    };
  }

  public async pushCollections(
    collections: PushCollectionInput[],
    options: { skipConfirmation?: boolean } = {},
  ): Promise<{
    successfullyPushed: number;
    errors: Error[];
  }> {
    const { skipConfirmation = false } = options;
    const pools = new Pools(POLL_DEFAULT_VALUE, this.projectClient);
    const attributesHelper = new Attributes(
      pools,
      skipConfirmation,
      this.projectClient,
    );

    const errors: Error[] = [];

    // Cast to state type since we'll be adding state properties
    const collectionsWithState = collections as PushCollectionState[];

    const databases = Array.from(
      new Set(collectionsWithState.map((collection) => collection.databaseId)),
    );

    // Parallel db actions
    await Promise.all(
      databases.map(async (databaseId) => {
        const databasesService = await getDatabasesService(this.projectClient);
        try {
          const database = await databasesService.get(databaseId);

          // Note: We can't get the local database name here since we don't have access to localConfig
          // This will need to be handled by the caller if needed
          const localDatabaseName =
            collectionsWithState.find((c) => c.databaseId === databaseId)
              ?.databaseName ?? databaseId;

          if (database.name !== localDatabaseName) {
            await databasesService.update(databaseId, localDatabaseName);

            this.success(`Updated ${localDatabaseName} ( ${databaseId} ) name`);
          }
        } catch (err: unknown) {
          if (err instanceof AppwriteException && Number(err.code) === 404) {
            this.log(`Database ${databaseId} not found. Creating it now ...`);

            const localDatabaseName =
              collectionsWithState.find((c) => c.databaseId === databaseId)
                ?.databaseName ?? databaseId;

            await databasesService.create(databaseId, localDatabaseName);
          } else {
            throw err;
          }
        }
      }),
    );

    // Parallel collection actions
    await Promise.all(
      collectionsWithState.map(async (collection) => {
        try {
          const databasesService = await getDatabasesService(
            this.projectClient,
          );
          const remoteCollection = await databasesService.getCollection(
            collection.databaseId,
            collection.$id,
          );

          if (remoteCollection.name !== collection.name) {
            await databasesService.updateCollection({
              databaseId: collection.databaseId,
              collectionId: collection.$id,
              name: collection.name,
            });

            this.success(
              `Updated ${collection.name} ( ${collection.$id} ) name`,
            );
          }
          collection.remoteVersion = remoteCollection;

          collection.isExisted = true;
        } catch (e: unknown) {
          if (e instanceof AppwriteException && Number(e.code) === 404) {
            this.log(
              `Collection ${collection.name} does not exist in the project. Creating ... `,
            );
            const databasesService = await getDatabasesService(
              this.projectClient,
            );
            await databasesService.createCollection({
              databaseId: collection.databaseId,
              collectionId: collection.$id,
              name: collection.name,
              documentSecurity: collection.documentSecurity,
              permissions: collection.$permissions,
              attributes: collection.attributes,
              indexes: collection.indexes,
            });
            collection.isNewlyCreated = true;
          } else {
            if (e instanceof Error) {
              errors.push(e);
            }
            throw e;
          }
        }
      }),
    );

    let numberOfCollections = 0;
    // Serialize attribute actions
    for (const collection of collectionsWithState) {
      // Skip newly created collections - their attributes and indexes were already created
      if (collection.isNewlyCreated) {
        numberOfCollections++;
        this.success(
          `Successfully pushed ${collection.name} ( ${collection.$id} )`,
        );
        continue;
      }

      if (!collection.isExisted) {
        continue;
      }

      const collectionAttributesResult =
        await attributesHelper.attributesToCreate(
          collection.remoteVersion!.attributes,
          collection.attributes ?? [],
          collection as Collection,
        );
      const indexesResult = await attributesHelper.attributesToCreate(
        collection.remoteVersion!.indexes,
        collection.indexes ?? [],
        collection as Collection,
        true,
      );

      const collectionAttributes = collectionAttributesResult.attributes;
      const indexes = indexesResult.attributes;

      if (
        collectionAttributes.length <= 0 &&
        indexes.length <= 0 &&
        !collectionAttributesResult.hasChanges &&
        !indexesResult.hasChanges
      ) {
        continue;
      }

      this.log(
        `Pushing collection ${collection.name} ( ${collection.databaseId} - ${collection.$id} ) attributes`,
      );

      try {
        await attributesHelper.createAttributes(
          collectionAttributes,
          collection as Collection,
        );
      } catch (e) {
        if (e instanceof Error) {
          errors.push(e);
        }
        throw e;
      }

      try {
        await attributesHelper.createIndexes(indexes, collection as Collection);
      } catch (e) {
        if (e instanceof Error) {
          errors.push(e);
        }
        throw e;
      }
      numberOfCollections++;
      this.success(
        `Successfully pushed ${collection.name} ( ${collection.$id} )`,
      );
    }

    return {
      successfullyPushed: numberOfCollections,
      errors,
    };
  }
}

async function createPushInstance(
  options: { silent?: boolean; requiresConsoleAuth?: boolean } = {
    silent: false,
    requiresConsoleAuth: false,
  },
): Promise<Push> {
  const { silent, requiresConsoleAuth } = options;
  const projectClient = await sdkForProject();
  const consoleClient = await sdkForConsole(requiresConsoleAuth);

  return new Push(projectClient, consoleClient, silent);
}

function withResolvedResourcePaths<T extends { path?: string }>(
  resource: "functions" | "sites",
  resources: T[],
): T[] {
  return resources.map((item) => ({
    ...item,
    path: item.path
      ? localConfig.resolveResourcePath(resource, item.path)
      : item.path,
  }));
}

const pushResources = async ({
  skipDeprecated = false,
  functionOptions,
  siteOptions,
}: PushOptions = {}): Promise<void> => {
  if (cliConfig.all) {
    checkDeployConditions(localConfig);

    const functions = localConfig.getFunctions();
    let allowFunctionsCodePush: boolean | null =
      cliConfig.force === true ? true : null;
    let activateFunctionsDeployment: boolean | undefined =
      cliConfig.force === true ? true : undefined;
    if (functions.length > 0 && allowFunctionsCodePush === null) {
      const codeAnswer = await inquirer.prompt(questionsPushFunctionsCode);
      allowFunctionsCodePush = codeAnswer.override;
    }
    if (
      functions.length > 0 &&
      allowFunctionsCodePush === true &&
      activateFunctionsDeployment === undefined
    ) {
      const activateAnswer = await inquirer.prompt(
        questionsPushFunctionsActivate,
      );
      activateFunctionsDeployment = activateAnswer.activate;
    }

    const sites = localConfig.getSites();
    let allowSitesCodePush: boolean | null =
      cliConfig.force === true ? true : null;
    let activateSitesDeployment: boolean | undefined =
      cliConfig.force === true ? true : undefined;
    if (sites.length > 0 && allowSitesCodePush === null) {
      const codeAnswer = await inquirer.prompt(questionsPushSitesCode);
      allowSitesCodePush = codeAnswer.override;
    }
    if (
      sites.length > 0 &&
      allowSitesCodePush === true &&
      activateSitesDeployment === undefined
    ) {
      const activateAnswer = await inquirer.prompt(questionsPushSitesActivate);
      activateSitesDeployment = activateAnswer.activate;
    }

    const pushInstance = await createPushInstance({
      requiresConsoleAuth: true,
    });
    const project = localConfig.getProject();
    const config: ConfigType = {
      projectId: project.projectId ?? "",
      projectName: project.projectName,
      settings: project.projectSettings,
      functions: withResolvedResourcePaths("functions", functions),
      sites: withResolvedResourcePaths("sites", sites),
      collections: localConfig.getCollections(),
      databases: localConfig.getDatabases(),
      tables: localConfig.getTables(),
      tablesDB: localConfig.getTablesDBs(),
      buckets: localConfig.getBuckets(),
      teams: localConfig.getTeams(),
      webhooks: localConfig.getWebhooks(),
      topics: localConfig.getMessagingTopics(),
    };

    parseWithBetterErrors<ConfigType>(
      ConfigSchema,
      config,
      "Configuration validation failed",
      config,
    );

    await pushInstance.pushResources(config, {
      all: cliConfig.all,
      skipDeprecated,
      functionOptions: {
        code: allowFunctionsCodePush === true,
        activate: activateFunctionsDeployment ?? true,
        withVariables: false,
        logs: functionOptions?.logs,
      },
      siteOptions: {
        code: allowSitesCodePush === true,
        activate: activateSitesDeployment ?? true,
        withVariables: false,
        logs: siteOptions?.logs,
      },
    });
  } else {
    const actions: Record<string, (options?: any) => Promise<void>> = {
      settings: pushSettings,
      functions: pushFunction,
      sites: pushSite,
      collections: pushCollection,
      tables: pushTable,
      buckets: pushBucket,
      teams: pushTeam,
      messages: pushMessagingTopic,
    };

    if (skipDeprecated) {
      delete actions.collections;
    }

    const answers = await inquirer.prompt(questionsPushResources);

    const action = actions[answers.resource];
    if (action !== undefined) {
      await action();
    }
  }
};

const pushSettings = async (): Promise<void> => {
  checkDeployConditions(localConfig);

  try {
    const projectsService = await getProjectsService();
    const response = await projectsService.get(
      localConfig.getProject().projectId,
    );

    const remoteSettings = createSettingsObject(response);
    const localSettings = localConfig.getProject().projectSettings ?? {};

    log("Checking for changes ...");
    const changes: any[] = [];

    changes.push(
      ...getObjectChanges(remoteSettings, localSettings, "services", "Service"),
    );
    changes.push(
      ...getObjectChanges(
        remoteSettings["auth"] ?? {},
        localSettings["auth"] ?? {},
        "methods",
        "Auth method",
      ),
    );
    changes.push(
      ...getObjectChanges(
        remoteSettings["auth"] ?? {},
        localSettings["auth"] ?? {},
        "security",
        "Auth security",
      ),
    );

    if (changes.length > 0) {
      drawTable(changes);
      if ((await getConfirmation()) !== true) {
        success(`Successfully pushed 0 project settings.`);
        return;
      }
    }
  } catch (_e) {}

  try {
    log("Pushing project settings ...");

    const pushInstance = await createPushInstance({
      requiresConsoleAuth: true,
    });
    const config = localConfig.getProject();

    await pushInstance.pushSettings({
      projectId: config.projectId,
      projectName: config.projectName,
      settings: config.projectSettings,
    });

    success(`Successfully pushed ${chalk.bold("all")} project settings.`);
  } catch (e) {
    throw e;
  }
};

const pushSite = async ({
  siteId,
  async: asyncDeploy,
  code,
  activate,
  withVariables,
  logs,
}: PushSiteOptions = {}): Promise<void> => {
  process.chdir(localConfig.configDirectoryPath);

  const siteIds: string[] = [];

  if (siteId) {
    siteIds.push(siteId);
  } else if (cliConfig.all) {
    checkDeployConditions(localConfig);
    const sites = localConfig.getSites();
    siteIds.push(
      ...sites.map((site: any) => {
        return site.$id;
      }),
    );
  }

  if (siteIds.length <= 0) {
    const answers = await inquirer.prompt(questionsPushSites);
    if (answers.sites) {
      siteIds.push(...answers.sites);
    }
  }

  if (siteIds.length === 0) {
    log("No sites found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull sites' to synchronize existing one, or use '${EXECUTABLE_NAME} init site' to create a new one.`,
    );
    return;
  }

  const sites = siteIds.map((id: string) => {
    const sites = localConfig.getSites();
    const site = sites.find((s: any) => s.$id === id);

    if (!site) {
      throw new Error("Site '" + id + "' not found.");
    }

    return site;
  });

  log("Validating sites ...");
  // Validation is done BEFORE pushing so the deployment process can be run in async with progress update
  for (const site of sites) {
    if (!site.buildCommand && siteRequiresBuildCommand(site)) {
      log(`Site ${site.name} is missing build command.`);
      const answers = await inquirer.prompt(questionsGetBuildCommand);
      site.buildCommand = answers.buildCommand;
      localConfig.addSite(site);
    }
  }

  if (
    !(await approveChanges(
      sites,
      async (args: any) => {
        const sitesService = await getSitesService();
        return await sitesService.get({ siteId: args.siteId });
      },
      KeysSite,
      "siteId",
      "sites",
      ["vars"],
    ))
  ) {
    return;
  }

  let allowCodePush: boolean | null = cliConfig.force === true ? true : null;
  if (code !== false && allowCodePush === null) {
    const codeAnswer = await inquirer.prompt(questionsPushSitesCode);
    allowCodePush = codeAnswer.override;
  }

  const shouldPushCode = code !== false && allowCodePush === true;
  let shouldActivate = activate;

  if (shouldPushCode && shouldActivate === undefined) {
    const activateAnswer = await inquirer.prompt(questionsPushSitesActivate);
    shouldActivate = activateAnswer.activate;
  }

  log("Pushing sites ...");

  const pushStartTime = Date.now();
  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushSites(
    withResolvedResourcePaths("sites", sites),
    {
      async: asyncDeploy,
      code: shouldPushCode,
      activate: shouldActivate ?? true,
      withVariables,
      logs,
    },
  );

  const {
    successfullyPushed,
    successfullyDeployed,
    failedDeployments,
    errors,
  } = result;
  const totalElapsed = ((Date.now() - pushStartTime) / 1000).toFixed(1);

  failedDeployments.forEach((failed) => {
    const { name } = failed;
    const failUrl =
      failed.consoleUrl ??
      getSiteDeploymentConsoleUrl(
        localConfig.getEndpoint() || globalConfig.getEndpoint(),
        localConfig.getProject().projectId,
        failed.$id,
        failed.deployment,
      );

    if (failed.reason === "timeout") {
      error(
        `Deployment of ${name} got stuck for more than ${DEPLOYMENT_TIMEOUT_MINUTES} minutes. Check deployment here: ${failUrl}\n`,
      );
      return;
    }

    error(
      `Deployment of ${name} has failed. Check deployment here: ${failUrl}\n`,
    );
  });

  if (!asyncDeploy) {
    if (successfullyPushed === 0) {
      error("No sites were pushed.");
    } else if (successfullyDeployed !== successfullyPushed) {
      warn(
        `Successfully deployed ${successfullyDeployed} of ${successfullyPushed} sites in ${chalk.bold(totalElapsed + "s")}.`,
      );
    } else {
      success(
        `Successfully deployed ${successfullyPushed} ${successfullyPushed === 1 ? "site" : "sites"} in ${chalk.bold(totalElapsed + "s")}.`,
      );
    }
  } else {
    success(`Successfully pushed ${successfullyPushed} sites.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => {
      console.error(e);
    });
  }
};

const pushFunction = async ({
  functionId,
  async: asyncDeploy,
  code,
  activate,
  withVariables,
  logs,
}: PushFunctionOptions = {}): Promise<void> => {
  process.chdir(localConfig.configDirectoryPath);

  const functionIds: string[] = [];

  if (functionId) {
    functionIds.push(functionId);
  } else if (cliConfig.all) {
    checkDeployConditions(localConfig);
    const functions = localConfig.getFunctions();
    functionIds.push(
      ...functions.map((func: any) => {
        return func.$id;
      }),
    );
  }

  if (functionIds.length <= 0) {
    const answers = await inquirer.prompt(questionsPushFunctions);
    if (answers.functions) {
      functionIds.push(...answers.functions);
    }
  }

  if (functionIds.length === 0) {
    log("No functions found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull functions' to synchronize existing one, or use '${EXECUTABLE_NAME} init function' to create a new one.`,
    );
    return;
  }

  const functions = functionIds.map((id: string) => {
    const functions = localConfig.getFunctions();
    const func = functions.find((f: any) => f.$id === id);

    if (!func) {
      throw new Error("Function '" + id + "' not found.");
    }

    return func;
  });

  log("Validating functions ...");
  for (const func of functions) {
    if (!func.entrypoint) {
      log(`Function ${func.name} is missing an entrypoint.`);
      const answers = await inquirer.prompt(questionsGetEntrypoint);
      func.entrypoint = answers.entrypoint;
      localConfig.addFunction(func);
    }
  }

  if (
    !(await approveChanges(
      functions,
      async (args: any) => {
        const functionsService = await getFunctionsService();
        return await functionsService.get({ functionId: args.functionId });
      },
      KeysFunction,
      "functionId",
      "functions",
      ["vars"],
    ))
  ) {
    return;
  }

  let allowCodePush: boolean | null = cliConfig.force === true ? true : null;
  if (code !== false && allowCodePush === null) {
    const codeAnswer = await inquirer.prompt(questionsPushFunctionsCode);
    allowCodePush = codeAnswer.override;
  }

  const shouldPushCode = code !== false && allowCodePush === true;
  let shouldActivate = activate;

  if (shouldPushCode && shouldActivate === undefined) {
    const activateAnswer = await inquirer.prompt(
      questionsPushFunctionsActivate,
    );
    shouldActivate = activateAnswer.activate;
  }

  log("Pushing functions ...");

  const pushStartTime = Date.now();
  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushFunctions(
    withResolvedResourcePaths("functions", functions),
    {
      async: asyncDeploy,
      code: shouldPushCode,
      activate: shouldActivate ?? true,
      withVariables,
      logs,
    },
  );

  const {
    successfullyPushed,
    successfullyDeployed,
    failedDeployments,
    errors,
  } = result;
  const totalElapsed = ((Date.now() - pushStartTime) / 1000).toFixed(1);

  failedDeployments.forEach((failed) => {
    const { name } = failed;
    const failUrl =
      failed.consoleUrl ??
      getFunctionDeploymentConsoleUrl(
        localConfig.getEndpoint() || globalConfig.getEndpoint(),
        localConfig.getProject().projectId,
        failed.$id,
        failed.deployment,
      );

    if (failed.reason === "timeout") {
      error(
        `Deployment of ${name} got stuck for more than ${DEPLOYMENT_TIMEOUT_MINUTES} minutes. Check deployment here: ${failUrl}\n`,
      );
      return;
    }

    error(
      `Deployment of ${name} has failed. Check deployment here: ${failUrl}\n`,
    );
  });

  if (!asyncDeploy) {
    if (successfullyPushed === 0) {
      error("No functions were pushed.");
    } else if (successfullyDeployed !== successfullyPushed) {
      warn(
        `Successfully deployed ${successfullyDeployed} of ${successfullyPushed} functions in ${chalk.bold(totalElapsed + "s")}.`,
      );
    } else {
      success(
        `Successfully deployed ${successfullyPushed} ${successfullyPushed === 1 ? "function" : "functions"} in ${chalk.bold(totalElapsed + "s")}.`,
      );
    }
  } else {
    success(`Successfully pushed ${successfullyPushed} functions.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => {
      console.error(e);
    });
  }
};

const pushTable = async ({
  attempts,
}: PushTableOptions = {}): Promise<void> => {
  const tables: any[] = [];

  const { resyncNeeded } = await checkAndApplyTablesDBChanges();
  if (resyncNeeded) {
    log("Resyncing configuration due to tables deletions ...");

    const remoteTablesDBs = (
      await paginate(
        async (args: any) => {
          const tablesService = await getTablesDBService();
          return await tablesService.list(args.queries || []);
        },
        {},
        100,
        "databases",
      )
    ).databases;
    const localTablesDBs = localConfig.getTablesDBs();

    const remoteDatabaseIds = new Set(remoteTablesDBs.map((db: any) => db.$id));
    const localTables = localConfig.getTables();
    const validTables = localTables.filter((table: any) =>
      remoteDatabaseIds.has(table.databaseId),
    );

    localConfig.set("tables", validTables);

    const validTablesDBs = localTablesDBs.filter((db: any) =>
      remoteDatabaseIds.has(db.$id),
    );
    localConfig.set("tablesDB", validTablesDBs);

    success("Configuration resynced successfully.");
    console.log();
  }

  log("Checking for deleted tables ...");
  const localTablesDBs = localConfig.getTablesDBs();
  const localTables = localConfig.getTables();
  const tablesToDelete: any[] = [];

  for (const db of localTablesDBs) {
    try {
      const { tables: remoteTables } = await paginate(
        async (args: any) => {
          const tablesService = await getTablesDBService();
          return await tablesService.listTables(
            args.databaseId,
            args.queries || [],
          );
        },
        {
          databaseId: db.$id,
        },
        100,
        "tables",
      );

      for (const remoteTable of remoteTables) {
        const localTable = localTables.find(
          (t: any) => t.$id === remoteTable.$id && t.databaseId === db.$id,
        );
        if (!localTable) {
          tablesToDelete.push({
            ...remoteTable,
            databaseId: db.$id,
            databaseName: db.name,
          });
        }
      }
    } catch (_e) {
      // Skip if database doesn't exist or other errors
    }
  }

  if (tablesToDelete.length > 0) {
    log("Found tables that exist remotely but not locally:");
    const deletionChanges = tablesToDelete.map((table: any) => ({
      id: table.$id,
      action: chalk.red("deleting"),
      key: "Table",
      database: table.databaseName,
      remote: table.name,
      local: "(deleted locally)",
    }));
    drawTable(deletionChanges);

    if ((await getConfirmation()) === true) {
      for (const table of tablesToDelete) {
        try {
          log(
            `Deleting table ${table.name} ( ${table.$id} ) from database ${table.databaseName} ...`,
          );
          const tablesService = await getTablesDBService();
          await tablesService.deleteTable(table.databaseId, table.$id);
          success(`Deleted ${table.name} ( ${table.$id} )`);
        } catch (e: any) {
          error(
            `Failed to delete table ${table.name} ( ${table.$id} ): ${e.message}`,
          );
        }
      }
    }
  }

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    tables.push(...localConfig.getTables());
  } else {
    const answers = await inquirer.prompt(questionsPushTables);
    if (answers.tables) {
      const configTables = new Map();
      localConfig.getTables().forEach((c: any) => {
        configTables.set(`${c["databaseId"]}|${c["$id"]}`, c);
      });
      answers.tables.forEach((a: any) => {
        const table = configTables.get(a);
        tables.push(table);
      });
    }
  }

  if (tables.length === 0) {
    log("No tables found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull tables' to synchronize existing one, or use '${EXECUTABLE_NAME} init table' to create a new one.`,
    );
    return;
  }

  if (
    !(await approveChanges(
      tables,
      async (args: any) => {
        const tablesService = await getTablesDBService();
        return await tablesService.getTable(args.databaseId, args.tableId);
      },
      KeysTable,
      "tableId",
      "tables",
      ["columns", "indexes"],
      "databaseId",
      "databaseId",
    ))
  ) {
    return;
  }

  log("Pushing tables ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushTables(tables, { attempts });

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    warn("No tables were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} tables.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

const pushCollection = async (): Promise<void> => {
  warn(
    `${EXECUTABLE_NAME} push collection has been deprecated. Please consider using '${EXECUTABLE_NAME} push tables' instead`,
  );
  const collections: any[] = [];

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    collections.push(...localConfig.getCollections());
  } else {
    const answers = await inquirer.prompt(questionsPushCollections);
    if (answers.collections) {
      const configCollections = new Map();
      localConfig.getCollections().forEach((c: any) => {
        configCollections.set(`${c["databaseId"]}|${c["$id"]}`, c);
      });
      answers.collections.forEach((a: any) => {
        const collection = configCollections.get(a);
        collections.push(collection);
      });
    }
  }

  if (collections.length === 0) {
    log("No collections found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull collections' to synchronize existing one, or use '${EXECUTABLE_NAME} init collection' to create a new one.`,
    );
    return;
  }

  // Add database names to collections for the class method
  collections.forEach((collection: any) => {
    const localDatabase = localConfig.getDatabase(collection.databaseId);
    collection.databaseName = localDatabase.name ?? collection.databaseId;
  });
  const projectClient = await sdkForProject();

  if (
    !(await approveChanges(
      collections,
      async (args: any) => {
        const databasesService = await getDatabasesService(projectClient);
        return await databasesService.getCollection(
          args.databaseId,
          args.collectionId,
        );
      },
      KeysCollection,
      "collectionId",
      "collections",
      ["attributes", "indexes"],
      "databaseId",
      "databaseId",
    ))
  ) {
    return;
  }

  log("Pushing collections ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushCollections(collections);

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    warn("No collections were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} collections.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

const pushBucket = async (): Promise<void> => {
  const bucketIds: string[] = [];
  const configBuckets = localConfig.getBuckets();

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    bucketIds.push(...configBuckets.map((b: any) => b.$id));
  }

  if (bucketIds.length === 0) {
    const answers = await inquirer.prompt(questionsPushBuckets);
    if (answers.buckets) {
      bucketIds.push(...answers.buckets);
    }
  }

  if (bucketIds.length === 0) {
    log("No buckets found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull buckets' to synchronize existing one, or use '${EXECUTABLE_NAME} init bucket' to create a new one.`,
    );
    return;
  }

  const buckets: any[] = [];

  for (const bucketId of bucketIds) {
    const idBuckets = configBuckets.filter((b: any) => b.$id === bucketId);
    buckets.push(...idBuckets);
  }

  if (
    !(await approveChanges(
      buckets,
      async (args: any) => {
        const storageService = await getStorageService();
        return await storageService.getBucket(args.bucketId);
      },
      KeysStorage,
      "bucketId",
      "buckets",
    ))
  ) {
    return;
  }

  log("Pushing buckets ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushBuckets(buckets);

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    error("No buckets were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} buckets.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

const pushTeam = async (): Promise<void> => {
  const teamIds: string[] = [];
  const configTeams = localConfig.getTeams();

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    teamIds.push(...configTeams.map((t: any) => t.$id));
  }

  if (teamIds.length === 0) {
    const answers = await inquirer.prompt(questionsPushTeams);
    if (answers.teams) {
      teamIds.push(...answers.teams);
    }
  }

  if (teamIds.length === 0) {
    log("No teams found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull teams' to synchronize existing one, or use '${EXECUTABLE_NAME} init team' to create a new one.`,
    );
    return;
  }

  const teams: any[] = [];

  for (const teamId of teamIds) {
    const idTeams = configTeams.filter((t: any) => t.$id === teamId);
    teams.push(...idTeams);
  }

  if (
    !(await approveChanges(
      teams,
      async (args: any) => {
        const teamsService = await getTeamsService();
        return await teamsService.get(args.teamId);
      },
      KeysTeams,
      "teamId",
      "teams",
    ))
  ) {
    return;
  }

  log("Pushing teams ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushTeams(teams);

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    error("No teams were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} teams.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

const pushWebhook = async (): Promise<void> => {
  const webhookIds: string[] = [];
  const configWebhooks = localConfig.getWebhooks();

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    webhookIds.push(...configWebhooks.map((w: any) => w.$id));
  }

  if (webhookIds.length === 0) {
    const answers = await inquirer.prompt(questionsPushWebhooks);
    if (answers.webhooks) {
      webhookIds.push(...answers.webhooks);
    }
  }

  if (webhookIds.length === 0) {
    log("No webhooks found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull webhooks' to synchronize existing ones.`,
    );
    return;
  }

  const webhooks: any[] = [];

  for (const webhookId of webhookIds) {
    const idWebhooks = configWebhooks.filter((w: any) => w.$id === webhookId);
    webhooks.push(...idWebhooks);
  }

  if (
    !(await approveChanges(
      webhooks,
      async (args: any) => {
        const webhooksService = await getWebhooksService();
        return await webhooksService.get({ webhookId: args.webhookId });
      },
      KeysWebhooks,
      "webhookId",
      "webhooks",
    ))
  ) {
    return;
  }

  log("Pushing webhooks ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushWebhooks(webhooks);

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    error("No webhooks were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} webhooks.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

const pushMessagingTopic = async (): Promise<void> => {
  const topicsIds: string[] = [];
  const configTopics = localConfig.getMessagingTopics();

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    topicsIds.push(...configTopics.map((b: any) => b.$id));
  }

  if (topicsIds.length === 0) {
    const answers = await inquirer.prompt(questionsPushMessagingTopics);
    if (answers.topics) {
      topicsIds.push(...answers.topics);
    }
  }

  if (topicsIds.length === 0) {
    log("No topics found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull topics' to synchronize existing one, or use '${EXECUTABLE_NAME} init topic' to create a new one.`,
    );
    return;
  }

  const topics: any[] = [];

  for (const topicId of topicsIds) {
    const idTopic = configTopics.filter((b: any) => b.$id === topicId);
    topics.push(...idTopic);
  }

  if (
    !(await approveChanges(
      topics,
      async (args: any) => {
        const messagingService = await getMessagingService();
        return await messagingService.getTopic(args.topicId);
      },
      KeysTopics,
      "topicId",
      "topics",
    ))
  ) {
    return;
  }

  log("Pushing topics ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushMessagingTopics(topics);

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    error("No topics were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} topics.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

export const push = new Command("push")
  .description(commandDescriptions["push"])
  .action(actionRunner(() => pushResources({ skipDeprecated: true })));

push
  .command("all")
  .description("Push all resource.")
  .option("--no-logs", "Don't stream deployment build logs")
  .action(
    actionRunner((options: { logs?: boolean }) => {
      cliConfig.all = true;
      return pushResources({
        skipDeprecated: true,
        functionOptions: { logs: options.logs },
        siteOptions: { logs: options.logs },
      });
    }),
  );

push
  .command("settings")
  .description("Push project name, services and auth settings")
  .action(actionRunner(pushSettings));

push
  .command("function")
  .alias("functions")
  .description("Push functions in the current directory.")
  .option(`-f, --function-id <function-id>`, `ID of function to run`)
  .option(`-A, --async`, `Don't wait for functions deployments status`)
  .option("--no-code", "Don't push the function's code")
  .option("--no-logs", "Don't stream deployment build logs")
  .option(
    "--activate [value]",
    "Activate the function's deployment after it is ready.",
    (value: string | undefined) =>
      value === undefined ? true : parseBool(value),
  )
  .option("--with-variables", `Push function variables.`)
  .action(actionRunner(pushFunction));

push
  .command("site")
  .alias("sites")
  .description("Push sites in the current directory.")
  .option(`-f, --site-id <site-id>`, `ID of site to run`)
  .option(`-A, --async`, `Don't wait for sites deployments status`)
  .option("--no-code", "Don't push the site's code")
  .option("--no-logs", "Don't stream deployment build logs")
  .option(
    "--activate [value]",
    "Activate the site's deployment after it is ready.",
    (value: string | undefined) =>
      value === undefined ? true : parseBool(value),
  )
  .option("--with-variables", `Push site variables.`)
  .action(actionRunner(pushSite));

push
  .command("collection")
  .alias("collections")
  .description(
    "Push collections in the current project. (deprecated, please use 'push tables' instead)",
  )
  .option(
    `-a, --attempts <numberOfAttempts>`,
    `Max number of attempts before timing out. default: 30.`,
  )
  .action(actionRunner(pushCollection));

push
  .command("table")
  .alias("tables")
  .description("Push tables in the current project.")
  .option(
    `-a, --attempts <numberOfAttempts>`,
    `Max number of attempts before timing out. default: 30.`,
  )
  .action(actionRunner(pushTable));

push
  .command("bucket")
  .alias("buckets")
  .description("Push buckets in the current project.")
  .action(actionRunner(pushBucket));

push
  .command("team")
  .alias("teams")
  .description("Push teams in the current project.")
  .action(actionRunner(pushTeam));

push
  .command("webhook")
  .alias("webhooks")
  .description("Push webhooks in the current project.")
  .action(actionRunner(pushWebhook));

push
  .command("topic")
  .alias("topics")
  .description("Push messaging topics in the current project.")
  .action(actionRunner(pushMessagingTopic));

export const deploy = new Command("deploy")
  .description(`Removed. Use ${EXECUTABLE_NAME} push instead`)
  .action(
    actionRunner(async () => {
      warn(
        `${EXECUTABLE_NAME} deploy has been removed. Please use '${EXECUTABLE_NAME} push' instead`,
      );
    }),
  );
