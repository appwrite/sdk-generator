/** @typedef {import('../attribute').Attribute} Attribute */
const { AttributeType } = require('../attribute');
const { LanguageMeta } = require("./language");

class Dart extends LanguageMeta {
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
        type = "int";
        break;
      case AttributeType.FLOAT:
        type = "double";
        break;
      case AttributeType.BOOLEAN:
        type = "bool";
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
      type = `List<${type}>`;
    }
    if (!attribute.required) {
      type += "?";
    }
    return type;
  }

  getTemplate() {
    return `<% for (const attribute of collection.attributes) { -%>
<% if (attribute.type === 'relationship') { -%>
import '<%- attribute.relatedCollection.toLowerCase() %>.dart';

<% } -%>
<% } -%>
<% for (const attribute of collection.attributes) { -%>
<% if (attribute.format === 'enum') { -%>
enum <%- toPascalCase(attribute.key) %> {
<% for (const element of attribute.elements) { -%>
  <%- element %>,
<% } -%>
}

<% } -%>
<% } -%>
class <%= toPascalCase(collection.name) %> {
<% for (const [index, attribute] of Object.entries(collection.attributes)) { -%>
  <%- getType(attribute) %> <%= toCamelCase(attribute.key) %>;
<% } -%>

  <%= toPascalCase(collection.name) %>({
  <% for (const [index, attribute] of Object.entries(collection.attributes)) { -%>
  <% if (attribute.required) { %>required <% } %>this.<%= toCamelCase(attribute.key) %>,
  <% } -%>
});

  factory <%= toPascalCase(collection.name) %>.fromMap(Map<String, dynamic> map) {
    return <%= toPascalCase(collection.name) %>(
<% for (const [index, attribute] of Object.entries(collection.attributes)) { -%>
      <%= toCamelCase(attribute.key) %>: <% if (attribute.type === 'string' || attribute.type === 'email' || attribute.type === 'datetime') { -%>
<% if (attribute.format === 'enum') { -%>
<% if (attribute.array) { -%>
(map['<%= attribute.key %>'] as List<dynamic>?)?.map((e) => <%- toPascalCase(attribute.key) %>.values.firstWhere((element) => element.name == e)).toList()<% if (!attribute.required) { %> ?? []<% } -%>
<% } else { -%>
<% if (!attribute.required) { -%>
map['<%= attribute.key %>'] != null ? <%- toPascalCase(attribute.key) %>.values.where((e) => e.name == map['<%= attribute.key %>']).firstOrNull : null<% } else { -%>
<%- toPascalCase(attribute.key) %>.values.firstWhere((e) => e.name == map['<%= attribute.key %>'])<% } -%>
<% } -%>
<% } else { -%>
<% if (attribute.array) { -%>
List<String>.from(map['<%= attribute.key %>'] ?? [])<% if (!attribute.required) { %> ?? []<% } -%>
<% } else { -%>
map['<%= attribute.key %>']<% if (!attribute.required) { %>?<% } %>.toString()<% if (!attribute.required) { %> ?? null<% } -%>
<% } -%>
<% } -%>
<% } else if (attribute.type === 'integer') { -%>
<% if (attribute.array) { -%>
List<int>.from(map['<%= attribute.key %>'] ?? [])<% if (!attribute.required) { %> ?? []<% } -%>
<% } else { -%>
map['<%= attribute.key %>']<% if (!attribute.required) { %> ?? null<% } -%>
<% } -%>
<% } else if (attribute.type === 'float') { -%>
<% if (attribute.array) { -%>
List<double>.from(map['<%= attribute.key %>'] ?? [])<% if (!attribute.required) { %> ?? []<% } -%>
<% } else { -%>
map['<%= attribute.key %>']<% if (!attribute.required) { %> ?? null<% } -%>
<% } -%>
<% } else if (attribute.type === 'boolean') { -%>
<% if (attribute.array) { -%>
List<bool>.from(map['<%= attribute.key %>'] ?? [])<% if (!attribute.required) { %> ?? []<% } -%>
<% } else { -%>
map['<%= attribute.key %>']<% if (!attribute.required) { %> ?? null<% } -%>
<% } -%>
<% } else if (attribute.type === 'relationship') { -%>
<% if ((attribute.relationType === 'oneToMany' && attribute.side === 'parent') || (attribute.relationType === 'manyToOne' && attribute.side === 'child') || attribute.relationType === 'manyToMany') { -%>
(map['<%= attribute.key %>'] as List<dynamic>?)?.map((e) => <%- toPascalCase(attribute.relatedCollection) %>.fromMap(e)).toList()<% if (!attribute.required) { %> ?? []<% } -%>
<% } else { -%>
<% if (!attribute.required) { -%>
map['<%= attribute.key %>'] != null ? <%- toPascalCase(attribute.relatedCollection) %>.fromMap(map['<%= attribute.key %>']) : null<% } else { -%>
<%- toPascalCase(attribute.relatedCollection) %>.fromMap(map['<%= attribute.key %>'])<% } -%>
<% } -%>
<% } -%>,
<% } -%>
    );
  }

  Map<String, dynamic> toMap() {
    return {
<% for (const [index, attribute] of Object.entries(collection.attributes)) { -%>
      "<%= attribute.key %>": <% if (attribute.type === 'relationship') { -%>
<% if ((attribute.relationType === 'oneToMany' && attribute.side === 'parent') || (attribute.relationType === 'manyToOne' && attribute.side === 'child') || attribute.relationType === 'manyToMany') { -%>
<%= toCamelCase(attribute.key) %><% if (!attribute.required) { %>?<% } %>.map((e) => e.toMap()).toList()<% if (!attribute.required) { %> ?? []<% } -%>
<% } else { -%>
<%= toCamelCase(attribute.key) %><% if (!attribute.required) { %>?<% } %>.toMap()<% if (!attribute.required) { %> ?? {}<% } -%>
<% } -%>
<% } else if (attribute.format === 'enum') { -%>
<% if (attribute.array) { -%>
<%= toCamelCase(attribute.key) %><% if (!attribute.required) { %>?<% } %>.map((e) => e.name).toList()<% if (!attribute.required) { %> ?? []<% } -%>
<% } else { -%>
<%= toCamelCase(attribute.key) %><% if (!attribute.required) { %>?<% } %>.name<% if (!attribute.required) { %> ?? null<% } -%>
<% } -%>
<% } else { -%>
<%= toCamelCase(attribute.key) -%>
<% } -%>,
<% } -%>
    };
  }
}
`;
  }

  getFileName(collection) {
    return LanguageMeta.toSnakeCase(collection.name) + ".dart";
  }
}

module.exports = { Dart };