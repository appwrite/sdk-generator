import chalk from "chalk";
import type { Command, Help } from "commander";
import { EXECUTABLE_NAME, SDK_LOGO, SDK_TITLE } from "./constants.js";

/**
 * The main help screen is grouped by intent rather than listed alphabetically.
 * Entries are command paths as typed, so `oauth2 list-projects` can be
 * surfaced next to `login` without moving it out of the oauth2 service.
 *
 * Anything not named here still shows up, under `OTHER`, so a service added
 * to the spec can never silently disappear from `--help`.
 */
const groups: ReadonlyArray<{
  title: string;
  commands: readonly string[];
  dim?: boolean;
}> = [
  {
    title: "GET STARTED",
    commands: [
      "login",
      "oauth2 list-organizations",
      "oauth2 list-projects",
      "init",
      "pull",
      "push",
      "run",
      "whoami",
    ],
  },
  {
    title: "PROJECT",
    commands: ["organization", "project", "apps", "proxy", "vcs", "webhooks"],
  },
  {
    title: "RESOURCES",
    commands: [
      "account",
      "users",
      "teams",
      "tablesdb",
      "storage",
      "functions",
      "sites",
      "messaging",
      "tokens",
      "backups",
      "presences",
    ],
  },
  {
    title: "UTILITIES",
    commands: [
      "graphql",
      "generate",
      "types",
      "locale",
      "activities",
      "migrations",
      "notifications",
      "oauth2",
      "client",
      "completion",
      "logout",
      "update",
    ],
  },
  {
    title: "DEPRECATED",
    dim: true,
    commands: ["databases"],
  },
];

/**
 * One-line summaries for the listing. `.description()` stays the long form
 * shown on a command's own help page.
 *
 * Written to fit one terminal line — keep them under 51 characters, in the
 * imperative, with no trailing period.
 */
const summaries: Record<string, string> = {
  login: `Authenticate with your ${SDK_TITLE} account`,
  "oauth2 list-organizations": "Organizations your session can access",
  "oauth2 list-projects": "Projects your session can access",
  init: "Scaffold a project, function, site, or resource",
  pull: "Pull remote project resources into this directory",
  push: "Push local project resources",
  run: "Run the project locally for development",
  whoami: "Show the currently authenticated account",

  organization: "Manage organization-level projects",
  project: "Usage, variables, and project-level settings",
  apps: "OAuth2 applications, keys, scopes, installations",
  proxy: "Domain configuration beyond DNS",
  vcs: "Connect and manage VCS repositories",
  webhooks: "Project webhooks",

  account: "Manage your own user account",
  users: "Manage project users",
  teams: "Group users to share resource access",
  tablesdb: "Structured tables of rows and columns",
  storage: "Files and buckets",
  functions: "Serverless functions, deployments, and executions",
  sites: "Static and SSR sites and their deployments",
  messaging: "Topics, subscribers, and message delivery",
  tokens: "Resource tokens for secure file access",
  backups: "Backup policies, archives, and restorations",
  presences: "Real-time user presence tracking",

  graphql: "Query and mutate any resource via GraphQL",
  generate: "Generate a type-safe SDK from your project config",
  types: "Generate TypeScript types for your project",
  locale: "Localize your app based on user location",
  activities: "List and inspect project activity events",
  migrations: "Migrate data between services",
  notifications: "Console notifications",
  oauth2: "Authorize apps and issue OAuth2 and OIDC tokens",
  client: "Configure the CLI itself",
  completion: "Generate shell completion scripts",
  logout: `Log out of your ${SDK_TITLE} account`,
  update: "Update the CLI to the latest version",

  databases: "Use `tablesdb` instead",
};

/** Order of the global flags, by long flag. Unlisted options are appended. */
const optionOrder: readonly string[] = [
  "--version",
  "--help",
  "--json",
  "--raw",
  "--show-secrets",
  "--verbose",
  "--force",
  "--all",
  "--id",
  "--report",
];

const MAX_WIDTH = 80;
const GAP = 2;
const INDENT = "  ";

type Row = { name: string; summary: string };

const isListed = (command: Command): boolean =>
  command.name() !== "help" &&
  !(command as Command & { _hidden?: boolean })._hidden;

/** Resolve a space-separated command path against the tree, or null. */
const resolve = (root: Command, path: string): Command | null => {
  let current: Command | undefined = root;

  for (const segment of path.split(" ")) {
    current = current?.commands.find(
      (candidate) =>
        candidate.name() === segment || candidate.aliases().includes(segment),
    );

    if (!current) {
      return null;
    }
  }

  return current ?? null;
};

/**
 * Fall back to the first sentence of the description so a command with no
 * summary still reads as one line rather than a paragraph.
 */
const summaryOf = (command: Command, path: string): string => {
  const declared = summaries[path] ?? command.summary();

  if (declared) {
    return declared;
  }

  const [sentence] = command.description().split(". ");

  return (sentence ?? "").replace(/\.$/, "");
};

const renderRows = (
  rows: readonly Row[],
  width: number,
  dim: boolean,
): string =>
  rows
    .map(({ name, summary }) => {
      const line = `${INDENT}${name.padEnd(width)}${" ".repeat(GAP)}${summary}`;

      return dim ? chalk.dim(line) : line;
    })
    .join("\n");

const renderOptions = (command: Command, helper: Help): string => {
  const options = [...helper.visibleOptions(command)].sort((left, right) => {
    const rank = (flag: string): number => {
      const index = optionOrder.indexOf(flag);

      return index === -1 ? optionOrder.length : index;
    };

    return rank(left.long ?? "") - rank(right.long ?? "");
  });

  const width = Math.max(
    ...options.map((option) => helper.optionTerm(option).length),
  );

  return options
    .map(
      (option) =>
        `${INDENT}${helper.optionTerm(option).padEnd(width)}${" ".repeat(GAP)}${helper.optionDescription(option)}`,
    )
    .join("\n");
};

export const formatMainHelp = (command: Command, helper: Help): string => {
  const width = Math.min(process.stdout.columns || MAX_WIDTH, MAX_WIDTH);

  const sections = groups
    .map((group) => ({
      ...group,
      rows: group.commands.flatMap((path) => {
        const child = resolve(command, path);

        return child && isListed(child)
          ? [{ name: path, summary: summaryOf(child, path) }]
          : [];
      }),
    }))
    .filter((group) => group.rows.length > 0);

  const claimed = new Set(groups.flatMap((group) => group.commands));

  // Via the helper so this inherits commander's hidden-command filtering and
  // the configured subcommand sort, rather than raw declaration order.
  const other = helper
    .visibleCommands(command)
    .filter((child) => isListed(child) && !claimed.has(child.name()))
    .map((child) => ({
      name: child.name(),
      summary: summaryOf(child, child.name()),
    }));

  if (other.length > 0) {
    sections.push({ title: "OTHER", commands: [], rows: other });
  }

  const nameWidth = Math.max(
    0,
    ...sections.flatMap((group) => group.rows.map((row) => row.name.length)),
  );

  const output = [
    chalk.redBright(SDK_LOGO.replace(/\n+$/, "")),
    "",
    INDENT + helper.wrap(command.description(), width - 2, 2, 2),
    "",
    chalk.bold("USAGE"),
    `${INDENT}${EXECUTABLE_NAME} [options] <command> [subcommand]`,
  ];

  for (const group of sections) {
    output.push(
      "",
      chalk.bold(group.title),
      renderRows(group.rows, nameWidth, group.dim === true),
    );
  }

  output.push(
    "",
    chalk.bold("OPTIONS"),
    renderOptions(command, helper),
    "",
    chalk.dim(
      `Run \`${EXECUTABLE_NAME} <command> --help\` for details on a specific command.`,
    ),
    "",
  );

  return output.join("\n");
};
