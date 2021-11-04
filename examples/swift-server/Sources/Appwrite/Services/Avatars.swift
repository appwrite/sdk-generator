import AsyncHTTPClient
import Foundation
import NIO
import AppwriteModels

open class Avatars: Service {
    ///
    /// Get Browser Icon
    ///
    /// You can use this endpoint to show different browser icons to your users.
    /// The code argument receives the browser code as it appears in your user
    /// /account/sessions endpoint. Use width, height and quality arguments to
    /// change the output settings.
    ///
    /// @param String code
    /// @param Int width
    /// @param Int height
    /// @param Int quality
    /// @throws Exception
    /// @return array
    ///
    open func getBrowser(
        code: String,
        width: Int? = nil,
        height: Int? = nil,
        quality: Int? = nil,
        completion: ((Result<ByteBuffer, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/avatars/browsers/{code}"

        path = path.replacingOccurrences(
          of: "{code}",
          with: code        )

        let params: [String: Any?] = [
            "width": width,
            "height": height,
            "quality": quality,
            "project": client.config["project"],
            "key": client.config["key"]
        ]

        client.call(
            method: "GET",
            path: path,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Credit Card Icon
    ///
    /// The credit card endpoint will return you the icon of the credit card
    /// provider you need. Use width, height and quality arguments to change the
    /// output settings.
    ///
    /// @param String code
    /// @param Int width
    /// @param Int height
    /// @param Int quality
    /// @throws Exception
    /// @return array
    ///
    open func getCreditCard(
        code: String,
        width: Int? = nil,
        height: Int? = nil,
        quality: Int? = nil,
        completion: ((Result<ByteBuffer, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/avatars/credit-cards/{code}"

        path = path.replacingOccurrences(
          of: "{code}",
          with: code        )

        let params: [String: Any?] = [
            "width": width,
            "height": height,
            "quality": quality,
            "project": client.config["project"],
            "key": client.config["key"]
        ]

        client.call(
            method: "GET",
            path: path,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Favicon
    ///
    /// Use this endpoint to fetch the favorite icon (AKA favicon) of any remote
    /// website URL.
    /// 
    ///
    /// @param String url
    /// @throws Exception
    /// @return array
    ///
    open func getFavicon(
        url: String,
        completion: ((Result<ByteBuffer, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/avatars/favicon"

        let params: [String: Any?] = [
            "url": url,
            "project": client.config["project"],
            "key": client.config["key"]
        ]

        client.call(
            method: "GET",
            path: path,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Country Flag
    ///
    /// You can use this endpoint to show different country flags icons to your
    /// users. The code argument receives the 2 letter country code. Use width,
    /// height and quality arguments to change the output settings.
    ///
    /// @param String code
    /// @param Int width
    /// @param Int height
    /// @param Int quality
    /// @throws Exception
    /// @return array
    ///
    open func getFlag(
        code: String,
        width: Int? = nil,
        height: Int? = nil,
        quality: Int? = nil,
        completion: ((Result<ByteBuffer, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/avatars/flags/{code}"

        path = path.replacingOccurrences(
          of: "{code}",
          with: code        )

        let params: [String: Any?] = [
            "width": width,
            "height": height,
            "quality": quality,
            "project": client.config["project"],
            "key": client.config["key"]
        ]

        client.call(
            method: "GET",
            path: path,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Image from URL
    ///
    /// Use this endpoint to fetch a remote image URL and crop it to any image size
    /// you want. This endpoint is very useful if you need to crop and display
    /// remote images in your app or in case you want to make sure a 3rd party
    /// image is properly served using a TLS protocol.
    ///
    /// @param String url
    /// @param Int width
    /// @param Int height
    /// @throws Exception
    /// @return array
    ///
    open func getImage(
        url: String,
        width: Int? = nil,
        height: Int? = nil,
        completion: ((Result<ByteBuffer, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/avatars/image"

        let params: [String: Any?] = [
            "url": url,
            "width": width,
            "height": height,
            "project": client.config["project"],
            "key": client.config["key"]
        ]

        client.call(
            method: "GET",
            path: path,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get User Initials
    ///
    /// Use this endpoint to show your user initials avatar icon on your website or
    /// app. By default, this route will try to print your logged-in user name or
    /// email initials. You can also overwrite the user name if you pass the 'name'
    /// parameter. If no name is given and no user is logged, an empty avatar will
    /// be returned.
    /// 
    /// You can use the color and background params to change the avatar colors. By
    /// default, a random theme will be selected. The random theme will persist for
    /// the user's initials when reloading the same theme will always return for
    /// the same initials.
    ///
    /// @param String name
    /// @param Int width
    /// @param Int height
    /// @param String color
    /// @param String background
    /// @throws Exception
    /// @return array
    ///
    open func getInitials(
        name: String? = nil,
        width: Int? = nil,
        height: Int? = nil,
        color: String? = nil,
        background: String? = nil,
        completion: ((Result<ByteBuffer, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/avatars/initials"

        let params: [String: Any?] = [
            "name": name,
            "width": width,
            "height": height,
            "color": color,
            "background": background,
            "project": client.config["project"],
            "key": client.config["key"]
        ]

        client.call(
            method: "GET",
            path: path,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get QR Code
    ///
    /// Converts a given plain text to a QR code image. You can use the query
    /// parameters to change the size and style of the resulting image.
    ///
    /// @param String text
    /// @param Int size
    /// @param Int margin
    /// @param Bool download
    /// @throws Exception
    /// @return array
    ///
    open func getQR(
        text: String,
        size: Int? = nil,
        margin: Int? = nil,
        download: Bool? = nil,
        completion: ((Result<ByteBuffer, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/avatars/qr"

        let params: [String: Any?] = [
            "text": text,
            "size": size,
            "margin": margin,
            "download": download,
            "project": client.config["project"],
            "key": client.config["key"]
        ]

        client.call(
            method: "GET",
            path: path,
            params: params,
            completion: completion
        )
    }

}
