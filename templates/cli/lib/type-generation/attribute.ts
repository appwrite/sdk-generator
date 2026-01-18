export const AttributeType = {
  STRING: "string",
  INTEGER: "integer",
  FLOAT: "double",
  BOOLEAN: "boolean",
  DATETIME: "datetime",
  EMAIL: "email",
  IP: "ip",
  URL: "url",
  ENUM: "enum",
  RELATIONSHIP: "relationship",
  POINT: "point",
  LINESTRING: "linestring",
  POLYGON: "polygon",
} as const;

export type AttributeTypeValue =
  (typeof AttributeType)[keyof typeof AttributeType];
