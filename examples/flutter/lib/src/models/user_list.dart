part of appwrite.models;

/// Users List
class UserList {
    /// Total number of items available on the server.
    final int sum;
    /// List of users.
    final List<User> users;

    UserList({
        required this.sum,
        required this.users,
    });

    factory UserList.fromMap(Map<String, dynamic> map) {
        return UserList(
            sum: map['sum'],
            users: List<User>.from(map['users'].map((p) => User.fromMap(p))),
        );
    }

    Map<String, dynamic> toMap() {
        return {
            "sum": sum,
            "users": users.map((p) => p.toMap()),
        };
    }
}
