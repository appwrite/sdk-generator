package io.appwrite.cookies

import android.util.Log
import okhttp3.Cookie
import okhttp3.CookieJar
import okhttp3.HttpUrl
import java.io.IOException
import java.net.CookieHandler
import java.text.SimpleDateFormat
import java.util.Collections
import java.util.Date
import java.util.Locale
import java.util.TimeZone

typealias CookieListener = (existing: List<Cookie>, new: List<Cookie>) -> Unit

class ListenableCookieJar(private val cookieHandler: CookieHandler) : CookieJar {

    private companion object {
        private const val TAG = "ListenableCookieJar"
    }

    private val listeners: MutableMap<Int, CookieListener> = mutableMapOf()

    fun onSave(key: String, listener: CookieListener) {
        listeners[key.hashCode()] = listener
    }

    override fun saveFromResponse(url: HttpUrl, cookies: List<Cookie>) {
        val existingCookies = loadForRequest(url)

        listeners.values.forEach { it(existingCookies, cookies) }

        val cookieStrings = mutableListOf<String>()
        for (cookie in cookies) {
            cookieStrings.add(cookieToString(cookie))
        }
        val multimap = mapOf("Set-Cookie" to cookieStrings)
        try {
            cookieHandler.put(url.toUri(), multimap)
        } catch (e: IOException) {
            Log.w(TAG, "Saving cookies failed for " + url.resolve("/..."), e)
        }
    }

    override fun loadForRequest(url: HttpUrl): List<Cookie> {
        val cookieHeaders = try {
            cookieHandler.get(url.toUri(), emptyMap<String, List<String>>())
        } catch (e: IOException) {
            Log.w(TAG, "Loading cookies failed for " + url.resolve("/..."), e)
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

            var value = if (equalsSign < pairEnd) {
                header.trimSubstring(equalsSign + 1, pairEnd)
            } else {
                ""
            }

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

    private fun cookieToString(cookie: Cookie): String = buildString {
        append(cookie.name)
        append('=')
        append(cookie.value)

        if (cookie.persistent) {
            if (cookie.expiresAt == Long.MIN_VALUE) {
                append("; Max-Age=0")
            } else {
                append("; Expires=").append(formatHttpDate(cookie.expiresAt))
            }
        }

        if (!cookie.hostOnly) {
            // Leading "." marks the cookie as subdomain-matching per RFC 2965, which java.net.CookieHandler still relies on.
            append("; Domain=.").append(cookie.domain)
        }

        append("; Path=").append(cookie.path)

        if (cookie.secure) append("; Secure")
        if (cookie.httpOnly) append("; HttpOnly")
    }

    private fun formatHttpDate(epochMillis: Long): String =
        SimpleDateFormat("EEE, dd MMM yyyy HH:mm:ss 'GMT'", Locale.US).apply {
            timeZone = TimeZone.getTimeZone("GMT")
        }.format(Date(epochMillis))

    private fun String.delimiterOffset(delimiters: String, startIndex: Int, endIndex: Int): Int {
        for (i in startIndex until endIndex) {
            if (this[i] in delimiters) return i
        }
        return endIndex
    }

    private fun String.delimiterOffset(delimiter: Char, startIndex: Int, endIndex: Int): Int {
        for (i in startIndex until endIndex) {
            if (this[i] == delimiter) return i
        }
        return endIndex
    }

    private fun String.trimSubstring(startIndex: Int, endIndex: Int): String {
        var start = startIndex
        var end = endIndex
        while (start < end && this[start].isWhitespace()) start++
        while (end > start && this[end - 1].isWhitespace()) end--
        return substring(start, end)
    }
}
