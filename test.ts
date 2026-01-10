import { Client, Users } from "./examples/web/src/index";

const client = new Client();
const users = new Users(client);

users.list({
	queries: ({ equal, or }) => [
		or(
			equal("$id", "value"),
			equal("mfa", true),
			equal("unknown", "value"), // fails because "unknown" doesnt exist on User
			equal("$id", 123), // fails because "$id" is a string
		),
	],
});
