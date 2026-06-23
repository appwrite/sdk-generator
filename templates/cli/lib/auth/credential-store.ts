import { globalConfig, normalizeCloudConsoleEndpoint } from "../config.js";
import { EXECUTABLE_NAME } from "../constants.js";
import { warn } from "../parser.js";
import type { SessionData } from "../types.js";

export type OAuthTokenStorage = "secureStore" | "prefsFallback";

const SERVICE = `${EXECUTABLE_NAME}-cli:oauth`;
let secureStoreWarningShown = false;

const getAccount = (sessionId: string, endpoint: string): string =>
  `${normalizeCloudConsoleEndpoint(endpoint)}:${sessionId}:refresh-token`;

const getSession = (sessionId: string): SessionData | undefined =>
  globalConfig.get(sessionId) as SessionData | undefined;

const updateSession = (
  sessionId: string,
  data: Partial<SessionData>,
): void => {
  const session = getSession(sessionId);
  if (!session) {
    return;
  }

  const nextSession = { ...session, ...data };
  if (nextSession.accessToken === "") {
    delete nextSession.accessToken;
  }
  if (nextSession.refreshToken === "") {
    delete nextSession.refreshToken;
  }
  if (nextSession.tokenStorage === "") {
    delete nextSession.tokenStorage;
  }

  globalConfig.addSession(sessionId, nextSession);
};

const warnPrefsFallback = (reason?: string): void => {
  if (secureStoreWarningShown) {
    return;
  }

  secureStoreWarningShown = true;
  const details = reason ? ` ${reason}` : "";
  warn(
    `Could not use the operating system credential store.${details} OAuth refresh tokens will be stored in the CLI prefs file with 0600 permissions.`,
  );
};

const createEntry = async (sessionId: string, endpoint: string) => {
  const { Entry } = await import("@napi-rs/keyring");
  return new Entry(SERVICE, getAccount(sessionId, endpoint));
};

const getSecureRefreshToken = async (
  sessionId: string,
  endpoint: string,
): Promise<string> => {
  try {
    const entry = await createEntry(sessionId, endpoint);
    return (entry.getPassword() ?? "") as string;
  } catch (_error) {
    return "";
  }
};

const setSecureRefreshToken = async (
  sessionId: string,
  endpoint: string,
  refreshToken: string,
): Promise<boolean> => {
  try {
    const entry = await createEntry(sessionId, endpoint);
    entry.setPassword(refreshToken);
    return true;
  } catch (error) {
    warnPrefsFallback(error instanceof Error ? error.message : String(error));
    return false;
  }
};

const deleteSecureRefreshToken = async (
  sessionId: string,
  endpoint: string,
): Promise<void> => {
  try {
    const entry = await createEntry(sessionId, endpoint);
    entry.deletePassword();
  } catch (_error) {
    // Missing/unavailable secure-store entries should not block logout/reset.
  }
};

export const storeOAuthRefreshToken = async (
  sessionId: string,
  endpoint: string,
  refreshToken: string,
): Promise<OAuthTokenStorage> => {
  globalConfig.removeAccessToken();
  if (await setSecureRefreshToken(sessionId, endpoint, refreshToken)) {
    updateSession(sessionId, {
      refreshToken: "",
      tokenStorage: "secureStore",
    });
    return "secureStore";
  }

  updateSession(sessionId, {
    refreshToken,
    tokenStorage: "prefsFallback",
  });
  return "prefsFallback";
};

export const getOAuthRefreshToken = async (
  sessionId: string,
  endpoint: string,
  { migratePrefsToken = true }: { migratePrefsToken?: boolean } = {},
): Promise<{ refreshToken: string; storage: OAuthTokenStorage | "" }> => {
  globalConfig.removeAccessToken();
  const secureRefreshToken = await getSecureRefreshToken(sessionId, endpoint);
  if (secureRefreshToken) {
    if (getSession(sessionId)?.tokenStorage !== "secureStore") {
      updateSession(sessionId, { tokenStorage: "secureStore" });
    }
    return { refreshToken: secureRefreshToken, storage: "secureStore" };
  }

  const prefsRefreshToken = getSession(sessionId)?.refreshToken ?? "";
  if (!prefsRefreshToken) {
    return { refreshToken: "", storage: "" };
  }

  if (!migratePrefsToken) {
    return { refreshToken: prefsRefreshToken, storage: "prefsFallback" };
  }

  const storage = await storeOAuthRefreshToken(
    sessionId,
    endpoint,
    prefsRefreshToken,
  );
  return { refreshToken: prefsRefreshToken, storage };
};

export const deleteOAuthRefreshToken = async (
  sessionId: string,
  endpoint: string,
): Promise<void> => {
  await deleteSecureRefreshToken(sessionId, endpoint);
  globalConfig.removeAccessToken();
  updateSession(sessionId, {
    refreshToken: "",
    tokenStorage: "",
  });
};
