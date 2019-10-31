using Service;
using System.Collections.Generic;

public class Teams : Service
{
    public GetTeam(string id)
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/teams/{id}";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteTeam(string id)
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/teams/{id}";

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreateTeam(string name, string[] roles = new string[]{"owner"})
    {
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "roles" = roles
        };
        path = $"/teams/{id}";

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateTeam(string id, string name)
    {
        paramsIn = new Dictionary<string, string>
        {
            "name" = name
        };
        path = $"/teams/{id}";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public ListTeams(string search="", int limit = 25, int offset = 0, string orderType = "ASC")
    {
        paramsIn = new Dictionary<string, string>
        {
            "search" = search,
            "limit" = limit,
            "offset" = offset,
            "orderType" = orderType
        };
        path = $"/teams";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetTeamMembers(string id)
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/teams/{id}/members";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreateTeamMembership(string id, string email, string[] roles, string redirect, string name = "")
    {
        paramsIn = new Dictionary<string, string>
        {
            "email" = email,
            "roles" = roles,
            "redirect" = redirect,
            "name" = name
        };
        path = $"/teams/{id}/memberships";

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteTeamMembership(string id, string inviteId)
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/teams/{id}/memberships/{inviteId}";

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreateTeamMembershipResend(string id, string inviteId, string redirect)
    {
        paramsIn = new Dictionary<string, string>
        {
            "redirect" = redirect
        };
        path = $"/teams/{id}/memberships/{inviteId}/resend";

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateTeamMembershipStatus(string id, string inviteId, string userId, string secret, string success = "", string failure = "")
    {
        paramsIn = new Dictionary<string, string>
        {
            "userId" = userId,
            "secret" = secret,
            "success" = success,
            "failure" = failure
        };
        path = $"/teams/{id}/memberships/{inviteId}/status";

        return _client.Call("patch", path, new Dictionary<string, string>(), paramsIn);
    }
}