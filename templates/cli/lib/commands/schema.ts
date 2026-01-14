import { Client } from "@appwrite.io/console";
import type { ConfigType } from "./config.js";
import { ConfigSchema } from "./config.js";
import { Pull, PullOptions } from "./pull.js";
import { Push, PushOptions } from "./push.js";
import { parseWithBetterErrors } from "./utils/error-formatter.js";
import JSONbig from "json-bigint";
import * as fs from "fs";
import * as path from "path";
import { Db } from "./db.js";

const JSONBig = JSONbig({ useNativeBigInt: true });

export class Schema {
  private pullCommand: Pull;
  private pushCommand: Push;

  private pullCommandSilent: Pull;

  public db: Db;

  constructor({
    projectClient,
    consoleClient,
  }: {
    projectClient: Client;
    consoleClient: Client;
  }) {
    this.pullCommand = new Pull(projectClient, consoleClient);
    this.pushCommand = new Push(projectClient, consoleClient);

    this.pullCommandSilent = new Pull(projectClient, consoleClient, true);

    this.db = new Db();
  }

  /**
   * Validates the provided configuration object against the schema.
   *
   * @param config - The configuration object to validate.
   * @returns The validated and possibly transformed configuration object.
   * @throws If the configuration does not match the schema.
   */
  public validate(config: ConfigType): ConfigType {
    return parseWithBetterErrors<ConfigType>(
      ConfigSchema,
      config,
      "Configuration schema validation",
      config,
    );
  }

  /**
   * Pulls the current schema and resources from the remote Appwrite project.
   *
   * @param config - The local configuration object.
   * @param options - Optional settings for the pull operation.
   * @returns A Promise that resolves to the updated configuration object reflecting the remote state.
   * @param configPath - Optional path to the config file. If provided, the config will be synced after pull.
   */
  public async pull(
    config: ConfigType,
    options: PullOptions,
    configPath?: string,
  ): Promise<ConfigType> {
    const updatedConfig = await this.pullCommand.pullResources(config, options);
    if (configPath) {
      this.write(updatedConfig, configPath);
    }
    return updatedConfig;
  }

  /**
   * Pushes the local configuration and schema to the remote Appwrite project.
   * Optionally syncs the config file by pulling the updated state from the server after push.
   *
   * @param config - The local configuration object to push.
   * @param options - Optional settings for the push operation. Use `force: true` to allow destructive changes.
   * @param configPath - Optional path to the config file. If provided, the config will be synced after push.
   * @returns A Promise that resolves when the push operation is complete.
   * @throws {DestructiveChangeError} When destructive changes are detected and force is not enabled.
   */
  public async push(
    config: ConfigType,
    options: PushOptions,
    configPath?: string,
  ): Promise<ConfigType> {
    await this.pushCommand.pushResources(config, options);
    const updatedConfig = await this.pullCommandSilent.pullResources(
      config,
      options,
    );

    if (configPath) {
      this.write(updatedConfig, configPath);
    }
    return updatedConfig;
  }

  /**
   * Reads the configuration object from a file.
   *
   * @param path - The path to the file to read.
   * @returns The configuration object.
   */
  public read(path: string): ConfigType {
    return JSONBig.parse(fs.readFileSync(path, "utf8")) as ConfigType;
  }

  /**
   * Writes the configuration object to a file.
   *
   * @param config - The configuration object to write.
   * @param filePath - The path to the file to write.
   * @returns void
   */
  public write(config: ConfigType, filePath: string): void {
    const resolvedPath = path.resolve(filePath);
    const content = JSONBig.stringify(config, null, 4);
    fs.writeFileSync(resolvedPath, content);
  }
}
