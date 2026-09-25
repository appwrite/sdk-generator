import android.app.AlarmManager
import android.app.Application
import android.app.Notification
import android.app.NotificationManager
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.content.pm.ActivityInfo
import android.content.pm.ResolveInfo
import android.os.Build
import android.os.Looper
import androidx.test.core.app.ApplicationProvider
import androidx.test.ext.junit.runners.AndroidJUnit4
import io.appwrite.flutter.AppwritePushPlugin
import io.appwrite.services.PushBackground
import io.appwrite.services.PushMessage
import io.appwrite.services.PushReceiver
import io.flutter.embedding.engine.plugins.FlutterPlugin
import io.flutter.plugin.common.BinaryMessenger
import io.flutter.plugin.common.MethodCall
import io.flutter.plugin.common.StandardMethodCodec
import org.json.JSONArray
import org.json.JSONObject
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.Shadows.shadowOf
import org.robolectric.annotation.Config
import java.io.File
import java.nio.ByteBuffer
import java.util.concurrent.CompletableFuture
import java.util.concurrent.CopyOnWriteArrayList

// The app's PushReceiver: records what reaches it and lets the SDK post its notification too.
class E2EPushReceiver : PushReceiver() {
    companion object {
        val messages = CopyOnWriteArrayList<String>()
    }

    override fun onMessage(context: Context, message: PushMessage): Boolean {
        messages.add(message.data)
        return false
    }
}

// Stands in for the Dart side: calls the plugin's channels with the standard codec, as Dart's
// MethodChannel and EventChannel do, and records what the plugin sends back.
class DartSide : BinaryMessenger {
    private val handlers = mutableMapOf<String, BinaryMessenger.BinaryMessageHandler>()
    val events = CopyOnWriteArrayList<Map<*, *>>()

    override fun setMessageHandler(channel: String, handler: BinaryMessenger.BinaryMessageHandler?) {
        if (handler == null) handlers.remove(channel) else handlers[channel] = handler
    }

    override fun send(channel: String, message: ByteBuffer?) = send(channel, message, null)

    // The plugin's event sink sends here.
    override fun send(channel: String, message: ByteBuffer?, callback: BinaryMessenger.BinaryReply?) {
        if (message != null) {
            message.rewind()
            (StandardMethodCodec.INSTANCE.decodeEnvelope(message) as? Map<*, *>)?.let { events.add(it) }
        }
        callback?.reply(null)
    }

    /** Invoke [method] on [channel]; completes with the reply, or the error message. */
    fun invoke(channel: String, method: String, arguments: Any?): CompletableFuture<Result<Any?>> {
        val reply = CompletableFuture<Result<Any?>>()
        val message = StandardMethodCodec.INSTANCE.encodeMethodCall(MethodCall(method, arguments)).also { it.rewind() }
        handlers.getValue(channel).onMessage(message) { bytes ->
            reply.complete(
                runCatching {
                    bytes!!.rewind()
                    StandardMethodCodec.INSTANCE.decodeEnvelope(bytes)
                },
            )
        }
        return reply
    }
}

/**
 * Background delivery through the Flutter plugin, called over its channels the way the SDK's Dart
 * code calls it and observed through the events Dart receives: a background subscription
 * completes once subscribed and its message arrives for its subscription id, the scheduled
 * wake-up after the process died brings the next message to the app's PushReceiver and a
 * notification, the app opening after sign-out delivers nothing, and a refused credential fails
 * the subscribe.
 */
@Config(manifest = Config.NONE)
@RunWith(AndroidJUnit4::class)
class Tests {
    private val context = ApplicationProvider.getApplicationContext<Application>()
    private val dart = DartSide()

    @Test
    fun background() {
        // Attach the plugin as the Flutter engine would, with only what it uses.
        val binding = FlutterPlugin.FlutterPluginBinding::class.java.constructors.first().let { constructor ->
            val arguments = arrayOfNulls<Any>(constructor.parameterCount)
            arguments[0] = context
            arguments[constructor.parameterTypes.indexOfFirst { it == BinaryMessenger::class.java }] = dart
            constructor.newInstance(*arguments) as FlutterPlugin.FlutterPluginBinding
        }
        AppwritePushPlugin().onAttachedToEngine(binding)
        call(EVENTS, "listen", null)

        val host = System.getenv("PUSH_HOST") ?: "mqtt"
        val config = { authMethod: String, credential: String ->
            JSONObject()
                .put("host", host)
                .put("port", 1883)
                .put("tls", false)
                .put("authMethod", authMethod)
                .put("credential", credential)
                .put("project", "console")
                .toString()
        }
        val subscriptions = JSONArray()
            .put(JSONObject().put("id", "sub-1").put("topic", "e2e-push").put("background", true).put("title", "E2E title").put("retry", true))
            .toString()
        val notifications = context.getSystemService(NotificationManager::class.java)
        val alarms = shadowOf(context.getSystemService(AlarmManager::class.java))
        shadowOf(context.packageManager).addResolveInfoForIntent(
            Intent("io.appwrite.push.MESSAGE").setPackage(context.packageName),
            ResolveInfo().apply {
                activityInfo = ActivityInfo().apply {
                    name = E2EPushReceiver::class.java.name
                    packageName = context.packageName
                }
            },
        )

        // A background subscription: host completes once subscribed, and the message the mock
        // publishes then arrives for its subscription id and is acknowledged, as the Dart side does.
        call(METHODS, "setErrorCallback", mapOf("registered" to true))
        val hosted = call(METHODS, "host", mapOf("config" to config("appwrite-session", SESSION), "subscriptions" to subscriptions))
        waitFor { messages().isNotEmpty() }
        val message = messages().firstOrNull()
        message?.let { call(METHODS, "ack", mapOf("token" to it["ackToken"])) }
        writeToFile(
            if (hosted.isSuccess && message?.get("id") == "sub-1" && String(message["payload"] as ByteArray) == "push-payload") {
                "Push background message:passed"
            } else {
                "Push background message:failed"
            },
        )

        // The process dies: Dart and the connection are gone, only what was saved remains.
        // Android fires the wake-up the SDK scheduled; this test runs without a manifest, so
        // register the alarm's receiver the way the merged manifest declares it.
        PushBackground.dropProcessState(context)
        notifications.cancelAll()
        E2EPushReceiver.messages.clear()
        val wakeUp = alarms.nextScheduledAlarm?.operation
        if (wakeUp != null) {
            val wakeUpIntent = shadowOf(wakeUp).savedIntent
            val receiver = Class.forName(wakeUpIntent.component!!.className).getDeclaredConstructor().newInstance() as BroadcastReceiver
            val filter = IntentFilter(wakeUpIntent.action)
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                context.registerReceiver(receiver, filter, Context.RECEIVER_NOT_EXPORTED)
            } else {
                context.registerReceiver(receiver, filter)
            }
            wakeUp.send()
        }
        waitFor { E2EPushReceiver.messages.isNotEmpty() }
        val posted = shadowOf(notifications).allNotifications.firstOrNull()
        val postedTitle = posted?.extras?.getCharSequence(Notification.EXTRA_TITLE)?.toString()
        val postedText = posted?.extras?.getCharSequence(Notification.EXTRA_TEXT)?.toString()
        writeToFile(
            if (E2EPushReceiver.messages.toList() == listOf("push-payload") && postedTitle == "E2E title" && postedText == "push-payload") {
                "Push background restore:passed"
            } else {
                "Push background restore:failed"
            },
        )

        // Sign-out, then the app opens again: nothing is resumed or delivered.
        call(METHODS, "stop", null)
        PushBackground.dropProcessState(context)
        notifications.cancelAll()
        E2EPushReceiver.messages.clear()
        dart.events.clear()
        call(METHODS, "resume", null)
        waitFor(3_000) { E2EPushReceiver.messages.isNotEmpty() || messages().isNotEmpty() }
        writeToFile(
            if (E2EPushReceiver.messages.isEmpty() && messages().isEmpty() && shadowOf(notifications).allNotifications.isEmpty()) {
                "Push background close:passed"
            } else {
                "Push background close:failed"
            },
        )

        // A refused credential fails the subscribe with the broker's reason.
        val refused = call(METHODS, "host", mapOf("config" to config("appwrite-jwt", "deny:refused-in-background"), "subscriptions" to subscriptions))
        writeToFile(
            if (refused.exceptionOrNull()?.message == "refused-in-background") {
                "Push background refused:passed"
            } else {
                "Push background refused:failed"
            },
        )
        call(METHODS, "stop", null)
    }

    private fun messages(): List<Map<*, *>> = dart.events.filter { it["type"] == "message" }

    private fun call(channel: String, method: String, arguments: Any?): Result<Any?> {
        val reply = dart.invoke(channel, method, arguments)
        val deadline = System.currentTimeMillis() + 30_000
        while (!reply.isDone && System.currentTimeMillis() < deadline) {
            shadowOf(Looper.getMainLooper()).idle()
            Thread.sleep(50)
        }
        return reply.getNow(Result.failure(Exception("no reply")))
    }

    private fun waitFor(timeoutMs: Long = 10_000, condition: () -> Boolean) {
        val deadline = System.currentTimeMillis() + timeoutMs
        while (!condition() && System.currentTimeMillis() < deadline) {
            shadowOf(Looper.getMainLooper()).idle()
            Thread.sleep(100)
        }
    }

    private fun writeToFile(line: String) {
        File("result.txt").appendText("$line\n")
    }

    private companion object {
        const val METHODS = "appwrite.push"
        const val EVENTS = "appwrite.push/events"
        const val SESSION = "eyJpZCI6ImUyZS1zZXNzaW9uLXVzZXIiLCJzZWNyZXQiOiJlMmUtc2VjcmV0In0="
    }
}
