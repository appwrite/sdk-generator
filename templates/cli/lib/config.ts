import os from "os";
import fs from "fs";
import _path from "path";
import process from "process";
import JSONbig from "json-bigint";
import type { Models } from "@appwrite.io/console";
import type {
  BucketType,
  CollectionType,
  FunctionType,
  ConfigType,
  SettingsType,
  SiteType,
  TableType,
  TeamType,
  TopicType,
} from "./commands/config.js";
import type {
  SessionData,
  ConfigData,
  Entity,
  GlobalConfigData,
} from "./types.js";
import { createSettingsObject } from "./utils.js";
import { EXECUTABLE_NAME } from "./constants.js";
import { BigNumber } from "bignumber.js";

const JSONbigParser = JSONbig({ storeAsString: false });
const JSONbigSerializer = JSONbig({ useNativeBigInt: true });

const MAX_SAFE = BigInt(Number.MAX_SAFE_INTEGER);
const MIN_SAFE = BigInt(Number.MIN_SAFE_INTEGER);

function reviver(_key: string, value: any): any {
  if (BigNumber.isBigNumber(value)) {
    if (value.isInteger()) {
      const str = value.toFixed();
      const bi = BigInt(str);
      if (bi >= MIN_SAFE && bi <= MAX_SAFE) {
        return Number(str);
      }
      return bi;
    }
    return value.toNumber();
  }
  return value;
}

const JSONBigInt = {
  parse: (text: string) => JSONbigParser.parse(text, reviver),
  stringify: JSONbigSerializer.stringify,
};

const KeysVars = new Set(["key", "value"]);
const KeysSite = new Set([
  "path",
  "$id",
  "name",
  "enabled",
  "logging",
  "timeout",
  "framework",
  "buildRuntime",
  "adapter",
  "installCommand",
  "buildCommand",
  "outputDirectory",
  "fallbackFile",
  "specification",
  "vars",
]);
const KeysFunction = new Set([
  "path",
  "$id",
  "execute",
  "name",
  "enabled",
  "logging",
  "runtime",
  "specification",
  "scopes",
  "events",
  "schedule",
  "timeout",
  "entrypoint",
  "commands",
  "vars",
]);
const KeysDatabase = new Set(["$id", "name", "enabled"]);
const KeysCollection = new Set([
  "$id",
  "$permissions",
  "databaseId",
  "name",
  "enabled",
  "documentSecurity",
  "attributes",
  "indexes",
]);
const KeysTable = new Set([
  "$id",
  "$permissions",
  "databaseId",
  "name",
  "enabled",
  "rowSecurity",
  "columns",
  "indexes",
]);
const KeysStorage = new Set([
  "$id",
  "$permissions",
  "fileSecurity",
  "name",
  "enabled",
  "maximumFileSize",
  "allowedFileExtensions",
  "compression",
  "encryption",
  "antivirus",
]);
const KeysTopics = new Set(["$id", "name", "subscribe"]);
const KeysTeams = new Set(["$id", "name"]);
const KeysAttributes = new Set([
  "key",
  "type",
  "required",
  "array",
  "size",
  "default",
  // integer and float
  "min",
  "max",
  // email, enum, URL, IP, and datetime
  "format",
  // enum
  "elements",
  // relationship
  "relatedCollection",
  "relationType",
  "twoWay",
  "twoWayKey",
  "onDelete",
  "side",
  // Indexes
  "attributes",
  "orders",
  // Strings
  "encrypt",
]);
const KeysColumns = new Set([
  "key",
  "type",
  "required",
  "array",
  "size",
  "default",
  // integer and float
  "min",
  "max",
  // email, enum, URL, IP, and datetime
  "format",
  // enum
  "elements",
  // relationship
  "relatedTable",
  "relationType",
  "twoWay",
  "twoWayKey",
  "onDelete",
  "side",
  // Indexes
  "columns",
  "orders",
  // Strings
  "encrypt",
]);
const KeyIndexes = new Set(["key", "type", "status", "attributes", "orders"]);
const KeyIndexesColumns = new Set([
  "key",
  "type",
  "status",
  "columns",
  "orders",
]);

const CONFIG_KEY_ORDER = [
  "projectId",
  "projectName",
  "endpoint",
  "settings",
  "functions",
  "sites",
  "databases",
  "collections",
  "tablesDB",
  "tables",
  "buckets",
  "teams",
  "topics",
  "messages",
];

function orderConfigKeys<T extends Record<string, any>>(data: T): T {
  const ordered: Record<string, any> = {};

  for (const key of CONFIG_KEY_ORDER) {
    if (key in data) {
      ordered[key] = data[key];
    }
  }

  for (const key of Object.keys(data)) {
    if (!(key in ordered)) {
      ordered[key] = data[key];
    }
  }

  return ordered as T;
}

function whitelistKeys<T = any>(
  value: T,
  keys: Set<string>,
  nestedKeys: Record<string, Set<string>> = {},
): T {
  if (Array.isArray(value)) {
    const newValue: any[] = [];

    for (const item of value) {
      newValue.push(whitelistKeys(item, keys, nestedKeys));
    }

    return newValue as T;
  }

  const newValue: Record<string, any> = {};
  Object.keys(value as any).forEach((key) => {
    if (keys.has(key)) {
      if (nestedKeys[key]) {
        newValue[key] = whitelistKeys((value as any)[key], nestedKeys[key]);
      } else {
        newValue[key] = (value as any)[key];
      }
    }
  });
  return newValue as T;
}

class Config<T extends ConfigData = ConfigData> {
  readonly path: string;
  protected data: T;

  constructor(path: string) {
    this.path = path;
    this.data = {} as T;
    this.read();
  }

  read(): void {
    try {
      const file = fs.readFileSync(this.path).toString();
      this.data = JSONBigInt.parse(file);
    } catch (e) {
      this.data = {} as T;
    }
  }

  write(): void {
    const dir = _path.dirname(this.path);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }
    fs.writeFileSync(this.path, JSONBigInt.stringify(this.data, null, 4), {
      mode: 0o600,
    });
  }

  get<K extends keyof T>(key: K): T[K];
  get(key: string): any {
    return this.data[key as keyof T];
  }

  set<K extends keyof T>(key: K, value: T[K]): void;
  set(key: string, value: any): void {
    (this.data as any)[key] = value;
    this.write();
  }

  delete(key: string): void {
    delete (this.data as any)[key];
    this.write();
  }

  clear(): void {
    this.data = {} as T;
    this.write();
  }

  has(key: string): boolean {
    return this.data[key as keyof T] !== undefined;
  }

  keys(): string[] {
    return Object.keys(this.data);
  }

  values(): unknown[] {
    return Object.values(this.data);
  }

  toString(): string {
    return JSONBigInt.stringify(this.data, null, 4);
  }

  protected _getDBEntities(entityType: string): Entity[] {
    if (!this.has(entityType)) {
      return [];
    }
    return this.get(entityType) as Entity[];
  }

  protected _getDBEntity(
    entityType: string,
    $id: string,
  ): Entity | Record<string, never> {
    if (!this.has(entityType)) {
      return {};
    }

    const entities = this.get(entityType) as Entity[];
    for (let i = 0; i < entities.length; i++) {
      if (entities[i]["$id"] == $id) {
        return entities[i];
      }
    }

    return {};
  }

  protected _addDBEntity(
    entityType: string,
    props: Entity,
    keysSet: Set<string>,
    nestedKeys: Record<string, Set<string>> = {},
  ): void {
    props = whitelistKeys(props, keysSet, nestedKeys);

    if (!this.has(entityType)) {
      (this.set as (key: string, value: Entity[]) => void)(entityType, []);
    }

    const entities = this.get(entityType) as Entity[];
    for (let i = 0; i < entities.length; i++) {
      if (entities[i]["$id"] == props["$id"]) {
        entities[i] = props;
        (this.set as (key: string, value: Entity[]) => void)(
          entityType,
          entities,
        );
        return;
      }
    }
    entities.push(props);
    (this.set as (key: string, value: Entity[]) => void)(entityType, entities);
  }
}

class Local extends Config<ConfigType> {
  static CONFIG_FILE_PATH = `${EXECUTABLE_NAME}.config.json`;
  static CONFIG_FILE_PATH_LEGACY = `${EXECUTABLE_NAME}.json`;
  configDirectoryPath = "";

  constructor(
    path: string = Local.CONFIG_FILE_PATH,
    legacyPath: string = Local.CONFIG_FILE_PATH_LEGACY,
  ) {
    let absolutePath =
      Local.findConfigFile(path) || Local.findConfigFile(legacyPath);

    if (!absolutePath) {
      absolutePath = `${process.cwd()}/${path}`;
    }

    super(absolutePath);
    this.configDirectoryPath = _path.dirname(absolutePath);
  }

  write(): void {
    const dir = _path.dirname(this.path);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }
    const orderedData = orderConfigKeys(this.data);
    fs.writeFileSync(this.path, JSONBigInt.stringify(orderedData, null, 4), {
      mode: 0o600,
    });
  }

  static findConfigFile(filename: string): string | null {
    let currentPath = process.cwd();

    while (true) {
      const filePath = `${currentPath}/${filename}`;

      if (fs.existsSync(filePath)) {
        return filePath;
      }

      const parentDirectory = _path.dirname(currentPath);
      if (parentDirectory === currentPath) {
        break;
      }
      currentPath = parentDirectory;
    }

    return null;
  }

  getDirname(): string {
    return _path.dirname(this.path);
  }

  getEndpoint(): string {
    return this.get("endpoint") || "";
  }

  setEndpoint(endpoint: string): void {
    this.set("endpoint", endpoint);
  }

  getSites(): SiteType[] {
    if (!this.has("sites")) {
      return [];
    }
    return this.get("sites") ?? [];
  }

  getSite($id: string): SiteType | Record<string, never> {
    if (!this.has("sites")) {
      return {};
    }

    const sites = this.get("sites") ?? [];
    for (let i = 0; i < sites.length; i++) {
      if (sites[i]["$id"] == $id) {
        return sites[i];
      }
    }

    return {};
  }

  addSite(props: SiteType): void {
    props = whitelistKeys(props, KeysSite, {
      vars: KeysVars,
    });

    if (!this.has("sites")) {
      this.set("sites", []);
    }

    const sites = this.get("sites") ?? [];
    for (let i = 0; i < sites.length; i++) {
      if (sites[i]["$id"] == props["$id"]) {
        sites[i] = {
          ...sites[i],
          ...props,
        };
        this.set("sites", sites);
        return;
      }
    }

    sites.push(props);
    this.set("sites", sites);
  }

  getFunctions(): FunctionType[] {
    if (!this.has("functions")) {
      return [];
    }
    return this.get("functions") ?? [];
  }

  getFunction($id: string): FunctionType | Record<string, never> {
    if (!this.has("functions")) {
      return {};
    }

    const functions = this.get("functions") ?? [];
    for (let i = 0; i < functions.length; i++) {
      if (functions[i]["$id"] == $id) {
        return functions[i];
      }
    }

    return {};
  }

  addFunction(props: FunctionType): void {
    props = whitelistKeys(props, KeysFunction, {
      vars: KeysVars,
    });

    if (!this.has("functions")) {
      this.set("functions", []);
    }

    const functions = this.get("functions") ?? [];
    for (let i = 0; i < functions.length; i++) {
      if (functions[i]["$id"] == props["$id"]) {
        functions[i] = {
          ...functions[i],
          ...props,
        };
        this.set("functions", functions);
        return;
      }
    }

    functions.push(props);
    this.set("functions", functions);
  }

  getCollections(): CollectionType[] {
    if (!this.has("collections")) {
      return [];
    }
    return this.get("collections") ?? [];
  }

  getCollection($id: string): CollectionType | Record<string, never> {
    if (!this.has("collections")) {
      return {};
    }

    const collections = this.get("collections") ?? [];
    for (let i = 0; i < collections.length; i++) {
      if (collections[i]["$id"] == $id) {
        return collections[i];
      }
    }

    return {};
  }

  addCollection(props: CollectionType): void {
    props = whitelistKeys(props, KeysCollection, {
      attributes: KeysAttributes,
      indexes: KeyIndexes,
    });

    if (!this.has("collections")) {
      this.set("collections", []);
    }

    const collections = this.get("collections") ?? [];
    for (let i = 0; i < collections.length; i++) {
      if (
        collections[i]["$id"] == props["$id"] &&
        collections[i]["databaseId"] == props["databaseId"]
      ) {
        collections[i] = props;
        this.set("collections", collections);
        return;
      }
    }
    collections.push(props);
    this.set("collections", collections);
  }

  getTables(): TableType[] {
    if (!this.has("tables")) {
      return [];
    }
    return this.get("tables") ?? [];
  }

  getTable($id: string): TableType | Record<string, never> {
    if (!this.has("tables")) {
      return {};
    }

    const tables = this.get("tables") ?? [];
    for (let i = 0; i < tables.length; i++) {
      if (tables[i]["$id"] == $id) {
        return tables[i];
      }
    }

    return {};
  }

  addTable(props: TableType): void {
    props = whitelistKeys(props, KeysTable, {
      columns: KeysColumns,
      indexes: KeyIndexesColumns,
    });

    if (!this.has("tables")) {
      this.set("tables", []);
    }

    const tables = this.get("tables") ?? [];
    for (let i = 0; i < tables.length; i++) {
      if (
        tables[i]["$id"] == props["$id"] &&
        tables[i]["databaseId"] == props["databaseId"]
      ) {
        tables[i] = props;
        this.set("tables", tables);
        return;
      }
    }
    tables.push(props);
    this.set("tables", tables);
  }

  getBuckets(): BucketType[] {
    if (!this.has("buckets")) {
      return [];
    }
    return this.get("buckets") ?? [];
  }

  getBucket($id: string): BucketType | Record<string, never> {
    if (!this.has("buckets")) {
      return {};
    }

    const buckets = this.get("buckets") ?? [];
    for (let i = 0; i < buckets.length; i++) {
      if (buckets[i]["$id"] == $id) {
        return buckets[i];
      }
    }

    return {};
  }

  addBucket(props: BucketType): void {
    props = whitelistKeys(props, KeysStorage);

    if (!this.has("buckets")) {
      this.set("buckets", []);
    }

    const buckets = this.get("buckets") ?? [];
    for (let i = 0; i < buckets.length; i++) {
      if (buckets[i]["$id"] == props["$id"]) {
        buckets[i] = props;
        this.set("buckets", buckets);
        return;
      }
    }
    buckets.push(props);
    this.set("buckets", buckets);
  }

  getMessagingTopics(): TopicType[] {
    if (!this.has("topics")) {
      return [];
    }
    return this.get("topics") ?? [];
  }

  getMessagingTopic($id: string): TopicType | Record<string, never> {
    if (!this.has("topics")) {
      return {};
    }

    const topics = this.get("topics") ?? [];
    for (let i = 0; i < topics.length; i++) {
      if (topics[i]["$id"] == $id) {
        return topics[i];
      }
    }

    return {};
  }

  addMessagingTopic(props: TopicType): void {
    props = whitelistKeys(props, KeysTopics);

    if (!this.has("topics")) {
      this.set("topics", []);
    }

    const topics = this.get("topics") ?? [];
    for (let i = 0; i < topics.length; i++) {
      if (topics[i]["$id"] === props["$id"]) {
        topics[i] = props;
        this.set("topics", topics);
        return;
      }
    }
    topics.push(props);
    this.set("topics", topics);
  }

  getTablesDBs(): any[] {
    return this._getDBEntities("tablesDB");
  }

  getTablesDB($id: string): any {
    return this._getDBEntity("tablesDB", $id);
  }

  addTablesDB(props: any): void {
    this._addDBEntity("tablesDB", props, KeysDatabase);
  }

  getDatabases(): any[] {
    return this._getDBEntities("databases");
  }

  getDatabase($id: string): any {
    return this._getDBEntity("databases", $id);
  }

  addDatabase(props: any): void {
    this._addDBEntity("databases", props, KeysDatabase);
  }

  getTeams(): TeamType[] {
    if (!this.has("teams")) {
      return [];
    }
    return this.get("teams") ?? [];
  }

  getTeam($id: string): TeamType | Record<string, never> {
    if (!this.has("teams")) {
      return {};
    }

    const teams = this.get("teams") ?? [];
    for (let i = 0; i < teams.length; i++) {
      if (teams[i]["$id"] == $id) {
        return teams[i];
      }
    }

    return {};
  }

  addTeam(props: TeamType): void {
    props = whitelistKeys(props, KeysTeams);
    if (!this.has("teams")) {
      this.set("teams", []);
    }

    const teams = this.get("teams") ?? [];
    for (let i = 0; i < teams.length; i++) {
      if (teams[i]["$id"] == props["$id"]) {
        teams[i] = props;
        this.set("teams", teams);
        return;
      }
    }
    teams.push(props);
    this.set("teams", teams);
  }

  getProject(): {
    projectId?: string;
    projectName?: string;
    projectSettings?: SettingsType;
  } {
    if (!this.has("projectId")) {
      return {};
    }

    return {
      projectId: this.get("projectId"),
      projectName: this.get("projectName"),
      projectSettings: this.get("settings"),
    };
  }

  setProject(
    projectId: string,
    projectName: string = "",
    project?: Models.Project,
  ): void {
    this.set("projectId", projectId);

    if (projectName !== "") {
      this.set("projectName", projectName);
    }

    if (project === undefined) {
      return;
    }

    this.set("settings", createSettingsObject(project));
  }
}

class Global extends Config<GlobalConfigData> {
  static CONFIG_FILE_PATH = `.${EXECUTABLE_NAME}/prefs.json`;

  static PREFERENCE_CURRENT = "current" as const;
  static PREFERENCE_ENDPOINT = "endpoint" as const;
  static PREFERENCE_EMAIL = "email" as const;
  static PREFERENCE_SELF_SIGNED = "selfSigned" as const;
  static PREFERENCE_COOKIE = "cookie" as const;
  static PREFERENCE_PROJECT = "project" as const;
  static PREFERENCE_KEY = "key" as const;
  static PREFERENCE_LOCALE = "locale" as const;
  static PREFERENCE_MODE = "mode" as const;

  static IGNORE_ATTRIBUTES: readonly string[] = [
    Global.PREFERENCE_CURRENT,
    Global.PREFERENCE_SELF_SIGNED,
    Global.PREFERENCE_ENDPOINT,
    Global.PREFERENCE_COOKIE,
    Global.PREFERENCE_PROJECT,
    Global.PREFERENCE_KEY,
    Global.PREFERENCE_LOCALE,
    Global.PREFERENCE_MODE,
  ];

  static MODE_ADMIN = "admin";
  static MODE_DEFAULT = "default";

  static PROJECT_CONSOLE = "console";

  constructor(path: string = Global.CONFIG_FILE_PATH) {
    const homeDir = os.homedir();
    super(`${homeDir}/${path}`);
  }

  getCurrentSession(): string {
    if (!this.has(Global.PREFERENCE_CURRENT)) {
      return "";
    }
    return this.get(Global.PREFERENCE_CURRENT);
  }

  setCurrentSession(session: string): void {
    if (session !== undefined) {
      this.set(Global.PREFERENCE_CURRENT, session);
    }
  }

  getSessionIds(): string[] {
    return Object.keys(this.data).filter(
      (key) => !Global.IGNORE_ATTRIBUTES.includes(key),
    );
  }

  getSessions(): Array<{ id: string; endpoint: string; email: string }> {
    const sessions = Object.keys(this.data).filter(
      (key) => !Global.IGNORE_ATTRIBUTES.includes(key),
    );
    const current = this.getCurrentSession();

    const sessionMap = new Map<
      string,
      { id: string; endpoint: string; email: string }
    >();

    sessions.forEach((sessionId) => {
      const sessionData = (this.data as any)[sessionId];
      const email = sessionData[Global.PREFERENCE_EMAIL];
      const endpoint = sessionData[Global.PREFERENCE_ENDPOINT];
      const key = `${email}|${endpoint}`;

      if (sessionId === current || !sessionMap.has(key)) {
        sessionMap.set(key, {
          id: sessionId,
          endpoint: sessionData[Global.PREFERENCE_ENDPOINT],
          email: sessionData[Global.PREFERENCE_EMAIL],
        });
      }
    });

    return Array.from(sessionMap.values());
  }

  addSession(session: string, data: SessionData): void {
    this.set(session as any, data as any);
  }

  removeSession(session: string): void {
    this.delete(session);
  }

  getEmail(): string {
    if (!this.hasFrom(Global.PREFERENCE_EMAIL)) {
      return "";
    }

    return this.getFrom(Global.PREFERENCE_EMAIL);
  }

  setEmail(email: string): void {
    this.setTo(Global.PREFERENCE_EMAIL, email);
  }

  getEndpoint(): string {
    if (!this.hasFrom(Global.PREFERENCE_ENDPOINT)) {
      return "";
    }

    return this.getFrom(Global.PREFERENCE_ENDPOINT);
  }

  setEndpoint(endpoint: string): void {
    this.setTo(Global.PREFERENCE_ENDPOINT, endpoint);
  }

  getSelfSigned(): boolean {
    if (!this.hasFrom(Global.PREFERENCE_SELF_SIGNED)) {
      return false;
    }
    return this.getFrom(Global.PREFERENCE_SELF_SIGNED);
  }

  setSelfSigned(selfSigned: boolean): void {
    this.setTo(Global.PREFERENCE_SELF_SIGNED, selfSigned);
  }

  getCookie(): string {
    if (!this.hasFrom(Global.PREFERENCE_COOKIE)) {
      return "";
    }
    return this.getFrom(Global.PREFERENCE_COOKIE);
  }

  setCookie(cookie: string): void {
    this.setTo(Global.PREFERENCE_COOKIE, cookie);
  }

  getProject(): string {
    if (!this.hasFrom(Global.PREFERENCE_PROJECT)) {
      return "";
    }
    return this.getFrom(Global.PREFERENCE_PROJECT);
  }

  setProject(project: string): void {
    this.setTo(Global.PREFERENCE_PROJECT, project);
  }

  getKey(): string {
    if (!this.hasFrom(Global.PREFERENCE_KEY)) {
      return "";
    }
    return this.getFrom(Global.PREFERENCE_KEY);
  }

  setKey(key: string): void {
    this.setTo(Global.PREFERENCE_KEY, key);
  }

  hasFrom(key: string): boolean {
    const current = this.getCurrentSession();

    if (current) {
      const config = this.get(current as any) ?? {};

      return (config as any)[key] !== undefined;
    }
    return false;
  }

  getFrom(key: string): any {
    const current = this.getCurrentSession();

    if (current) {
      const config = this.get(current as any) ?? {};

      return (config as any)[key];
    }
  }

  setTo(key: string, value: any): void {
    const current = this.getCurrentSession();

    if (current) {
      const config = this.get(current as any);

      (config as any)[key] = value;
      this.write();
    }
  }
}

export const localConfig = new Local();
export const globalConfig = new Global();
export {
  KeysAttributes,
  KeysSite,
  KeysFunction,
  KeysTopics,
  KeysStorage,
  KeysTeams,
  KeysCollection,
  KeysTable,
  whitelistKeys,
};
