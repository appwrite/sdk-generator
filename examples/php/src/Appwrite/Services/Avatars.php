<?php

namespace Appwrite\Services;

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\Service;

class Avatars extends Service
{
    /**
     * Get Browser Icon
     *
     * You can use this endpoint to show different browser icons to your users.
     * The code argument receives the browser code as it appears in your user
     * /account/sessions endpoint. Use width, height and quality arguments to
     * change the output settings.
     *
     * @param string $code
     * @param int $width
     * @param int $height
     * @param int $quality
     * @throws AppwriteException
     * @return string
     */
    public function getBrowser(string $code, int $width = null, int $height = null, int $quality = null): string
    {
        if (!isset($code)) {
            throw new AppwriteException('Missing required parameter: "code"');
        }

        $path   = str_replace(['{code}'], [$code], '/avatars/browsers/{code}');
        $params = [];

        if (!is_null($width)) {
            $params['width'] = $width;
        }

        if (!is_null($height)) {
            $params['height'] = $height;
        }

        if (!is_null($quality)) {
            $params['quality'] = $quality;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get Credit Card Icon
     *
     * The credit card endpoint will return you the icon of the credit card
     * provider you need. Use width, height and quality arguments to change the
     * output settings.
     *
     * @param string $code
     * @param int $width
     * @param int $height
     * @param int $quality
     * @throws AppwriteException
     * @return string
     */
    public function getCreditCard(string $code, int $width = null, int $height = null, int $quality = null): string
    {
        if (!isset($code)) {
            throw new AppwriteException('Missing required parameter: "code"');
        }

        $path   = str_replace(['{code}'], [$code], '/avatars/credit-cards/{code}');
        $params = [];

        if (!is_null($width)) {
            $params['width'] = $width;
        }

        if (!is_null($height)) {
            $params['height'] = $height;
        }

        if (!is_null($quality)) {
            $params['quality'] = $quality;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get Favicon
     *
     * Use this endpoint to fetch the favorite icon (AKA favicon) of any remote
     * website URL.
     * 
     *
     * @param string $url
     * @throws AppwriteException
     * @return string
     */
    public function getFavicon(string $url): string
    {
        if (!isset($url)) {
            throw new AppwriteException('Missing required parameter: "url"');
        }

        $path   = str_replace([], [], '/avatars/favicon');
        $params = [];

        if (!is_null($url)) {
            $params['url'] = $url;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get Country Flag
     *
     * You can use this endpoint to show different country flags icons to your
     * users. The code argument receives the 2 letter country code. Use width,
     * height and quality arguments to change the output settings.
     *
     * @param string $code
     * @param int $width
     * @param int $height
     * @param int $quality
     * @throws AppwriteException
     * @return string
     */
    public function getFlag(string $code, int $width = null, int $height = null, int $quality = null): string
    {
        if (!isset($code)) {
            throw new AppwriteException('Missing required parameter: "code"');
        }

        $path   = str_replace(['{code}'], [$code], '/avatars/flags/{code}');
        $params = [];

        if (!is_null($width)) {
            $params['width'] = $width;
        }

        if (!is_null($height)) {
            $params['height'] = $height;
        }

        if (!is_null($quality)) {
            $params['quality'] = $quality;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get Image from URL
     *
     * Use this endpoint to fetch a remote image URL and crop it to any image size
     * you want. This endpoint is very useful if you need to crop and display
     * remote images in your app or in case you want to make sure a 3rd party
     * image is properly served using a TLS protocol.
     *
     * @param string $url
     * @param int $width
     * @param int $height
     * @throws AppwriteException
     * @return string
     */
    public function getImage(string $url, int $width = null, int $height = null): string
    {
        if (!isset($url)) {
            throw new AppwriteException('Missing required parameter: "url"');
        }

        $path   = str_replace([], [], '/avatars/image');
        $params = [];

        if (!is_null($url)) {
            $params['url'] = $url;
        }

        if (!is_null($width)) {
            $params['width'] = $width;
        }

        if (!is_null($height)) {
            $params['height'] = $height;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get User Initials
     *
     * Use this endpoint to show your user initials avatar icon on your website or
     * app. By default, this route will try to print your logged-in user name or
     * email initials. You can also overwrite the user name if you pass the 'name'
     * parameter. If no name is given and no user is logged, an empty avatar will
     * be returned.
     * 
     * You can use the color and background params to change the avatar colors. By
     * default, a random theme will be selected. The random theme will persist for
     * the user's initials when reloading the same theme will always return for
     * the same initials.
     *
     * @param string $name
     * @param int $width
     * @param int $height
     * @param string $color
     * @param string $background
     * @throws AppwriteException
     * @return string
     */
    public function getInitials(string $name = null, int $width = null, int $height = null, string $color = null, string $background = null): string
    {
        $path   = str_replace([], [], '/avatars/initials');
        $params = [];

        if (!is_null($name)) {
            $params['name'] = $name;
        }

        if (!is_null($width)) {
            $params['width'] = $width;
        }

        if (!is_null($height)) {
            $params['height'] = $height;
        }

        if (!is_null($color)) {
            $params['color'] = $color;
        }

        if (!is_null($background)) {
            $params['background'] = $background;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get QR Code
     *
     * Converts a given plain text to a QR code image. You can use the query
     * parameters to change the size and style of the resulting image.
     *
     * @param string $text
     * @param int $size
     * @param int $margin
     * @param bool $download
     * @throws AppwriteException
     * @return string
     */
    public function getQR(string $text, int $size = null, int $margin = null, bool $download = null): string
    {
        if (!isset($text)) {
            throw new AppwriteException('Missing required parameter: "text"');
        }

        $path   = str_replace([], [], '/avatars/qr');
        $params = [];

        if (!is_null($text)) {
            $params['text'] = $text;
        }

        if (!is_null($size)) {
            $params['size'] = $size;
        }

        if (!is_null($margin)) {
            $params['margin'] = $margin;
        }

        if (!is_null($download)) {
            $params['download'] = $download;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }
}
