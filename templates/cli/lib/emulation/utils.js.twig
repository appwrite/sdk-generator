const EventEmitter = require('node:events');
const { projectsCreateJWT } = require('../commands/projects');
const { localConfig } = require("../config");
const { usersGet, usersCreateJWT } = require("../commands/users");
const { log } = require("../parser");

const openRuntimesVersion = 'v4';

const runtimeNames = {
    'node': 'Node.js',
    'php': 'PHP',
    'ruby': 'Ruby',
    'python': 'Python',
    'python-ml': 'Python (ML)',
    'deno': 'Deno',
    'dart': 'Dart',
    'dotnet': '.NET',
    'java': 'Java',
    'swift': 'Swift',
    'kotlin': 'Kotlin',
    'bun': 'Bun',
    'go': 'Go',
};

const systemTools = {
    'node': {
        isCompiled: false,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ "package.json", "package-lock.json" ]
    },
    'php': {
        isCompiled: false,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ "composer.json", "composer.lock" ]
    },
    'ruby': {
        isCompiled: false,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ "Gemfile", "Gemfile.lock" ]
    },
    'python': {
        isCompiled: false,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ "requirements.txt", "requirements.lock" ]
    },
    'python-ml': {
        isCompiled: false,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ "requirements.txt", "requirements.lock" ]
    },
    'deno': {
        isCompiled: false,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ ]
    },
    'dart': {
        isCompiled: true,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ ]
    },
    'dotnet': {
        isCompiled: true,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ ]
    },
    'java': {
        isCompiled: true,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ ]
    },
    'swift': {
        isCompiled: true,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ ]
    },
    'kotlin': {
        isCompiled: true,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ ]
    },
    'bun': {
        isCompiled: false,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ "package.json", "package-lock.json", "bun.lockb" ]
    },
    'go': {
        isCompiled: true,
        startCommand: "sh helpers/server.sh",
        dependencyFiles: [ ]
    },
};

const JwtManager = {
    userJwt: null,
    functionJwt: null,

    timerWarn: null,
    timerError: null,

    async setup(userId = null, projectScopes = []) {
        if(this.timerWarn) {
            clearTimeout(this.timerWarn);
        }

        if(this.timerError) {
            clearTimeout(this.timerError);
        }

        this.timerWarn = setTimeout(() => {
            log("Warning: Authorized JWT will expire in 5 minutes. Please stop and re-run the command to refresh tokens for 1 hour.");
        }, 1000 * 60 * 55); // 55 mins

        this.timerError = setTimeout(() => {
            log("Warning: Authorized JWT just expired. Please stop and re-run the command to obtain new tokens with 1 hour validity.");
            log("Some Appwrite API communication is not authorized now.")
        }, 1000 * 60 * 60); // 60 mins

        if(userId) {
            await usersGet({
                userId,
                parseOutput: false
            });
            const userResponse = await usersCreateJWT({
                userId,
                duration: 60*60,
                parseOutput: false
            });
            this.userJwt = userResponse.jwt;
        }

        const functionResponse = await projectsCreateJWT({
            projectId: localConfig.getProject().projectId,
            scopes: projectScopes,
            duration: 60*60,
            parseOutput: false
        });
        this.functionJwt = functionResponse.jwt;
    }
};

const Queue = {
    files: [],
    locked: false,
    events: new EventEmitter(),
    debounce: null,
    push(file) {
        if(!this.files.includes(file)) {
            this.files.push(file);
        }

        if(!this.locked) {
            this._trigger();
        }
    },
    lock() {
        this.files = [];
        this.locked = true;
    },
    isEmpty() {
        return this.files.length === 0
    },
    unlock() {
        this.locked = false;
        if(this.files.length > 0) {
            this._trigger();
        }
    },
    _trigger() {
        if(this.debounce) {
            return;
        }

        this.debounce = setTimeout(() => {
            this.events.emit('reload', { files: this.files });
            this.debounce = null;
        }, 300);
    }
};

module.exports = {
    openRuntimesVersion,
    runtimeNames,
    systemTools,
    JwtManager,
    Queue
}
