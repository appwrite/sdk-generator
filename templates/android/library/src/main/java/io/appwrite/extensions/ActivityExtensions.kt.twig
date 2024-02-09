package io.appwrite.extensions

import android.Manifest
import android.content.pm.PackageManager
import android.os.Build
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat

fun AppCompatActivity.requestNotificationPermission(
    onGranted: () -> Unit,
    onDenied: () -> Unit,
    onShowRationale: () -> Unit,
) {
    if (Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU) {
        onGranted()
        return
    }

    if (ContextCompat.checkSelfPermission(
        this,
        Manifest.permission.POST_NOTIFICATIONS
    ) == PackageManager.PERMISSION_GRANTED) {
        onGranted()
    } else if (shouldShowRequestPermissionRationale(Manifest.permission.POST_NOTIFICATIONS)) {
        onShowRationale()
    } else {
        requestPermissionLauncher(
            onGranted,
            onDenied
        ).launch(Manifest.permission.POST_NOTIFICATIONS)
    }
}

private fun AppCompatActivity.requestPermissionLauncher(
    onGranted: () -> Unit,
    onDenied: () -> Unit,
) = registerForActivityResult(
    ActivityResultContracts.RequestPermission(),
) { isGranted: Boolean ->
    if (isGranted) {
        onGranted()
    } else {
        onDenied()
    }
}