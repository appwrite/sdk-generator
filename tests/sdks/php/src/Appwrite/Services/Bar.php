<?php

namespace Appwrite\Services;

use Exception;
use Appwrite\Client;
use Appwrite\Service;

class Bar extends Service
{
    /**
     * Mock a get request for SDK tests
     *
     * @param string $x
     * @param integer $y
     * @throws Exception
     * @return array
     */
    public function get($x, $y)
    {
        $path   = str_replace([], [], '/mock/tests/bar');
        $params = [];

        $params['x'] = $x;
        $params['y'] = $y;

        return $this->client->call(Client::METHOD_GET, $path, [
        ], $params);
    }

    /**
     * Mock a post request for SDK tests
     *
     * @param string $x
     * @param integer $y
     * @throws Exception
     * @return array
     */
    public function post($x, $y)
    {
        $path   = str_replace([], [], '/mock/tests/bar');
        $params = [];

        $params['x'] = $x;
        $params['y'] = $y;

        return $this->client->call(Client::METHOD_POST, $path, [
        ], $params);
    }

    /**
     * Mock a put request for SDK tests
     *
     * @param string $x
     * @param integer $y
     * @throws Exception
     * @return array
     */
    public function put($x, $y)
    {
        $path   = str_replace([], [], '/mock/tests/bar');
        $params = [];

        $params['x'] = $x;
        $params['y'] = $y;

        return $this->client->call(Client::METHOD_PUT, $path, [
        ], $params);
    }

    /**
     * Mock a patch request for SDK tests
     *
     * @param string $x
     * @param integer $y
     * @throws Exception
     * @return array
     */
    public function patch($x, $y)
    {
        $path   = str_replace([], [], '/mock/tests/bar');
        $params = [];

        $params['x'] = $x;
        $params['y'] = $y;

        return $this->client->call(Client::METHOD_PATCH, $path, [
        ], $params);
    }

    /**
     * Mock a delete request for SDK tests
     *
     * @param string $x
     * @param integer $y
     * @throws Exception
     * @return array
     */
    public function delete($x, $y)
    {
        $path   = str_replace([], [], '/mock/tests/bar');
        $params = [];

        $params['x'] = $x;
        $params['y'] = $y;

        return $this->client->call(Client::METHOD_DELETE, $path, [
        ], $params);
    }

}