'use strict';

// Creates a team-scoped APNs key, or prepares the app's App ID, through the
// Apple Developer portal with @expo/apple-utils, the library eas-cli uses for
// the same jobs.
//
// apple-utils asks for the Apple ID, the password and the two-factor code
// itself, so the CLI runs this script with the terminal attached. Its logging
// goes to stdout, so the result is written to the file named by --out instead.

const fs = require('fs');
const {
  Auth,
  BundleId,
  CapabilityType,
  CapabilityTypeOption,
  InvalidUserCredentialsError,
  Keys,
} = require('@expo/apple-utils');

function parseArguments(argv) {
  const values = {};
  for (let index = 0; index < argv.length; index += 2) {
    values[argv[index].replace(/^--/, '')] = argv[index + 1];
  }
  if (!values.out || !values.name) {
    throw new Error(
      'usage: helper.js [--action app-id --bundle-id <id> --mode check|apply [--saved-only true]] [--team-id <id>] [--reset true] --name <name> --out <file>'
    );
  }

  return values;
}

// Apple words a full team as "maximum allowed number of Keys" or, since 2026,
// "maximum allowed number of team scoped Keys ...", which apple-utils does not
// turn into MaxKeysCreatedError, so the message itself is checked too.
const maxKeysMessage = /maximum allowed number of (?:[a-z ]+ )?keys/i;

function isMaxKeysError(error) {
  return (
    error instanceof Keys.MaxKeysCreatedError ||
    maxKeysMessage.test(error?.rawDump?.resultString ?? '') ||
    maxKeysMessage.test(error?.message ?? '')
  );
}

// signIn returns the request context, or null when --saved-only finds no
// saved sign-in for EXPO_APPLE_ID.
async function signIn({ 'team-id': teamId, reset, 'saved-only': savedOnly }) {
  // --reset deletes apple-utils' saved session for this Apple ID first, so the
  // sign-in starts over, two-factor included.
  if (reset === 'true') {
    await Auth.logoutAsync({});
  }
  let authState;
  if (savedOnly === 'true') {
    authState = await Auth.tryRestoringAuthStateFromUserCredentialsAsync(
      { username: process.env.EXPO_APPLE_ID, teamId },
      { autoResolveProvider: true }
    );
    if (!authState) {
      return null;
    }
  } else {
    // Without a team ID, apple-utils asks which team to use when there are several.
    authState = await Auth.loginAsync(teamId ? { teamId } : {}, { autoResolveProvider: true });
  }
  const context = authState.context;
  if (teamId && context.teamId !== teamId) {
    throw new Error(`Signed in to Apple team ${context.teamId}, not ${teamId}`);
  }

  return context;
}

async function createKey(context, { name }) {
  let key;
  try {
    key = await Keys.createKeyAsync(context, { name, isApns: true });
  } catch (error) {
    if (!isMaxKeysError(error)) {
      throw error;
    }
    const keys = await Keys.getKeysAsync(context);

    return {
      error: 'max-keys',
      keys: keys.map(({ id, name, canRevoke }) => ({ id, name, canRevoke })),
    };
  }
  const p8 = await Keys.downloadKeyAsync(context, { id: key.id });

  return { keyId: key.id, teamId: context.teamId, p8 };
}

// prepareAppId reports whether --bundle-id is an App ID with Push
// Notifications, and with --mode apply registers it and turns push on.
async function prepareAppId(context, { 'bundle-id': identifier, name, mode }) {
  let bundleId = await BundleId.findAsync(context, { identifier });
  const result = {
    teamId: context.teamId,
    registered: Boolean(bundleId),
    push: false,
    created: false,
    pushTurnedOn: false,
  };
  if (bundleId) {
    result.push = Boolean(await bundleId.hasCapabilityAsync(CapabilityType.PUSH_NOTIFICATIONS));
  }
  if (mode !== 'apply') {
    return result;
  }
  if (!bundleId) {
    bundleId = await BundleId.createAsync(context, { name, identifier });
    result.registered = true;
    result.created = true;
  }
  if (!result.push) {
    await bundleId.updateBundleIdCapabilityAsync({
      capabilityType: CapabilityType.PUSH_NOTIFICATIONS,
      option: CapabilityTypeOption.ON,
    });
    result.push = true;
    result.pushTurnedOn = true;
  }

  return result;
}

async function run(options) {
  const context = await signIn(options);
  if (!context) {
    return { error: 'sign-in-needed' };
  }

  return options.action === 'app-id' ? prepareAppId(context, options) : createKey(context, options);
}

async function main() {
  const options = parseArguments(process.argv.slice(2));
  let result;
  try {
    result = await run(options);
  } catch (error) {
    const kind = error instanceof InvalidUserCredentialsError ? 'invalid-credentials' : 'failed';
    result = { error: kind, message: error?.message ?? String(error) };
  }
  fs.writeFileSync(options.out, JSON.stringify(result), { mode: 0o600 });
  if (result.error) {
    process.exitCode = 1;
  }
}

main().catch(error => {
  console.error(error?.message ?? error);
  process.exitCode = 1;
});
