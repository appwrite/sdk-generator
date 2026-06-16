import { globalConfig, localConfig, normalizeCloudConsoleEndpoint } from "./config.js";
import { Client, Oauth2 } from "@appwrite.io/console";
import os from "os";
import {
  DEFAULT_ENDPOINT,
  EXECUTABLE_NAME,
  SDK_TITLE,
  SDK_VERSION,
} from "./constants.js";
import { warn } from "./parser.js";

export const OAUTH2_CLIENT_ID = "appwrite-cli";
export const OAUTH2_SCOPES = "openid email profile";

let legacySessionWarningShown = false;

const warnLegacySession = (): void => {
  if (legacySessionWarningShown) {
    return;
  }

  legacySessionWarningShown = true;
  warn(
    `This CLI is using a legacy cookie session. Run \`${EXECUTABLE_NAME} login --new\` to switch to the new browser-based login flow.`,
  );
};

const getValidAccessToken = async (endpoint: string): Promise<string> => {
  const accessToken = globalConfig.getAccessToken();
  const refreshToken = globalConfig.getRefreshToken();
  const tokenExpiry = globalConfig.getTokenExpiry();
  const clientId = globalConfig.getClientId() || OAUTH2_CLIENT_ID;

  if (accessToken && tokenExpiry > Date.now() + 60_000) {
    return accessToken;
  }

  if (!refreshToken) {
    throw new Error(
      `Session expired. Please run \`${EXECUTABLE_NAME} login\` to create a new session.`,
    );
  }

  const oauth2Client = new Client()
    .setEndpoint(endpoint)
    .setProject("console")
    .setSelfSigned(globalConfig.getSelfSigned());
  const oauth2 = new Oauth2(oauth2Client);
  const token = await oauth2.createToken({
    grantType: "refresh_token",
    refreshToken,
    clientId,
  });
  const newExpiry = Date.now() + token.expires_in * 1000;
  globalConfig.setAccessToken(token.access_token);
  if (token.refresh_token) {
    globalConfig.setRefreshToken(token.refresh_token);
  }
  globalConfig.setTokenExpiry(newExpiry);

  return token.access_token;
};

export const sdkForConsole = async ({
  requiresAuth = true,
  endpointOverride,
  organizationId,
}: {
  requiresAuth?: boolean;
  endpointOverride?: string;
  organizationId?: string;
} = {}): Promise<Client> => {
  const client = new Client();
  const endpoint = normalizeCloudConsoleEndpoint(
    endpointOverride || globalConfig.getEndpoint() || DEFAULT_ENDPOINT,
  );
  const selfSigned = globalConfig.getSelfSigned();

  const accessToken = globalConfig.getAccessToken();
  const cookie = globalConfig.getCookie();

  if (requiresAuth && !accessToken && !cookie) {
    throw new Error(
      `Session not found. Please run \`${EXECUTABLE_NAME} login\` to create a session`,
    );
  }

  client.headers = {
    ...client.headers,
    "x-sdk-name": "Command Line",
    "x-sdk-platform": "console",
    "x-sdk-language": "cli",
    "x-sdk-version": SDK_VERSION,
    "user-agent": `AppwriteCLI/${SDK_VERSION} (${os.type()} ${os.version()}; ${os.arch()})`,
  };

  client
    .setEndpoint(endpoint)
    .setProject("console")
    .setSelfSigned(selfSigned)
    .setLocale("en-US");

  if (requiresAuth) {
    if (accessToken) {
      const validAccessToken = await getValidAccessToken(endpoint);
      client.headers["Authorization"] = `Bearer ${validAccessToken}`;
    } else if (cookie) {
      warnLegacySession();
      client.setCookie(cookie);
    }
  }

  if (organizationId) {
    client.headers["X-Appwrite-Organization"] = organizationId;
  }

  return client;
};

export const sdkForProject = async (): Promise<Client> => {
  const client = new Client();

  const endpoint = normalizeCloudConsoleEndpoint(
    localConfig.getEndpoint() || globalConfig.getEndpoint() || DEFAULT_ENDPOINT,
  );

  const project = localConfig.getProject().projectId
    ? localConfig.getProject().projectId
    : globalConfig.getProject();

  const key = globalConfig.getKey();
  const accessToken = globalConfig.getAccessToken();
  const cookie = globalConfig.getCookie();
  const selfSigned = globalConfig.getSelfSigned();

  if (!project) {
    throw new Error(
      `Project is not set. Please run \`${EXECUTABLE_NAME} init project\` to initialize the current directory with an ${SDK_TITLE} project.`,
    );
  }

  client.headers = {
    ...client.headers,
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

  if (accessToken) {
    const validAccessToken = await getValidAccessToken(endpoint);
    client.headers["Authorization"] = `Bearer ${validAccessToken}`;
    return client.setMode("admin");
  }

  if (cookie) {
    warnLegacySession();
    client.setCookie(cookie);
    return client.setMode("admin");
  }

  if (key) {
    return client.setKey(key).setMode("default");
  }

  throw new Error(
    `Session not found. Please run \`${EXECUTABLE_NAME} login\` to create a session.`,
  );
};
