package io.appwrite

import com.google.gson.Gson
import io.appwrite.exceptions.AppwriteException
import io.appwrite.extensions.JsonExtensions.fromJson
import io.appwrite.models.Error
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.Job
import kotlinx.coroutines.suspendCancellableCoroutine
import okhttp3.*
import okhttp3.Headers.Companion.toHeaders
import okhttp3.HttpUrl.Companion.toHttpUrl
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import okhttp3.logging.HttpLoggingInterceptor
import java.io.BufferedReader
import java.io.File
import java.io.IOException
import java.security.SecureRandom
import java.security.cert.X509Certificate
import javax.net.ssl.HostnameVerifier
import javax.net.ssl.SSLContext
import javax.net.ssl.SSLSocketFactory
import javax.net.ssl.TrustManager
import javax.net.ssl.X509TrustManager
import kotlin.coroutines.CoroutineContext
import kotlin.coroutines.resume

class Client @JvmOverloads constructor(
    var endPoint: String = "https://appwrite.io/v1",
    private var selfSigned: Boolean = false
) : CoroutineScope {

    override val coroutineContext: CoroutineContext
        get() = Dispatchers.Main + job

    private val job = Job()

    private lateinit var http: OkHttpClient

    private val headers: MutableMap<String, String>
    
    val config: MutableMap<String, String>

    init {
        headers = mutableMapOf(
            "content-type" to "application/json",
            "x-sdk-version" to "appwrite:kotlin:0.0.0-SNAPSHOT",            
            "x-appwrite-response-format" to "0.8.0"
        )
        config = mutableMapOf()
        
        setSelfSigned(selfSigned)
    }

    /**
     * Set Project
     *
     * Your project ID
     *
     * @param {string} project
     *
     * @return this
     */
    fun setProject(value: String): Client {
        config["project"] = value
        addHeader("x-appwrite-project", value)
        return this
    }

    /**
     * Set Key
     *
     * Your secret API key
     *
     * @param {string} key
     *
     * @return this
     */
    fun setKey(value: String): Client {
        config["key"] = value
        addHeader("x-appwrite-key", value)
        return this
    }

    /**
     * Set JWT
     *
     * Your secret JSON Web Token
     *
     * @param {string} jwt
     *
     * @return this
     */
    fun setJWT(value: String): Client {
        config["jWT"] = value
        addHeader("x-appwrite-jwt", value)
        return this
    }

    /**
     * Set Locale
     *
     * @param {string} locale
     *
     * @return this
     */
    fun setLocale(value: String): Client {
        config["locale"] = value
        addHeader("x-appwrite-locale", value)
        return this
    }

    /**
     * Set self Signed
     * 
     * @param status
     *
     * @return this     
     */
    fun setSelfSigned(status: Boolean): Client {
        selfSigned = status

        val builder = OkHttpClient()
            .newBuilder()

        if (!selfSigned) {
            http = builder.build()
            return this
        }

        try {
            // Create a trust manager that does not validate certificate chains
            val trustAllCerts = arrayOf<TrustManager>(
                object : X509TrustManager {
                    override fun checkClientTrusted(chain: Array<X509Certificate>, authType: String) {
                    }
                    override fun checkServerTrusted(chain: Array<X509Certificate>, authType: String) {
                    }
                    override fun getAcceptedIssuers(): Array<X509Certificate> {
                        return arrayOf()
                    }
                }
            )
            // Install the all-trusting trust manager
            val sslContext = SSLContext.getInstance("SSL")
            sslContext.init(null, trustAllCerts, SecureRandom())

            // Create an ssl socket factory with our all-trusting manager
            val sslSocketFactory: SSLSocketFactory = sslContext.socketFactory
            builder.sslSocketFactory(sslSocketFactory, trustAllCerts[0] as X509TrustManager)
            builder.hostnameVerifier(HostnameVerifier { _, _ -> true })

            http = builder.build()
        } catch (e: Exception) {
            throw RuntimeException(e)
        }

        return this
    }

    /**
    * Set endpoint.
    *
    * @param endpoint
    *
    * @return this
    */
    fun setEndpoint(endPoint: String): Client {
        this.endPoint = endPoint
        return this
    }

    /**
     * Add Header
     * 
     * @param key
     * @param value
     *
     * @return this     
     */
    fun addHeader(key: String, value: String): Client {
        headers[key] = value
        return this
    }

    /**
     * Send the HTTP request
     * 
     * @param method
     * @param path
     * @param headers
     * @param params
     *
     * @return [Response]    
     */
    @Throws(AppwriteException::class)
    suspend fun call(
        method: String, 
        path: String, 
        headers:  Map<String, String> = mapOf(), 
        params: Map<String, Any?> = mapOf()
    ): Response {

        val filteredParams = params.filterValues { it != null }

        val requestHeaders = this.headers.toHeaders().newBuilder()
            .addAll(headers.toHeaders())
            .build()

        val httpBuilder = (endPoint + path).toHttpUrl().newBuilder()

        if ("GET" == method) {
            filteredParams.forEach {
                when (it.value) {
                    null -> {
                        return@forEach
                    }
                    is List<*> -> {
                        val list = it.value as List<*>
                        for (index in list.indices) {
                            httpBuilder.addQueryParameter(
                                "${it.key}[]",
                                list[index].toString()
                            )
                        }
                    }
                    else -> {
                        httpBuilder.addQueryParameter(it.key, it.value.toString())
                    }
                }
            }
            val request = Request.Builder()
                .url(httpBuilder.build())
                .headers(requestHeaders)
                .get()
                .build()

            return awaitResponse(request)
        }

        val body = if (MultipartBody.FORM.toString() == headers["content-type"]) {
            val builder = MultipartBody.Builder().setType(MultipartBody.FORM)

            filteredParams.forEach {
                when {
                    it.key == "file" -> {
                        val file = it.value as File
                        builder.addFormDataPart(it.key, file.name, file.asRequestBody())
                    }
                    it.value is List<*> -> {
                        val list = it.value as List<*>
                        for (index in list.indices) {
                            builder.addFormDataPart(
                                "${it.key}[]",
                                list[index].toString()
                            )
                        }
                    }
                    else -> {
                        builder.addFormDataPart(it.key, it.value.toString())
                    }
                }
            }
            builder.build()
        } else {
            Gson().toJson(filteredParams)
                .toRequestBody("application/json".toMediaType())
        }

        val request = Request.Builder()
            .url(httpBuilder.build())
            .headers(requestHeaders)
            .method(method, body)
            .build()

        return awaitResponse(request)
    }

    /**
     * Await Response
     * 
     * @param method
     * @param path
     * @param headers
     * @param params
     *
     * @return [Response]    
     */
    @Throws(AppwriteException::class)
    private suspend fun awaitResponse(request: Request) = suspendCancellableCoroutine<Response> {
        http.newCall(request).enqueue(object : Callback {
            override fun onFailure(call: Call, e: IOException) {
                if (it.isCancelled) {
                    return
                }
                it.cancel(e)
            }

            override fun onResponse(call: Call, response: Response) {
                if (response.code >= 400) {
                    val bodyString = response.body
                        ?.charStream()
                        ?.buffered()
                        ?.use(BufferedReader::readText) ?: ""

                    val contentType: String = response.headers["content-type"] ?: ""
                    val error = if (contentType.contains("application/json", ignoreCase = true)) {
                        bodyString.fromJson(Error::class.java)
                    } else {
                        Error(bodyString, response.code)
                    }

                    it.cancel(AppwriteException(
                        error.message,
                        error.code,
                        bodyString
                    ))
                }
                it.resume(response)
            }
        })
    }
}