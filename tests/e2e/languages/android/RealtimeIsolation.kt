package io.appwrite

import android.content.Context
import io.appwrite.extensions.fromJson
import io.appwrite.extensions.toJson
import io.appwrite.services.Realtime
import mockwebserver3.MockResponse
import mockwebserver3.MockWebServer
import okhttp3.Response
import okhttp3.WebSocket
import okhttp3.WebSocketListener
import java.util.concurrent.ConcurrentHashMap
import java.util.concurrent.CountDownLatch
import java.util.concurrent.LinkedBlockingQueue
import java.util.concurrent.TimeUnit
import java.util.concurrent.atomic.AtomicInteger
import java.util.concurrent.atomic.AtomicReference

data class IsolationPayload(val source: String)

/** Exercises the public API against independent WebSocket backends. */
object RealtimeIsolationScenarios {
    fun run(context: Context) {
        MockWebServer().use { serverA ->
            MockWebServer().use { serverB ->
                serverA.start()
                serverB.start()
                val backendA = Backend("a")
                val backendB = Backend("b")
                val backendC = Backend("c")
                val reconnectB = Backend("b-reconnected")
                serverA.enqueue(upgrade(backendA))
                serverA.enqueue(upgrade(backendC))
                serverB.enqueue(upgrade(backendB))
                serverB.enqueue(upgrade(reconnectB))

                val clientA = client(context, serverA, "project-a", "jwt-a")
                val clientB = client(context, serverB, "project-b", "jwt-b")
                val clientC = client(context, serverA, "project-a", "jwt-c")
                val realtimeA = Realtime(clientA)
                val realtimeB = Realtime(clientB)
                val realtimeC = Realtime(clientC)
                val sharedA = Realtime(clientA)
                val eventsA = LinkedBlockingQueue<String>()
                val eventsB = LinkedBlockingQueue<String>()
                val eventsC = LinkedBlockingQueue<String>()
                val sharedEventsA = LinkedBlockingQueue<String>()
                val errorsA = LinkedBlockingQueue<Int>()
                val errorsB = LinkedBlockingQueue<Int>()
                val errorsC = LinkedBlockingQueue<Int>()
                val closesA = AtomicInteger()
                val closesC = AtomicInteger()
                val closeB = CountDownLatch(1)

                realtimeA.onError { _, code -> errorsA.offer(code ?: -1) }
                realtimeB.onError { _, code -> errorsB.offer(code ?: -1) }
                realtimeC.onError { _, code -> errorsC.offer(code ?: -1) }
                realtimeA.onClose { closesA.incrementAndGet() }
                realtimeB.onClose { closeB.countDown() }
                realtimeC.onClose { closesC.incrementAndGet() }

                try {
                    subscribe(realtimeA, eventsA)
                    expect(eventsA, "a", "first backend")
                    assertHandshake(serverA, "project-a", "jwt-a")

                    subscribe(realtimeB, eventsB)
                    expect(eventsB, "b", "second backend must receive its own subscription")
                    assertHandshake(serverB, "project-b", "jwt-b")

                    // The same endpoint and project still need distinct connections for different clients.
                    subscribe(realtimeC, eventsC)
                    expect(eventsC, "c", "same endpoint with another client")
                    assertHandshake(serverA, "project-a", "jwt-c")

                    // Wrappers around the same Client retain one shared connection.
                    subscribe(sharedA, sharedEventsA)
                    expect(sharedEventsA, "a", "same-client wrapper")
                    check(serverA.requestCount == 2) { "same Client opened another socket" }
                    check(serverB.requestCount == 1) { "unexpected second-backend socket" }

                    realtimeA.upsertPresence("online", "presence-a")
                    realtimeB.upsertPresence("online", "presence-b")
                    realtimeC.upsertPresence("online", "presence-c")
                    expect(backendA.presences, "presence-a", "first-client presence")
                    expect(backendB.presences, "presence-b", "second-client presence")
                    expect(backendC.presences, "presence-c", "same-endpoint client presence")

                    backendB.error()
                    check(errorsB.poll(10, TimeUnit.SECONDS) == 400) { "missing second-client error" }
                    check(errorsA.isEmpty() && errorsC.isEmpty()) { "error leaked across clients" }

                    backendB.closeForReconnect()
                    check(closeB.await(10, TimeUnit.SECONDS)) { "missing second-client close callback" }
                    expect(eventsB, "b-reconnected", "reconnect must stay on the second backend")
                    assertHandshake(serverB, "project-b", "jwt-b")
                    check(closesA.get() == 0 && closesC.get() == 0) { "close leaked across clients" }

                    realtimeB.disconnect()
                    check(reconnectB.closed.await(10, TimeUnit.SECONDS)) { "second client did not disconnect" }
                    backendA.emit("a-after-b-disconnect")
                    expect(eventsA, "a-after-b-disconnect", "first client after second-client disconnect")
                    expect(sharedEventsA, "a-after-b-disconnect", "same-client subscription remains active")

                    realtimeA.disconnect()
                    check(backendA.closed.await(10, TimeUnit.SECONDS)) { "first client did not disconnect" }
                    backendC.emit("c-after-a-disconnect")
                    expect(eventsC, "c-after-a-disconnect", "same-endpoint client after first-client disconnect")
                    check(eventsB.isEmpty()) { "disconnected client received another client's event" }
                } finally {
                    realtimeA.disconnect()
                    realtimeB.disconnect()
                    realtimeC.disconnect()
                }
            }
        }
    }

    private fun client(context: Context, server: MockWebServer, project: String, jwt: String): Client =
        Client(context)
            .setEndpointRealtime(server.url("/v1").toString().replaceFirst("http", "ws"))
            .setProject(project)
            .setJWT(jwt)

    private fun subscribe(realtime: Realtime, events: LinkedBlockingQueue<String>) {
        realtime.subscribe("tests", payloadType = IsolationPayload::class.java) {
            events.offer(it.payload.source)
        }
    }

    private fun expect(events: LinkedBlockingQueue<String>, expected: String, context: String) {
        val actual = events.poll(10, TimeUnit.SECONDS)
        check(actual == expected) { "$context: expected $expected, got $actual" }
    }

    private fun assertHandshake(server: MockWebServer, project: String, jwt: String) {
        val request = checkNotNull(server.takeRequest(10, TimeUnit.SECONDS)) { "missing WebSocket handshake" }
        check(request.url.queryParameter("project") == project) { "wrong project in handshake" }
        check(request.headers["x-appwrite-jwt"] == jwt) { "wrong credentials in handshake" }
    }

    private fun upgrade(listener: WebSocketListener): MockResponse =
        MockResponse.Builder().webSocketUpgrade(listener).build()

    private class Backend(private val source: String) : WebSocketListener() {
        private val socket = AtomicReference<WebSocket>()
        private val subscriptions = ConcurrentHashMap<String, List<String>>()
        val closed = CountDownLatch(1)
        val presences = LinkedBlockingQueue<String>()

        override fun onOpen(webSocket: WebSocket, response: Response) {
            socket.set(webSocket)
            webSocket.send(mapOf("type" to "connected", "data" to emptyMap<String, Any>()).toJson())
        }

        override fun onMessage(webSocket: WebSocket, text: String) {
            val frame = text.fromJson<Map<String, Any?>>()
            if (frame["type"] == "presence") {
                val presence = frame["data"] as Map<*, *>
                presences.offer(presence["presenceId"] as String)
                return
            }
            if (frame["type"] != "subscribe") return
            val rows = frame["data"] as List<*>
            rows.forEach { row ->
                val subscription = row as Map<*, *>
                val id = subscription["subscriptionId"] as String
                val channels = (subscription["channels"] as List<*>).map { it.toString() }
                subscriptions[id] = channels
                emit(source, id, channels)
            }
        }

        fun emit(source: String) {
            subscriptions.forEach { (id, channels) -> emit(source, id, channels) }
        }

        private fun emit(source: String, id: String, channels: List<String>) {
            check(socket.get().send(
                mapOf(
                    "type" to "event",
                    "data" to mapOf(
                        "events" to listOf("tests.update"),
                        "channels" to channels,
                        "timestamp" to "2026-01-01T00:00:00.000Z",
                        "subscriptions" to listOf(id),
                        "payload" to mapOf("source" to source),
                    ),
                ).toJson()
            )) { "could not emit test event" }
        }

        fun error() {
            socket.get().send(mapOf("type" to "error", "data" to mapOf("message" to "test error", "code" to 400)).toJson())
        }

        fun closeForReconnect() {
            check(socket.get().close(1011, "reconnect this client"))
        }

        override fun onClosing(webSocket: WebSocket, code: Int, reason: String) {
            webSocket.close(code, reason)
        }

        override fun onClosed(webSocket: WebSocket, code: Int, reason: String) {
            closed.countDown()
        }
    }
}
