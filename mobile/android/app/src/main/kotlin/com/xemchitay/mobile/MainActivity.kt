package com.it36vn.xemchitay

import android.content.Intent
import android.net.Uri
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.embedding.android.FlutterActivity
import io.flutter.plugin.common.MethodChannel

class MainActivity : FlutterActivity() {
    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, "com.it36vn.xemchitay/support")
            .setMethodCallHandler { call, result ->
                when (call.method) {
                    "email" -> {
                        val email = call.argument<String>("email") ?: ""
                        val intent = Intent(Intent.ACTION_SENDTO, Uri.parse("mailto:$email"))
                        startActivity(intent)
                        result.success(null)
                    }
                    "call" -> {
                        val phone = call.argument<String>("phone") ?: ""
                        val intent = Intent(Intent.ACTION_DIAL, Uri.parse("tel:$phone"))
                        startActivity(intent)
                        result.success(null)
                    }
                    else -> result.notImplemented()
                }
            }
    }
}
