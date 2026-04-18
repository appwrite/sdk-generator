import chalk from "chalk";
import inquirer from "inquirer";
import { AppwriteException } from "@appwrite.io/console";
import { cliConfig, success, warn, log, drawTable } from "../../parser.js";
import { whitelistKeys } from "../../config.js";
import {
  questionPushChanges,
  questionPushChangesConfirmation,
} from "../../questions.js";

/**
 * Check if a value is considered empty
 */
export const isEmpty = (value: unknown): boolean =>
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

type ComparableValue = boolean | number | string | unknown[] | undefined;
const LOCAL_ONLY_RESOURCE_KEYS = new Set(["ignore", "path"]);

const getComparableKeys = (
  remote: Record<string, ComparableValue>,
  local: Record<string, ComparableValue>,
  skipKeys: string[],
): string[] => {
  const skippedKeys = new Set([
    ...skipKeys,
    ...Array.from(LOCAL_ONLY_RESOURCE_KEYS),
  ]);

  return [...new Set([...Object.keys(remote), ...Object.keys(local)])].filter(
    (key) => !skippedKeys.has(key),
  );
};

export const getObjectChanges = <T extends Record<string, unknown>>(
  remote: T,
  local: T,
  index: keyof T,
  what: string,
): ObjectChange[] => {
  const changes: ObjectChange[] = [];

  const remoteNested = remote[index];
  const localNested = local[index];

  const remoteObj =
    remoteNested &&
    typeof remoteNested === "object" &&
    !Array.isArray(remoteNested)
      ? (remoteNested as Record<string, ComparableValue>)
      : {};
  const localObj =
    localNested &&
    typeof localNested === "object" &&
    !Array.isArray(localNested)
      ? (localNested as Record<string, ComparableValue>)
      : {};

  for (const key of getComparableKeys(remoteObj, localObj, [])) {
    const remoteValue = remoteObj[key];
    const localValue = localObj[key];
    let valuesEqual = false;

    if (Array.isArray(remoteValue) && Array.isArray(localValue)) {
      valuesEqual = JSON.stringify(remoteValue) === JSON.stringify(localValue);
    } else {
      valuesEqual = remoteValue === localValue;
    }

    if (!valuesEqual) {
      changes.push({
        group: what,
        setting: key,
        remote: chalk.red(String(remoteValue ?? "")),
        local: chalk.green(String(localValue ?? "")),
      });
    }
  }

  return changes;
};

/**
 * Approve changes before pushing resources
 * Compares local resources with remote resources and prompts user for confirmation
 */
export const approveChanges = async (
  resource: Array<Record<string, unknown>>,
  resourceGetFunction: (
    options: Record<string, unknown>,
  ) => Promise<Record<string, unknown>>,
  keys: Set<string>,
  resourceName: string,
  resourcePlural: string,
  skipKeys: string[] = [],
  secondId: string = "",
  secondResourceName: string = "",
): Promise<boolean> => {
  log("Checking for changes ...");
  const changes: Array<Record<string, unknown>> = [];

  await Promise.all(
    resource.map(async (localResource) => {
      try {
        const options: Record<string, unknown> = {
          [resourceName]: localResource["$id"],
        };

        if (secondId !== "" && secondResourceName !== "") {
          options[secondResourceName] = localResource[secondId];
        }

        const remoteResource = await resourceGetFunction(options);
        const remoteComparable = whitelistKeys(
          remoteResource,
          keys,
        ) as Record<string, ComparableValue>;
        const localComparable = whitelistKeys(
          localResource,
          keys,
        ) as Record<string, ComparableValue>;

        for (const key of getComparableKeys(
          remoteComparable,
          localComparable,
          skipKeys,
        )) {
          const value = remoteComparable[key];
          const localValue = localComparable[key];

          if (isEmpty(value) && isEmpty(localValue)) {
            continue;
          }

          if (Array.isArray(value) && Array.isArray(localValue)) {
            if (JSON.stringify(value) !== JSON.stringify(localValue)) {
              changes.push({
                id: localResource["$id"],
                key,
                remote: chalk.red((value as string[]).join("\n")),
                local: chalk.green(
                  localValue.map((entry) => String(entry)).join("\n"),
                ),
              });
            }
          } else if (value !== localValue) {
            changes.push({
              id: localResource["$id"],
              key,
              remote: chalk.red(String(value ?? "")),
              local: chalk.green(String(localValue ?? "")),
            });
          }
        }
      } catch (e: unknown) {
        const isNotFound =
          e instanceof AppwriteException && Number(e.code) === 404;
        if (!isNotFound) throw e;
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
