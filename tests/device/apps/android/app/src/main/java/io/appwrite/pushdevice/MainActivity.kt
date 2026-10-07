package io.appwrite.pushdevice

import android.os.Bundle
import android.util.Log
import android.view.View
import android.widget.Button
import android.widget.LinearLayout
import android.widget.ScrollView
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import io.appwrite.Client
import io.appwrite.services.Push
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import org.json.JSONObject

// The device tests' user on the mock broker: base64 of {"id": "e2e-device-user", "secret": "device"}.
private const val SESSION = "eyJpZCI6ImUyZS1kZXZpY2UtdXNlciIsInNlY3JldCI6ImRldmljZSJ9"

class MainActivity : AppCompatActivity() {
    private lateinit var push: Push
    private lateinit var lines: TextView
    private lateinit var exactAlarms: Button
    private lateinit var battery: Button

    // One event per line, read by the device test driver.
    private fun log(line: String) {
        Log.i("push-e2e", line)
        runOnUiThread { lines.text = "$line\n${lines.text}" }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        lines = TextView(this)
        exactAlarms = Button(this).apply {
            text = "Allow exact alarms"
            setOnClickListener {
                log("tapped exact")
                log("asked exact: ${Push.requestExactAlarms(context)}")
            }
        }
        battery = Button(this).apply {
            text = "Ignore battery optimisation"
            setOnClickListener {
                log("tapped battery")
                log("asked battery: ${Push.requestIgnoreBatteryOptimizations(context)}")
            }
        }
        val status = Button(this).apply {
            text = "Check background status"
            setOnClickListener { checkStatus() }
        }
        setContentView(
            LinearLayout(this).apply {
                orientation = LinearLayout.VERTICAL
                setPadding(32, 32, 32, 32)
                addView(exactAlarms)
                addView(battery)
                addView(status)
                addView(ScrollView(context).apply { addView(lines) })
            },
        )

        val client = Client(applicationContext)
            .setEndpoint("http://10.0.2.2/v1")
            .setProject("console")
            .setPushEndpoint("mqtt://10.0.2.2:1883")
            .setSession(SESSION)
        push = Push(client, this)
            .onOpen { log("connected") }
            .onClose { log("disconnected") }
            .onError { log("error: ${it.message}") }

        lifecycleScope.launch(Dispatchers.IO) {
            try {
                push.subscribe { message ->
                    val title = JSONObject(message.data).optJSONObject("notification")?.optString("title")
                    log("message: $title")
                }
                log("subscribed")
                checkStatus()
                push.getInitialNotification()?.let { log("launched: ${it.data["saleId"]}") }
                push.onNotificationOpened { log("opened: ${it.data["saleId"]}") }
            } catch (e: Exception) {
                log("error: ${e.message}")
            }
        }
    }

    private fun checkStatus() {
        val status = Push.backgroundStatus(applicationContext)
        log("status: exact=${status.exactAlarms} battery=${status.ignoringBatteryOptimizations} bestEffort=${status.bestEffort}")
        runOnUiThread {
            exactAlarms.visibility = if (status.exactAlarms) View.GONE else View.VISIBLE
            battery.visibility = if (status.ignoringBatteryOptimizations) View.GONE else View.VISIBLE
        }
    }
}
