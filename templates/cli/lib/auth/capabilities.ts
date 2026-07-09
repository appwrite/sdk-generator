import { EXECUTABLE_NAME } from "../constants.js";
import { globalConfig } from "../config.js";
import { AppwriteException } from "@appwrite.io/console";
import { hasAuthSession } from "./session.js";

export type AuthMode = "session" | "apiKey" | "none";

export const hasApiKey = (): boolean => globalConfig.getKey() !== "";

export const getAuthMode = (): AuthMode => {
  if (hasAuthSession()) {
    return "session";
  }

  if (hasApiKey()) {
    return "apiKey";
  }

  return "none";
};

export const canUseConsole = (): boolean => hasAuthSession();

export const requireProjectAuth = (): void => {
  if (getAuthMode() !== "none") {
    return;
  }

  throw new Error(
    `Authentication not found. Run \`${EXECUTABLE_NAME} login\` or \`${EXECUTABLE_NAME} client --key <API_KEY>\`.`,
  );
};

export const requireConsoleAuth = (action: string): void => {
  if (canUseConsole()) {
    return;
  }

  if (hasApiKey()) {
    throw new Error(
      `${action} requires a console session. API keys work for project commands (e.g. \`${EXECUTABLE_NAME} push functions\`), not console-only commands. Run \`${EXECUTABLE_NAME} login\`.`,
    );
  }

  throw new Error(
    `${action} requires a console session. Run \`${EXECUTABLE_NAME} login\`.`,
  );
};

export const isAuthScopeError = (error: unknown): boolean => {
  if (!(error instanceof Error)) {
    return false;
  }

  const message = error.message.toLowerCase();
  const isScopeMessage =
    message.includes("missing scope") ||
    message.includes("missing scopes") ||
    message.includes("session not found") ||
    message.includes("unauthorized") ||
    message.includes("console session required");

  if (error instanceof AppwriteException) {
    return (
      isScopeMessage ||
      Number(error.code) === 401 ||
      Number(error.code) === 403
    );
  }

  return isScopeMessage;
};
