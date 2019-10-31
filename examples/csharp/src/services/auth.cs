using Service;
using System.Collections.Generic;

public class Auth : Service
{
    public Login(string email, string password, string success, string failure)
    {
        path = $"/users/{userId}/sessions";
        paramsIn = new Dictionary<string, string>
        {
            "email" = email,
            "password" = password,
            "success" = success,
            "failure" = failure
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public Logout()
    {
        path = $"/auth/logout";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public LogoutBySession(string id)
    {
        path = $"/auth/logout/{id}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public OAuth(string provider, string success, string failure)
    {
        path = $"/auth/oauth/{provider}";
        paramsIn = new Dictionary<string, string>
        {
            "success" = success,
            "failure" = failure
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public Recovery(string email, string reset)
    {
        path = $"/auth/recovery";
        paramsIn = new Dictionary<string, string>
        {
            "email" = email,
            "reset" = reset
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public RecoveryReset(string id, string token, string passwordA, string passwordB)
    {
        path = $"/auth/recovery";
        paramsIn = new Dictionary<string, string>
        {
            "userId" = id,
            "token" = token,
            "password-a" = passwordA,
            "password-b" = passwordB
        };

        return _client.Call("put", path, new Dictionary<string, string>(), paramsIn);
    }

    public Register(string email, string password, string confirm, string success = "", string failure = "", string name = "")
    {
        path = $"/auth/register";
        paramsIn = new Dictionary<string, string>
        {
            "email" = email,
            "password" = password,
            "confirm" = confirm,
            "success" = success,
            "failure" = failure,
            "name" = name
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public Confirm(string id, string token)
    {
        path = $"/auth/register/confirm";
        paramsIn = new Dictionary<string, string>
        {
            "userId" = id,
            "token" = token
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public ConfirmResend(string confirm)
    {
        path = $"/auth/register/confirm";
        paramsIn = new Dictionary<string, string>
        {
            "confirm" = confirm
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }
}