part of appwrite.models;

/// Tags List
class TagList {
    /// Total number of items available on the server.
    final int sum;
    /// List of tags.
    final List<Tag> tags;

    TagList({
        required this.sum,
        required this.tags,
    });

    factory TagList.fromMap(Map<String, dynamic> map) {
        return TagList(
            sum: map['sum'],
            tags: List<Tag>.from(map['tags'].map((p) => Tag.fromMap(p))),
        );
    }

    Map<String, dynamic> toMap() {
        return {
            "sum": sum,
            "tags": tags.map((p) => p.toMap()),
        };
    }
}
