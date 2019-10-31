using Service;
using System.Collections.Generic;

public class Projects : Service
{
    public ListProjects()
    {
        path = $"/projects";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreateProject(string name, string teamId, string description = "", string logo = "", string url = "", string legalName = "", string legalCountry = "", string legalState = "", string legalCity = "", string legalAddress = "", string legalTaxId = "")
    {
        path = $"/projects";
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "teamId" = teamId,
            "description" = description,
            "logo" = logo,
            "url" = url,
            "legalName" = legalName,
            "legalCountry" = legalCountry,
            "legalState" = legalState,
            "legalCity" = legalCity,
            "legalAddress" = legalAddress,
            "legalTaxId" = legalTaxId
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetProject(string projectId)
    {
        path = $"/projects/{projectId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateProject(string projectId, string name, string description = "", string logo = "", string url = "", string legalName = "", string legalCountry = "", string legalState = "", string legalCity = "", string legalAddress = "", string legalTaxId = "")
    {
        path = $"/projects/{projectId}";
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "teamId" = teamId,
            "description" = description,
            "logo" = logo,
            "url" = url,
            "legalName" = legalName,
            "legalCountry" = legalCountry,
            "legalState" = legalState,
            "legalCity" = legalCity,
            "legalAddress" = legalAddress,
            "legalTaxId" = legalTaxId
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteProject(string projectId)
    {
        path = $"/projects/{projectId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public ListKeys(string projectId)
    {
        path = $"/projects/{projectId}/keys";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetKey(string projectId, string keyId)
    {
        path = $"/projects/{projectId}/keys/{keyId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }
    
    public CreateKey(string projectId, string name, string scopes)
    {
        path = $"/projects/{projectId}/keys";
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "scopes" = scopes
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateKey(string projectId, string keyId, string name, string scopes)
    {
        path = $"/projects/{projectId}/keys/{keyId}";
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "scopes" = scopes
        };

        return _client.Call("put", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteKey(string projectId, string keyId)
    {
        path = $"/projects/{projectId}/keys/{keyId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateProjectOAuth(string projectId, string provider, string appId, string secret)
    {
        path = $"/projects/{projectId}/oauth";
        paramsIn = new Dictionary<string, string>
        {
            "provider" = provider,
            "appId" = appId,
            "secret" = secret
        };

        return _client.Call("patch", path, new Dictionary<string, string>(), paramsIn);
    }

    public ListPlatforms(string projectId)
    {
        path = $"/projects/{projectId}/platforms";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetPlatform(string projectId, string platfomId)
    {
        path = $"/projects/{projectId}/platforms/{platformId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreatePlatform(string projectId, string type, string name, string key = "", string store = "", string url = "")
    {
        path = $"/projects/{projectId}/platforms";
        paramsIn = new Dictionary<string, string>
        {
            "type" = type,
            "name" = name,
            "key" = key,
            "store" = store,
            "url" = url
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreatePlatform(string projectId, string platformId, string name, string key = "", string store = "", string url = "")
    {
        path = $"/projects/{projectId}/platforms/{platformId}";
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "key" = key,
            "store" = store,
            "url" = url
        };

        return _client.Call("put", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeletePlatform(string projectId, string platfomId)
    {
        path = $"/projects/{projectId}/platforms/{platformId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public ListTasks(string projectId)
    {
        path = $"/projects/{projectId}/tasks";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetTask(string projectId, string taskId)
    {
        path = $"/projects/{projectId}/tasks/{taskId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreateTask(string projectId, string name, string status, string schedule, string security, string httpMethod, string httpUrl, Dictionary httpHeaders = new Dictionary(), string httpUser = "", string httpPass = "")
    {
        path = $"/projects/{projectId}/tasks";
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "status" = status,
            "schedule" = schedule,
            "security" = security,
            "httpMethod" = httpMethod,
            "httpUrl" = httpUrl,
            "httpHeaders" = httpHeaders,
            "httpUser" = httpUser,
            "httpPass" = httpUser
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateTask(string projectId, string taskId, string name, string status, string schedule, string security, string httpMethod, string httpUrl, Dictionary httpHeaders = new Dictionary(), string httpUser = "", string httpPass = "")
    {
        path = $"/projects/{projectId}/tasks/{taskId}";
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "status" = status,
            "schedule" = schedule,
            "security" = security,
            "httpMethod" = httpMethod,
            "httpUrl" = httpUrl,
            "httpHeaders" = httpHeaders,
            "httpUser" = httpUser,
            "httpPass" = httpUser
        };

        return _client.Call("put", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteTask(string projectId, string taskId)
    {
        path = $"/projects/{projectId}/tasks/{taskId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetProjectUsage(string projectId)
    {
        path = $"/projects/{projectId}/usage";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public ListWebhooks(string projectId)
    {
        path = $"/projects/{projectId}/webhooks";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetWebhook(string projectId, string webhookId)
    {
        path = $"/projects/{projectId}/webhooks/{webhookId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreateWebhook(string projectId, string name, string events, string url, string security, string httpUser = "", string httpPass = "")
    {
        path = $"/projects/{projectId}/webhooks";
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "events" = events,
            "url" = url,
            "security" = security,
            "httpUser" = httpUser,
            "httpPass" = httpPass
        };

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateWebhook(string projectId, string webhookId, string name, string events, string url, string security, string httpUser = "", string httpPass = "")
    {
        path = $"/projects/{projectId}/webhooks/{webhookId}";
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "events" = events,
            "url" = url,
            "security" = security,
            "httpUser" = httpUser,
            "httpPass" = httpPass
        };

        return _client.Call("put", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteWebhook(string projectId, string webhookId)
    {
        path = $"/projects/{projectId}/webhooks/{webhookId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }
}