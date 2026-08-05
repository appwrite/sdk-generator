// @ts-expect-error BigInt toJSON polyfill for JSON.stringify support
BigInt.prototype.toJSON = function () {
  return this.toString();
};

import chalk from "chalk";
import { InvalidArgumentError } from "commander";
import Table from "cli-table3";
import stringWidth from "string-width";
import packageJson from "../package.json" with { type: "json" };
const { description } = packageJson;
import { globalConfig } from "./config.js";
import os from "os";
import { Client } from "@appwrite.io/console";
import { getErrorMessage, isCloud } from "./utils.js";
import {
  MAX_REPORT_BODY_LENGTH,
  sanitizeErrorText,
  summarizeErrorBody,
} from "./errors.js";
import { errorHintsFor } from "./hints.js";
import type { CliConfig } from "./types.js";
import {
  SDK_VERSION,
  SDK_TITLE,
  SDK_LOGO,
  EXECUTABLE_NAME,
} from "./constants.js";
import {
  formatSectionField,
  formatTimestamp,
  humanizeSeconds,
  renderStructuredCollection,
  sectionFieldKeys,
} from "./response-config.js";

const cliConfig: CliConfig = {
  verbose: false,
  json: false,
  raw: false,
  showSecrets: false,
  force: false,
  all: false,
  ids: [],
  report: false,
  reportData: {},
  displayFields: [],
  followUpHint: "",
};

type JsonObject = Record<string, unknown>;

const HIDDEN_VALUE = "[hidden]";
const SENSITIVE_KEYS = new Set([
  "secret",
  "apikey",
  "accesstoken",
  "refreshtoken",
  "password",
  "jwt",
  "clientsecret",
  "secretkey",
  "sessionsecret",
]);
let renderDepth = 0;
let redactionApplied = false;
let renderedRedactionApplied = false;

interface ReportDataPayload {
  data?: {
    args?: string[];
  };
}

const toJsonObject = (value: unknown): JsonObject | null => {
  if (value && typeof value === "object" && !Array.isArray(value)) {
    return value as JsonObject;
  }

  return null;
};

/**
 * Internal bookkeeping that carries no meaning for a CLI reader, plus fields
 * the API returns twice under two names (`billingPlanId` === `billingPlan`).
 */
const NORMAL_VIEW_HIDDEN_KEYS = new Set(["onboarding", "billingPlanId"]);

const isNormalViewHiddenKey = (key: string): boolean =>
  (key.startsWith("$") && key !== "$id") || NORMAL_VIEW_HIDDEN_KEYS.has(key);
const printSpacerLine = (): void => {
  process.stdout.write(" \n");
};

const extractReportCommandArgs = (value: unknown): string[] => {
  if (!value || typeof value !== "object") {
    return [];
  }

  const reportData = value as ReportDataPayload;
  if (!Array.isArray(reportData.data?.args)) {
    return [];
  }

  return reportData.data.args;
};

const beginRender = (): void => {
  if (renderDepth === 0) {
    redactionApplied = false;
    renderedRedactionApplied = false;
  }

  renderDepth++;
};

const endRender = (): void => {
  renderDepth = Math.max(0, renderDepth - 1);

  const shouldShowRedactionHint =
    cliConfig.json || cliConfig.raw
      ? redactionApplied
      : renderedRedactionApplied;

  if (renderDepth === 0 && shouldShowRedactionHint && !cliConfig.showSecrets) {
    const message =
      "Sensitive values were redacted. Pass --show-secrets to display them.";

    if (cliConfig.json || cliConfig.raw) {
      console.error(`${chalk.cyan.bold("♥ Hint:")} ${chalk.cyan(message)}`);
    } else {
      hint(message);
    }
  }

  // Machine-readable output stays free of prose. Cleared once shown so commands
  // that render more than once do not repeat themselves.
  if (renderDepth === 0 && cliConfig.followUpHint !== "") {
    const message = cliConfig.followUpHint;
    cliConfig.followUpHint = "";

    if (!cliConfig.json && !cliConfig.raw) {
      hint(message);
    }
  }
};

const withRender = <T>(callback: () => T): T => {
  beginRender();

  try {
    return callback();
  } finally {
    endRender();
  }
};

const isSensitiveKey = (key: string): boolean => {
  const normalizedKey = key.toLowerCase().replace(/[^a-z0-9]/g, "");

  return [...SENSITIVE_KEYS].some(
    (sensitiveKey) =>
      normalizedKey === sensitiveKey || normalizedKey.endsWith(sensitiveKey),
  );
};

const maskSensitiveString = (value: string, key: string): string => {
  // A key or token tail helps identify which credential is in play; a password
  // tail is just a leak, so those are masked whole.
  if (value.length <= 16 || /password/i.test(key)) {
    return HIDDEN_VALUE;
  }

  const separatorIndex = value.indexOf("_");
  if (separatorIndex > 0 && separatorIndex < value.length - 9) {
    const prefix = value.slice(0, separatorIndex + 1);
    const tail = value.slice(-4);
    return `${prefix}${HIDDEN_VALUE}${tail}`;
  }

  return `${HIDDEN_VALUE}${value.slice(-4)}`;
};

const maskSensitiveData = (
  value: unknown,
  key?: string,
  trackRedaction: boolean = true,
): unknown => {
  if (key && isSensitiveKey(key) && !cliConfig.showSecrets) {
    if (trackRedaction) {
      redactionApplied = true;
    }

    if (typeof value === "string") {
      return maskSensitiveString(value, key);
    }

    if (value == null) {
      return value;
    }

    return HIDDEN_VALUE;
  }

  if (Array.isArray(value)) {
    return value.map((item) =>
      maskSensitiveData(item, undefined, trackRedaction),
    );
  }

  if (value && typeof value === "object") {
    if (value?.constructor?.name === "BigNumber") {
      return String(value);
    }

    const result: JsonObject = {};
    for (const childKey of Object.keys(value as JsonObject)) {
      const childValue = (value as JsonObject)[childKey];
      if (typeof childValue === "function") continue;
      result[childKey] = maskSensitiveData(
        childValue,
        childKey,
        trackRedaction,
      );
    }

    return result;
  }

  return value;
};

const filterObject = (obj: JsonObject): JsonObject => {
  const result: JsonObject = {};
  for (const key of Object.keys(obj)) {
    const value = obj[key];
    if (typeof value === "function") continue;
    if (value == null) continue;
    if (value?.constructor?.name === "BigNumber") {
      result[key] = String(value);
      continue;
    }
    if (typeof value === "object") continue;
    if (typeof value === "string" && value.trim() === "") continue;
    result[key] = value;
  }
  return result;
};

const applyDisplayFilter = (rows: JsonObject[]): JsonObject[] => {
  if (cliConfig.displayFields.length === 0) {
    return rows;
  }

  return rows.map((row) => {
    const filtered: JsonObject = {};

    for (const key of cliConfig.displayFields) {
      if (Object.prototype.hasOwnProperty.call(row, key)) {
        filtered[key] = row[key];
      }
    }

    return Object.keys(filtered).length > 0 ? filtered : row;
  });
};

const filterData = (data: JsonObject): JsonObject => {
  const result: JsonObject = {};
  for (const key of Object.keys(data)) {
    const value = data[key];
    if (typeof value === "function") continue;
    if (value == null) continue;
    if (value?.constructor?.name === "BigNumber") {
      result[key] = String(value);
      continue;
    }
    if (Array.isArray(value)) {
      result[key] = value.map((item) => {
        if (item?.constructor?.name === "BigNumber") return String(item);
        return item && typeof item === "object" && !Array.isArray(item)
          ? filterObject(item as JsonObject)
          : item;
      });
    } else if (typeof value === "object") {
      continue;
    } else if (typeof value === "string" && value.trim() === "") {
      continue;
    } else {
      result[key] = value;
    }
  }
  return result;
};

export const parse = (data: unknown): void => {
  withRender(() => {
    if (data == null || (typeof data === "string" && data.trim() === "")) {
      if (!cliConfig.json && !cliConfig.raw) {
        success("Request completed successfully.");
      }
      return;
    }

    if (cliConfig.raw) {
      const sanitizedData = maskSensitiveData(data) as JsonObject;
      drawJSON(sanitizedData);
      return;
    }

    if (cliConfig.json) {
      const sanitizedData = maskSensitiveData(data) as JsonObject;
      drawJSON(filterData(sanitizedData));
      return;
    }

    const sanitizedData = maskSensitiveData(data, undefined, false);

    const objectData = toJsonObject(sanitizedData);
    if (!objectData) {
      console.log(String(sanitizedData));
      return;
    }

    const keys = Object.keys(objectData).filter(
      (k) => typeof objectData[k] !== "function",
    );
    const scalarEntries: Array<[string, string]> = [];
    const sections: Array<{ key: string; value: unknown }> = [];

    for (const key of keys) {
      if (isNormalViewHiddenKey(key)) continue;
      const value = objectData[key];
      if (value == null) continue;
      if (typeof value === "string" && value.trim() === "") continue;

      if (Array.isArray(value)) {
        if (value.length === 0) continue;
        if (
          value.every(
            (item) =>
              item &&
              typeof item === "object" &&
              !Array.isArray(item) &&
              Object.keys(item).length === 0,
          )
        )
          continue;
        sections.push({ key, value });
      } else if (typeof value === "object") {
        if (value?.constructor?.name === "BigNumber") {
          scalarEntries.push([key, String(value)]);
        } else {
          const tableRow = toJsonObject(value) ?? {};
          if (Object.keys(tableRow).length === 0) continue;
          sections.push({ key, value: tableRow });
        }
      } else {
        scalarEntries.push([key, formatKeyValue(key, value)]);
      }
    }

    let hasOutput = false;

    if (scalarEntries.length > 0) {
      drawKeyValueEntries(scalarEntries);
      hasOutput = true;
    }

    for (const section of sections) {
      if (hasOutput) {
        printSpacerLine();
      }

      if (Array.isArray(section.value)) {
        const sectionTitle =
          typeof section.value[0] === "object"
            ? `${section.key} (${section.value.length})`
            : section.key;
        console.log(`${chalk.yellow.bold.underline(sectionTitle)}`);

        if (typeof section.value[0] === "object") {
          drawTable(section.value, { indent: "  ", sectionName: section.key });
        } else {
          drawScalarArray(section.value, { indent: "  " });
        }
      } else {
        console.log(`${chalk.yellow.bold.underline(section.key)}`);
        drawTable([section.value as JsonObject], {
          indent: "  ",
          sectionName: section.key,
          keyValue: true,
        });
      }

      hasOutput = true;
    }
  });
};

const MAX_COL_WIDTH = 40;
const MAX_COLUMNS = 6;

/** Visible-width cap for table cells; scales with terminal size. */
const getMaxColWidth = (): number => {
  const columns = process.stdout.columns || 120;
  // Leave room for Key + Action + borders; give Reason most of the rest.
  return Math.max(MAX_COL_WIDTH, Math.min(120, Math.floor(columns * 0.5)));
};

/**
 * Truncate by visible width so chalk ANSI codes do not eat the budget and
 * cut off readable text while the terminal still has space.
 */
const truncateToVisibleWidth = (str: string, max: number): string => {
  if (stringWidth(str) <= max) {
    return str;
  }

  let result = "";
  for (const char of str) {
    const next = result + char;
    if (stringWidth(next) > max - 1) {
      break;
    }
    result = next;
  }
  return `${result}…`;
};

type NamedTableOptions = {
  indent?: string;
  sectionName?: string;
  /** Render as stacked key/value lines rather than a column table. */
  keyValue?: boolean;
};

type EntryRenderOptions = {
  indent?: string;
};

const formatCellValue = (value: unknown): string => {
  if (value == null) return "-";
  if (
    !cliConfig.showSecrets &&
    typeof value === "string" &&
    value.includes(HIDDEN_VALUE)
  ) {
    renderedRedactionApplied = true;
  }
  if (Array.isArray(value)) {
    if (value.length === 0) return "[]";
    if (
      value.every(
        (item) =>
          item == null ||
          typeof item === "string" ||
          typeof item === "number" ||
          typeof item === "boolean",
      )
    ) {
      const joinedValue = value.map((item) => String(item)).join(", ");
      if (stringWidth(joinedValue) <= getMaxColWidth()) {
        return joinedValue;
      }
    }
    return `[${value.length} items]`;
  }
  if (typeof value === "object") {
    if (value?.constructor?.name === "BigNumber") return String(value);
    const keys = Object.keys(value as Record<string, unknown>);
    if (keys.length === 0) return "{}";
    return `{${keys.length} keys}`;
  }
  return truncateToVisibleWidth(String(value), getMaxColWidth());
};

const formatKeyValue = (key: string, value: unknown): string => {
  if (key === "status" && typeof value === "boolean") {
    return value ? chalk.green("active") : chalk.dim("inactive");
  }

  if (typeof value === "boolean") {
    return value ? chalk.green("true") : chalk.dim("false");
  }

  // Durations come over the wire as raw seconds, which nobody reads at a glance.
  if (typeof value === "number" && /duration$/i.test(key)) {
    const humanized = humanizeSeconds(value);
    if (humanized !== "") {
      return `${value} ${chalk.dim(`(${humanized})`)}`;
    }
  }

  if (typeof value === "string") {
    const timestamp = formatTimestamp(value);
    if (timestamp !== null) {
      return timestamp;
    }
  }

  return String(value);
};

const toScalarEntries = (row: JsonObject): Array<[string, string]> => {
  const entries: Array<[string, string]> = [];

  for (const key of Object.keys(row)) {
    if (isNormalViewHiddenKey(key)) continue;
    const value = row[key];
    if (typeof value === "function") continue;
    if (value == null) continue;
    if (value?.constructor?.name === "BigNumber") {
      entries.push([key, String(value)]);
      continue;
    }
    if (typeof value === "object") continue;
    if (typeof value === "string" && value.trim() === "") continue;
    entries.push([key, formatKeyValue(key, value)]);
  }

  return entries;
};

const toDisplayEntries = (row: JsonObject): Array<[string, string]> => {
  const entries: Array<[string, string]> = [];

  for (const key of Object.keys(row)) {
    if (isNormalViewHiddenKey(key)) continue;
    const value = row[key];
    if (typeof value === "function") continue;
    if (value == null) continue;
    if (typeof value === "string" && value.trim() === "") continue;
    entries.push([key, formatCellValue(value)]);
  }

  return entries;
};

const drawScalarArray = (
  values: unknown[],
  options: EntryRenderOptions = {},
): void => {
  if (values.length === 0) {
    console.log(`${options.indent ?? ""}[]`);
    return;
  }

  const indent = options.indent ?? "";
  const displayValues = values.map((value) => formatCellValue(value));
  const joinedValues = displayValues.join(", ");

  if (joinedValues.length <= 80) {
    console.log(`${indent}${joinedValues}`);
    return;
  }

  displayValues.forEach((value, idx) => {
    console.log(`${indent}[${idx + 1}] ${value}`);
  });
};

const drawKeyValueEntries = (
  entries: Array<[string, string]>,
  options: EntryRenderOptions = {},
): void => {
  if (entries.length === 0) return;

  const indent = options.indent ?? "";
  const maxKeyLen = Math.max(...entries.map(([key]) => key.length));

  for (const [key, value] of entries) {
    const paddedKey = key.padEnd(maxKeyLen);
    if (!cliConfig.showSecrets && value.includes(HIDDEN_VALUE)) {
      renderedRedactionApplied = true;
    }
    console.log(`${indent}${chalk.yellow.bold(paddedKey)}  ${value}`);
  }
};

const printWithheldFieldNote = (count: number, indent?: string): void => {
  if (count <= 0) return;

  const label = count === 1 ? "field" : "fields";
  console.log(
    `${indent ?? ""}${chalk.dim(`… ${count} more ${label} — pass --raw to show all`)}`,
  );
};

const drawNamedObjectCollection = (
  rows: JsonObject[],
  options: NamedTableOptions = {},
): boolean => {
  const scalarEntries = rows.map((row) => toScalarEntries(row));
  const flatScalarEntries = scalarEntries.flat();

  if (flatScalarEntries.length > 0) {
    scalarEntries.forEach((entries, idx) => {
      if (idx > 0) {
        console.log();
      }

      if (rows.length > 1) {
        const indent = options.indent ?? "";
        console.log(`${indent}${chalk.cyan.bold(`[${idx + 1}]`)}`);
      }

      drawKeyValueEntries(entries, {
        indent: rows.length > 1 ? `${options.indent ?? ""}  ` : options.indent,
      });
    });

    return true;
  }

  const displayEntries = rows.map((row) => toDisplayEntries(row));
  const flatDisplayEntries = displayEntries.flat();

  if (flatDisplayEntries.length === 0) {
    return false;
  }

  displayEntries.forEach((entries, idx) => {
    if (idx > 0) {
      console.log();
    }

    if (rows.length > 1) {
      const indent = options.indent ?? "";
      console.log(`${indent}${chalk.cyan.bold(`[${idx + 1}]`)}`);
    }

    drawKeyValueEntries(entries, {
      indent: rows.length > 1 ? `${options.indent ?? ""}  ` : options.indent,
    });
  });

  return true;
};

export const drawTable = (
  data: Array<JsonObject | null | undefined>,
  options: NamedTableOptions = {},
): void => {
  withRender(() => {
    if (data.length == 0) {
      console.log("[]");
      return;
    }

    const visibleRows = applyDisplayFilter(
      data.map((item): JsonObject => {
        const maskedItem = maskSensitiveData(item, undefined, false);
        const row = toJsonObject(maskedItem) ?? {};
        const visibleRow: JsonObject = {};

        for (const key of Object.keys(row)) {
          if (isNormalViewHiddenKey(key)) continue;
          visibleRow[key] = row[key];
        }

        return visibleRow;
      }),
    );

    // An explicit --display selection wins; the reader already said what they want.
    const allowlist =
      cliConfig.displayFields.length > 0
        ? undefined
        : sectionFieldKeys(options.sectionName);
    let withheldFields = 0;

    const rows = allowlist
      ? visibleRows.map((row) => {
          const kept: JsonObject = {};

          for (const key of allowlist) {
            if (Object.prototype.hasOwnProperty.call(row, key)) {
              kept[key] = formatSectionField(
                options.sectionName,
                key,
                row[key],
              );
            }
          }

          withheldFields += Object.keys(row).length - Object.keys(kept).length;

          return kept;
        })
      : visibleRows;

    if (renderStructuredCollection(options.sectionName, rows, options)) {
      printWithheldFieldNote(withheldFields, options.indent);
      return;
    }

    if (options.keyValue && drawNamedObjectCollection(rows, options)) {
      printWithheldFieldNote(withheldFields, options.indent);
      return;
    }

    // Create an object with all the keys in it
    const obj = rows.reduce((res, item) => ({ ...res, ...item }), {});
    // Get those keys as an array
    const allKeys = Object.keys(obj);
    if (allKeys.length === 0) {
      drawJSON(data);
      return;
    }

    // If too many columns, show condensed key-value output with only scalar, non-empty fields
    if (allKeys.length > MAX_COLUMNS) {
      if (drawNamedObjectCollection(rows, options)) {
        printWithheldFieldNote(withheldFields, options.indent);
        return;
      }

      if (rows.every((row) => toScalarEntries(row).length === 0)) {
        drawJSON(data);
        return;
      }
    }

    const columns = allKeys;

    // Create an object with all keys set to the default value ''
    const def = allKeys.reduce((result: Record<string, string>, key) => {
      result[key] = "-";
      return result;
    }, {});
    // Use object destructuring to replace all default values with the ones we have
    const normalizedData = rows.map((item) => ({ ...def, ...item }));

    const table = new Table({
      head: columns.map((c) => chalk.cyan.italic.bold(c)),
      colWidths: columns.map(() => null) as (number | null)[],
      wordWrap: true,
      wrapOnWordBoundary: true,
      chars: {
        top: " ",
        "top-mid": " ",
        "top-left": " ",
        "top-right": " ",
        bottom: " ",
        "bottom-mid": " ",
        "bottom-left": " ",
        "bottom-right": " ",
        left: " ",
        "left-mid": " ",
        mid: chalk.cyan("─"),
        "mid-mid": chalk.cyan("┼"),
        right: " ",
        "right-mid": " ",
        middle: chalk.cyan("│"),
      },
    });

    normalizedData.forEach((row) => {
      const rowValues: string[] = [];
      for (const key of columns) {
        rowValues.push(formatCellValue(row[key]));
      }
      table.push(rowValues);
    });
    console.log(table.toString());
    printWithheldFieldNote(withheldFields, options.indent);
  });
};

export const drawJSON = (data: unknown): void => {
  console.log(JSON.stringify(data, null, 2));
};

const printErrorHints = (err: Error): void => {
  for (const message of errorHintsFor(err)) {
    hint(message);
  }
};

const ERROR_DETAIL_KEYS = ["code", "type", "response"] as const;
const ERROR_DETAIL_INDENT = "  ";
const ERROR_DETAIL_LABEL_WIDTH =
  Math.max(...ERROR_DETAIL_KEYS.map((key) => key.length)) + 2;

const formatErrorDetail = (value: unknown): string => {
  if (typeof value === "string") {
    const text = value;
    try {
      const parsed: unknown = JSON.parse(text);
      if (parsed && typeof parsed === "object") {
        value = parsed;
      }
    } catch {
      // Not JSON — summarize HTML and oversized proxy responses.
      return summarizeErrorBody(text);
    }
  }

  try {
    const json = JSON.stringify(value, null, 2) ?? String(value);
    return json
      .split("\n")
      .map((line, index) => (index === 0 ? line : ERROR_DETAIL_INDENT + line))
      .join("\n");
  } catch {
    return String(value);
  }
};

/** Keeps a summarized message from dominating a bug report title. */
const MAX_REPORT_TITLE_LENGTH = 120;

/**
 * Plain (uncolored, bounded) error details for a bug report body.
 */
const reportErrorDetails = (err: Error): string[] => {
  const lines: string[] = [];

  for (const key of ERROR_DETAIL_KEYS) {
    if (!Object.prototype.hasOwnProperty.call(err, key)) {
      continue;
    }

    const value = (err as unknown as Record<string, unknown>)[key];
    const rendered =
      typeof value === "string"
        ? sanitizeErrorText(value, MAX_REPORT_BODY_LENGTH)
        : String(value);

    lines.push(`${key}: ${rendered}`);
  }

  return lines;
};

/**
 * Stack frames only — the message line is rendered separately and, unlike a raw
 * stack, frames can never carry a response body.
 */
const errorStackFrames = (err: Error): string[] =>
  (err.stack ?? "")
    .split("\n")
    .filter((line) => line.trim().startsWith("at "))
    .map((line) => line.trim());

export const formatErrorForLog = (err: Error): string => {
  const lines = [
    `${chalk.red.bold(err.name || "Error")}${chalk.red(`: ${getErrorMessage(err)}`)}`,
  ];

  for (const key of ERROR_DETAIL_KEYS) {
    if (!Object.prototype.hasOwnProperty.call(err, key)) {
      continue;
    }

    const value = (err as unknown as Record<string, unknown>)[key];
    lines.push(
      `${ERROR_DETAIL_INDENT}${chalk.cyan(`${key}:`.padEnd(ERROR_DETAIL_LABEL_WIDTH))}${formatErrorDetail(value)}`,
    );
  }

  const frames = errorStackFrames(err);
  if (frames.length > 0) {
    lines.push(
      "",
      chalk.dim(`${ERROR_DETAIL_INDENT}Stack trace:`),
      ...frames.map((frame) =>
        chalk.dim(`${ERROR_DETAIL_INDENT.repeat(2)}${frame}`),
      ),
    );
  }

  return lines.join("\n");
};

export const parseError = (err: Error): void => {
  if (cliConfig.report) {
    void (async () => {
      let appwriteVersion = "unknown";
      const endpoint = globalConfig.getEndpoint();

      try {
        const client = new Client().setEndpoint(endpoint);
        const res = (await client.call(
          "get",
          new URL("/health/version", endpoint),
        )) as { version: string };
        appwriteVersion = res.version;
      } catch {
        // Silently fail
      }

      const version = SDK_VERSION;
      const commandArgs = extractReportCommandArgs(cliConfig.reportData);
      const stepsToReproduce = `Running \`${EXECUTABLE_NAME} ${commandArgs.join(" ")}\``;
      const yourEnvironment = `CLI version: ${version}\nOperation System: ${os.type()}\nAppwrite version: ${appwriteVersion}\nIs Cloud: ${isCloud()}`;

      // Response bodies are summarized and frames are listed separately, so an
      // HTML error page can never blow the issue URL past what GitHub accepts.
      const details = [
        `${err.name || "Error"}: ${getErrorMessage(err)}`,
        ...reportErrorDetails(err),
        ...errorStackFrames(err),
      ].join("\n");
      const stack = "```\n" + details + "\n```";

      const githubIssueUrl = new URL(
        "https://github.com/appwrite/appwrite/issues/new",
      );
      githubIssueUrl.searchParams.append("labels", "bug");
      githubIssueUrl.searchParams.append("template", "bug.yaml");
      githubIssueUrl.searchParams.append(
        "title",
        `🐛 Bug Report: ${sanitizeErrorText(getErrorMessage(err), MAX_REPORT_TITLE_LENGTH)}`,
      );
      githubIssueUrl.searchParams.append(
        "actual-behavior",
        `CLI Error:\n${stack}`,
      );
      githubIssueUrl.searchParams.append(
        "steps-to-reproduce",
        stepsToReproduce,
      );
      githubIssueUrl.searchParams.append("environment", yourEnvironment);

      log(
        `To report this error you can:\n - Create a support ticket in our Discord server https://appwrite.io/discord \n - Create an issue in our Github\n   ${githubIssueUrl.href}\n`,
      );
      printErrorHints(err);

      error("\n Stack Trace: \n");
      console.error(formatErrorForLog(err));
      process.exit(1);
    })();
  } else {
    if (cliConfig.verbose) {
      console.error(formatErrorForLog(err));
      printErrorHints(err);
    } else {
      log("For detailed error pass the --verbose or --report flag");
      error(getErrorMessage(err));
      printErrorHints(err);
    }
    process.exit(1);
  }
};

export const actionRunner = <
  T extends (...args: unknown[]) => Promise<unknown>,
>(
  fn: T,
): ((...args: Parameters<T>) => Promise<void>) => {
  return (...args: Parameters<T>) => {
    if (
      cliConfig.all &&
      Array.isArray(cliConfig.ids) &&
      cliConfig.ids.length !== 0
    ) {
      error(`The '--all' and '--id' flags cannot be used together.`);
      process.exit(1);
    }
    return fn(...args)
      .then(() => undefined)
      .catch(parseError);
  };
};

export const parseInteger = (value: string): number => {
  const parsedValue = parseInt(value, 10);
  if (isNaN(parsedValue)) {
    throw new InvalidArgumentError("Not a number.");
  }
  return parsedValue;
};

export const parseBool = (value: string): boolean => {
  if (value === "true") return true;
  if (value === "false") return false;
  throw new InvalidArgumentError("Not a boolean.");
};

export const parseJsonObject = (
  value: string | undefined,
  optionName: string,
): Record<string, unknown> | undefined => {
  if (value === undefined) {
    return undefined;
  }

  try {
    const parsed = JSON.parse(value) as unknown;

    if (parsed && typeof parsed === "object" && !Array.isArray(parsed)) {
      return parsed as Record<string, unknown>;
    }
  } catch {
    throw new InvalidArgumentError(
      `${optionName} must be a valid JSON object.`,
    );
  }

  throw new InvalidArgumentError(`${optionName} must be a valid JSON object.`);
};

export const log = (message?: string): void => {
  console.log(`${chalk.cyan.bold("ℹ Info:")} ${chalk.cyan(message ?? "")}`);
};

export const warn = (message?: string): void => {
  console.log(
    `${chalk.yellow.bold("ℹ Warning:")} ${chalk.yellow(message ?? "")}`,
  );
};

export const hint = (message?: string): void => {
  console.log(`${chalk.cyan.bold("♥ Hint:")} ${chalk.cyan(message ?? "")}`);
};

export const success = (message?: string): void => {
  console.log(
    `${chalk.green.bold("✓ Success:")} ${chalk.green(message ?? "")}`,
  );
};

export const error = (message?: string): void => {
  console.error(`${chalk.red.bold("✗ Error:")} ${chalk.red(message ?? "")}`);
};

export const logo = SDK_LOGO;

export const commandDescriptions: Record<string, string> = {
  account: `The account command allows you to authenticate and manage a user account.`,
  activities: `The activities command allows you to list and inspect project activity events.`,
  graphql: `The graphql command allows you to query and mutate any resource type on your Appwrite server.`,
  avatars: `The avatars command aims to help you complete everyday tasks related to your app image, icons, and avatars.`,
  backups: `The backups command allows you to manage backup policies, archives, and restorations for your project.`,
  databases: `(Legacy) The databases command allows you to create structured collections of documents and query and filter lists of documents.`,
  tablesDB: `The tablesdb command allows you to create structured tables of columns and query and filter lists of rows.`,
  init: `The init command provides a convenient wrapper for creating and initializing projects, functions, collections, buckets, teams, messaging-topics, and skills in ${SDK_TITLE}.`,
  push: `The push command provides a convenient wrapper for pushing your functions, collections, buckets, teams, and messaging-topics.`,
  run: `The run command allows you to run the project locally to allow easy development and quick debugging.`,
  functions: `The functions command allows you to view, create, and manage your Cloud Functions.`,
  generate: `The generate command allows you to generate a type-safe SDK from your ${SDK_TITLE} project configuration.`,
  health: `The health command allows you to both validate and monitor your ${SDK_TITLE} server's health.`,
  pull: `The pull command helps you pull your ${SDK_TITLE} project, functions, collections, buckets, teams, and messaging-topics`,
  locale: `The locale command allows you to customize your app based on your users' location.`,
  sites: `The sites command allows you to view, create and manage your Appwrite Sites.`,
  storage: `The storage command allows you to manage your project files.`,
  teams: `The teams command allows you to group users of your project to enable them to share read and write access to your project resources. Requires a linked project. To manage organization members, use the 'organization' command instead.`,
  update: `The update command allows you to update the ${SDK_TITLE} CLI to the latest version.`,
  users: `The users command allows you to manage your project users.`,
  projects: `The projects command allows you to manage your projects, add platforms, manage API keys, Dev Keys etc.`,
  project: `The project command allows you to manage project related resources like usage, variables, etc.`,
  proxy: `The proxy command allows you to configure actions for your domains beyond DNS configuration.`,
  client: `The client command allows you to configure your CLI`,
  login: `The login command allows you to authenticate and manage a user account.`,
  logout: `The logout command allows you to log out of your ${SDK_TITLE} account.`,
  whoami: `The whoami command gives information about the currently logged-in user.`,
  register: `Outputs the link to create an ${SDK_TITLE} account.`,
  console: `The console command gives you access to the APIs used by the Appwrite Console.`,
  messaging: `The messaging command allows you to manage topics and targets and send messages.`,
  migrations: `The migrations command allows you to migrate data between services.`,
  notifications: `The notifications command allows you to read and manage your Appwrite Console notifications.`,
  oauth2: `The oauth2 command allows you to authorize apps and issue standards-based OAuth2 and OpenID Connect tokens.`,
  organization: `The organization command allows you to manage organization-level projects.`,
  organizations: `The organizations command allows you to manage organization billing, plans, invoices, and add-ons.`,
  presences: `The presences command allows you to track and manage real-time user presence in your project.`,
  tokens: `The tokens command allows you to create and manage resource tokens for secure file access.`,
  vcs: `The vcs command allows you to interact with VCS providers and manage your code repositories.`,
  webhooks: `The webhooks command allows you to manage your project webhooks.`,
  // The logo is rendered by the help formatter, so this stays plain prose and
  // can be reused wherever the CLI needs a one-paragraph description.
  main: description,
};

export { cliConfig };
