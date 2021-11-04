package io.appwrite.models

import java.io.Closeable

data class RealtimeSubscription(
    private val close: () -> Unit
) : Closeable {
    override fun close() = close.invoke()
}

data class RealtimeCallback(
    val channels: Collection<String>,
    val payloadClass: Class<*>,
    val callback: (RealtimeResponseEvent<*>) -> Unit
)

open class RealtimeResponse(
    val type: String,
    val data: Any
)

data class RealtimeResponseEvent<T>(
    val event: String,
    val channels: Collection<String>,
    val timestamp: Long,
    var payload: T
)

enum class RealtimeCode(val value: Int) {
    POLICY_VIOLATION(1008),
    UNKNOWN_ERROR(-1)
}