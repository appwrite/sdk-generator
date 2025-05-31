/** @typedef {import('../attribute').Attribute} Attribute */
const { AttributeType } = require('../attribute');
const { LanguageMeta } = require("./language");

class Kotlin extends LanguageMeta {
  getType(attribute) {
    let type = "";
    switch (attribute.type) {
      case AttributeType.STRING:
      case AttributeType.EMAIL:
      case AttributeType.DATETIME:
        type = "String";
        if (attribute.format === AttributeType.ENUM) {
          type = LanguageMeta.toPascalCase(attribute.key);
        }
        break;
      case AttributeType.INTEGER:
        type = "Int";
        break;
      case AttributeType.FLOAT:
        type = "Float";
        break;
      case AttributeType.BOOLEAN:
        type = "Boolean";
        break;
      case AttributeType.RELATIONSHIP:
        type = LanguageMeta.toPascalCase(attribute.relatedCollection);
        if ((attribute.relationType === 'oneToMany' && attribute.side === 'parent') || (attribute.relationType === 'manyToOne' && attribute.side === 'child') || attribute.relationType === 'manyToMany') {
          type = `List<${type}>`;
        }
        break;
      default:
        throw new Error(`Unknown attribute type: ${attribute.type}`);
    }
    if (attribute.array) {
      type = "List<" + type + ">";
    }
    if (!attribute.required) {
      type += "?";
    }
    return type;
  }

  getTemplate() {
    return `package io.appwrite.models

<% for (const attribute of collection.attributes) { -%>
<% if (attribute.type === 'relationship') { -%>
import <%- toPascalCase(attribute.relatedCollection) %>

<% } -%>
<% } -%>
<% for (const attribute of collection.attributes) { -%>
<% if (attribute.format === 'enum') { -%>
enum class <%- toPascalCase(attribute.key) %> {
<% for (const [index, element] of Object.entries(attribute.elements)) { -%>
    <%- element %><%- index < attribute.elements.length - 1 ? ',' : '' %>
<% } -%>
}

<% } -%>
<% } -%>
data class <%- toPascalCase(collection.name) %>(
<% for (const attribute of collection.attributes) { -%>
    val <%- toCamelCase(attribute.key) %>: <%- getType(attribute) %>,
<% } -%>
)`;
  }

  getFileName(collection) {
    return LanguageMeta.toPascalCase(collection.name) + ".kt";
  }
}

module.exports = { Kotlin };
