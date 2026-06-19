import { globalConfig, normalizeCloudConsoleEndpoint } from "../config.js";
import type { SessionData } from "../types.js";
import ClientLegacy from "../client.js";
import { OAUTH2_CLIENT_ID } from "../constants.js";
import { revokeRefreshToken } from "./oauth.js";

/**
 * Typed accessor for a stored session, avoiding repeated inline casts.
 */
export const getSession = (sessionId: string): SessionData | undefined =>
  globalConfig.get(sessionId) as SessionData | undefined;

export const createLegacyConsoleClient = (
  endpoint: string,
  selfSigned: boolean = globalConfig.getSelfSigned(),
): ClientLegacy => {
  const legacyClient = new ClientLegacy();
  legacyClient.setEndpoint(endpoint);
  legacyClient.setProject("console");
  if (selfSigned) {
    legacyClient.setSelfSigned(true);
  }
  return legacyClient;
};

export const hasAuthSession = (): boolean =>
  globalConfig.getAccessToken() !== "" || globalConfig.getCookie() !== "";

/**
 * A session that exists only in local config (no server-side credential to
 * revoke), e.g. an endpoint/key-only entry created by `client --endpoint`.
 */
export const isLocalOnlySession = (sessionId: string): boolean => {
  const session = getSession(sessionId);
  return Boolean(session && !session.refreshToken && !session.cookie);
};

/**
 * A legacy cookie session that predates the OAuth device-login flow.
 */
export const isLegacySession = (sessionId: string): boolean => {
  const session = getSession(sessionId);
  return Boolean(session?.cookie && !session?.accessToken);
};

export const getSessionAccountKey = (sessionId: string): string | undefined => {
  const session = getSession(sessionId);
  if (!session) return undefined;
  return `${session.email ?? ""}|${normalizeCloudConsoleEndpoint(
    session.endpoint ?? "",
  )}`;
};

export const restoreCurrentSession = (sessionId: string): void => {
  globalConfig.setCurrentSession(
    globalConfig.getSessionIds().includes(sessionId) ? sessionId : "",
  );
};

export const restoreCurrentSessionFallback = (
  preferredSessionId: string,
  fallbackSessionIds: string[],
): void => {
  const sessionIds = globalConfig.getSessionIds();
  globalConfig.setCurrentSession(
    [preferredSessionId, ...fallbackSessionIds].find((sessionId) =>
      sessionIds.includes(sessionId),
    ) ?? "",
  );
};

export const removeCurrentSession = (): void => {
  const current = globalConfig.getCurrentSession();
  globalConfig.setCurrentSession("");
  globalConfig.removeSession(current);
};

/**
 * Revoke a session on the server. OAuth sessions revoke their refresh token;
 * legacy cookie sessions delete the current server session. Returns whether
 * the server-side cleanup succeeded.
 */
export const deleteServerSession = async (
  sessionId: string,
): Promise<boolean> => {
  const session = getSession(sessionId);
  if (!session?.endpoint) {
    return false;
  }

  try {
    if (session.refreshToken) {
      await revokeRefreshToken(
        session.endpoint,
        session.refreshToken,
        session.clientId || OAUTH2_CLIENT_ID,
      );
      return true;
    }

    if (session.cookie) {
      // Use the target session's own self-signed setting, not the current
      // session's, so revoking a self-signed legacy session works even when a
      // different (e.g. new OAuth) session is current.
      const legacyClient = createLegacyConsoleClient(
        session.endpoint,
        Boolean(session.selfSigned),
      );
      legacyClient.setCookie(session.cookie);
      await legacyClient.call("DELETE", "/account/sessions/current", {
        "content-type": "application/json",
      });
      return true;
    }

    return false;
  } catch (_e) {
    return false;
  }
};

/**
 * Log out a set of session IDs: local-only sessions are removed locally;
 * the rest are revoked server-side and removed locally on success. Returns
 * the IDs that could not be revoked so callers can restore/warn as needed.
 */
export const logoutSessions = async (
  sessionIds: string[],
): Promise<{ failed: number; failedIds: string[] }> => {
  let failed = 0;
  const failedIds: string[] = [];

  for (const sessionId of sessionIds) {
    if (isLocalOnlySession(sessionId)) {
      globalConfig.removeSession(sessionId);
      continue;
    }

    globalConfig.setCurrentSession(sessionId);
    const serverDeleted = await deleteServerSession(sessionId);
    if (serverDeleted) {
      globalConfig.removeSession(sessionId);
    } else {
      failed++;
      failedIds.push(sessionId);
    }
  }

  return { failed, failedIds };
};

export const removeLegacySessionsExcept = async (
  sessionIdToKeep: string,
): Promise<{ removed: number; failed: number }> => {
  let removed = 0;
  let failed = 0;

  for (const sessionId of globalConfig.getSessionIds()) {
    if (sessionId === sessionIdToKeep || !isLegacySession(sessionId)) {
      continue;
    }

    const serverDeleted = await deleteServerSession(sessionId);
    if (serverDeleted) {
      globalConfig.removeSession(sessionId);
      removed++;
    } else {
      failed++;
    }
  }

  return { removed, failed };
};

/**
 * Given selected session IDs, determine which local sessions belong to the
 * selected accounts and should be individually logged out from the server.
 *
 * @param selectedSessionIds Array of session IDs to process for logout.
 * @returns Session IDs that belong to selected account groups.
 */
export const planSessionLogout = (selectedSessionIds: string[]): string[] => {
  // Map to group all session IDs by their unique account key (email+endpoint)
  const sessionIdsByAccount = new Map<string, string[]>();
  for (const sessionId of globalConfig.getSessionIds()) {
    const key = getSessionAccountKey(sessionId);
    if (!key) continue; // Skip sessions without proper account key

    // For each account key, gather all associated session IDs
    const ids = sessionIdsByAccount.get(key) ?? [];
    ids.push(sessionId);
    sessionIdsByAccount.set(key, ids);
  }

  // Map to store one selected session ID per unique account for server logout
  const selectedByAccount = new Map<string, string>();
  for (const selectedSessionId of selectedSessionIds) {
    const key = getSessionAccountKey(selectedSessionId);
    if (!key || selectedByAccount.has(key)) continue; // Skip if key missing or already considered for this account
    selectedByAccount.set(key, selectedSessionId);
  }

  return Array.from(selectedByAccount.keys()).flatMap(
    (accountKey) => sessionIdsByAccount.get(accountKey) ?? [],
  );
};
