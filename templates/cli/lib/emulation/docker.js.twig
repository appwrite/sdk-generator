const tar = require("tar");
const ignore = require("ignore");
const net = require('net');
const chalk = require('chalk');
const childProcess = require('child_process');
const { localConfig } = require("../config");
const path = require('path');
const fs = require('fs');
const { log, error, success, hint } = require("../parser");
const { openRuntimesVersion, systemTools, Queue } = require("./utils");
const { getAllFiles } = require("../utils");

async function dockerStop(id) {
    const stopProcess = childProcess.spawn('docker', ['rm', '--force', id], {
        stdio: 'pipe',
        env: {
            ...process.env,
            DOCKER_CLI_HINTS: 'false'
        }
    });

    await new Promise((res) => { stopProcess.on('close', res) });
}

async function dockerPull(func) {
    const runtimeChunks = func.runtime.split("-");
    const runtimeVersion = runtimeChunks.pop();
    const runtimeName = runtimeChunks.join("-");
    const imageName = `openruntimes/${runtimeName}:${openRuntimesVersion}-${runtimeVersion}`;

    log('Verifying Docker image ...');

    const pullProcess = childProcess.spawn('docker', ['pull', imageName], {
        stdio: 'pipe',
        env: {
            ...process.env,
            DOCKER_CLI_HINTS: 'false'
        }
    });

    await new Promise((res) => { pullProcess.on('close', res) });
}

async function dockerBuild(func, variables) {
    const runtimeChunks = func.runtime.split("-");
    const runtimeVersion = runtimeChunks.pop();
    const runtimeName = runtimeChunks.join("-");
    const imageName = `openruntimes/${runtimeName}:${openRuntimesVersion}-${runtimeVersion}`;

    const functionDir = path.join(localConfig.getDirname(), func.path);

    const id = func.$id;

    const ignorer = ignore();
    ignorer.add('.appwrite');
    if (func.ignore) {
        ignorer.add(func.ignore);
    } else if (fs.existsSync(path.join(functionDir, '.gitignore'))) {
        ignorer.add(fs.readFileSync(path.join(functionDir, '.gitignore')).toString());
    }
    
    const files = getAllFiles(functionDir).map((file) => path.relative(functionDir, file)).filter((file) => !ignorer.ignores(file));
    const tmpBuildPath = path.join(functionDir, '.appwrite/tmp-build');
    if (!fs.existsSync(tmpBuildPath)) {
        fs.mkdirSync(tmpBuildPath, { recursive: true });
    }

    for(const f of files) {
        const filePath = path.join(tmpBuildPath, f);
        const fileDir = path.dirname(filePath);
        if (!fs.existsSync(fileDir)) {
            fs.mkdirSync(fileDir, { recursive: true });
        }

        const sourcePath = path.join(functionDir, f);
        fs.copyFileSync(sourcePath, filePath);
    }

    const params = [ 'run' ];
    params.push('--name', id);
    params.push('-v', `${tmpBuildPath}/:/mnt/code:rw`);
    params.push('-e', 'OPEN_RUNTIMES_ENV=development');
    params.push('-e', 'OPEN_RUNTIMES_SECRET=');
    params.push('-e', `OPEN_RUNTIMES_ENTRYPOINT=${func.entrypoint}`);

    for(const k of Object.keys(variables)) {
        params.push('-e', `${k}=${variables[k]}`);
    }

    params.push(imageName, 'sh', '-c', `helpers/build.sh "${func.commands}"`);

    const buildProcess = childProcess.spawn('docker', params, {
        stdio: 'pipe',
        pwd: functionDir,
        env: {
            ...process.env,
            DOCKER_CLI_HINTS: 'false'
        }
    });

    buildProcess.stdout.on('data', (data) => {
        process.stdout.write(chalk.blackBright(`${data}\n`));
    });

    buildProcess.stderr.on('data', (data) => {
        process.stderr.write(chalk.blackBright(`${data}\n`));
    });

    const killInterval = setInterval(() => {
        if(!Queue.isEmpty()) {
            log('Cancelling build ...');
            buildProcess.stdout.destroy();
            buildProcess.stdin.destroy();
            buildProcess.stderr.destroy();
            buildProcess.kill("SIGKILL");
            clearInterval(killInterval);
        }
    }, 100);

    await new Promise((res) => { buildProcess.on('close', res) });

    clearInterval(killInterval);
    if(!Queue.isEmpty()) {
        return;
    }

    const copyPath = path.join(localConfig.getDirname(), func.path, '.appwrite', 'build.tar.gz');
    const copyDir = path.dirname(copyPath);
    if (!fs.existsSync(copyDir)) {
        fs.mkdirSync(copyDir, { recursive: true });
    }

    const copyProcess = childProcess.spawn('docker', ['cp', `${id}:/mnt/code/code.tar.gz`, copyPath], {
        stdio: 'pipe',
        pwd: functionDir,
        env: {
            ...process.env,
            DOCKER_CLI_HINTS: 'false'
        }
    });

    await new Promise((res) => { copyProcess.on('close', res) });

    await dockerStop(id);

    const tempPath = path.join(localConfig.getDirname(), func.path, 'code.tar.gz');
    if (fs.existsSync(tempPath)) {
        fs.rmSync(tempPath, { force: true });
    }

    fs.rmSync(tmpBuildPath, { recursive: true, force: true });
}

async function dockerStart(func, variables, port) {
    // Pack function files
    const functionDir = path.join(localConfig.getDirname(), func.path);

    const runtimeChunks = func.runtime.split("-");
    const runtimeVersion = runtimeChunks.pop();
    const runtimeName = runtimeChunks.join("-");
    const imageName = `openruntimes/${runtimeName}:${openRuntimesVersion}-${runtimeVersion}`;

    const tool = systemTools[runtimeName];

    const id = func.$id;

    const params = [ 'run' ];
    params.push('--rm');
    params.push('--name', id);
    params.push('-p', `${port}:3000`);
    params.push('-e', 'OPEN_RUNTIMES_ENV=development');
    params.push('-e', 'OPEN_RUNTIMES_SECRET=');

   for(const k of Object.keys(variables)) {
        params.push('-e', `${k}=${variables[k]}`);
    }

    params.push('-v', `${functionDir}/.appwrite/logs.txt:/mnt/logs/dev_logs.log:rw`);
    params.push('-v', `${functionDir}/.appwrite/errors.txt:/mnt/logs/dev_errors.log:rw`);
    params.push('-v', `${functionDir}/.appwrite/build.tar.gz:/mnt/code/code.tar.gz:ro`);
    params.push(imageName, 'sh', '-c', `helpers/start.sh "${tool.startCommand}"`);

    const startProcess = childProcess.spawn('docker', params, {
        stdio: 'pipe',
        pwd: functionDir,
        env: {
            ...process.env,
            DOCKER_CLI_HINTS: 'false'
        }
    });

    startProcess.stdout.on('data', (data) => {
        process.stdout.write(chalk.blackBright(data));
    });

    startProcess.stderr.on('data', (data) => {
        process.stdout.write(chalk.blackBright(data));
    });

    try {
        await waitUntilPortOpen(port);
    } catch(err) {
        error("Failed to start function with error: " + err.message ? err.message : err.toString());
        return;
    }

    success(`Visit http://localhost:${port}/ to execute your function.`);
}

async function dockerCleanup(functionId) {
    await dockerStop(functionId);

    const func = localConfig.getFunction(functionId);
    const appwritePath = path.join(localConfig.getDirname(), func.path, '.appwrite');
    if (fs.existsSync(appwritePath)) {
        fs.rmSync(appwritePath, { recursive: true, force: true });
    }

    const tempPath = path.join(localConfig.getDirname(), func.path, 'code.tar.gz');
    if (fs.existsSync(tempPath)) {
        fs.rmSync(tempPath, { force: true });
    }
}

function waitUntilPortOpen(port, iteration = 0) {
    return new Promise((resolve, reject) => {
        const client = new net.Socket();

        client.once('connect', () => {
            client.removeAllListeners('connect');
            client.removeAllListeners('error');
            client.end();
            client.destroy();
            client.unref();

            resolve();
        });

        client.once('error', async (err) => {
            client.removeAllListeners('connect');
            client.removeAllListeners('error');
            client.end();
            client.destroy();
            client.unref();

            if(iteration > 100) {
                reject(err);
            } else {
                await new Promise((res) => setTimeout(res, 100));
                waitUntilPortOpen(port, iteration + 1).then(resolve).catch(reject);
            }
        });

        client.connect({port, host: '127.0.0.1'}, function() {});
    });
}

module.exports = {
    dockerPull,
    dockerBuild,
    dockerStart,
    dockerCleanup,
    dockerStop,
}
