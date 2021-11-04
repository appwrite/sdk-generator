<?php

namespace Appwrite;

use Exception;
use Utopia\CLI\Console;

class Client
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_TRACE = 'TRACE';

    const USER_PREFERENCES_FILE = __DIR__."/../app/.preferences/.prefs.json";
    const PREFERENCE_ENDPOINT = "endpoint";
    const PREFERENCE_SELF_SIGNED = "selfSigned";
    const SELF_SIGNED_CERTIFICATE_ERROR_CODE = 18;

    /**
     * Global Headers
     *
     * @var array
     */
    protected $headers = [
        'content-type' => '',
        'x-sdk-version' => 'appwrite:cli:0.0.1',
    ];

    /**
     * Default User Preferences
     *
     * @var array
     */
    protected $preferences = [
        self::PREFERENCE_ENDPOINT => '',
        self::PREFERENCE_SELF_SIGNED => '',
        'X-Appwrite-Project' => '',
        'X-Appwrite-Key' => '',
        'X-Appwrite-Locale' => '',
    ];

    /**
     * Getter for preferences
     *
     * @param string $key
     * @return string
     */
    public function getPreference(string $key): string
    {
        return $this->preferences[$key] ?? '';
    }

    /**
     * Setter for preferences
     *
     * @param string $key
     * @param string $value
     */
    public function setPreference(string $key , string $value) 
    {
        $this->preferences[$key] = $value;
        
        return $this;
    }

    /**
     * Load user preferences from the JSON file
     * 
     * @param string $filename 
     * @return bool
     */
    protected function loadPreferences(string $filename = self::USER_PREFERENCES_FILE): bool
    {
        try {
            $jsondata = @file_get_contents($filename);
            if($jsondata === false || empty($jsondata)) {
                return false;
            }

            $arr_data = json_decode($jsondata, true);
            $this->preferences = array_replace($this->preferences, $arr_data);
            if (!$this->isPreferenceLoaded()) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Function to write user preferences to
     * the JSON file
     * 
     * @param string $filename 
     * @return int
     */
    function savePreferences(string $filename = self::USER_PREFERENCES_FILE): int
    {
        $jsondata = json_encode($this->preferences, JSON_PRETTY_PRINT);
        $result = file_put_contents($filename, $jsondata);
        return $result;
    }

    /**
     * Check if all the preferences have been successfully loaded.
     * 
     * @return bool
     */
    protected function isPreferenceLoaded() : bool {
        if(empty($this->getPreference(self::PREFERENCE_ENDPOINT))) return false;
        if(empty($this->getPreference('X-Appwrite-Project'))) return false;
        if(empty($this->getPreference('X-Appwrite-Key'))) return false;
        if(empty($this->getPreference('X-Appwrite-Locale'))) return false;
        return true;
    }

    /**
     * Set Project
     *
     * Your project ID
     *
     * @param string $value
     *
     * @return Client
     */
    public function setProject($value)
    {
        $this->addHeader('X-Appwrite-Project', $value);
        return $this;
    }

    /**
     * Set Key
     *
     * Your secret API key
     *
     * @param string $value
     *
     * @return Client
     */
    public function setKey($value)
    {
        $this->addHeader('X-Appwrite-Key', $value);
        return $this;
    }

    /**
     * Set Locale
     *
     * @param string $value
     *
     * @return Client
     */
    public function setLocale($value)
    {
        $this->addHeader('X-Appwrite-Locale', $value);
        return $this;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addHeader($key, $value)
    {
        $this->headers[strtolower($key)] = $value;
        
        return $this;
    }

    /**
     * Call
     *
     * Make an API call
     *
     * @param string $method
     * @param string $path
     * @param array $params
     * @param array $headers
     * @return array|string
     * @throws Exception
     */
    public function call($method, $path = '', $headers = array(), array $params = array())
    {

        if (!$this->loadPreferences()) {
            Console::error("❌ Oops We were unable to load your preferences. Ensure that you have run 'appwrite init' before using the CLI");
            Console::exit();
        }
        
        $this
            ->setProject($this->preferences['X-Appwrite-Project'])
            ->setKey($this->preferences['X-Appwrite-Key'])
            ->setLocale($this->preferences['X-Appwrite-Locale'])
        ;
        
        $headers            = array_merge($this->headers, $headers);
        $ch                 = curl_init($this->getPreference(self::PREFERENCE_ENDPOINT) . $path . (($method == self::METHOD_GET && !empty($params)) ? '?' . http_build_query($params) : ''));
        $responseHeaders    = [];
        $responseStatus     = -1;
        $responseType       = '';
        $responseBody       = '';

        $params = array_map(function ($param) {
            if (is_string($param)) {
                $param = \urldecode($param);
            }
            return $param;
        }, $params);

        switch ($headers['content-type']) {
            case 'application/json':
                $query = json_encode($params);
                break;

            case 'multipart/form-data':
                $query = $this->flatten($params);
                break;

            default:
                $query = http_build_query($params);
                break;
        }

        foreach ($headers as $i => $header) {
            $headers[] = $i . ':' . $header;
            unset($headers[$i]);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, php_uname('s') . '-' . php_uname('r') . ':cli-' . phpversion());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $header = explode(':', strtolower($header), 2);

            if (count($header) < 2) { // ignore invalid headers
                return $len;
            }

            $responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);

            return $len;
        });

        if ($method != self::METHOD_GET) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        }

        /**
        * Allow self-signed certificates
        * Default to false if no preference is found
        */
        $selfSigned = $this->getPreference(self::PREFERENCE_SELF_SIGNED) === 'true';
        if ($selfSigned) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $responseBody   = curl_exec($ch);
        $responseType   = (isset($responseHeaders['content-type'])) ? $responseHeaders['content-type'] : '';
        $responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        switch (substr($responseType, 0, strpos($responseType, ';'))) {
            case 'application/json':
                $responseBody = json_decode($responseBody, true);
                break;
            default:
                $responseBody = [
                    "responseCode" => $responseStatus,
                    "message" => $responseBody
                ];
                break;
        }

        if ((curl_errno($ch)/* || 200 != $responseStatus*/)) {
            // Self signed certificate error
            if(curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT) === self::SELF_SIGNED_CERTIFICATE_ERROR_CODE) {
                throw new Exception("Your server is using a self signed certificate. If you trust this domain, disable certificate check by running `appwrite client setSelfSigned --value=true`", $responseStatus);
            } else {
                 throw new Exception(curl_error($ch) . ' with status code ' . $responseStatus, $responseStatus);
            }
        }

        curl_close($ch);

        return $responseBody;
    }

    /**
     * Flatten params array to PHP multiple format
     *
     * @param array $data
     * @param string $prefix
     * @return array
     */
    protected function flatten(array $data, $prefix = '')
    {
        $output = [];

        foreach ($data as $key => $value) {
            $finalKey = $prefix ? "{$prefix}[{$key}]" : $key;

            if (is_array($value)) {
                $output += $this->flatten($value, $finalKey); // @todo: handle name collision here if needed
            } else {
                $output[$finalKey] = $value;
            }
        }

        return $output;
    }
}
