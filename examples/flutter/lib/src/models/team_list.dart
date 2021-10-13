part of appwrite.models;

/// Teams List
class TeamList implements Model {
    /// Total sum of items in the list.
    final int sum;
    /// List of teams.
    final List<Team> teams;

    TeamList({
        required this.sum,
        required this.teams,
    });

    factory TeamList.fromMap(Map<String, dynamic> map) {
        return TeamList(
            sum: map['sum'],
            teams: List<Team>.from(map['teams'].map((p) => Team.fromMap(p))),
        );
    }

    @override
    Map<String, dynamic> toMap() {
        return {
            "sum": sum,
            "teams": teams.map((p) => p.toMap()),
        };
    }
}
