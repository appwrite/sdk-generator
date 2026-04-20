import chalk from "chalk";
import { AppwriteException } from "@appwrite.io/console";
import { localConfig } from "../../config.js";
import { log, success, error, drawTable } from "../../parser.js";
import { paginate } from "../../paginate.js";
import { getTablesDBService } from "../../services.js";
import { getConfirmation } from "./change-approval.js";

export interface TablesDBChangesResult {
  applied: boolean;
  resyncNeeded: boolean;
}

interface TablesDBResource {
  $id: string;
  name: string;
  enabled: boolean;
}

const isTablesDBResource = (value: unknown): value is TablesDBResource => {
  if (!value || typeof value !== "object") {
    return false;
  }

  return (
    "$id" in value &&
    typeof value.$id === "string" &&
    "name" in value &&
    typeof value.name === "string" &&
    "enabled" in value &&
    typeof value.enabled === "boolean"
  );
};

const getSyncErrorMessage = (err: unknown): string => {
  if (err instanceof AppwriteException) {
    return err.message;
  }

  if (err instanceof Error) {
    return err.message;
  }

  return String(err);
};

/**
 * Check for and apply changes to tablesDB (databases)
 * Handles creation, update, and deletion of databases
 */
export const checkAndApplyTablesDBChanges =
  async (): Promise<TablesDBChangesResult> => {
    log("Checking for tablesDB changes ...");

    const localTablesDBs: TablesDBResource[] = localConfig.getTablesDBs();
    const paginatedResult = await paginate(
      async (args) => {
        const tablesDBService = await getTablesDBService();
        const queries = Array.isArray(args.queries)
          ? args.queries.filter(
              (query): query is string => typeof query === "string",
            )
          : [];
        return await tablesDBService.list(queries);
      },
      {},
      100,
      "databases",
    );
    const remoteTablesDBs = Array.isArray(paginatedResult.databases)
      ? paginatedResult.databases.filter(isTablesDBResource)
      : [];

    if (localTablesDBs.length === 0 && remoteTablesDBs.length === 0) {
      return { applied: false, resyncNeeded: false };
    }

    const changes: Array<Record<string, unknown>> = [];
    const toCreate: TablesDBResource[] = [];
    const toUpdate: TablesDBResource[] = [];
    const toDelete: TablesDBResource[] = [];

    // Check for deletions - remote DBs that aren't in local config
    for (const remoteDB of remoteTablesDBs) {
      const localDB = localTablesDBs.find((db) => db.$id === remoteDB.$id);
      if (!localDB) {
        toDelete.push(remoteDB);
        changes.push({
          id: remoteDB.$id,
          action: chalk.red("deleting"),
          key: "Database",
          remote: remoteDB.name,
          local: "(deleted locally)",
        });
      }
    }

    // Check for additions and updates
    for (const localDB of localTablesDBs) {
      const remoteDB = remoteTablesDBs.find((db) => db.$id === localDB.$id);

      if (!remoteDB) {
        toCreate.push(localDB);
        changes.push({
          id: localDB.$id,
          action: chalk.green("creating"),
          key: "Database",
          remote: "(does not exist)",
          local: localDB.name,
        });
      } else {
        let hasChanges = false;

        if (remoteDB.name !== localDB.name) {
          hasChanges = true;
          changes.push({
            id: localDB.$id,
            action: chalk.yellow("updating"),
            key: "Name",
            remote: remoteDB.name,
            local: localDB.name,
          });
        }

        if (remoteDB.enabled !== localDB.enabled) {
          hasChanges = true;
          changes.push({
            id: localDB.$id,
            action: chalk.yellow("updating"),
            key: "Enabled",
            remote: remoteDB.enabled,
            local: localDB.enabled,
          });
        }

        if (hasChanges) {
          toUpdate.push(localDB);
        }
      }
    }

    if (changes.length === 0) {
      return { applied: false, resyncNeeded: false };
    }

    log("Found changes in tablesDB resource:");
    drawTable(changes);

    if (toDelete.length > 0) {
      console.log(
        `${chalk.red("------------------------------------------------------------------")}`,
      );
      console.log(
        `${chalk.red("| WARNING: Database deletion will also delete all related tables |")}`,
      );
      console.log(
        `${chalk.red("------------------------------------------------------------------")}`,
      );
      console.log();
    }

    if ((await getConfirmation()) !== true) {
      return { applied: false, resyncNeeded: false };
    }

    // Apply deletions first
    let needsResync = false;
    for (const db of toDelete) {
      try {
        log(`Deleting database ${db.name} ( ${db.$id} ) ...`);
        const tablesDBService = await getTablesDBService();
        await tablesDBService.delete(db.$id);
        success(`Deleted ${db.name} ( ${db.$id} )`);
        needsResync = true;
      } catch (e: unknown) {
        error(
          `Failed to delete database ${db.name} ( ${db.$id} ): ${getSyncErrorMessage(e)}`,
        );
        throw new Error(
          `Database sync failed during deletion of ${db.$id}. Some changes may have been applied.`,
        );
      }
    }

    // Apply creations
    for (const db of toCreate) {
      try {
        log(`Creating database ${db.name} ( ${db.$id} ) ...`);
        const tablesDBService = await getTablesDBService();
        await tablesDBService.create(db.$id, db.name, db.enabled);
        success(`Created ${db.name} ( ${db.$id} )`);
      } catch (e: unknown) {
        error(
          `Failed to create database ${db.name} ( ${db.$id} ): ${getSyncErrorMessage(e)}`,
        );
        throw new Error(
          `Database sync failed during creation of ${db.$id}. Some changes may have been applied.`,
        );
      }
    }

    // Apply updates
    for (const db of toUpdate) {
      try {
        log(`Updating database ${db.name} ( ${db.$id} ) ...`);
        const tablesDBService = await getTablesDBService();
        await tablesDBService.update(db.$id, db.name, db.enabled);
        success(`Updated ${db.name} ( ${db.$id} )`);
      } catch (e: unknown) {
        error(
          `Failed to update database ${db.name} ( ${db.$id} ): ${getSyncErrorMessage(e)}`,
        );
        throw new Error(
          `Database sync failed during update of ${db.$id}. Some changes may have been applied.`,
        );
      }
    }

    return { applied: true, resyncNeeded: needsResync };
  };
