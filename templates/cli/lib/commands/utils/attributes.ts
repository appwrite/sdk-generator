import chalk from "chalk";
import { getDatabasesService } from "../../services.js";
import { log, warn, success, cliConfig, drawTable } from "../../parser.js";
import { Pools } from "./pools.js";
import inquirer from "inquirer";
import type { Client } from "@appwrite.io/console";

/**
 * Per-type field rules for push diffing.
 * - updatable: can be changed in place via update*Attribute / update*Column
 * - recreate: require delete + recreate (no update API accepts them)
 * Fields in neither set are ignored (server-derived or irrelevant).
 */
interface FieldRules {
  updatable: string[];
  recreate: string[];
}

const COMMON_RECREATE_KEYS = ["type", "array", "encrypt", "format"];

const getAttributeFieldRules = (attribute: any): FieldRules => {
  const type = attribute?.type;
  const format = attribute?.format || "";

  switch (type) {
    case "string":
      switch (format) {
        case "enum":
          return {
            updatable: ["required", "default", "elements"],
            recreate: COMMON_RECREATE_KEYS,
          };
        case "email":
        case "url":
        case "ip":
          return {
            updatable: ["required", "default"],
            recreate: COMMON_RECREATE_KEYS,
          };
        default:
          return {
            updatable: ["required", "default", "size"],
            recreate: COMMON_RECREATE_KEYS,
          };
      }
    case "varchar":
      return {
        updatable: ["required", "default", "size"],
        recreate: COMMON_RECREATE_KEYS,
      };
    case "text":
    case "mediumtext":
    case "longtext":
    case "boolean":
    case "datetime":
    case "point":
    case "linestring":
    case "polygon":
      return {
        updatable: ["required", "default"],
        recreate: COMMON_RECREATE_KEYS,
      };
    case "integer":
    case "bigint":
    case "double":
      return {
        updatable: ["required", "default", "min", "max"],
        recreate: COMMON_RECREATE_KEYS,
      };
    case "relationship":
      return {
        updatable: ["onDelete"],
        recreate: [
          "type",
          "relatedTable",
          "relatedCollection",
          "relationType",
          "twoWay",
          "twoWayKey",
        ],
      };
    default:
      return {
        updatable: ["required", "default"],
        recreate: COMMON_RECREATE_KEYS,
      };
  }
};

const INDEX_FIELD_RULES: FieldRules = {
  updatable: [],
  recreate: ["type", "attributes", "columns", "orders"],
};

export interface AttributeChange {
  key: string;
  attribute: any;
  reason: string;
  action: string;
}

export interface AttributeRename {
  from: string;
  to: string;
  /** Remote attribute snapshot used for the update* call (key = from). */
  attribute: any;
}

export interface Collection {
  $id: string;
  databaseId: string;
  name: string;
  attributes?: any[];
  indexes?: any[];
  columns?: any[];
  [key: string]: any;
}

const questionPushChanges = [
  {
    type: "input",
    name: "changes",
    message: 'Type "YES" to confirm or "NO" to cancel:',
  },
];

const questionPushChangesConfirmation = [
  {
    type: "input",
    name: "changes",
    message:
      'Incorrect answer. Please type "YES" to confirm or "NO" to cancel:',
  },
];

export class Attributes {
  private pools: Pools;
  private skipConfirmation: boolean;
  private client?: Client;

  constructor(pools?: Pools, skipConfirmation = false, client?: Client) {
    this.client = client;
    this.pools = pools || new Pools(undefined, client);
    this.skipConfirmation = skipConfirmation;
  }

  private getConfirmation = async (): Promise<boolean> => {
    if (cliConfig.force || this.skipConfirmation) {
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

    return false;
  };

  private isEmpty = (value: any): boolean =>
    value === null ||
    value === undefined ||
    (typeof value === "string" && value.trim().length === 0) ||
    (Array.isArray(value) && value.length === 0);

  private isEqual = (a: any, b: any): boolean => {
    if (a === b) return true;
    if (
      typeof a === "object" &&
      typeof b === "object" &&
      a !== null &&
      b !== null
    ) {
      return JSON.stringify(a) === JSON.stringify(b);
    }
    return String(a) === String(b);
  };

  private compareAttribute = (
    remote: any,
    local: any,
    reason: string,
    key: string,
    immutable: boolean = false,
  ): string => {
    // Omitted local fields mean "leave remote as-is" (e.g. encrypt not in config).
    if (local === undefined) {
      return reason;
    }

    if (this.isEmpty(remote) && this.isEmpty(local)) {
      return reason;
    }

    const suffix = immutable
      ? " (cannot be changed in place, requires recreation)"
      : "";

    if (Array.isArray(remote) && Array.isArray(local)) {
      if (JSON.stringify(remote) !== JSON.stringify(local)) {
        const bol = reason === "" ? "" : "\n";
        reason += `${bol}${key} changed from ${chalk.red(remote)} to ${chalk.green(local)}${suffix}`;
      }
    } else if (!this.isEqual(remote, local)) {
      const bol = reason === "" ? "" : "\n";
      reason += `${bol}${key} changed from ${chalk.red(remote)} to ${chalk.green(local)}${suffix}`;
    }

    return reason;
  };

  private getFieldRules = (
    entity: any,
    isIndex: boolean = false,
  ): FieldRules => (isIndex ? INDEX_FIELD_RULES : getAttributeFieldRules(entity));

  /**
   * Check if attribute fields have changed.
   * When recreating=true, only immutable (recreate-forcing) fields are compared.
   * When recreating=false, only updatable fields are compared.
   */
  private checkAttributeChanges = (
    remote: any,
    local: any,
    collection: Collection,
    recreating: boolean = true,
    isIndex: boolean = false,
  ): AttributeChange | undefined => {
    if (local === undefined) {
      return undefined;
    }

    const keyName = `${chalk.yellow(local.key)} in ${collection.name} (${collection["$id"]})`;
    const action = chalk.cyan(recreating ? "recreating" : "changing");
    let reason = "";
    const attribute = recreating ? remote : local;
    const rules = this.getFieldRules(local, isIndex);
    const keys = recreating ? rules.recreate : rules.updatable;

    for (const key of keys) {
      reason = this.compareAttribute(
        remote[key],
        local[key],
        reason,
        key,
        recreating,
      );
    }

    return reason === ""
      ? undefined
      : { key: keyName, attribute, reason, action };
  };

  /**
   * Check if attributes contain the given attribute
   */
  private attributesContains = (attribute: any, attributes: any[]): any =>
    attributes.find((attr) => attr.key === attribute.key);

  private generateChangesObject = (
    attribute: any,
    collection: Collection,
    isAdding: boolean,
  ): AttributeChange => {
    return {
      key: `${chalk.yellow(attribute.key)} in ${collection.name} (${collection["$id"]})`,
      attribute: attribute,
      reason: isAdding
        ? "Field isn't present on the remote server"
        : "Field isn't present on the appwrite.config.json file",
      action: isAdding ? chalk.green("adding") : chalk.red("deleting"),
    };
  };

  public createAttribute = async (
    databaseId: string,
    collectionId: string,
    attribute: any,
  ): Promise<any> => {
    const databasesService = await getDatabasesService(this.client);
    switch (attribute.type) {
      case "string":
        switch (attribute.format) {
          case "email":
            return databasesService.createEmailAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              required: attribute.required,
              xdefault: attribute.default,
              array: attribute.array,
            });
          case "url":
            return databasesService.createUrlAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              required: attribute.required,
              xdefault: attribute.default,
              array: attribute.array,
            });
          case "ip":
            return databasesService.createIpAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              required: attribute.required,
              xdefault: attribute.default,
              array: attribute.array,
            });
          case "enum":
            return databasesService.createEnumAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              elements: attribute.elements,
              required: attribute.required,
              xdefault: attribute.default,
              array: attribute.array,
            });
          default:
            return databasesService.createStringAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              size: attribute.size,
              required: attribute.required,
              xdefault: attribute.default,
              array: attribute.array,
              encrypt: attribute.encrypt,
            });
        }
      case "varchar":
        return databasesService.createVarcharAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          size: attribute.size,
          required: attribute.required,
          xdefault: attribute.default,
          array: attribute.array,
          encrypt: attribute.encrypt,
        });
      case "text":
        return databasesService.createTextAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          array: attribute.array,
          encrypt: attribute.encrypt,
        });
      case "mediumtext":
        return databasesService.createMediumtextAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          array: attribute.array,
          encrypt: attribute.encrypt,
        });
      case "longtext":
        return databasesService.createLongtextAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          array: attribute.array,
          encrypt: attribute.encrypt,
        });
      case "integer":
        return databasesService.createIntegerAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          min: attribute.min,
          max: attribute.max,
          xdefault: attribute.default,
          array: attribute.array,
        });
      case "bigint":
        return databasesService.createBigIntAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          min: attribute.min,
          max: attribute.max,
          xdefault: attribute.default,
          array: attribute.array,
        });
      case "double":
        return databasesService.createFloatAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          min: attribute.min,
          max: attribute.max,
          xdefault: attribute.default,
          array: attribute.array,
        });
      case "boolean":
        return databasesService.createBooleanAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          array: attribute.array,
        });
      case "datetime":
        return databasesService.createDatetimeAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          array: attribute.array,
        });
      case "relationship":
        return databasesService.createRelationshipAttribute({
          databaseId,
          collectionId,
          relatedCollectionId:
            attribute.relatedTable ?? attribute.relatedCollection,
          type: attribute.relationType,
          twoWay: attribute.twoWay,
          key: attribute.key,
          twoWayKey: attribute.twoWayKey,
          onDelete: attribute.onDelete,
        });
      case "point":
        return databasesService.createPointAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
        });
      case "linestring":
        return databasesService.createLineAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
        });
      case "polygon":
        return databasesService.createPolygonAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
        });
      default:
        throw new Error(`Unsupported attribute type: ${attribute.type}`);
    }
  };

  private formatUpdateError = (attribute: any, err: unknown): string => {
    const message = String(err);
    const key = attribute?.key ?? "unknown";
    const isResize =
      message.includes("attribute_invalid_resize") ||
      message.includes("column_invalid_resize") ||
      message.includes("invalid_resize");

    if (isResize) {
      return (
        `Failed to update "${key}": existing values exceed the new size. ` +
        `Increase the size, shorten existing data, or recreate the attribute. ` +
        `(${message})`
      );
    }

    return `Failed to update "${key}": ${message}`;
  };

  public updateAttribute = async (
    databaseId: string,
    collectionId: string,
    attribute: any,
    newKey?: string,
  ): Promise<any> => {
    // Indexes have no update endpoint; callers must recreate them.
    if (
      Array.isArray(attribute.attributes) ||
      Array.isArray(attribute.columns)
    ) {
      throw new Error(
        `Indexes cannot be updated in place (key: ${attribute.key}). Recreate the index instead.`,
      );
    }

    const databasesService = await getDatabasesService(this.client);
    switch (attribute.type) {
      case "string":
        switch (attribute.format) {
          case "email":
            return databasesService.updateEmailAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              required: attribute.required,
              xdefault: attribute.default,
              newKey: newKey,
            });
          case "url":
            return databasesService.updateUrlAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              required: attribute.required,
              xdefault: attribute.default,
              newKey: newKey,
            });
          case "ip":
            return databasesService.updateIpAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              required: attribute.required,
              xdefault: attribute.default,
              newKey: newKey,
            });
          case "enum":
            return databasesService.updateEnumAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              elements: attribute.elements,
              required: attribute.required,
              xdefault: attribute.default,
              newKey: newKey,
            });
          default:
            return databasesService.updateStringAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              required: attribute.required,
              xdefault: attribute.default,
              size: attribute.size,
              newKey: newKey,
            });
        }
      case "varchar":
        return databasesService.updateVarcharAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          size: attribute.size,
          newKey: newKey,
        });
      case "text":
        return databasesService.updateTextAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          newKey: newKey,
        });
      case "mediumtext":
        return databasesService.updateMediumtextAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          newKey: newKey,
        });
      case "longtext":
        return databasesService.updateLongtextAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          newKey: newKey,
        });
      case "integer":
        return databasesService.updateIntegerAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          min: attribute.min,
          max: attribute.max,
          xdefault: attribute.default,
          newKey: newKey,
        });
      case "bigint":
        return databasesService.updateBigIntAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          min: attribute.min,
          max: attribute.max,
          xdefault: attribute.default,
          newKey: newKey,
        });
      case "double":
        return databasesService.updateFloatAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          min: attribute.min,
          max: attribute.max,
          xdefault: attribute.default,
          newKey: newKey,
        });
      case "boolean":
        return databasesService.updateBooleanAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          newKey: newKey,
        });
      case "datetime":
        return databasesService.updateDatetimeAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          newKey: newKey,
        });
      case "relationship":
        return databasesService.updateRelationshipAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          onDelete: attribute.onDelete,
          newKey: newKey,
        });
      case "point":
        return databasesService.updatePointAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          newKey: newKey,
        });
      case "linestring":
        return databasesService.updateLineAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          newKey: newKey,
        });
      case "polygon":
        return databasesService.updatePolygonAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
          newKey: newKey,
        });
      default:
        throw new Error(`Unsupported attribute type: ${attribute.type}`);
    }
  };

  public deleteAttribute = async (
    collection: Collection,
    attribute: any,
    isIndex: boolean = false,
  ): Promise<void> => {
    log(
      `Deleting ${isIndex ? "index" : "attribute"} ${attribute.key} of ${collection.name} ( ${collection["$id"]} )`,
    );

    const databasesService = await getDatabasesService(this.client);
    if (isIndex) {
      await databasesService.deleteIndex(
        collection["databaseId"],
        collection["$id"],
        attribute.key,
      );
      return;
    }

    await databasesService.deleteAttribute(
      collection["databaseId"],
      collection["$id"],
      attribute.key,
    );
  };

  /**
   * Check if attribute is a child-side relationship
   * Child-side relationships are auto-generated by Appwrite and should be skipped
   */
  private isChildSideRelationship = (attribute: any): boolean =>
    attribute.type === "relationship" && attribute.side === "child";

  /**
   * Resolve previousKey rename hints against the remote snapshot.
   * Patches matched remote keys in place (before classification) so a pure
   * rename does not surface as delete+add. API calls are executed later.
   */
  private resolveRenames = (
    remoteAttributes: any[],
    localAttributes: any[],
    collection: Collection,
  ): { renames: AttributeRename[]; renameChanges: AttributeChange[] } => {
    const renames: AttributeRename[] = [];
    const renameChanges: AttributeChange[] = [];

    for (const local of localAttributes) {
      if (!local.previousKey || local.previousKey === local.key) {
        continue;
      }

      const remotePrevious = remoteAttributes.find(
        (attr) => attr.key === local.previousKey,
      );
      const remoteCurrent = remoteAttributes.find(
        (attr) => attr.key === local.key,
      );

      if (remotePrevious && !remoteCurrent) {
        // Pending rename: patch remote key so classification matches by new name.
        renames.push({
          from: local.previousKey,
          to: local.key,
          attribute: { ...remotePrevious },
        });
        renameChanges.push({
          key: `${chalk.yellow(local.key)} in ${collection.name} (${collection["$id"]})`,
          attribute: { ...remotePrevious },
          reason: `key renamed from ${chalk.red(local.previousKey)} to ${chalk.green(local.key)}`,
          action: chalk.cyan("renaming"),
        });
        remotePrevious.key = local.key;
        continue;
      }

      if (remoteCurrent && !remotePrevious) {
        // Already renamed on the server; hint is stale and harmless.
        continue;
      }

      if (!remotePrevious && !remoteCurrent) {
        // Fresh create; ignore the hint and let the add path handle it.
        continue;
      }

      // Both keys exist remotely — cannot rename without a collision.
      warn(
        `Ignoring previousKey "${local.previousKey}" for "${local.key}" in ${collection.name} (${collection["$id"]}): both keys already exist remotely. "${local.previousKey}" will be treated as a deletion if it is absent from the local config.`,
      );
    }

    return { renames, renameChanges };
  };

  /**
   * Filter deleted and recreated attributes,
   * return list of attributes to create and whether any changes were made
   */
  public attributesToCreate = async (
    remoteAttributes: any[],
    localAttributes: any[],
    collection: Collection,
    isIndex: boolean = false,
  ): Promise<{
    attributes: any[];
    hasChanges: boolean;
    renames: AttributeRename[];
  }> => {
    // Filter out child-side relationships from both local and remote attributes for comparison
    // Child-side relationships are auto-generated by Appwrite when creating two-way relationships
    // from the parent side, so we should not compare or try to create them directly
    const filteredLocalAttributes = localAttributes.filter(
      (attr) => !this.isChildSideRelationship(attr),
    );
    let filteredRemoteAttributes = remoteAttributes.filter(
      (attr) => !this.isChildSideRelationship(attr),
    );

    // Resolve previousKey hints before classification so renames do not
    // appear as delete+add. Indexes have no rename API — skip entirely.
    const { renames, renameChanges } = isIndex
      ? { renames: [] as AttributeRename[], renameChanges: [] as AttributeChange[] }
      : this.resolveRenames(
          filteredRemoteAttributes,
          filteredLocalAttributes,
          collection,
        );

    const deleting = filteredRemoteAttributes
      .filter(
        (attribute) =>
          !this.attributesContains(attribute, filteredLocalAttributes),
      )
      .map((attr) => this.generateChangesObject(attr, collection, false));
    const adding = filteredLocalAttributes
      .filter(
        (attribute) =>
          !this.attributesContains(attribute, filteredRemoteAttributes),
      )
      .map((attr) => this.generateChangesObject(attr, collection, true));
    const conflicts = filteredRemoteAttributes
      .map((attribute) =>
        this.checkAttributeChanges(
          attribute,
          this.attributesContains(attribute, filteredLocalAttributes),
          collection,
          true,
          isIndex,
        ),
      )
      .filter((attribute) => attribute !== undefined) as AttributeChange[];
    const changes = filteredRemoteAttributes
      .map((attribute) =>
        this.checkAttributeChanges(
          attribute,
          this.attributesContains(attribute, filteredLocalAttributes),
          collection,
          false,
          isIndex,
        ),
      )
      .filter((attribute) => attribute !== undefined)
      .filter(
        (attribute) =>
          conflicts.filter((attr) => attribute!.key === attr.key).length !== 1,
      ) as AttributeChange[];

    let changedAttributes: any[] = [];
    const changing = [
      ...renameChanges,
      ...deleting,
      ...adding,
      ...conflicts,
      ...changes,
    ];
    if (changing.length === 0) {
      return { attributes: changedAttributes, hasChanges: false, renames: [] };
    }

    log(
      !cliConfig.force
        ? "There are pending changes in your collection deployment"
        : "List of applied changes",
    );

    drawTable(
      changing.map((change) => {
        return {
          Key: change.key,
          Action: change.action,
          Reason: change.reason,
        };
      }),
    );

    if (!cliConfig.force) {
      if (deleting.length > 0 && !isIndex) {
        console.log(
          `${chalk.red("------------------------------------------------------")}`,
        );
        console.log(
          `${chalk.red("| WARNING: Attribute deletion may cause loss of data |")}`,
        );
        console.log(
          `${chalk.red("------------------------------------------------------")}`,
        );
        console.log();
      }
      if (conflicts.length > 0 && !isIndex) {
        console.log(
          `${chalk.red("--------------------------------------------------------")}`,
        );
        console.log(
          `${chalk.red("| WARNING: Attribute recreation may cause loss of data |")}`,
        );
        console.log(
          `${chalk.red("--------------------------------------------------------")}`,
        );
        console.log();
      }

      if ((await this.getConfirmation()) !== true) {
        return { attributes: changedAttributes, hasChanges: false, renames: [] };
      }
    }

    // Apply renames before field updates / deletions so a failed rename
    // never leaves data half-destroyed.
    if (renames.length > 0) {
      const renameResults = await Promise.allSettled(
        renames.map((rename) =>
          this.updateAttribute(
            collection["databaseId"],
            collection["$id"],
            rename.attribute,
            rename.to,
          ),
        ),
      );

      const renameFailures = renameResults
        .map((result, index) =>
          result.status === "rejected"
            ? this.formatUpdateError(
                { key: renames[index].to },
                result.reason,
              )
            : null,
        )
        .filter((message): message is string => message !== null);

      if (renameFailures.length > 0) {
        throw new Error(
          `Error renaming attribute for ${collection["$id"]}:\n${renameFailures.join("\n")}`,
        );
      }

      const renameReady = await this.pools.expectAttributes(
        collection["databaseId"],
        collection["$id"],
        renames.map((rename) => rename.to),
      );

      if (!renameReady) {
        throw new Error(
          `Attribute rename timed out waiting for keys: ${renames.map((r) => r.to).join(", ")}`,
        );
      }
    }

    // Apply in-place updates first so failures abort before any deletions.
    if (changes.length > 0) {
      const updateResults = await Promise.allSettled(
        changes.map((change) =>
          this.updateAttribute(
            collection["databaseId"],
            collection["$id"],
            change.attribute,
          ),
        ),
      );

      const failures = updateResults
        .map((result, index) =>
          result.status === "rejected"
            ? this.formatUpdateError(changes[index].attribute, result.reason)
            : null,
        )
        .filter((message): message is string => message !== null);

      if (failures.length > 0) {
        throw new Error(
          `Error updating ${isIndex ? "index" : "attribute"} for ${collection["$id"]}:\n${failures.join("\n")}`,
        );
      }
    }

    if (conflicts.length > 0) {
      changedAttributes = conflicts.map((change) => change.attribute);
      await Promise.all(
        changedAttributes.map((changed) =>
          this.deleteAttribute(collection, changed, isIndex),
        ),
      );
      filteredRemoteAttributes = filteredRemoteAttributes.filter(
        (attribute) => !this.attributesContains(attribute, changedAttributes),
      );
    }

    const deletingAttributes = deleting.map((change) => change.attribute);
    await Promise.all(
      deletingAttributes.map((attribute) =>
        this.deleteAttribute(collection, attribute, isIndex),
      ),
    );

    // Wait for both removals and recreate-driven deletes before creating.
    const deletedKeys = [
      ...deletingAttributes,
      ...conflicts.map((change) => change.attribute),
    ].map((attribute: any) => attribute.key);

    if (deletedKeys.length) {
      const waitForDeletion = isIndex
        ? this.pools.waitForIndexDeletion
        : this.pools.waitForAttributeDeletion;
      const deletePoolStatus = await waitForDeletion(
        collection["databaseId"],
        collection["$id"],
        deletedKeys,
      );

      if (!deletePoolStatus) {
        throw new Error(
          `${isIndex ? "Index" : "Attribute"} deletion timed out.`,
        );
      }
    }

    const newAttributes = filteredLocalAttributes.filter(
      (attribute) =>
        !this.attributesContains(attribute, filteredRemoteAttributes),
    );
    return { attributes: newAttributes, hasChanges: true, renames };
  };

  public createIndexes = async (
    indexes: any[],
    collection: Collection,
  ): Promise<void> => {
    log(`Creating indexes ...`);

    const databasesService = await getDatabasesService(this.client);
    for (const index of indexes) {
      await databasesService.createIndex({
        databaseId: collection["databaseId"],
        collectionId: collection["$id"],
        key: index.key,
        type: index.type,
        attributes: index.columns ?? index.attributes,
        orders: index.orders,
      });
    }

    const result = await this.pools.expectIndexes(
      collection["databaseId"],
      collection["$id"],
      indexes.map((index: any) => index.key),
    );

    if (!result) {
      throw new Error("Index creation timed out.");
    }

    if (indexes.length > 0) {
      success(`Created ${indexes.length} indexes`);
    }
  };

  public createAttributes = async (
    attributes: any[],
    collection: Collection,
  ): Promise<void> => {
    log(`Creating attributes ...`);

    for (const attribute of attributes) {
      if (attribute.side !== "child") {
        await this.createAttribute(
          collection["databaseId"],
          collection["$id"],
          attribute,
        );
      }
    }

    const result = await this.pools.expectAttributes(
      collection["databaseId"],
      collection["$id"],
      attributes
        .filter((attribute: any) => attribute.side !== "child")
        .map((attribute: any) => attribute.key),
    );

    if (!result) {
      throw new Error(`Attribute creation timed out.`);
    }

    const createdCount = attributes.filter((a) => a.side !== "child").length;
    if (createdCount > 0) {
      success(`Created ${createdCount} attributes`);
    }
  };

  public createColumns = async (
    columns: any[],
    table: Collection,
  ): Promise<void> => {
    log(`Creating columns ...`);

    for (const column of columns) {
      if (column.side !== "child") {
        await this.createAttribute(table["databaseId"], table["$id"], column);
      }
    }

    const result = await this.pools.expectAttributes(
      table["databaseId"],
      table["$id"],
      columns
        .filter((column: any) => column.side !== "child")
        .map((column: any) => column.key),
    );

    if (!result) {
      throw new Error(`Column creation timed out.`);
    }

    const createdCount = columns.filter((c) => c.side !== "child").length;
    if (createdCount > 0) {
      success(`Created ${createdCount} columns`);
    }
  };
}
