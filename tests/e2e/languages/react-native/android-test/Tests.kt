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
import android.util.Base64
import androidx.test.core.app.ApplicationProvider
import androidx.test.ext.junit.runners.AndroidJUnit4
import com.facebook.react.bridge.Promise
import com.facebook.react.bridge.BridgeReactContext
import com.facebook.react.bridge.WritableMap
import io.appwrite.reactnative.AppwritePushModule
import io.appwrite.services.PushBackground
import io.appwrite.services.PushMessage
import io.appwrite.services.PushReceiver
import org.json.JSONArray
import org.json.JSONObject
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.Shadows.shadowOf
import org.robolectric.annotation.Config
import java.io.File
import java.util.concurrent.CompletableFuture
import java.util.concurrent.CopyOnWriteArrayList
import java.util.concurrent.TimeUnit

// The app's PushReceiver: records what reaches it and lets the SDK post its notification too.
class E2EPushReceiver : PushReceiver() {
    companion object {
        val messages = CopyOnWriteArrayList<String>()
    }

    override fun onMessage(context: Context, message: PushMessage): Boolean {
        messages.add(message.string)
        return false
    }
}

// A React Native Promise settled into a future: a resolved value, or the rejection message.
class TestPromise : Promise {
    val settled = CompletableFuture<Result<Any?>>()

    fun await(): Result<Any?> = settled.get(30, TimeUnit.SECONDS)

    override fun resolve(value: Any?) {
        settled.complete(Result.success(value))
    }

    private fun fail(message: String?) {
        settled.complete(Result.failure(Exception(message)))
    }

    override fun reject(code: String, message: String?) = fail(message)

    override fun reject(code: String, throwable: Throwable?) = fail(throwable?.message)

    override fun reject(code: String, message: String?, throwable: Throwable?) = fail(message)

    override fun reject(throwable: Throwable) = fail(throwable.message)

    override fun reject(throwable: Throwable, userInfo: WritableMap) = fail(throwable.message)

    override fun reject(code: String, userInfo: WritableMap) = fail(code)

    override fun reject(code: String, throwable: Throwable?, userInfo: WritableMap) = fail(throwable?.message)

    override fun reject(code: String, message: String?, userInfo: WritableMap) = fail(message)

    override fun reject(code: String?, message: String?, throwable: Throwable?, userInfo: WritableMap?) = fail(message)

    @Deprecated("Deprecated in React Native")
    override fun reject(message: String) = fail(message)
}

/**
 * Background delivery through the React Native module, called the way the SDK's push.ts calls it
 * and observed through the events JS receives: a background subscription resolves once
 * subscribed and its message arrives for its subscription id, the scheduled wake-up after the
 * process died brings the next message to the app's PushReceiver and a notification, the app
 * opening after sign-out delivers nothing, and a refused credential rejects the subscribe.
 */
@Config(manifest = Config.NONE)
@RunWith(AndroidJUnit4::class)
class Tests {
    private val context = ApplicationProvider.getApplicationContext<Application>()
    private val events = CopyOnWriteArrayList<Pair<String, Map<String, Any?>>>()
    private val module = AppwritePushModule(BridgeReactContext(context)) { event, body -> events.add(event to body) }

    @Test
    fun background() {
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

        // A background subscription: the subscribe resolves once subscribed, and the message the
        // mock publishes then arrives for its subscription id and is acknowledged, as push.ts does.
        call { module.setErrorCallback(true, it) }
        val hosted = call { module.host(config("appwrite-session", SESSION), subscriptions, it) }
        waitFor { messages().isNotEmpty() }
        val message = messages().firstOrNull()
        message?.let { module.ack(it["ackToken"] as String) }
        writeToFile(
            if (hosted.isSuccess && message?.get("id") == "sub-1" && decode(message["payload"]) == "push-payload") {
                "Push background message:passed"
            } else {
                "Push background message:failed"
            },
        )

        // The process dies: JS and the connection are gone, only what was saved remains. Android
        // fires the wake-up the SDK scheduled; this test runs without a manifest, so register the
        // alarm's receiver the way the merged manifest declares it.
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
        call { module.stop(it) }
        PushBackground.dropProcessState(context)
        notifications.cancelAll()
        E2EPushReceiver.messages.clear()
        events.clear()
        call { module.resume(it) }
        waitFor(3_000) { E2EPushReceiver.messages.isNotEmpty() || messages().isNotEmpty() }
        writeToFile(
            if (E2EPushReceiver.messages.isEmpty() && messages().isEmpty() && shadowOf(notifications).allNotifications.isEmpty()) {
                "Push background close:passed"
            } else {
                "Push background close:failed"
            },
        )

        // A refused credential rejects the subscribe with the broker's reason.
        val refused = call { module.host(config("appwrite-jwt", "deny:refused-in-background"), subscriptions, it) }
        writeToFile(
            if (refused.exceptionOrNull()?.message == "refused-in-background") {
                "Push background refused:passed"
            } else {
                "Push background refused:failed"
            },
        )
        call { module.stop(it) }
    }

    private fun messages(): List<Map<String, Any?>> =
        events.filter { it.first == AppwritePushModule.MESSAGE_EVENT }.map { it.second }

    private fun decode(payload: Any?): String = String(Base64.decode(payload as String, Base64.NO_WRAP))

    private fun call(block: (Promise) -> Unit): Result<Any?> {
        val promise = TestPromise()
        block(promise)
        while (!promise.settled.isDone) {
            shadowOf(Looper.getMainLooper()).idle()
            Thread.sleep(50)
        }
        return promise.await()
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
        const val SESSION = "eyJpZCI6ImUyZS1zZXNzaW9uLXVzZXIiLCJzZWNyZXQiOiJlMmUtc2VjcmV0In0="
    }
}
