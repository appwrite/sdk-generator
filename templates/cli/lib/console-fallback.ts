import { Oauth2, Organization, Teams } from "@appwrite.io/console";
import type { Models } from "@appwrite.io/console";
import { globalConfig } from "./config.js";
import { DEFAULT_ENDPOINT } from "./constants.js";
import { paginate } from "./paginate.js";
import { sdkForConsole, sdkForConsoleWithOrganization } from "./sdks.js";
import { getCloudBaseHostname } from "./utils.js";

/**
 * Endpoints that only exist on Cloud, and the console endpoints that stand in
 * for them elsewhere.
 *
 * The OAuth2 listing endpoints resolve an access token's authorization details,
 * so they only exist where the OAuth2 server does — Cloud. Self-hosted installs
 * answer `general_route_not_found`, and an email/password session has no token
 * to resolve in the first place. These helpers keep the OAuth2 call for sessions
 * that can use it and rebuild the same response from the console endpoints
 * (`GET /teams`, `GET /organization/projects`) for everyone else.
 */

/** Server-side defaults of the OAuth2 listing endpoints, mirrored here. */
const DEFAULT_LIMIT = 25;
const DEFAULT_OFFSET = 0;

/** Page size used while collecting the full set to window locally. */
const PAGE_SIZE = 100;

const hasOauth2Session = (): boolean => globalConfig.getAccessToken() !== "";

const isRouteMissing = (error: unknown): boolean => {
  const failure = error as { code?: number; type?: string };
  return failure.code === 404 || failure.type === "general_route_not_found";
};

/** Use the Cloud-only route when available, otherwise build its local result. */
const withRouteFallback = async <T>(
  primary: (() => Promise<T>) | undefined,
  fallback: () => Promise<T>,
): Promise<T> => {
  if (primary) {
    try {
      return await primary();
    } catch (error) {
      if (!isRouteMissing(error)) {
        throw error;
      }
    }
  }

  return fallback();
};

/**
 * The OAuth2 endpoints window server-side, so `total` counts every match while
 * the items cover one page. Windowing locally keeps that contract.
 */
const applyWindow = <T>(items: T[], limit?: number, offset?: number): T[] => {
  const start = offset ?? DEFAULT_OFFSET;
  return items.slice(start, start + (limit ?? DEFAULT_LIMIT));
};

/**
 * Regional API endpoint for a project, matching the `endpoint` the OAuth2
 * response carries. Regions only have their own hostname on Cloud; anywhere
 * else every project is served from the configured endpoint.
 */
const endpointForRegion = (region: string): string => {
  const endpoint = globalConfig.getEndpoint() || DEFAULT_ENDPOINT;

  try {
    const url = new URL(endpoint);
    const base = getCloudBaseHostname(url.hostname);

    if (base !== null && region !== "") {
      return `${url.protocol}//${region}.${base}${url.pathname}`;
    }
  } catch {
    // Fall through to the configured endpoint.
  }

  return endpoint;
};

/** Every organization the session can see, as teams. */
const listAllOrganizations = async (
  search?: string,
): Promise<Models.Team[]> => {
  const teams = new Teams(await sdkForConsole());

  const response = await paginate<Models.Team, "teams">(
    (args) => teams.list(args.queries as string[], search),
    {},
    PAGE_SIZE,
    "teams",
  );

  return response.teams;
};

const listAllProjects = async (
  organizationId: string,
  search?: string,
): Promise<Models.Project[]> => {
  const organization = new Organization(
    await sdkForConsole({ organizationId }),
  );

  const response = await paginate<Models.Project, "projects">(
    (args) => organization.listProjects(args.queries as string[], search),
    {},
    PAGE_SIZE,
    "projects",
  );

  return response.projects;
};

export const listProjectsForSession = async (
  limit?: number,
  offset?: number,
  search?: string,
): Promise<Models.Oauth2ProjectList> => {
  return withRouteFallback(
    hasOauth2Session()
      ? async () =>
          new Oauth2(await sdkForConsole()).listProjects(limit, offset, search)
      : undefined,
    async () => {
      const projects: Models.Oauth2Project[] = [];

      for (const organization of await listAllOrganizations()) {
        for (const project of await listAllProjects(organization.$id, search)) {
          projects.push({
            $id: project.$id,
            region: project.region,
            endpoint: endpointForRegion(project.region),
          });
        }
      }

      return {
        total: projects.length,
        projects: applyWindow(projects, limit, offset),
      };
    },
  );
};

export const listOrganizationsForSession = async (
  limit?: number,
  offset?: number,
  search?: string,
): Promise<Models.Oauth2OrganizationList> => {
  return withRouteFallback(
    hasOauth2Session()
      ? async () =>
          new Oauth2(await sdkForConsole()).listOrganizations(
            limit,
            offset,
            search,
          )
      : undefined,
    async () => {
      const organizations = (await listAllOrganizations(search)).map(
        ({ $id }) => ({ $id }),
      );

      return {
        total: organizations.length,
        organizations: applyWindow(organizations, limit, offset),
      };
    },
  );
};

/**
 * Self-hosted installs have no `/organization` — the singular organization
 * service is Cloud-only, and an organization there is just its team. The team
 * carries the fields the server actually knows ($id, name, timestamps, prefs);
 * the billing fields of a Cloud organization have no equivalent to report.
 */
export const getOrganizationForSession = async (
  organizationId?: string,
): Promise<Models.Organization | Models.Team> => {
  const client = await sdkForConsoleWithOrganization(organizationId);
  return withRouteFallback(
    () => new Organization(client).get(),
    () => new Teams(client).get(client.headers["X-Appwrite-Organization"]),
  );
};
