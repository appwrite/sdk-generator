import chalk from "chalk";
import { localConfig, globalConfig } from "./config.js";
import { sdkForConsole } from "./sdks.js";
import { validateRequired } from "./validations.js";
import { paginate } from "./paginate.js";
import { checkDeployConditions, isCloud } from "./utils.js";
import { Account, Client } from "@appwrite.io/console";
import {
  getOrganizationsService,
  getTeamsService,
  getProjectsService,
  getFunctionsService,
  getSitesService,
  getDatabasesService,
} from "./services.js";
import { SDK_TITLE, DEFAULT_ENDPOINT, EXECUTABLE_NAME } from "./constants.js";

interface Answers {
  override?: boolean;
  method?: string;
  start?: string;
  organization?: string;
  [key: string]: any;
}

interface Choice {
  name: string;
  value: any;
  disabled?: boolean | string;
  current?: boolean;
}

interface Question {
  type: string;
  name: string;
  message: string;
  default?: any;
  when?: ((answers: Answers) => boolean | Promise<boolean>) | boolean;
  choices?:
    | ((answers: Answers) => Promise<Choice[]> | Choice[])
    | (() => Promise<Choice[]> | Choice[])
    | Choice[]
    | string[];
  validate?: (value: any) => boolean | string | Promise<boolean | string>;
  mask?: string;
}

const whenOverride = (answers: Answers): boolean =>
  answers.override === undefined ? true : answers.override;

const getIgnores = (runtime: string): string[] => {
  const language = runtime.split("-").slice(0, -1).join("-");

  switch (language) {
    case "cpp":
      return ["build", "CMakeFiles", "CMakeCaches.txt"];
    case "dart":
      return [".packages", ".dart_tool"];
    case "deno":
      return [];
    case "dotnet":
      return ["bin", "obj", ".nuget"];
    case "java":
    case "kotlin":
      return ["build"];
    case "node":
    case "bun":
      return ["node_modules", ".npm"];
    case "php":
      return ["vendor"];
    case "python":
    case "python-ml":
      return ["__pypackages__"];
    case "ruby":
      return ["vendor"];
    case "rust":
      return ["target", "debug", "*.rs.bk", "*.pdb"];
    case "swift":
      return [".build", ".swiftpm"];
  }

  return [];
};

const getEntrypoint = (runtime: string): string | undefined => {
  const language = runtime.split("-").slice(0, -1).join("-");

  switch (language) {
    case "dart":
      return "lib/main.dart";
    case "deno":
      return "src/main.ts";
    case "node":
      return "src/main.js";
    case "bun":
      return "src/main.ts";
    case "php":
      return "src/index.php";
    case "python":
    case "python-ml":
      return "src/main.py";
    case "ruby":
      return "lib/main.rb";
    case "rust":
      return "main.rs";
    case "swift":
      return "Sources/index.swift";
    case "cpp":
      return "src/main.cc";
    case "dotnet":
      return "src/Index.cs";
    case "java":
      return "src/Main.java";
    case "kotlin":
      return "src/Main.kt";
    case "go":
      return "main.go";
  }

  return undefined;
};

const getInstallCommand = (runtime: string): string | undefined => {
  const language = runtime.split("-").slice(0, -1).join("-");

  switch (language) {
    case "dart":
      return "dart pub get";
    case "deno":
      return "deno cache src/main.ts";
    case "node":
      return "npm install";
    case "bun":
      return "bun install";
    case "php":
      return "composer install";
    case "python":
    case "python-ml":
      return "pip install -r requirements.txt";
    case "ruby":
      return "bundle install";
    case "rust":
      return "cargo install";
    case "dotnet":
      return "dotnet restore";
    case "swift":
    case "java":
    case "kotlin":
    case "cpp":
      return "";
  }

  return undefined;
};

export const questionsInitProject: Question[] = [
  {
    type: "confirm",
    name: "override",
    message: `An ${SDK_TITLE} project ( ${localConfig.getProject()["projectId"]} ) is already associated with the current directory. Would you like to override it?`,
    when() {
      return Object.keys(localConfig.getProject()).length !== 0;
    },
  },
  {
    type: "list",
    name: "start",
    when: whenOverride,
    message: "How would you like to start?",
    choices: [
      {
        name: "Create new project",
        value: "new",
      },
      {
        name: "Link directory to an existing project",
        value: "existing",
      },
    ],
  },
  {
    type: "search-list",
    name: "organization",
    message: "Choose your organization",
    choices: async () => {
      let client = await sdkForConsole(true);
      const { teams } = isCloud()
        ? await paginate(
            async (opts: { sdk?: Client } = {}) =>
              (await getOrganizationsService(opts.sdk)).list(),
            { sdk: client },
            100,
            "teams",
          )
        : await paginate(
            async (opts: { sdk?: Client } = {}) =>
              (await getTeamsService(opts.sdk)).list(),
            { parseOutput: false, sdk: client },
            100,
            "teams",
          );

      let choices = teams.map((team: any, idx: number) => {
        return {
          name: `${team.name} (${team["$id"]})`,
          value: team["$id"],
        };
      });

      if (choices.length == 0) {
        throw new Error(
          `No organizations found. Please create a new organization at ${globalConfig.getEndpoint().replace("/v1", "/console/onboarding")}`,
        );
      }

      return choices;
    },
    when: whenOverride,
  },
  {
    type: "input",
    name: "project",
    message: "What would you like to name your project?",
    default: "My Awesome Project",
    when: (answer: Answers) => answer.start !== "existing",
  },
  {
    type: "input",
    name: "id",
    message: "What ID would you like to have for your project?",
    default: "unique()",
    when: (answer: Answers) => answer.start !== "existing",
  },
  {
    type: "search-list",
    name: "project",
    message: `Choose your ${SDK_TITLE} project.`,
    choices: async (answers: Answers) => {
      const queries = [
        JSON.stringify({
          method: "equal",
          attribute: "teamId",
          values: [answers.organization],
        }),
        JSON.stringify({ method: "orderDesc", attribute: "$id" }),
      ];

      const { projects } = await paginate(
        async () => (await getProjectsService()).list(queries),
        { parseOutput: false },
        100,
        "projects",
        queries,
      );

      let choices = projects.map((project: any) => {
        return {
          name: `${project.name} (${project["$id"]})`,
          value: {
            $id: project["$id"],
            region: project.region || "",
          },
        };
      });

      if (choices.length === 0) {
        throw new Error("No projects found. Please create a new project.");
      }

      return choices;
    },
    when: (answer: Answers) => answer.start === "existing",
  },
  {
    type: "list",
    name: "region",
    message: `Select your ${SDK_TITLE} Cloud region`,
    choices: async () => {
      let client = await sdkForConsole(true);
      const endpoint = globalConfig.getEndpoint() || DEFAULT_ENDPOINT;
      let response = (await client.call(
        "GET",
        new URL(endpoint + "/console/regions"),
      )) as { regions: any[] };
      let regions = response.regions || [];
      if (!regions.length) {
        throw new Error(
          "No regions found. Please check your network or Appwrite Cloud availability.",
        );
      }
      return regions
        .filter((region: any) => !region.disabled)
        .map((region: any) => ({
          name: `${region.name} (${region.$id})`,
          value: region.$id,
        }));
    },
    when: (answer: Answers) => {
      if (answer.start === "existing") return false;
      return isCloud();
    },
  },
];

export const questionsInitProjectAutopull: Question[] = [
  {
    type: "confirm",
    name: "autopull",
    message: `Would you like to pull all resources from the project you just linked?`,
  },
];

export const questionsPullResources: Question[] = [
  {
    type: "list",
    name: "resource",
    message: "Which resources would you like to pull?",
    choices: [
      { name: `Settings ${chalk.blackBright(`(Project)`)}`, value: "settings" },
      {
        name: `Functions ${chalk.blackBright(`(Deployment)`)}`,
        value: "functions",
      },
      { name: `Tables ${chalk.blackBright(`(TablesDB)`)}`, value: "tables" },
      { name: `Buckets ${chalk.blackBright(`(Storage)`)}`, value: "buckets" },
      { name: `Teams ${chalk.blackBright(`(Auth)`)}`, value: "teams" },
      { name: `Topics ${chalk.blackBright(`(Messaging)`)}`, value: "messages" },
      {
        name: `Collections ${chalk.blackBright(`(Legacy Databases)`)}`,
        value: "collections",
      },
    ],
  },
];

export const questionsPullFunctions: Question[] = [
  {
    type: "checkbox",
    name: "functions",
    message: "Which functions would you like to pull?",
    validate: (value: any) => validateRequired("function", value),
    choices: async () => {
      const { functions } = await paginate(
        async () => (await getFunctionsService()).list(),
        { parseOutput: false },
        100,
        "functions",
      );

      if (functions.length === 0) {
        throw `We couldn't find any functions in your ${SDK_TITLE} project`;
      }
      return functions.map((func: any) => {
        return {
          name: `${func.name} (${func.$id})`,
          value: { ...func },
        };
      });
    },
  },
];

export const questionsPullFunctionsCode: Question[] = [
  {
    type: "confirm",
    name: "override",
    message: "Do you want to pull source code of the latest deployment?",
  },
];

export const questionsPushFunctionsCode: Question[] = [
  {
    type: "confirm",
    name: "override",
    message: "Do you want to create a deployment for your functions?",
  },
];

export const questionsPullSites: Question[] = [
  {
    type: "checkbox",
    name: "sites",
    message: "Which sites would you like to pull?",
    validate: (value: any) => validateRequired("site", value),
    choices: async () => {
      const { sites } = await paginate(
        async () => (await getSitesService()).list(),
        { parseOutput: false },
        100,
        "sites",
      );

      if (sites.length === 0) {
        throw `We couldn't find any sites in your ${SDK_TITLE} project`;
      }
      return sites.map((site: any) => {
        return {
          name: `${site.name} (${site.$id})`,
          value: { ...site },
        };
      });
    },
  },
];

export const questionsPullSitesCode: Question[] = [
  {
    type: "confirm",
    name: "override",
    message: "Do you want to pull source code of the latest deployment?",
  },
];

export const questionsPushSitesCode: Question[] = [
  {
    type: "confirm",
    name: "override",
    message: "Do you want to create a deployment for your sites?",
  },
];

export const questionsCreateFunction: Question[] = [
  {
    type: "input",
    name: "name",
    message: "What would you like to name your function?",
    default: "My Awesome Function",
  },
  {
    type: "input",
    name: "id",
    message: "What ID would you like to have for your function?",
    default: "unique()",
  },
  {
    type: "list",
    name: "runtime",
    message: "What runtime would you like to use?",
    choices: async () => {
      let response = await (await getFunctionsService()).listRuntimes();
      let runtimes = response["runtimes"];
      let choices = runtimes.map((runtime: any, idx: number) => {
        return {
          name: `${runtime.name} (${runtime["$id"]})`,
          value: {
            id: runtime["$id"],
            name: runtime["$id"].split("-")[0],
            entrypoint: getEntrypoint(runtime["$id"]),
            ignore: getIgnores(runtime["$id"]),
            commands: getInstallCommand(runtime["$id"]),
          },
        };
      });
      return choices;
    },
  },
  {
    type: "list",
    name: "specification",
    message: "What specification would you like to use?",
    choices: async () => {
      let response = await (await getFunctionsService()).listSpecifications();
      let specifications = response["specifications"];
      let choices = specifications.map((spec: any, idx: number) => {
        return {
          name: `${spec.cpus} CPU, ${spec.memory}MB RAM`,
          value: spec.slug,
          disabled: spec.enabled === false ? "Upgrade to use" : false,
        };
      });
      return choices;
    },
  },
];

export const questionsCreateFunctionSelectTemplate = (
  templates: string[],
): Question[] => {
  return [
    {
      type: "search-list",
      name: "template",
      message: "What template would you like to use?",
      choices: templates.map((template) => {
        const name =
          `${template[0].toUpperCase()}${template.split("").slice(1).join("")}`.replace(
            /[-_]/g,
            " ",
          );

        return { value: template, name };
      }),
    },
  ];
};

export const questionsCreateBucket: Question[] = [
  {
    type: "input",
    name: "bucket",
    message: "What would you like to name your bucket?",
    default: "My Awesome Bucket",
  },
  {
    type: "input",
    name: "id",
    message: "What ID would you like to have for your bucket?",
    default: "unique()",
  },
  {
    type: "list",
    name: "fileSecurity",
    message: "Enable File-Security configuring permissions for individual file",
    choices: ["No", "Yes"],
  },
];

export const questionsCreateTeam: Question[] = [
  {
    type: "input",
    name: "team",
    message: "What would you like to name your team?",
    default: "My Awesome Team",
  },
  {
    type: "input",
    name: "id",
    message: "What ID would you like to have for your team?",
    default: "unique()",
  },
];

export const questionsCreateCollection: Question[] = [
  {
    type: "list",
    name: "method",
    message: "What database would you like to use for your collection",
    choices: ["New", "Existing"],
    when: async () => {
      return localConfig.getDatabases().length !== 0;
    },
  },
  {
    type: "search-list",
    name: "database",
    message: "Choose the collection database",
    choices: async () => {
      const databases = localConfig.getDatabases();

      let choices = databases.map((database: any, idx: number) => {
        return {
          name: `${database.name} (${database.$id})`,
          value: database.$id,
        };
      });

      if (choices.length === 0) {
        throw new Error(
          "No databases found. Please create one in project console.",
        );
      }

      return choices;
    },
    when: (answers: Answers) =>
      (answers.method ?? "").toLowerCase() === "existing",
  },
  {
    type: "input",
    name: "databaseName",
    message: "What would you like to name your database?",
    default: "My Awesome Database",
    when: (answers: Answers) =>
      (answers.method ?? "").toLowerCase() !== "existing",
  },
  {
    type: "input",
    name: "databaseId",
    message: "What ID would you like to have for your database?",
    default: "unique()",
    when: (answers: Answers) =>
      (answers.method ?? "").toLowerCase() !== "existing",
  },
  {
    type: "input",
    name: "collection",
    message: "What would you like to name your collection?",
    default: "My Awesome Collection",
  },
  {
    type: "input",
    name: "id",
    message: "What ID would you like to have for your collection?",
    default: "unique()",
  },
  {
    type: "list",
    name: "documentSecurity",
    message:
      "Enable document security for configuring permissions for individual documents",
    choices: ["No", "Yes"],
  },
];

export const questionsCreateTable: Question[] = [
  {
    type: "list",
    name: "method",
    message: "What database would you like to use for your table?",
    choices: ["New", "Existing"],
    when: async () => {
      return localConfig.getTablesDBs().length !== 0;
    },
  },
  {
    type: "search-list",
    name: "database",
    message: "Choose the table database",
    choices: async () => {
      const databases = localConfig.getTablesDBs();

      let choices = databases.map((database: any, idx: number) => {
        return {
          name: `${database.name} (${database.$id})`,
          value: database.$id,
        };
      });

      if (choices.length === 0) {
        throw new Error(
          "No databases found. Please create one in project console.",
        );
      }

      return choices;
    },
    when: (answers: Answers) =>
      (answers.method ?? "").toLowerCase() === "existing",
  },
  {
    type: "input",
    name: "databaseName",
    message: "What would you like to name your database?",
    default: "My Awesome Database",
    when: (answers: Answers) =>
      (answers.method ?? "").toLowerCase() !== "existing",
  },
  {
    type: "input",
    name: "databaseId",
    message: "What ID would you like to have for your database?",
    default: "unique()",
    when: (answers: Answers) =>
      (answers.method ?? "").toLowerCase() !== "existing",
  },
  {
    type: "input",
    name: "table",
    message: "What would you like to name your table?",
    default: "My Awesome Table",
  },
  {
    type: "input",
    name: "id",
    message: "What ID would you like to have for your table?",
    default: "unique()",
  },
  {
    type: "list",
    name: "rowSecurity",
    message:
      "Enable row security for configuring permissions for individual rows",
    choices: ["No", "Yes"],
  },
];

export const questionsCreateMessagingTopic: Question[] = [
  {
    type: "input",
    name: "topic",
    message: "What would you like to name your messaging topic?",
    default: "My Awesome Topic",
  },
  {
    type: "input",
    name: "id",
    message: "What ID would you like to have for your messaging topic?",
    default: "unique()",
  },
];

export const questionsPullCollection: Question[] = [
  {
    type: "checkbox",
    name: "databases",
    message: "From which database would you like to pull collections?",
    validate: (value: any) => validateRequired("collection", value),
    choices: async () => {
      let response = await (await getDatabasesService()).list();
      let databases = response["databases"];

      if (databases.length <= 0) {
        throw new Error(
          "No databases found. Please create one in project console.",
        );
      }
      let choices = databases.map((database: any, idx: number) => {
        return {
          name: `${database.name} (${database.$id})`,
          value: database.$id,
        };
      });
      return choices;
    },
  },
];

export const questionsLogin: Question[] = [
  {
    type: "list",
    name: "method",
    message: "What would you like to do?",
    choices: [
      { name: "Login to an account", value: "login" },
      { name: "Switch to an account", value: "select" },
    ],
    when: () => globalConfig.getSessions().length >= 2,
  },
  {
    type: "input",
    name: "email",
    message: "Enter your email",
    validate(value: string) {
      if (!value) {
        return "Please enter your email";
      }
      return true;
    },
    when: (answers: Answers) => answers.method !== "select",
  },
  {
    type: "password",
    name: "password",
    message: "Enter your password",
    mask: "*",
    validate(value: string) {
      if (!value) {
        return "Please enter your password";
      }
      return true;
    },
    when: (answers: Answers) => answers.method !== "select",
  },
  {
    type: "search-list",
    name: "accountId",
    message: "Select an account to use",
    choices() {
      const sessions = globalConfig.getSessions();
      const current = globalConfig.getCurrentSession();

      const data: Choice[] = [];

      const longestEmail = sessions.reduce((prev: any, current: any) =>
        prev && (prev.email ?? "").length > (current.email ?? "").length
          ? prev
          : current,
      ).email.length;

      sessions.forEach((session: any) => {
        if (session.email) {
          data.push({
            current: current === session.id,
            value: session.id,
            name: `${session.email.padEnd(longestEmail)} ${current === session.id ? chalk.green.bold("current") : " ".repeat(6)} ${session.endpoint}`,
          });
        }
      });

      return data.sort((a, b) => Number(b.current) - Number(a.current));
    },
    when: (answers: Answers) => answers.method === "select",
  },
];

export const questionGetEndpoint: Question[] = [
  {
    type: "input",
    name: "endpoint",
    message: `Enter the endpoint of your ${SDK_TITLE} server`,
    default: "http://localhost/v1",
    async validate(value: string) {
      if (!value) {
        return "Please enter a valid endpoint.";
      }
      let client = new Client().setEndpoint(value);
      try {
        let response = (await client.call(
          "get",
          new URL(value + "/health/version"),
        )) as { version?: string };
        if (response.version) {
          return true;
        } else {
          throw new Error();
        }
      } catch (error) {
        return "Invalid endpoint or your Appwrite server is not running as expected.";
      }
    },
  },
];

export const questionsLogout: Question[] = [
  {
    type: "checkbox",
    name: "accounts",
    message: "Select accounts to logout from",
    validate: (value: any) => validateRequired("account", value),
    choices() {
      const sessions = globalConfig.getSessions();
      const current = globalConfig.getCurrentSession();

      const data: Choice[] = [];

      const longestEmail = sessions.reduce((prev: any, current: any) =>
        prev && (prev.email ?? "").length > (current.email ?? "").length
          ? prev
          : current,
      ).email.length;

      sessions.forEach((session: any) => {
        if (session.email) {
          data.push({
            current: current === session.id,
            value: session.id,
            name: `${session.email.padEnd(longestEmail)} ${current === session.id ? chalk.green.bold("current") : " ".repeat(6)} ${session.endpoint}`,
          });
        }
      });

      return data.sort((a, b) => Number(b.current) - Number(a.current));
    },
  },
];

export const questionsPushResources: Question[] = [
  {
    type: "list",
    name: "resource",
    message: "Which resources would you like to push?",
    choices: [
      { name: `Settings ${chalk.blackBright(`(Project)`)}`, value: "settings" },
      {
        name: `Functions ${chalk.blackBright(`(Deployment)`)}`,
        value: "functions",
      },
      { name: `Tables ${chalk.blackBright(`(TablesDB)`)}`, value: "tables" },
      { name: `Buckets ${chalk.blackBright(`(Storage)`)}`, value: "buckets" },
      { name: `Teams ${chalk.blackBright(`(Auth)`)}`, value: "teams" },
      { name: `Topics ${chalk.blackBright(`(Messaging)`)}`, value: "messages" },
      {
        name: `Collections ${chalk.blackBright(`(Legacy Databases)`)}`,
        value: "collections",
      },
    ],
  },
];

export const questionsInitResources: Question[] = [
  {
    type: "list",
    name: "resource",
    message: "Which resource would you create?",
    choices: [
      { name: "Function", value: "function" },
      { name: "Site", value: "site" },
      { name: "Table", value: "table" },
      { name: "Bucket", value: "bucket" },
      { name: "Team", value: "team" },
      { name: "Topic", value: "message" },
      { name: "Collection", value: "collection" },
    ],
  },
];

export const questionsPushSites: Question[] = [
  {
    type: "checkbox",
    name: "sites",
    message: "Which sites would you like to push?",
    validate: (value: any) => validateRequired("site", value),
    when: () => localConfig.getSites().length > 0,
    choices: () => {
      let sites = localConfig.getSites();
      checkDeployConditions(localConfig);
      let choices = sites.map((site: any, idx: number) => {
        return {
          name: `${site.name} (${site.$id})`,
          value: site.$id,
        };
      });
      return choices;
    },
  },
];

export const questionsPushFunctions: Question[] = [
  {
    type: "checkbox",
    name: "functions",
    message: "Which functions would you like to push?",
    validate: (value: any) => validateRequired("function", value),
    when: () => localConfig.getFunctions().length > 0,
    choices: () => {
      let functions = localConfig.getFunctions();
      checkDeployConditions(localConfig);
      let choices = functions.map((func: any, idx: number) => {
        return {
          name: `${func.name} (${func.$id})`,
          value: func.$id,
        };
      });
      return choices;
    },
  },
];

export const questionsPushCollections: Question[] = [
  {
    type: "checkbox",
    name: "collections",
    message: "Which collections would you like to push?",
    validate: (value: any) => validateRequired("collection", value),
    when: () => localConfig.getCollections().length > 0,
    choices: () => {
      let collections = localConfig.getCollections();
      checkDeployConditions(localConfig);

      return collections.map((collection: any) => {
        return {
          name: `${collection.name} (${collection["databaseId"]} - ${collection["$id"]})`,
          value: `${collection["databaseId"]}|${collection["$id"]}`,
        };
      });
    },
  },
];

export const questionsPushTables: Question[] = [
  {
    type: "checkbox",
    name: "tables",
    message: "Which tables would you like to push?",
    validate: (value: any) => validateRequired("table", value),
    when: () => localConfig.getTables().length > 0,
    choices: () => {
      let tables = localConfig.getTables();
      checkDeployConditions(localConfig);

      return tables.map((table: any) => {
        return {
          name: `${table.name} (${table["databaseId"]} - ${table["$id"]})`,
          value: `${table["databaseId"]}|${table["$id"]}`,
        };
      });
    },
  },
];

export const questionPushChanges: Question[] = [
  {
    type: "input",
    name: "changes",
    message: `Are you sure you want to apply these changes? (YES/NO)`,
  },
];

export const questionPushChangesConfirmation: Question[] = [
  {
    type: "input",
    name: "changes",
    message: `Please type 'YES' or 'NO':`,
  },
];

export const questionsPushBuckets: Question[] = [
  {
    type: "checkbox",
    name: "buckets",
    message: "Which buckets would you like to push?",
    validate: (value: any) => validateRequired("bucket", value),
    when: () => localConfig.getBuckets().length > 0,
    choices: () => {
      let buckets = localConfig.getBuckets();
      checkDeployConditions(localConfig);

      return buckets.map((bucket: any) => {
        return {
          name: `${bucket.name} (${bucket["$id"]})`,
          value: bucket.$id,
        };
      });
    },
  },
];

export const questionsPushMessagingTopics: Question[] = [
  {
    type: "checkbox",
    name: "topics",
    message: "Which messaging topic would you like to push?",
    validate: (value: any) => validateRequired("topics", value),
    when: () => localConfig.getMessagingTopics().length > 0,
    choices: () => {
      let topics = localConfig.getMessagingTopics();

      return topics.map((topic: any) => {
        return {
          name: `${topic.name} (${topic["$id"]})`,
          value: topic.$id,
        };
      });
    },
  },
];

export const questionsGetEntrypoint: Question[] = [
  {
    type: "input",
    name: "entrypoint",
    message: "Enter the entrypoint",
    validate(value: string) {
      if (!value) {
        return "Please enter your entrypoint";
      }
      return true;
    },
  },
];

export const questionsPushTeams: Question[] = [
  {
    type: "checkbox",
    name: "teams",
    message: "Which teams would you like to push?",
    validate: (value: any) => validateRequired("team", value),
    when: () => localConfig.getTeams().length > 0,
    choices: () => {
      let teams = localConfig.getTeams();
      checkDeployConditions(localConfig);

      return teams.map((team: any) => {
        return {
          name: `${team.name} (${team["$id"]})`,
          value: team.$id,
        };
      });
    },
  },
];

export const questionsListFactors: Question[] = [
  {
    type: "list",
    name: "factor",
    message:
      "Your account is protected by multi-factor authentication. Please choose one for verification.",
    choices: async () => {
      let client = await sdkForConsole(false);
      const accountClient = new Account(client);
      const factors = await accountClient.listMfaFactors();

      const choices = [
        {
          name: `Authenticator app (Get a code from a third-party authenticator app)`,
          value: "totp",
        },
        {
          name: `Email (Get a security code at your ${SDK_TITLE} email address)`,
          value: "email",
        },
        {
          name: `SMS (Get a security code on your ${SDK_TITLE} phone number)`,
          value: "phone",
        },
        {
          name: `Recovery code (Use one of your recovery codes for verification)`,
          value: "recoveryCode",
        },
      ].filter((ch) => factors[ch.value] === true);

      return choices;
    },
  },
];

export const questionsMFAChallenge: Question[] = [
  {
    type: "input",
    name: "otp",
    message: "Enter OTP",
    validate(value: string) {
      if (!value) {
        return "Please enter OTP";
      }
      return true;
    },
  },
];

export const questionsRunFunctions: Question[] = [
  {
    type: "list",
    name: "function",
    message: "Which function would you like to develop locally?",
    validate: (value: any) => validateRequired("function", value),
    choices: () => {
      let functions = localConfig.getFunctions();
      if (functions.length === 0) {
        throw new Error(
          `No functions found. Use '${EXECUTABLE_NAME} pull functions' to synchronize existing one, or use '${EXECUTABLE_NAME} init function' to create a new one.`,
        );
      }
      let choices = functions.map((func: any, idx: number) => {
        return {
          name: `${func.name} (${func.$id})`,
          value: func.$id,
        };
      });
      return choices;
    },
  },
];

export const questionsCreateSite: Question[] = [
  {
    type: "input",
    name: "name",
    message: "What would you like to name your site?",
    default: "My Awesome Site",
  },
  {
    type: "input",
    name: "id",
    message: "What ID would you like to have for your site?",
    default: "unique()",
  },
  {
    type: "list",
    name: "framework",
    message: "What framework would you like to use?",
    choices: async () => {
      let response = await (await getSitesService()).listFrameworks();
      let frameworks = response["frameworks"];
      let choices = frameworks.map((framework: any) => {
        return {
          name: `${framework.name} (${framework.key})`,
          value: framework,
        };
      });
      return choices;
    },
  },
  {
    type: "list",
    name: "specification",
    message: "What specification would you like to use?",
    choices: async () => {
      let response = await (await getSitesService()).listSpecifications();
      let specifications = response["specifications"];
      let choices = specifications.map((spec: any) => {
        return {
          name: `${spec.cpus} CPU, ${spec.memory}MB RAM`,
          value: spec.slug,
          disabled: spec.enabled === false ? "Upgrade to use" : false,
        };
      });
      return choices;
    },
  },
];
