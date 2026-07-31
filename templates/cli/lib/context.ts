import { Client } from "@appwrite.io/console";
import { globalConfig, localConfig } from "./config.js";
import { warn } from "./parser.js";
import { EXECUTABLE_NAME } from "./constants.js";

/**
 * Resolves the resource a command acts on.
 *
 * The `/project` and `/organization` endpoints carry no ID in their path — they
 * act on whatever `X-Appwrite-Project` or `X-Appwrite-Organization` names — so
 * the CLI has to decide which value to send. Precedence is the same for both:
 *
 *   --project-id / --organization-id  ->  environment  ->  appwrite.config.json
 *
 * Every caller resolves through here, so an ID passed on the command line
 * cannot apply to some commands and be silently ignored by others.
 */

const ENV_PROJECT_ID = "APPWRITE_PROJECT_ID";
const ENV_ORGANIZATION_ID = "APPWRITE_ORGANIZATION_ID";

export const resolveProjectId = (override?: string): string =>
  override ||
  process.env[ENV_PROJECT_ID] ||
  localConfig.getProject().projectId ||
  globalConfig.getProject() ||
  "";

/**
 * The organization known without contacting the API: environment, then
 * appwrite.config.json. Empty when it could only be derived from the project,
 * so callers that must not perform I/O — `client --debug` — can report what is
 * configured without triggering a lookup.
 */
export const configuredOrganizationId = (): string =>
  process.env[ENV_ORGANIZATION_ID] ||
  localConfig.getProject().organizationId ||
  "";

let derivedOrganizationWarned = false;

/**
 * Look up the organization that owns a project.
 *
 * `GET /projects/{projectId}` is not published in the spec, so there is no
 * generated service method for it and the request has to be issued by hand. It
 * must set `X-Appwrite-Project` itself — without it the API treats the call as
 * a guest request and rejects it. Keeping the one raw call here means the rest
 * of the CLI never repeats it.
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
      `Unable to resolve the organization for project ${projectId}. Pass --organization-id <id>, or run \`${EXECUTABLE_NAME} init project\` to relink this directory.`,
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
  const direct = override || configuredOrganizationId();

  if (direct) {
    return direct;
  }

  const projectId = resolveProjectId();

  if (!projectId || !consoleClient) {
    throw new Error(
      `Organization is not set. Pass --organization-id <id>, or run \`${EXECUTABLE_NAME} init project\` to link this directory to a project.`,
    );
  }

  const organizationId = await fetchOrganizationForProject(
    consoleClient,
    projectId,
  );

  if (!derivedOrganizationWarned) {
    derivedOrganizationWarned = true;
    warn(
      `Resolved the organization for this command from project ${projectId}. Run \`${EXECUTABLE_NAME} init project\` to persist organizationId in ${EXECUTABLE_NAME}.config.json.`,
    );
  }

  return organizationId;
};
