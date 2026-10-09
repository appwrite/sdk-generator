'use strict';

// Creates a team-scoped APNs key through the Apple Developer portal with
// @expo/apple-utils, the library eas-cli uses for the same job.
//
// apple-utils asks for the Apple ID, the password and the two-factor code
// itself, so the CLI runs this script with the terminal attached. Its logging
// goes to stdout, so the result is written to the file named by --out instead.

const fs = require('fs');
const { Auth, Keys, InvalidUserCredentialsError } = require('@expo/apple-utils');

function parseArguments(argv) {
  const values = {};
  for (let index = 0; index < argv.length; index += 2) {
    values[argv[index].replace(/^--/, '')] = argv[index + 1];
  }
  if (!values.out || !values.name) {
    throw new Error('usage: helper.js [--team-id <id>] --name <key name> --out <file>');
  }

  return values;
}

function isMaxKeysError(error) {
  return (
    error instanceof Keys.MaxKeysCreatedError ||
    /maximum allowed number of Keys/.test(error?.rawDump?.resultString ?? '')
  );
}

async function createKey({ 'team-id': teamId, name }) {
  // Without a team ID, apple-utils asks which team to use when there are several.
  const authState = await Auth.loginAsync(teamId ? { teamId } : {}, { autoResolveProvider: true });
  const context = authState.context;
  if (teamId && context.teamId !== teamId) {
    throw new Error(`Signed in to Apple team ${context.teamId}, not ${teamId}`);
  }

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

async function main() {
  const options = parseArguments(process.argv.slice(2));
  let result;
  try {
    result = await createKey(options);
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
