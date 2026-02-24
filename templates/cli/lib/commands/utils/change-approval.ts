import chalk from "chalk";
import inquirer from "inquirer";
import { cliConfig, success, warn, log, drawTable } from "../../parser.js";
import { whitelistKeys } from "../../config.js";
import {
  questionPushChanges,
  questionPushChangesConfirmation,
} from "../../questions.js";

/**
 * Check if a value is considered empty
 */
export const isEmpty = (value: any): boolean =>
  value === null ||
  value === undefined ||
  (typeof value === "string" && value.trim().length === 0) ||
  (Array.isArray(value) && value.length === 0);

/**
 * Prompt user for confirmation to proceed with push
 */
export const getConfirmation = async (): Promise<boolean> => {
  if (cliConfig.force) {
    return true;
  }

  async function fixConfirmation(): Promise<string> {
    const answers = await inquirer.prompt(questionPushChangesConfirmation);
    if (answers.changes !== "YES" && answers.changes !== "NO") {
      return await fixConfirmation();
    }

    return answers.changes;
  }

  const answers = await inquirer.prompt(questionPushChanges);

  if (answers.changes !== "YES" && answers.changes !== "NO") {
    answers.changes = await fixConfirmation();
  }

  if (answers.changes === "YES") {
    return true;
  }

  warn("Skipping push action. Changes were not applied.");
  return false;
};

/**
 * Compare two objects and return their differences
 */
interface ObjectChange {
  group: string;
  setting: string;
  remote: string;
  local: string;
}

type ComparableValue = boolean | number | string | any[] | undefined;

export const getObjectChanges = <T extends Record<string, any>>(
  remote: T,
  local: T,
  index: keyof T,
  what: string,
): ObjectChange[] => {
  const changes: ObjectChange[] = [];

  const remoteNested = remote[index];
  const localNested = local[index];

  if (
    remoteNested &&
    localNested &&
    typeof remoteNested === "object" &&
    !Array.isArray(remoteNested) &&
    typeof localNested === "object" &&
    !Array.isArray(localNested)
  ) {
    const remoteObj = remoteNested as Record<string, ComparableValue>;
    const localObj = localNested as Record<string, ComparableValue>;

    for (const [service, status] of Object.entries(remoteObj)) {
      const localValue = localObj[service];
      let valuesEqual = false;

      if (Array.isArray(status) && Array.isArray(localValue)) {
        valuesEqual = JSON.stringify(status) === JSON.stringify(localValue);
      } else {
        valuesEqual = status === localValue;
      }

      if (!valuesEqual) {
        changes.push({
          group: what,
          setting: service,
          remote: chalk.red(String(status ?? "")),
          local: chalk.green(String(localValue ?? "")),
        });
      }
    }
  }

  return changes;
};

/**
 * Approve changes before pushing resources
 * Compares local resources with remote resources and prompts user for confirmation
 */
export const approveChanges = async (
  resource: any[],
  resourceGetFunction: (options: Record<string, unknown>) => Promise<unknown>,
  keys: Set<string>,
  resourceName: string,
  resourcePlural: string,
  skipKeys: string[] = [],
  secondId: string = "",
  secondResourceName: string = "",
): Promise<boolean> => {
  log("Checking for changes ...");
  const changes: any[] = [];

  await Promise.all(
    resource.map(async (localResource) => {
      try {
        const options: Record<string, any> = {
          [resourceName]: localResource["$id"],
        };

        if (secondId !== "" && secondResourceName !== "") {
          options[secondResourceName] = localResource[secondId];
        }

        const remoteResource = await resourceGetFunction(options);

        for (const [key, value] of Object.entries(
          whitelistKeys(remoteResource, keys),
        )) {
          if (skipKeys.includes(key)) {
            continue;
          }

          if (isEmpty(value) && isEmpty(localResource[key])) {
            continue;
          }

          if (Array.isArray(value) && Array.isArray(localResource[key])) {
            if (JSON.stringify(value) !== JSON.stringify(localResource[key])) {
              changes.push({
                id: localResource["$id"],
                key,
                remote: chalk.red((value as string[]).join("\n")),
                local: chalk.green(localResource[key].join("\n")),
              });
            }
          } else if (value !== localResource[key]) {
            changes.push({
              id: localResource["$id"],
              key,
              remote: chalk.red(value),
              local: chalk.green(localResource[key]),
            });
          }
        }
      } catch (e: any) {
        if (Number(e.code) !== 404) {
          throw e;
        }
      }
    }),
  );

  if (changes.length === 0) {
    return true;
  }

  drawTable(changes);
  if ((await getConfirmation()) === true) {
    return true;
  }

  success(`Successfully pushed 0 ${resourcePlural}.`);
  return false;
};
