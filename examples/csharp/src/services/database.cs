using System.Collections.Generic;
using Service;

public class Database : Service
{
    public ListCollections(string search = "", int limit = 25, int offset = 0, string orderType = "ASC")
    {
        paramsIn = new Dictionary<string, string>
        {
            "search" = search,
            "limit" = limit,
            "offset" = offset,
            "orderType" = orderType
        };
        path = $"/database";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreateCollections(string name = "", string[] read = "", string[] write = "", string[] rules = "")
    {
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "read" = read,
            "write" = write,
            "rules" = rules
        };
        path = $"/database";

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetCollections(string id)
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/database/{id}";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateCollections(string name = "", string[] read = "", string[] write = "", string[] rules = "")
    {
        paramsIn = new Dictionary<string, string>
        {
            "name" = name,
            "read" = read,
            "write" = write,
            "rules" = rules
        };
        path = $"/database";

        return _client.Call("put", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteCollections(string id)
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/database/{id}";

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public ListDocuments(string id = "", string[] filters = "", int offset = 0, int limit = 50, string orderField = "$uid", string orderType = "ASC", string orderCast = "string", int first = 0, int last = 0)
    {
        paramsIn = new Dictionary<string, string>
        {
            "filters" = filters,
            "offset" = offset,
            "limit" = limit,
            "order-field" = orderField,
            "order-type" = orderType,
            "order-cast" = orderCast,
            "search" = search,
            "first" = first,
            "last" = last
        };
        path = $"/database/{id}/documents";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreateDocument(string id, string data, string[] read = "", string[] write = "", string parentDocument = "", string parentProperty = "", string parentPropertyType = "assign")
    {
        paramsIn = new Dictionary<string, string>
        {
            "data" = data,
            "read" = read,
            "write" = write,
            "parentDocument" = parentDocument,
            "parentProperty" = parentProperty,
            "parentPropertyType" = parentPropertyType
        };
        path = $"/database/{id}/documents";

        return _client.Call("post", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetDocument(string collectionId, string documentId)
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/database/{collectionId}/documents/{documentId}";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public UpdateDocument(string collectionId, string documentId, string data, string[] read = "", string[] write = "")
    {
        paramsIn = new Dictionary<string, string>
        {
            "data" = data,
            "read" = read,
            "write" = write
        };
        path = $"/database/{collectionId}/documents/{documentId}";

        return _client.Call("patch", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteDocument(string collectionId, string documentId)
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/database/{collectionId}/documents/{documentId}";

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }
}