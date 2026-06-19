import inquirer from "inquirer";
import { Command } from "commander";
import { Client } from "@appwrite.io/console";
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
import { questionsLogout } from "../questions.js";
import { getCurrentAccount, loginCommand } from "../auth/login.js";
import {
  hasAuthSession,
  logoutSessions,
  planSessionLogout,
  restoreCurrentSessionFallback,
} from "../auth/session.js";

export { loginCommand };

export const whoami = new Command("whoami")
  .description(commandDescriptions["whoami"])
  .action(
    actionRunner(async () => {
      if (globalConfig.getEndpoint() === "" || !hasAuthSession()) {
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
  .option(`--email [email]`, `Email`)
  .option(`--password [password]`, `Password`)
  .option(
    `--mfa [factor]`,
    `Factor used for MFA. Must be one of: email, phone, totp, recoveryCode`,
  )
  .option(`--code [code]`, `Code used for MFA`)
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
        const { failed, failedIds } = await logoutSessions(
          planSessionLogout([current]),
        );

        if (failed > 0) {
          restoreCurrentSessionFallback(originalCurrent, failedIds);
          hint(
            "Could not reach server for all sessions; kept local session data",
          );
        } else {
          globalConfig.setCurrentSession("");
        }
        success("Logged out successfully");

        return;
      }

      const answers = await inquirer.prompt(questionsLogout);

      if (answers.accounts?.length) {
        const { failed } = await logoutSessions(
          planSessionLogout(answers.accounts as string[]),
        );

        if (failed > 0) {
          hint(
            "Could not reach server for all sessions; kept local session data",
          );
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
          const originalCurrent = globalConfig.getCurrentSession();
          const { failed, failedIds } = await logoutSessions(
            globalConfig.getSessionIds(),
          );

          if (failed > 0) {
            restoreCurrentSessionFallback(originalCurrent, failedIds);
            hint(
              "Could not reach server for all sessions; kept local session data",
            );
          } else {
            globalConfig.setCurrentSession("");
          }
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
