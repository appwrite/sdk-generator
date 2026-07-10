import { Client } from "@appwrite.io/console";
import { warn } from "./parser.js";

type ConfigFilterContext = {
  config: {
    organizationId?: string;
    projectId?: string;
  };
  consoleClient: Client;
};

type ConfigFilter = {
  id: string;
  matches(context: ConfigFilterContext): boolean;
  apply(context: ConfigFilterContext): Promise<void>;
};

const warned = new Set<string>();

const configFilters: ConfigFilter[] = [
  {
    id: "organization-id-header",
    matches({ config, consoleClient }) {
      return (
        !consoleClient.headers["X-Appwrite-Organization"] &&
        (!!config.organizationId || !!config.projectId)
      );
    },
    async apply({ config, consoleClient }) {
      let organizationId = config.organizationId;

      if (!organizationId) {
        if (!config.projectId) {
          throw new Error(
            "Project configuration not found. Please run 'appwrite init project' to initialize your project first.",
          );
        }

        const project = await consoleClient.call(
          "get",
          new URL(
            `${consoleClient.config.endpoint}/projects/${encodeURIComponent(config.projectId)}`,
          ),
          {
            "X-Appwrite-Project": "console",
          },
          {},
        );

        organizationId = project.teamId;

        if (!organizationId || typeof organizationId !== "string") {
          throw new Error(
            "Unable to resolve organization for this project. Please run 'appwrite init project' to relink this project.",
          );
        }

        if (!warned.has(this.id)) {
          warned.add(this.id);
          warn(
            "Your appwrite.config.json is missing organizationId. The CLI resolved it for this command without changing your config. Run 'appwrite init project' to relink and persist it.",
          );
        }
      }

      consoleClient.headers["X-Appwrite-Organization"] = organizationId;
    },
  },
];

export const applyConfigFilters = async (
  context: ConfigFilterContext,
): Promise<void> => {
  for (const filter of configFilters) {
    if (filter.matches(context)) {
      await filter.apply(context);
    }
  }
};
