import { ZodError, ZodIssue, z } from "zod";
import type {
  $ZodIssueInvalidType,
  $ZodIssueTooBig,
  $ZodIssueTooSmall,
  $ZodIssueUnrecognizedKeys,
  $ZodIssueNotMultipleOf,
  $ZodIssueInvalidStringFormat,
} from "zod/v4/core";

/**
 * Formats a Zod validation error into a human-readable message
 */
export class ZodErrorFormatter {
  static formatError(error: ZodError, contextData?: any): string {
    const issues = error.issues;

    if (issues.length === 1) {
      return this.formatIssue(issues[0], contextData);
    }

    const messages = issues.map(
      (issue) => `• ${this.formatIssue(issue, contextData)}`,
    );
    return `Found ${issues.length} validation errors:\n\n${messages.join("\n\n")}`;
  }

  private static formatIssue(issue: ZodIssue, contextData?: any): string {
    const location = this.formatPath(issue.path, contextData);
    const locationText = location ? ` at ${location}` : "";

    switch (issue.code) {
      case "unrecognized_keys": {
        const unrecognizedIssue = issue as $ZodIssueUnrecognizedKeys;
        const keys = unrecognizedIssue.keys.map((key) => `"${key}"`).join(", ");
        const propertyWord =
          unrecognizedIssue.keys.length === 1 ? "property" : "properties";
        return `Unexpected ${propertyWord} ${keys}${locationText}`;
      }

      case "invalid_type": {
        const invalidTypeIssue = issue as $ZodIssueInvalidType;
        return `Expected ${invalidTypeIssue.expected}, got ${this.formatValue(invalidTypeIssue.input)}${locationText}`;
      }

      case "too_small": {
        const tooSmallIssue = issue as $ZodIssueTooSmall;
        const minimum = tooSmallIssue.minimum;
        const origin = tooSmallIssue.origin;

        if (origin === "array") {
          const itemWord = minimum === 1 ? "item" : "items";
          return `Array${locationText} must have at least ${minimum} ${itemWord}`;
        } else if (origin === "string") {
          const charWord = minimum === 1 ? "character" : "characters";
          return `String${locationText} must be at least ${minimum} ${charWord}`;
        } else if (origin === "number") {
          return `Number${locationText} must be at least ${minimum}`;
        }
        return `Value${locationText} must be at least ${minimum}`;
      }

      case "too_big": {
        const tooBigIssue = issue as $ZodIssueTooBig;
        const maximum = tooBigIssue.maximum;
        const origin = tooBigIssue.origin;

        if (origin === "array") {
          const itemWord = maximum === 1 ? "item" : "items";
          return `Array${locationText} must have at most ${maximum} ${itemWord}`;
        } else if (origin === "string") {
          const charWord = maximum === 1 ? "character" : "characters";
          return `String${locationText} must be at most ${maximum} ${charWord}`;
        } else if (origin === "number") {
          return `Number${locationText} must be at most ${maximum}`;
        }
        return `Value${locationText} must be at most ${maximum}`;
      }

      case "invalid_format": {
        const formatIssue = issue as $ZodIssueInvalidStringFormat;
        return `Invalid ${formatIssue.format} format${locationText}`;
      }

      case "invalid_union": {
        // Check if this is an enum validation error by examining the issue context
        const unionIssue = issue as any;
        if (unionIssue.unionErrors && unionIssue.unionErrors.length > 0) {
          // Look for enum-like validation errors
          const enumError = unionIssue.unionErrors.find(
            (err: any) =>
              err.issues &&
              err.issues.some(
                (subIssue: any) =>
                  subIssue.code === "invalid_literal" ||
                  subIssue.code === "invalid_enum_value",
              ),
          );

          if (enumError) {
            // Extract allowed values from the enum error
            const enumIssues = enumError.issues.filter(
              (subIssue: any) =>
                subIssue.code === "invalid_literal" ||
                subIssue.code === "invalid_enum_value",
            );

            if (enumIssues.length > 0) {
              // Try to extract the allowed values
              const allowedValues = this.extractAllowedEnumValues(
                unionIssue.unionErrors,
              );
              if (allowedValues.length > 0) {
                const valuesList = allowedValues
                  .map((v) => `"${v}"`)
                  .join(", ");
                return `Invalid value${locationText}. Expected one of: ${valuesList}`;
              }
            }
          }
        }
        return `Invalid value${locationText}. None of the allowed types matched`;
      }

      case "custom":
        return `${issue.message || "Custom validation failed"}${locationText}`;

      case "not_multiple_of": {
        const multipleOfIssue = issue as $ZodIssueNotMultipleOf;
        return `Number${locationText} must be a multiple of ${multipleOfIssue.divisor}`;
      }

      case "invalid_key":
        return `Invalid key${locationText}`;

      case "invalid_element":
        return `Invalid element${locationText}`;

      case "invalid_value": {
        const invalidValueIssue = issue as any;
        if (
          invalidValueIssue.values &&
          Array.isArray(invalidValueIssue.values)
        ) {
          const allowedValues = invalidValueIssue.values
            .map((v: any) => `"${v}"`)
            .join(", ");
          return `Invalid value${locationText}. Expected one of: ${allowedValues}`;
        }
        return `Invalid value${locationText}`;
      }

      default:
        return `${(issue as ZodIssue).message || "Validation failed"}${locationText}`;
    }
  }

  private static formatValue(value: unknown): string {
    if (value === null) {
      return "null";
    }
    if (value === undefined) {
      return "undefined";
    }
    if (typeof value === "string") {
      return `"${value}"`;
    }
    return String(value);
  }

  private static formatPath(path: PropertyKey[], contextData?: any): string {
    if (path.length === 0) return "";

    const formatted: string[] = [];

    for (let i = 0; i < path.length; i++) {
      const segment = path[i];

      if (typeof segment === "number") {
        formatted.push(`[${segment}]`);
      } else if (typeof segment === "string") {
        if (i === 0) {
          formatted.push(segment);
        } else {
          formatted.push(`.${segment}`);
        }
      } else {
        // Handle symbol keys by converting to string
        formatted.push(`.${String(segment)}`);
      }
    }

    return this.humanizePath(formatted.join(""), contextData);
  }

  private static humanizePath(path: string, contextData?: any): string {
    // Try to resolve names from context data first
    if (contextData) {
      const resolvedPath = this.resolvePathWithNames(path, contextData);
      if (resolvedPath !== path) {
        return resolvedPath;
      }
    }

    const patterns = [
      {
        pattern: /^collections\[(\d+)\]\.attributes\[(\d+)\]$/,
        replacement: "Collections $1 → attributes $2",
      },
      {
        pattern: /^collections\[(\d+)\]\.indexes\[(\d+)\]$/,
        replacement: "Collections $1 → indexes $2",
      },
      {
        pattern: /^collections\[(\d+)\]$/,
        replacement: "Collections $1",
      },
      { pattern: /^databases\[(\d+)\]$/, replacement: "Databases $1" },
      { pattern: /^functions\[(\d+)\]$/, replacement: "Functions $1" },
      { pattern: /^sites\[(\d+)\]$/, replacement: "Sites $1" },
      { pattern: /^buckets\[(\d+)\]$/, replacement: "Buckets $1" },
      { pattern: /^teams\[(\d+)\]$/, replacement: "Teams $1" },
      { pattern: /^topics\[(\d+)\]$/, replacement: "Topics $1" },
      {
        pattern: /^settings\.auth\.methods$/,
        replacement: "auth.methods",
      },
      {
        pattern: /^settings\.auth\.security$/,
        replacement: "auth.security",
      },
      { pattern: /^settings\.services$/, replacement: "services" },
    ];

    for (const { pattern, replacement } of patterns) {
      if (pattern.test(path)) {
        return path.replace(pattern, replacement);
      }
    }

    return path
      .replace(/\[(\d+)\]/g, " $1")
      .replace(/\./g, " → ")
      .replace(/^(\w)/, (match) => match.toUpperCase());
  }

  private static resolvePathWithNames(path: string, contextData: any): string {
    // Handle collections and their attributes/indexes
    const collectionAttributeMatch = path.match(
      /^collections\[(\d+)\]\.attributes\[(\d+)\](.*)$/,
    );
    if (collectionAttributeMatch) {
      const [, collectionIndex, attributeIndex, remainder] =
        collectionAttributeMatch;
      const collection = contextData.collections?.[parseInt(collectionIndex)];
      const attribute = collection?.attributes?.[parseInt(attributeIndex)];

      if (collection && attribute) {
        const collectionName = collection.name || collection.$id;
        const attributeName = attribute.key;
        return `Collections "${collectionName}" → attributes "${attributeName}"${remainder}`;
      }
    }

    const collectionIndexMatch = path.match(
      /^collections\[(\d+)\]\.indexes\[(\d+)\](.*)$/,
    );
    if (collectionIndexMatch) {
      const [, collectionIndex, indexIndex, remainder] = collectionIndexMatch;
      const collection = contextData.collections?.[parseInt(collectionIndex)];
      const index = collection?.indexes?.[parseInt(indexIndex)];

      if (collection && index) {
        const collectionName = collection.name || collection.$id;
        const indexName = index.key;
        return `Collections "${collectionName}" → indexes "${indexName}"${remainder}`;
      }
    }

    const collectionMatch = path.match(/^collections\[(\d+)\](.*)$/);
    if (collectionMatch) {
      const [, collectionIndex, remainder] = collectionMatch;
      const collection = contextData.collections?.[parseInt(collectionIndex)];

      if (collection) {
        const collectionName = collection.name || collection.$id;
        return `Collections "${collectionName}"${remainder}`;
      }
    }

    // Handle databases
    const databaseMatch = path.match(/^databases\[(\d+)\](.*)$/);
    if (databaseMatch) {
      const [, databaseIndex, remainder] = databaseMatch;
      const database = contextData.databases?.[parseInt(databaseIndex)];

      if (database) {
        const databaseName = database.name || database.$id;
        return `Databases "${databaseName}"${remainder}`;
      }
    }

    // Handle functions
    const functionMatch = path.match(/^functions\[(\d+)\](.*)$/);
    if (functionMatch) {
      const [, functionIndex, remainder] = functionMatch;
      const func = contextData.functions?.[parseInt(functionIndex)];

      if (func) {
        const functionName = func.name || func.$id;
        return `Functions "${functionName}"${remainder}`;
      }
    }

    // Handle sites
    const siteMatch = path.match(/^sites\[(\d+)\](.*)$/);
    if (siteMatch) {
      const [, siteIndex, remainder] = siteMatch;
      const site = contextData.sites?.[parseInt(siteIndex)];

      if (site) {
        const siteName = site.name || site.$id;
        return `Sites "${siteName}"${remainder}`;
      }
    }

    // Handle buckets
    const bucketMatch = path.match(/^buckets\[(\d+)\](.*)$/);
    if (bucketMatch) {
      const [, bucketIndex, remainder] = bucketMatch;
      const bucket = contextData.buckets?.[parseInt(bucketIndex)];

      if (bucket) {
        const bucketName = bucket.name || bucket.$id;
        return `Buckets "${bucketName}"${remainder}`;
      }
    }

    // Handle teams
    const teamMatch = path.match(/^teams\[(\d+)\](.*)$/);
    if (teamMatch) {
      const [, teamIndex, remainder] = teamMatch;
      const team = contextData.teams?.[parseInt(teamIndex)];

      if (team) {
        const teamName = team.name || team.$id;
        return `Teams "${teamName}"${remainder}`;
      }
    }

    // Handle topics
    const topicMatch = path.match(/^topics\[(\d+)\](.*)$/);
    if (topicMatch) {
      const [, topicIndex, remainder] = topicMatch;
      const topic = contextData.topics?.[parseInt(topicIndex)];

      if (topic) {
        const topicName = topic.name || topic.$id;
        return `Topics "${topicName}"${remainder}`;
      }
    }

    return path;
  }

  private static extractAllowedEnumValues(unionErrors: any[]): string[] {
    const allowedValues = new Set<string>();

    for (const error of unionErrors) {
      if (error.issues) {
        for (const issue of error.issues) {
          if (
            issue.code === "invalid_literal" &&
            issue.expected !== undefined
          ) {
            allowedValues.add(String(issue.expected));
          } else if (issue.code === "invalid_enum_value" && issue.options) {
            issue.options.forEach((option: any) =>
              allowedValues.add(String(option)),
            );
          }
        }
      }
    }

    return Array.from(allowedValues).sort();
  }
}

/**
 * Helper function to wrap Zod parse calls with better error formatting
 * This function outputs the error directly to console and exits the process
 */
export function parseWithBetterErrors<T>(
  schema: z.ZodTypeAny,
  data: unknown,
  context?: string,
  contextData?: any,
): T {
  try {
    return schema.parse(data) as T;
  } catch (error) {
    if (error instanceof ZodError) {
      const formattedMessage = ZodErrorFormatter.formatError(
        error,
        contextData,
      );
      const errorMessage = context
        ? `❌ ${context}: ${formattedMessage}`
        : `❌ ${formattedMessage}`;

      console.error(errorMessage);
      process.exit(1);
    }
    throw error;
  }
}
