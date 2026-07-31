import chalk from "chalk";
import stringWidth from "string-width";
import { z } from "zod";

type JsonObject = Record<string, unknown>;

export type StructuredCollectionRenderOptions = {
  indent?: string;
};

type RowContext = {
  index: number;
};

type ColumnDefinition = {
  header: string;
  value: (row: JsonObject, context: RowContext) => string;
};

type StructuredCollectionRenderer = {
  itemSchema: z.ZodType<JsonObject>;
  columns: ColumnDefinition[];
};

const splitWords = (value: string): string[] =>
  value
    .replace(/([a-z0-9])([A-Z])/g, "$1 $2")
    .split(/[\s_-]+/)
    .filter(Boolean);

const toTitleCase = (value: string): string =>
  splitWords(value)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1).toLowerCase())
    .join(" ");

const COLUMN_GAP = "  ";

const padColumn = (value: string, width: number): string => {
  const valueWidth = stringWidth(value);
  if (valueWidth >= width) return value;
  return value + " ".repeat(width - valueWidth);
};

const renderAlignedColumns = (
  columns: string[][],
  options: StructuredCollectionRenderOptions = {},
  headers?: string[],
): void => {
  const indent = options.indent ?? "";
  const widths = columns.map((column, columnIndex) => {
    const dataWidth = column.reduce(
      (max, value) => Math.max(max, stringWidth(value)),
      0,
    );
    const headerWidth = stringWidth(headers?.[columnIndex] ?? "");

    return Math.max(dataWidth, headerWidth);
  });

  if (headers && headers.length === columns.length) {
    const headerParts = headers.map((header, columnIndex) => {
      const value = chalk.cyan.bold(header);
      if (columnIndex === columns.length - 1) {
        return value;
      }

      return padColumn(value, widths[columnIndex]);
    });

    console.log(`${indent}${headerParts.join(COLUMN_GAP)}`.trimEnd());
  }

  for (let idx = 0; idx < columns[0].length; idx++) {
    const parts = columns.map((column, columnIndex) => {
      const value = column[idx] ?? "";
      if (columnIndex === columns.length - 1) {
        return value;
      }

      return padColumn(value, widths[columnIndex]);
    });

    console.log(`${indent}${parts.join(COLUMN_GAP)}`.trimEnd());
  }
};

const wrapValues = (values: string[], width: number): string[] => {
  const lines: string[] = [];
  let current = "";

  values.forEach((value, index) => {
    const piece = index === values.length - 1 ? value : `${value},`;

    if (current === "") {
      current = piece;
      return;
    }

    if (stringWidth(`${current} ${piece}`) > width) {
      lines.push(current);
      current = piece;
      return;
    }

    current = `${current} ${piece}`;
  });

  if (current !== "") {
    lines.push(current);
  }

  return lines;
};

/** Plan quotas are quoted in decimal units, matching how the console reads them. */
const SIZE_UNITS = ["MB", "GB", "TB", "PB"] as const;
type SizeUnit = (typeof SIZE_UNITS)[number];

const trimTrailingZeros = (value: number): string =>
  String(Number(value.toFixed(2)));

const formatSize = (amount: number, unit: SizeUnit): string => {
  const base = `${amount} ${unit}`;
  let index = SIZE_UNITS.indexOf(unit);
  let scaled = amount;

  while (scaled >= 1000 && index < SIZE_UNITS.length - 1) {
    scaled /= 1000;
    index++;
  }

  if (SIZE_UNITS[index] === unit) {
    return base;
  }

  return `${base} ${chalk.dim(`(${trimTrailingZeros(scaled)} ${SIZE_UNITS[index]})`)}`;
};

const compactNumber = new Intl.NumberFormat("en", {
  notation: "compact",
  maximumFractionDigits: 2,
});

const formatCount = (amount: number): string => {
  if (Math.abs(amount) < 10000) {
    return String(amount);
  }

  return `${amount} ${chalk.dim(`(${compactNumber.format(amount)})`)}`;
};

export const humanizeSeconds = (seconds: number): string => {
  if (!Number.isFinite(seconds) || seconds <= 0) {
    return "";
  }

  const units: Array<[string, number]> = [
    ["d", 86400],
    ["h", 3600],
    ["m", 60],
    ["s", 1],
  ];

  const parts: string[] = [];
  let remaining = Math.round(seconds);

  for (const [suffix, size] of units) {
    const amount = Math.floor(remaining / size);
    if (amount > 0) {
      parts.push(`${amount}${suffix}`);
      remaining -= amount * size;
    }
  }

  return parts.slice(0, 2).join(" ");
};

/**
 * The offset is mandatory: ECMAScript reads an offset-less date-time as local
 * time, so accepting one would mean labelling a local instant UTC. Values
 * without an offset fall through and render as they arrived.
 */
const ISO_DATE_TIME =
  /^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2}:\d{2})(?:\.\d+)?(Z|[+-]\d{2}:\d{2})$/;

/** Coarsest-unit-wins tiers; approximate on purpose, this is a readability aid. */
const RELATIVE_TIERS: Array<[string, number]> = [
  ["y", 31536000],
  ["mo", 2592000],
  ["d", 86400],
  ["h", 3600],
  ["m", 60],
];

const relativeTime = (date: Date): string => {
  const deltaSeconds = (Date.now() - date.getTime()) / 1000;
  const magnitude = Math.abs(deltaSeconds);

  if (magnitude < 45) {
    return "just now";
  }

  const [suffix, size] =
    RELATIVE_TIERS.find(([, tierSize]) => magnitude >= tierSize) ??
    RELATIVE_TIERS[RELATIVE_TIERS.length - 1];
  const label = `${Math.floor(magnitude / size)}${suffix}`;

  return deltaSeconds > 0 ? `${label} ago` : `in ${label}`;
};

/**
 * Turns `2026-07-31T02:49:41.895+00:00` into `2026-07-31 02:49:41 UTC (2h ago)`.
 * Returns null when the value is not an ISO timestamp, so callers can fall back.
 */
export const formatTimestamp = (value: string): string | null => {
  const match = ISO_DATE_TIME.exec(value.trim());
  if (!match) {
    return null;
  }

  const [, date, time, offset] = match;
  const zone = offset === "Z" || offset === "+00:00" ? " UTC" : ` ${offset}`;
  const stamp = `${date} ${time}${zone}`;
  const parsed = new Date(value);

  if (Number.isNaN(parsed.getTime())) {
    return stamp;
  }

  return `${stamp} ${chalk.dim(`(${relativeTime(parsed)})`)}`;
};

const compactDate = (value: unknown): string => {
  if (typeof value !== "string" || value.trim() === "") {
    return "—";
  }

  return value.replace("T", " ").replace("+00:00", "Z");
};

const compactText = (value: unknown, fallback: string = "—"): string => {
  if (typeof value !== "string") {
    return fallback;
  }

  const trimmed = value.trim();
  return trimmed === "" ? fallback : trimmed;
};

const compactAmount = (value: unknown): string => {
  if (typeof value === "number" || typeof value === "string") {
    return String(value);
  }

  return "—";
};

const compactBytes = (value: unknown): string => {
  const bytes =
    typeof value === "number"
      ? value
      : typeof value === "string" && value.trim() !== ""
        ? Number(value)
        : Number.NaN;

  if (!Number.isFinite(bytes) || bytes < 0) {
    return "—";
  }

  if (bytes < 1024) {
    return `${Math.round(bytes)} B`;
  }

  const units = ["KB", "MB", "GB", "TB"];
  let amount = bytes / 1024;
  let unitIndex = 0;

  while (amount >= 1024 && unitIndex < units.length - 1) {
    amount /= 1024;
    unitIndex++;
  }

  return `${amount.toFixed(1).replace(/\.0$/, "")} ${units[unitIndex]}`;
};

const compactDuration = (value: unknown): string => {
  const duration =
    typeof value === "number"
      ? value
      : typeof value === "string" && value.trim() !== ""
        ? Number(value)
        : Number.NaN;

  if (!Number.isFinite(duration) || duration < 0) {
    return "—";
  }

  const totalSeconds = Math.round(duration);
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  if (hours > 0) {
    return `${hours}h ${minutes}m`;
  }

  if (minutes > 0) {
    return `${minutes}m ${seconds}s`;
  }

  return `${seconds}s`;
};

const isPresent = (value: unknown): boolean => {
  if (value == null) return false;
  if (typeof value === "string") return value.trim() !== "";
  return true;
};

const valueFrom = <T = unknown>(row: JsonObject, key: string): T | undefined =>
  row[key] as T | undefined;

const indexedLabel = (label: string, context: RowContext): string =>
  `[${context.index + 1}] ${label}`;

const createSummarySchema = (
  shape: z.ZodRawShape,
  requiredKeys: string[],
  message: string,
): z.ZodType<JsonObject> =>
  z
    .object(shape)
    .passthrough()
    .refine((row) => requiredKeys.some((key) => isPresent(row[key])), {
      message,
    });

const createColumnRenderer = (
  itemSchema: z.ZodType<JsonObject>,
  columns: ColumnDefinition[],
): StructuredCollectionRenderer => ({
  itemSchema,
  columns,
});

const PaymentMethodSummarySchema = createSummarySchema(
  {
    providerUserId: z.string().nullable().optional(),
    providerMethodId: z.string().nullable().optional(),
    mandateId: z.string().nullable().optional(),
    mandateStatus: z.string().nullable().optional(),
    last4: z.string().nullable().optional(),
    brand: z.string().nullable().optional(),
    country: z.string().nullable().optional(),
    expiryMonth: z.union([z.string(), z.number()]).nullable().optional(),
    expiryYear: z.union([z.string(), z.number()]).nullable().optional(),
    default: z.boolean().optional(),
    expired: z.boolean().optional(),
    failed: z.boolean().optional(),
  },
  ["providerUserId", "providerMethodId", "mandateId", "last4"],
  "Expected a payment method summary row",
);

const IdentitySummarySchema = createSummarySchema(
  {
    provider: z.string().nullable().optional(),
    providerUid: z.union([z.string(), z.number()]).nullable().optional(),
    providerEmail: z.string().nullable().optional(),
    providerAccessTokenExpiry: z.string().nullable().optional(),
  },
  ["provider", "providerUid", "providerEmail"],
  "Expected an identity summary row",
);

const SessionSummarySchema = createSummarySchema(
  {
    provider: z.string().nullable().optional(),
    current: z.boolean().optional(),
    expire: z.string().nullable().optional(),
    clientName: z.string().nullable().optional(),
    clientType: z.string().nullable().optional(),
    osName: z.string().nullable().optional(),
    deviceName: z.string().nullable().optional(),
    countryName: z.string().nullable().optional(),
    countryCode: z.string().nullable().optional(),
  },
  ["provider", "expire"],
  "Expected a session summary row",
);

const LogSummarySchema = createSummarySchema(
  {
    event: z.string().nullable().optional(),
    time: z.string().nullable().optional(),
    clientName: z.string().nullable().optional(),
    osName: z.string().nullable().optional(),
    countryName: z.string().nullable().optional(),
    countryCode: z.string().nullable().optional(),
    mode: z.string().nullable().optional(),
  },
  ["event", "time"],
  "Expected a log summary row",
);

const InvoiceSummarySchema = createSummarySchema(
  {
    plan: z.string().nullable().optional(),
    currency: z.string().nullable().optional(),
    amount: z.union([z.string(), z.number()]).nullable().optional(),
    grossAmount: z.union([z.string(), z.number()]).nullable().optional(),
    status: z.string().nullable().optional(),
    dueAt: z.string().nullable().optional(),
    from: z.string().nullable().optional(),
    to: z.string().nullable().optional(),
  },
  ["plan", "status", "dueAt"],
  "Expected an invoice summary row",
);

const RuntimeSummarySchema = createSummarySchema(
  {
    $id: z.string().nullable().optional(),
    key: z.string().nullable().optional(),
    name: z.string().nullable().optional(),
    version: z.union([z.string(), z.number()]).nullable().optional(),
    base: z.string().nullable().optional(),
    image: z.string().nullable().optional(),
    logo: z.string().nullable().optional(),
  },
  ["$id", "name", "version", "base", "image"],
  "Expected a runtime summary row",
);

const DeploymentSummarySchema = z
  .object({
    $id: z.string(),
    status: z.string(),
    type: z.string().nullable().optional(),
    activate: z.boolean().optional(),
    totalSize: z.union([z.string(), z.number()]).nullable().optional(),
    buildDuration: z.union([z.string(), z.number()]).nullable().optional(),
  })
  .passthrough();

const paymentMethodLabel = (row: JsonObject): string => {
  const brand = valueFrom<string>(row, "brand") ?? "";
  const last4 = valueFrom<string>(row, "last4") ?? "";

  if (brand && last4) {
    return `${toTitleCase(brand)} •••• ${last4}`;
  }

  if (last4) {
    return `Card •••• ${last4}`;
  }

  return "Setup only";
};

const paymentMethodDetails = (row: JsonObject): string => {
  const expiryMonth = valueFrom<string | number>(row, "expiryMonth");
  const expiryYear = valueFrom<string | number>(row, "expiryYear");

  if (expiryMonth != null && expiryYear != null) {
    return `exp ${String(expiryMonth).padStart(2, "0")}/${String(expiryYear)}`;
  }

  return "no card data";
};

const paymentMethodStatus = (row: JsonObject): string => {
  const mandateStatus = valueFrom<string>(row, "mandateStatus");
  if (mandateStatus && mandateStatus.trim() !== "") {
    return `mandate: ${mandateStatus}`;
  }

  if (row.failed === true) {
    return "status: failed";
  }

  if (row.expired === true) {
    return "status: expired";
  }

  if (
    isPresent(valueFrom(row, "providerMethodId")) ||
    isPresent(valueFrom(row, "last4"))
  ) {
    return "status: active";
  }

  return "status: pending";
};

const runtimeLabel = (row: JsonObject): string => {
  const name = compactText(valueFrom(row, "name"), "");
  const key = compactText(valueFrom(row, "key"), "");
  const version = valueFrom<string | number>(row, "version");
  const runtimeName = name || (key ? toTitleCase(key) : "Runtime");

  if (version == null || String(version).trim() === "") {
    return runtimeName;
  }

  return `${runtimeName} ${version}`;
};

const deploymentStatus = (row: JsonObject): string => {
  const status = compactText(valueFrom(row, "status"), "unknown");

  switch (status.toLowerCase()) {
    case "ready":
      return chalk.green(status);
    case "failed":
      return chalk.red(status);
    case "waiting":
    case "processing":
    case "building":
      return chalk.yellow(status);
    case "canceled":
      return chalk.dim(status);
    default:
      return status;
  }
};

const structuredCollectionRenderers: Record<
  string,
  StructuredCollectionRenderer
> = {
  deployments: createColumnRenderer(DeploymentSummarySchema, [
    {
      header: "deployment",
      value: (row, context) =>
        indexedLabel(compactText(valueFrom(row, "$id")), context),
    },
    {
      header: "status",
      value: (row) => deploymentStatus(row),
    },
    {
      header: "type",
      value: (row) => compactText(valueFrom(row, "type")),
    },
    {
      header: "auto-activate",
      value: (row) => (row.activate === true ? "yes" : "no"),
    },
    {
      header: "size",
      value: (row) => compactBytes(valueFrom(row, "totalSize")),
    },
    {
      header: "build",
      value: (row) => compactDuration(valueFrom(row, "buildDuration")),
    },
  ]),
  runtimes: createColumnRenderer(RuntimeSummarySchema, [
    {
      header: "runtime",
      value: (row, context) => indexedLabel(runtimeLabel(row), context),
    },
    {
      header: "id",
      value: (row) => compactText(valueFrom(row, "$id")),
    },
    {
      header: "base",
      value: (row) => compactText(valueFrom(row, "base")),
    },
    {
      header: "image",
      value: (row) => compactText(valueFrom(row, "image")),
    },
  ]),
  identities: createColumnRenderer(IdentitySummarySchema, [
    {
      header: "identity",
      value: (row, context) =>
        indexedLabel(
          toTitleCase(compactText(valueFrom(row, "provider"), "Identity")),
          context,
        ),
    },
    {
      header: "account",
      value: (row) => {
        const providerEmail = valueFrom<string>(row, "providerEmail");
        const providerUid = valueFrom<string | number>(row, "providerUid");

        if (providerEmail && providerEmail.trim() !== "") {
          return providerEmail;
        }

        if (providerUid != null) {
          return `uid ${providerUid}`;
        }

        return "—";
      },
    },
    {
      header: "identifier",
      value: (row) => {
        const providerEmail = valueFrom<string>(row, "providerEmail");
        const providerUid = valueFrom<string | number>(row, "providerUid");

        if (
          providerEmail &&
          providerEmail.trim() !== "" &&
          providerUid != null
        ) {
          return `uid ${providerUid}`;
        }

        return "";
      },
    },
    {
      header: "expires",
      value: (row) => {
        const expiry = valueFrom<string>(row, "providerAccessTokenExpiry");
        return expiry ? `expires ${compactDate(expiry)}` : "";
      },
    },
  ]),
  sessions: createColumnRenderer(SessionSummarySchema, [
    {
      header: "session",
      value: (row, context) => {
        const provider = compactText(valueFrom(row, "provider"), "Session");
        const current = row.current === true ? " (current)" : "";
        return indexedLabel(`${toTitleCase(provider)}${current}`, context);
      },
    },
    {
      header: "client",
      value: (row) => {
        const clientName = compactText(valueFrom(row, "clientName"), "");
        const osName = compactText(valueFrom(row, "osName"), "");
        const deviceName = compactText(valueFrom(row, "deviceName"), "");

        if (clientName && osName) {
          return `${clientName} on ${osName}`;
        }

        if (clientName && deviceName) {
          return `${clientName} on ${deviceName}`;
        }

        return clientName || osName || deviceName || "—";
      },
    },
    {
      header: "location",
      value: (row) => compactText(row.countryName ?? row.countryCode, "—"),
    },
    {
      header: "expires",
      value: (row) => `expires ${compactDate(valueFrom(row, "expire"))}`,
    },
  ]),
  logs: createColumnRenderer(LogSummarySchema, [
    {
      header: "time",
      value: (row) => compactDate(valueFrom(row, "time")),
    },
    {
      header: "event",
      value: (row) => {
        const event = compactText(valueFrom(row, "event"), "event");
        const mode = valueFrom<string>(row, "mode");

        if (mode && mode.trim() !== "" && mode !== "default") {
          return `${event} (${mode})`;
        }

        return event;
      },
    },
    {
      header: "client",
      value: (row) => {
        const clientName = compactText(valueFrom(row, "clientName"), "");
        const osName = compactText(valueFrom(row, "osName"), "");

        if (clientName && osName) {
          return `${clientName} on ${osName}`;
        }

        return clientName || osName || "—";
      },
    },
    {
      header: "location",
      value: (row) => compactText(row.countryName ?? row.countryCode, "—"),
    },
  ]),
  invoices: createColumnRenderer(InvoiceSummarySchema, [
    {
      header: "status",
      value: (row, context) =>
        indexedLabel(compactText(valueFrom(row, "status"), "pending"), context),
    },
    {
      header: "plan",
      value: (row) => compactText(valueFrom(row, "plan")),
    },
    {
      header: "amount",
      value: (row) => {
        const currency = compactText(valueFrom(row, "currency"), "—");
        const grossAmount = valueFrom<string | number>(row, "grossAmount");
        const amount = valueFrom<string | number>(row, "amount");

        return `${currency} ${grossAmount != null ? compactAmount(grossAmount) : compactAmount(amount)}`.trim();
      },
    },
    {
      header: "due",
      value: (row) => `due ${compactDate(valueFrom(row, "dueAt"))}`,
    },
    {
      header: "period",
      value: (row) =>
        `${compactDate(valueFrom(row, "from"))} -> ${compactDate(valueFrom(row, "to"))}`,
    },
  ]),
  paymentMethods: createColumnRenderer(PaymentMethodSummarySchema, [
    {
      header: "method",
      value: (row, context) => indexedLabel(paymentMethodLabel(row), context),
    },
    {
      header: "country",
      value: (row) => compactText(valueFrom(row, "country"), "—"),
    },
    {
      header: "details",
      value: (row) => paymentMethodDetails(row),
    },
    {
      header: "default",
      value: (row) => `default: ${Boolean(row.default) ? "yes" : "no"}`,
    },
    {
      header: "status",
      value: (row) => paymentMethodStatus(row),
    },
  ]),
};

/**
 * How a field's bare number should be annotated. Units belong to the section,
 * not to the key: a plan's `fileSize` is megabytes, a bucket's is bytes.
 */
type FieldFormat =
  /** Decimal size in the given unit, scaled up when that reads better. */
  | { kind: "size"; unit: SizeUnit }
  /** Large tallies get a compact magnitude hint: 3500000 (3.5M). */
  | { kind: "count" }
  /** A literal trailing label, e.g. `168 days`. */
  | { kind: "label"; label: string };

type SectionField = {
  key: string;
  format?: FieldFormat;
};

/**
 * Embedded models that arrive with far more fields than a reader asked for.
 * These are allowlists rather than denylists on purpose: the API keeps adding
 * capability flags, and an allowlist stays correct without maintenance. Order
 * here is the render order; anything absent is summarised as a footer count.
 *
 * Units are verified against how the console interprets the same plan fields —
 * `bandwidth`/`storage` in GB, `fileSize` in MB.
 */
const sectionFields: Record<string, SectionField[]> = {
  billingPlanDetails: [
    { key: "$id" },
    { key: "name" },
    { key: "group" },
    { key: "price" },
    { key: "currency" },
    { key: "trial", format: { kind: "label", label: "days" } },
    { key: "bandwidth", format: { kind: "size", unit: "GB" } },
    { key: "storage", format: { kind: "size", unit: "GB" } },
    { key: "fileSize", format: { kind: "size", unit: "MB" } },
    { key: "users", format: { kind: "count" } },
    { key: "executions", format: { kind: "count" } },
    { key: "GBHours", format: { kind: "label", label: "GB-hours" } },
    { key: "databasesReads", format: { kind: "count" } },
    { key: "databasesWrites", format: { kind: "count" } },
    { key: "realtime", format: { kind: "count" } },
    { key: "realtimeMessages", format: { kind: "count" } },
    { key: "messages", format: { kind: "count" } },
    { key: "domains", format: { kind: "count" } },
  ],
};

export const sectionFieldKeys = (
  sectionName: string | undefined,
): string[] | undefined =>
  sectionName
    ? sectionFields[sectionName]?.map((field) => field.key)
    : undefined;

export const formatSectionField = (
  sectionName: string | undefined,
  key: string,
  value: unknown,
): unknown => {
  if (typeof value !== "number" || !Number.isFinite(value)) {
    return value;
  }

  const format = sectionName
    ? sectionFields[sectionName]?.find((field) => field.key === key)?.format
    : undefined;

  if (!format) {
    return value;
  }

  switch (format.kind) {
    case "size":
      return formatSize(value, format.unit);
    case "count":
      return formatCount(value);
    case "label":
      return `${value} ${format.label}`;
  }
};

/**
 * Rows that carry nothing but a name and an on/off switch — `authMethods`,
 * `services`, `protocols` and friends. A two column table wastes a screen on
 * them, so they collapse into wrapped enabled/disabled lists instead.
 */
const ToggleRowSchema = z
  .strictObject({
    $id: z.string().optional(),
    name: z.string().optional(),
    key: z.string().optional(),
    enabled: z.boolean(),
  })
  .refine(
    (row) => isPresent(row.$id) || isPresent(row.key) || isPresent(row.name),
    { message: "Expected a toggle row with a name" },
  );

const toggleLabel = (row: JsonObject): string =>
  compactText(
    valueFrom(row, "$id") ?? valueFrom(row, "key") ?? valueFrom(row, "name"),
  );

const renderToggleCollection = (
  rows: JsonObject[],
  options: StructuredCollectionRenderOptions = {},
): boolean => {
  if (rows.length === 0) {
    return false;
  }

  if (!rows.every((row) => ToggleRowSchema.safeParse(row).success)) {
    return false;
  }

  const groups: Array<[string, string[], (value: string) => string]> = [
    ["enabled", [], chalk.green],
    ["disabled", [], chalk.dim],
  ];

  for (const row of rows) {
    groups[row.enabled === true ? 0 : 1][1].push(toggleLabel(row));
  }

  const populated = groups.filter(([, labels]) => labels.length > 0);
  const indent = options.indent ?? "";
  const headings = populated.map(
    ([group, labels]) => `${group} (${labels.length})`,
  );
  const headingWidth = Math.max(
    ...headings.map((heading) => stringWidth(heading)),
  );
  const available = Math.max(
    40,
    (process.stdout.columns || 100) -
      stringWidth(indent) -
      headingWidth -
      COLUMN_GAP.length,
  );

  populated.forEach(([, labels, paint], groupIndex) => {
    const heading = padColumn(paint(headings[groupIndex]), headingWidth);

    wrapValues(labels, available).forEach((line, lineIndex) => {
      const prefix = lineIndex === 0 ? heading : " ".repeat(headingWidth);
      console.log(`${indent}${prefix}${COLUMN_GAP}${line}`);
    });
  });

  return true;
};

export const renderStructuredCollection = (
  sectionName: string | undefined,
  rows: JsonObject[],
  options: StructuredCollectionRenderOptions = {},
): boolean => {
  const renderer = sectionName
    ? structuredCollectionRenderers[sectionName]
    : undefined;

  if (!renderer) {
    return renderToggleCollection(rows, options);
  }

  const allRowsMatch = rows.every(
    (row) => renderer.itemSchema.safeParse(row).success,
  );

  if (!allRowsMatch) {
    return renderToggleCollection(rows, options);
  }

  const columns = renderer.columns.map((column) =>
    rows.map((row, index) => column.value(row, { index })),
  );

  renderAlignedColumns(
    columns,
    options,
    renderer.columns.map((column) => column.header),
  );

  return true;
};
