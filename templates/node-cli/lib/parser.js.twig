const chalk = require('chalk');
const { description } = require('../package.json')
var Table = require('cli-table3');

const parse = (data) => {
    if (data.hasOwnProperty('sum')) {
        for (let key in data) {
            if (Array.isArray(data[key]) && typeof data[key] === 'object') {
                drawTable(data[key]);
            } else if (typeof data[key] === 'object') {
                parse(data[key]);
            } else {
                console.log(key + ': ' + data[key]);
            }
        }
    } else {
        drawJSON(data);
    }
}

const drawTable = (data) => {
    if (data.length == 0) {
        return;
    }

    let columns = Object.keys(data[0]);
    let table = new Table({
        head: columns.map(c => chalk.cyan.italic.bold(c)),
        chars: {
            'top': ' ',
            'top-mid': ' ',
            'top-left': ' ',
            'top-right': ' ',
            'bottom': ' ',
            'bottom-mid': ' ',
            'bottom-left': ' ',
            'bottom-right': ' ',
            'left': ' ',
            'left-mid': ' ',
            'mid': chalk.cyan('─'),
            'mid-mid': chalk.cyan('┼'),
            'right': ' ',
            'right-mid': ' ',
            'middle': chalk.cyan('│')
        }
    });

    data.forEach(row => {
        let rowValues = [];
        for (let key in row) {
            if (Array.isArray(row[key])) {
                rowValues.push(`array(${row[key].length})`);
            } else if (typeof row[key] === 'object') {
                rowValues.push("object");
            } else if (!row[key]) {
                rowValues.push("---");
            } else {
                rowValues.push(row[key]);
            }
        }
        table.push(rowValues);
    });
    console.log(table.toString());
}

const drawJSON = (data) => {
    console.log(JSON.stringify(data, null, 2));
}

const parseError = (err) => {
    error(err.message)
    process.exit(1)
}

const actionRunner = (fn) => {
    return (...args) => fn(...args).catch(parseError);
}

const log = (message) => {
    console.log(`${chalk.cyan.bold("ℹ Info")} ${chalk.cyan(message ?? "")}`);
}

const success = (message) => {
    console.log(`${chalk.green.bold("✓ Success")} ${chalk.green(message ?? "")}`);
}

const error = (message) => {
    console.error(`${chalk.red.bold("✗ Error")} ${chalk.red(message ?? "")}`);
}

const logo = "\n" +
    "    _                            _ _           ___   __   _____ \n" +
    "   /_\\  _ __  _ ____      ___ __(_) |_ ___    / __\\ / /   \\_   \\\n" +
    "  //_\\\\| '_ \\| '_ \\ \\ /\\ / / '__| | __/ _ \\  / /   / /     / /\\/\n" +
    " /  _  \\ |_) | |_) \\ V  V /| |  | | ||  __/ / /___/ /___/\\/ /_  \n" +
    " \\_/ \\_/ .__/| .__/ \\_/\\_/ |_|  |_|\\__\\___| \\____/\\____/\\____/  \n" +
    "       |_|   |_|                                                  \n" +
    "\n";

const commandDescriptions = {
    "account": `The account command allows you to authenticate and manage a user account.`,
    "avatars": `The avatars command aims to help you complete everyday tasks related to your app image, icons, and avatars.`,
    "database": `The database command allows you to create structured collections of documents, query and filter lists of documents.`,
    "deploy": `The deploy command provides a convenient wrapper for deploying your functions and collections.`,
    "functions": `The functions command allows you view, create and manage your Cloud Functions.`,
    "health": `The health command allows you to both validate and monitor your Appwrite server's health.`,
    "init": `The init command helps you initialize your Appwrite project, functions and collections`,
    "locale": `The locale command allows you to customize your app based on your users' location.`,
    "projects": `The projects command allows you to view, create and manage your Appwrite projects.`,
    "storage": `The storage command allows you to manage your project files.`,
    "teams": `The teams command allows you to group users of your project and to enable them to share read and write access to your project resources`,
    "users": `The users command allows you to manage your project users.`,
    "client": `The client command allows you to configure your CLI`,
    "login": `The login command allows you to authenticate and manage a user account.`,
    "logout": `The logout command allows you to logout of your Appwrite account.`,
    "main": chalk.redBright(`${logo}${description}`),
}

module.exports = {
    parse,
    actionRunner,
    log,
    success,
    error,
    commandDescriptions
}