package io.appwrite.cookies

import okhttp3.Cookie
import okhttp3.CookieJar
import okhttp3.HttpUrl
import okhttp3.internal.cookieToString
import okhttp3.internal.delimiterOffset
import okhttp3.internal.platform.Platform
import okhttp3.internal.trimSubstring
import java.io.IOException
import java.net.CookieHandler
import java.net.HttpCookie
import java.util.Collections

typealias CookieListener = (existing: List<Cookie>, new: List<Cookie>) -> Unit

class ListenableCookieJar(private val cookieHandler: CookieHandler) : CookieJar {

    private val listeners: MutableMap<Int, CookieListener> = mutableMapOf()

    fun onSave(key: String, listener: CookieListener) {
        listeners[key.hashCode()] = listener
    }

    override fun saveFromResponse(url: HttpUrl, cookies: List<Cookie>) {
        val existingCookies = loadForRequest(url)

        listeners.values.forEach { it(existingCookies, cookies) }

        val cookieStrings = mutableListOf<String>()
        for (cookie in cookies) {
            cookieStrings.add(cookieToString(cookie, true))
        }
        val multimap = mapOf("Set-Cookie" to cookieStrings)
        try {
            cookieHandler.put(url.toUri(), multimap)
        } catch (e: IOException) {
            Platform.get().log(
                "Saving cookies failed for " + url.resolve("/...")!!,
                Platform.WARN, e
            )
        }
    }

    override fun loadForRequest(url: HttpUrl): List<Cookie> {
        val cookieHeaders = try {
            cookieHandler.get(url.toUri(), emptyMap<String, List<String>>())
        } catch (e: IOException) {
            Platform.get().log(
                "Loading cookies failed for " + url.resolve("/...")!!,
                Platform.WARN, e
            )
            return emptyList()
        }

        var cookies: MutableList<Cookie>? = null
        for ((key, value) in cookieHeaders) {
            if (("Cookie".equals(key, ignoreCase = true) || "Cookie2".equals(
                    key,
                    ignoreCase = true
                )) &&
                value.isNotEmpty()
            ) {
                for (header in value) {
                    if (cookies == null) cookies = mutableListOf()
                    cookies.addAll(decodeHeaderAsJavaNetCookies(url, header))
                }
            }
        }

        return if (cookies != null) {
            Collections.unmodifiableList(cookies)
        } else {
            emptyList()
        }
    }

    /**
     * Convert a request header to OkHttp's cookies via [HttpCookie]. That extra step handles
     * multiple cookies in a single request header, which [Cookie.parse] doesn't support.
     */
    private fun decodeHeaderAsJavaNetCookies(url: HttpUrl, header: String): List<Cookie> {
        val result = mutableListOf<Cookie>()
        var pos = 0
        val limit = header.length
        var pairEnd: Int
        while (pos < limit) {
            pairEnd = header.delimiterOffset(";,", pos, limit)
            val equalsSign = header.delimiterOffset('=', pos, pairEnd)
            val name = header.trimSubstring(pos, equalsSign)
            if (name.startsWith("$")) {
                pos = pairEnd + 1
                continue
            }

            // We have either name=value or just a name.
            var value = if (equalsSign < pairEnd) {
                header.trimSubstring(equalsSign + 1, pairEnd)
            } else {
                ""
            }

            // If the value is "quoted", drop the quotes.
            if (value.startsWith("\"") && value.endsWith("\"")) {
                value = value.substring(1, value.length - 1)
            }

            result.add(
                Cookie.Builder()
                    .name(name)
                    .value(value)
                    .domain(url.host)
                    .build()
            )
            pos = pairEnd + 1
        }
        return result
    }
}