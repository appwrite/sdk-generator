using Service;
using System.Collections.Generic;

public class Storage : Service
{
    public ListFiles(string search = "", int limit = 25, int offset = 0, string orderType = "ASC")
    {
        path = $"/storage/files";
        paramsIn = new Dictionary<string, string>
        {
            "search" = search,
            "limit" = limit,
            "offset" = offset,
            "orderType" = orderType
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetFile(string id)
    {
        path = $"/storage/files/{id}";
        paramsIn = new Dictionary<string, string>
        {
            "search" = search,
            "limit" = limit,
            "offset" = offset,
            "orderType" = orderType
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public CreateFile(string files, string[] read, string[] write, string folder = "")
    {
        path = $"/storage/files";
        paramsIn = new Dictionary<string, string>
        {
            "files" = files,
            "read" = read,
            "write" = write,
            "folderId" = folder
        };

        return _client.Call("put", path, new Dictionary<string, string>{"content-type" = "multipart/form-data"}, paramsIn);
    }

    public UpdateFile(string fileId, string[] read, string[] write, string folderId = "")
    {
        path = $"/storage/files/{fileId}";
        paramsIn = new Dictionary<string, string>
        {
            "read" = read,
            "write" = write,
            "folderId" = folderId
        };

        return _client.Call("put", path, new Dictionary<string, string>(), paramsIn);
    }

    public DeleteFile(string fileId)
    {
        path = $"/storage/files/{fileId}";
        paramsIn = new Dictionary<string, string>();

        return _client.Call("delete", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetFileDownload(string id)
    {
        path = $"/storage/files/{id}/download";
        paramsIn = new Dictionary<string, string>
        {
            "search" = search,
            "limit" = limit,
            "offset" = offset,
            "orderType" = orderType
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetFilePreview(string id, int width = 0, int height = 0, int quality = 100, string background = "", string output = "")
    {
        path = $"/storage/files/{id}/preview";
        paramsIn = new Dictionary<string, string>
        {
            "width" = width,
            "height" = height,
            "quality" = quality,
            "background" = background,
            "output" = output
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetFileView(string id, string asIn = "")
    {
        path = $"/storage/files/{id}/view";
        paramsIn = new Dictionary<string, string>
        {
            "as" = asIn
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }
}