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

    console.log(`${indent}${headerParts.join("  ")}`.trimEnd());
  }

  for (let idx = 0; idx < columns[0].length; idx++) {
    const parts = columns.map((column, columnIndex) => {
      const value = column[idx] ?? "";
      if (columnIndex === columns.length - 1) {
        return value;
      }

      return padColumn(value, widths[columnIndex]);
    });

    console.log(`${indent}${parts.join("  ")}`.trimEnd());
  }
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

  if ("providerMethodId" in row || "last4" in row) {
    return "status: active";
  }

  return "status: pending";
};

const structuredCollectionRenderers: Record<
  string,
  StructuredCollectionRenderer
> = {
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

export const renderStructuredCollection = (
  sectionName: string | undefined,
  rows: JsonObject[],
  options: StructuredCollectionRenderOptions = {},
): boolean => {
  if (!sectionName) {
    return false;
  }

  const renderer = structuredCollectionRenderers[sectionName];
  if (!renderer) {
    return false;
  }

  const allRowsMatch = rows.every(
    (row) => renderer.itemSchema.safeParse(row).success,
  );

  if (!allRowsMatch) {
    return false;
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
