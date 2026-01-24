import fs from "fs";
import path from "path";
import childProcess from "child_process";
import { Command } from "commander";
import inquirer from "inquirer";
import { getProjectsService, getSitesService } from "../services.js";
import { pullResources } from "./pull.js";
import ID from "../id.js";
import { localConfig, globalConfig } from "../config.js";
import {
  questionsCreateFunction,
  questionsCreateFunctionSelectTemplate,
  questionsCreateSite,
  questionsCreateBucket,
  questionsCreateMessagingTopic,
  questionsCreateCollection,
  questionsCreateTable,
  questionsInitProject,
  questionsInitProjectAutopull,
  questionsInitResources,
  questionsCreateTeam,
} from "../questions.js";
import {
  cliConfig,
  success,
  log,
  hint,
  error,
  actionRunner,
  commandDescriptions,
} from "../parser.js";
import { sdkForConsole } from "../sdks.js";
import { isCloud } from "../utils.js";
import { Account } from "@appwrite.io/console";
import { DEFAULT_ENDPOINT, EXECUTABLE_NAME } from "../constants.js";

const initResources = async (): Promise<void> => {
  const actions: Record<string, (options?: any) => Promise<void>> = {
    function: initFunction,
    site: initSite,
    table: initTable,
    bucket: initBucket,
    team: initTeam,
    message: initTopic,
    collection: initCollection,
  };

  const answers = await inquirer.prompt([questionsInitResources[0]]);

  const action = actions[answers.resource];
  if (action !== undefined) {
    await action({ returnOnZero: true });
  }
};

interface InitProjectOptions {
  organizationId?: string;
  projectId?: string;
  projectName?: string;
}

const initProject = async ({
  organizationId,
  projectId,
  projectName,
}: InitProjectOptions = {}): Promise<void> => {
  let response: any = {};

  try {
    if (globalConfig.getEndpoint() === "" || globalConfig.getCookie() === "") {
      throw new Error(
        `Missing endpoint or cookie configuration. Please run '${EXECUTABLE_NAME} login' first.`,
      );
    }
    const client = await sdkForConsole();
    const accountClient = new Account(client);

    await accountClient.get();
  } catch (e) {
    error(
      `Error Session not found. Please run '${EXECUTABLE_NAME} login' to create a session`,
    );
    process.exit(1);
  }

  let answers: any = {};

  if (!organizationId && !projectId && !projectName) {
    answers = await inquirer.prompt(questionsInitProject);
    if (answers.override === false) {
      process.exit(1);
    }
  } else {
    answers.start = "existing";
    answers.project = {};
    answers.organization = {};

    answers.organization =
      organizationId ??
      (await inquirer.prompt([questionsInitProject[2]])).organization;
    answers.project.name =
      projectName ?? (await inquirer.prompt([questionsInitProject[3]])).project;
    answers.project =
      projectId ?? (await inquirer.prompt([questionsInitProject[4]])).id;

    try {
      const projectsService = await getProjectsService();
      await projectsService.get(projectId);
    } catch (e: any) {
      if (e.code === 404) {
        answers.start = "new";
        answers.id = answers.project;
        answers.project = answers.project.name;
      } else {
        throw e;
      }
    }
  }

  localConfig.clear(); // Clear the config to avoid any conflicts
  const url = new URL(DEFAULT_ENDPOINT);

  if (answers.start === "new") {
    const projectsService = await getProjectsService();
    response = await projectsService.create(
      answers.id,
      answers.project,
      answers.organization,
      answers.region,
    );

    localConfig.setProject(response["$id"]);
    if (answers.region) {
      localConfig.setEndpoint(
        `https://${answers.region}.${url.host}${url.pathname}`,
      );
    }
  } else {
    localConfig.setProject(answers.project["$id"]);
    if (isCloud()) {
      localConfig.setEndpoint(
        `https://${answers.project["region"]}.${url.host}${url.pathname}`,
      );
    }
  }

  success(
    `Project successfully ${answers.start === "existing" ? "linked" : "created"}. Details are now stored in appwrite.config.json file.`,
  );

  if (answers.start === "existing") {
    answers = await inquirer.prompt(questionsInitProjectAutopull);
    if (answers.autopull) {
      cliConfig.all = true;
      cliConfig.force = true;
      await pullResources({
        skipDeprecated: true,
      });
    } else {
      log(
        `You can run '${EXECUTABLE_NAME} pull all' to synchronize all of your existing resources.`,
      );
    }
  }

  hint(
    `Next you can use '${EXECUTABLE_NAME} init' to create resources in your project, or use '${EXECUTABLE_NAME} pull' and '${EXECUTABLE_NAME} push' to synchronize your project.`,
  );
};

const initBucket = async (): Promise<void> => {
  const answers = await inquirer.prompt(questionsCreateBucket);

  localConfig.addBucket({
    $id: answers.id === "unique()" ? ID.unique() : answers.id,
    name: answers.bucket,
    fileSecurity: answers.fileSecurity.toLowerCase() === "yes",
    enabled: true,
  });
  success("Initialing bucket");
  log(
    `Next you can use '${EXECUTABLE_NAME} push bucket' to deploy the changes.`,
  );
};

const initTeam = async (): Promise<void> => {
  const answers = await inquirer.prompt(questionsCreateTeam);

  localConfig.addTeam({
    $id: answers.id === "unique()" ? ID.unique() : answers.id,
    name: answers.team,
  });

  success("Initialing team");
  log(`Next you can use '${EXECUTABLE_NAME} push team' to deploy the changes.`);
};

const initTable = async (): Promise<void> => {
  const answers = await inquirer.prompt(questionsCreateTable);
  const newDatabase = (answers.method ?? "").toLowerCase() !== "existing";

  if (!newDatabase) {
    answers.databaseId = answers.database;
    answers.databaseName = localConfig.getTablesDB(answers.database).name;
  }

  const databaseId =
    answers.databaseId === "unique()" ? ID.unique() : answers.databaseId;

  if (newDatabase || !localConfig.getTablesDB(answers.databaseId)) {
    localConfig.addTablesDB({
      $id: databaseId,
      name: answers.databaseName,
      enabled: true,
    });
  }

  localConfig.addTable({
    $id: answers.id === "unique()" ? ID.unique() : answers.id,
    $permissions: [],
    databaseId: databaseId,
    name: answers.table,
    enabled: true,
    rowSecurity: answers.rowSecurity.toLowerCase() === "yes",
    columns: [],
    indexes: [],
  });

  success("Initialing table");
  log(
    `Next you can use '${EXECUTABLE_NAME} push table' to deploy the changes.`,
  );
};

const initCollection = async (): Promise<void> => {
  const answers = await inquirer.prompt(questionsCreateCollection);
  const newDatabase = (answers.method ?? "").toLowerCase() !== "existing";

  if (!newDatabase) {
    answers.databaseId = answers.database;
    answers.databaseName = localConfig.getDatabase(answers.database).name;
  }

  const databaseId =
    answers.databaseId === "unique()" ? ID.unique() : answers.databaseId;

  if (newDatabase || !localConfig.getDatabase(answers.databaseId)) {
    localConfig.addDatabase({
      $id: databaseId,
      name: answers.databaseName,
      enabled: true,
    });
  }

  localConfig.addCollection({
    $id: answers.id === "unique()" ? ID.unique() : answers.id,
    databaseId: databaseId,
    name: answers.collection,
    documentSecurity: answers.documentSecurity.toLowerCase() === "yes",
    attributes: [],
    indexes: [],
    enabled: true,
  });

  success("Initialing collection");
  log(
    `Next you can use '${EXECUTABLE_NAME} push collection' to deploy the changes.`,
  );
};

const initTopic = async (): Promise<void> => {
  const answers = await inquirer.prompt(questionsCreateMessagingTopic);

  localConfig.addMessagingTopic({
    $id: answers.id === "unique()" ? ID.unique() : answers.id,
    name: answers.topic,
  });

  success("Initialing topic");
  log(
    `Next you can use '${EXECUTABLE_NAME} push topic' to deploy the changes.`,
  );
};

const initFunction = async (): Promise<void> => {
  process.chdir(localConfig.configDirectoryPath);

  // TODO: Add CI/CD support (ID, name, runtime)
  const answers = await inquirer.prompt(questionsCreateFunction);
  const functionFolder = path.join(process.cwd(), "functions");

  if (!fs.existsSync(functionFolder)) {
    fs.mkdirSync(functionFolder, {
      recursive: true,
    });
  }

  const functionId = answers.id === "unique()" ? ID.unique() : answers.id;
  const functionName = answers.name;
  const functionDir = path.join(functionFolder, functionName);
  const templatesDir = path.join(functionFolder, `${functionId}-templates`);
  const runtimeDir = path.join(templatesDir, answers.runtime.name);

  if (fs.existsSync(functionDir)) {
    throw new Error(
      `( ${functionName} ) already exists in the current directory. Please choose another name.`,
    );
  }

  if (!answers.runtime.entrypoint) {
    log(
      `Entrypoint for this runtime not found. You will be asked to configure entrypoint when you first push the function.`,
    );
  }

  if (!answers.runtime.commands) {
    log(
      `Installation command for this runtime not found. You will be asked to configure the install command when you first push the function.`,
    );
  }

  fs.mkdirSync(functionDir, { mode: 0o777 });
  fs.mkdirSync(templatesDir, { mode: 0o777 });
  const repo = "https://github.com/appwrite/templates";
  const api = `https://api.github.com/repos/appwrite/templates/contents/${answers.runtime.name}`;
  let selected: { template: string } = { template: "starter" };

  const sparse = (
    selected
      ? `${answers.runtime.name}/${selected.template}`
      : answers.runtime.name
  ).toLowerCase();

  let gitInitCommands = `git clone --single-branch --depth 1 --sparse ${repo} .`; // depth prevents fetching older commits reducing the amount fetched

  let gitPullCommands = `git sparse-checkout add ${sparse}`;

  /* Force use CMD as powershell does not support && */
  if (process.platform === "win32") {
    gitInitCommands = 'cmd /c "' + gitInitCommands + '"';
    gitPullCommands = 'cmd /c "' + gitPullCommands + '"';
  }

  /*  Execute the child process but do not print any std output */
  try {
    childProcess.execSync(gitInitCommands, {
      stdio: "pipe",
      cwd: templatesDir,
    });
    childProcess.execSync(gitPullCommands, {
      stdio: "pipe",
      cwd: templatesDir,
    });
  } catch (err: any) {
    /* Specialised errors with recommended actions to take */
    if (err.message.includes("error: unknown option")) {
      throw new Error(
        `${err.message} \n\nSuggestion: Try updating your git to the latest version, then trying to run this command again.`,
      );
    } else if (
      err.message.includes(
        "is not recognized as an internal or external command,",
      ) ||
      err.message.includes("command not found")
    ) {
      throw new Error(
        `${err.message} \n\nSuggestion: It appears that git is not installed, try installing git then trying to run this command again.`,
      );
    } else {
      throw err;
    }
  }

  fs.rmSync(path.join(templatesDir, ".git"), { recursive: true });
  if (!selected) {
    const templates: string[] = [];
    templates.push(
      ...fs
        .readdirSync(runtimeDir, { withFileTypes: true })
        .filter((item) => item.isDirectory() && item.name !== "starter")
        .map((dirent) => dirent.name),
    );
    selected = { template: "starter" };

    if (templates.length > 1) {
      selected = await inquirer.prompt(
        questionsCreateFunctionSelectTemplate(templates),
      );
    }
  }

  const copyRecursiveSync = (src: string, dest: string): void => {
    let exists = fs.existsSync(src);
    let stats = exists && fs.statSync(src);
    let isDirectory = exists && stats && stats.isDirectory();
    if (isDirectory) {
      if (!fs.existsSync(dest)) {
        fs.mkdirSync(dest);
      }

      fs.readdirSync(src).forEach(function (childItemName) {
        copyRecursiveSync(
          path.join(src, childItemName),
          path.join(dest, childItemName),
        );
      });
    } else {
      fs.copyFileSync(src, dest);
    }
  };
  copyRecursiveSync(path.join(runtimeDir, selected.template), functionDir);

  fs.rmSync(templatesDir, { recursive: true, force: true });

  const readmePath = path.join(
    process.cwd(),
    "functions",
    functionName,
    "README.md",
  );
  const readmeFile = fs.readFileSync(readmePath).toString();
  const newReadmeFile = readmeFile.split("\n");
  newReadmeFile[0] = `# ${answers.name}`;
  newReadmeFile.splice(1, 2);
  fs.writeFileSync(readmePath, newReadmeFile.join("\n"));

  let data = {
    $id: functionId,
    name: answers.name,
    runtime: answers.runtime.id,
    specification: answers.specification,
    execute: ["any"],
    events: [],
    scopes: ["users.read"],
    schedule: "",
    timeout: 15,
    enabled: true,
    logging: true,
    entrypoint: answers.runtime.entrypoint || "",
    commands: answers.runtime.commands || "",
    ignore: answers.runtime.ignore || null,
    path: `functions/${functionName}`,
  };

  localConfig.addFunction(data);
  success("Initialing function");
  log(
    `Next you can use '${EXECUTABLE_NAME} run function' to develop a function locally. To deploy the function, use '${EXECUTABLE_NAME} push function'`,
  );
};

const initSite = async (): Promise<void> => {
  process.chdir(localConfig.configDirectoryPath);

  const answers = await inquirer.prompt(questionsCreateSite);
  const siteFolder = path.join(process.cwd(), "sites");

  if (!fs.existsSync(siteFolder)) {
    fs.mkdirSync(siteFolder, {
      recursive: true,
    });
  }

  const siteId = answers.id === "unique()" ? ID.unique() : answers.id;
  const siteName = answers.name;
  const siteDir = path.join(siteFolder, siteName);
  const templatesDir = path.join(siteFolder, `${siteId}-templates`);

  if (fs.existsSync(siteDir)) {
    throw new Error(
      `( ${siteName} ) already exists in the current directory. Please choose another name.`,
    );
  }

  let templateDetails: any;
  try {
    const sitesService = await getSitesService();
    const response = await sitesService.listTemplates(
      [answers.framework.key],
      ["starter"],
      1,
    );
    if (response.total == 0) {
      throw new Error(
        `No starter template found for framework ${answers.framework.key}`,
      );
    }
    templateDetails = response.templates[0];
  } catch (err: any) {
    throw new Error(
      `Failed to fetch template for framework ${answers.framework.key}: ${err.message}`,
    );
  }

  fs.mkdirSync(siteDir, { mode: 0o777 });
  fs.mkdirSync(templatesDir, { mode: 0o777 });
  const repo = `https://github.com/${templateDetails.providerOwner}/${templateDetails.providerRepositoryId}`;
  let selected = {
    template: templateDetails.frameworks[0].providerRootDirectory,
  };

  let dirSetupCommands = "";

  const sparse = selected.template.startsWith("./")
    ? selected.template.substring(2)
    : selected.template;

  log("Fetching site code ...");

  if (selected.template === "./") {
    dirSetupCommands = `
            cd ${templatesDir}
            git init
            git remote add origin ${repo}
            git config --global init.defaultBranch main   
        `.trim();
  } else {
    dirSetupCommands = `
            cd ${templatesDir}
            git init
            git remote add origin ${repo}
            git config --global init.defaultBranch main
            git config core.sparseCheckout true
            echo "${sparse}" >> .git/info/sparse-checkout
            git config --add remote.origin.fetch '+refs/heads/*:refs/remotes/origin/*'
            git config remote.origin.tagopt --no-tags
        `.trim();
  }
  let windowsGitCloneCommands = `
            $tag = (git ls-remote --tags origin "${templateDetails.providerVersion}" | Select-Object -Last 1) -replace '.*refs/tags/', ''
            git fetch --depth=1 origin "refs/tags/$tag"
            git checkout FETCH_HEAD
            `.trim();
  let unixGitCloneCommands = `
            git fetch --depth=1 origin refs/tags/$(git ls-remote --tags origin "${templateDetails.providerVersion}" | tail -n 1 | awk -F '/' '{print $3}')
            git checkout FETCH_HEAD
            `.trim();

  let usedShell = null;
  if (process.platform === "win32") {
    dirSetupCommands = dirSetupCommands + "\n" + windowsGitCloneCommands;
    usedShell = "powershell.exe";
  }
  else {
    dirSetupCommands = dirSetupCommands + "\n" + unixGitCloneCommands;
  }

  /*  Execute the child process but do not print any std output */
  try {
    childProcess.execSync(dirSetupCommands, {
      stdio: "pipe",
      cwd: templatesDir,
      shell: usedShell,
    });
  } catch (err: any) {
    /* Specialised errors with recommended actions to take */
    if (err.message.includes("error: unknown option")) {
      throw new Error(
        `${err.message} \n\nSuggestion: Try updating your git to the latest version, then trying to run this command again.`,
      );
    } else if (
      err.message.includes(
        "is not recognized as an internal or external command,",
      ) ||
      err.message.includes("command not found")
    ) {
      throw new Error(
        `${err.message} \n\nSuggestion: It appears that git is not installed, try installing git then trying to run this command again.`,
      );
    } else {
      throw err;
    }
  }

  fs.rmSync(path.join(templatesDir, ".git"), { recursive: true, force: true });

  fs.cpSync(selected.template === "./"
    ? templatesDir
    : path.join(templatesDir, selected.template),
    siteDir, { recursive: true, force: true });

  fs.rmSync(templatesDir, { recursive: true, force: true });

  const readmePath = path.join(process.cwd(), "sites", siteName, "README.md");
  const readmeFile = fs.readFileSync(readmePath).toString();
  const newReadmeFile = readmeFile.split("\n");
  newReadmeFile[0] = `# ${answers.name}`;
  newReadmeFile.splice(1, 2);
  fs.writeFileSync(readmePath, newReadmeFile.join("\n"));

  let vars = (templateDetails.variables ?? []).map((variable: any) => {
    let value = variable.value;
    const replacements: Record<string, string> = {
      "{apiEndpoint}": globalConfig.getEndpoint(),
      "{projectId}": localConfig.getProject().projectId,
      "{projectName}": localConfig.getProject().projectName,
    };

    for (const placeholder in replacements) {
      if (value?.includes(placeholder)) {
        value = value.replace(placeholder, replacements[placeholder]);
      }
    }

    return {
      key: variable.name,
      value: value,
    };
  });

  let data = {
    $id: siteId,
    name: answers.name,
    framework: answers.framework.key,
    adapter: templateDetails.frameworks[0].adapter || "",
    buildRuntime: templateDetails.frameworks[0].buildRuntime || "",
    installCommand: templateDetails.frameworks[0].installCommand || "",
    buildCommand: templateDetails.frameworks[0].buildCommand || "",
    outputDirectory: templateDetails.frameworks[0].outputDirectory || "",
    fallbackFile: templateDetails.frameworks[0].fallbackFile || "",
    specification: answers.specification,
    enabled: true,
    timeout: 30,
    logging: true,
    ignore: answers.framework.ignore || null,
    path: `sites/${siteName}`,
    vars: vars,
  };

  if (!data.buildRuntime) {
    log(
      `Build runtime for this framework not found. You will be asked to configure build runtime when you first push the site.`,
    );
  }

  if (!data.installCommand) {
    log(
      `Installation command for this framework not found. You will be asked to configure the install command when you first push the site.`,
    );
  }

  if (!data.buildCommand) {
    log(
      `Build command for this framework not found. You will be asked to configure the build command when you first push the site.`,
    );
  }

  if (!data.outputDirectory) {
    log(
      `Output directory for this framework not found. You will be asked to configure the output directory when you first push the site.`,
    );
  }

  localConfig.addSite(data);
  success("Initializing site");
  log(`Next you can use '${EXECUTABLE_NAME} push site' to deploy the changes.`);
};

export const init = new Command("init")
  .description(commandDescriptions["init"])
  .action(actionRunner(initResources));

init
  .command("project")
  .description("Init a new Appwrite project")
  .option("--organization-id <organization-id>", "Appwrite organization ID")
  .option("--project-id <project-id>", "Appwrite project ID")
  .option("--project-name <project-name>", "Appwrite project ID")
  .action(actionRunner(initProject));

init
  .command("function")
  .alias("functions")
  .description("Init a new Appwrite function")
  .action(actionRunner(initFunction));

init
  .command("site")
  .alias("sites")
  .description("Init a new Appwrite site")
  .action(actionRunner(initSite));

init
  .command("bucket")
  .alias("buckets")
  .description("Init a new Appwrite bucket")
  .action(actionRunner(initBucket));

init
  .command("team")
  .alias("teams")
  .description("Init a new Appwrite team")
  .action(actionRunner(initTeam));

init
  .command("collection")
  .alias("collections")
  .description("Init a new Appwrite collection")
  .action(actionRunner(initCollection));

init
  .command("table")
  .alias("tables")
  .description("Init a new Appwrite table")
  .action(actionRunner(initTable));

init
  .command("topic")
  .alias("topics")
  .description("Init a new Appwrite topic")
  .action(actionRunner(initTopic));
