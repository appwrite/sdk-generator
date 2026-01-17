import { EventEmitter } from "node:events";
import { localConfig } from "../config.js";
import { log } from "../parser.js";
import { sdkForConsole } from "../sdks.js";
import { Projects, Users } from "@appwrite.io/console";

export const openRuntimesVersion = "v4";

export const runtimeNames: Record<string, string> = {
  node: "Node.js",
  php: "PHP",
  ruby: "Ruby",
  python: "Python",
  "python-ml": "Python (ML)",
  deno: "Deno",
  dart: "Dart",
  dotnet: ".NET",
  java: "Java",
  swift: "Swift",
  kotlin: "Kotlin",
  bun: "Bun",
  go: "Go",
};

interface SystemTool {
  isCompiled: boolean;
  startCommand: string;
  dependencyFiles: string[];
}

export const systemTools: Record<string, SystemTool> = {
  node: {
    isCompiled: false,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: ["package.json", "package-lock.json"],
  },
  php: {
    isCompiled: false,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: ["composer.json", "composer.lock"],
  },
  ruby: {
    isCompiled: false,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: ["Gemfile", "Gemfile.lock"],
  },
  python: {
    isCompiled: false,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: ["requirements.txt", "requirements.lock"],
  },
  "python-ml": {
    isCompiled: false,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: ["requirements.txt", "requirements.lock"],
  },
  deno: {
    isCompiled: false,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: [],
  },
  dart: {
    isCompiled: true,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: [],
  },
  dotnet: {
    isCompiled: true,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: [],
  },
  java: {
    isCompiled: true,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: [],
  },
  swift: {
    isCompiled: true,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: [],
  },
  kotlin: {
    isCompiled: true,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: [],
  },
  bun: {
    isCompiled: false,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: ["package.json", "package-lock.json", "bun.lockb"],
  },
  go: {
    isCompiled: true,
    startCommand: "sh helpers/server.sh",
    dependencyFiles: [],
  },
};

export const JwtManager = {
  userJwt: null as string | null,
  functionJwt: null as string | null,

  timerWarn: null as NodeJS.Timeout | null,
  timerError: null as NodeJS.Timeout | null,

  async setup(
    userId: string | null = null,
    projectScopes: string[] = [],
  ): Promise<void> {
    const consoleClient = await sdkForConsole();
    const usersClient = new Users(consoleClient);
    const projectsClient = new Projects(consoleClient);

    if (this.timerWarn) {
      clearTimeout(this.timerWarn);
    }

    if (this.timerError) {
      clearTimeout(this.timerError);
    }

    this.timerWarn = setTimeout(
      () => {
        log(
          "Warning: Authorized JWT will expire in 5 minutes. Please stop and re-run the command to refresh tokens for 1 hour.",
        );
      },
      1000 * 60 * 55,
    ); // 55 mins

    this.timerError = setTimeout(
      () => {
        log(
          "Warning: Authorized JWT just expired. Please stop and re-run the command to obtain new tokens with 1 hour validity.",
        );
        log("Some Appwrite API communication is not authorized now.");
      },
      1000 * 60 * 60,
    ); // 60 mins

    if (userId) {
      await usersClient.get({
        userId,
      });
      const userResponse: any = await usersClient.createJWT({
        userId,
        duration: 60 * 60,
      });
      this.userJwt = userResponse.jwt;
    }

    const functionResponse: any = await projectsClient.createJWT({
      projectId: localConfig.getProject().projectId!,
      scopes: projectScopes,
      duration: 60 * 60,
    });
    this.functionJwt = functionResponse.jwt;
  },
};

interface QueueReloadEvent {
  files: string[];
}

export const Queue = {
  files: [] as string[],
  locked: false,
  events: new EventEmitter(),
  debounce: null as NodeJS.Timeout | null,

  push(file: string): void {
    if (!this.files.includes(file)) {
      this.files.push(file);
    }

    if (!this.locked) {
      this._trigger();
    }
  },

  lock(): void {
    this.files = [];
    this.locked = true;
  },

  isEmpty(): boolean {
    return this.files.length === 0;
  },

  unlock(): void {
    this.locked = false;
    if (this.files.length > 0) {
      this._trigger();
    }
  },

  _trigger(): void {
    if (this.debounce) {
      return;
    }

    this.debounce = setTimeout(() => {
      this.events.emit("reload", { files: this.files } as QueueReloadEvent);
      this.debounce = null;
    }, 300);
  },
};
