import { Client } from "@appwrite.io/console";
import { globalConfig, localConfig } from "./config.js";
import { cliConfig, warn } from "./parser.js";
import { EXECUTABLE_NAME } from "./constants.js";

/**
 * Resolves the resource context a command runs against.
 *
 * Both identifiers travel to the API as headers the spec declares as security
 * schemes (X-Appwrite-Project, X-Appwrite-Organization), so the only thing the
 * CLI decides is which value to send. Precedence is the same for both:
 *
 *   explicit flag  ->  environment  ->  appwrite.config.json  ->  derived
 *
 * Every caller goes through here so that `--project-id` cannot apply to some
 * commands and be silently ignored by others.
 */

const ENV_PROJECT_ID = "APPWRITE_PROJECT_ID";
const ENV_ORGANIZATION_ID = "APPWRITE_ORGANIZATION_ID";

export const resolveProjectId = (override?: string): string =>
  override ||
  cliConfig.projectId ||
  process.env[ENV_PROJECT_ID] ||
  localConfig.getProject().projectId ||
  globalConfig.getProject() ||
  "";

/**
 * True when the project in play did not come from appwrite.config.json, so
 * config-derived values (project name, settings) do not describe it.
 */
export const isProjectOverridden = (): boolean => {
  const configured = localConfig.getProject().projectId;
  const resolved = resolveProjectId();

  return resolved !== "" && configured !== undefined && resolved !== configured;
};

let derivedOrganizationWarned = false;

/**
 * Look up the organization that owns a project.
 *
 * `GET /projects/{projectId}` is not published in the spec, so there is no
 * generated SDK method for it and the request has to be issued by hand. Keeping
 * the one raw call here means the rest of the CLI never repeats it.
 */
const fetchOrganizationForProject = async (
  consoleClient: Client,
  projectId: string,
): Promise<string> => {
  const project = await consoleClient.call(
    "get",
    new URL(
      `${consoleClient.config.endpoint}/projects/${encodeURIComponent(projectId)}`,
    ),
    { "X-Appwrite-Project": "console" },
    {},
  );

  const organizationId = project?.teamId;

  if (!organizationId || typeof organizationId !== "string") {
    throw new Error(
      `Unable to resolve the organization for project ${projectId}. Pass --organization-id, or run \`${EXECUTABLE_NAME} init project\` to relink it.`,
    );
  }

  return organizationId;
};

export const resolveOrganizationId = async ({
  override,
  consoleClient,
}: {
  override?: string;
  consoleClient?: Client;
} = {}): Promise<string> => {
  const direct =
    override ||
    process.env[ENV_ORGANIZATION_ID] ||
    localConfig.getProject().organizationId;

  if (direct) {
    return direct;
  }

  const projectId = resolveProjectId();

  if (!projectId) {
    throw new Error(
      `Organization is not set. Pass --organization-id, or run \`${EXECUTABLE_NAME} init project\` to link this directory.`,
    );
  }

  if (!consoleClient) {
    throw new Error(
      `Organization is not set. Pass --organization-id to run this command against a specific organization.`,
    );
  }

  const organizationId = await fetchOrganizationForProject(
    consoleClient,
    projectId,
  );

  if (!derivedOrganizationWarned) {
    derivedOrganizationWarned = true;
    warn(
      `Resolved the organization for this command from project ${projectId}. Run \`${EXECUTABLE_NAME} init project\` to persist organizationId in appwrite.config.json.`,
    );
  }

  return organizationId;
};
