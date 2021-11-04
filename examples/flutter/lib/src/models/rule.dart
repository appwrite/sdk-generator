part of appwrite.models;

/// Rule
class Rule {
    /// Rule ID.
    final String $id;
    /// Rule Collection.
    final String $collection;
    /// Rule type. Possible values: 
    final String type;
    /// Rule key.
    final String key;
    /// Rule label.
    final String label;
    /// Rule default value.
    final String xdefault;
    /// Is array?
    final bool array;
    /// Is required?
    final bool xrequired;
    /// List of allowed values
    final List list;

    Rule({
        required this.$id,
        required this.$collection,
        required this.type,
        required this.key,
        required this.label,
        required this.xdefault,
        required this.array,
        required this.xrequired,
        required this.list,
    });

    factory Rule.fromMap(Map<String, dynamic> map) {
        return Rule(
            $id: map['\$id'],
            $collection: map['\$collection'],
            type: map['type'],
            key: map['key'],
            label: map['label'],
            xdefault: map['default'],
            array: map['array'],
            xrequired: map['required'],
            list: map['list'],
        );
    }

    Map<String, dynamic> toMap() {
        return {
            "\$id": $id,
            "\$collection": $collection,
            "type": type,
            "key": key,
            "label": label,
            "default": xdefault,
            "array": array,
            "required": xrequired,
            "list": list,
        };
    }
}
