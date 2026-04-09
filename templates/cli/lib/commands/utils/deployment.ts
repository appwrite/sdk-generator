import fs from "fs";
import os from "os";
import path from "path";
import { create, extract } from "tar";
import ignoreModule from "ignore";
import { Client, AppwriteException } from "@appwrite.io/console";
import { error } from "../../parser.js";

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
