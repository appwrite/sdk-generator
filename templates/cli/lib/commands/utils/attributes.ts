import chalk from "chalk";
import { getDatabasesService } from "../../services.js";
import { KeysAttributes } from "../../config.js";
import { log, success, error, cliConfig, drawTable } from "../../parser.js";
import { Pools } from "./pools.js";
import inquirer from "inquirer";

const changeableKeys = [
  "status",
  "required",
  "xdefault",
  "elements",
  "min",
  "max",
  "default",
  "error",
];

export interface AttributeChange {
  key: string;
  attribute: any;
  reason: string;
  action: string;
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

  constructor(pools?: Pools, skipConfirmation = false) {
    this.pools = pools || new Pools();
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

    let answers = await inquirer.prompt(questionPushChanges);

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
  ): string => {
    if (this.isEmpty(remote) && this.isEmpty(local)) {
      return reason;
    }

    if (Array.isArray(remote) && Array.isArray(local)) {
      if (JSON.stringify(remote) !== JSON.stringify(local)) {
        const bol = reason === "" ? "" : "\n";
        reason += `${bol}${key} changed from ${chalk.red(remote)} to ${chalk.green(local)}`;
      }
    } else if (!this.isEqual(remote, local)) {
      const bol = reason === "" ? "" : "\n";
      reason += `${bol}${key} changed from ${chalk.red(remote)} to ${chalk.green(local)}`;
    }

    return reason;
  };

  /**
   * Check if attribute non-changeable fields has been changed
   * If so return the differences as an object.
   */
  private checkAttributeChanges = (
    remote: any,
    local: any,
    collection: Collection,
    recreating: boolean = true,
  ): AttributeChange | undefined => {
    if (local === undefined) {
      return undefined;
    }

    const keyName = `${chalk.yellow(local.key)} in ${collection.name} (${collection["$id"]})`;
    const action = chalk.cyan(recreating ? "recreating" : "changing");
    let reason = "";
    let attribute = recreating ? remote : local;

    for (let key of Object.keys(remote)) {
      if (!KeysAttributes.has(key)) {
        continue;
      }

      if (changeableKeys.includes(key)) {
        if (!recreating) {
          reason = this.compareAttribute(remote[key], local[key], reason, key);
        }
        continue;
      }

      if (!recreating) {
        continue;
      }

      reason = this.compareAttribute(remote[key], local[key], reason, key);
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
    const databasesService = await getDatabasesService();
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

  public updateAttribute = async (
    databaseId: string,
    collectionId: string,
    attribute: any,
  ): Promise<any> => {
    const databasesService = await getDatabasesService();
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
            });
          case "url":
            return databasesService.updateUrlAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              required: attribute.required,
              xdefault: attribute.default,
            });
          case "ip":
            return databasesService.updateIpAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              required: attribute.required,
              xdefault: attribute.default,
            });
          case "enum":
            return databasesService.updateEnumAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              elements: attribute.elements,
              required: attribute.required,
              xdefault: attribute.default,
            });
          default:
            return databasesService.updateStringAttribute({
              databaseId,
              collectionId,
              key: attribute.key,
              required: attribute.required,
              xdefault: attribute.default,
            });
        }
      case "integer":
        return databasesService.updateIntegerAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          min: attribute.min,
          max: attribute.max,
          xdefault: attribute.default,
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
        });
      case "boolean":
        return databasesService.updateBooleanAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
        });
      case "datetime":
        return databasesService.updateDatetimeAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
        });
      case "relationship":
        return databasesService.updateRelationshipAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          onDelete: attribute.onDelete,
        });
      case "point":
        return databasesService.updatePointAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
        });
      case "linestring":
        return databasesService.updateLineAttribute({
          databaseId,
          collectionId,
          key: attribute.key,
          required: attribute.required,
          xdefault: attribute.default,
        });
      case "polygon":
        return databasesService.updatePolygonAttribute({
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

  public deleteAttribute = async (
    collection: Collection,
    attribute: any,
    isIndex: boolean = false,
  ): Promise<void> => {
    log(
      `Deleting ${isIndex ? "index" : "attribute"} ${attribute.key} of ${collection.name} ( ${collection["$id"]} )`,
    );

    const databasesService = await getDatabasesService();
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
   * Filter deleted and recreated attributes,
   * return list of attributes to create and whether any changes were made
   */
  public attributesToCreate = async (
    remoteAttributes: any[],
    localAttributes: any[],
    collection: Collection,
    isIndex: boolean = false,
  ): Promise<{ attributes: any[]; hasChanges: boolean }> => {
    const deleting = remoteAttributes
      .filter(
        (attribute) => !this.attributesContains(attribute, localAttributes),
      )
      .map((attr) => this.generateChangesObject(attr, collection, false));
    const adding = localAttributes
      .filter(
        (attribute) => !this.attributesContains(attribute, remoteAttributes),
      )
      .map((attr) => this.generateChangesObject(attr, collection, true));
    const conflicts = remoteAttributes
      .map((attribute) =>
        this.checkAttributeChanges(
          attribute,
          this.attributesContains(attribute, localAttributes),
          collection,
        ),
      )
      .filter((attribute) => attribute !== undefined) as AttributeChange[];
    const changes = remoteAttributes
      .map((attribute) =>
        this.checkAttributeChanges(
          attribute,
          this.attributesContains(attribute, localAttributes),
          collection,
          false,
        ),
      )
      .filter((attribute) => attribute !== undefined)
      .filter(
        (attribute) =>
          conflicts.filter((attr) => attribute!.key === attr.key).length !== 1,
      ) as AttributeChange[];

    let changedAttributes: any[] = [];
    const changing = [...deleting, ...adding, ...conflicts, ...changes];
    if (changing.length === 0) {
      return { attributes: changedAttributes, hasChanges: false };
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
        return { attributes: changedAttributes, hasChanges: false };
      }
    }

    if (conflicts.length > 0) {
      changedAttributes = conflicts.map((change) => change.attribute);
      await Promise.all(
        changedAttributes.map((changed) =>
          this.deleteAttribute(collection, changed, isIndex),
        ),
      );
      remoteAttributes = remoteAttributes.filter(
        (attribute) => !this.attributesContains(attribute, changedAttributes),
      );
    }

    if (changes.length > 0) {
      changedAttributes = changes.map((change) => change.attribute);
      try {
        await Promise.all(
          changedAttributes.map((changed) =>
            this.updateAttribute(
              collection["databaseId"],
              collection["$id"],
              changed,
            ),
          ),
        );
      } catch (err) {
        error(
          `Error updating attribute for ${collection["$id"]}: ${String(err)}`,
        );
      }
    }

    const deletingAttributes = deleting.map((change) => change.attribute);
    await Promise.all(
      deletingAttributes.map((attribute) =>
        this.deleteAttribute(collection, attribute, isIndex),
      ),
    );
    const attributeKeys = deletingAttributes.map(
      (attribute: any) => attribute.key,
    );

    if (attributeKeys.length) {
      const deleteAttributesPoolStatus =
        await this.pools.waitForAttributeDeletion(
          collection["databaseId"],
          collection["$id"],
          attributeKeys,
        );

      if (!deleteAttributesPoolStatus) {
        throw new Error("Attribute deletion timed out.");
      }
    }

    const newAttributes = localAttributes.filter(
      (attribute) => !this.attributesContains(attribute, remoteAttributes),
    );
    return { attributes: newAttributes, hasChanges: true };
  };

  public createIndexes = async (
    indexes: any[],
    collection: Collection,
  ): Promise<void> => {
    log(`Creating indexes ...`);

    const databasesService = await getDatabasesService();
    for (let index of indexes) {
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

    for (let attribute of attributes) {
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

    for (let column of columns) {
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
