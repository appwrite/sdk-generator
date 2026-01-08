import fs from "fs";
import chalk from "chalk";
import { Command } from "commander";
import inquirer from "inquirer";
import {
  Databases,
  Functions,
  Messaging,
  Projects,
  Sites,
  Storage,
  TablesDB,
  Teams,
  Client,
  Query,
  Models,
} from "@appwrite.io/console";
import { getFunctionsService, getSitesService } from "../services.js";
import { sdkForProject, sdkForConsole } from "../sdks.js";
import { localConfig } from "../config.js";
import { paginate } from "../paginate.js";
import {
  questionsPullFunctions,
  questionsPullFunctionsCode,
  questionsPullSites,
  questionsPullSitesCode,
  questionsPullResources,
} from "../questions.js";
import {
  cliConfig,
  success,
  log,
  warn,
  error,
  actionRunner,
  commandDescriptions,
} from "../parser.js";
import type { ConfigType } from "./config.js";
import { createSettingsObject } from "../utils.js";
import { ProjectNotInitializedError } from "./errors.js";
import type { SettingsType, FunctionType, SiteType } from "./config.js";
import { downloadDeploymentCode } from "./utils/deployment.js";

export interface PullOptions {
  all?: boolean;
  settings?: boolean;
  functions?: boolean;
  sites?: boolean;
  collections?: boolean;
  tables?: boolean;
  buckets?: boolean;
  teams?: boolean;
  topics?: boolean;
  skipDeprecated?: boolean;
  withVariables?: boolean;
  noCode?: boolean;
}

interface PullFunctionsOptions {
  code?: boolean;
  withVariables?: boolean;
  functionIds?: string[];
}

interface PullSitesOptions {
  code?: boolean;
  withVariables?: boolean;
  siteIds?: string[];
}

export interface PullSettingsResult {
  projectName: string;
  settings: SettingsType;
  project: Models.Project;
}

async function createPullInstance(): Promise<Pull> {
  const projectClient = await sdkForProject();
  const consoleClient = await sdkForConsole();
  const pullInstance = new Pull(projectClient, consoleClient);

  pullInstance.setConfigDirectoryPath(localConfig.configDirectoryPath);
  return pullInstance;
}

export class Pull {
  private projectClient: Client;
  private consoleClient: Client;
  private configDirectoryPath: string;
  private silent: boolean;

  constructor(projectClient: Client, consoleClient: Client, silent = false) {
    this.projectClient = projectClient;
    this.consoleClient = consoleClient;
    this.configDirectoryPath = process.cwd();
    this.silent = silent;
  }

  /**
   * Set the base directory path for config files and resources
   */
  public setConfigDirectoryPath(path: string): void {
    this.configDirectoryPath = path;
  }

  /**
   * Log a message (respects silent mode)
   */
  private log(message: string): void {
    if (!this.silent) {
      log(message);
    }
  }

  /**
   * Log a success message (respects silent mode)
   */
  private success(message: string): void {
    if (!this.silent) {
      success(message);
    }
  }

  /**
   * Log a warning message (respects silent mode)
   */
  private warn(message: string): void {
    if (!this.silent) {
      warn(message);
    }
  }

  /**
   * Pull resources from Appwrite project and return updated config
   *
   * @param config - Current configuration object
   * @param options - Pull options specifying which resources to pull
   * @returns Updated configuration object with pulled resources
   */
  public async pullResources(
    config: ConfigType,
    options: PullOptions = { all: true, skipDeprecated: true },
  ): Promise<ConfigType> {
    const { skipDeprecated = true } = options;
    if (!config.projectId) {
      throw new ProjectNotInitializedError();
    }

    const updatedConfig: ConfigType = { ...config };
    const shouldPullAll = options.all === true;

    if (shouldPullAll || options.settings) {
      const settings = await this.pullSettings(config.projectId);
      updatedConfig.settings = settings.settings;
      updatedConfig.projectName = settings.projectName;
    }

    if (shouldPullAll || options.functions) {
      const functions = await this.pullFunctions({
        code: options.noCode === true ? false : true,
        withVariables: options.withVariables,
      });
      updatedConfig.functions = functions;
    }

    if (shouldPullAll || options.sites) {
      const sites = await this.pullSites({
        code: options.noCode === true ? false : true,
        withVariables: options.withVariables,
      });
      updatedConfig.sites = sites;
    }

    if (shouldPullAll || options.tables) {
      const { databases, tables } = await this.pullTables();
      updatedConfig.databases = databases;
      updatedConfig.tables = tables;
    }

    if (!skipDeprecated && (shouldPullAll || options.collections)) {
      const { databases, collections } = await this.pullCollections();
      updatedConfig.databases = databases;
      updatedConfig.collections = collections;
    }

    if (shouldPullAll || options.buckets) {
      const buckets = await this.pullBuckets();
      updatedConfig.buckets = buckets;
    }

    if (shouldPullAll || options.teams) {
      const teams = await this.pullTeams();
      updatedConfig.teams = teams;
    }

    if (shouldPullAll || options.topics) {
      const topics = await this.pullMessagingTopics();
      updatedConfig.topics = topics;
    }

    return updatedConfig;
  }

  /**
   * Pull project settings
   */
  public async pullSettings(projectId: string): Promise<PullSettingsResult> {
    this.log("Pulling project settings ...");

    const projectsService = new Projects(this.consoleClient);
    const project = await projectsService.get({ projectId: projectId });

    this.success(`Successfully pulled ${chalk.bold("all")} project settings.`);

    return {
      projectName: project.name,
      settings: createSettingsObject(project),
      project,
    };
  }

  /**
   * Pull functions from the project
   */
  public async pullFunctions(
    options: PullFunctionsOptions = {},
  ): Promise<FunctionType[]> {
    this.log("Fetching functions ...");

    const originalCwd = process.cwd();
    process.chdir(this.configDirectoryPath);

    try {
      const functionsService = new Functions(this.projectClient);
      let functions: Models.Function[];

      if (options.functionIds && options.functionIds.length > 0) {
        functions = await Promise.all(
          options.functionIds.map((id) =>
            functionsService.get({
              functionId: id,
            }),
          ),
        );
      } else {
        const fetchResponse = await functionsService.list({
          queries: [Query.limit(1)],
        });

        if (fetchResponse["functions"].length <= 0) {
          this.log("No functions found.");
          this.success(`Successfully pulled ${chalk.bold(0)} functions.`);
          return [];
        }

        const { functions: allFunctions } = await paginate(
          async () => new Functions(this.projectClient).list(),
          {},
          100,
          "functions",
        );
        functions = allFunctions;
      }

      const result: FunctionType[] = [];

      for (const func of functions) {
        this.log(`Pulling function ${chalk.bold(func.name)} ...`);

        const funcPath = `functions/${func.name}`;
        const holdingVars = func.vars || [];

        const functionConfig: FunctionType = {
          $id: func.$id,
          name: func.name,
          runtime: func.runtime,
          path: funcPath,
          entrypoint: func.entrypoint,
          execute: func.execute,
          enabled: func.enabled,
          logging: func.logging,
          events: func.events,
          schedule: func.schedule,
          timeout: func.timeout,
          commands: func.commands,
          scopes: func.scopes,
          specification: func.specification,
        };

        result.push(functionConfig);

        if (!fs.existsSync(funcPath)) {
          fs.mkdirSync(funcPath, { recursive: true });
        }

        if (options.code !== false) {
          await downloadDeploymentCode({
            resourceId: func["$id"],
            resourcePath: funcPath,
            holdingVars,
            withVariables: options.withVariables,
            listDeployments: () =>
              functionsService.listDeployments({
                functionId: func["$id"],
                queries: [Query.limit(1), Query.orderDesc("$id")],
              }),
            getDownloadUrl: (deploymentId) =>
              functionsService.getDeploymentDownload({
                functionId: func["$id"],
                deploymentId,
              }),
            projectClient: this.projectClient,
          });
        }
      }

      if (options.code === false) {
        this.warn("Source code download skipped.");
      }

      this.success(
        `Successfully pulled ${chalk.bold(result.length)} functions.`,
      );
      return result;
    } finally {
      process.chdir(originalCwd);
    }
  }

  /**
   * Pull sites from the project
   */
  public async pullSites(options: PullSitesOptions = {}): Promise<SiteType[]> {
    this.log("Fetching sites ...");

    const originalCwd = process.cwd();
    process.chdir(this.configDirectoryPath);

    try {
      const sitesService = new Sites(this.projectClient);
      let sites: Models.Site[];

      if (options.siteIds && options.siteIds.length > 0) {
        sites = await Promise.all(
          options.siteIds.map((id) =>
            sitesService.get({
              siteId: id,
            }),
          ),
        );
      } else {
        const fetchResponse = await sitesService.list({
          queries: [Query.limit(1)],
        });

        if (fetchResponse["sites"].length <= 0) {
          this.log("No sites found.");
          this.success(`Successfully pulled ${chalk.bold(0)} sites.`);
          return [];
        }

        const { sites: fetchedSites } = await paginate(
          async () => new Sites(this.projectClient).list(),
          {},
          100,
          "sites",
        );
        sites = fetchedSites;
      }

      const result: SiteType[] = [];

      for (const site of sites) {
        this.log(`Pulling site ${chalk.bold(site.name)} ...`);

        const sitePath = `sites/${site.name}`;
        const holdingVars = site.vars || [];

        const siteConfig: SiteType = {
          $id: site.$id,
          name: site.name,
          path: sitePath,
          framework: site.framework,
          enabled: site.enabled,
          logging: site.logging,
          timeout: site.timeout,
          buildRuntime: site.buildRuntime,
          adapter: site.adapter,
          installCommand: site.installCommand,
          buildCommand: site.buildCommand,
          outputDirectory: site.outputDirectory,
          fallbackFile: site.fallbackFile,
          specification: site.specification,
        };

        result.push(siteConfig);

        if (!fs.existsSync(sitePath)) {
          fs.mkdirSync(sitePath, { recursive: true });
        }

        if (options.code !== false) {
          await downloadDeploymentCode({
            resourceId: site["$id"],
            resourcePath: sitePath,
            holdingVars,
            withVariables: options.withVariables,
            listDeployments: () =>
              sitesService.listDeployments({
                siteId: site["$id"],
                queries: [Query.limit(1), Query.orderDesc("$id")],
              }),
            getDownloadUrl: (deploymentId) =>
              sitesService.getDeploymentDownload({
                siteId: site["$id"],
                deploymentId,
              }),
            projectClient: this.projectClient,
          });
        }
      }

      if (options.code === false) {
        this.warn("Source code download skipped.");
      }

      this.success(`Successfully pulled ${chalk.bold(result.length)} sites.`);
      return result;
    } finally {
      process.chdir(originalCwd);
    }
  }

  /**
   * Pull collections from the project (deprecated)
   */
  public async pullCollections(): Promise<{
    databases: any[];
    collections: any[];
  }> {
    this.warn(
      "appwrite pull collection has been deprecated. Please consider using 'appwrite pull tables' instead",
    );
    this.log("Fetching collections ...");

    const databasesService = new Databases(this.projectClient);

    const fetchResponse = await databasesService.list([Query.limit(1)]);

    if (fetchResponse["databases"].length <= 0) {
      this.log("No collections found.");
      this.success(
        `Successfully pulled ${chalk.bold(0)} collections from ${chalk.bold(0)} databases.`,
      );
      return { databases: [], collections: [] };
    }

    const { databases } = await paginate(
      async () => new Databases(this.projectClient).list(),
      {},
      100,
      "databases",
    );

    const allDatabases: any[] = [];
    const allCollections: any[] = [];

    for (const database of databases) {
      this.log(
        `Pulling all collections from ${chalk.bold(database.name)} database ...`,
      );
      allDatabases.push(database);

      const { collections } = await paginate(
        async () =>
          new Databases(this.projectClient).listCollections(database.$id),
        {},
        100,
        "collections",
      );

      for (const collection of collections) {
        allCollections.push({
          ...collection,
          $createdAt: undefined,
          $updatedAt: undefined,
        });
      }
    }

    this.success(
      `Successfully pulled ${chalk.bold(allCollections.length)} collections from ${chalk.bold(allDatabases.length)} databases.`,
    );

    return {
      databases: allDatabases,
      collections: allCollections,
    };
  }

  /**
   * Pull tables from the project
   */
  public async pullTables(): Promise<{
    databases: any[];
    tables: any[];
  }> {
    this.log("Fetching tables ...");

    const tablesDBService = new TablesDB(this.projectClient);

    const fetchResponse = await tablesDBService.list({
      queries: [Query.limit(1)],
    });

    if (fetchResponse["databases"].length <= 0) {
      this.log("No tables found.");
      this.success(
        `Successfully pulled ${chalk.bold(0)} tables from ${chalk.bold(0)} tableDBs.`,
      );
      return { databases: [], tables: [] };
    }

    const { databases } = await paginate(
      async () => new TablesDB(this.projectClient).list(),
      {},
      100,
      "databases",
    );

    const allDatabases: any[] = [];
    const allTables: any[] = [];

    for (const database of databases) {
      this.log(
        `Pulling all tables from ${chalk.bold(database.name)} database ...`,
      );
      allDatabases.push(database);

      const { tables } = await paginate(
        async () => new TablesDB(this.projectClient).listTables(database.$id),
        {},
        100,
        "tables",
      );

      for (const table of tables) {
        allTables.push({
          ...table,
          $createdAt: undefined,
          $updatedAt: undefined,
        });
      }
    }

    this.success(
      `Successfully pulled ${chalk.bold(allTables.length)} tables from ${chalk.bold(allDatabases.length)} tableDBs.`,
    );

    return {
      databases: allDatabases,
      tables: allTables,
    };
  }

  /**
   * Pull storage buckets from the project
   */
  public async pullBuckets(): Promise<any[]> {
    this.log("Fetching buckets ...");

    const storageService = new Storage(this.projectClient);

    const fetchResponse = await storageService.listBuckets({
      queries: [Query.limit(1)],
    });

    if (fetchResponse["buckets"].length <= 0) {
      this.log("No buckets found.");
      this.success(`Successfully pulled ${chalk.bold(0)} buckets.`);
      return [];
    }

    const { buckets } = await paginate(
      async () => new Storage(this.projectClient).listBuckets(),
      {},
      100,
      "buckets",
    );

    for (const bucket of buckets) {
      this.log(`Pulling bucket ${chalk.bold(bucket.name)} ...`);
    }

    this.success(`Successfully pulled ${chalk.bold(buckets.length)} buckets.`);

    return buckets;
  }

  /**
   * Pull teams from the project
   */
  public async pullTeams(): Promise<any[]> {
    this.log("Fetching teams ...");

    const teamsService = new Teams(this.projectClient);

    const fetchResponse = await teamsService.list({
      queries: [Query.limit(1)],
    });

    if (fetchResponse["teams"].length <= 0) {
      this.log("No teams found.");
      this.success(`Successfully pulled ${chalk.bold(0)} teams.`);
      return [];
    }

    const { teams } = await paginate(
      async () => new Teams(this.projectClient).list(),
      {},
      100,
      "teams",
    );

    for (const team of teams) {
      this.log(`Pulling team ${chalk.bold(team.name)} ...`);
    }

    this.success(`Successfully pulled ${chalk.bold(teams.length)} teams.`);

    return teams;
  }

  /**
   * Pull messaging topics from the project
   */
  public async pullMessagingTopics(): Promise<any[]> {
    this.log("Fetching topics ...");

    const messagingService = new Messaging(this.projectClient);

    const fetchResponse = await messagingService.listTopics({
      queries: [Query.limit(1)],
    });

    if (fetchResponse["topics"].length <= 0) {
      this.log("No topics found.");
      this.success(`Successfully pulled ${chalk.bold(0)} topics.`);
      return [];
    }

    const { topics } = await paginate(
      async () => new Messaging(this.projectClient).listTopics(),
      {},
      100,
      "topics",
    );

    for (const topic of topics) {
      this.log(`Pulling topic ${chalk.bold(topic.name)} ...`);
    }

    this.success(`Successfully pulled ${chalk.bold(topics.length)} topics.`);

    return topics;
  }
}

/** Helper methods for CLI commands */

export const pullResources = async ({
  skipDeprecated = true,
}: {
  skipDeprecated?: boolean;
} = {}): Promise<void> => {
  const project = localConfig.getProject();
  if (!project.projectId) {
    error(
      "Project configuration not found. Please run 'appwrite init project' to initialize your project first.",
    );
    process.exit(1);
  }

  const actions: Record<string, (options?: any) => Promise<void>> = {
    settings: pullSettings,
    functions: pullFunctions,
    sites: pullSites,
    collections: pullCollection,
    tables: pullTable,
    buckets: pullBucket,
    teams: pullTeam,
    messages: pullMessagingTopic,
  };

  if (skipDeprecated) {
    delete actions.collections;
  }

  if (cliConfig.all) {
    for (let action of Object.values(actions)) {
      cliConfig.all = true;
      await action({ returnOnZero: true });
    }
  } else {
    const answers = await inquirer.prompt([questionsPullResources[0]]);

    const action = actions[answers.resource];
    if (action !== undefined) {
      await action({ returnOnZero: true });
    }
  }
};

const pullSettings = async (): Promise<void> => {
  const pullInstance = await createPullInstance();
  const projectId = localConfig.getProject().projectId;
  const settings = await pullInstance.pullSettings(projectId);

  localConfig.setProject(projectId, settings.projectName, settings.project);
};

const pullFunctions = async ({
  code,
  withVariables,
}: PullFunctionsOptions = {}): Promise<void> => {
  const functionsService = await getFunctionsService();
  const fetchResponse = await functionsService.list([Query.limit(1)]);
  if (fetchResponse["functions"].length <= 0) {
    log("No functions found.");
    success(`Successfully pulled ${chalk.bold(0)} functions.`);
    return;
  }

  const functionsToCheck = cliConfig.all
    ? (
        await paginate(
          async () => (await getFunctionsService()).list(),
          {},
          100,
          "functions",
        )
      ).functions
    : (await inquirer.prompt(questionsPullFunctions)).functions;

  let allowCodePull: boolean | null = cliConfig.force === true ? true : null;
  if (code !== false && allowCodePull === null) {
    const codeAnswer = await inquirer.prompt(questionsPullFunctionsCode);
    allowCodePull = codeAnswer.override;
  }

  const shouldPullCode = code !== false && allowCodePull === true;
  const selectedFunctionIds = functionsToCheck.map((f: any) => f.$id);

  const pullInstance = await createPullInstance();
  const functions = await pullInstance.pullFunctions({
    code: shouldPullCode,
    withVariables,
    functionIds: selectedFunctionIds,
  });

  for (const func of functions) {
    const localFunction = localConfig.getFunction(func.$id);
    func["path"] = localFunction["path"] || func["path"];
    localConfig.addFunction(func);
  }
};

const pullSites = async ({
  code,
  withVariables,
}: PullSitesOptions = {}): Promise<void> => {
  const sitesService = await getSitesService();
  const fetchResponse = await sitesService.list({
    queries: [Query.limit(1)],
  });
  if (fetchResponse["sites"].length <= 0) {
    log("No sites found.");
    success(`Successfully pulled ${chalk.bold(0)} sites.`);
    return;
  }

  const sitesToCheck = cliConfig.all
    ? (
        await paginate(
          async () => (await getSitesService()).list(),
          {},
          100,
          "sites",
        )
      ).sites
    : (await inquirer.prompt(questionsPullSites)).sites;

  let allowCodePull: boolean | null = cliConfig.force === true ? true : null;
  if (code !== false && allowCodePull === null) {
    const codeAnswer = await inquirer.prompt(questionsPullSitesCode);
    allowCodePull = codeAnswer.override;
  }

  const shouldPullCode = code !== false && allowCodePull === true;
  const selectedSiteIds = sitesToCheck.map((s: any) => s.$id);

  const pullInstance = await createPullInstance();
  const sites = await pullInstance.pullSites({
    code: shouldPullCode,
    withVariables,
    siteIds: selectedSiteIds,
  });

  for (const site of sites) {
    const localSite = localConfig.getSite(site.$id);
    site["path"] = localSite["path"] || site["path"];
    localConfig.addSite(site);
  }
};

const pullCollection = async (): Promise<void> => {
  const pullInstance = await createPullInstance();
  const { databases, collections } = await pullInstance.pullCollections();

  for (const database of databases) {
    localConfig.addDatabase(database);
  }

  for (const collection of collections) {
    localConfig.addCollection(collection);
  }
};

const pullTable = async (): Promise<void> => {
  const pullInstance = await createPullInstance();
  const { databases, tables } = await pullInstance.pullTables();

  for (const database of databases) {
    localConfig.addTablesDB(database);
  }

  for (const table of tables) {
    localConfig.addTable(table);
  }
};

const pullBucket = async (): Promise<void> => {
  const pullInstance = await createPullInstance();
  const buckets = await pullInstance.pullBuckets();

  for (const bucket of buckets) {
    localConfig.addBucket(bucket);
  }
};

const pullTeam = async (): Promise<void> => {
  const pullInstance = await createPullInstance();
  const teams = await pullInstance.pullTeams();

  for (const team of teams) {
    localConfig.addTeam(team);
  }
};

const pullMessagingTopic = async (): Promise<void> => {
  const pullInstance = await createPullInstance();
  const topics = await pullInstance.pullMessagingTopics();

  for (const topic of topics) {
    localConfig.addMessagingTopic(topic);
  }
};

/** Commander.js exports */

export const pull = new Command("pull")
  .description(commandDescriptions["pull"])
  .action(actionRunner(() => pullResources({ skipDeprecated: true })));

pull
  .command("all")
  .description("Pull all resources")
  .action(
    actionRunner(() => {
      cliConfig.all = true;
      return pullResources({
        skipDeprecated: true,
      });
    }),
  );

pull
  .command("settings")
  .description("Pull your Appwrite project name, services and auth settings")
  .action(actionRunner(pullSettings));

pull
  .command("function")
  .alias("functions")
  .description("Pull your Appwrite cloud function")
  .option("--no-code", "Don't pull the function's code")
  .option(
    "--with-variables",
    `Pull function variables. ${chalk.red("recommend for testing purposes only")}`,
  )
  .action(actionRunner(pullFunctions));

pull
  .command("site")
  .alias("sites")
  .description("Pull your Appwrite site")
  .option("--no-code", "Don't pull the site's code")
  .option(
    "--with-variables",
    `Pull site variables. ${chalk.red("recommend for testing purposes only")}`,
  )
  .action(actionRunner(pullSites));

pull
  .command("collection")
  .alias("collections")
  .description(
    "Pull your Appwrite collections (deprecated, please use 'pull tables' instead)",
  )
  .action(actionRunner(pullCollection));

pull
  .command("table")
  .alias("tables")
  .description("Pull your Appwrite tables")
  .action(actionRunner(pullTable));

pull
  .command("bucket")
  .alias("buckets")
  .description("Pull your Appwrite buckets")
  .action(actionRunner(pullBucket));

pull
  .command("team")
  .alias("teams")
  .description("Pull your Appwrite teams")
  .action(actionRunner(pullTeam));

pull
  .command("topic")
  .alias("topics")
  .description("Pull your Appwrite messaging topics")
  .action(actionRunner(pullMessagingTopic));
