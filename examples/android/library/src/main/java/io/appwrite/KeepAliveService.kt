package io.appwrite.services

import android.app.Service
import android.content.Intent
import android.os.Binder
import android.os.IBinder

internal class KeepAliveService: Service() {
    companion object {
      val binder = Binder()
    }

    override fun onBind(intent: Intent) = binder
}