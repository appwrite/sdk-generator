part of appwrite.models;

/// Collection
class Collection {
    /// Collection ID.
    final String $id;
    /// Collection permissions.
    final Permissions $permissions;
    /// Collection name.
    final String name;
    /// Collection creation date in Unix timestamp.
    final int dateCreated;
    /// Collection creation date in Unix timestamp.
    final int dateUpdated;
    /// Collection rules.
    final List<Rule> rules;

    Collection({
        required this.$id,
        required this.$permissions,
        required this.name,
        required this.dateCreated,
        required this.dateUpdated,
        required this.rules,
    });

    factory Collection.fromMap(Map<String, dynamic> map) {
        return Collection(
            $id: map['\$id'],
            $permissions: Permissions.fromMap(map['\$permissions']),
            name: map['name'],
            dateCreated: map['dateCreated'],
            dateUpdated: map['dateUpdated'],
            rules: List<Rule>.from(map['rules'].map((p) => Rule.fromMap(p))),
        );
    }

    Map<String, dynamic> toMap() {
        return {
            "\$id": $id,
            "\$permissions": $permissions.toMap(),
            "name": name,
            "dateCreated": dateCreated,
            "dateUpdated": dateUpdated,
            "rules": rules.map((p) => p.toMap()),
        };
    }
}
