import {
  AppwriteException,
  Client,
  Oauth2,
  type Models,
} from "@appwrite.io/console";
import { globalConfig, normalizeCloudConsoleEndpoint } from "../config.js";
import { EXECUTABLE_NAME } from "../constants.js";

export const OAUTH2_CLIENT_ID = "appwrite-cli";
export const OAUTH2_SCOPES = "openid email profile";

export type DeviceAuthorization = Awaited<
  ReturnType<Oauth2["createDeviceAuthorization"]>
>;

const sleep = (ms: number): Promise<void> =>
  new Promise((resolve) => setTimeout(resolve, ms));

/**
 * Build an Oauth2 client for the console project at the given endpoint.
 * The endpoint is used as-is; callers normalize it when required.
 */
export const createOauth2 = (endpoint: string): Oauth2 => {
  const client = new Client()
    .setEndpoint(endpoint)
    .setProject("console")
    .setSelfSigned(globalConfig.getSelfSigned());
  return new Oauth2(client);
};

export const decodeIdToken = (
  idToken: string,
): { email?: string; name?: string; sub?: string } => {
  try {
    const payload = idToken.split(".")[1];
    if (!payload) return {};
    const decoded = JSON.parse(
      Buffer.from(payload, "base64url").toString("utf-8"),
    );
    return {
      email: decoded.email,
      name: decoded.name,
      sub: decoded.sub,
    };
  } catch (_error) {
    return {};
  }
};

export const isAuthorizationPendingError = (err: unknown): boolean => {
  if (!(err instanceof AppwriteException)) {
    return false;
  }

  const response = typeof err.response === "string" ? err.response : "";

  return (
    err.type === "authorization_pending" ||
    err.type === "slow_down" ||
    err.message === "authorization_pending" ||
    err.message === "slow_down" ||
    response.includes("authorization_pending") ||
    response.includes("slow_down")
  );
};

/**
 * Poll the token endpoint until the device authorization is approved.
 * Returns the token, or `null` if the authorization window expires.
 * Pending/slow-down responses are retried; any other error is thrown.
 */
export const pollForDeviceToken = async (
  oauth2: Oauth2,
  deviceAuth: DeviceAuthorization,
  clientId: string,
): Promise<Models.Oauth2Token | null> => {
  const expiresAt = Date.now() + deviceAuth.expires_in * 1000;

  while (Date.now() < expiresAt) {
    await sleep(deviceAuth.interval * 1000);

    let token: Models.Oauth2Token;
    try {
      token = await oauth2.createToken({
        grantType: "urn:ietf:params:oauth:grant-type:device_code",
        deviceCode: deviceAuth.device_code,
        clientId,
      });
    } catch (err) {
      if (isAuthorizationPendingError(err)) {
        continue;
      }
      throw err;
    }

    if (token) {
      return token;
    }
  }

  return null;
};

export const revokeRefreshToken = async (
  endpoint: string,
  refreshToken: string,
  clientId: string,
): Promise<void> => {
  const oauth2 = createOauth2(endpoint);
  await oauth2.revoke({
    projectId: "console",
    token: refreshToken,
    tokenTypeHint: "refresh_token",
    clientId,
  });
};

export const getValidAccessToken = async (
  endpoint: string,
): Promise<string> => {
  const accessToken = globalConfig.getAccessToken();
  const refreshToken = globalConfig.getRefreshToken();
  const tokenExpiry = globalConfig.getTokenExpiry();
  const clientId = globalConfig.getClientId() || OAUTH2_CLIENT_ID;
  const consoleEndpoint = normalizeCloudConsoleEndpoint(endpoint);

  if (accessToken && tokenExpiry > Date.now() + 60_000) {
    return accessToken;
  }

  if (!refreshToken) {
    throw new Error(
      `Session expired. Please run \`${EXECUTABLE_NAME} login\` to create a new session.`,
    );
  }

  const oauth2 = createOauth2(consoleEndpoint);
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
