import { InvalidArgumentError } from "commander";

type QueryValue = string | number | boolean | null;
type QueryValues = QueryValue | QueryValue[];

export type CliQueryOptions = {
  queries?: string[];
  limit?: number;
  offset?: number;
  cursorAfter?: string;
  cursorBefore?: string;
  sortAsc?: string[];
  sortDesc?: string[];
  select?: string[];
  where?: string[];
};

const stringifyQuery = (
  method: string,
  attribute?: string,
  values?: QueryValues,
): string => {
  const query: {
    method: string;
    attribute?: string;
    values?: QueryValue[];
  } = { method };

  if (attribute !== undefined) {
    query.attribute = attribute;
  }

  if (values !== undefined) {
    query.values = Array.isArray(values) ? values : [values];
  }

  return JSON.stringify(query);
};

const parseQueryValue = (value: string): QueryValues => {
  const normalized = value.trim();

  if (normalized === "true") {
    return true;
  }

  if (normalized === "false") {
    return false;
  }

  if (normalized === "null") {
    return null;
  }

  if (/^-?(?:\d+|\d*\.\d+)(?:e[+-]?\d+)?$/i.test(normalized)) {
    const parsed = Number(normalized);

    if (!Number.isFinite(parsed)) {
      throw new InvalidArgumentError(
        "Numeric filter values must be finite numbers.",
      );
    }

    return parsed;
  }

  if (normalized.startsWith("[") && normalized.endsWith("]")) {
    try {
      const parsed = JSON.parse(normalized) as unknown;
      if (Array.isArray(parsed)) {
        return parsed.map((item) => {
          if (
            item === null ||
            typeof item === "string" ||
            typeof item === "boolean"
          ) {
            return item;
          }

          if (typeof item === "number") {
            if (!Number.isFinite(item)) {
              throw new InvalidArgumentError(
                "Array filter values must be finite numbers.",
              );
            }

            return item;
          }

          throw new InvalidArgumentError(
            "Array filters can only contain strings, numbers, booleans, or null.",
          );
        });
      }
    } catch (error) {
      if (error instanceof InvalidArgumentError) {
        throw error;
      }

      throw new InvalidArgumentError(
        "Array filter values must be valid JSON arrays.",
      );
    }
  }

  return normalized;
};

const whereOperators: Array<[RegExp, string]> = [
  [/^(.+?)\s*!=\s*(.*)$/, "notEqual"],
  [/^(.+?)\s*>=\s*(.*)$/, "greaterThanEqual"],
  [/^(.+?)\s*<=\s*(.*)$/, "lessThanEqual"],
  [/^(.+?)\s*=\s*(.*)$/, "equal"],
  [/^(.+?)\s*>\s*(.*)$/, "greaterThan"],
  [/^(.+?)\s*<\s*(.*)$/, "lessThan"],
];

export const collectQueryValue = <T>(value: T, previous: T[] = []): T[] => [
  ...previous,
  value,
];

export const parseWhereQuery = (expression: string): string => {
  for (const [pattern, method] of whereOperators) {
    const match = expression.match(pattern);

    if (!match) {
      continue;
    }

    const attribute = match[1].trim();
    if (attribute === "") {
      throw new InvalidArgumentError(
        "Where filters must include an attribute before the operator.",
      );
    }

    return stringifyQuery(method, attribute, parseQueryValue(match[2]));
  }

  throw new InvalidArgumentError(
    "Where filters must use one of: field=value, field!=value, field>value, field>=value, field<value, field<=value.",
  );
};

export const buildQueries = ({
  queries,
  limit,
  offset,
  cursorAfter,
  cursorBefore,
  sortAsc,
  sortDesc,
  select,
  where,
}: CliQueryOptions): string[] | undefined => {
  const builtQueries = [...(queries ?? [])];

  if (where) {
    // parseWhereQuery returns fully serialized Appwrite query JSON strings.
    builtQueries.push(...where);
  }

  if (sortAsc) {
    builtQueries.push(
      ...sortAsc.map((attribute) => stringifyQuery("orderAsc", attribute)),
    );
  }

  if (sortDesc) {
    builtQueries.push(
      ...sortDesc.map((attribute) => stringifyQuery("orderDesc", attribute)),
    );
  }

  if (limit !== undefined) {
    builtQueries.push(stringifyQuery("limit", undefined, limit));
  }

  if (offset !== undefined) {
    builtQueries.push(stringifyQuery("offset", undefined, offset));
  }

  if (cursorAfter !== undefined) {
    builtQueries.push(stringifyQuery("cursorAfter", undefined, cursorAfter));
  }

  if (cursorBefore !== undefined) {
    builtQueries.push(stringifyQuery("cursorBefore", undefined, cursorBefore));
  }

  if (select && select.length > 0) {
    builtQueries.push(stringifyQuery("select", undefined, select));
  }

  return builtQueries.length > 0 ? builtQueries : undefined;
};
