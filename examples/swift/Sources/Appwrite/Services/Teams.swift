

class Teams: Service
{
    /**
     * List Teams
     *
     * Get a list of all the current user teams. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project teams. [Learn more about different API modes](/docs/admin).
     *
     * @param String search
     * @param Int limit
     * @param Int offset
     * @param String orderType
     * @throws Exception
     * @return array
     */

    func listTeams(search: String = null, limit: Int = 25, offset: Int = 0, orderType: String = 'ASC')-> Array<Any> {
        let methodPath = "/teams"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["search"] = search
        params["limit"] = limit
        params["offset"] = offset
        params["orderType"] = orderType

        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Create Team
     *
     * Create a new team. The user who creates the team will automatically be
     * assigned as the owner of the team. The team owner can invite new members,
     * who will be able add new owners and update or delete the team from your
     * project.
     *
     * @param String name
     * @param Array roles
     * @throws Exception
     * @return array
     */

    func createTeam(name: String, roles: Array = const ["owner"])-> Array<Any> {
        let methodPath = "/teams"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name
        params["roles"] = roles

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Get Team
     *
     * Get team by its unique ID. All team members have read access for this
     * resource.
     *
     * @param String teamId
     * @throws Exception
     * @return array
     */

    func getTeam(teamId: String)-> Array<Any> {
        let methodPath = "/teams/{teamId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{teamId}']",
          with: "$teamId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Update Team
     *
     * Update team by its unique ID. Only team owners have write access for this
     * resource.
     *
     * @param String teamId
     * @param String name
     * @throws Exception
     * @return array
     */

    func updateTeam(teamId: String, name: String)-> Array<Any> {
        let methodPath = "/teams/{teamId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{teamId}']",
          with: "$teamId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name

        return self.client.call(HTTPMethod.put, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Delete Team
     *
     * Delete team by its unique ID. Only team owners have write access for this
     * resource.
     *
     * @param String teamId
     * @throws Exception
     * @return array
     */

    func deleteTeam(teamId: String)-> Array<Any> {
        let methodPath = "/teams/{teamId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{teamId}']",
          with: "$teamId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Get Team Members
     *
     * Get team members by the team unique ID. All team members have read access
     * for this list of resources.
     *
     * @param String teamId
     * @throws Exception
     * @return array
     */

    func getTeamMembers(teamId: String)-> Array<Any> {
        let methodPath = "/teams/{teamId}/members"
        let path = methodPath.replacingOccurrences(
          of: "['//{teamId}']",
          with: "$teamId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Create Team Membership
     *
     * Use this endpoint to invite a new member to your team. An email with a link
     * to join the team will be sent to the new member email address. If member
     * doesn't exists in the project it will be automatically created.
     * 
     * Use the redirect parameter to redirect the user from the invitation email
     * back to your app. When the user is redirected, use the
     * /teams/{teamId}/memberships/{inviteId}/status endpoint to finally join the
     * user to the team.
     * 
     * Please notice that in order to avoid a [Redirect
     * Attacks](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URL's are the once from domains you have set when
     * added your platforms in the console interface.
     *
     * @param String teamId
     * @param String email
     * @param Array roles
     * @param String redirect
     * @param String name
     * @throws Exception
     * @return array
     */

    func createTeamMembership(teamId: String, email: String, roles: Array, redirect: String, name: String = null)-> Array<Any> {
        let methodPath = "/teams/{teamId}/memberships"
        let path = methodPath.replacingOccurrences(
          of: "['//{teamId}']",
          with: "$teamId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["email"] = email
        params["name"] = name
        params["roles"] = roles
        params["redirect"] = redirect

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Delete Team Membership
     *
     * This endpoint allows a user to leave a team or for a team owner to delete
     * the membership of any other team member.
     *
     * @param String teamId
     * @param String inviteId
     * @throws Exception
     * @return array
     */

    func deleteTeamMembership(teamId: String, inviteId: String)-> Array<Any> {
        let methodPath = "/teams/{teamId}/memberships/{inviteId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{teamId}', '//{inviteId}']",
          with: "$teamId, $inviteId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Create Team Membership (Resend)
     *
     * Use this endpoint to resend your invitation email for a user to join a
     * team.
     *
     * @param String teamId
     * @param String inviteId
     * @param String redirect
     * @throws Exception
     * @return array
     */

    func createTeamMembershipResend(teamId: String, inviteId: String, redirect: String)-> Array<Any> {
        let methodPath = "/teams/{teamId}/memberships/{inviteId}/resend"
        let path = methodPath.replacingOccurrences(
          of: "['//{teamId}', '//{inviteId}']",
          with: "$teamId, $inviteId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["redirect"] = redirect

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Update Team Membership Status
     *
     * Use this endpoint to let user accept an invitation to join a team after he
     * is being redirect back to your app from the invitation email. Use the
     * success and failure URL's to redirect users back to your application after
     * the request completes.
     * 
     * Please notice that in order to avoid a [Redirect
     * Attacks](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URL's are the once from domains you have set when
     * added your platforms in the console interface.
     * 
     * When not using the success or failure redirect arguments this endpoint will
     * result with a 200 status code on success and with 401 status error on
     * failure. This behavior was applied to help the web clients deal with
     * browsers who don't allow to set 3rd party HTTP cookies needed for saving
     * the account session token.
     *
     * @param String teamId
     * @param String inviteId
     * @param String userId
     * @param String secret
     * @param String success
     * @param String failure
     * @throws Exception
     * @return array
     */

    func updateTeamMembershipStatus(teamId: String, inviteId: String, userId: String, secret: String, success: String = null, failure: String = null)-> Array<Any> {
        let methodPath = "/teams/{teamId}/memberships/{inviteId}/status"
        let path = methodPath.replacingOccurrences(
          of: "['//{teamId}', '//{inviteId}']",
          with: "$teamId, $inviteId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["userId"] = userId
        params["secret"] = secret
        params["success"] = success
        params["failure"] = failure

        return self.client.call(HTTPMethod.patch, path, [
            "content-type": "application/json",
        ], params);
    }

}
