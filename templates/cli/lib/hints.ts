import type { Command } from "commander";
import { globalConfig } from "./config.js";
import { EXECUTABLE_NAME } from "./constants.js";
import { looksLikeHtml } from "./errors.js";

/**
 * Commands whose response carries identifiers but no detail, mapped to the
 * command that shows the full resource. Keys are the command path as typed,
 * so they can be checked against `--help` output directly.
 */
const followUpHints: Record<string, string> = {
  "list-projects": `Run \`${EXECUTABLE_NAME} project get --project-id <project-id>\` to see a project's details.`,
  "list-organizations": `Run \`${EXECUTABLE_NAME} organization get --organization-id <organization-id>\` to see an organization's details.`,
  // Hidden oauth2 paths kept so the legacy invocations still get a hint.
  "oauth2 list-projects": `Run \`${EXECUTABLE_NAME} project get --project-id <project-id>\` to see a project's details.`,
  "oauth2 list-organizations": `Run \`${EXECUTABLE_NAME} organization get --organization-id <organization-id>\` to see an organization's details.`,
};

/** Command path without the executable name, e.g. `list-projects`. */
const commandPath = (command: Command): string => {
  const segments: string[] = [];

  for (let current: Command | null = command; current?.parent;) {
    segments.unshift(current.name());
    current = current.parent;
  }

  return segments.join(" ");
};

export const followUpHintFor = (command: Command): string =>
  followUpHints[commandPath(command)] ?? "";

const isQueryFailure = (message: string): boolean =>
  /Invalid query(?: method)?/i.test(message) ||
  /query[^.:\n]*syntax error|syntax error[^.:\n]*query/i.test(message);

/** Endpoints without a path are missing the `/v1` the API is served under. */
const endpointMissingApiPath = (endpoint: string): boolean => {
  try {
    return new URL(endpoint).pathname.replace(/\/+$/, "") === "";
  } catch {
    return false;
  }
};

/**
 * The hints that apply to a failure. Requests made with an explicit
 * `--endpoint` record it on the exception, so prefer that over whatever
 * endpoint happens to be configured.
 */
export const errorHintsFor = (err: Error, endpoint?: string): string[] => {
  const failure = err as Error & {
    endpoint?: string;
    code?: number;
    type?: string;
    response?: unknown;
  };

  const hints: string[] = [];
  const requestEndpoint =
    endpoint ?? failure.endpoint ?? globalConfig.getEndpoint();

  if (isQueryFailure(failure.message ?? "")) {
    hints.push(
      `For common list filters, use flags like --limit 25, --sort-desc '$createdAt', or --filter 'status=active'. Raw --queries values must be Appwrite JSON query strings, for example: ${EXECUTABLE_NAME} tablesdb list-rows --queries '{"method":"limit","values":[25]}'`,
    );
  }

  const response = typeof failure.response === "string" ? failure.response : "";
  const routeMissing =
    failure.code === 404 ||
    failure.type === "general_route_not_found" ||
    looksLikeHtml(response);

  if (routeMissing && endpointMissingApiPath(requestEndpoint)) {
    hints.push(
      `Appwrite's API is served under /v1. Try --endpoint ${new URL(requestEndpoint).origin}/v1`,
    );
  }

  return hints;
};
