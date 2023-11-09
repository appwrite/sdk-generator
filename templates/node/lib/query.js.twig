class Query {
  static equal = (attribute, value) =>
    Query.addQuery(attribute, "equal", value);

  static notEqual = (attribute, value) =>
    Query.addQuery(attribute, "notEqual", value);

  static lessThan = (attribute, value) =>
    Query.addQuery(attribute, "lessThan", value);

  static lessThanEqual = (attribute, value) =>
    Query.addQuery(attribute, "lessThanEqual", value);

  static greaterThan = (attribute, value) =>
    Query.addQuery(attribute, "greaterThan", value);

  static greaterThanEqual = (attribute, value) =>
    Query.addQuery(attribute, "greaterThanEqual", value);

  static isNull = (attribute) =>
    `isNull("${attribute}")`;

  static isNotNull = (attribute) =>
    `isNotNull("${attribute}")`;

  static between = (attribute, start, end) =>
    `between("${attribute}", ${Query.parseValues(start)}, ${Query.parseValues(end)})`

  static startsWith = (attribute, value) =>
    Query.addQuery(attribute, "startsWith", value);

  static endsWith = (attribute, value) =>
    Query.addQuery(attribute, "endsWith", value);

  static select = (attributes) =>
    `select([${attributes.map((attr) => `"${attr}"`).join(",")}])`;

  static search = (attribute, value) =>
    Query.addQuery(attribute, "search", value);

  static orderDesc = (attribute) =>
    `orderDesc("${attribute}")`;

  static orderAsc = (attribute) =>
    `orderAsc("${attribute}")`;

  static cursorAfter = (documentId) =>
    `cursorAfter("${documentId}")`;

  static cursorBefore = (documentId) =>
    `cursorBefore("${documentId}")`;

  static limit = (limit) =>
    `limit(${limit})`;

  static offset = (offset) =>
    `offset(${offset})`;

  static addQuery = (attribute, method, value) =>
    value instanceof Array
      ? `${method}("${attribute}", [${value
          .map((v) => Query.parseValues(v))
          .join(",")}])`
      : `${method}("${attribute}", [${Query.parseValues(value)}])`;

  static parseValues = (value) =>
    typeof value === "string" || value instanceof String
      ? `"${value}"`
      : `${value}`;
}

module.exports = Query;
