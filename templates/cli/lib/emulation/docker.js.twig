const childProcess = require('child_process');
const { localConfig } = require("../config");
const path = require('path');
const fs = require('fs');
const { log,success } = require("../parser");
const { openRuntimesVersion, systemTools } = require("./utils");
const ID = require("../id");

const activeDockerIds = {};

async function dockerStop(id) {
    delete activeDockerIds[id];
    const stopProcess = childProcess.spawn('docker', ['rm', '--force', id], {
        stdio: 'pipe',
    });

    await new Promise((res) => { stopProcess.on('close', res) });
}

async function dockerPull(func) {
    log('Pulling Docker image of function runtime ...');

    const runtimeChunks = func.runtime.split("-");
    const runtimeVersion = runtimeChunks.pop();
    const runtimeName = runtimeChunks.join("-");
    const imageName = `openruntimes/${runtimeName}:${openRuntimesVersion}-${runtimeVersion}`;

    const pullProcess = childProcess.spawn('docker', ['pull', imageName], {
        stdio: 'pipe',
        pwd: path.join(process.cwd(), func.path)
    });

    pullProcess.stderr.on('data', (data) => {
        process.stderr.write(`\n${data}$ `);
    });

    await new Promise((res) => { pullProcess.on('close', res) });
}

async function dockerBuild(func, variables) {
    log('Building function using Docker ...');

    const runtimeChunks = func.runtime.split("-");
    const runtimeVersion = runtimeChunks.pop();
    const runtimeName = runtimeChunks.join("-");
    const imageName = `openruntimes/${runtimeName}:${openRuntimesVersion}-${runtimeVersion}`;

    const functionDir = path.join(process.cwd(), func.path);

    const id = ID.unique();

    const params = [ 'run' ];
    params.push('--name', id);
    params.push('-v', `${functionDir}/:/mnt/code:rw`);
    params.push('-e', 'APPWRITE_ENV=development');
    params.push('-e', 'OPEN_RUNTIMES_ENV=development');
    params.push('-e', 'OPEN_RUNTIMES_SECRET=');
    params.push('-e', `OPEN_RUNTIMES_ENTRYPOINT=${func.entrypoint}`);

    for(const k of Object.keys(variables)) {
        params.push('-e', `${k}=${variables[k]}`);
    }

    params.push(imageName, 'sh', '-c', `helpers/build.sh "${func.commands}"`);

    const buildProcess = childProcess.spawn('docker', params, {
        stdio: 'pipe',
        pwd: functionDir
    });

    buildProcess.stdout.on('data', (data) => {
        process.stdout.write(`\n${data}`);
    });

    buildProcess.stderr.on('data', (data) => {
        process.stderr.write(`\n${data}`);
    });

    await new Promise((res) => { buildProcess.on('close', res) });

    const copyPath = path.join(process.cwd(), func.path, '.appwrite', 'build.tar.gz');
    const copyDir = path.dirname(copyPath);
    if (!fs.existsSync(copyDir)) {
        fs.mkdirSync(copyDir, { recursive: true });
    }

    const copyProcess = childProcess.spawn('docker', ['cp', `${id}:/mnt/code/code.tar.gz`, copyPath], {
        stdio: 'pipe',
        pwd: functionDir
    });

    await new Promise((res) => { copyProcess.on('close', res) });

    const cleanupProcess = childProcess.spawn('docker', ['rm', '--force', id], {
        stdio: 'pipe',
        pwd: functionDir
    });

    await new Promise((res) => { cleanupProcess.on('close', res) });

    delete activeDockerIds[id];

    const tempPath = path.join(process.cwd(), func.path, 'code.tar.gz');
    if (fs.existsSync(tempPath)) {
        fs.rmSync(tempPath, { force: true });
    }
}

async function dockerStart(func, variables, port) {
   log('Starting function using Docker ...');

   log("Permissions, events, CRON and timeouts dont apply when running locally.");

   log('💡 Hint: Function automatically restarts when you edit your code.');

   success(`Visit http://localhost:${port}/ to execute your function.`);


   const runtimeChunks = func.runtime.split("-");
   const runtimeVersion = runtimeChunks.pop();
   const runtimeName = runtimeChunks.join("-");
    const imageName = `openruntimes/${runtimeName}:${openRuntimesVersion}-${runtimeVersion}`;

    const tool = systemTools[runtimeName];

    const functionDir = path.join(process.cwd(), func.path);

    const id = ID.unique();

    const params = [ 'run' ];
    params.push('--rm');
    params.push('-d');
    params.push('--name', id);
    params.push('-p', `${port}:3000`);
    params.push('-e', 'APPWRITE_ENV=development');
    params.push('-e', 'OPEN_RUNTIMES_ENV=development');
    params.push('-e', 'OPEN_RUNTIMES_SECRET=');

   for(const k of Object.keys(variables)) {
        params.push('-e', `${k}=${variables[k]}`);
    }

    params.push('-v', `${functionDir}/.appwrite/logs.txt:/mnt/logs/dev_logs.log:rw`);
    params.push('-v', `${functionDir}/.appwrite/errors.txt:/mnt/logs/dev_errors.log:rw`);
    params.push('-v', `${functionDir}/.appwrite/build.tar.gz:/mnt/code/code.tar.gz:ro`);
    params.push(imageName, 'sh', '-c', `helpers/start.sh "${tool.startCommand}"`);

    childProcess.spawn('docker', params, {
        stdio: 'pipe',
        pwd: functionDir
    });

    activeDockerIds[id] = true;
}

async function dockerCleanup() {
    await dockerStop();

    const functions = localConfig.getFunctions();
    for(const func of functions) {
        const appwritePath = path.join(process.cwd(), func.path, '.appwrite');
        if (fs.existsSync(appwritePath)) {
            fs.rmSync(appwritePath, { recursive: true, force: true });
        }

        const tempPath = path.join(process.cwd(), func.path, 'code.tar.gz');
        if (fs.existsSync(tempPath)) {
            fs.rmSync(tempPath, { force: true });
        }
    }
}

async function dockerStopActive() {
    const ids = Object.keys(activeDockerIds);
    for await (const id of ids) {
        await dockerStop(id);
    }
}

module.exports = {
    dockerStop,
    dockerPull,
    dockerBuild,
    dockerStart,
    dockerCleanup,
    dockerStopActive,
}
