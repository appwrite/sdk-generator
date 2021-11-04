<?php

namespace Appwrite;

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

    /**
     * Is Self Signed Certificates Allowed?
     *
     * @var bool
     */
    protected $selfSigned = false;

    /**
     * Service host name
     *
     * @var string
     */
    protected $endpoint = 'https://appwrite.io/v1';

    /**
     * Global Headers
     *
     * @var array
     */
    protected $headers = [
        'content-type' => '',
        'x-sdk-version' => 'appwrite:php:',
    ];

    /**
     * SDK constructor.
     */
    public function __construct()
    {
        $this->headers['X-Appwrite-Response-Format'] = '0.7.0';
 
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
     * Set JWT
     *
     * Your secret JSON Web Token
     *
     * @param string $value
     *
     * @return Client
     */
    public function setJWT($value)
    {
        $this->addHeader('X-Appwrite-JWT', $value);

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


    /***
     * @param bool $status
     * @return $this
     */
    public function setSelfSigned($status = true)
    {
        $this->selfSigned = $status;

        return $this;
    }

    /***
     * @param $endpoint
     * @return $this
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

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
     * @throws AppwriteException
     */
    public function call($method, $path = '', $headers = array(), array $params = array())
    {
        $headers            = array_merge($this->headers, $headers);
        $ch                 = curl_init($this->endpoint . $path . (($method == self::METHOD_GET && !empty($params)) ? '?' . http_build_query($params) : ''));
        $responseHeaders    = [];
        $responseStatus     = -1;
        $responseType       = '';
        $responseBody       = '';

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
        curl_setopt($ch, CURLOPT_USERAGENT, php_uname('s') . '-' . php_uname('r') . ':php-' . phpversion());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $header = explode(':', strtolower($header), 2);

            if (count($header) < 2) { // ignore invalid headers
                return $len;
            }

            $responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);

            return $len;
        });

        if($method != self::METHOD_GET) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        }

        // Allow self signed certificates
        if($this->selfSigned) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $responseBody   = curl_exec($ch);
        $responseType   = $responseHeaders['content-type'] ?? '';
        $responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        switch(substr($responseType, 0, strpos($responseType, ';'))) {
            case 'application/json':
                $responseBody = json_decode($responseBody, true);
            break;
        }

        if (curl_errno($ch)) {
            throw new AppwriteException(curl_error($ch), $responseStatus, $responseBody);
        }
        
        curl_close($ch);

        if($responseStatus >= 400) {
            if(is_array($responseBody)) {
                throw new AppwriteException($responseBody['message'], $responseStatus, $responseBody);
            } else {
                throw new AppwriteException($responseBody, $responseStatus);
            }
        }


        return $responseBody;
    }

    /**
     * Flatten params array to PHP multiple format
     *
     * @param array $data
     * @param string $prefix
     * @return array
     */
    protected function flatten(array $data, $prefix = '') {
        $output = [];

        foreach($data as $key => $value) {
            $finalKey = $prefix ? "{$prefix}[{$key}]" : $key;

            if (is_array($value)) {
                $output += $this->flatten($value, $finalKey); // @todo: handle name collision here if needed
            }
            else {
                $output[$finalKey] = $value;
            }
        }

        return $output;
    }
}
