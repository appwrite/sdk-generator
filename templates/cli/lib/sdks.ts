import {
  endpointsMatch,
  globalConfig,
  localConfig,
  normalizeCloudConsoleEndpoint,
} from "./config.js";
import { Client, Oauth2 } from "@appwrite.io/console";
import os from "os";
import {
  DEFAULT_ENDPOINT,
  EXECUTABLE_NAME,
  OAUTH2_CLIENT_ID,
  SDK_TITLE,
  SDK_VERSION,
} from "./constants.js";
import { warn } from "./parser.js";
import { resolveOrganizationId, resolveProjectId } from "./context.js";
import { isCloudHostname } from "./utils.js";
import {
  getStoredRefreshToken,
  setStoredRefreshToken,
} from "./auth/refresh-token.js";

export const assertSessionEndpointMatches = (endpoint: string): void => {
  const sessionEndpoint = globalConfig.getEndpoint() || DEFAULT_ENDPOINT;
  if (!endpointsMatch(endpoint, sessionEndpoint)) {
    throw new Error(
      `Endpoint ${endpoint} does not match the current login session endpoint ${sessionEndpoint}. Switch to an account for this environment with \`${EXECUTABLE_NAME} login --switch\`.`,
    );
  }
};

export const getValidAccessToken = async (
  options: { forceRefresh?: boolean } = {},
): Promise<string> => {
  const accessToken = globalConfig.getAccessToken();
  const tokenExpiry = globalConfig.getTokenExpiry();
  const clientId = globalConfig.getClientId() || OAUTH2_CLIENT_ID;
  const currentSession = globalConfig.getCurrentSession();
  const sessionEndpoint = globalConfig.getEndpoint() || DEFAULT_ENDPOINT;

  if (
    !options.forceRefresh &&
    accessToken &&
    tokenExpiry > Date.now() + 60_000
  ) {
    return accessToken;
  }

  const refreshToken = currentSession
    ? getStoredRefreshToken(currentSession)
    : "";

  if (accessToken && tokenExpiry === 0 && !refreshToken) {
    return accessToken;
  }

  if (!refreshToken) {
    throw new Error(
      `Session expired. Please run \`${EXECUTABLE_NAME} login\` to create a new session.`,
    );
  }

  const oauth2 = new Oauth2(
    new Client()
      .setEndpoint(normalizeCloudConsoleEndpoint(sessionEndpoint))
      .setProject("console")
      .setSelfSigned(globalConfig.getSelfSigned()),
  );
  const token = await oauth2.createToken({
    grantType: "refresh_token",
    refreshToken,
    clientId,
  });
  const newExpiry = Date.now() + token.expires_in * 1000;
  globalConfig.setAccessToken(token.access_token);
  if (token.refresh_token) {
    setStoredRefreshToken(currentSession, token.refresh_token);
  }
  globalConfig.setTokenExpiry(newExpiry);

  return token.access_token;
};

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

export const sdkForConsole = async ({
  requiresAuth = true,
  endpointOverride,
  organizationId,
  preserveRegion = false,
}: {
  requiresAuth?: boolean;
  endpointOverride?: string;
  organizationId?: string;
  preserveRegion?: boolean;
} = {}): Promise<Client> => {
  const client = new Client();
  const configuredEndpoint =
    endpointOverride || globalConfig.getEndpoint() || DEFAULT_ENDPOINT;
  const endpoint = preserveRegion
    ? configuredEndpoint
    : normalizeCloudConsoleEndpoint(configuredEndpoint);
  const isCloudEndpoint = isCloudHostname(new URL(endpoint).hostname);
  const selfSigned = globalConfig.getSelfSigned();

  const accessToken = globalConfig.getAccessToken();
  const cookie = globalConfig.getCookie();

  if (requiresAuth && !accessToken && !cookie) {
    const hasKey = globalConfig.getKey() !== "";
    throw new Error(
      hasKey
        ? `Session not found. Run \`${EXECUTABLE_NAME} login\`. API keys work for project commands (e.g. \`${EXECUTABLE_NAME} push functions\`), not console-only commands (e.g. \`${EXECUTABLE_NAME} push settings\`).`
        : `Session not found. Please run \`${EXECUTABLE_NAME} login\` to create a session`,
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

  if (requiresAuth && (accessToken || cookie)) {
    assertSessionEndpointMatches(endpoint);
  }

  if (requiresAuth) {
    if (accessToken) {
      const validAccessToken = await getValidAccessToken();
      client.headers["Authorization"] = `Bearer ${validAccessToken}`;
    } else if (cookie) {
      if (isCloudEndpoint) {
        warnLegacySession();
      }
      client.setCookie(cookie);
    }
  }

  if (organizationId) {
    client.headers["X-Appwrite-Organization"] = organizationId;
  }

  return client;
};

/**
 * The `/organization` endpoints carry no organization ID in their path and act
 * on whichever organization `X-Appwrite-Organization` names, so resolve it from
 * the current directory's config unless the caller names one explicitly.
 */
export const sdkForConsoleWithOrganization = async (
  organizationId?: string,
): Promise<Client> => {
  const client = await sdkForConsole();

  client.headers["X-Appwrite-Organization"] = await resolveOrganizationId({
    override: organizationId,
    consoleClient: client,
  });

  return client;
};

export const sdkForProject = async (
  projectIdOverride?: string,
): Promise<Client> => {
  const client = new Client();

  const endpoint =
    localConfig.getEndpoint() || globalConfig.getEndpoint() || DEFAULT_ENDPOINT;
  const isCloudEndpoint = isCloudHostname(new URL(endpoint).hostname);

  const project = resolveProjectId(projectIdOverride);

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

  if (accessToken || cookie) {
    assertSessionEndpointMatches(endpoint);
  }

  if (accessToken) {
    const validAccessToken = await getValidAccessToken();
    client.headers["Authorization"] = `Bearer ${validAccessToken}`;
    return client.setMode("admin");
  }

  if (cookie) {
    if (isCloudEndpoint) {
      warnLegacySession();
    }
    client.setCookie(cookie);
    return client.setMode("admin");
  }

  if (key) {
    return client.setKey(key).setMode("default");
  }

  throw new Error(
    `Authentication not found. Run \`${EXECUTABLE_NAME} login\` or \`${EXECUTABLE_NAME} client --key <API_KEY>\`.`,
  );
};
