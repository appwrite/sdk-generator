import fs from "fs";
import os from "os";
import path from "path";
import { create, extract } from "tar";
import ignoreModule from "ignore";
import chalk from "chalk";
import { Agent, WebSocket } from "undici";
import { Client, AppwriteException } from "@appwrite.io/console";
import { error } from "../../parser.js";
import { globalConfig } from "../../config.js";
import { Spinner } from "../../spinner.js";

const ignore: typeof ignoreModule =
  (ignoreModule as unknown as { default?: typeof ignoreModule }).default ??
  ignoreModule;
const POLL_DEBOUNCE = 2000; // Milliseconds

interface IgnoreMatcher {
  baseDir: string;
  ignorer: ReturnType<typeof ignore>;
}

interface DeploymentListResult {
  total: number;
  deployments: Array<{
    $id: string;
  }>;
}

interface DeploymentDetails {
  $id: string;
  status: string;
  [key: string]: unknown;
}

interface RealtimeEnvelope {
  type?: string;
  data?: {
    events?: string[];
    payload?: DeploymentDetails;
    user?: unknown;
  };
}

interface DeploymentUpdateSubscription {
  close: () => Promise<void>;
}

interface WatchDeploymentUpdatesParams {
  endpoint: string;
  event: string;
  onDeploymentUpdate: (deployment: DeploymentDetails) => void;
}

interface DeploymentLogPrinter {
  ingest: (deployment: DeploymentDetails) => void;
  flush: () => void;
  complete: () => void;
  getLastLogs: () => string;
  hasPrintedLogs: () => boolean;
}

interface DeploymentLogPrinterOptions {
  heading?: string;
  label?: string;
  showPrefix?: boolean;
  isVisible?: () => boolean;
}

interface ClosableDispatcher {
  close?: () => Promise<void>;
  destroy?: () => Promise<void> | void;
}

function getCookieHeader(cookie: string): string | null {
  const [pair = ""] = cookie.split(";", 1);
  const sanitized = pair.trim();

  return sanitized.length > 0 ? sanitized : null;
}

function getSessionSecret(cookieHeader: string | null): string | null {
  if (!cookieHeader) {
    return null;
  }

  const separatorIndex = cookieHeader.indexOf("=");
  if (separatorIndex === -1) {
    return null;
  }

  const value = cookieHeader.slice(separatorIndex + 1);
  if (value.length === 0) {
    return null;
  }

  try {
    return decodeURIComponent(value);
  } catch {
    return value;
  }
}

function getRealtimeUrl(endpoint: string): string {
  const realtimeEndpoint = endpoint
    .replace("https://", "wss://")
    .replace("http://", "ws://");
  const url = new URL(`${realtimeEndpoint}/realtime`);
  url.searchParams.set("project", "console");
  url.searchParams.append("channels[]", "console");

  return url.toString();
}

function getMessageData(rawData: unknown): string | null {
  if (typeof rawData === "string") {
    return rawData;
  }

  if (rawData instanceof ArrayBuffer) {
    return Buffer.from(rawData).toString("utf8");
  }

  if (ArrayBuffer.isView(rawData)) {
    return Buffer.from(
      rawData.buffer,
      rawData.byteOffset,
      rawData.byteLength,
    ).toString("utf8");
  }

  return null;
}

function writeLogChunk(
  label: string | undefined,
  chunk: string,
  showPrefix: boolean,
  includePartial: boolean = false,
): string {
  if (chunk.length === 0) {
    return "";
  }

  const prefix = showPrefix && label ? `${chalk.cyan(`[${label}]`)} ` : "";
  const printableChunk =
    includePartial || chunk.endsWith("\n")
      ? chunk
      : chunk.slice(0, chunk.lastIndexOf("\n") + 1);

  if (printableChunk.length === 0) {
    return "";
  }

  const normalizedChunk = printableChunk.replace(/\r\n/g, "\n");
  const lines = normalizedChunk.split("\n");
  lines.pop();

  for (const line of lines) {
    Spinner.log(`${prefix}${line}`);
  }

  return printableChunk;
}

export function createDeploymentLogPrinter(
  options: DeploymentLogPrinterOptions = {},
): DeploymentLogPrinter {
  let lastLogs = "";
  let lastPrintedLogs = "";
  let hasPrintedHeader = false;
  let hasPrintedLogs = false;
  const {
    heading = "Build logs",
    label,
    showPrefix = false,
    isVisible = () => true,
  } = options;

  const flush = (includePartial: boolean = false): void => {
    if (!isVisible() || lastLogs.length === 0 || lastLogs === lastPrintedLogs) {
      return;
    }

    if (!hasPrintedHeader) {
      Spinner.log("");
      Spinner.log(chalk.cyan.bold(heading));
      Spinner.log("");
      hasPrintedHeader = true;
    }

    if (lastPrintedLogs.length > 0 && lastLogs.startsWith(lastPrintedLogs)) {
      const printedChunk = writeLogChunk(
        label,
        lastLogs.slice(lastPrintedLogs.length),
        showPrefix,
        includePartial,
      );
      if (printedChunk.length === 0) {
        return;
      }
      lastPrintedLogs += printedChunk;
      hasPrintedLogs = true;
      return;
    }

    if (lastPrintedLogs.startsWith(lastLogs)) {
      lastPrintedLogs = lastLogs;
      return;
    }

    const printedChunk = writeLogChunk(
      label,
      lastLogs,
      showPrefix,
      includePartial,
    );
    if (printedChunk.length === 0) {
      return;
    }
    lastPrintedLogs = printedChunk;
    hasPrintedLogs = true;
  };

  return {
    ingest(deployment: DeploymentDetails): void {
      const currentLogs =
        typeof deployment["buildLogs"] === "string"
          ? deployment["buildLogs"]
          : "";

      if (currentLogs.length === 0 || currentLogs === lastLogs) {
        return;
      }

      lastLogs = currentLogs;
      flush();
    },

    flush(): void {
      flush();
    },

    complete(): void {
      flush(true);
    },

    getLastLogs(): string {
      return lastLogs;
    },

    hasPrintedLogs(): boolean {
      return hasPrintedLogs;
    },
  };
}

async function closeDispatcher(dispatcher: ClosableDispatcher): Promise<void> {
  if (typeof dispatcher.close === "function") {
    await dispatcher.close();
    return;
  }

  if (typeof dispatcher.destroy === "function") {
    await dispatcher.destroy();
  }
}

export async function watchDeploymentUpdates(
  params: WatchDeploymentUpdatesParams,
): Promise<DeploymentUpdateSubscription | null> {
  const cookieHeader = getCookieHeader(globalConfig.getCookie());
  if (!cookieHeader) {
    return null;
  }

  const sessionSecret = getSessionSecret(cookieHeader);
  const selfSigned = globalConfig.getSelfSigned();
  const dispatcher = new Agent({
    connect: {
      rejectUnauthorized: !selfSigned,
    },
  });

  let socket: WebSocket;
  try {
    socket = new WebSocket(getRealtimeUrl(params.endpoint), {
      headers: {
        cookie: cookieHeader,
      },
      dispatcher,
    });
  } catch {
    await closeDispatcher(dispatcher);
    return null;
  }

  let closed = false;

  const close = async (): Promise<void> => {
    if (closed) {
      return;
    }

    closed = true;

    await new Promise<void>((resolve) => {
      if (socket.readyState >= WebSocket.CLOSING) {
        resolve();
        return;
      }

      const cleanup = (): void => {
        socket.removeEventListener("close", cleanup);
        resolve();
      };

      socket.addEventListener("close", cleanup, { once: true });
      socket.close(1000, "done");
    });

    await closeDispatcher(dispatcher);
  };

  socket.addEventListener("message", (event: unknown) => {
    const data = getMessageData((event as { data?: unknown }).data);
    if (!data) {
      return;
    }

    let message: RealtimeEnvelope;
    try {
      message = JSON.parse(data) as RealtimeEnvelope;
    } catch {
      return;
    }

    if (
      message.type === "connected" &&
      sessionSecret &&
      !message.data?.user &&
      socket.readyState === WebSocket.OPEN
    ) {
      socket.send(
        JSON.stringify({
          type: "authentication",
          data: {
            session: sessionSecret,
          },
        }),
      );
      return;
    }

    if (message.type !== "event") {
      return;
    }

    if (
      !message.data?.events?.includes(params.event) ||
      !message.data.payload
    ) {
      return;
    }

    params.onDeploymentUpdate(message.data.payload);
  });

  socket.addEventListener("error", () => {
    void close();
  });

  socket.addEventListener("close", () => {
    closed = true;
  });

  return { close };
}

/**
 * Package a directory into a tar.gz File object for deployment
 */
function listDeployableFiles(
  dirPath: string,
  extraIgnoreRules: string[] = [],
): string[] {
  const normalizePath = (value: string): string =>
    value.split(path.sep).join("/");

  const createMatcher = (baseDir: string, rules: string[]): IgnoreMatcher => {
    const ignorer = ignore();
    for (const rule of rules) {
      ignorer.add(rule);
    }
    return {
      baseDir,
      ignorer,
    };
  };

  const loadMatcher = (
    baseDir: string,
    extraRules: string[] = [],
  ): IgnoreMatcher | null => {
    const rules = [...extraRules];
    const gitignorePath = path.join(dirPath, baseDir, ".gitignore");

    if (fs.existsSync(gitignorePath)) {
      rules.push(fs.readFileSync(gitignorePath, "utf8"));
    }

    if (rules.length === 0) {
      return null;
    }

    return createMatcher(baseDir, rules);
  };

  const isIgnored = (
    relativePath: string,
    matchers: IgnoreMatcher[],
    isDirectory: boolean,
  ): boolean => {
    let ignored = false;

    for (const matcher of matchers) {
      if (
        matcher.baseDir !== "" &&
        relativePath !== matcher.baseDir &&
        !relativePath.startsWith(`${matcher.baseDir}/`)
      ) {
        continue;
      }

      const localPath = normalizePath(
        matcher.baseDir === ""
          ? relativePath
          : path.relative(matcher.baseDir, relativePath),
      );

      const result = matcher.ignorer.test(
        isDirectory ? `${localPath}/` : localPath,
      );

      if (result.ignored) {
        ignored = true;
      } else if (result.unignored) {
        ignored = false;
      }
    }

    return ignored;
  };

  const rootMatcher = createMatcher("", [".git", ...extraIgnoreRules]);
  const rootGitignoreMatcher = loadMatcher("");
  const rootMatchers = rootGitignoreMatcher
    ? [rootMatcher, rootGitignoreMatcher]
    : [rootMatcher];

  const files: string[] = [];

  const walk = (relativeDir = "", inheritedMatchers = rootMatchers): void => {
    const absoluteDir = path.join(dirPath, relativeDir);
    const localMatcher = relativeDir === "" ? null : loadMatcher(relativeDir);
    const activeMatchers = localMatcher
      ? [...inheritedMatchers, localMatcher]
      : inheritedMatchers;

    for (const entry of fs.readdirSync(absoluteDir, { withFileTypes: true })) {
      const relativePath = normalizePath(path.join(relativeDir, entry.name));

      if (isIgnored(relativePath, activeMatchers, entry.isDirectory())) {
        continue;
      }

      if (entry.isDirectory()) {
        walk(relativePath, activeMatchers);
      } else {
        files.push(relativePath);
      }
    }
  };

  walk();
  return files;
}

async function packageDirectory(
  dirPath: string,
  extraIgnoreRules: string[] = [],
): Promise<File> {
  const tempFile = path.join(
    os.tmpdir(),
    `appwrite-deploy-${Date.now()}.tar.gz`,
  );
  const files = listDeployableFiles(dirPath, extraIgnoreRules);

  if (files.length === 0) {
    throw new Error(
      `No deployable files found at path: ${dirPath}. Check your .gitignore and ignore rules.`,
    );
  }

  await create(
    {
      gzip: true,
      file: tempFile,
      cwd: dirPath,
    },
    files,
  );

  try {
    const buffer = fs.readFileSync(tempFile);
    return new File([buffer], path.basename(tempFile), {
      type: "application/gzip",
    });
  } finally {
    if (fs.existsSync(tempFile)) {
      fs.unlinkSync(tempFile);
    }
  }
}

/**
 * Resolve a file path (file or directory) into a File object for upload.
 * Directories are packaged into a tar.gz archive.
 */
export async function resolveFileParam(filePath: string): Promise<File> {
  const resolved = path.resolve(filePath);
  if (!fs.existsSync(resolved)) {
    throw new Error(`File or directory not found: ${resolved}`);
  }
  const stat = fs.statSync(resolved);
  if (stat.isDirectory()) {
    return packageDirectory(resolved);
  }
  const buffer = fs.readFileSync(resolved);
  return new File([buffer], path.basename(resolved));
}

/**
 * Download and extract deployment code for a resource
 */
export async function downloadDeploymentCode(params: {
  resourceId: string;
  resourcePath: string;
  holdingVars: { key: string; value: string }[];
  withVariables?: boolean;
  listDeployments: () => Promise<DeploymentListResult>;
  getDownloadUrl: (deploymentId: string) => string;
  projectClient: Client;
}): Promise<void> {
  const {
    resourceId,
    resourcePath,
    holdingVars,
    withVariables,
    listDeployments,
    getDownloadUrl,
    projectClient,
  } = params;

  let deploymentId: string | null = null;
  try {
    const deployments = await listDeployments();
    if (deployments["total"] > 0) {
      deploymentId = deployments["deployments"][0]["$id"];
    }
  } catch (e: unknown) {
    if (e instanceof AppwriteException) {
      error(e.message);
      return;
    } else {
      throw e;
    }
  }

  if (deploymentId === null) {
    return;
  }

  const compressedFileName = path.resolve(
    path.dirname(resourcePath),
    `${resourceId}-${+new Date()}.tar.gz`,
  );
  const downloadUrl = getDownloadUrl(deploymentId);

  const downloadBuffer = await projectClient.call(
    "get",
    new URL(downloadUrl),
    {},
    {},
    "arrayBuffer",
  );

  if (!(downloadBuffer instanceof ArrayBuffer)) {
    throw new Error("Failed to download deployment archive as ArrayBuffer.");
  }

  try {
    fs.writeFileSync(compressedFileName, Buffer.from(downloadBuffer));
  } catch (err) {
    const message = err instanceof Error ? err.message : String(err);
    throw new Error(
      `Failed to write deployment archive to "${compressedFileName}": ${message}`,
    );
  }

  extract({
    sync: true,
    cwd: resourcePath,
    file: compressedFileName,
    strict: false,
  });

  fs.rmSync(compressedFileName);

  if (withVariables) {
    const envFileLocation = `${resourcePath}/.env`;
    try {
      fs.rmSync(envFileLocation);
    } catch {}

    fs.writeFileSync(
      envFileLocation,
      holdingVars.map((r) => `${r.key}=${r.value}\n`).join(""),
    );
  }
}

export interface PushDeploymentParams {
  resourcePath: string;
  createDeployment: (codeFile: File) => Promise<DeploymentDetails>;
  extraIgnoreRules?: string[];
  getDeployment?: (deploymentId: string) => Promise<DeploymentDetails>;
  pollForStatus?: boolean;
  onStatusUpdate?: (status: string) => void;
}

export interface PushDeploymentResult {
  deployment: DeploymentDetails;
  wasPolled: boolean;
  finalStatus?: string;
}

/**
 * Push a deployment for a resource (function or site)
 * Handles packaging, creating the deployment, and optionally polling for status
 */
export async function pushDeployment(
  params: PushDeploymentParams,
): Promise<PushDeploymentResult> {
  const {
    resourcePath,
    createDeployment,
    extraIgnoreRules = [],
    getDeployment,
    pollForStatus = false,
    onStatusUpdate,
  } = params;

  // Package the directory
  const codeFile = await packageDirectory(resourcePath, extraIgnoreRules);

  // Create the deployment
  let deployment = await createDeployment(codeFile);

  // Poll for deployment status if requested
  let finalStatus: string | undefined;
  let wasPolled = false;

  if (pollForStatus && getDeployment) {
    wasPolled = true;
    const deploymentId = deployment["$id"];

    while (true) {
      deployment = await getDeployment(deploymentId);
      const status = deployment["status"];

      if (onStatusUpdate) {
        onStatusUpdate(status);
      }

      if (status === "ready" || status === "failed") {
        finalStatus = status;
        break;
      }

      await new Promise((resolve) => setTimeout(resolve, POLL_DEBOUNCE * 1.5));
    }
  }

  return {
    deployment,
    wasPolled,
    finalStatus,
  };
}
