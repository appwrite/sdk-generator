using System.Collections.Generic;
using Service;

public class Locale : Service
{

    public GetLocale()
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/locale";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetCurrencies()
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/locale/currencies";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetCountries()
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/locale/countries";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetCountriesEu()
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/locale/countries/eu";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }

    public GetCountriesPhones()
    {
        paramsIn = new Dictionary<string, string>();
        path = $"/locale/countries/phones";

        return _client.Call("get", path, new Dictionary<string, string>(), paramsIn);
    }
}