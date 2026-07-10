import { globalConfig } from "../config.js";
import { EXECUTABLE_NAME } from "../constants.js";
import type { SessionData } from "../types.js";

const REFRESH_TOKEN_SERVICE = `${EXECUTABLE_NAME}-oauth-refresh-token`;

interface KeyringEntry {
  setPassword(password: string): void;
  getPassword(): string | null;
  deletePassword(): boolean;
}

type KeyringEntryConstructor = new (
  service: string,
  account: string,
) => KeyringEntry;

type KeyringEntryFactory = (service: string, account: string) => KeyringEntry;

// undefined = unresolved; null = resolved but no native binding (use fallback).
let cachedEntry: KeyringEntryConstructor | null | undefined;

// Resolve @napi-rs/keyring lazily via a literal per-platform require: a top-level
// import would crash the `bun --compile` binaries ("Cannot find native binding"),
// whereas a literal specifier lets bun embed the matching .node per --target so
// secure storage still works. Anything unresolved falls back to config storage.
/* eslint-disable @typescript-eslint/no-require-imports */
const loadEntry = (): KeyringEntryConstructor | null => {
  if (cachedEntry !== undefined) {
    return cachedEntry;
  }

  cachedEntry = null;
  if (typeof require !== "function") {
    return cachedEntry;
  }

  try {
    let mod: { Entry: KeyringEntryConstructor } | undefined;
    if (process.platform === "darwin" && process.arch === "arm64") {
      mod = require("@napi-rs/keyring-darwin-arm64");
    } else if (process.platform === "darwin" && process.arch === "x64") {
      mod = require("@napi-rs/keyring-darwin-x64");
    } else if (process.platform === "linux" && process.arch === "x64") {
      mod = require("@napi-rs/keyring-linux-x64-gnu");
    } else if (process.platform === "linux" && process.arch === "arm64") {
      mod = require("@napi-rs/keyring-linux-arm64-gnu");
    } else if (process.platform === "win32" && process.arch === "x64") {
      mod = require("@napi-rs/keyring-win32-x64-msvc");
    } else if (process.platform === "win32" && process.arch === "arm64") {
      mod = require("@napi-rs/keyring-win32-arm64-msvc");
    }

    mod ??= require("@napi-rs/keyring");
    cachedEntry = mod.Entry;
  } catch {
    cachedEntry = null;
  }

  return cachedEntry;
};
/* eslint-enable @typescript-eslint/no-require-imports */

let keyringEntryFactory: KeyringEntryFactory = (service, account) => {
  const Entry = loadEntry();
  if (!Entry) {
    throw new Error("Secure credential storage is unavailable in this build.");
  }

  return new Entry(service, account);
};

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
