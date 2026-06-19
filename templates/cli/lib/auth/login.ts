import inquirer from "inquirer";
import { Account, type Models } from "@appwrite.io/console";
import { sdkForConsole } from "../sdks.js";
import { globalConfig, normalizeCloudConsoleEndpoint } from "../config.js";
import {
  EXECUTABLE_NAME,
  OAUTH2_CLIENT_ID,
  OAUTH2_SCOPES,
} from "../constants.js";
import { success, hint, warn, log } from "../parser.js";
import { isCloudLoginEndpoint, isRegionalCloudEndpoint } from "../utils.js";
import { isFlagEnabled } from "../flags.js";
import ID from "../id.js";
import {
  questionsListFactors,
  questionsLogin,
  questionsMFAChallenge,
  questionsSwitchAccount,
} from "../questions.js";
import ClientLegacy from "../client.js";
import { decodeIdToken, pollForDeviceToken } from "./oauth.js";
import { getOauth2Service } from "../services.js";
import {
  createLegacyConsoleClient,
  hasAuthSession,
  removeCurrentSession,
  removeLegacySessionsExcept,
  restoreCurrentSession,
  deleteServerSession,
} from "./session.js";

const DEFAULT_ENDPOINT = "https://cloud.appwrite.io/v1";

interface AppwriteError {
  type?: string;
  response?: string;
}

const isGuestUnauthorizedError = (err: unknown): err is AppwriteError =>
  (err as AppwriteError)?.type === "general_unauthorized_scope" ||
  (err as AppwriteError)?.response === "general_unauthorized_scope";

const isMfaRequiredError = (err: unknown): err is AppwriteError =>
  (err as AppwriteError)?.type === "user_more_factors_required" ||
  (err as AppwriteError)?.response === "user_more_factors_required";

const startWaitingForApprovalSpinner = (): (() => void) => {
  const message = "Waiting for approval...";
  const frames = ["⠋", "⠙", "⠹", "⠸", "⠼", "⠴", "⠦", "⠧", "⠇", "⠏"];

  if (!process.stdout.isTTY) {
    process.stdout.write(`${message}\n`);
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

export const getCurrentAccount = async (): Promise<Models.User | null> => {
  if (globalConfig.getEndpoint() === "" || !hasAuthSession()) {
    return null;
  }

  // sdkForConsole normalizes the endpoint when building the console client, so
  // we must not persist it back into the session here — that would overwrite a
  // regional Cloud endpoint and route later project calls to the generic host.
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

const completeMfaLogin = async ({
  client,
  legacyClient,
  mfa,
  code,
}: {
  client: Awaited<ReturnType<typeof sdkForConsole>>;
  legacyClient: ClientLegacy;
  mfa?: string;
  code?: string;
}): Promise<Models.User> => {
  let accountClient = new Account(client);

  const savedCookie = globalConfig.getCookie();
  if (savedCookie) {
    legacyClient.setCookie(savedCookie);
    client.setCookie(savedCookie);
  }

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
      otp,
    },
  );

  const updatedCookie = globalConfig.getCookie();
  if (updatedCookie) {
    client.setCookie(updatedCookie);
  }

  accountClient = new Account(client);
  return accountClient.get();
};

const loginWithEmailPassword = async ({
  id,
  oldCurrent,
  configEndpoint,
  email,
  password,
  endpoint,
  mfa,
  code,
}: {
  id: string;
  oldCurrent: string;
  configEndpoint: string;
  email: string;
  password: string;
  endpoint?: string;
  mfa?: string;
  code?: string;
}): Promise<void> => {
  globalConfig.addSession(id, { endpoint: configEndpoint });
  globalConfig.setCurrentSession(id);
  globalConfig.setEndpoint(configEndpoint);
  globalConfig.setEmail(email);

  const legacyClient = createLegacyConsoleClient(configEndpoint);
  const client = await sdkForConsole({ requiresAuth: false });
  let accountClient = new Account(client);
  let account: Models.User;

  try {
    await legacyClient.call(
      "POST",
      "/account/sessions/email",
      {
        "content-type": "application/json",
      },
      { email, password },
    );

    const savedCookie = globalConfig.getCookie();
    if (savedCookie) {
      legacyClient.setCookie(savedCookie);
      client.setCookie(savedCookie);
    }

    accountClient = new Account(client);
    account = await accountClient.get();
  } catch (err) {
    if (isMfaRequiredError(err)) {
      try {
        account = await completeMfaLogin({
          client,
          legacyClient,
          mfa,
          code,
        });
      } catch (mfaErr) {
        globalConfig.removeSession(id);
        restoreCurrentSession(oldCurrent);
        throw mfaErr;
      }
    } else {
      globalConfig.removeSession(id);
      globalConfig.setCurrentSession(oldCurrent);

      if (
        endpoint !== DEFAULT_ENDPOINT &&
        ((err as AppwriteError)?.type === "user_invalid_credentials" ||
          (err as AppwriteError)?.response === "user_invalid_credentials")
      ) {
        log("Use the --endpoint option for self-hosted instances");
      }

      throw err;
    }
  }

  globalConfig.setEmail(account.email);
  success("Successfully signed in as " + account.email);
  hint(
    "Next you can create or link to your project using 'appwrite init project'",
  );
};

const switchToAccount = async ({
  oldCurrent,
  accountId,
}: {
  oldCurrent: string;
  accountId: string;
}): Promise<void> => {
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

  let account: Models.User | null = null;
  try {
    account = await getCurrentAccount();
  } catch (err) {
    restoreCurrentSession(oldCurrent);
    throw err;
  }

  if (!account) {
    restoreCurrentSession(oldCurrent);
    throw new Error(
      `Selected account session is no longer valid. Run '${EXECUTABLE_NAME} login --switch' again.`,
    );
  }

  success(`Switched to ${account.email}`);
};

const loginWithOAuthDevice = async ({
  id,
  oldCurrent,
  configEndpoint,
}: {
  id: string;
  oldCurrent: string;
  configEndpoint: string;
}): Promise<void> => {
  const clientId = OAUTH2_CLIENT_ID;
  const oauth2 = await getOauth2Service(
    await sdkForConsole({ requiresAuth: false, endpointOverride: configEndpoint }),
  );

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

  const verificationUri =
    deviceAuth.verification_uri_complete || deviceAuth.verification_uri;
  process.stdout.write(
    "\nTo sign in, confirm the code below in your browser:\n\n" +
      `  Code: ${deviceAuth.user_code}\n` +
      `  URL:  ${verificationUri}\n\n`,
  );

  let token: Models.Oauth2Token | null = null;
  const stopWaitingForApprovalSpinner = startWaitingForApprovalSpinner();

  try {
    token = await pollForDeviceToken(oauth2, deviceAuth, clientId);
  } catch (err) {
    globalConfig.removeSession(id);
    globalConfig.setCurrentSession(oldCurrent);
    throw err;
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

  let tokenEmail = "";
  if (token.id_token) {
    tokenEmail = decodeIdToken(token.id_token).email || "";
  }

  if (tokenEmail) {
    globalConfig.setEmail(tokenEmail);
  }

  let account: Models.User | null = null;
  try {
    account = await getCurrentAccount();
    if (!account?.email) {
      throw new Error("Unable to verify the new session.");
    }
  } catch (err) {
    await deleteServerSession(id);
    globalConfig.removeSession(id);
    restoreCurrentSession(oldCurrent);
    throw err;
  }

  globalConfig.setEmail(account.email);

  const { removed: removedLegacySessions, failed: failedLegacySessions } =
    await removeLegacySessionsExcept(id);

  success("Successfully signed in as " + globalConfig.getEmail());
  if (removedLegacySessions > 0) {
    hint("Removed legacy cookie session data from this CLI configuration.");
  }
  if (failedLegacySessions > 0) {
    warn("Could not revoke all legacy sessions; kept them in local config.");
  }
  hint(
    "Next you can create or link to your project using 'appwrite init project'",
  );
};

export const loginCommand = async ({
  email,
  password,
  endpoint,
  mfa,
  code,
  switch: switchAccount,
  new: newAccount,
}: {
  email?: string;
  password?: string;
  endpoint?: string;
  mfa?: string;
  code?: string;
  switch?: boolean;
  new?: boolean;
}): Promise<void> => {
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
  const shouldUseCloudLogin =
    isFlagEnabled("oauthLogin") && isCloudLoginEndpoint(configEndpoint);

  let oldCurrent = globalConfig.getCurrentSession();

  if (oldCurrent !== "" && !newAccount) {
    let account: Models.User | null = null;
    try {
      account = await getCurrentAccount();
    } catch (_err) {
      account = null;
    }
    // Refresh the current session after account lookup.
    oldCurrent = globalConfig.getCurrentSession();

    if (account) {
      if (
        isFlagEnabled("oauthLogin") &&
        !globalConfig.getAccessToken() &&
        globalConfig.getCookie()
      ) {
        warn(
          `You are using a legacy cookie session. Run '${EXECUTABLE_NAME} login --new' to switch to the new browser-based login flow.`,
        );
      }

      if (!email && !password && !endpoint && !switchAccount && !newAccount) {
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
  } else if (!shouldUseCloudLogin) {
    answers =
      email && password
        ? { email, password }
        : await inquirer.prompt(questionsLogin);
  }

  if (switchAccount && answers?.accountId) {
    await switchToAccount({ oldCurrent, accountId: answers.accountId });
    return;
  }

  const id = ID.unique();

  if (!shouldUseCloudLogin) {
    await loginWithEmailPassword({
      id,
      oldCurrent,
      configEndpoint,
      email: answers.email,
      password: answers.password,
      endpoint,
      mfa,
      code,
    });
    return;
  }

  await loginWithOAuthDevice({ id, oldCurrent, configEndpoint });
};
