import inquirer from "inquirer";
import { Command } from "commander";
import { AppwriteException, Client } from "@appwrite.io/console";
import { OAUTH2_CLIENT_ID, OAUTH2_SCOPES, sdkForConsole } from "../sdks.js";
import {
  globalConfig,
  localConfig,
  normalizeCloudConsoleEndpoint,
} from "../config.js";
import { EXECUTABLE_NAME } from "../constants.js";
import {
  actionRunner,
  success,
  parseBool,
  commandDescriptions,
  error,
  parse,
  hint,
  warn,
  log,
  drawTable,
  cliConfig,
} from "../parser.js";
import { isCloudHostname } from "../utils.js";
import ID from "../id.js";
import { questionsLogout, questionsSwitchAccount } from "../questions.js";
import { Account, Oauth2, type Models } from "@appwrite.io/console";

const DEFAULT_ENDPOINT = "https://cloud.appwrite.io/v1";

interface AppwriteError {
  type?: string;
  response?: string;
}

const isGuestUnauthorizedError = (err: unknown): err is AppwriteError =>
  (err as AppwriteError)?.type === "general_unauthorized_scope" ||
  (err as AppwriteError)?.response === "general_unauthorized_scope";

const isAuthorizationPendingError = (err: unknown): boolean => {
  if (!(err instanceof AppwriteException)) {
    return false;
  }

  return (
    err.type === "authorization_pending" ||
    err.type === "slow_down" ||
    err.message === "authorization_pending" ||
    err.message === "slow_down" ||
    err.response.includes("authorization_pending") ||
    err.response.includes("slow_down")
  );
};

const decodeIdToken = (idToken: string): { email?: string; name?: string; sub?: string } => {
  try {
    const payload = idToken.split(".")[1];
    if (!payload) return {};
    const decoded = JSON.parse(Buffer.from(payload, "base64url").toString("utf-8"));
    return {
      email: decoded.email,
      name: decoded.name,
      sub: decoded.sub,
    };
  } catch (_error) {
    return {};
  }
};

const isRegionalCloudEndpoint = (endpoint: string): boolean => {
  try {
    const hostname = new URL(endpoint).hostname;
    return isCloudHostname(hostname) && hostname !== "cloud.appwrite.io";
  } catch (_error) {
    return false;
  }
};

const restoreCurrentSession = (sessionId: string): void => {
  globalConfig.setCurrentSession(
    globalConfig.getSessionIds().includes(sessionId) ? sessionId : "",
  );
};

const removeCurrentSession = (): void => {
  const current = globalConfig.getCurrentSession();
  globalConfig.setCurrentSession("");
  globalConfig.removeSession(current);
};

const hasAuthSession = (): boolean => {
  return globalConfig.getAccessToken() !== "" || globalConfig.getCookie() !== "";
};

const getCurrentAccount = async (): Promise<Models.User | null> => {
  if (globalConfig.getEndpoint() === "" || !hasAuthSession()) {
    return null;
  }

  const endpoint = normalizeCloudConsoleEndpoint(globalConfig.getEndpoint());
  if (endpoint !== globalConfig.getEndpoint()) {
    globalConfig.setEndpoint(endpoint);
  }

  const client = await sdkForConsole();
  const accountClient = new Account(client);

  try {
    const account = await accountClient.get();
    globalConfig.setEmail(account.email);
    return account;
  } catch (err) {
    if (isGuestUnauthorizedError(err)) {
      removeCurrentSession();
    }
    return null;
  }
};

const deleteServerSession = async (sessionId: string): Promise<boolean> => {
  const session = globalConfig.get(sessionId) as
    | { refreshToken?: string; endpoint?: string; clientId?: string }
    | undefined;
  if (!session?.refreshToken || !session?.endpoint) {
    return false;
  }

  try {
    const client = new Client()
      .setEndpoint(session.endpoint)
      .setProject("console")
      .setSelfSigned(globalConfig.getSelfSigned());
    const oauth2 = new Oauth2(client);
    await oauth2.revoke({
      projectId: "console",
      token: session.refreshToken,
      tokenTypeHint: "refresh_token",
      clientId: session.clientId || OAUTH2_CLIENT_ID,
    });
    return true;
  } catch (_e) {
    return false;
  }
};

const deleteLocalSession = (accountId: string): void => {
  globalConfig.removeSession(accountId);
};

const getSessionAccountKey = (sessionId: string): string | undefined => {
  const session = globalConfig.get(sessionId) as
    | { email?: string; endpoint?: string }
    | undefined;
  if (!session) return undefined;
  return `${session.email ?? ""}|${normalizeCloudConsoleEndpoint(
    session.endpoint ?? "",
  )}`;
};

const sleep = (ms: number): Promise<void> => {
  return new Promise((resolve) => setTimeout(resolve, ms));
};

const startWaitingForApprovalSpinner = (): (() => void) => {
  const message = "Waiting for approval...";
  const frames = ["⠋", "⠙", "⠹", "⠸", "⠼", "⠴", "⠦", "⠧", "⠇", "⠏"];

  if (!process.stdout.isTTY) {
    console.log(message);
    return () => {};
  }

  let frame = 0;
  process.stdout.write(`${frames[frame]} ${message}`);

  const interval = setInterval(() => {
    frame = (frame + 1) % frames.length;
    process.stdout.write(`\r${frames[frame]} ${message}`);
  }, 80);

  return () => {
    clearInterval(interval);
    process.stdout.write("\r\x1b[K");
  };
};

const isLegacySession = (sessionId: string): boolean => {
  const session = globalConfig.get(sessionId) as
    | { accessToken?: string; cookie?: string }
    | undefined;

  return Boolean(session?.cookie && !session?.accessToken);
};

const removeLegacySessionsExcept = (sessionIdToKeep: string): number => {
  let removed = 0;
  for (const sessionId of globalConfig.getSessionIds()) {
    if (sessionId === sessionIdToKeep || !isLegacySession(sessionId)) {
      continue;
    }

    globalConfig.removeSession(sessionId);
    removed++;
  }

  return removed;
};

/**
 * Given selected session IDs, determine which sessions should be logged out
 * from the server (one per unique account) and which should be removed locally (all sessions for those accounts).
 *
 * @param selectedSessionIds Array of session IDs to process for logout.
 * @returns Object containing `serverTargets` (sessions to logout from server)
 *          and `localTargets` (sessions to remove locally).
 */
const planSessionLogout = (
  selectedSessionIds: string[],
): { serverTargets: string[]; localTargets: string[] } => {
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

  // Sessions to target for server logout: one per unique account
  const serverTargets = Array.from(selectedByAccount.values());
  // Sessions to remove locally: all sessions under selected accounts
  const localTargets = Array.from(selectedByAccount.keys()).flatMap(
    (accountKey) => sessionIdsByAccount.get(accountKey) ?? [],
  );

  return { serverTargets, localTargets };
};

export const loginCommand = async ({
  endpoint,
  switch: switchAccount,
  new: newAccount,
}: {
  endpoint?: string;
  switch?: boolean;
  new?: boolean;
}): Promise<void> => {
  let oldCurrent = globalConfig.getCurrentSession();

  if (switchAccount && newAccount) {
    throw new Error("Use either --switch or --new, not both.");
  }

  if (endpoint && isRegionalCloudEndpoint(endpoint)) {
    throw new Error(
      `Cloud login uses ${DEFAULT_ENDPOINT}. Regional Cloud endpoints are for project API calls, not account login.`,
    );
  }

  const configEndpoint = normalizeCloudConsoleEndpoint(
    (endpoint ?? globalConfig.getEndpoint()) || DEFAULT_ENDPOINT,
  );

  if (globalConfig.getCurrentSession() !== "") {
    const account = await getCurrentAccount();
    oldCurrent = globalConfig.getCurrentSession();

    if (account) {
      if (!globalConfig.getAccessToken() && globalConfig.getCookie()) {
        warn(
          `You are using a legacy cookie session. Run '${EXECUTABLE_NAME} login --new' to switch to the new browser-based login flow.`,
        );
      }

      if (!endpoint && !switchAccount && !newAccount) {
        success("Already logged in as " + account.email);
        hint(`Use '${EXECUTABLE_NAME} login --new' to add another account`);
        return;
      }
    }
  }

  let answers;
  if (switchAccount) {
    if (!globalConfig.getSessions().some((session) => session.email)) {
      throw new Error(
        `No signed-in accounts found. Run '${EXECUTABLE_NAME} login' to sign in.`,
      );
    }
    answers = await inquirer.prompt(questionsSwitchAccount);
  }

  if (switchAccount && answers?.method === "select") {
    const accountId = answers.accountId;

    if (!globalConfig.getSessionIds().includes(accountId)) {
      throw Error("Session ID not found");
    }

    if (accountId === oldCurrent) {
      const account = await getCurrentAccount();
      if (account) {
        success(`Already using ${account.email}`);
        return;
      }
      throw new Error(
        `Selected account session is no longer valid. Run '${EXECUTABLE_NAME} login --switch' again.`,
      );
    }

    globalConfig.setCurrentSession(accountId);
    globalConfig.setEndpoint(
      normalizeCloudConsoleEndpoint(globalConfig.getEndpoint()),
    );

    try {
      await getCurrentAccount();
    } catch (err) {
      if (isGuestUnauthorizedError(err)) {
        globalConfig.removeSession(accountId);
      }
      restoreCurrentSession(oldCurrent);
      throw err;
    }

    success(`Switched to ${globalConfig.getEmail()}`);
    return;
  }

  const id = ID.unique();
  const clientId = OAUTH2_CLIENT_ID;
  const oauth2Client = new Client()
    .setEndpoint(configEndpoint)
    .setProject("console")
    .setSelfSigned(globalConfig.getSelfSigned());
  const oauth2 = new Oauth2(oauth2Client);

  globalConfig.addSession(id, { endpoint: configEndpoint, clientId });
  globalConfig.setCurrentSession(id);
  globalConfig.setEndpoint(configEndpoint);

  let deviceAuth;
  try {
    deviceAuth = await oauth2.createDeviceAuthorization({
      clientId,
      scope: OAUTH2_SCOPES,
    });
  } catch (err) {
    globalConfig.removeSession(id);
    globalConfig.setCurrentSession(oldCurrent);
    throw err;
  }

  console.log("");
  console.log("To sign in, confirm the code below in your browser:");
  console.log("");
  console.log(`  Code: ${deviceAuth.user_code}`);
  console.log(`  URL:  ${deviceAuth.verification_uri_complete || deviceAuth.verification_uri}`);
  console.log("");

  const expiresAt = Date.now() + deviceAuth.expires_in * 1000;
  let token: Models.Oauth2Token | null = null;
  const stopWaitingForApprovalSpinner = startWaitingForApprovalSpinner();

  try {
    while (Date.now() < expiresAt) {
      await sleep(deviceAuth.interval * 1000);

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

        globalConfig.removeSession(id);
        globalConfig.setCurrentSession(oldCurrent);
        throw err;
      }

      if (token) {
        break;
      }
    }
  } finally {
    stopWaitingForApprovalSpinner();
  }

  if (!token || !token.access_token) {
    globalConfig.removeSession(id);
    globalConfig.setCurrentSession(oldCurrent);
    throw new Error("Device authorization timed out or was denied.");
  }

  const tokenExpiry = Date.now() + token.expires_in * 1000;
  globalConfig.setAccessToken(token.access_token);
  globalConfig.setRefreshToken(token.refresh_token || "");
  globalConfig.setTokenExpiry(tokenExpiry);

  let email = "";
  if (token.id_token) {
    const idTokenClaims = decodeIdToken(token.id_token);
    email = idTokenClaims.email || "";
  }

  if (email) {
    globalConfig.setEmail(email);
  }

  let account: Models.User | null = null;
  try {
    account = await getCurrentAccount();
  } catch (_err) {
    // Fallback: account may still be fetched later
  }

  if (account?.email) {
    globalConfig.setEmail(account.email);
  }

  if (!globalConfig.getEmail()) {
    globalConfig.setEmail("unknown");
  }

  const removedLegacySessions = removeLegacySessionsExcept(id);

  success("Successfully signed in as " + globalConfig.getEmail());
  if (removedLegacySessions > 0) {
    hint("Removed legacy cookie session data from this CLI configuration.");
  }
  hint(
    "Next you can create or link to your project using 'appwrite init project'",
  );
};

export const whoami = new Command("whoami")
  .description(commandDescriptions["whoami"])
  .action(
    actionRunner(async () => {
      if (
        globalConfig.getEndpoint() === "" ||
        !hasAuthSession()
      ) {
        error("No user is signed in. To sign in, run 'appwrite login'");
        return;
      }

      const account = await getCurrentAccount();
      if (!account) {
        error("No user is signed in. To sign in, run 'appwrite login'");
        return;
      }

      const data = [
        {
          ID: account.$id,
          Name: account.name,
          Email: account.email,
          "MFA enabled": account.mfa ? "Yes" : "No",
          Endpoint: globalConfig.getEndpoint(),
        },
      ];

      if (cliConfig.json) {
        console.log(data);
        return;
      }

      drawTable(data);
    }),
  );

export const register = new Command("register")
  .description(commandDescriptions["register"])
  .action(
    actionRunner(async () => {
      log("Visit https://cloud.appwrite.io/register to create an account");
    }),
  );

export const login = new Command("login")
  .description(commandDescriptions["login"])
  .option(
    `--endpoint [endpoint]`,
    `Appwrite endpoint for self hosted instances`,
  )
  .option(`--switch`, `Switch to another signed-in account`)
  .option(`--new`, `Sign in to another account`)
  .configureHelp({
    helpWidth: process.stdout.columns || 80,
  })
  .action(actionRunner(loginCommand));

export const logout = new Command("logout")
  .description(commandDescriptions["logout"])
  .configureHelp({
    helpWidth: process.stdout.columns || 80,
  })
  .action(
    actionRunner(async () => {
      const sessions = globalConfig.getSessions();
      const current = globalConfig.getCurrentSession();
      const originalCurrent = current;

      if (current === "" || !sessions.length) {
        log("No active sessions found.");
        return;
      }
      if (sessions.length === 1) {
        // Try to delete from server, then remove locally
        const serverDeleted = await deleteServerSession(current);
        // Remove all local sessions with the same email+endpoint
        const allSessionIds = globalConfig.getSessionIds();
        for (const sessId of allSessionIds) {
          deleteLocalSession(sessId);
        }
        globalConfig.setCurrentSession("");
        if (!serverDeleted) {
          hint("Could not reach server, removed local session data");
        }
        success("Logged out successfully");

        return;
      }

      const answers = await inquirer.prompt(questionsLogout);

      if (answers.accounts?.length) {
        const { serverTargets, localTargets } = planSessionLogout(
          answers.accounts as string[],
        );

        for (const sessionId of serverTargets) {
          globalConfig.setCurrentSession(sessionId);
          await deleteServerSession(sessionId);
        }

        for (const sessionId of localTargets) {
          deleteLocalSession(sessionId);
        }
      }

      const remainingSessions = globalConfig.getSessions();
      const hasCurrent = remainingSessions.some(
        (session) => session.id === originalCurrent,
      );

      if (remainingSessions.length > 0 && hasCurrent) {
        globalConfig.setCurrentSession(originalCurrent);
      } else if (remainingSessions.length > 0) {
        const nextSession =
          remainingSessions.find((session) => session.email) ??
          remainingSessions[0];
        globalConfig.setCurrentSession(nextSession.id);

        success(
          nextSession.email
            ? `Switched to ${nextSession.email}`
            : `Switched to session at ${nextSession.endpoint}`,
        );
      } else if (remainingSessions.length === 0) {
        globalConfig.setCurrentSession("");
      }

      success("Logged out successfully");
    }),
  );

interface ClientCommandOptions {
  selfSigned?: boolean;
  endpoint?: string;
  projectId?: string;
  key?: string;
  debug?: boolean;
  reset?: boolean;
}

export const client = new Command("client")
  .description(commandDescriptions["client"])
  .configureHelp({
    helpWidth: process.stdout.columns || 80,
  })
  .option(
    "-ss, --self-signed <value>",
    "Configure the CLI to use a self-signed certificate ( true or false )",
    parseBool,
  )
  .option("-e, --endpoint <endpoint>", "Set your Appwrite server endpoint")
  .option("-p, --project-id <project-id>", "Set your Appwrite project ID")
  .option("-k, --key <key>", "Set your Appwrite server's API key")
  .option("-d, --debug", "Print CLI debug information")
  .option("-r, --reset", "Reset the CLI configuration")
  .action(
    actionRunner(
      async (
        {
          selfSigned,
          endpoint,
          projectId,
          key,
          debug,
          reset,
        }: ClientCommandOptions,
        command: Command,
      ) => {
        if (
          selfSigned == undefined &&
          endpoint == undefined &&
          projectId == undefined &&
          key == undefined &&
          debug == undefined &&
          reset == undefined
        ) {
          command.help();
        }

        if (debug) {
          const key = globalConfig.getKey();
          const maskedKey =
            key && key.length > 16
              ? `${key.slice(0, 8)}...${key.slice(-8)}`
              : key
                ? "********"
                : "";
          const project = localConfig.getProject();
          const accessToken = globalConfig.getAccessToken();
          const maskedAccessToken = accessToken
            ? `${accessToken.slice(0, 8)}...${accessToken.slice(-8)}`
            : "";
          const config = {
            endpoint: globalConfig.getEndpoint(),
            key: maskedKey,
            accessToken: maskedAccessToken,
            selfSigned: globalConfig.getSelfSigned(),
            projectId: project.projectId ?? "",
            projectName: project.projectName ?? "",
          };
          parse(config);
        }

        if (endpoint !== undefined) {
          try {
            const id = ID.unique();
            const url = new URL(endpoint);
            if (url.protocol !== "http:" && url.protocol !== "https:") {
              throw new Error();
            }

            const clientInstance = new Client().setEndpoint(endpoint);
            clientInstance.setProject("console");
            if (selfSigned || globalConfig.getSelfSigned()) {
              clientInstance.setSelfSigned(true);
            }
            const response = (await clientInstance.call(
              "GET",
              new URL(endpoint + "/health/version"),
            )) as { version?: string };
            if (!response.version) {
              throw new Error();
            }
            globalConfig.setCurrentSession(id);
            globalConfig.addSession(id, { endpoint });
            globalConfig.setEndpoint(endpoint);
          } catch (_) {
            throw new Error(
              "Invalid endpoint or your Appwrite server is not running as expected.",
            );
          }
        }

        if (key !== undefined) {
          if (!globalConfig.getCurrentSession()) {
            throw new Error(
              `Session not found. Please run \`${EXECUTABLE_NAME} client --endpoint <endpoint>\` first.`,
            );
          }
          globalConfig.setKey(key);
        }

        if (projectId !== undefined) {
          localConfig.setProject(projectId, "");
        }

        if (selfSigned == true || selfSigned == false) {
          if (!globalConfig.getCurrentSession()) {
            throw new Error(
              `Session not found. Please run \`${EXECUTABLE_NAME} client --endpoint <endpoint>\` first.`,
            );
          }
          globalConfig.setSelfSigned(selfSigned);
        }

        if (reset !== undefined) {
          for (const sessionId of globalConfig.getSessionIds()) {
            await deleteServerSession(sessionId);
          }

          for (const sessionId of globalConfig.getSessionIds()) {
            deleteLocalSession(sessionId);
          }

          globalConfig.setCurrentSession("");
        }

        if (!debug) {
          success("Setting client");
        }
      },
    ),
  );

export const migrate = async (): Promise<void> => {
  if (!globalConfig.has("endpoint") || !globalConfig.has("cookie")) {
    return;
  }

  const endpoint = globalConfig.get("endpoint") as string;
  const cookie = globalConfig.get("cookie") as string;

  const id = ID.unique();
  const data = {
    endpoint,
    cookie,
    email: "legacy",
  };

  globalConfig.addSession(id, data);
  globalConfig.setCurrentSession(id);
  globalConfig.delete("endpoint");
  globalConfig.delete("cookie");
};
