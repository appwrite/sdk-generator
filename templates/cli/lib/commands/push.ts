import fs from "fs";
import path from "path";
import { parse as parseDotenv } from "dotenv";
import chalk from "chalk";
import inquirer from "inquirer";
import { Command } from "commander";
import ID from "../id.js";
import { EXECUTABLE_NAME } from "../constants.js";
import {
  localConfig,
  globalConfig,
  KeysFunction,
  KeysSite,
  KeysTopics,
  KeysStorage,
  KeysTeams,
  KeysCollection,
  KeysTable,
} from "../config.js";
import { ConfigSchema, type SettingsType, type ConfigType } from "./config.js";
import { parseWithBetterErrors } from "./utils/error-formatter.js";
import { createSettingsObject } from "../utils.js";
import { Spinner, SPINNER_DOTS } from "../spinner.js";
import { paginate } from "../paginate.js";
import { pushDeployment } from "./utils/deployment.js";
import {
  questionsPushBuckets,
  questionsPushTeams,
  questionsPushFunctions,
  questionsPushFunctionsCode,
  questionsPushSites,
  questionsPushSitesCode,
  questionsGetEntrypoint,
  questionsPushCollections,
  questionsPushTables,
  questionsPushMessagingTopics,
  questionsPushResources,
} from "../questions.js";
import {
  cliConfig,
  actionRunner,
  success,
  warn,
  log,
  hint,
  error,
  commandDescriptions,
  drawTable,
} from "../parser.js";
import {
  getProxyService,
  getConsoleService,
  getFunctionsService,
  getSitesService,
  getDatabasesService,
  getTablesDBService,
  getStorageService,
  getMessagingService,
  getTeamsService,
  getProjectsService,
} from "../services.js";
import { sdkForProject, sdkForConsole } from "../sdks.js";
import {
  ApiService,
  AuthMethod,
  AppwriteException,
  Client,
  Query,
} from "@appwrite.io/console";
import { checkDeployConditions } from "../utils.js";
import { Pools } from "./utils/pools.js";
import { Attributes, Collection } from "./utils/attributes.js";
import {
  getConfirmation,
  approveChanges,
  getObjectChanges,
} from "./utils/change-approval.js";
import { checkAndApplyTablesDBChanges } from "./utils/database-sync.js";

const POLL_DEBOUNCE = 2000; // Milliseconds
const POLL_DEFAULT_VALUE = 30;
const DEPLOYMENT_TIMEOUT_MS = 10 * 60 * 1000; // 10 minutes

export interface PushOptions {
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
  skipConfirmation?: boolean;
  functionOptions?: {
    async?: boolean;
    code?: boolean;
    withVariables?: boolean;
  };
  siteOptions?: {
    async?: boolean;
    code?: boolean;
    withVariables?: boolean;
  };
  tableOptions?: {
    attempts?: number;
  };
}

interface PushSiteOptions {
  siteId?: string;
  async?: boolean;
  code?: boolean;
  withVariables?: boolean;
}

interface PushFunctionOptions {
  functionId?: string;
  async?: boolean;
  code?: boolean;
  withVariables?: boolean;
}

interface PushTableOptions {
  attempts?: number;
  skipConfirmation?: boolean;
}

export class Push {
  private projectClient: Client;
  private consoleClient: Client;
  private silent: boolean;

  constructor(projectClient: Client, consoleClient: Client, silent = false) {
    this.projectClient = projectClient;
    this.consoleClient = consoleClient;
    this.silent = silent;
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
   * Log an error message (respects silent mode)
   */
  private error(message: string): void {
    if (!this.silent) {
      error(message);
    }
  }

  public async pushResources(
    config: ConfigType,
    options: PushOptions = { all: true, skipDeprecated: true },
  ): Promise<{
    results: Record<string, any>;
    errors: any[];
  }> {
    const { skipDeprecated = true } = options;
    const results: Record<string, any> = {};
    const allErrors: any[] = [];
    const shouldPushAll = options.all === true;

    // Push settings
    if (
      (shouldPushAll || options.settings) &&
      (config.projectName || config.settings)
    ) {
      try {
        this.log("Pushing settings ...");
        await this.pushSettings({
          projectId: config.projectId,
          projectName: config.projectName,
          settings: config.settings,
        });
        this.success(
          `Successfully pushed ${chalk.bold("all")} project settings.`,
        );
        results.settings = { success: true };
      } catch (e: any) {
        allErrors.push(e);
        results.settings = { success: false, error: e.message };
      }
    }

    // Push buckets
    if (
      (shouldPushAll || options.buckets) &&
      config.buckets &&
      config.buckets.length > 0
    ) {
      try {
        this.log("Pushing buckets ...");
        const result = await this.pushBuckets(config.buckets);
        this.success(
          `Successfully pushed ${chalk.bold(result.successfullyPushed)} buckets.`,
        );
        results.buckets = result;
        allErrors.push(...result.errors);
      } catch (e: any) {
        allErrors.push(e);
        results.buckets = { successfullyPushed: 0, errors: [e] };
      }
    }

    // Push teams
    if (
      (shouldPushAll || options.teams) &&
      config.teams &&
      config.teams.length > 0
    ) {
      try {
        this.log("Pushing teams ...");
        const result = await this.pushTeams(config.teams);
        this.success(
          `Successfully pushed ${chalk.bold(result.successfullyPushed)} teams.`,
        );
        results.teams = result;
        allErrors.push(...result.errors);
      } catch (e: any) {
        allErrors.push(e);
        results.teams = { successfullyPushed: 0, errors: [e] };
      }
    }

    // Push messaging topics
    if (
      (shouldPushAll || options.topics) &&
      config.topics &&
      config.topics.length > 0
    ) {
      try {
        this.log("Pushing topics ...");
        const result = await this.pushMessagingTopics(config.topics);
        this.success(
          `Successfully pushed ${chalk.bold(result.successfullyPushed)} topics.`,
        );
        results.topics = result;
        allErrors.push(...result.errors);
      } catch (e: any) {
        allErrors.push(e);
        results.topics = { successfullyPushed: 0, errors: [e] };
      }
    }

    // Push functions
    if (
      (shouldPushAll || options.functions) &&
      config.functions &&
      config.functions.length > 0
    ) {
      try {
        this.log("Pushing functions ...");
        const result = await this.pushFunctions(
          config.functions,
          options.functionOptions,
        );
        this.success(
          `Successfully pushed ${chalk.bold(result.successfullyPushed)} functions.`,
        );
        results.functions = result;
        allErrors.push(...result.errors);
      } catch (e: any) {
        allErrors.push(e);
        results.functions = {
          successfullyPushed: 0,
          successfullyDeployed: 0,
          failedDeployments: [],
          errors: [e],
        };
      }
    }

    // Push sites
    if (
      (shouldPushAll || options.sites) &&
      config.sites &&
      config.sites.length > 0
    ) {
      try {
        this.log("Pushing sites ...");
        const result = await this.pushSites(config.sites, options.siteOptions);
        this.success(
          `Successfully pushed ${chalk.bold(result.successfullyPushed)} sites.`,
        );
        results.sites = result;
        allErrors.push(...result.errors);
      } catch (e: any) {
        allErrors.push(e);
        results.sites = {
          successfullyPushed: 0,
          successfullyDeployed: 0,
          failedDeployments: [],
          errors: [e],
        };
      }
    }

    // Push tables
    if (
      (shouldPushAll || options.tables) &&
      config.tables &&
      config.tables.length > 0
    ) {
      try {
        this.log("Pushing tables ...");
        const result = await this.pushTables(config.tables, {
          attempts: options.tableOptions?.attempts,
          skipConfirmation: options.skipConfirmation,
        });
        this.success(
          `Successfully pushed ${chalk.bold(result.successfullyPushed)} tables.`,
        );
        results.tables = result;
        allErrors.push(...result.errors);
      } catch (e: any) {
        allErrors.push(e);
        results.tables = { successfullyPushed: 0, errors: [e] };
      }
    }

    // Push collections (skipDeprecated only applies when pushing all, explicit collections option takes precedence)
    if (
      (options.collections || (shouldPushAll && !skipDeprecated)) &&
      config.collections &&
      config.collections.length > 0
    ) {
      try {
        this.log("Pushing collections ...");
        // Add database names to collections
        const collectionsWithDbNames = config.collections.map(
          (collection: any) => {
            const database = config.databases?.find(
              (db: any) => db.$id === collection.databaseId,
            );
            return {
              ...collection,
              databaseName: database?.name ?? collection.databaseId,
            };
          },
        );
        const result = await this.pushCollections(collectionsWithDbNames, {
          skipConfirmation: options.skipConfirmation,
        });
        this.success(
          `Successfully pushed ${chalk.bold(result.successfullyPushed)} collections.`,
        );
        results.collections = result;
        allErrors.push(...result.errors);
      } catch (e: any) {
        allErrors.push(e);
        results.collections = { successfullyPushed: 0, errors: [e] };
      }
    }

    return {
      results,
      errors: allErrors,
    };
  }

  public async pushSettings(config: {
    projectId: string;
    projectName?: string;
    settings?: SettingsType;
  }): Promise<void> {
    const projectsService = await getProjectsService(this.consoleClient);
    const projectId = config.projectId;
    const projectName = config.projectName;
    const settings = config.settings ?? {};

    if (projectName) {
      this.log("Applying project name ...");
      await projectsService.update({
        projectId: projectId,
        name: projectName,
      });
    }

    if (settings.services) {
      this.log("Applying service statuses ...");
      for (let [service, status] of Object.entries(settings.services)) {
        await projectsService.updateServiceStatus({
          projectId: projectId,
          service: service as ApiService,
          status: status,
        });
      }
    }

    if (settings.auth) {
      if (settings.auth.security) {
        this.log("Applying auth security settings ...");
        await projectsService.updateAuthDuration({
          projectId,
          duration: Number(settings.auth.security.duration),
        });
        await projectsService.updateAuthLimit({
          projectId,
          limit: Number(settings.auth.security.limit),
        });
        await projectsService.updateAuthSessionsLimit({
          projectId,
          limit: Number(settings.auth.security.sessionsLimit),
        });
        await projectsService.updateAuthPasswordDictionary({
          projectId,
          enabled: settings.auth.security.passwordDictionary,
        });
        await projectsService.updateAuthPasswordHistory({
          projectId,
          limit: Number(settings.auth.security.passwordHistory),
        });
        await projectsService.updatePersonalDataCheck({
          projectId,
          enabled: settings.auth.security.personalDataCheck,
        });
        await projectsService.updateSessionAlerts({
          projectId,
          alerts: settings.auth.security.sessionAlerts,
        });
        await projectsService.updateMockNumbers({
          projectId,
          numbers: settings.auth.security.mockNumbers,
        });
      }

      if (settings.auth.methods) {
        this.log("Applying auth methods statuses ...");
        for (let [method, status] of Object.entries(settings.auth.methods)) {
          await projectsService.updateAuthStatus({
            projectId,
            method: method as AuthMethod,
            status: status,
          });
        }
      }
    }
  }

  public async pushBuckets(buckets: any[]): Promise<{
    successfullyPushed: number;
    errors: any[];
  }> {
    let successfullyPushed = 0;
    const errors: any[] = [];

    for (const bucket of buckets) {
      try {
        this.log(`Pushing bucket ${chalk.bold(bucket["name"])} ...`);
        const storageService = await getStorageService(this.projectClient);

        try {
          await storageService.getBucket(bucket["$id"]);
          await storageService.updateBucket({
            bucketId: bucket["$id"],
            name: bucket.name,
            permissions: bucket["$permissions"],
            fileSecurity: bucket.fileSecurity,
            enabled: bucket.enabled,
            maximumFileSize: bucket.maximumFileSize,
            allowedFileExtensions: bucket.allowedFileExtensions,
            encryption: bucket.encryption,
            antivirus: bucket.antivirus,
            compression: bucket.compression,
          });
        } catch (e: unknown) {
          if (e instanceof AppwriteException && Number(e.code) === 404) {
            await storageService.createBucket({
              bucketId: bucket["$id"],
              name: bucket.name,
              permissions: bucket["$permissions"],
              fileSecurity: bucket.fileSecurity,
              enabled: bucket.enabled,
              maximumFileSize: bucket.maximumFileSize,
              allowedFileExtensions: bucket.allowedFileExtensions,
              compression: bucket.compression,
              encryption: bucket.encryption,
              antivirus: bucket.antivirus,
            });
          } else {
            throw e;
          }
        }

        successfullyPushed++;
      } catch (e: any) {
        errors.push(e);
        this.error(`Failed to push bucket ${bucket["name"]}: ${e.message}`);
      }
    }

    return {
      successfullyPushed,
      errors,
    };
  }

  public async pushTeams(teams: any[]): Promise<{
    successfullyPushed: number;
    errors: any[];
  }> {
    let successfullyPushed = 0;
    const errors: any[] = [];

    for (const team of teams) {
      try {
        this.log(`Pushing team ${chalk.bold(team["name"])} ...`);
        const teamsService = await getTeamsService(this.projectClient);

        try {
          await teamsService.get(team["$id"]);
          await teamsService.updateName({
            teamId: team["$id"],
            name: team.name,
          });
        } catch (e: unknown) {
          if (e instanceof AppwriteException && Number(e.code) === 404) {
            await teamsService.create({
              teamId: team["$id"],
              name: team.name,
            });
          } else {
            throw e;
          }
        }

        successfullyPushed++;
      } catch (e: any) {
        errors.push(e);
        this.error(`Failed to push team ${team["name"]}: ${e.message}`);
      }
    }

    return {
      successfullyPushed,
      errors,
    };
  }

  public async pushMessagingTopics(topics: any[]): Promise<{
    successfullyPushed: number;
    errors: any[];
  }> {
    let successfullyPushed = 0;
    const errors: any[] = [];

    for (const topic of topics) {
      try {
        this.log(`Pushing topic ${chalk.bold(topic["name"])} ...`);
        const messagingService = await getMessagingService(this.projectClient);

        try {
          await messagingService.getTopic(topic["$id"]);
          await messagingService.updateTopic({
            topicId: topic["$id"],
            name: topic.name,
            subscribe: topic.subscribe,
          });
        } catch (e: unknown) {
          if (e instanceof AppwriteException && Number(e.code) === 404) {
            await messagingService.createTopic({
              topicId: topic["$id"],
              name: topic.name,
              subscribe: topic.subscribe,
            });
          } else {
            throw e;
          }
        }

        this.success(`Pushed ${topic.name} ( ${topic["$id"]} )`);
        successfullyPushed++;
      } catch (e: any) {
        errors.push(e);
        this.error(`Failed to push topic ${topic["name"]}: ${e.message}`);
      }
    }

    return {
      successfullyPushed,
      errors,
    };
  }

  public async pushFunctions(
    functions: any[],
    options: {
      async?: boolean;
      code?: boolean;
      withVariables?: boolean;
    } = {},
  ): Promise<{
    successfullyPushed: number;
    successfullyDeployed: number;
    failedDeployments: any[];
    errors: any[];
  }> {
    const { async: asyncDeploy, code, withVariables } = options;

    Spinner.start(false);
    let successfullyPushed = 0;
    let successfullyDeployed = 0;
    const failedDeployments: any[] = [];
    const errors: any[] = [];

    await Promise.all(
      functions.map(async (func: any) => {
        let response: any = {};

        const ignore = func.ignore ? "appwrite.config.json" : ".gitignore";
        let functionExists = false;
        let deploymentCreated = false;

        const updaterRow = new Spinner({
          status: "",
          resource: func.name,
          id: func["$id"],
          end: `Ignoring using: ${ignore}`,
        });

        updaterRow.update({ status: "Getting" }).startSpinner(SPINNER_DOTS);
        const functionsService = await getFunctionsService(this.projectClient);
        try {
          response = await functionsService.get({ functionId: func["$id"] });
          functionExists = true;
          if (response.runtime !== func.runtime) {
            updaterRow.fail({
              errorMessage: `Runtime mismatch! (local=${func.runtime},remote=${response.runtime}) Please delete remote function or update your appwrite.config.json`,
            });
            return;
          }

          updaterRow
            .update({ status: "Updating" })
            .replaceSpinner(SPINNER_DOTS);

          response = await functionsService.update({
            functionId: func["$id"],
            name: func.name,
            runtime: func.runtime,
            execute: func.execute,
            events: func.events,
            schedule: func.schedule,
            timeout: func.timeout,
            enabled: func.enabled,
            logging: func.logging,
            entrypoint: func.entrypoint,
            commands: func.commands,
            scopes: func.scopes,
            specification: func.specification,
          });
        } catch (e: any) {
          if (Number(e.code) === 404) {
            functionExists = false;
          } else {
            errors.push(e);
            updaterRow.fail({
              errorMessage:
                e.message ?? "General error occurs please try again",
            });
            return;
          }
        }

        if (!functionExists) {
          updaterRow
            .update({ status: "Creating" })
            .replaceSpinner(SPINNER_DOTS);

          try {
            response = await functionsService.create({
              functionId: func.$id,
              name: func.name,
              runtime: func.runtime,
              execute: func.execute,
              events: func.events,
              schedule: func.schedule,
              timeout: func.timeout,
              enabled: func.enabled,
              logging: func.logging,
              entrypoint: func.entrypoint,
              commands: func.commands,
              scopes: func.scopes,
              specification: func.specification,
            });

            let domain = "";
            try {
              const consoleService = await getConsoleService(
                this.consoleClient,
              );
              const variables = await consoleService.variables();
              domain = ID.unique() + "." + variables["_APP_DOMAIN_FUNCTIONS"];
            } catch (err) {
              this.error("Error fetching console variables.");
              throw err;
            }

            try {
              const proxyService = await getProxyService(this.projectClient);
              await proxyService.createFunctionRule(domain, func.$id);
            } catch (err) {
              this.error("Error creating function rule.");
              throw err;
            }

            updaterRow.update({ status: "Created" });
          } catch (e: any) {
            errors.push(e);
            updaterRow.fail({
              errorMessage:
                e.message ?? "General error occurs please try again",
            });
            return;
          }
        }

        if (withVariables) {
          updaterRow
            .update({ status: "Updating variables" })
            .replaceSpinner(SPINNER_DOTS);

          const functionsServiceForVars = await getFunctionsService(
            this.projectClient,
          );
          const { variables } = await paginate(
            async (args: any) => {
              return await functionsServiceForVars.listVariables({
                functionId: args.functionId,
              });
            },
            {
              functionId: func["$id"],
            },
            100,
            "variables",
          );

          await Promise.all(
            variables.map(async (variable: any) => {
              const functionsServiceDel = await getFunctionsService(
                this.projectClient,
              );
              await functionsServiceDel.deleteVariable({
                functionId: func["$id"],
                variableId: variable["$id"],
              });
            }),
          );

          const envFileLocation = `${func["path"]}/.env`;
          let envVariables: Array<{ key: string; value: string }> = [];
          try {
            if (fs.existsSync(envFileLocation)) {
              const envObject = parseDotenv(
                fs.readFileSync(envFileLocation, "utf8"),
              );
              envVariables = Object.entries(envObject || {}).map(
                ([key, value]) => ({ key, value }),
              );
            }
          } catch (error) {
            envVariables = [];
          }
          await Promise.all(
            envVariables.map(async (variable) => {
              const functionsServiceCreate = await getFunctionsService(
                this.projectClient,
              );
              await functionsServiceCreate.createVariable({
                functionId: func["$id"],
                key: variable.key,
                value: variable.value,
                secret: false,
              });
            }),
          );
        }

        if (code === false) {
          successfullyPushed++;
          successfullyDeployed++;
          updaterRow.update({ status: "Pushed" });
          updaterRow.stopSpinner();
          return;
        }

        if (!func.path) {
          errors.push(
            new Error(`Function '${func.name}' has no path configured`),
          );
          updaterRow.fail({
            errorMessage: `No path configured for function`,
          });
          return;
        }

        if (
          !fs.existsSync(func.path) ||
          fs.readdirSync(func.path).length === 0
        ) {
          errors.push(
            new Error(`Deployment not found or empty at path: ${func.path}`),
          );
          updaterRow.fail({
            errorMessage: `path not found or empty: ${path.relative(process.cwd(), path.resolve(func.path))}`,
          });
          return;
        }

        try {
          updaterRow.update({ status: "Pushing" }).replaceSpinner(SPINNER_DOTS);
          const functionsServiceDeploy = await getFunctionsService(
            this.projectClient,
          );

          const result = await pushDeployment({
            resourcePath: func.path,
            createDeployment: async (codeFile) => {
              return await functionsServiceDeploy.createDeployment({
                functionId: func["$id"],
                entrypoint: func.entrypoint,
                commands: func.commands,
                code: codeFile,
                activate: true,
              });
            },
            pollForStatus: false,
          });

          response = result.deployment;
          updaterRow.update({ status: "Pushed" });

          deploymentCreated = true;
          successfullyPushed++;
        } catch (e: any) {
          errors.push(e);

          switch (e.code) {
            case "ENOENT":
              updaterRow.fail({
                errorMessage: `Deployment not found at path: ${path.resolve(func.path)}`,
              });
              break;
            default:
              updaterRow.fail({
                errorMessage:
                  e.message ?? "An unknown error occurred. Please try again.",
              });
          }
        }

        if (deploymentCreated && !asyncDeploy) {
          try {
            const deploymentId = response["$id"];
            updaterRow.update({
              status: "Deploying",
              end: "Checking deployment status...",
            });

            const timeoutDeadline = Date.now() + DEPLOYMENT_TIMEOUT_MS;

            while (true) {
              if (Date.now() > timeoutDeadline) {
                failedDeployments.push({
                  name: func["name"],
                  $id: func["$id"],
                  deployment: deploymentId,
                });
                updaterRow.fail({
                  errorMessage: "Deployment timed out after 10 minutes",
                });
                break;
              }

              const functionsServicePoll = await getFunctionsService(
                this.projectClient,
              );
              response = await functionsServicePoll.getDeployment({
                functionId: func["$id"],
                deploymentId: deploymentId,
              });

              const status = response["status"];
              if (status === "ready") {
                successfullyDeployed++;

                let url = "";
                const proxyServiceUrl = await getProxyService(
                  this.projectClient,
                );
                const res = await proxyServiceUrl.listRules({
                  queries: [
                    Query.limit(1),
                    Query.equal("deploymentResourceType", "function"),
                    Query.equal("deploymentResourceId", func["$id"]),
                    Query.equal("trigger", "manual"),
                  ],
                });

                if (Number(res.total) === 1) {
                  url = `https://${res.rules[0].domain}`;
                }

                updaterRow.update({ status: "Deployed", end: url });

                break;
              } else if (status === "failed") {
                failedDeployments.push({
                  name: func["name"],
                  $id: func["$id"],
                  deployment: response["$id"],
                });
                updaterRow.fail({ errorMessage: `Failed to deploy` });

                break;
              } else {
                updaterRow.update({
                  status: "Deploying",
                  end: `Current status: ${status}`,
                });
              }

              await new Promise((resolve) =>
                setTimeout(resolve, POLL_DEBOUNCE),
              );
            }
          } catch (e: any) {
            errors.push(e);
            updaterRow.fail({
              errorMessage:
                e.message ?? "Unknown error occurred. Please try again",
            });
          }
        }

        updaterRow.stopSpinner();
      }),
    );

    Spinner.stop();

    return {
      successfullyPushed,
      successfullyDeployed,
      failedDeployments,
      errors,
    };
  }

  public async pushSites(
    sites: any[],
    options: {
      async?: boolean;
      code?: boolean;
      withVariables?: boolean;
    } = {},
  ): Promise<{
    successfullyPushed: number;
    successfullyDeployed: number;
    failedDeployments: any[];
    errors: any[];
  }> {
    const { async: asyncDeploy, code, withVariables } = options;

    Spinner.start(false);
    let successfullyPushed = 0;
    let successfullyDeployed = 0;
    const failedDeployments: any[] = [];
    const errors: any[] = [];

    await Promise.all(
      sites.map(async (site: any) => {
        let response: any = {};

        const ignore = site.ignore ? "appwrite.config.json" : ".gitignore";
        let siteExists = false;
        let deploymentCreated = false;

        const updaterRow = new Spinner({
          status: "",
          resource: site.name,
          id: site["$id"],
          end: `Ignoring using: ${ignore}`,
        });

        updaterRow.update({ status: "Getting" }).startSpinner(SPINNER_DOTS);

        const sitesService = await getSitesService(this.projectClient);
        try {
          response = await sitesService.get({ siteId: site["$id"] });
          siteExists = true;
          if (response.framework !== site.framework) {
            updaterRow.fail({
              errorMessage: `Framework mismatch! (local=${site.framework},remote=${response.framework}) Please delete remote site or update your appwrite.config.json`,
            });
            return;
          }

          updaterRow
            .update({ status: "Updating" })
            .replaceSpinner(SPINNER_DOTS);

          response = await sitesService.update({
            siteId: site["$id"],
            name: site.name,
            framework: site.framework,
            enabled: site.enabled,
            logging: site.logging,
            timeout: site.timeout,
            installCommand: site.installCommand,
            buildCommand: site.buildCommand,
            outputDirectory: site.outputDirectory,
            buildRuntime: site.buildRuntime,
            adapter: site.adapter,
            specification: site.specification,
          });
        } catch (e: any) {
          if (Number(e.code) === 404) {
            siteExists = false;
          } else {
            errors.push(e);
            updaterRow.fail({
              errorMessage:
                e.message ?? "General error occurs please try again",
            });
            return;
          }
        }

        if (!siteExists) {
          updaterRow
            .update({ status: "Creating" })
            .replaceSpinner(SPINNER_DOTS);

          try {
            response = await sitesService.create({
              siteId: site.$id,
              name: site.name,
              framework: site.framework,
              enabled: site.enabled,
              logging: site.logging,
              timeout: site.timeout,
              installCommand: site.installCommand,
              buildCommand: site.buildCommand,
              outputDirectory: site.outputDirectory,
              buildRuntime: site.buildRuntime,
              adapter: site.adapter,
              specification: site.specification,
            });

            let domain = "";
            try {
              const consoleService = await getConsoleService(
                this.consoleClient,
              );
              const variables = await consoleService.variables();
              domain = ID.unique() + "." + variables["_APP_DOMAIN_SITES"];
            } catch (err) {
              this.error("Error fetching console variables.");
              throw err;
            }

            try {
              const proxyService = await getProxyService(this.projectClient);
              await proxyService.createSiteRule(domain, site.$id);
            } catch (err) {
              this.error("Error creating site rule.");
              throw err;
            }

            updaterRow.update({ status: "Created" });
          } catch (e: any) {
            errors.push(e);
            updaterRow.fail({
              errorMessage:
                e.message ?? "General error occurs please try again",
            });
            return;
          }
        }

        if (withVariables) {
          updaterRow
            .update({ status: "Creating variables" })
            .replaceSpinner(SPINNER_DOTS);

          const sitesServiceForVars = await getSitesService(this.projectClient);
          const { variables } = await paginate(
            async (args: any) => {
              return await sitesServiceForVars.listVariables({
                siteId: args.siteId,
              });
            },
            {
              siteId: site["$id"],
            },
            100,
            "variables",
          );

          await Promise.all(
            variables.map(async (variable: any) => {
              const sitesServiceDel = await getSitesService(this.projectClient);
              await sitesServiceDel.deleteVariable({
                siteId: site["$id"],
                variableId: variable["$id"],
              });
            }),
          );

          const envFileLocation = `${site["path"]}/.env`;
          let envVariables: Array<{ key: string; value: string }> = [];
          try {
            if (fs.existsSync(envFileLocation)) {
              const envObject = parseDotenv(
                fs.readFileSync(envFileLocation, "utf8"),
              );
              envVariables = Object.entries(envObject || {}).map(
                ([key, value]) => ({ key, value }),
              );
            }
          } catch (error) {
            envVariables = [];
          }
          await Promise.all(
            envVariables.map(async (variable) => {
              const sitesServiceCreate = await getSitesService(
                this.projectClient,
              );
              await sitesServiceCreate.createVariable({
                siteId: site["$id"],
                key: variable.key,
                value: variable.value,
                secret: false,
              });
            }),
          );
        }

        if (code === false) {
          successfullyPushed++;
          successfullyDeployed++;
          updaterRow.update({ status: "Pushed" });
          updaterRow.stopSpinner();
          return;
        }

        if (!site.path) {
          errors.push(new Error(`Site '${site.name}' has no path configured`));
          updaterRow.fail({
            errorMessage: `No path configured for site`,
          });
          return;
        }

        if (
          !fs.existsSync(site.path) ||
          fs.readdirSync(site.path).length === 0
        ) {
          errors.push(
            new Error(`Deployment not found or empty at path: ${site.path}`),
          );
          updaterRow.fail({
            errorMessage: `path not found or empty: ${path.relative(process.cwd(), path.resolve(site.path))}`,
          });
          return;
        }

        try {
          updaterRow.update({ status: "Pushing" }).replaceSpinner(SPINNER_DOTS);
          const sitesServiceDeploy = await getSitesService(this.projectClient);

          const result = await pushDeployment({
            resourcePath: site.path,
            createDeployment: async (codeFile) => {
              return await sitesServiceDeploy.createDeployment({
                siteId: site["$id"],
                installCommand: site.installCommand,
                buildCommand: site.buildCommand,
                outputDirectory: site.outputDirectory,
                code: codeFile,
                activate: true,
              });
            },
            pollForStatus: false,
          });

          response = result.deployment;
          updaterRow.update({ status: "Pushed" });
          deploymentCreated = true;
          successfullyPushed++;
        } catch (e: any) {
          errors.push(e);

          switch (e.code) {
            case "ENOENT":
              updaterRow.fail({
                errorMessage: `Deployment not found at path: ${path.resolve(site.path)}`,
              });
              break;
            default:
              updaterRow.fail({
                errorMessage:
                  e.message ?? "An unknown error occurred. Please try again.",
              });
          }
        }

        if (deploymentCreated && !asyncDeploy) {
          try {
            const deploymentId = response["$id"];
            updaterRow.update({
              status: "Deploying",
              end: "Checking deployment status...",
            });

            const timeoutDeadline = Date.now() + DEPLOYMENT_TIMEOUT_MS;

            while (true) {
              if (Date.now() > timeoutDeadline) {
                failedDeployments.push({
                  name: site["name"],
                  $id: site["$id"],
                  deployment: deploymentId,
                });
                updaterRow.fail({
                  errorMessage: "Deployment timed out after 10 minutes",
                });
                break;
              }

              const sitesServicePoll = await getSitesService(
                this.projectClient,
              );
              response = await sitesServicePoll.getDeployment({
                siteId: site["$id"],
                deploymentId: deploymentId,
              });

              const status = response["status"];
              if (status === "ready") {
                successfullyDeployed++;

                let url = "";
                const proxyServiceUrl = await getProxyService(
                  this.projectClient,
                );
                const res = await proxyServiceUrl.listRules({
                  queries: [
                    Query.limit(1),
                    Query.equal("deploymentResourceType", "site"),
                    Query.equal("deploymentResourceId", site["$id"]),
                    Query.equal("trigger", "manual"),
                  ],
                });

                if (Number(res.total) === 1) {
                  url = `https://${res.rules[0].domain}`;
                }

                updaterRow.update({ status: "Deployed", end: url });

                break;
              } else if (status === "failed") {
                failedDeployments.push({
                  name: site["name"],
                  $id: site["$id"],
                  deployment: response["$id"],
                });
                updaterRow.fail({ errorMessage: `Failed to deploy` });

                break;
              } else {
                updaterRow.update({
                  status: "Deploying",
                  end: `Current status: ${status}`,
                });
              }

              await new Promise((resolve) =>
                setTimeout(resolve, POLL_DEBOUNCE),
              );
            }
          } catch (e: any) {
            errors.push(e);
            updaterRow.fail({
              errorMessage:
                e.message ?? "Unknown error occurred. Please try again",
            });
          }
        }

        updaterRow.stopSpinner();
      }),
    );

    Spinner.stop();

    return {
      successfullyPushed,
      successfullyDeployed,
      failedDeployments,
      errors,
    };
  }

  public async pushTables(
    tables: any[],
    options: PushTableOptions = {},
  ): Promise<{
    successfullyPushed: number;
    errors: any[];
  }> {
    const { attempts, skipConfirmation = false } = options;
    const pollMaxDebounces = attempts ?? POLL_DEFAULT_VALUE;
    const pools = new Pools(pollMaxDebounces);
    const attributes = new Attributes(pools, skipConfirmation);

    let tablesChanged = new Set();
    const errors: any[] = [];

    // Parallel tables actions
    await Promise.all(
      tables.map(async (table: any) => {
        try {
          const tablesService = await getTablesDBService(this.projectClient);
          const remoteTable = await tablesService.getTable({
            databaseId: table["databaseId"],
            tableId: table["$id"],
          });

          const changes: string[] = [];
          if (remoteTable.name !== table.name) changes.push("name");
          if (remoteTable.rowSecurity !== table.rowSecurity)
            changes.push("rowSecurity");
          if (remoteTable.enabled !== table.enabled) changes.push("enabled");
          if (
            JSON.stringify(remoteTable["$permissions"]) !==
            JSON.stringify(table["$permissions"])
          )
            changes.push("permissions");

          if (changes.length > 0) {
            await tablesService.updateTable({
              databaseId: table["databaseId"],
              tableId: table["$id"],
              name: table.name,
              rowSecurity: table.rowSecurity,
              permissions: table["$permissions"],
            });

            this.success(
              `Updated ${table.name} ( ${table["$id"]} ) - ${changes.join(", ")}`,
            );
            tablesChanged.add(table["$id"]);
          }
          table.remoteVersion = remoteTable;

          table.isExisted = true;
        } catch (e: any) {
          if (Number(e.code) === 404) {
            this.log(
              `Table ${table.name} does not exist in the project. Creating ... `,
            );
            const tablesService = await getTablesDBService(this.projectClient);
            await tablesService.createTable({
              databaseId: table["databaseId"],
              tableId: table["$id"],
              name: table.name,
              rowSecurity: table.rowSecurity,
              permissions: table["$permissions"]
                ? [...table["$permissions"]]
                : undefined,
            });

            this.success(`Created ${table.name} ( ${table["$id"]} )`);
            tablesChanged.add(table["$id"]);
          } else {
            errors.push(e);
            throw e;
          }
        }
      }),
    );

    // Serialize attribute actions
    for (let table of tables) {
      let columns = table.columns;
      let indexes = table.indexes;

      if (table.isExisted) {
        columns = await attributes.attributesToCreate(
          table.remoteVersion.columns,
          table.columns,
          table as Collection,
        );
        indexes = await attributes.attributesToCreate(
          table.remoteVersion.indexes,
          table.indexes,
          table as Collection,
          true,
        );

        if (
          Array.isArray(columns) &&
          columns.length <= 0 &&
          Array.isArray(indexes) &&
          indexes.length <= 0
        ) {
          continue;
        }
      }

      this.log(
        `Pushing table ${table.name} ( ${table["databaseId"]} - ${table["$id"]} ) attributes`,
      );

      try {
        await attributes.createColumns(columns, table as Collection);
      } catch (e) {
        errors.push(e);
        throw e;
      }

      try {
        await attributes.createIndexes(indexes, table as Collection);
      } catch (e) {
        errors.push(e);
        throw e;
      }
      tablesChanged.add(table["$id"]);
      this.success(`Successfully pushed ${table.name} ( ${table["$id"]} )`);
    }

    return {
      successfullyPushed: tablesChanged.size,
      errors,
    };
  }

  public async pushCollections(
    collections: any[],
    options: { skipConfirmation?: boolean } = {},
  ): Promise<{
    successfullyPushed: number;
    errors: any[];
  }> {
    const { skipConfirmation = false } = options;
    const pools = new Pools(POLL_DEFAULT_VALUE);
    const attributes = new Attributes(pools, skipConfirmation);

    const errors: any[] = [];

    const databases = Array.from(
      new Set(collections.map((collection: any) => collection["databaseId"])),
    );

    // Parallel db actions
    await Promise.all(
      databases.map(async (databaseId: any) => {
        const databasesService = await getDatabasesService(this.projectClient);
        try {
          const database = await databasesService.get(databaseId);

          // Note: We can't get the local database name here since we don't have access to localConfig
          // This will need to be handled by the caller if needed
          const localDatabaseName =
            collections.find((c: any) => c.databaseId === databaseId)
              ?.databaseName ?? databaseId;

          if (database.name !== localDatabaseName) {
            await databasesService.update(databaseId, localDatabaseName);

            this.success(`Updated ${localDatabaseName} ( ${databaseId} ) name`);
          }
        } catch (err: any) {
          if (Number(err.code) === 404) {
            this.log(`Database ${databaseId} not found. Creating it now ...`);

            const localDatabaseName =
              collections.find((c: any) => c.databaseId === databaseId)
                ?.databaseName ?? databaseId;

            await databasesService.create(databaseId, localDatabaseName);
          } else {
            throw err;
          }
        }
      }),
    );

    // Parallel collection actions
    await Promise.all(
      collections.map(async (collection: any) => {
        try {
          const databasesService = await getDatabasesService(
            this.projectClient,
          );
          const remoteCollection = await databasesService.getCollection(
            collection["databaseId"],
            collection["$id"],
          );

          if (remoteCollection.name !== collection.name) {
            await databasesService.updateCollection(
              collection["databaseId"],
              collection["$id"],
              collection.name,
            );

            this.success(
              `Updated ${collection.name} ( ${collection["$id"]} ) name`,
            );
          }
          collection.remoteVersion = remoteCollection;

          collection.isExisted = true;
        } catch (e: any) {
          if (Number(e.code) === 404) {
            this.log(
              `Collection ${collection.name} does not exist in the project. Creating ... `,
            );
            const databasesService = await getDatabasesService(
              this.projectClient,
            );
            await databasesService.createCollection({
              databaseId: collection["databaseId"],
              collectionId: collection["$id"],
              name: collection.name,
              documentSecurity: collection.documentSecurity,
              permissions: collection["$permissions"],
            });
          } else {
            errors.push(e);
            throw e;
          }
        }
      }),
    );

    let numberOfCollections = 0;
    // Serialize attribute actions
    for (let collection of collections) {
      let collectionAttributes = collection.attributes;
      let indexes = collection.indexes;

      if (collection.isExisted) {
        collectionAttributes = await attributes.attributesToCreate(
          collection.remoteVersion.attributes,
          collection.attributes,
          collection as Collection,
        );
        indexes = await attributes.attributesToCreate(
          collection.remoteVersion.indexes,
          collection.indexes,
          collection as Collection,
          true,
        );

        if (
          Array.isArray(collectionAttributes) &&
          collectionAttributes.length <= 0 &&
          Array.isArray(indexes) &&
          indexes.length <= 0
        ) {
          continue;
        }
      }

      this.log(
        `Pushing collection ${collection.name} ( ${collection["databaseId"]} - ${collection["$id"]} ) attributes`,
      );

      try {
        await attributes.createAttributes(
          collectionAttributes,
          collection as Collection,
        );
      } catch (e) {
        errors.push(e);
        throw e;
      }

      try {
        await attributes.createIndexes(indexes, collection as Collection);
      } catch (e) {
        errors.push(e);
        throw e;
      }
      numberOfCollections++;
      this.success(
        `Successfully pushed ${collection.name} ( ${collection["$id"]} )`,
      );
    }

    return {
      successfullyPushed: numberOfCollections,
      errors,
    };
  }
}

async function createPushInstance(silent = false): Promise<Push> {
  const projectClient = await sdkForProject();
  const consoleClient = await sdkForConsole();
  return new Push(projectClient, consoleClient, silent);
}

const pushResources = async ({
  skipDeprecated = false,
}: {
  skipDeprecated?: boolean;
} = {}): Promise<void> => {
  if (cliConfig.all) {
    checkDeployConditions(localConfig);

    const functions = localConfig.getFunctions();
    let allowFunctionsCodePush: boolean | null =
      cliConfig.force === true ? true : null;
    if (functions.length > 0 && allowFunctionsCodePush === null) {
      const codeAnswer = await inquirer.prompt(questionsPushFunctionsCode);
      allowFunctionsCodePush = codeAnswer.override;
    }

    const sites = localConfig.getSites();
    let allowSitesCodePush: boolean | null =
      cliConfig.force === true ? true : null;
    if (sites.length > 0 && allowSitesCodePush === null) {
      const codeAnswer = await inquirer.prompt(questionsPushSitesCode);
      allowSitesCodePush = codeAnswer.override;
    }

    const pushInstance = await createPushInstance();
    const project = localConfig.getProject();
    const config: ConfigType = {
      projectId: project.projectId ?? "",
      projectName: project.projectName,
      settings: project.projectSettings,
      functions,
      sites,
      collections: localConfig.getCollections(),
      databases: localConfig.getDatabases(),
      tables: localConfig.getTables(),
      tablesDB: localConfig.getTablesDBs(),
      buckets: localConfig.getBuckets(),
      teams: localConfig.getTeams(),
      topics: localConfig.getMessagingTopics(),
    };

    parseWithBetterErrors<ConfigType>(
      ConfigSchema,
      config,
      "Configuration validation failed",
      config,
    );

    await pushInstance.pushResources(config, {
      all: cliConfig.all,
      skipDeprecated,
      functionOptions: {
        code: allowFunctionsCodePush === true,
        withVariables: false,
      },
      siteOptions: { code: allowSitesCodePush === true, withVariables: false },
    });
  } else {
    const actions: Record<string, (options?: any) => Promise<void>> = {
      settings: pushSettings,
      functions: pushFunction,
      sites: pushSite,
      collections: pushCollection,
      tables: pushTable,
      buckets: pushBucket,
      teams: pushTeam,
      messages: pushMessagingTopic,
    };

    if (skipDeprecated) {
      delete actions.collections;
    }

    const answers = await inquirer.prompt(questionsPushResources);

    const action = actions[answers.resource];
    if (action !== undefined) {
      await action();
    }
  }
};

const pushSettings = async (): Promise<void> => {
  checkDeployConditions(localConfig);

  try {
    const projectsService = await getProjectsService();
    let response = await projectsService.get(
      localConfig.getProject().projectId,
    );

    const remoteSettings = createSettingsObject(response);
    const localSettings = localConfig.getProject().projectSettings ?? {};

    log("Checking for changes ...");
    const changes: any[] = [];

    changes.push(
      ...getObjectChanges(remoteSettings, localSettings, "services", "Service"),
    );
    changes.push(
      ...getObjectChanges(
        remoteSettings["auth"] ?? {},
        localSettings["auth"] ?? {},
        "methods",
        "Auth method",
      ),
    );
    changes.push(
      ...getObjectChanges(
        remoteSettings["auth"] ?? {},
        localSettings["auth"] ?? {},
        "security",
        "Auth security",
      ),
    );

    if (changes.length > 0) {
      drawTable(changes);
      if ((await getConfirmation()) !== true) {
        success(`Successfully pushed 0 project settings.`);
        return;
      }
    }
  } catch (e) {}

  try {
    log("Pushing project settings ...");

    const pushInstance = await createPushInstance();
    const config = localConfig.getProject();

    await pushInstance.pushSettings({
      projectId: config.projectId,
      projectName: config.projectName,
      settings: config.projectSettings,
    });

    success(`Successfully pushed ${chalk.bold("all")} project settings.`);
  } catch (e) {
    throw e;
  }
};

const pushSite = async ({
  siteId,
  async: asyncDeploy,
  code,
  withVariables,
}: PushSiteOptions = {}): Promise<void> => {
  process.chdir(localConfig.configDirectoryPath);

  const siteIds: string[] = [];

  if (siteId) {
    siteIds.push(siteId);
  } else if (cliConfig.all) {
    checkDeployConditions(localConfig);
    const sites = localConfig.getSites();
    siteIds.push(
      ...sites.map((site: any) => {
        return site.$id;
      }),
    );
  }

  if (siteIds.length <= 0) {
    const answers = await inquirer.prompt(questionsPushSites);
    if (answers.sites) {
      siteIds.push(...answers.sites);
    }
  }

  if (siteIds.length === 0) {
    log("No sites found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull sites' to synchronize existing one, or use '${EXECUTABLE_NAME} init site' to create a new one.`,
    );
    return;
  }

  let sites = siteIds.map((id: string) => {
    const sites = localConfig.getSites();
    const site = sites.find((s: any) => s.$id === id);

    if (!site) {
      throw new Error("Site '" + id + "' not found.");
    }

    return site;
  });

  log("Validating sites ...");
  // Validation is done BEFORE pushing so the deployment process can be run in async with progress update
  for (let site of sites) {
    if (!site.buildCommand) {
      log(`Site ${site.name} is missing build command.`);
      const answers = await inquirer.prompt(questionsGetEntrypoint);
      site.buildCommand = answers.entrypoint;
      localConfig.addSite(site);
    }
  }

  if (
    !(await approveChanges(
      sites,
      async (args: any) => {
        const sitesService = await getSitesService();
        return await sitesService.get({ siteId: args.siteId });
      },
      KeysSite,
      "siteId",
      "sites",
      ["vars"],
    ))
  ) {
    return;
  }

  let allowCodePush: boolean | null = cliConfig.force === true ? true : null;
  if (code !== false && allowCodePush === null) {
    const codeAnswer = await inquirer.prompt(questionsPushSitesCode);
    allowCodePush = codeAnswer.override;
  }

  const shouldPushCode = code !== false && allowCodePush === true;

  log("Pushing sites ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushSites(sites, {
    async: asyncDeploy,
    code: shouldPushCode,
    withVariables,
  });

  const {
    successfullyPushed,
    successfullyDeployed,
    failedDeployments,
    errors,
  } = result;

  failedDeployments.forEach((failed) => {
    const { name, deployment, $id } = failed;
    const failUrl = `${globalConfig.getEndpoint().slice(0, -3)}/console/project-${localConfig.getProject().projectId}/sites/site-${$id}/deployments/deployment-${deployment}`;

    error(
      `Deployment of ${name} has failed. Check at ${failUrl} for more details\n`,
    );
  });

  if (!asyncDeploy) {
    if (successfullyPushed === 0) {
      error("No sites were pushed.");
    } else if (successfullyDeployed !== successfullyPushed) {
      warn(
        `Successfully pushed ${successfullyDeployed} of ${successfullyPushed} sites`,
      );
    } else {
      success(`Successfully pushed ${successfullyPushed} sites.`);
    }
  } else {
    success(`Successfully pushed ${successfullyPushed} sites.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => {
      console.error(e);
    });
  }
};

const pushFunction = async ({
  functionId,
  async: asyncDeploy,
  code,
  withVariables,
}: PushFunctionOptions = {}): Promise<void> => {
  process.chdir(localConfig.configDirectoryPath);

  const functionIds: string[] = [];

  if (functionId) {
    functionIds.push(functionId);
  } else if (cliConfig.all) {
    checkDeployConditions(localConfig);
    const functions = localConfig.getFunctions();
    functionIds.push(
      ...functions.map((func: any) => {
        return func.$id;
      }),
    );
  }

  if (functionIds.length <= 0) {
    const answers = await inquirer.prompt(questionsPushFunctions);
    if (answers.functions) {
      functionIds.push(...answers.functions);
    }
  }

  if (functionIds.length === 0) {
    log("No functions found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull functions' to synchronize existing one, or use '${EXECUTABLE_NAME} init function' to create a new one.`,
    );
    return;
  }

  let functions = functionIds.map((id: string) => {
    const functions = localConfig.getFunctions();
    const func = functions.find((f: any) => f.$id === id);

    if (!func) {
      throw new Error("Function '" + id + "' not found.");
    }

    return func;
  });

  log("Validating functions ...");
  for (let func of functions) {
    if (!func.entrypoint) {
      log(`Function ${func.name} is missing an entrypoint.`);
      const answers = await inquirer.prompt(questionsGetEntrypoint);
      func.entrypoint = answers.entrypoint;
      localConfig.addFunction(func);
    }
  }

  if (
    !(await approveChanges(
      functions,
      async (args: any) => {
        const functionsService = await getFunctionsService();
        return await functionsService.get({ functionId: args.functionId });
      },
      KeysFunction,
      "functionId",
      "functions",
      ["vars"],
    ))
  ) {
    return;
  }

  let allowCodePush: boolean | null = cliConfig.force === true ? true : null;
  if (code !== false && allowCodePush === null) {
    const codeAnswer = await inquirer.prompt(questionsPushFunctionsCode);
    allowCodePush = codeAnswer.override;
  }

  const shouldPushCode = code !== false && allowCodePush === true;

  log("Pushing functions ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushFunctions(functions, {
    async: asyncDeploy,
    code: shouldPushCode,
    withVariables,
  });

  const {
    successfullyPushed,
    successfullyDeployed,
    failedDeployments,
    errors,
  } = result;

  failedDeployments.forEach((failed) => {
    const { name, deployment, $id } = failed;
    const failUrl = `${globalConfig.getEndpoint().slice(0, -3)}/console/project-${localConfig.getProject().projectId}/functions/function-${$id}/deployment-${deployment}`;

    error(
      `Deployment of ${name} has failed. Check at ${failUrl} for more details\n`,
    );
  });

  if (!asyncDeploy) {
    if (successfullyPushed === 0) {
      error("No functions were pushed.");
    } else if (successfullyDeployed !== successfullyPushed) {
      warn(
        `Successfully pushed ${successfullyDeployed} of ${successfullyPushed} functions`,
      );
    } else {
      success(`Successfully pushed ${successfullyPushed} functions.`);
    }
  } else {
    success(`Successfully pushed ${successfullyPushed} functions.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => {
      console.error(e);
    });
  }
};

const pushTable = async ({
  attempts,
}: PushTableOptions = {}): Promise<void> => {
  const tables: any[] = [];

  const { resyncNeeded } = await checkAndApplyTablesDBChanges();
  if (resyncNeeded) {
    log("Resyncing configuration due to tables deletions ...");

    const remoteTablesDBs = (
      await paginate(
        async (args: any) => {
          const tablesService = await getTablesDBService();
          return await tablesService.list(args.queries || []);
        },
        {},
        100,
        "databases",
      )
    ).databases;
    const localTablesDBs = localConfig.getTablesDBs();

    const remoteDatabaseIds = new Set(remoteTablesDBs.map((db: any) => db.$id));
    const localTables = localConfig.getTables();
    const validTables = localTables.filter((table: any) =>
      remoteDatabaseIds.has(table.databaseId),
    );

    localConfig.set("tables", validTables);

    const validTablesDBs = localTablesDBs.filter((db: any) =>
      remoteDatabaseIds.has(db.$id),
    );
    localConfig.set("tablesDB", validTablesDBs);

    success("Configuration resynced successfully.");
    console.log();
  }

  log("Checking for deleted tables ...");
  const localTablesDBs = localConfig.getTablesDBs();
  const localTables = localConfig.getTables();
  const tablesToDelete: any[] = [];

  for (const db of localTablesDBs) {
    try {
      const { tables: remoteTables } = await paginate(
        async (args: any) => {
          const tablesService = await getTablesDBService();
          return await tablesService.listTables(
            args.databaseId,
            args.queries || [],
          );
        },
        {
          databaseId: db.$id,
        },
        100,
        "tables",
      );

      for (const remoteTable of remoteTables) {
        const localTable = localTables.find(
          (t: any) => t.$id === remoteTable.$id && t.databaseId === db.$id,
        );
        if (!localTable) {
          tablesToDelete.push({
            ...remoteTable,
            databaseId: db.$id,
            databaseName: db.name,
          });
        }
      }
    } catch (e) {
      // Skip if database doesn't exist or other errors
    }
  }

  if (tablesToDelete.length > 0) {
    log("Found tables that exist remotely but not locally:");
    const deletionChanges = tablesToDelete.map((table: any) => ({
      id: table.$id,
      action: chalk.red("deleting"),
      key: "Table",
      database: table.databaseName,
      remote: table.name,
      local: "(deleted locally)",
    }));
    drawTable(deletionChanges);

    if ((await getConfirmation()) === true) {
      for (const table of tablesToDelete) {
        try {
          log(
            `Deleting table ${table.name} ( ${table.$id} ) from database ${table.databaseName} ...`,
          );
          const tablesService = await getTablesDBService();
          await tablesService.deleteTable(table.databaseId, table.$id);
          success(`Deleted ${table.name} ( ${table.$id} )`);
        } catch (e: any) {
          error(
            `Failed to delete table ${table.name} ( ${table.$id} ): ${e.message}`,
          );
        }
      }
    }
  }

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    tables.push(...localConfig.getTables());
  } else {
    const answers = await inquirer.prompt(questionsPushTables);
    if (answers.tables) {
      const configTables = new Map();
      localConfig.getTables().forEach((c: any) => {
        configTables.set(`${c["databaseId"]}|${c["$id"]}`, c);
      });
      answers.tables.forEach((a: any) => {
        const table = configTables.get(a);
        tables.push(table);
      });
    }
  }

  if (tables.length === 0) {
    log("No tables found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull tables' to synchronize existing one, or use '${EXECUTABLE_NAME} init table' to create a new one.`,
    );
    return;
  }

  if (
    !(await approveChanges(
      tables,
      async (args: any) => {
        const tablesService = await getTablesDBService();
        return await tablesService.getTable(args.databaseId, args.tableId);
      },
      KeysTable,
      "tableId",
      "tables",
      ["columns", "indexes"],
      "databaseId",
      "databaseId",
    ))
  ) {
    return;
  }

  log("Pushing tables ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushTables(tables, { attempts });

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    error("No tables were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} tables.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

const pushCollection = async (): Promise<void> => {
  warn(
    `${EXECUTABLE_NAME} push collection has been deprecated. Please consider using '${EXECUTABLE_NAME} push tables' instead`,
  );
  const collections: any[] = [];

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    collections.push(...localConfig.getCollections());
  } else {
    const answers = await inquirer.prompt(questionsPushCollections);
    if (answers.collections) {
      const configCollections = new Map();
      localConfig.getCollections().forEach((c: any) => {
        configCollections.set(`${c["databaseId"]}|${c["$id"]}`, c);
      });
      answers.collections.forEach((a: any) => {
        const collection = configCollections.get(a);
        collections.push(collection);
      });
    }
  }

  if (collections.length === 0) {
    log("No collections found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull collections' to synchronize existing one, or use '${EXECUTABLE_NAME} init collection' to create a new one.`,
    );
    return;
  }

  // Add database names to collections for the class method
  collections.forEach((collection: any) => {
    const localDatabase = localConfig.getDatabase(collection.databaseId);
    collection.databaseName = localDatabase.name ?? collection.databaseId;
  });

  if (
    !(await approveChanges(
      collections,
      async (args: any) => {
        const databasesService = await getDatabasesService();
        return await databasesService.getCollection(
          args.databaseId,
          args.collectionId,
        );
      },
      KeysCollection,
      "collectionId",
      "collections",
      ["attributes", "indexes"],
      "databaseId",
      "databaseId",
    ))
  ) {
    return;
  }

  log("Pushing collections ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushCollections(collections);

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    error("No collections were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} collections.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

const pushBucket = async (): Promise<void> => {
  let bucketIds: string[] = [];
  const configBuckets = localConfig.getBuckets();

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    bucketIds.push(...configBuckets.map((b: any) => b.$id));
  }

  if (bucketIds.length === 0) {
    const answers = await inquirer.prompt(questionsPushBuckets);
    if (answers.buckets) {
      bucketIds.push(...answers.buckets);
    }
  }

  if (bucketIds.length === 0) {
    log("No buckets found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull buckets' to synchronize existing one, or use '${EXECUTABLE_NAME} init bucket' to create a new one.`,
    );
    return;
  }

  let buckets: any[] = [];

  for (const bucketId of bucketIds) {
    const idBuckets = configBuckets.filter((b: any) => b.$id === bucketId);
    buckets.push(...idBuckets);
  }

  if (
    !(await approveChanges(
      buckets,
      async (args: any) => {
        const storageService = await getStorageService();
        return await storageService.getBucket(args.bucketId);
      },
      KeysStorage,
      "bucketId",
      "buckets",
    ))
  ) {
    return;
  }

  log("Pushing buckets ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushBuckets(buckets);

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    error("No buckets were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} buckets.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

const pushTeam = async (): Promise<void> => {
  let teamIds: string[] = [];
  const configTeams = localConfig.getTeams();

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    teamIds.push(...configTeams.map((t: any) => t.$id));
  }

  if (teamIds.length === 0) {
    const answers = await inquirer.prompt(questionsPushTeams);
    if (answers.teams) {
      teamIds.push(...answers.teams);
    }
  }

  if (teamIds.length === 0) {
    log("No teams found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull teams' to synchronize existing one, or use '${EXECUTABLE_NAME} init team' to create a new one.`,
    );
    return;
  }

  let teams: any[] = [];

  for (const teamId of teamIds) {
    const idTeams = configTeams.filter((t: any) => t.$id === teamId);
    teams.push(...idTeams);
  }

  if (
    !(await approveChanges(
      teams,
      async (args: any) => {
        const teamsService = await getTeamsService();
        return await teamsService.get(args.teamId);
      },
      KeysTeams,
      "teamId",
      "teams",
    ))
  ) {
    return;
  }

  log("Pushing teams ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushTeams(teams);

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    error("No teams were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} teams.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

const pushMessagingTopic = async (): Promise<void> => {
  let topicsIds: string[] = [];
  const configTopics = localConfig.getMessagingTopics();

  if (cliConfig.all) {
    checkDeployConditions(localConfig);
    topicsIds.push(...configTopics.map((b: any) => b.$id));
  }

  if (topicsIds.length === 0) {
    const answers = await inquirer.prompt(questionsPushMessagingTopics);
    if (answers.topics) {
      topicsIds.push(...answers.topics);
    }
  }

  if (topicsIds.length === 0) {
    log("No topics found.");
    hint(
      `Use '${EXECUTABLE_NAME} pull topics' to synchronize existing one, or use '${EXECUTABLE_NAME} init topic' to create a new one.`,
    );
    return;
  }

  let topics: any[] = [];

  for (const topicId of topicsIds) {
    const idTopic = configTopics.filter((b: any) => b.$id === topicId);
    topics.push(...idTopic);
  }

  if (
    !(await approveChanges(
      topics,
      async (args: any) => {
        const messagingService = await getMessagingService();
        return await messagingService.getTopic(args.topicId);
      },
      KeysTopics,
      "topicId",
      "topics",
    ))
  ) {
    return;
  }

  log("Pushing topics ...");

  const pushInstance = await createPushInstance();
  const result = await pushInstance.pushMessagingTopics(topics);

  const { successfullyPushed, errors } = result;

  if (successfullyPushed === 0) {
    error("No topics were pushed.");
  } else {
    success(`Successfully pushed ${successfullyPushed} topics.`);
  }

  if (cliConfig.verbose) {
    errors.forEach((e) => console.error(e));
  }
};

export const push = new Command("push")
  .description(commandDescriptions["push"])
  .action(actionRunner(() => pushResources({ skipDeprecated: true })));

push
  .command("all")
  .description("Push all resource.")
  .action(
    actionRunner(() => {
      cliConfig.all = true;
      return pushResources({ skipDeprecated: true });
    }),
  );

push
  .command("settings")
  .description("Push project name, services and auth settings")
  .action(actionRunner(pushSettings));

push
  .command("function")
  .alias("functions")
  .description("Push functions in the current directory.")
  .option(`-f, --function-id <function-id>`, `ID of function to run`)
  .option(`-A, --async`, `Don't wait for functions deployments status`)
  .option("--no-code", "Don't push the function's code")
  .option("--with-variables", `Push function variables.`)
  .action(actionRunner(pushFunction));

push
  .command("site")
  .alias("sites")
  .description("Push sites in the current directory.")
  .option(`-f, --site-id <site-id>`, `ID of site to run`)
  .option(`-A, --async`, `Don't wait for sites deployments status`)
  .option("--no-code", "Don't push the site's code")
  .option("--with-variables", `Push site variables.`)
  .action(actionRunner(pushSite));

push
  .command("collection")
  .alias("collections")
  .description(
    "Push collections in the current project. (deprecated, please use 'push tables' instead)",
  )
  .option(
    `-a, --attempts <numberOfAttempts>`,
    `Max number of attempts before timing out. default: 30.`,
  )
  .action(actionRunner(pushCollection));

push
  .command("table")
  .alias("tables")
  .description("Push tables in the current project.")
  .option(
    `-a, --attempts <numberOfAttempts>`,
    `Max number of attempts before timing out. default: 30.`,
  )
  .action(actionRunner(pushTable));

push
  .command("bucket")
  .alias("buckets")
  .description("Push buckets in the current project.")
  .action(actionRunner(pushBucket));

push
  .command("team")
  .alias("teams")
  .description("Push teams in the current project.")
  .action(actionRunner(pushTeam));

push
  .command("topic")
  .alias("topics")
  .description("Push messaging topics in the current project.")
  .action(actionRunner(pushMessagingTopic));

export const deploy = new Command("deploy")
  .description(`Removed. Use ${EXECUTABLE_NAME} push instead`)
  .action(
    actionRunner(async () => {
      warn(
        `${EXECUTABLE_NAME} deploy has been removed. Please use '${EXECUTABLE_NAME} push' instead`,
      );
    }),
  );
