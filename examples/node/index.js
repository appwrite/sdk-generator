const Client = require('./lib/client.js');
const AppwriteException = require('./lib/exception.js');
const Account = require('./lib/services/account.js');
const Avatars = require('./lib/services/avatars.js');
const Database = require('./lib/services/database.js');
const Functions = require('./lib/services/functions.js');
const Health = require('./lib/services/health.js');
const Locale = require('./lib/services/locale.js');
const Storage = require('./lib/services/storage.js');
const Teams = require('./lib/services/teams.js');
const Users = require('./lib/services/users.js');

module.exports = {
    Client,
    AppwriteException,
    Account,
    Avatars,
    Database,
    Functions,
    Health,
    Locale,
    Storage,
    Teams,
    Users,
};