part of appwrite.models;

/// Membership
class Membership {
    /// Membership ID.
    final String $id;
    /// User ID.
    final String userId;
    /// Team ID.
    final String teamId;
    /// User name.
    final String name;
    /// User email address.
    final String email;
    /// Date, the user has been invited to join the team in Unix timestamp.
    final int invited;
    /// Date, the user has accepted the invitation to join the team in Unix timestamp.
    final int joined;
    /// User confirmation status, true if the user has joined the team or false otherwise.
    final bool confirm;
    /// User list of roles
    final List roles;

    Membership({
        required this.$id,
        required this.userId,
        required this.teamId,
        required this.name,
        required this.email,
        required this.invited,
        required this.joined,
        required this.confirm,
        required this.roles,
    });

    factory Membership.fromMap(Map<String, dynamic> map) {
        return Membership(
            $id: map['\$id'],
            userId: map['userId'],
            teamId: map['teamId'],
            name: map['name'],
            email: map['email'],
            invited: map['invited'],
            joined: map['joined'],
            confirm: map['confirm'],
            roles: map['roles'],
        );
    }

    Map<String, dynamic> toMap() {
        return {
            "\$id": $id,
            "userId": userId,
            "teamId": teamId,
            "name": name,
            "email": email,
            "invited": invited,
            "joined": joined,
            "confirm": confirm,
            "roles": roles,
        };
    }
}
