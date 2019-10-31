using Service;
using System.Collections.Generic;

public class Users : Service
{
    public ListUsers(string search = "", int limit = 25, int offset = 0, string orderType="ASC")
    {
        path = "/users";
        paramsIn = new Dictionary<string, string>
        {
            "search" = search,
            "limit" = limit,
            "offset" = offset,
            "orderType" = orderType
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreateUsers(string email, string password, string name = "")
    {
        path = "/users";
        paramsIn = new Dictionary<string, string>
        {
            "email" = email,
            "password" = password,
            "name" = name
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetUser(string userId)
    {
        path = $"/users/{userId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetUserLogs(string userId)
    {
        path = $"/users/{userId}/logs";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetUserPrefs(string userId)
    {
        path = $"/users/{userId}/prefs";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateUserPrefs(string userId, string prefs)
    {
        path = $"/users/{userId}/prefs";
        paramsIn = new Dictionary<string, string>
        {
            "prefs" = prefs
        };

        return _client.Call("patch", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetUserSessions(string userId)
    {
        path = $"/users/{userId}/sessions";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteUserSessions(string userId)
    {
        path = $"/users/{userId}/sessions";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteUserSession(string userId, string sessionId)
    {
        path = $"/users/{userId}/sessions/{sessionId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetUserStatus(string userId)
    {
        path = $"/users/{userId}/status";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateUserStatus(string userId, string status)
    {
        path = $"/users/{userId}/status";
        paramsIn = new Dictionary<string, string>
        {
            "status" = status
        };

        return _client.Call("patch", path, new Dictionary<string, string>(), paramsIn);
    }
}