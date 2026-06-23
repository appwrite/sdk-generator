import { Entry } from "@napi-rs/keyring";
import { globalConfig } from "../config.js";
import { EXECUTABLE_NAME } from "../constants.js";
import type { SessionData } from "../types.js";

const REFRESH_TOKEN_SERVICE = `${EXECUTABLE_NAME}-oauth-refresh-token`;

interface KeyringEntry {
  setPassword(password: string): void;
  getPassword(): string | null;
  deletePassword(): boolean;
}

type KeyringEntryFactory = (service: string, account: string) => KeyringEntry;

let keyringEntryFactory: KeyringEntryFactory = (service, account) =>
  new Entry(service, account);

const getSessionData = (sessionId: string): SessionData | undefined =>
  globalConfig.get(sessionId) as SessionData | undefined;

const setSessionData = (sessionId: string, data: SessionData): void => {
  globalConfig.addSession(sessionId, data);
};

const setPrefsRefreshToken = (sessionId: string, refreshToken: string): void => {
  const session = getSessionData(sessionId);
  if (!session) return;

  setSessionData(sessionId, {
    ...session,
    refreshToken,
  });
};

export const deletePrefsRefreshToken = (sessionId: string): void => {
  const session = getSessionData(sessionId);
  if (!session?.refreshToken) return;

  const { refreshToken: _refreshToken, ...rest } = session;
  setSessionData(sessionId, rest);
};

export const setRefreshTokenEntryFactoryForTests = (
  factory: KeyringEntryFactory,
): (() => void) => {
  if (process.env.NODE_ENV !== "test") {
    throw new Error("setRefreshTokenEntryFactoryForTests is for tests only");
  }

  const previousFactory = keyringEntryFactory;
  keyringEntryFactory = factory;
  return () => {
    keyringEntryFactory = previousFactory;
  };
};

export const getStoredRefreshToken = (sessionId: string): string => {
  try {
    const refreshToken = keyringEntryFactory(
      REFRESH_TOKEN_SERVICE,
      sessionId,
    ).getPassword();

    if (refreshToken) {
      return refreshToken;
    }
  } catch (_error) {
    // Fall through to prefs fallback below.
  }

  return getSessionData(sessionId)?.refreshToken ?? "";
};

export const hasStoredRefreshToken = (sessionId: string): boolean =>
  getStoredRefreshToken(sessionId) !== "";

export const setStoredRefreshToken = (
  sessionId: string,
  refreshToken: string,
): void => {
  if (!refreshToken) {
    deleteStoredRefreshToken(sessionId);
    return;
  }

  try {
    keyringEntryFactory(REFRESH_TOKEN_SERVICE, sessionId).setPassword(
      refreshToken,
    );
    deletePrefsRefreshToken(sessionId);
  } catch (_error) {
    setPrefsRefreshToken(sessionId, refreshToken);
  }
};

export const deleteStoredRefreshToken = (sessionId: string): void => {
  try {
    keyringEntryFactory(REFRESH_TOKEN_SERVICE, sessionId).deletePassword();
  } catch (_error) {
    // Missing or unavailable secure storage must not block local cleanup.
  }

  deletePrefsRefreshToken(sessionId);
};
