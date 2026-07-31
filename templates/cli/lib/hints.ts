import type { Command } from "commander";
import { EXECUTABLE_NAME } from "./constants.js";

/**
 * Commands whose response carries identifiers but no detail, mapped to the
 * command that shows the full resource. Keys are the command path as typed,
 * so they can be checked against `--help` output directly.
 */
const followUpHints: Record<string, string> = {
  "oauth2 list-projects": `Run \`${EXECUTABLE_NAME} project get --project-id <project-id>\` to see a project's details.`,
  "oauth2 list-organizations": `Run \`${EXECUTABLE_NAME} organization get --organization-id <organization-id>\` to see an organization's details.`,
};

/** Command path without the executable name, e.g. `oauth2 list-projects`. */
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
