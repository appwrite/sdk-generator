import fs from "fs";
import os from "os";
import path from "path";
import net from "net";
import childProcess from "child_process";
import type { Models } from "@appwrite.io/console";
import { z } from "zod";
import { globalConfig } from "./config.js";
import type { SettingsType } from "./commands/config.js";
import {
  NPM_REGISTRY_URL,
  NPM_PACKAGE_NAME,
  DEFAULT_ENDPOINT,
  EXECUTABLE_NAME,
  UPDATE_CHECK_INTERVAL_MS,
} from "./constants.js";

export const createSettingsObject = (project: Models.Project): SettingsType => {
  return {
    services: {
      account: project.serviceStatusForAccount,
      avatars: project.serviceStatusForAvatars,
      databases: project.serviceStatusForDatabases,
      locale: project.serviceStatusForLocale,
      health: project.serviceStatusForHealth,
      storage: project.serviceStatusForStorage,
      teams: project.serviceStatusForTeams,
      users: project.serviceStatusForUsers,
      sites: project.serviceStatusForSites,
      functions: project.serviceStatusForFunctions,
      graphql: project.serviceStatusForGraphql,
      messaging: project.serviceStatusForMessaging,
    },
    protocols: {
      rest: project.protocolStatusForRest,
      graphql: project.protocolStatusForGraphql,
      websocket: project.protocolStatusForWebsocket,
    },
    auth: {
      methods: {
        jwt: project.authJWT,
        phone: project.authPhone,
        invites: project.authInvites,
        anonymous: project.authAnonymous,
        "email-otp": project.authEmailOtp,
        "magic-url": project.authUsersAuthMagicURL,
        "email-password": project.authEmailPassword,
      },
      security: {
        duration: project.authDuration,
        limit: project.authLimit,
        sessionsLimit: project.authSessionsLimit,
        passwordHistory: project.authPasswordHistory,
        passwordDictionary: project.authPasswordDictionary,
        personalDataCheck: project.authPersonalDataCheck,
        sessionAlerts: project.authSessionAlerts,
        mockNumbers: project.authMockNumbers,
      },
    },
  };
};

export const getSafeDirectoryName = (
  value: string,
  fallback: string,
): string => {
  const normalized = value
    .normalize("NFKD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");

  return normalized || fallback;
};

type SiteBuildConfig = {
  framework?: string;
  adapter?: string;
};

export const siteRequiresBuildCommand = (site: SiteBuildConfig): boolean => {
  return !(site.framework === "other" && site.adapter === "static");
};

export const getErrorMessage = (error: unknown): string => {
  if (error instanceof Error) {
    return error.message;
  }

  return String(error);
};

export type InstallationMethod = "npm" | "homebrew" | "standalone";
type LatestVersionSource = "npm" | "homebrew";
type HomebrewInfoResponse = {
  formulae?: Array<{
    versions?: {
      stable?: string;
    };
  }>;
};

const WINDOWS_EXECUTABLE_NAME = `${EXECUTABLE_NAME.toLowerCase()}.exe`;
const HOMEBREW_FORMULA_NAME = EXECUTABLE_NAME.toLowerCase();

const getExecutablePaths = (): {
  execPath: string;
  realExecPath: string;
  scriptPath: string;
} => {
  const execPath = process.execPath;
  const scriptPath = process.argv[1] ?? "";

  try {
    return {
      execPath,
      realExecPath: fs.realpathSync.native(execPath),
      scriptPath,
    };
  } catch (_error) {
    return {
      execPath,
      realExecPath: execPath,
      scriptPath,
    };
  }
};

const isExecutableName = (candidatePath: string): boolean => {
  const basename = path.basename(candidatePath).toLowerCase();
  return (
    basename === EXECUTABLE_NAME.toLowerCase() ||
    basename === WINDOWS_EXECUTABLE_NAME
  );
};

const isHomebrewManagedPath = (candidatePath: string): boolean => {
  return (
    candidatePath.includes("/opt/homebrew/") ||
    candidatePath.includes("/usr/local/Cellar/") ||
    candidatePath.includes("/home/linuxbrew/.linuxbrew/") ||
    candidatePath.includes("/linuxbrew/.linuxbrew/")
  );
};

const isInstalledViaNpm = (): boolean => {
  try {
    const { scriptPath } = getExecutablePaths();

    if (
      scriptPath.includes("node_modules") &&
      scriptPath.includes(NPM_PACKAGE_NAME)
    ) {
      return true;
    }

    if (
      scriptPath.includes("/usr/local/lib/node_modules/") ||
      scriptPath.includes("/opt/homebrew/lib/node_modules/") ||
      scriptPath.includes("/.npm-global/") ||
      scriptPath.includes("/node_modules/.bin/") ||
      scriptPath.includes("/.nvm/versions/node/")
    ) {
      return true;
    }

    return false;
  } catch (_error) {
    return false;
  }
};

const isInstalledViaHomebrew = (): boolean => {
  try {
    const { execPath, realExecPath, scriptPath } = getExecutablePaths();
    const runtimeCandidates = [execPath, realExecPath].filter(isExecutableName);

    return [scriptPath, ...runtimeCandidates].some(isHomebrewManagedPath);
  } catch (_error) {
    return false;
  }
};

const isInstalledViaStandaloneBinary = (): boolean => {
  if (isInstalledViaNpm() || isInstalledViaHomebrew()) {
    return false;
  }

  try {
    const { execPath, realExecPath } = getExecutablePaths();
    return [execPath, realExecPath].some(isExecutableName);
  } catch (_error) {
    return false;
  }
};

export const detectInstallationMethod = (): InstallationMethod | null => {
  if (isInstalledViaNpm()) {
    return "npm";
  }

  if (isInstalledViaHomebrew()) {
    return "homebrew";
  }

  if (isInstalledViaStandaloneBinary()) {
    return "standalone";
  }

  return null;
};

const getLatestVersionSource = (
  method: InstallationMethod | null = detectInstallationMethod(),
): LatestVersionSource => {
  return method === "homebrew" ? "homebrew" : "npm";
};

const normalizeHomebrewVersion = (version: string): string => {
  return version.split("_")[0].trim();
};

const getHomebrewLatestVersion = async (
  options: { timeoutMs?: number } = {},
): Promise<string> => {
  try {
    const output = childProcess.execFileSync(
      "brew",
      ["info", "--json=v2", HOMEBREW_FORMULA_NAME],
      {
        encoding: "utf8",
        stdio: ["ignore", "pipe", "pipe"],
        timeout: options.timeoutMs,
        env: {
          ...process.env,
          HOMEBREW_NO_AUTO_UPDATE: "1",
        },
      },
    );

    const parsed = JSON.parse(output) as HomebrewInfoResponse;
    const stableVersion = parsed.formulae?.[0]?.versions?.stable;

    if (typeof stableVersion !== "string" || stableVersion.trim() === "") {
      throw new Error("Homebrew did not return a stable formula version.");
    }

    return normalizeHomebrewVersion(stableVersion);
  } catch (error) {
    throw new Error(
      `Failed to fetch latest Homebrew version: ${getErrorMessage(error)}`,
    );
  }
};

const isCloudHostname = (hostname: string): boolean =>
  hostname === "cloud.appwrite.io" || hostname.endsWith(".cloud.appwrite.io");

export const getConsoleBaseUrl = (endpoint: string): string => {
  try {
    const url = new URL(endpoint);

    if (isCloudHostname(url.hostname)) {
      url.hostname = "cloud.appwrite.io";
    }

    url.pathname = url.pathname.replace(/\/v1\/?$/, "");
    url.search = "";
    url.hash = "";

    return url.toString().replace(/\/$/, "");
  } catch {
    return endpoint.replace(/\/v1\/?$/, "");
  }
};

export const getConsoleProjectSlug = (
  endpoint: string,
  projectId: string,
): string => {
  try {
    const hostname = new URL(endpoint).hostname;

    if (!isCloudHostname(hostname)) {
      return `project-${projectId}`;
    }

    const firstSubdomain = hostname.split(".")[0];
    return firstSubdomain.length === 3
      ? `project-${firstSubdomain}-${projectId}`
      : `project-${projectId}`;
  } catch {
    return `project-${projectId}`;
  }
};

export const getFunctionDeploymentConsoleUrl = (
  endpoint: string,
  projectId: string,
  functionId: string,
  deploymentId: string,
): string => {
  const projectSlug = getConsoleProjectSlug(endpoint, projectId);
  return `${getConsoleBaseUrl(endpoint)}/console/${projectSlug}/functions/function-${functionId}/deployment-${deploymentId}`;
};

export const getSiteDeploymentConsoleUrl = (
  endpoint: string,
  projectId: string,
  siteId: string,
  deploymentId: string,
): string => {
  const projectSlug = getConsoleProjectSlug(endpoint, projectId);
  return `${getConsoleBaseUrl(endpoint)}/console/${projectSlug}/sites/site-${siteId}/deployments/deployment-${deploymentId}`;
};

type UpdateCheckCache = {
  checkedAt?: string;
  latestVersion?: string;
  notifiedAt?: string;
  checkedOn?: string;
  notifiedOn?: string;
  source?: LatestVersionSource;
};

const UPDATE_CHECK_FILE_NAME = "update-check.json";
const DEFAULT_UPDATE_CHECK_TIMEOUT_MS = 250;

const getCurrentTimestamp = (): string => {
  return new Date().toISOString();
};

const normalizeLegacyDate = (dateStamp?: string): string | undefined => {
  if (typeof dateStamp !== "string" || dateStamp.trim() === "") {
    return undefined;
  }

  const parsed = new Date(`${dateStamp}T00:00:00Z`);
  if (Number.isNaN(parsed.getTime())) {
    return undefined;
  }

  return parsed.toISOString();
};

const isWithinUpdateInterval = (timestamp?: string): boolean => {
  if (typeof timestamp !== "string" || timestamp.trim() === "") {
    return false;
  }

  const parsedTimestamp = new Date(timestamp).getTime();
  if (Number.isNaN(parsedTimestamp)) {
    return false;
  }

  return Date.now() - parsedTimestamp < UPDATE_CHECK_INTERVAL_MS;
};

const getCliConfigDirectory = (): string => {
  const xdgConfigHome = process.env.XDG_CONFIG_HOME?.trim();
  if (xdgConfigHome) {
    return path.join(xdgConfigHome, EXECUTABLE_NAME);
  }

  if (process.platform === "win32") {
    return path.join(
      process.env.APPDATA || path.join(os.homedir(), "AppData", "Roaming"),
      EXECUTABLE_NAME,
    );
  }

  return path.join(os.homedir(), ".config", EXECUTABLE_NAME);
};

const getUpdateCheckCachePath = (): string => {
  return path.join(getCliConfigDirectory(), UPDATE_CHECK_FILE_NAME);
};

const readUpdateCheckCache = (): UpdateCheckCache | null => {
  try {
    const raw = fs.readFileSync(getUpdateCheckCachePath(), "utf8");
    const parsed = JSON.parse(raw) as UpdateCheckCache;

    if (!parsed || typeof parsed !== "object") {
      return null;
    }

    return {
      checkedAt:
        typeof parsed.checkedAt === "string"
          ? parsed.checkedAt
          : normalizeLegacyDate(parsed.checkedOn),
      latestVersion:
        typeof parsed.latestVersion === "string"
          ? parsed.latestVersion
          : undefined,
      notifiedAt:
        typeof parsed.notifiedAt === "string"
          ? parsed.notifiedAt
          : normalizeLegacyDate(parsed.notifiedOn),
      source:
        parsed.source === "npm" || parsed.source === "homebrew"
          ? parsed.source
          : undefined,
    };
  } catch (_error) {
    return null;
  }
};

const writeUpdateCheckCache = (cache: UpdateCheckCache): void => {
  const cachePath = getUpdateCheckCachePath();
  const cacheDir = path.dirname(cachePath);

  if (!fs.existsSync(cacheDir)) {
    fs.mkdirSync(cacheDir, { recursive: true });
  }

  fs.writeFileSync(cachePath, JSON.stringify(cache, null, 2), {
    mode: 0o600,
  });
};

const tryWriteUpdateCheckCache = (cache: UpdateCheckCache): void => {
  try {
    writeUpdateCheckCache(cache);
  } catch (_error) {
    // Ignore cache write failures so notifications still work on restricted filesystems.
  }
};

const getCacheForVersionSource = (
  cache: UpdateCheckCache | null,
  source: LatestVersionSource,
): UpdateCheckCache | null => {
  if (!cache) {
    return null;
  }

  if (typeof cache.source === "string") {
    return cache.source === source ? cache : null;
  }

  // Legacy caches without a source were populated from npm.
  return source === "npm" ? cache : null;
};

export const syncVersionCheckCache = (
  currentVersion: string,
  latestVersion: string,
): void => {
  const now = getCurrentTimestamp();
  const source = getLatestVersionSource();
  const existingCache = getCacheForVersionSource(
    readUpdateCheckCache(),
    source,
  );
  const updateAvailable = compareVersions(currentVersion, latestVersion) > 0;

  tryWriteUpdateCheckCache({
    checkedAt: now,
    latestVersion,
    notifiedAt: updateAvailable ? now : existingCache?.notifiedAt,
    source,
  });
};

/**
 * Get the latest version from npm registry
 */
export async function getLatestVersion(
  options: { timeoutMs?: number } = {},
): Promise<string> {
  try {
    const timeoutMs = options.timeoutMs;
    const signal =
      typeof timeoutMs === "number"
        ? AbortSignal.timeout(timeoutMs)
        : undefined;

    const response = await fetch(NPM_REGISTRY_URL, { signal });
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    const data = (await response.json()) as { version: string };
    return data.version;
  } catch (e) {
    throw new Error(`Failed to fetch latest version: ${(e as Error).message}`);
  }
}

export async function getLatestVersionForInstallation(
  method: InstallationMethod | null,
  options: { timeoutMs?: number } = {},
): Promise<string> {
  switch (getLatestVersionSource(method)) {
    case "homebrew":
      return getHomebrewLatestVersion(options);
    case "npm":
    default:
      return getLatestVersion(options);
  }
}

export async function getLatestVersionForCurrentInstallation(
  options: { timeoutMs?: number } = {},
): Promise<string> {
  return getLatestVersionForInstallation(detectInstallationMethod(), options);
}

/**
 * Compare versions using semantic versioning
 */
export function compareVersions(current: string, latest: string): number {
  const currentParts = current.split(".").map(Number);
  const latestParts = latest.split(".").map(Number);

  for (let i = 0; i < Math.max(currentParts.length, latestParts.length); i++) {
    const currentPart = currentParts[i] || 0;
    const latestPart = latestParts[i] || 0;

    if (latestPart > currentPart) return 1; // Latest is newer
    if (latestPart < currentPart) return -1; // Current is newer
  }

  return 0; // Same version
}

export async function getCachedUpdateNotification(
  currentVersion: string,
): Promise<string | null> {
  const now = getCurrentTimestamp();
  const installationMethod = detectInstallationMethod();
  const source = getLatestVersionSource(installationMethod);
  const cache = getCacheForVersionSource(readUpdateCheckCache(), source);

  if (isWithinUpdateInterval(cache?.checkedAt)) {
    if (!cache) {
      return null;
    }

    const hasFreshUpdate =
      typeof cache.latestVersion === "string" &&
      compareVersions(currentVersion, cache.latestVersion) > 0;

    if (hasFreshUpdate && !isWithinUpdateInterval(cache?.notifiedAt)) {
      tryWriteUpdateCheckCache({
        ...cache,
        notifiedAt: now,
      });

      return cache.latestVersion ?? null;
    }

    return null;
  }

  try {
    const latestVersion = await getLatestVersionForInstallation(
      installationMethod,
      {
        timeoutMs: DEFAULT_UPDATE_CHECK_TIMEOUT_MS,
      },
    );
    const updateAvailable = compareVersions(currentVersion, latestVersion) > 0;
    const alreadyNotifiedRecently = isWithinUpdateInterval(cache?.notifiedAt);

    tryWriteUpdateCheckCache({
      checkedAt: now,
      latestVersion,
      notifiedAt:
        updateAvailable && !alreadyNotifiedRecently ? now : cache?.notifiedAt,
      source,
    });

    return updateAvailable && !alreadyNotifiedRecently ? latestVersion : null;
  } catch (_error) {
    const cachedVersion = cache?.latestVersion;
    const hasCachedUpdate =
      typeof cachedVersion === "string" &&
      compareVersions(currentVersion, cachedVersion) > 0;

    if (hasCachedUpdate && !isWithinUpdateInterval(cache?.notifiedAt)) {
      tryWriteUpdateCheckCache({
        ...cache,
        notifiedAt: now,
      });

      return cachedVersion;
    }

    return null;
  }
}

export function getAllFiles(folder: string): string[] {
  const files: string[] = [];
  for (const pathDir of fs.readdirSync(folder)) {
    const pathAbsolute = path.join(folder, pathDir);
    let stats: fs.Stats;
    try {
      stats = fs.statSync(pathAbsolute);
    } catch (_error) {
      continue;
    }
    if (stats.isDirectory()) {
      files.push(...getAllFiles(pathAbsolute));
    } else {
      files.push(pathAbsolute);
    }
  }
  return files;
}

export async function isPortTaken(port: number): Promise<boolean> {
  const taken = await new Promise<boolean>((res, rej) => {
    const tester = net
      .createServer()
      .once("error", function (err: NodeJS.ErrnoException) {
        if (err.code != "EADDRINUSE") return rej(err);
        res(true);
      })
      .once("listening", function () {
        tester
          .once("close", function () {
            res(false);
          })
          .close();
      })
      .listen(port);
  });

  return taken;
}

export function systemHasCommand(command: string): boolean {
  const isUsingWindows = process.platform == "win32";

  try {
    if (isUsingWindows) {
      childProcess.execSync("where " + command, { stdio: "pipe" });
    } else {
      childProcess.execSync(
        `[[ $(${command} --version) ]] || { exit 1; } && echo "OK"`,
        {
          stdio: "pipe",
          shell: "/bin/bash",
        },
      );
    }
  } catch (error) {
    console.log(error);
    return false;
  }

  return true;
}

type DeployLocalConfig = {
  keys: () => string[];
};

export const checkDeployConditions = (localConfig: DeployLocalConfig): void => {
  if (localConfig.keys().length === 0) {
    throw new Error(
      "No appwrite.config.json file found in the current directory. Please run this command again in the folder containing your appwrite.config.json file, or run 'appwrite init project' to link current directory to an Appwrite project.",
    );
  }
};

export function isCloud(): boolean {
  const endpoint = globalConfig.getEndpoint() || DEFAULT_ENDPOINT;
  const hostname = new URL(endpoint).hostname;
  return hostname.endsWith("appwrite.io");
}

// --- Agent Skills helpers ---

const SKILLS_REPO = "https://github.com/appwrite/agent-skills";

const LANGUAGE_MARKERS: Record<string, string[]> = {
  typescript: [
    "package.json",
    "tsconfig.json",
    "bun.lockb",
    "yarn.lock",
    "package-lock.json",
  ],
  python: ["requirements.txt", "pyproject.toml", "setup.py", "Pipfile"],
  php: ["composer.json"],
  dart: ["pubspec.yaml"],
  swift: ["Package.swift", "*.xcodeproj"],
  kotlin: ["build.gradle.kts", "build.gradle"],
  go: ["go.mod"],
  ruby: ["Gemfile"],
  dotnet: ["*.csproj", "*.sln", "*.fsproj"],
};

export interface SkillInfo {
  name: string;
  description: string;
  dirName: string;
}

const parseSkillFrontmatter = (
  content: string,
): { name: string; description: string } => {
  const match = content.match(/^---\s*\n([\s\S]*?)\n---/);
  if (!match) return { name: "", description: "" };

  const frontmatter = match[1];
  const nameMatch = frontmatter.match(/^name:\s*(.+)$/m);
  const descMatch = frontmatter.match(/^description:\s*(.+)$/m);

  return {
    name: nameMatch ? nameMatch[1].trim() : "",
    description: descMatch ? descMatch[1].trim() : "",
  };
};

export function hasSkillsInstalled(configDirectoryPath: string): boolean {
  const skillsDirs = [
    path.join(configDirectoryPath, ".agents", "skills"),
    path.join(configDirectoryPath, ".claude", "skills"),
  ];
  return skillsDirs.some(
    (dir) =>
      fs.existsSync(dir) &&
      fs.statSync(dir).isDirectory() &&
      fs.readdirSync(dir).length > 0,
  );
}

export function fetchAvailableSkills(): {
  skills: SkillInfo[];
  tempDir: string;
} {
  const tempDir = fs.mkdtempSync(path.join(os.tmpdir(), "appwrite-skills-"));

  let gitInitCommands = `git clone --single-branch --depth 1 --sparse ${SKILLS_REPO} .`;
  let gitPullCommands = `git sparse-checkout add skills`;

  if (process.platform === "win32") {
    gitInitCommands = 'cmd /c "' + gitInitCommands + '"';
    gitPullCommands = 'cmd /c "' + gitPullCommands + '"';
  }

  try {
    childProcess.execSync(gitInitCommands, { stdio: "pipe", cwd: tempDir });
    childProcess.execSync(gitPullCommands, { stdio: "pipe", cwd: tempDir });
  } catch (err) {
    fs.rmSync(tempDir, { recursive: true, force: true });
    const errorMessage = err instanceof Error ? err.message : String(err);
    if (errorMessage.includes("error: unknown option")) {
      throw new Error(
        `${errorMessage}\n\nSuggestion: Try updating your git to the latest version, then trying to run this command again.`,
      );
    } else if (
      errorMessage.includes(
        "is not recognized as an internal or external command,",
      ) ||
      errorMessage.includes("command not found")
    ) {
      throw new Error(
        `${errorMessage}\n\nSuggestion: It appears that git is not installed, try installing git then trying to run this command again.`,
      );
    }
    throw err;
  }

  const skillsSrcDir = path.join(tempDir, "skills");
  if (!fs.existsSync(skillsSrcDir)) {
    fs.rmSync(tempDir, { recursive: true, force: true });
    throw new Error("No skills directory found in the repository.");
  }

  const skillDirs = fs
    .readdirSync(skillsSrcDir, { withFileTypes: true })
    .filter((entry) => entry.isDirectory());

  const skills: SkillInfo[] = [];
  for (const dir of skillDirs) {
    const skillMdPath = path.join(skillsSrcDir, dir.name, "SKILL.md");
    if (fs.existsSync(skillMdPath)) {
      const content = fs.readFileSync(skillMdPath, "utf-8");
      const { name, description } = parseSkillFrontmatter(content);
      skills.push({
        name: name || dir.name,
        description,
        dirName: dir.name,
      });
    }
  }

  if (skills.length === 0) {
    fs.rmSync(tempDir, { recursive: true, force: true });
    throw new Error("No skills found in the repository.");
  }

  return { skills, tempDir };
}

export function detectProjectSkills(
  cwd: string,
  skills: SkillInfo[],
): SkillInfo[] {
  const detected: Set<string> = new Set();

  for (const [language, markers] of Object.entries(LANGUAGE_MARKERS)) {
    for (const marker of markers) {
      if (marker.includes("*")) {
        const ext = marker.replace("*", "");
        try {
          const files = fs.readdirSync(cwd);
          if (files.some((f) => f.endsWith(ext))) {
            detected.add(language);
          }
        } catch {
          // ignore read errors
        }
      } else if (fs.existsSync(path.join(cwd, marker))) {
        detected.add(language);
      }
    }
  }

  // Always include the CLI skill
  detected.add("cli");

  return skills.filter((skill) =>
    Array.from(detected).some((lang) =>
      skill.dirName.toLowerCase().includes(lang),
    ),
  );
}

export function placeSkills(
  cwd: string,
  tempDir: string,
  selectedDirNames: string[],
  selectedAgents: string[],
  useSymlinks: boolean,
): void {
  const skillsSrcDir = path.join(tempDir, "skills");

  if (useSymlinks && selectedAgents.length > 1) {
    const canonicalAgent = selectedAgents[0];
    const canonicalBase = path.join(cwd, canonicalAgent, "skills");
    fs.mkdirSync(canonicalBase, { recursive: true });

    for (const dirName of selectedDirNames) {
      const src = path.join(skillsSrcDir, dirName);
      const dest = path.join(canonicalBase, dirName);
      if (fs.existsSync(dest)) {
        fs.rmSync(dest, { recursive: true, force: true });
      }
      fs.cpSync(src, dest, { recursive: true });
    }

    for (const agent of selectedAgents.slice(1)) {
      const targetBase = path.join(cwd, agent, "skills");
      fs.mkdirSync(targetBase, { recursive: true });

      for (const dirName of selectedDirNames) {
        const canonicalSkillDir = path.join(canonicalBase, dirName);
        const dest = path.join(targetBase, dirName);

        try {
          fs.lstatSync(dest);
          fs.rmSync(dest, { recursive: true, force: true });
        } catch {
          // dest does not exist, nothing to remove
        }

        const relativePath = path.relative(targetBase, canonicalSkillDir);
        try {
          fs.symlinkSync(relativePath, dest);
        } catch (err: unknown) {
          if (
            process.platform === "win32" &&
            (err as NodeJS.ErrnoException).code === "EPERM"
          ) {
            throw new Error(
              "Symlinks require Developer Mode or Administrator rights on Windows.\n" +
                "Enable Developer Mode in Settings > System > For developers, or re-run as Administrator.\n" +
                "Alternatively, use 'Copy' install mode instead.",
            );
          }
          throw err;
        }
      }
    }
  } else {
    for (const agent of selectedAgents) {
      const targetBase = path.join(cwd, agent, "skills");
      fs.mkdirSync(targetBase, { recursive: true });

      for (const dirName of selectedDirNames) {
        const src = path.join(skillsSrcDir, dirName);
        const dest = path.join(targetBase, dirName);
        if (fs.existsSync(dest)) {
          fs.rmSync(dest, { recursive: true, force: true });
        }
        fs.cpSync(src, dest, { recursive: true });
      }
    }
  }
}

export function arrayEqualsUnordered(left: unknown, right: unknown): boolean {
  const a = Array.isArray(left)
    ? [...left].map((item) => String(item)).sort()
    : [];
  const b = Array.isArray(right)
    ? [...right].map((item) => String(item)).sort()
    : [];

  if (a.length !== b.length) {
    return false;
  }

  return a.every((value, index) => value === b[index]);
}

/**
 * Filters an object to only include fields defined in a Zod object schema.
 * Uses the schema's shape to determine allowed keys.
 *
 * @param data - The data to filter
 * @param schema - A Zod object schema with a shape property
 * @returns The filtered data with only schema-defined fields
 */
export function filterBySchema<T extends z.ZodObject<z.ZodRawShape>>(
  data: Record<string, unknown>,
  schema: T,
): z.infer<T> {
  const allowedKeys = Object.keys(schema.shape);
  const result: Record<string, unknown> = {};

  for (const key of allowedKeys) {
    if (key in data) {
      result[key] = data[key];
    }
  }

  return result as z.infer<T>;
}
