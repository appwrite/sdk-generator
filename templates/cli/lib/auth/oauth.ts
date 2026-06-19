import { AppwriteException, Oauth2, type Models } from "@appwrite.io/console";
import { sdkForConsole } from "../sdks.js";
import { getOauth2Service } from "../services.js";

export type DeviceAuthorization = Awaited<
  ReturnType<Oauth2["createDeviceAuthorization"]>
>;

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

const matchesOAuthError = (err: unknown, code: string): boolean => {
  if (!(err instanceof AppwriteException)) {
    return false;
  }

  const response = typeof err.response === "string" ? err.response : "";

  return err.type === code || err.message === code || response.includes(code);
};

export const isAuthorizationPendingError = (err: unknown): boolean =>
  matchesOAuthError(err, "authorization_pending") ||
  matchesOAuthError(err, "slow_down");

const isSlowDownError = (err: unknown): boolean =>
  matchesOAuthError(err, "slow_down");

// An empty/unrecognized error body during polling (e.g. a 400 with no type,
// message, or response) is treated as a transient pending response rather than
// aborting the device flow.
const isEmptyDevicePollError = (err: unknown): boolean => {
  if (!(err instanceof AppwriteException)) {
    return false;
  }

  const type = typeof err.type === "string" ? err.type.trim() : "";
  const message = typeof err.message === "string" ? err.message.trim() : "";
  const response = typeof err.response === "string" ? err.response.trim() : "";

  return type === "" && message === "" && response === "";
};

/**
 * Poll the token endpoint until the device authorization is approved.
 * Returns the token, or `null` if the authorization window expires.
 * Pending and empty-body responses are retried; `slow_down` additionally
 * increases the polling interval by 5s (RFC 8628 §3.5); any other error throws.
 */
export const pollForDeviceToken = async (
  oauth2: Oauth2,
  deviceAuth: DeviceAuthorization,
  clientId: string,
): Promise<Models.Oauth2Token | null> => {
  const expiresAt = Date.now() + deviceAuth.expires_in * 1000;
  // Default to a 5s interval when the server omits one (RFC 8628 §3.5);
  // an omitted interval would otherwise be NaN and busy-poll the endpoint.
  let intervalMs =
    (Number.isFinite(deviceAuth.interval) && deviceAuth.interval >= 0
      ? deviceAuth.interval
      : 5) * 1000;

  while (Date.now() < expiresAt) {
    await new Promise((resolve) => setTimeout(resolve, intervalMs));

    let token: Models.Oauth2Token;
    try {
      token = await oauth2.createToken({
        grantType: "urn:ietf:params:oauth:grant-type:device_code",
        deviceCode: deviceAuth.device_code,
        clientId,
      });
    } catch (err) {
      if (isAuthorizationPendingError(err) || isEmptyDevicePollError(err)) {
        if (isSlowDownError(err)) {
          intervalMs += 5000;
        }
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
  const oauth2 = await getOauth2Service(
    await sdkForConsole({ requiresAuth: false, endpointOverride: endpoint }),
  );
  await oauth2.revoke({
    token: refreshToken,
    tokenTypeHint: "refresh_token",
    clientId,
  });
};
