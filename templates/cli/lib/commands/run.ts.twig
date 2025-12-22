import { Tail } from 'tail';
import { parse as parseDotenv } from 'dotenv';
import chalk from 'chalk';
import ignore = require('ignore');
import tar = require('tar');
import fs = require('fs');
import chokidar from 'chokidar';
import inquirer from 'inquirer';
import path = require('path');
import { Command } from 'commander';
import { localConfig, globalConfig } from '../config';
import { paginate } from '../paginate';
import { functionsListVariables } from './functions';
import { questionsRunFunctions } from '../questions';
import { actionRunner, success, log, warn, error, hint, commandDescriptions, drawTable } from '../parser';
import { systemHasCommand, isPortTaken, getAllFiles } from '../utils';
import { runtimeNames, systemTools, JwtManager, Queue } from '../emulation/utils';
import { dockerStop, dockerCleanup, dockerStart, dockerBuild, dockerPull } from '../emulation/docker';

interface RunFunctionOptions {
    port?: string | number;
    functionId?: string;
    withVariables?: boolean;
    reload?: boolean;
    userId?: string;
}

const runFunction = async ({ port, functionId, withVariables, reload, userId }: RunFunctionOptions = {}): Promise<void> => {
    // Selection
    if(!functionId) {
        const answers = await inquirer.prompt(questionsRunFunctions[0]);
        functionId = answers.function;
    }

    const functions = localConfig.getFunctions();
    const func = functions.find((f: any) => f.$id === functionId);
    if (!func) {
        throw new Error("Function '" + functionId + "' not found.")
    }

    const runtimeName = func.runtime.split("-").slice(0, -1).join("-");
    const tool = systemTools[runtimeName];

    // Configuration: Port
    let portNum: number | null = null;
    if(port) {
        portNum = +port;
    }

    if(isNaN(portNum!)) {
        portNum = null;
    }

    if(portNum) {
        const taken = await isPortTaken(portNum);

        if(taken) {
            error(`Port ${portNum} is already in use by another process.`);
            return;
        }
    }

    if(!portNum) {
        let portFound = false;
        portNum = 3000;
        while(portNum < 3100) {
            const taken = await isPortTaken(portNum);
            if(!taken) {
                portFound = true;
                break;
            }

            portNum++;
        }

        if(!portFound) {
            error("Could not find an available port. Please select a port with 'appwrite run --port YOUR_PORT' command.");
            return;
        }
    }

    // Configuration: Engine
    if(!systemHasCommand('docker')) {
        return error("Docker Engine is required for local development. Please install Docker using: https://docs.docker.com/engine/install/");
    }

    // Settings
    const settings = {
        runtime: func.runtime,
        entrypoint: func.entrypoint,
        path: func.path,
        commands: func.commands,
        scopes: func.scopes ?? []
    };

    drawTable([settings]);
    log("If you wish to change your local settings, update the appwrite.config.json file and rerun the 'appwrite run' command.");
    hint("Permissions, events, CRON and timeouts dont apply when running locally.");

    await dockerCleanup(func.$id);

    process.on('SIGINT', async () => {
        log('Cleaning up ...');
        await dockerCleanup(func.$id);
        success("Local function successfully stopped.");
        process.exit();
    });

    const logsPath = path.join(localConfig.getDirname(), func.path, '.appwrite/logs.txt');
    const errorsPath = path.join(localConfig.getDirname(), func.path, '.appwrite/errors.txt');

    if(!fs.existsSync(path.dirname(logsPath))) {
        fs.mkdirSync(path.dirname(logsPath), { recursive: true });
    }

    if (!fs.existsSync(logsPath)) {
        fs.writeFileSync(logsPath, '');
    }

    if (!fs.existsSync(errorsPath)) {
        fs.writeFileSync(errorsPath, '');
    }

    const userVariables: Record<string, string> = {};
    const allVariables: Record<string, string> = {};

    if(withVariables) {
        try {
            const { variables: remoteVariables } = await paginate(functionsListVariables, {
                functionId: func['$id'],
                parseOutput: false
            }, 100, 'variables');

            remoteVariables.forEach((v: any) => {
                allVariables[v.key] = v.value;
                userVariables[v.key] = v.value;
            });
        } catch(err: any) {
            warn("Remote variables not fetched. Production environment variables will not be available. Reason: " + err.message);
        }
    }

    const functionPath = path.join(localConfig.getDirname(), func.path);
    const envPath = path.join(functionPath, '.env');
    if(fs.existsSync(envPath)) {
        const env = parseDotenv(fs.readFileSync(envPath).toString() ?? '');

        Object.keys(env).forEach((key) => {
            allVariables[key] = env[key];
            userVariables[key] = env[key];
        });
    }

    allVariables['APPWRITE_FUNCTION_API_ENDPOINT'] = globalConfig.getFrom('endpoint');
    allVariables['APPWRITE_FUNCTION_ID'] = func.$id;
    allVariables['APPWRITE_FUNCTION_NAME'] = func.name;
    allVariables['APPWRITE_FUNCTION_DEPLOYMENT'] = ''; // TODO: Implement when relevant
    allVariables['APPWRITE_FUNCTION_PROJECT_ID'] = localConfig.getProject().projectId;
    allVariables['APPWRITE_FUNCTION_RUNTIME_NAME'] = runtimeNames[runtimeName] ?? '';
    allVariables['APPWRITE_FUNCTION_RUNTIME_VERSION'] = func.runtime;

    try {
        await JwtManager.setup(userId, func.scopes ?? []);
    } catch(err: any) {
        warn("Dynamic API key not generated. Header x-appwrite-key will not be set. Reason: " + err.message);
    }

    const headers: Record<string, string> = {};
    headers['x-appwrite-key'] = JwtManager.functionJwt ?? '';
    headers['x-appwrite-trigger'] = 'http';
    headers['x-appwrite-event'] = '';
    headers['x-appwrite-user-id'] = userId ?? '';
    headers['x-appwrite-user-jwt'] = JwtManager.userJwt ?? '';
    allVariables['OPEN_RUNTIMES_HEADERS'] = JSON.stringify(headers);

    if(Object.keys(userVariables).length > 0) {
        drawTable(Object.keys(userVariables).map((key) => ({
            key,
            value: userVariables[key].split("").filter((_: string, i: number) => i < 16).map(() => "*").join("")
        })));
    }

    await dockerPull(func);

    new Tail(logsPath).on("line", function(data: string) {
        process.stdout.write(chalk.white(`${data}\n`));
    });
    new Tail(errorsPath).on("line", function(data: string) {
        process.stdout.write(chalk.white(`${data}\n`));
    });

    if(reload) {
        const ignorer = ignore();
        ignorer.add('.appwrite');
        ignorer.add('code.tar.gz');

        if (func.ignore) {
            ignorer.add(func.ignore);
        } else if (fs.existsSync(path.join(functionPath, '.gitignore'))) {
            ignorer.add(fs.readFileSync(path.join(functionPath, '.gitignore')).toString());
        }

        chokidar.watch('.', {
            cwd: path.join(localConfig.getDirname(), func.path),
            ignoreInitial: true,
            ignored: (xpath: string) => {
                const relativePath = path.relative(functionPath, xpath);

                if(!relativePath) {
                    return false;
                }
                return ignorer.ignores(relativePath);
            }
        }).on('all', async (_event: string, filePath: string) => {
            Queue.push(filePath);
        });
    }

    Queue.events.on('reload', async ({ files }: { files: string[] }) => {
        Queue.lock();

        try {
            await dockerStop(func.$id);

            const dependencyFile = files.find((filePath: string) => tool.dependencyFiles.includes(filePath));
            if(tool.isCompiled || dependencyFile) {
                log(`Rebuilding the function due to file changes ...`);
                await dockerBuild(func, allVariables);

                if(!Queue.isEmpty()) {
                    Queue.unlock();
                    return;
                }

                await dockerStart(func, allVariables, portNum!);
            } else {
                log('Hot-swapping function.. Files with change are ' + files.join(', '));

                const hotSwapPath = path.join(functionPath, '.appwrite/hot-swap');
                const buildPath = path.join(functionPath, '.appwrite/build.tar.gz');

                // Prepare temp folder
                if (!fs.existsSync(hotSwapPath)) {
                    fs.mkdirSync(hotSwapPath, { recursive: true });
                } else {
                    fs.rmSync(hotSwapPath, { recursive: true, force: true });
                    fs.mkdirSync(hotSwapPath, { recursive: true });
                }

                await tar
                    .extract({
                        keep: true,
                        gzip: true,
                        sync: true,
                        cwd: hotSwapPath,
                        file: buildPath
                    });

                const ignorer = ignore.default();
                ignorer.add('.appwrite');
                if (func.ignore) {
                    ignorer.add(func.ignore);
                } else if (fs.existsSync(path.join(functionPath, '.gitignore'))) {
                    ignorer.add(fs.readFileSync(path.join(functionPath, '.gitignore')).toString());
                }

                const filesToCopy = getAllFiles(functionPath).map((file: string) => path.relative(functionPath, file)).filter((file: string) => !ignorer.ignores(file));
                for(const f of filesToCopy) {
                    const filePath = path.join(hotSwapPath, f);
                    if (fs.existsSync(filePath)) {
                        fs.rmSync(filePath, { force: true });
                    }

                    const fileDir = path.dirname(filePath);
                    if (!fs.existsSync(fileDir)) {
                        fs.mkdirSync(fileDir, { recursive: true });
                    }

                    const sourcePath = path.join(functionPath, f);
                    fs.copyFileSync(sourcePath, filePath);
                }

                await tar
                    .create({
                        gzip: true,
                        sync: true,
                        cwd: hotSwapPath,
                        file: buildPath
                    }, ['.']);

                await dockerStart(func, allVariables, portNum!);
            }
        } catch(err) {
            console.error(err);
        } finally {
            Queue.unlock();
        }
    });

    Queue.lock();

    log('Building function using Docker ...');
    await dockerBuild(func, allVariables);

    if(!Queue.isEmpty()) {
        Queue.unlock();
        return;
    }

    log('Starting function using Docker ...');
    hint('Function automatically restarts when you edit your code.');
    await dockerStart(func, allVariables, portNum!);

    Queue.unlock();
}

export const run = new Command("run")
    .description(commandDescriptions['run'])
    .configureHelp({
        helpWidth: process.stdout.columns || 80
    })
    .action(actionRunner(async (_options: any, command: Command) => {
        command.help();
    }));

run
    .command("function")
    .alias("functions")
    .description("Run functions in the current directory.")
    .option(`--function-id <function-id>`, `ID of function to run`)
    .option(`--port <port>`, `Local port`)
    .option(`--user-id <user-id>`, `ID of user to impersonate`)
    .option(`--with-variables`, `Run with function variables from function settings`)
    .option(`--no-reload`, `Prevent live reloading of server when changes are made to function files`)
    .action(actionRunner(runFunction));

