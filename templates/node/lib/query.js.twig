class Query {
  constructor(method, attribute, values) {
    this.method = method
    this.attribute = attribute

    if (values !== undefined) {
      if (Array.isArray(values)) {
        this.values = values
      } else {
        this.values = [values]
      }
    }
  }

  static equal = (attribute, value) =>
    new Query("equal", attribute, value).toString()

  static notEqual = (attribute, value) =>
    new Query("notEqual", attribute, value).toString()

  static lessThan = (attribute, value) =>
    new Query("lessThan", attribute, value).toString()

  static lessThanEqual = (attribute, value) =>
    new Query("lessThanEqual", attribute, value).toString()

  static greaterThan = (attribute, value) =>
    new Query("greaterThan", attribute, value).toString()

  static greaterThanEqual = (attribute, value) =>
    new Query("greaterThanEqual", attribute, value).toString()

  static isNull = attribute =>
    new Query("isNull", attribute).toString()

  static isNotNull = attribute =>
    new Query("isNotNull", attribute).toString()

  static between = (attribute, start, end) =>
    new Query("between", attribute, [start, end]).toString()

  static startsWith = (attribute, value) =>
    new Query("startsWith", attribute, value).toString()

  static endsWith = (attribute, value) =>
    new Query("endsWith", attribute, value).toString()

  static select = attributes =>
    new Query("select", undefined, attributes).toString()

  static search = (attribute, value) =>
    new Query("search", attribute, value).toString()

  static orderDesc = attribute =>
    new Query("orderDesc", attribute).toString()

  static orderAsc = attribute =>
    new Query("orderAsc", attribute).toString()

  static cursorAfter = documentId =>
    new Query("cursorAfter", undefined, documentId).toString()

  static cursorBefore = documentId =>
    new Query("cursorBefore", undefined, documentId).toString()

  static limit = limit =>
    new Query("limit", undefined, limit).toString()

  static offset = offset =>
    new Query("offset", undefined, offset).toString()

  static contains = (attribute, value) =>
    new Query("contains", attribute, value).toString()

  static or = (queries) =>
    new Query("or", undefined, queries.map((query) => JSON.parse(query))).toString()

  static and = (queries) =>
    new Query("and", undefined, queries.map((query) => JSON.parse(query))).toString();
}

Query.prototype.toString = function () {
  return JSON.stringify({
    method: this.method,
    attribute: this.attribute,
    values: this.values
  })
} 

module.exports = Query;
