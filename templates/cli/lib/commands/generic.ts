import inquirer from "inquirer";
import { Command } from "commander";
import { Client } from "@appwrite.io/console";
import { sdkForConsole } from "../sdks.js";
import { globalConfig, localConfig } from "../config.js";
import { EXECUTABLE_NAME } from "../constants.js";
import {
  actionRunner,
  success,
  parseBool,
  commandDescriptions,
  error,
  parse,
  hint,
  log,
  drawTable,
  cliConfig,
} from "../parser.js";
import ID from "../id.js";
import {
  questionsLogin,
  questionsLogout,
  questionsListFactors,
  questionsMFAChallenge,
} from "../questions.js";
import { Account, Client as ConsoleClient } from "@appwrite.io/console";
import ClientLegacy from "../client.js";

const DEFAULT_ENDPOINT = "https://cloud.appwrite.io/v1";

interface LoginCommandOptions {
  email?: string;
  password?: string;
  endpoint?: string;
  mfa?: string;
  code?: string;
}

export const loginCommand = async ({
  email,
  password,
  endpoint,
  mfa,
  code,
}: LoginCommandOptions): Promise<void> => {
  const oldCurrent = globalConfig.getCurrentSession();

  const configEndpoint =
    (endpoint ?? globalConfig.getEndpoint()) || DEFAULT_ENDPOINT;

  if (globalConfig.getCurrentSession() !== "") {
    log("You are currently signed in as " + globalConfig.getEmail());

    if (globalConfig.getSessions().length === 1) {
      hint("You can sign in and manage multiple accounts with Appwrite CLI");
    }
  }

  const answers =
    email && password
      ? { email, password }
      : await inquirer.prompt(questionsLogin);

  if (!answers.method) {
    answers.method = "login";
  }

  if (answers.method === "select") {
    const accountId = answers.accountId;

    if (!globalConfig.getSessionIds().includes(accountId)) {
      throw Error("Session ID not found");
    }

    globalConfig.setCurrentSession(accountId);
    success(`Switched to ${globalConfig.getEmail()}`);

    return;
  }

  const id = ID.unique();

  globalConfig.addSession(id, { endpoint: configEndpoint });
  globalConfig.setCurrentSession(id);
  globalConfig.setEndpoint(configEndpoint);
  globalConfig.setEmail(answers.email);

  // Use legacy client for login to extract cookies from response
  const legacyClient = new ClientLegacy();
  legacyClient.setEndpoint(configEndpoint);
  legacyClient.setProject("console");
  if (globalConfig.getSelfSigned()) {
    legacyClient.setSelfSigned(true);
  }

  let client = await sdkForConsole(false);
  let accountClient = new Account(client);

  let account;

  try {
    await legacyClient.call(
      "POST",
      "/account/sessions/email",
      {
        "content-type": "application/json",
      },
      {
        email: answers.email,
        password: answers.password,
      },
    );

    const savedCookie = globalConfig.getCookie();

    if (savedCookie) {
      legacyClient.setCookie(savedCookie);
      client.setCookie(savedCookie);
    }

    accountClient = new Account(client);
    account = await accountClient.get();
  } catch (err: any) {
    if (
      err.type === "user_more_factors_required" ||
      err.response === "user_more_factors_required"
    ) {
      const { factor } = mfa
        ? { factor: mfa }
        : await inquirer.prompt(questionsListFactors);
      const challenge = await accountClient.createMfaChallenge(factor);

      const { otp } = code
        ? { otp: code }
        : await inquirer.prompt(questionsMFAChallenge);
      await legacyClient.call(
        "PUT",
        "/account/mfa/challenges",
        {
          "content-type": "application/json",
        },
        {
          challengeId: challenge.$id,
          otp: otp,
        },
      );

      const savedCookie = globalConfig.getCookie();
      if (savedCookie) {
        client.setCookie(savedCookie);
      }

      accountClient = new Account(client);
      account = await accountClient.get();
    } else {
      globalConfig.removeSession(id);
      globalConfig.setCurrentSession(oldCurrent);
      if (
        endpoint !== DEFAULT_ENDPOINT &&
        (err.type === "user_invalid_credentials" ||
          err.response === "user_invalid_credentials")
      ) {
        log("Use the --endpoint option for self-hosted instances");
      }
      throw err;
    }
  }

  success("Successfully signed in as " + account.email);
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
        globalConfig.getCookie() === ""
      ) {
        error("No user is signed in. To sign in, run 'appwrite login'");
        return;
      }

      let client = await sdkForConsole(false);
      let accountClient = new Account(client);

      let account;

      try {
        account = await accountClient.get();
      } catch (_) {
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
  .option(`--email [email]`, `User email`)
  .option(`--password [password]`, `User password`)
  .option(
    `--endpoint [endpoint]`,
    `Appwrite endpoint for self hosted instances`,
  )
  .option(
    `--mfa [factor]`,
    `Multi-factor authentication login factor: totp, email, phone or recoveryCode`,
  )
  .option(`--code [code]`, `Multi-factor code`)
  .configureHelp({
    helpWidth: process.stdout.columns || 80,
  })
  .action(actionRunner(loginCommand));

const deleteSessionFromServer = async (): Promise<boolean> => {
  try {
    let client = await sdkForConsole();
    let accountClient = new Account(client);
    await accountClient.deleteSession("current");
    return true;
  } catch (e) {
    return false;
  }
};

const removeLocalSession = (accountId: string): void => {
  globalConfig.removeSession(accountId);
};

type SessionData = { email?: string; endpoint?: string };

const getSessionData = (sessionId: string): SessionData | undefined =>
  globalConfig.get(sessionId as any) as SessionData | undefined;

const getSessionAccountKey = (sessionId: string): string | undefined => {
  const session = getSessionData(sessionId);
  if (!session) return undefined;

  return `${session.email ?? ""}|${session.endpoint ?? ""}`;
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
        const serverDeleted = await deleteSessionFromServer();
        // Remove all local sessions with the same email+endpoint
        const allSessionIds = globalConfig.getSessionIds();
        for (const sessId of allSessionIds) {
          removeLocalSession(sessId);
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
          await deleteSessionFromServer();
        }

        for (const sessionId of localTargets) {
          removeLocalSession(sessionId);
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
          const cookie = globalConfig.getCookie();
          let maskedCookie = "";
          if (cookie) {
            const [cookieName, cookieValueAndRest = ""] = cookie.split("=", 2);
            const cookieValue = cookieValueAndRest.split(";")[0] ?? "";
            const tail =
              cookieValue.length > 8
                ? cookieValue.slice(-8)
                : cookieValue || "********";
            maskedCookie = `${cookieName}=...${tail}`;
          }
          let config = {
            endpoint: globalConfig.getEndpoint(),
            key: maskedKey,
            cookie: maskedCookie,
            selfSigned: globalConfig.getSelfSigned(),
            projectId: project.projectId ?? "",
            projectName: project.projectName ?? "",
          };
          parse(config);
        }

        if (endpoint !== undefined) {
          try {
            const id = ID.unique();
            let url = new URL(endpoint);
            if (url.protocol !== "http:" && url.protocol !== "https:") {
              throw new Error();
            }

            let clientInstance = new Client().setEndpoint(endpoint);
            clientInstance.setProject("console");
            if (selfSigned || globalConfig.getSelfSigned()) {
              clientInstance.setSelfSigned(true);
            }
            let response = (await clientInstance.call(
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
          const sessions = globalConfig.getSessions();

          for (const accountId of sessions.map((session) => session.id)) {
            globalConfig.setCurrentSession(accountId);
            await deleteSessionFromServer();
          }

          for (const accountId of globalConfig.getSessionIds()) {
            removeLocalSession(accountId);
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
