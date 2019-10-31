using System.Collections.Generic;
using Service;

public class Avatars : Service
{
    public GetBrowser(string code, int width=100, int height=100, int quality=100)
    {
        path = $"/avatars/browsers/{code}";
        paramsIn = new Dictionary<string, string>
        {
            "width" = width,
            "height" = height,
            "quality" = quality
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }
    
    public GetCreditCard(string code, int width=100, int height=100, int quality=100)
    {
        path = $"/avatars/credit-cards/{code}";
        paramsIn = new Dictionary<string, string>
        {
            "width" = width,
            "height" = height,
            "quality" = quality
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetFavicon(string url)
    {
        path = $"/avatars/favicon";
        paramsIn = new Dictionary<string, string>
        {
            "url" = url,
            "height" = height,
            "quality" = quality
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetFlag(string code, int width = 100, int height = 100, int quality = 100)
    {
        path = $"/avatars/flags/{code}";
        paramsIn = new Dictionary<string, string>
        {
            "width" = width,
            "height" = height,
            "quality" = quality
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetImage(string url, int width = 400, int height = 400)
    {
        path = $"/avatars/image";
        paramsIn = new Dictionary<string, string>
        {
            "width" = width,
            "height" = height,
            "url" = url
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetQr(string text, int size = 400, int margin = 1, int download = 0)
    {
        path = $"/avatars/flags/{code}";
        paramsIn = new Dictionary<string, string>
        {
            "text" = text,
            "size" = size,
            "margin" = margin,
            "download" = download
        };

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }
}