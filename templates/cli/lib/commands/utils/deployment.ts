import fs from "fs";
import path from "path";
import tar from "tar";
import { Client, AppwriteException } from "@appwrite.io/console";
import { error } from "../../parser.js";

const POLL_DEBOUNCE = 2000; // Milliseconds

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
 * @private - Only used internally by pushDeployment
 */
async function packageDirectory(dirPath: string): Promise<File> {
  const tempFile = `${dirPath.replace(/[^a-zA-Z0-9]/g, "_")}-${Date.now()}.tar.gz`;

  await tar.create(
    {
      gzip: true,
      file: tempFile,
      cwd: dirPath,
    },
    ["."],
  );

  const buffer = fs.readFileSync(tempFile);
  fs.unlinkSync(tempFile);

  return new File([buffer], path.basename(tempFile), {
    type: "application/gzip",
  });
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

  tar.extract({
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
    getDeployment,
    pollForStatus = false,
    onStatusUpdate,
  } = params;

  // Package the directory
  const codeFile = await packageDirectory(resourcePath);

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
