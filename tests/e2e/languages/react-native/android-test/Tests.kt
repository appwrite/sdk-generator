import android.app.AlarmManager
import android.app.Application
import android.app.Notification
import android.app.NotificationManager
import android.app.job.JobScheduler
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
import io.appwrite.services.PushBackground
import io.appwrite.services.PushBridge
import io.appwrite.services.PushMessage
import io.appwrite.services.PushReceiver
import org.json.JSONArray
import org.json.JSONObject
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.Shadows.shadowOf
import org.robolectric.annotation.Config
import java.io.File
import java.util.concurrent.CopyOnWriteArrayList

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

/**
 * Background delivery through the React Native module, driven with the JSON the SDK's push.ts
 * sends over the bridge: a background subscription's message reaches its subscription id, the
 * scheduled wake-up after the process died brings the next message to the app's PushReceiver
 * and a notification, sign-out stops it, and a refused credential stops it with onError.
 */
@Config(manifest = Config.NONE)
@RunWith(AndroidJUnit4::class)
class Tests {
    private val context = ApplicationProvider.getApplicationContext<Application>()
    private val messages = CopyOnWriteArrayList<String>()
    private val errors = CopyOnWriteArrayList<String>()
    private val bridge = PushBridge(
        context,
        object : PushBridge.Events {
            override fun onMessage(subscriptionId: String, message: PushMessage) {
                messages.add("$subscriptionId:${message.string}")
            }

            override fun onError(message: String) {
                errors.add(message)
            }
        },
    )

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
        val jobs = context.getSystemService(JobScheduler::class.java)
        shadowOf(context.packageManager).addResolveInfoForIntent(
            Intent("io.appwrite.push.MESSAGE").setPackage(context.packageName),
            ResolveInfo().apply {
                activityInfo = ActivityInfo().apply {
                    name = E2EPushReceiver::class.java.name
                    packageName = context.packageName
                }
            },
        )

        // A background subscription hosted over the bridge; the mock publishes on SUBSCRIBE.
        bridge.setErrorCallback(true)
        bridge.host(config("appwrite-session", SESSION), subscriptions)
        waitFor { messages.contains("sub-1:push-payload") }
        writeToFile(if (messages.contains("sub-1:push-payload")) "Push background message:passed" else "Push background message:failed")

        // The process dies: the bridge's subscriptions and the connection are gone, only what was
        // saved remains. Android fires the wake-up the SDK scheduled; this test runs without a
        // manifest, so register the alarm's receiver the way the merged manifest declares it.
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

        // Sign-out: stop() leaves no wake-up scheduled.
        bridge.stop()
        writeToFile(
            if (jobs.allPendingJobs.isEmpty() && alarms.nextScheduledAlarm == null) {
                "Push background close:passed"
            } else {
                "Push background close:failed"
            },
        )

        // A refused credential stops background delivery and reaches onError.
        errors.clear()
        bridge.setErrorCallback(true)
        bridge.host(config("appwrite-jwt", "deny:refused-in-background"), subscriptions)
        waitFor { errors.isNotEmpty() }
        Thread.sleep(500)
        writeToFile(
            if (errors.firstOrNull() == "refused-in-background" && jobs.allPendingJobs.isEmpty()) {
                "Push background refused:passed"
            } else {
                "Push background refused:failed"
            },
        )
        bridge.stop()
    }

    private fun waitFor(condition: () -> Boolean) {
        val deadline = System.currentTimeMillis() + 10_000
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
