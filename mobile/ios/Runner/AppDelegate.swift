import Flutter
import UIKit

@main
@objc class AppDelegate: FlutterAppDelegate, FlutterImplicitEngineDelegate {
  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }

  func didInitializeImplicitFlutterEngine(_ engineBridge: FlutterImplicitEngineBridge) {
    GeneratedPluginRegistrant.register(with: engineBridge.pluginRegistry)
    let channel = FlutterMethodChannel(
      name: "com.it36vn.xemchitay/support",
      binaryMessenger: engineBridge.pluginRegistry.registrar(forPlugin: "SupportLauncher")!.messenger()
    )
    channel.setMethodCallHandler { call, result in
      guard let args = call.arguments as? [String: Any] else {
        result(FlutterError(code: "bad_args", message: "Missing arguments", details: nil))
        return
      }
      if call.method == "email", let email = args["email"] as? String, let url = URL(string: "mailto:\(email)") {
        UIApplication.shared.open(url)
        result(nil)
      } else if call.method == "call", let phone = args["phone"] as? String, let url = URL(string: "tel:\(phone)") {
        UIApplication.shared.open(url)
        result(nil)
      } else {
        result(FlutterMethodNotImplemented)
      }
    }
  }
}
