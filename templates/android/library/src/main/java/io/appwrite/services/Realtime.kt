package {{ sdk.namespace | caseDot }}.services

import android.util.Log
import com.google.gson.Gson
import com.google.gson.JsonParseException
import {{ sdk.namespace | caseDot }}.Client
import {{ sdk.namespace | caseDot }}.extensions.forEachAsync
import {{ sdk.namespace | caseDot }}.models.RealtimeCodes
import {{ sdk.namespace | caseDot }}.models.RealtimeError
import {{ sdk.namespace | caseDot }}.models.RealtimeMessage
import {{ sdk.namespace | caseDot }}.models.RealtimeSubscription
import kotlinx.coroutines.*
import kotlinx.coroutines.Dispatchers.IO
import okhttp3.*
import okhttp3.internal.concurrent.TaskRunner
import okhttp3.internal.ws.RealWebSocket
import java.util.*
import kotlin.coroutines.CoroutineContext

class Realtime(client: Client) : BaseService(client), CoroutineScope {

    private val job = Job()

    override val coroutineContext: CoroutineContext
        get() = Dispatchers.Main + job

    private companion object {
        private const val debounceDelayMillis = 20

        private var socket: RealWebSocket? = null
        private var lastMessage: RealtimeMessage? = null
        private var channelCallbacks = mutableMapOf<String, MutableCollection<(Any) -> Unit>>()

        private var goingToConnect = false
        private var subscribeReentered = false
    }

    private fun createSocket() {
        val queryParamBuilder = StringBuilder()
            .append("project=")
            .append(client.config["project"])

        channelCallbacks.forEach {
            queryParamBuilder
                .append("&channels[]=")
                .append(it.key)
        }

        val request = Request.Builder()
            .url("${client.endPointRealtime}/realtime?$queryParamBuilder")
            .build()

        if (socket != null) {
            socket?.close(1000, null)
        }

        socket = RealWebSocket(
            taskRunner = TaskRunner.INSTANCE,
            originalRequest = request,
            listener = {{ spec.title | caseUcFirst }}WebSocketListener(),
            random = Random(),
            pingIntervalMillis = client.http.pingIntervalMillis.toLong(),
            extensions = null,
            minimumDeflateSize = client.http.minWebSocketMessageToCompress
        )

        socket!!.connect(client.http)
    }

    fun subscribe(
        channels: Collection<String>,
        callback: (Any) -> Unit
    ) : RealtimeSubscription {
        if (goingToConnect) {
            subscribeReentered = true
        }

        channels.forEach {
            channelCallbacks[it]?.add(callback)
        }

        launch {
            goingToConnect = true
            delay(debounceDelayMillis.toLong())
            if (subscribeReentered) {
                subscribeReentered = false
                return@launch
            }

            socket?.connect(client.http)
            goingToConnect = false
            subscribeReentered = false
        }

        return RealtimeSubscription {
            // TODO: Allow subscription closing
        }
    }

    fun subscribe(channel: String, callback: (Any) -> Unit) =
        subscribe(listOf(channel), callback)

    private inner class {{ spec.title | caseUcFirst }}WebSocketListener : WebSocketListener() {

        override fun onOpen(webSocket: WebSocket, response: Response) {
            super.onOpen(webSocket, response)
            Log.i(this@Realtime::class.java.simpleName, "WebSocket connected.")
        }

        override fun onMessage(webSocket: WebSocket, text: String) {
            super.onMessage(webSocket, text)
            Log.i(this@Realtime::class.java.simpleName, "WebSocket message received.")

            launch(IO) {
                val message = try {
                    Gson().fromJson(text, RealtimeMessage::class.java)
                } catch (ex: JsonParseException) {
                    val error = parseError(text) // TODO: How to propagate this to client?
                    throw ex
                } ?: return@launch

                lastMessage = message

                message.channels.forEachAsync { channel ->
                    channelCallbacks[channel]?.forEachAsync { callback ->
                        callback.invoke(message.payload)
                    }
                }
            }
        }

        override fun onClosing(webSocket: WebSocket, code: Int, reason: String) {
            super.onClosing(webSocket, code, reason)
            Log.i(this@Realtime::class.java.simpleName, "WebSocket closing with code $code because: $reason. Reconnecting in 1 second.")
            if (lastMessage?.code == RealtimeCodes.POLICY_VIOLATION.value) {
                return
            }
            launch {
                delay(1000)
                createSocket()
            }
        }

        override fun onFailure(webSocket: WebSocket, t: Throwable, response: Response?) {
            super.onFailure(webSocket, t, response)
            Log.e(this@Realtime::class.java.simpleName, "WebSocket failure.")
            t.printStackTrace()
        }

        private fun parseError(text: String) : RealtimeError {
            return try {
                Gson().fromJson(text, RealtimeError::class.java)
            } catch (ex: JsonParseException) {
                ex.printStackTrace()
                throw ex
            }
        }
    }
}