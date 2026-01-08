import { globalConfig, localConfig } from "./config.js";
import { Client } from "@appwrite.io/console";
import os from "os";
import {
  DEFAULT_ENDPOINT,
  EXECUTABLE_NAME,
  SDK_TITLE,
  SDK_VERSION,
} from "./constants.js";

export const sdkForConsole = async (
  requiresAuth: boolean = true,
): Promise<Client> => {
  const client = new Client();
  const endpoint = globalConfig.getEndpoint() || DEFAULT_ENDPOINT;
  const cookie = globalConfig.getCookie();
  const selfSigned = globalConfig.getSelfSigned();

  if (requiresAuth && cookie === "") {
    throw new Error(
      `Session not found. Please run \`${EXECUTABLE_NAME} login\` to create a session`,
    );
  }

  client.headers = {
    "x-sdk-name": "Command Line",
    "x-sdk-platform": "console",
    "x-sdk-language": "cli",
    "x-sdk-version": SDK_VERSION,
    "user-agent": `AppwriteCLI/${SDK_VERSION} (${os.type()} ${os.version()}; ${os.arch()})`,
  };

  client
    .setEndpoint(endpoint)
    .setProject("console")
    .setCookie(cookie)
    .setSelfSigned(selfSigned)
    .setLocale("en-US");

  return client;
};

export const sdkForProject = async (): Promise<Client> => {
  const client = new Client();

  const endpoint =
    localConfig.getEndpoint() || globalConfig.getEndpoint() || DEFAULT_ENDPOINT;

  const project = localConfig.getProject().projectId
    ? localConfig.getProject().projectId
    : globalConfig.getProject();

  const key = globalConfig.getKey();
  const cookie = globalConfig.getCookie();
  const selfSigned = globalConfig.getSelfSigned();

  if (!project) {
    throw new Error(
      `Project is not set. Please run \`${EXECUTABLE_NAME} init project\` to initialize the current directory with an ${SDK_TITLE} project.`,
    );
  }

  client.headers = {
    "x-sdk-name": "Command Line",
    "x-sdk-platform": "console",
    "x-sdk-language": "cli",
    "x-sdk-version": SDK_VERSION,
    "user-agent": `AppwriteCLI/${SDK_VERSION} (${os.type()} ${os.version()}; ${os.arch()})`,
  };

  client
    .setEndpoint(endpoint)
    .setProject(project)
    .setSelfSigned(selfSigned)
    .setLocale("en-US");

  if (cookie) {
    return client.setCookie(cookie).setMode("admin");
  }

  if (key) {
    return client.setKey(key).setMode("default");
  }

  throw new Error(
    `Session not found. Please run \`${EXECUTABLE_NAME} login\` to create a session.`,
  );
};
