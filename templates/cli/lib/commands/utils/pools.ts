import { getDatabasesService } from "../../services.js";
import { paginate } from "../../paginate.js";
import { log } from "../../parser.js";

export class Pools {
  private STEP_SIZE = 100; // Resources
  private POLL_DEBOUNCE = 2000; // Milliseconds
  private pollMaxDebounces = 30;
  private POLL_DEFAULT_VALUE = 30;

  constructor(pollMaxDebounces?: number) {
    if (pollMaxDebounces) {
      this.pollMaxDebounces = pollMaxDebounces;
    }
  }

  public setPollMaxDebounces(value: number): void {
    this.pollMaxDebounces = value;
  }

  public getPollMaxDebounces(): number {
    return this.pollMaxDebounces;
  }

  public wipeAttributes = async (
    databaseId: string,
    collectionId: string,
    iteration: number = 1,
  ): Promise<boolean> => {
    if (iteration > this.pollMaxDebounces) {
      return false;
    }

    const databasesService = await getDatabasesService();
    const response = await databasesService.listAttributes(
      databaseId,
      collectionId,
      [JSON.stringify({ method: "limit", values: [1] })],
    );
    const { total } = response;

    if (total === 0) {
      return true;
    }

    if (this.pollMaxDebounces === this.POLL_DEFAULT_VALUE) {
      let steps = Math.max(1, Math.ceil(Number(total) / this.STEP_SIZE));
      if (steps > 1 && iteration === 1) {
        this.pollMaxDebounces *= steps;

        log(
          "Found a large number of attributes, increasing timeout to " +
            (this.pollMaxDebounces * this.POLL_DEBOUNCE) / 1000 / 60 +
            " minutes",
        );
      }
    }

    await new Promise((resolve) => setTimeout(resolve, this.POLL_DEBOUNCE));

    return await this.wipeAttributes(databaseId, collectionId, iteration + 1);
  };

  public wipeIndexes = async (
    databaseId: string,
    collectionId: string,
    iteration: number = 1,
  ): Promise<boolean> => {
    if (iteration > this.pollMaxDebounces) {
      return false;
    }

    const databasesService = await getDatabasesService();
    const response = await databasesService.listIndexes(
      databaseId,
      collectionId,
      [JSON.stringify({ method: "limit", values: [1] })],
    );
    const { total } = response;

    if (total === 0) {
      return true;
    }

    if (this.pollMaxDebounces === this.POLL_DEFAULT_VALUE) {
      let steps = Math.max(1, Math.ceil(Number(total) / this.STEP_SIZE));
      if (steps > 1 && iteration === 1) {
        this.pollMaxDebounces *= steps;

        log(
          "Found a large number of indexes, increasing timeout to " +
            (this.pollMaxDebounces * this.POLL_DEBOUNCE) / 1000 / 60 +
            " minutes",
        );
      }
    }

    await new Promise((resolve) => setTimeout(resolve, this.POLL_DEBOUNCE));

    return await this.wipeIndexes(databaseId, collectionId, iteration + 1);
  };

  public waitForAttributeDeletion = async (
    databaseId: string,
    collectionId: string,
    attributeKeys: any[],
    iteration: number = 1,
  ): Promise<boolean> => {
    if (iteration > this.pollMaxDebounces) {
      return false;
    }

    if (this.pollMaxDebounces === this.POLL_DEFAULT_VALUE) {
      let steps = Math.max(1, Math.ceil(attributeKeys.length / this.STEP_SIZE));
      if (steps > 1 && iteration === 1) {
        this.pollMaxDebounces *= steps;

        log(
          "Found a large number of attributes to be deleted. Increasing timeout to " +
            (this.pollMaxDebounces * this.POLL_DEBOUNCE) / 1000 / 60 +
            " minutes",
        );
      }
    }

    const { attributes } = await paginate(
      async (args: any) => {
        const databasesService = await getDatabasesService();
        return await databasesService.listAttributes({
          databaseId: args.databaseId,
          collectionId: args.collectionId,
          queries: args.queries || [],
        });
      },
      {
        databaseId,
        collectionId,
      },
      100,
      "attributes",
    );

    const ready = attributeKeys.filter((attribute: any) =>
      attributes.some((a: any) => a.key === attribute.key),
    );

    if (ready.length === 0) {
      return true;
    }

    await new Promise((resolve) => setTimeout(resolve, this.POLL_DEBOUNCE));

    return await this.waitForAttributeDeletion(
      databaseId,
      collectionId,
      attributeKeys,
      iteration + 1,
    );
  };

  public expectAttributes = async (
    databaseId: string,
    collectionId: string,
    attributeKeys: string[],
    iteration: number = 1,
  ): Promise<boolean> => {
    if (iteration > this.pollMaxDebounces) {
      return false;
    }

    if (this.pollMaxDebounces === this.POLL_DEFAULT_VALUE) {
      let steps = Math.max(1, Math.ceil(attributeKeys.length / this.STEP_SIZE));
      if (steps > 1 && iteration === 1) {
        this.pollMaxDebounces *= steps;

        log(
          "Creating a large number of attributes, increasing timeout to " +
            (this.pollMaxDebounces * this.POLL_DEBOUNCE) / 1000 / 60 +
            " minutes",
        );
      }
    }

    const { attributes } = await paginate(
      async (args: any) => {
        const databasesService = await getDatabasesService();
        return await databasesService.listAttributes(
          args.databaseId,
          args.collectionId,
          args.queries || [],
        );
      },
      {
        databaseId,
        collectionId,
      },
      100,
      "attributes",
    );

    const ready = attributes
      .filter((attribute: any) => {
        if (attributeKeys.includes(attribute.key)) {
          if (["stuck", "failed"].includes(attribute.status)) {
            throw new Error(`Attribute '${attribute.key}' failed!`);
          }

          return attribute.status === "available";
        }

        return false;
      })
      .map((attribute: any) => attribute.key);

    if (ready.length === attributeKeys.length) {
      return true;
    }

    await new Promise((resolve) => setTimeout(resolve, this.POLL_DEBOUNCE));

    return await this.expectAttributes(
      databaseId,
      collectionId,
      attributeKeys,
      iteration + 1,
    );
  };

  public deleteIndexes = async (
    databaseId: string,
    collectionId: string,
    indexesKeys: any[],
    iteration: number = 1,
  ): Promise<boolean> => {
    if (iteration > this.pollMaxDebounces) {
      return false;
    }

    if (this.pollMaxDebounces === this.POLL_DEFAULT_VALUE) {
      let steps = Math.max(1, Math.ceil(indexesKeys.length / this.STEP_SIZE));
      if (steps > 1 && iteration === 1) {
        this.pollMaxDebounces *= steps;

        log(
          "Found a large number of indexes to be deleted. Increasing timeout to " +
            (this.pollMaxDebounces * this.POLL_DEBOUNCE) / 1000 / 60 +
            " minutes",
        );
      }
    }

    const { indexes } = await paginate(
      async (args: any) => {
        const databasesService = await getDatabasesService();
        return await databasesService.listIndexes(
          args.databaseId,
          args.collectionId,
          args.queries || [],
        );
      },
      {
        databaseId,
        collectionId,
      },
      100,
      "indexes",
    );

    const indexKeySet = new Set(indexes.map((i: any) => i.key));
    const ready = indexesKeys.filter((index: any) =>
      indexKeySet.has(index.key),
    );

    if (ready.length === 0) {
      return true;
    }

    await new Promise((resolve) => setTimeout(resolve, this.POLL_DEBOUNCE));

    return await this.deleteIndexes(
      databaseId,
      collectionId,
      indexesKeys,
      iteration + 1,
    );
  };

  public expectIndexes = async (
    databaseId: string,
    collectionId: string,
    indexKeys: string[],
    iteration: number = 1,
  ): Promise<boolean> => {
    if (iteration > this.pollMaxDebounces) {
      return false;
    }

    if (this.pollMaxDebounces === this.POLL_DEFAULT_VALUE) {
      let steps = Math.max(1, Math.ceil(indexKeys.length / this.STEP_SIZE));
      if (steps > 1 && iteration === 1) {
        this.pollMaxDebounces *= steps;

        log(
          "Creating a large number of indexes, increasing timeout to " +
            (this.pollMaxDebounces * this.POLL_DEBOUNCE) / 1000 / 60 +
            " minutes",
        );
      }
    }

    const { indexes } = await paginate(
      async (args: any) => {
        const databasesService = await getDatabasesService();
        return await databasesService.listIndexes(
          args.databaseId,
          args.collectionId,
          args.queries || [],
        );
      },
      {
        databaseId,
        collectionId,
      },
      100,
      "indexes",
    );

    const ready = indexes
      .filter((index: any) => {
        if (indexKeys.includes(index.key)) {
          if (["stuck", "failed"].includes(index.status)) {
            throw new Error(`Index '${index.key}' failed!`);
          }

          return index.status === "available";
        }

        return false;
      })
      .map((index: any) => index.key);

    if (ready.length >= indexKeys.length) {
      return true;
    }

    await new Promise((resolve) => setTimeout(resolve, this.POLL_DEBOUNCE));

    return await this.expectIndexes(
      databaseId,
      collectionId,
      indexKeys,
      iteration + 1,
    );
  };
}
