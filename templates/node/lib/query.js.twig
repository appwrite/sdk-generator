class Query {
  static equal = (attribute, value) =>
    Query.addQuery(attribute, "equal", value);

  static notEqual = (attribute, value) =>
    Query.addQuery(attribute, "notEqual", value);

  static lesser = (attribute, value) =>
    Query.addQuery(attribute, "lesser", value);

  static lesserEqual = (attribute, value) =>
    Query.addQuery(attribute, "lesserEqual", value);

  static greater = (attribute, value) =>
    Query.addQuery(attribute, "greater", value);

  static greaterEqual = (attribute, value) =>
    Query.addQuery(attribute, "greaterEqual", value);

  static search = (attribute, value) =>
    Query.addQuery(attribute, "search", value);

  static addQuery = (attribute, oper, value) =>
    value instanceof Array
      ? `${attribute}.${oper}(${value
          .map((v) => Query.parseValues(v))
          .join(",")})`
      : `${attribute}.${oper}(${Query.parseValues(value)})`;

  static parseValues = (value) =>
    typeof value === "string" || value instanceof String
      ? `"${value}"`
      : `${value}`;
}

module.exports = Query;
