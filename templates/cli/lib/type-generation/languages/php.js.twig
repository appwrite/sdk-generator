/** @typedef {import('../attribute').Attribute} Attribute */
const { AttributeType } = require('../attribute');
const { LanguageMeta } = require("./language");

class PHP extends LanguageMeta {
  getType(attribute) {
    if (attribute.array) {
      return "array";
    }
    let type = ""
    switch (attribute.type) {
      case AttributeType.STRING:
      case AttributeType.EMAIL:
      case AttributeType.DATETIME:
        type = "string";
        if (attribute.format === AttributeType.ENUM) {
          type = LanguageMeta.toPascalCase(attribute.key);
        }
        break;
      case AttributeType.INTEGER:
        type = "int";
        break;
      case AttributeType.FLOAT:
        type = "float";
        break;
      case AttributeType.BOOLEAN:
        type = "bool";
        break;
      case AttributeType.RELATIONSHIP:
        type = LanguageMeta.toPascalCase(attribute.relatedCollection);
        if ((attribute.relationType === 'oneToMany' && attribute.side === 'parent') || (attribute.relationType === 'manyToOne' && attribute.side === 'child') || attribute.relationType === 'manyToMany') {
          type = "array";
        }
        break;
      default:
        throw new Error(`Unknown attribute type: ${attribute.type}`);
    }
    if (!attribute.required) {
      type += "|null";
    }
    return type;
  }

  getTemplate() {
    return `<?php
namespace Appwrite\\Models;

<% for (const attribute of collection.attributes) { -%>
<% if (attribute.type === 'relationship' && !(attribute.relationType === 'manyToMany') && !(attribute.relationType === 'oneToMany' && attribute.side === 'parent')) { -%>
use Appwrite\\Models\\<%- toPascalCase(attribute.relatedCollection) %>;

<% } -%>
<% } -%>
<% for (const attribute of collection.attributes) { -%>
<% if (attribute.format === 'enum') { -%>
enum <%- toPascalCase(attribute.key) %> {
<% for (const [index, element] of Object.entries(attribute.elements)) { -%>
  case <%- element.toUpperCase() %> = '<%- element %>';
<% } -%>
}

<% } -%>
<% } -%>
class <%- toPascalCase(collection.name) %> {
<% for (const attribute of collection.attributes ){ -%>
  private <%- getType(attribute) %> $<%- toCamelCase(attribute.key) %>;
<% } -%>

  public function __construct(
<% for (const attribute of collection.attributes ){ -%>
<% if (attribute.required) { -%>
    <%- getType(attribute).replace('|null', '') %> $<%- toCamelCase(attribute.key) %><% if (collection.attributes.indexOf(attribute) < collection.attributes.length - 1) { %>,<% } %>
<% } else { -%>
    ?<%- getType(attribute).replace('|null', '') %> $<%- toCamelCase(attribute.key) %> = null<% if (collection.attributes.indexOf(attribute) < collection.attributes.length - 1) { %>,<% } %>
<% } -%>
<% } -%>
  ) {
<% for (const attribute of collection.attributes ){ -%>
    $this-><%- toCamelCase(attribute.key) %> = $<%- toCamelCase(attribute.key) %>;
<% } -%>
  }

<% for (const attribute of collection.attributes ){ -%>
  public function get<%- toPascalCase(attribute.key) %>(): <%- getType(attribute) %> {
    return $this-><%- toCamelCase(attribute.key) %>;
  }

  public function set<%- toPascalCase(attribute.key) %>(<%- getType(attribute) %> $<%- toCamelCase(attribute.key) %>): void {
    $this-><%- toCamelCase(attribute.key) %> = $<%- toCamelCase(attribute.key) %>;
  }
<% } -%>
}`;
  }

  getFileName(collection) {
    return LanguageMeta.toPascalCase(collection.name) + ".php";
  }
}

module.exports = { PHP };
