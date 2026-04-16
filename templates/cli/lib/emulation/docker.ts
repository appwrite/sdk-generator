import ignoreModule from "ignore";
const ignore: typeof ignoreModule =
  (ignoreModule as unknown as { default?: typeof ignoreModule }).default ??
  ignoreModule;
import net from "net";
import chalk from "chalk";
import childProcess from "child_process";
import { localConfig } from "../config.js";
import path from "path";
import fs from "fs";
import { log, error, success } from "../parser.js";
import { openRuntimesVersion, systemTools, Queue } from "./utils.js";
import { getAllFiles } from "../utils.js";
import type { FunctionType } from "../commands/config.js";

function getFunctionIgnorer(
  func: FunctionType,
  functionDir: string,
): ignoreModule.Ignore {
  const ignorer = ignore();
  ignorer.add(".appwrite");

  if (func.ignore) {
    ignorer.add(func.ignore);
  } else if (fs.existsSync(path.join(functionDir, ".gitignore"))) {
    ignorer.add(
      fs.readFileSync(path.join(functionDir, ".gitignore")).toString(),
    );
  }

  return ignorer;
}

function getFunctionFiles(func: FunctionType): {
  functionDir: string;
  files: string[];
  ignorer: ignoreModule.Ignore;
} {
  const functionDir = path.join(localConfig.getDirname(), func.path);
  const ignorer = getFunctionIgnorer(func, functionDir);
  const files = getAllFiles(functionDir)
    .map((file) => path.relative(functionDir, file))
    .filter((file) => !ignorer.ignores(file));

  return { functionDir, files, ignorer };
}

export function assertFunctionSourceCode(func: FunctionType): void {
  const functionDir = path.join(localConfig.getDirname(), func.path);

  if (!fs.existsSync(functionDir)) {
    throw new Error(
      `Function path '${func.path}' was not found. Add your source code before running locally.`,
    );
  }

  const { files, ignorer } = getFunctionFiles(func);

  if (!func.entrypoint) {
    throw new Error(
      `Function '${func.name}' is missing an entrypoint. Update appwrite.config.json before running locally.`,
    );
  }

  const normalizedEntrypoint = path.normalize(func.entrypoint);
  const relativeEntrypoint = normalizedEntrypoint.split(path.sep).join("/");

  if (ignorer.ignores(relativeEntrypoint)) {
    throw new Error(
      `Entrypoint '${func.entrypoint}' is ignored by your local ignore rules. Update appwrite.config.json or your ignore file before running locally.`,
    );
  }

  if (!fs.existsSync(path.join(functionDir, normalizedEntrypoint))) {
    throw new Error(
      `Entrypoint '${func.entrypoint}' was not found in '${func.path}'. Add your source code before running locally.`,
    );
  }

  if (files.length === 0) {
    throw new Error(
      `No source files were found in '${func.path}'. Add your source code before running locally.`,
    );
  }
}

function getRuntimeImageName(func: FunctionType): string {
  const runtimeChunks = func.runtime.split("-");
  const runtimeVersion = runtimeChunks.pop();
  const runtimeName = runtimeChunks.join("-");

  return `openruntimes/${runtimeName}:${openRuntimesVersion}-${runtimeVersion}`;
}

async function waitForProcessClose(
  process: childProcess.ChildProcessWithoutNullStreams,
): Promise<{ code: number | null; signal: NodeJS.Signals | null }> {
  return new Promise((resolve, reject) => {
    process.once("error", reject);
    process.once("close", (code, signal) => {
      resolve({ code, signal });
    });
  });
}

function assertDockerSuccess(
  code: number | null,
  signal: NodeJS.Signals | null,
  errorMessage: string,
): void {
  if (code === 0) {
    return;
  }

  if (signal) {
    throw new Error(
      `${errorMessage} Docker process exited with signal ${signal}.`,
    );
  }

  throw new Error(
    `${errorMessage} Docker process exited with code ${code ?? "unknown"}.`,
  );
}

function getDockerExitMessage(
  code: number | null,
  signal: NodeJS.Signals | null,
): string {
  if (signal) {
    return `Docker process exited with signal ${signal}.`;
  }

  return `Docker process exited with code ${code ?? "unknown"}.`;
}

function waitForProcessOutput(
  process: childProcess.ChildProcessWithoutNullStreams,
  needle: string,
): Promise<void> {
  return new Promise((resolve) => {
    let output = "";

    const onData = (data: Buffer): void => {
      output += data.toString();

      if (output.includes(needle)) {
        cleanup();
        resolve();
      }

      if (output.length > needle.length * 4) {
        output = output.slice(-needle.length * 4);
      }
    };

    const cleanup = (): void => {
      process.stdout.off("data", onData);
      process.stderr.off("data", onData);
      process.off("close", cleanup);
      process.off("error", cleanup);
    };

    process.stdout.on("data", onData);
    process.stderr.on("data", onData);
    process.once("close", cleanup);
    process.once("error", cleanup);
  });
}

export async function dockerStop(id: string): Promise<void> {
  const stopProcess = childProcess.spawn("docker", ["rm", "--force", id], {
    stdio: "pipe",
    env: {
      ...process.env,
      DOCKER_CLI_HINTS: "false",
    },
  });

  await new Promise<void>((res) => {
    stopProcess.on("close", res);
  });
}

export async function dockerPull(func: FunctionType): Promise<void> {
  const imageName = getRuntimeImageName(func);

  log("Verifying Docker image ...");

  const pullProcess = childProcess.spawn("docker", ["pull", imageName], {
    stdio: "pipe",
    env: {
      ...process.env,
      DOCKER_CLI_HINTS: "false",
    },
  });

  const { code, signal } = await waitForProcessClose(pullProcess);
  assertDockerSuccess(
    code,
    signal,
    `Unable to pull Docker image '${imageName}'.`,
  );
}

export async function dockerBuild(
  func: FunctionType,
  variables: Record<string, string>,
): Promise<void> {
  const imageName = getRuntimeImageName(func);

  const { functionDir, files } = getFunctionFiles(func);

  const id = func.$id;
  const tmpBuildPath = path.join(functionDir, ".appwrite/tmp-build");
  if (!fs.existsSync(tmpBuildPath)) {
    fs.mkdirSync(tmpBuildPath, { recursive: true });
  }

  let killInterval: ReturnType<typeof setInterval> | undefined;

  try {
    for (const f of files) {
      const filePath = path.join(tmpBuildPath, f);
      const fileDir = path.dirname(filePath);
      if (!fs.existsSync(fileDir)) {
        fs.mkdirSync(fileDir, { recursive: true });
      }

      const sourcePath = path.join(functionDir, f);
      fs.copyFileSync(sourcePath, filePath);
    }

    const params: string[] = ["run"];
    params.push("--name", id);
    params.push("-v", `${tmpBuildPath}/:/mnt/code:rw`);
    params.push("-e", "OPEN_RUNTIMES_ENV=development");
    params.push("-e", "OPEN_RUNTIMES_SECRET=");
    params.push("-e", `OPEN_RUNTIMES_ENTRYPOINT=${func.entrypoint}`);

    for (const k of Object.keys(variables)) {
      params.push("-e", `${k}=${variables[k]}`);
    }

    params.push(imageName, "sh", "-c", `helpers/build.sh "${func.commands}"`);

    const buildProcess = childProcess.spawn("docker", params, {
      stdio: "pipe",
      cwd: functionDir,
      env: {
        ...process.env,
        DOCKER_CLI_HINTS: "false",
      },
    });

    let hasPrintedBuildSeparator = false;
    const writeBuildChunk = (
      stream: NodeJS.WriteStream,
      data: Buffer | string,
    ): void => {
      if (!hasPrintedBuildSeparator) {
        stream.write("\n");
        hasPrintedBuildSeparator = true;
      }

      stream.write(chalk.blackBright(data));
    };

    buildProcess.stdout.on("data", (data) => {
      writeBuildChunk(process.stdout, data);
    });

    buildProcess.stderr.on("data", (data) => {
      writeBuildChunk(process.stderr, data);
    });

    killInterval = setInterval(() => {
      if (!Queue.isEmpty()) {
        log("Cancelling build ...");
        buildProcess.stdout.destroy();
        buildProcess.stdin.destroy();
        buildProcess.stderr.destroy();
        buildProcess.kill("SIGKILL");
        clearInterval(killInterval);
      }
    }, 100);

    const { code, signal } = await waitForProcessClose(buildProcess);

    clearInterval(killInterval);
    killInterval = undefined;

    if (!Queue.isEmpty()) {
      await dockerStop(id);
      return;
    }

    assertDockerSuccess(
      code,
      signal,
      `Unable to build function '${func.name}'.`,
    );

    const copyPath = path.join(
      localConfig.getDirname(),
      func.path,
      ".appwrite",
      "build.tar.gz",
    );
    const copyDir = path.dirname(copyPath);
    if (!fs.existsSync(copyDir)) {
      fs.mkdirSync(copyDir, { recursive: true });
    }

    const copyProcess = childProcess.spawn(
      "docker",
      ["cp", `${id}:/mnt/code/code.tar.gz`, copyPath],
      {
        stdio: "pipe",
        cwd: functionDir,
        env: {
          ...process.env,
          DOCKER_CLI_HINTS: "false",
        },
      },
    );

    const copyResult = await waitForProcessClose(copyProcess);
    assertDockerSuccess(
      copyResult.code,
      copyResult.signal,
      `Unable to copy built bundle for function '${func.name}'.`,
    );

    await dockerStop(id);
  } finally {
    await dockerStop(id);

    // Clean up interval if still running
    if (killInterval !== undefined) {
      clearInterval(killInterval);
    }

    // Clean up temp files
    const tempPath = path.join(
      localConfig.getDirname(),
      func.path,
      "code.tar.gz",
    );
    if (fs.existsSync(tempPath)) {
      fs.rmSync(tempPath, { force: true });
    }

    // Always clean up tmpBuildPath
    fs.rmSync(tmpBuildPath, { recursive: true, force: true });
  }
}

export async function dockerStart(
  func: FunctionType,
  variables: Record<string, string>,
  port: number,
): Promise<void> {
  // Pack function files
  const functionDir = path.join(localConfig.getDirname(), func.path);

  const imageName = getRuntimeImageName(func);
  const runtimeName = func.runtime.split("-").slice(0, -1).join("-");

  const tool = systemTools[runtimeName];

  const id = func.$id;

  const params: string[] = ["run"];
  params.push("--rm");
  params.push("--name", id);
  params.push("-p", `${port}:3000`);
  params.push("-e", "OPEN_RUNTIMES_ENV=development");
  params.push("-e", "OPEN_RUNTIMES_SECRET=");
  params.push("-e", `OPEN_RUNTIMES_ENTRYPOINT=${func.entrypoint}`);

  for (const k of Object.keys(variables)) {
    params.push("-e", `${k}=${variables[k]}`);
  }

  params.push(
    "-v",
    `${functionDir}/.appwrite/logs.txt:/mnt/logs/dev_logs.log:rw`,
  );
  params.push(
    "-v",
    `${functionDir}/.appwrite/errors.txt:/mnt/logs/dev_errors.log:rw`,
  );
  params.push(
    "-v",
    `${functionDir}/.appwrite/build.tar.gz:/mnt/code/code.tar.gz:ro`,
  );
  params.push(imageName, "sh", "-c", `helpers/start.sh "${tool.startCommand}"`);

  const startProcess = childProcess.spawn("docker", params, {
    stdio: "pipe",
    cwd: functionDir,
    env: {
      ...process.env,
      DOCKER_CLI_HINTS: "false",
    },
  });

  startProcess.stdout.on("data", (data) => {
    process.stdout.write(chalk.blackBright(data));
  });

  startProcess.stderr.on("data", (data) => {
    process.stderr.write(chalk.blackBright(data));
  });

  const startProcessExit = waitForProcessClose(startProcess);
  void startProcessExit.catch(() => {});
  const startupLogDetected = waitForProcessOutput(
    startProcess,
    "HTTP server successfully started!",
  );
  void startupLogDetected.catch(() => {});

  try {
    const result = await Promise.race([
      waitUntilPortOpen(port).then(() => ({
        type: "port-open" as const,
      })),
      startProcessExit.then(({ code, signal }) => ({
        type: "process-exit" as const,
        code,
        signal,
      })),
    ]);

    if (result.type === "process-exit") {
      throw new Error(
        `Function container exited before opening port ${port}. ${getDockerExitMessage(result.code, result.signal)}`,
      );
    }

    const postStartupResult = await Promise.race([
      startupLogDetected.then(() => ({
        type: "startup-log" as const,
      })),
      startProcessExit.then(({ code, signal }) => ({
        type: "process-exit" as const,
        code,
        signal,
      })),
      new Promise<{ type: "timeout" }>((resolve) => {
        setTimeout(() => resolve({ type: "timeout" }), 1500);
      }),
    ]);

    if (postStartupResult.type === "process-exit") {
      throw new Error(
        `Function container exited before startup logs completed. ${getDockerExitMessage(postStartupResult.code, postStartupResult.signal)}`,
      );
    }
  } catch (err: unknown) {
    const message = err instanceof Error ? err.message : String(err);
    error(`Failed to start function with error: ${message}`);
    return;
  }

  success(`Visit http://localhost:${port}/ to execute your function.`);
}

export async function dockerCleanup(functionId: string): Promise<void> {
  await dockerStop(functionId);

  const func = localConfig.getFunction(functionId);
  const appwritePath = path.join(
    localConfig.getDirname(),
    func.path,
    ".appwrite",
  );
  if (fs.existsSync(appwritePath)) {
    fs.rmSync(appwritePath, { recursive: true, force: true });
  }

  const tempPath = path.join(
    localConfig.getDirname(),
    func.path,
    "code.tar.gz",
  );
  if (fs.existsSync(tempPath)) {
    fs.rmSync(tempPath, { force: true });
  }
}

function waitUntilPortOpen(port: number, iteration: number = 0): Promise<void> {
  return new Promise((resolve, reject) => {
    const client = new net.Socket();

    client.once("connect", () => {
      client.removeAllListeners("connect");
      client.removeAllListeners("error");
      client.end();
      client.destroy();
      client.unref();

      resolve();
    });

    client.once("error", async (err) => {
      client.removeAllListeners("connect");
      client.removeAllListeners("error");
      client.end();
      client.destroy();
      client.unref();

      if (iteration > 100) {
        reject(err);
      } else {
        await new Promise<void>((res) => setTimeout(res, 100));
        waitUntilPortOpen(port, iteration + 1)
          .then(resolve)
          .catch(reject);
      }
    });

    client.connect({ port, host: "127.0.0.1" }, function () {});
  });
}
