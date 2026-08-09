import inquirer from "inquirer";
import { Command } from "commander";
import { endpointsMatch, globalConfig, localConfig } from "../config.js";
import { configuredOrganizationId } from "../context.js";
import { EXECUTABLE_NAME } from "../constants.js";
import {
  actionRunner,
  success,
  parseBool,
  commandDescriptions,
  error,
  parse,
  log,
  warn,
  drawTable,
  cliConfig,
} from "../parser.js";
import ID from "../id.js";
import { formatAccountList } from "../utils.js";
import { questionsClientReset, questionsLogout } from "../questions.js";
import { getCurrentAccount, loginCommand } from "../auth/login.js";
import {
  findSessionForEndpoint,
  getSession,
  getSignedInAccounts,
  hasAuthSession,
  isAuthenticatedSession,
  logoutSessions,
  planSessionLogout,
  restoreCurrentSessionFallback,
  verifyEndpoint,
} from "../auth/session.js";

export { loginCommand };

const logMessages = {
  noActiveSessions: "No active sessions found.",
  logoutFailure: (errors: string[] = []): string => {
    const uniqueErrors = [...new Set(errors)];
    const details = uniqueErrors.length ? `: ${uniqueErrors.join("; ")}` : "";
    return `Could not log out because the server session could not be revoked${details}. Kept local session data.`;
  },
  logoutSuccess: "Logged out successfully",
  clientConfigUpdated: "Client configuration updated",
  noUserSignedIn: "No user is signed in. To sign in, run 'appwrite login'",
} as const;

const hintSignedInAccounts = (): void => {
  if (cliConfig.json || cliConfig.raw) {
    return;
  }

  const accounts = getSignedInAccounts();
  if (accounts.length === 0) {
    return;
  }

  log("Signed-in accounts are still available:");
  for (const account of accounts) {
    log(`  ${account.email} (${account.endpoint})`);
  }
  log(`Run '${EXECUTABLE_NAME} login --switch' to select one.`);
};

const warnDetachedAuthenticatedSession = (previousSessionId: string): void => {
  if (!previousSessionId || !isAuthenticatedSession(previousSessionId)) {
    return;
  }

  const previous = getSession(previousSessionId);
  const email = previous?.email ? ` (${previous.email})` : "";
  warn(
    `Signed-in account${email} is still available but no longer active. Run '${EXECUTABLE_NAME} login --switch' to return to it.`,
  );
};

export const whoami = new Command("whoami")
  .description(commandDescriptions["whoami"])
  .action(
    actionRunner(async () => {
      if (globalConfig.getEndpoint() === "" || !hasAuthSession()) {
        error(logMessages.noUserSignedIn);
        hintSignedInAccounts();
        return;
      }

      const account = await getCurrentAccount();
      if (!account) {
        error(logMessages.noUserSignedIn);
        hintSignedInAccounts();
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
  .option(`--email [email]`, `Email, for self hosted instances`)
  .option(`--password [password]`, `Password, for self hosted instances`)
  .option(
    `--mfa [factor]`,
    `Factor used for MFA on self hosted instances. Must be one of: email, phone, totp, recoveryCode`,
  )
  .option(`--code [code]`, `Code used for MFA on self hosted instances`)
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

      // The picker only offers sessions with an email, so count those — counting
      // endpoint-only entries would open a checkbox with no selectable options.
      const accounts = sessions.filter((session) => session.email);

      if (current === "" || !accounts.length) {
        log(logMessages.noActiveSessions);
        return;
      }
      if (accounts.length === 1) {
        const { failed, failedIds, errors } = await logoutSessions(
          planSessionLogout([accounts[0].id]),
        );

        if (failed > 0) {
          restoreCurrentSessionFallback(originalCurrent, failedIds);
          error(logMessages.logoutFailure(errors));
          return;
        } else {
          globalConfig.setCurrentSession("");
        }
        success(logMessages.logoutSuccess);

        return;
      }

      const answers = await inquirer.prompt(questionsLogout);
      let logoutFailed = false;

      if (answers.accounts?.length) {
        const { failed, errors } = await logoutSessions(
          planSessionLogout(answers.accounts as string[]),
        );

        if (failed > 0) {
          logoutFailed = true;
          error(logMessages.logoutFailure(errors));
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

        if (!logoutFailed && nextSession.email) {
          success(`Switched to ${nextSession.email}`);
        }
      } else if (remainingSessions.length === 0) {
        globalConfig.setCurrentSession("");
      }

      if (!logoutFailed) {
        success(logMessages.logoutSuccess);
      }
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
            organizationId: configuredOrganizationId(),
            projectId: project.projectId ?? "",
            projectName: project.projectName ?? "",
          };
          parse(config);
        }

        if (endpoint !== undefined) {
          await verifyEndpoint(
            endpoint,
            selfSigned || globalConfig.getSelfSigned(),
          );

          const previous = globalConfig.getCurrentSession();
          const match = findSessionForEndpoint(endpoint);

          if (
            previous &&
            endpointsMatch(getSession(previous)?.endpoint ?? "", endpoint) &&
            (isAuthenticatedSession(previous) || !match.authenticated)
          ) {
            // Already on the best available session for this endpoint — keep
            // current and refresh the stored value so regional hosts stay as
            // requested.
            globalConfig.setEndpoint(endpoint);
          } else if (match.authenticated) {
            globalConfig.setCurrentSession(match.authenticated);
            globalConfig.setEndpoint(endpoint);
            const email = getSession(match.authenticated)?.email;
            if (email) {
              log(`Using signed-in account ${email}`);
            }
          } else if (match.endpointOnly) {
            globalConfig.setCurrentSession(match.endpointOnly);
            globalConfig.setEndpoint(endpoint);
            warnDetachedAuthenticatedSession(previous);
          } else if (previous && !isAuthenticatedSession(previous)) {
            // Update an existing endpoint-only stub in place.
            globalConfig.setEndpoint(endpoint);
          } else {
            const id = ID.unique();
            globalConfig.addSession(id, { endpoint });
            globalConfig.setCurrentSession(id);
            globalConfig.setEndpoint(endpoint);
            warnDetachedAuthenticatedSession(previous);
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
          const accounts = getSignedInAccounts();
          if (accounts.length > 0 && !cliConfig.force) {
            if (!process.stdin.isTTY) {
              throw new Error(
                `Resetting will sign out:\n${formatAccountList(accounts)}\nRe-run with --force to confirm.`,
              );
            }

            const answers = await inquirer.prompt(
              questionsClientReset(accounts),
            );
            if (!answers.confirm) {
              log("Reset cancelled.");
              return;
            }
          }

          const originalCurrent = globalConfig.getCurrentSession();
          const { failed, failedIds, errors } = await logoutSessions(
            globalConfig.getSessionIds(),
          );

          if (failed > 0) {
            restoreCurrentSessionFallback(originalCurrent, failedIds);
            error(logMessages.logoutFailure(errors));
            return;
          } else {
            globalConfig.setCurrentSession("");
          }
        }

        if (!debug) {
          success(logMessages.clientConfigUpdated);
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
