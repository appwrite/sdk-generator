const fs = require("fs");
const path = require("path");
const net = require("net");
const childProcess = require('child_process');
const { localConfig, globalConfig } = require("./config");
const { success } = require('./parser')

function getAllFiles(folder) {
    const files = [];
    for (const pathDir of fs.readdirSync(folder)) {
        const pathAbsolute = path.join(folder, pathDir);
        if (fs.statSync(pathAbsolute).isDirectory()) {
            files.push(...getAllFiles(pathAbsolute));
        } else {
            files.push(pathAbsolute);
        }
    }
    return files;
}

async function isPortTaken(port) {
    const taken = await new Promise((res, rej) => {
        const tester = net.createServer()
        .once('error', function (err) {
            if (err.code != 'EADDRINUSE') return rej(err)
                res(true)
            })
            .once('listening', function() {
                tester.once('close', function() { res(false) })
                .close()
            })
            .listen(port);
    });

    return taken;
}

function systemHasCommand(command) {
    const isUsingWindows = process.platform == 'win32'

     try {
        if(isUsingWindows) {
            childProcess.execSync('where ' + command, { stdio: 'pipe' })
        } else {
            childProcess.execSync(`[[ $(${command} --version) ]] || { exit 1; } && echo "OK"`, { stdio: 'pipe', shell: '/bin/bash' });
        }
    } catch (error) {
        console.log(error);
        return false;
    }

    return true;
}

const checkDeployConditions = (localConfig) => {
    if (Object.keys(localConfig.data).length === 0) {
        throw new Error("No appwrite.json file found in the current directory. Please run this command again in the folder containing your appwrite.json file, or run 'appwrite init project' to link current directory to an Appwrite project.");
    }
}

function showConsoleLink(serviceName, action, ...ids) {
    const projectId = localConfig.getProject().projectId;

    const url = new URL(globalConfig.getEndpoint().replace('/v1', '/console'));
    url.pathname += `/project-${projectId}`;
    action = action.toLowerCase();

    switch (serviceName) {
        case "account":
            url.pathname = url.pathname.replace(`/project-${projectId}`, '');
            url.pathname += getAccountPath(action);
            break;
        case "databases":
            url.pathname += getDatabasePath(action, ids);
            break;
        case "functions":
            url.pathname += getFunctionsPath(action, ids);
            break;
        case "messaging":
            url.pathname += getMessagingPath(action, ids);
            break;
        case "projects":
            url.pathname = url.pathname.replace(`/project-${projectId}`, '');
            url.pathname += getProjectsPath(action, ids);
            break;
        case "storage":
            url.pathname += getBucketsPath(action, ids);
            break;
        case "teams":
            url.pathname += getTeamsPath(action, ids);
            break;
        case "users":
            url.pathname += getUsersPath(action, ids);
            break;
        default:
            return;
    }


    success(url);
}

function getAccountPath(action) {
    let path = '/account';

    if (action === 'listsessions') {
        path += '/sessions';
    }

    return path;
}

function getDatabasePath(action, ids) {
    let path = '/databases';


    if (['get', 'listcollections', 'getcollection', 'listattributes', 'listdocuments', 'getdocument', 'listindexes', 'getdatabaseusage'].includes(action)) {
        path += `/database-${ids[0]}`;
    }

    if (action === 'getdatabaseusage') {
        path += `/usage`;
    }

    if (['getcollection', 'listattributes', 'listdocuments', 'getdocument', 'listindexes'].includes(action)) {
        path += `/collection-${ids[1]}`;
    }

    if (action === 'listattributes') {
        path += '/attributes';
    }
    if (action === 'listindexes') {
        path += '/indexes';
    }
    if (action === 'getdocument') {
        path += `/document-${ids[2]}`;
    }


    return path;
}

function getFunctionsPath(action, ids) {
    let path = '/functions';

    if (action !== 'list') {
        path += `/function-${ids[0]}`;
    }

    if (action === 'getdeployment') {
        path += `/deployment-${ids[1]}`
    }

    if (action === 'getexecution' || action === 'listexecution') {
        path += `/executions`
    }
    if (action === 'getfunctionusage') {
        path += `/usage`
    }

    return path;
}

function getMessagingPath(action, ids) {
    let path = '/messaging';

    if (['getmessage', 'listmessagelogs'].includes(action)) {
        path += `/message-${ids[0]}`;
    }

    if (['listproviders', 'getprovider'].includes(action)) {
        path += `/providers`;
    }

    if (action === 'getprovider') {
        path += `/provider-${ids[0]}`;
    }

    if (['listtopics', 'gettopic'].includes(action)) {
        path += `/topics`;
    }

    if (action === 'gettopic') {
        path += `/topic-${ids[0]}`;
    }

    return path;
}

function getProjectsPath(action, ids) {
    let path = '';

    if (action !== 'list') {
        path += `/project-${ids[0]}`;
    }

    if (['listkeys', 'getkey'].includes(action)) {
        path += '/overview/keys'
    }

    if (['listplatforms', 'getplatform'].includes(action)) {
        path += '/overview/platforms'
    }

    if (['listwebhooks', 'getwebhook'].includes(action)) {
        path += '/settings/webhooks'
    }

    if (['getplatform', 'getkey', 'getwebhook'].includes(action)) {
        path += `/${ids[1]}`;
    }

    return path;
}

function getBucketsPath(action, ids) {
    let path = '/storage';

    if (action !== 'listbuckets') {
        path += `/bucket-${ids[0]}`;
    }

    if (action === 'getbucketusage') {
        path += `/usage`
    }

    if (action === 'getfile') {
        path += `/file-${ids[1]}`
    }

    return path;
}

function getTeamsPath(action, ids) {
    let path = '/auth/teams';

    if (action !== 'list') {
        path += `/team-${ids[0]}`;
    }

    return path;
}

function getUsersPath(action, ids) {
    let path = '/auth';

    if (action !== 'list') {
        path += `/user-${ids[0]}`;
    }

    if (action === 'listsessions') {
        path += 'sessions';
    }

    return path;
}

module.exports = {
    getAllFiles,
    isPortTaken,
    systemHasCommand,
    checkDeployConditions,
    showConsoleLink
};
