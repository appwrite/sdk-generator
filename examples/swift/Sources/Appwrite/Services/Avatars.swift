

class Avatars: Service
{
    /**
     * Get Browser Icon
     *
     * You can use this endpoint to show different browser icons to your users.
     * The code argument receives the browser code as it appears in your user
     * /account/sessions endpoint. Use width, height and quality arguments to
     * change the output settings.
     *
     * @param String code
     * @param Int width
     * @param Int height
     * @param Int quality
     * @throws Exception
     * @return array
     */

    func getBrowser(code: String, width: Int = 100, height: Int = 100, quality: Int = 100)-> Array<Any> {
        var path: String = "/avatars/browsers/{code}"

        path = path.replacingOccurrences(
          of: "{code}",
          with: code
        )

                var params: [String: Any] = [:]
        
        params["width"] = width
        params["height"] = height
        params["quality"] = quality

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Credit Card Icon
     *
     * Need to display your users with your billing method or their payment
     * methods? The credit card endpoint will return you the icon of the credit
     * card provider you need. Use width, height and quality arguments to change
     * the output settings.
     *
     * @param String code
     * @param Int width
     * @param Int height
     * @param Int quality
     * @throws Exception
     * @return array
     */

    func getCreditCard(code: String, width: Int = 100, height: Int = 100, quality: Int = 100)-> Array<Any> {
        var path: String = "/avatars/credit-cards/{code}"

        path = path.replacingOccurrences(
          of: "{code}",
          with: code
        )

                var params: [String: Any] = [:]
        
        params["width"] = width
        params["height"] = height
        params["quality"] = quality

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Favicon
     *
     * Use this endpoint to fetch the favorite icon (AKA favicon) of a  any remote
     * website URL.
     *
     * @param String url
     * @throws Exception
     * @return array
     */

    func getFavicon(url: String)-> Array<Any> {
        let path: String = "/avatars/favicon"


                var params: [String: Any] = [:]
        
        params["url"] = url

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Country Flag
     *
     * You can use this endpoint to show different country flags icons to your
     * users. The code argument receives the 2 letter country code. Use width,
     * height and quality arguments to change the output settings.
     *
     * @param String code
     * @param Int width
     * @param Int height
     * @param Int quality
     * @throws Exception
     * @return array
     */

    func getFlag(code: String, width: Int = 100, height: Int = 100, quality: Int = 100)-> Array<Any> {
        var path: String = "/avatars/flags/{code}"

        path = path.replacingOccurrences(
          of: "{code}",
          with: code
        )

                var params: [String: Any] = [:]
        
        params["width"] = width
        params["height"] = height
        params["quality"] = quality

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Image from URL
     *
     * Use this endpoint to fetch a remote image URL and crop it to any image size
     * you want. This endpoint is very useful if you need to crop and display
     * remote images in your app or in case you want to make sure a 3rd party
     * image is properly served using a TLS protocol.
     *
     * @param String url
     * @param Int width
     * @param Int height
     * @throws Exception
     * @return array
     */

    func getImage(url: String, width: Int = 400, height: Int = 400)-> Array<Any> {
        let path: String = "/avatars/image"


                var params: [String: Any] = [:]
        
        params["url"] = url
        params["width"] = width
        params["height"] = height

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get QR Code
     *
     * Converts a given plain text to a QR code image. You can use the query
     * parameters to change the size and style of the resulting image.
     *
     * @param String text
     * @param Int size
     * @param Int margin
     * @param Int download
     * @throws Exception
     * @return array
     */

    func getQR(text: String, size: Int = 400, margin: Int = 1, download: Int = 0)-> Array<Any> {
        let path: String = "/avatars/qr"


                var params: [String: Any] = [:]
        
        params["text"] = text
        params["size"] = size
        params["margin"] = margin
        params["download"] = download

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}
