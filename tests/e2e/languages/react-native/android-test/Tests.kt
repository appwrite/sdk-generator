import android.app.Activity
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
import android.net.Uri
import android.os.Looper
import android.util.Base64
import androidx.test.core.app.ApplicationProvider
import androidx.test.ext.junit.runners.AndroidJUnit4
import com.facebook.react.bridge.Promise
import com.facebook.react.bridge.BridgeReactContext
import com.facebook.react.bridge.WritableMap
import com.facebook.react.modules.network.ForwardingCookieHandler
import io.appwrite.reactnative.AppwriteCookiesModule
import io.appwrite.reactnative.AppwritePushModule
import io.appwrite.services.PushBackground
import io.appwrite.services.PushMessage
import io.appwrite.services.PushReceiver
import io.appwrite.services.PushStore
import org.json.JSONArray
import org.json.JSONObject
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.Robolectric
import org.robolectric.Shadows.shadowOf
import org.robolectric.annotation.Config
import java.io.File
import java.net.URI
import java.util.concurrent.CompletableFuture
import java.util.concurrent.CopyOnWriteArrayList
import java.util.concurrent.TimeUnit

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
    private val reactContext = BridgeReactContext(context)
    private val module = AppwritePushModule(reactContext) { event, body -> events.add(event to body) }

    @Test
    fun background() {
        // The app is not on screen, so background subscriptions post their notifications.
        shadowOf(context.getSystemService(android.app.ActivityManager::class.java)).setProcesses(
            listOf(
                android.app.ActivityManager.RunningAppProcessInfo().apply {
                    pid = android.os.Process.myPid()
                    importance = android.app.ActivityManager.RunningAppProcessInfo.IMPORTANCE_CACHED
                },
            ),
        )
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
            // On Android 12+ the alarm hands the run to an expedited job; run that job's work, as
            // JobScheduler would (Robolectric does not run jobs).
            shadowOf(android.os.Looper.getMainLooper()).idle()
            val jobScheduler = context.getSystemService(android.app.job.JobScheduler::class.java)
            if (jobScheduler.allPendingJobs.any { it.id == PushBackground.EXPEDITED_JOB_ID }) {
                jobScheduler.cancel(PushBackground.EXPEDITED_JOB_ID)
                PushBackground.tick(context, drainMs = PushBackground.JOB_DRAIN_MS) {}
            }
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
        call { module.resume("appwrite-session", SESSION, true, it) }
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
        sessionCookie()
        notificationOpened()

        // While no JS runs, background runs follow the session cookie the app signs in with: a
        // rotated session of the same user replaces the saved one, and signing out stops delivery.
        val cookieUrl = "http://$host/v1"
        val cookies = android.webkit.CookieManager.getInstance()
        val rotated = android.util.Base64.encodeToString(
            JSONObject().put("id", "e2e-session-user").put("secret", "rotated").toString().toByteArray(),
            android.util.Base64.NO_WRAP,
        )
        cookies.setCookie(cookieUrl, "a_session_console=$rotated")
        val cookieConfig = JSONObject(config("appwrite-session", SESSION)).put("sessionCookieUrl", cookieUrl).toString()
        call { module.host(cookieConfig, subscriptions, it) }
        PushBackground.dropProcessState(context)
        PushBackground.tick(context) {}
        waitFor { PushStore.loadState(context)?.first?.credential == rotated }
        val followed = PushStore.loadState(context)?.first?.credential == rotated
        cookies.removeAllCookies(null)
        PushBackground.tick(context) {}
        waitFor { !PushBackground.hasSaved(context) }
        val stoppedWhenSignedOut = !PushBackground.hasSaved(context)
        writeToFile(
            if (followed && stoppedWhenSignedOut) {
                "Push background cookie refresh:passed"
            } else {
                "Push background cookie refresh:failed (followed: $followed, stopped: $stoppedWhenSignedOut)"
            },
        )
        call { module.stop(it) }

        // An automatic reconnect sends the session the app has now: the cookie rotates while the
        // connection is up, the broker drops it, and the client reconnects on its own with the new
        // session, which the broker echoes on "e2e-whoauth".
        cookies.setCookie(cookieUrl, "a_session_console=$SESSION")
        val reconnected = android.util.Base64.encodeToString(
            JSONObject().put("id", "e2e-session-user").put("secret", "reconnected").toString().toByteArray(),
            android.util.Base64.NO_WRAP,
        )
        val authSubscriptions = JSONArray()
            .put(JSONObject().put("id", "auth").put("topic", "e2e-whoauth").put("background", true).put("retry", false))
            .put(JSONObject().put("id", "drop").put("topic", "e2e-drop/1500").put("background", true).put("retry", false))
            .toString()
        val sentCredentials = {
            messages().filter { it["id"] == "auth" }.map { decode(it["payload"]) }
        }
        events.clear()
        call { module.host(cookieConfig, authSubscriptions, it) }
        waitFor { SESSION in sentCredentials() }
        cookies.setCookie(cookieUrl, "a_session_console=$reconnected")
        waitFor(15_000) { reconnected in sentCredentials() }
        writeToFile(
            if (sentCredentials().firstOrNull() == SESSION && reconnected in sentCredentials()) {
                "Push background reconnect credential:passed"
            } else {
                "Push background reconnect credential:failed (sent: ${sentCredentials()})"
            },
        )
        call { module.stop(it) }

        // A scheduled run stays up while a delivery is pending (JS has not acknowledged it yet) and
        // ends shortly after it settles; with nothing pending it ends once deliveries are quiet.
        events.clear()
        call { module.host(config("appwrite-session", SESSION), subscriptions, it) }
        waitFor { messages().isNotEmpty() }
        val pendingToken = messages().firstOrNull()?.get("ackToken") as? String
        val pendingRun = CompletableFuture<Long>()
        PushBackground.tick(context, drainMs = PushBackground.JOB_DRAIN_MS) { pendingRun.complete(System.currentTimeMillis()) }
        Thread.sleep(4_000)
        val heldWhilePending = !pendingRun.isDone
        val acknowledgedAt = System.currentTimeMillis()
        pendingToken?.let { module.ack(it) }
        val endedAfterAck = runCatching { pendingRun.get(6, TimeUnit.SECONDS) - acknowledgedAt }.getOrNull()
        val quietRun = CompletableFuture<Long>()
        val quietStart = System.currentTimeMillis()
        PushBackground.tick(context, drainMs = PushBackground.JOB_DRAIN_MS) { quietRun.complete(System.currentTimeMillis()) }
        val quietTook = runCatching { quietRun.get(12, TimeUnit.SECONDS) - quietStart }.getOrNull()
        writeToFile(
            if (pendingToken != null && heldWhilePending && endedAfterAck != null && quietTook != null && quietTook in 1_500L..8_000L) {
                "Push background drain:passed"
            } else {
                "Push background drain:failed (held: $heldWhilePending, after ack: $endedAfterAck, quiet: $quietTook)"
            },
        )
        call { module.stop(it) }
    }

    // Taps on the notifications background delivery posts, read the way push.ts reads them: the tap
    // that launched the app is reported once, and a tap while the app runs arrives as an event.
    private fun notificationOpened() {
        val payload = JSONObject().put("data", JSONObject().put("saleId", "42")).toString()
        val tap = { Intent(Intent.ACTION_MAIN).putExtra(PushBackground.EXTRA_TOPIC, "e2e-push").putExtra(PushBackground.EXTRA_PAYLOAD, payload) }
        val activity = Robolectric.buildActivity(Activity::class.java, tap()).setup().get()
        reactContext.onHostResume(activity)
        val launched = call { module.getInitialNotification(it) }.getOrNull() as? String
        val again = call { module.getInitialNotification(it) }.getOrNull()
        val initial = launched?.let { JSONObject(it) }
        events.clear()
        reactContext.onNewIntent(activity, tap())
        val opened = events.firstOrNull { it.first == AppwritePushModule.OPENED_EVENT }?.second
        writeToFile(
            if (initial?.optString("topic") == "e2e-push" && initial.optString("payload") == payload && again == null &&
                opened?.get("topic") == "e2e-push" && opened["payload"] == payload
            ) {
                "Push notification opened:passed"
            } else {
                "Push notification opened:failed (launched: $launched, again: $again, opened: $opened)"
            },
        )
    }

    private fun sessionCookie() {
        val url = "https://cloud.example.test/v1"
        val reactContext = BridgeReactContext(context)
        ForwardingCookieHandler(reactContext).put(
            URI("$url/account/sessions/email"),
            mapOf(
                "Set-Cookie" to listOf(
                    "a_session_console=${Uri.encode(SESSION)}; Path=/; Secure; HttpOnly",
                    "a_session_console_legacy=legacy; Path=/; Secure; HttpOnly",
                ),
            ),
        )
        val cookies = AppwriteCookiesModule(reactContext)
        val value = call { cookies.session(url, "console", it) }.getOrNull()
        val other = call { cookies.session(url, "other", it) }.getOrNull()
        writeToFile(
            if (value == Uri.encode(SESSION) && other == null) {
                "Push session cookie:passed"
            } else {
                "Push session cookie:failed"
            },
        )
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
