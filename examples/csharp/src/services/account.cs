using System.Collections.Generic;
using Service;

public class Account : Service
{
    public Get()
    {
        paramsIn = new Dictionary<string, string>();
        path = "/account";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public Delete()
    {
        paramsIn = new Dictionary<string, string>();
        path = "/account";

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateEmail(string email, string password)
    {
        path = "/account/email";
        paramsIn = new Dictionary<string, string>
        {
            "email" = email,
            "password" = password
        };
        
        return _client.Call("patch", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateName(string name)
    {
        path = "/account/name";
        paramsIn = new Dictionary<string, string>
        {
            "name" = name
        };
        
        return _client.Call("patch", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateName(string password, string oldPassword)
    {
        path = "/account/password";
        paramsIn = new Dictionary<string, string>
        {
            "password" = password,
            "old-password" = oldPassword
        };

        return _client.Call("patch", path, new Dictionary<string, string>(), paramsIn);
    }
    
    public GetPrefs()
    {
        path = "/account/prefs";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdatePrefs(string prefs)
    {
        path = "/account/prefs";
        paramsIn = new Dictionary<string, string>
        {
            "prefs" = prefs
        };

        return _client.Call("patch", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetSecurity()
    {
        path = "/account/security";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetSessions()
    {
        path = "/account/sessions";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }
}