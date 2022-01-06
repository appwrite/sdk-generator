import SwiftUI
import Appwrite
import NIO

@main
struct ExampleApp: App {
    var body: some Scene {
        WindowGroup {
            ExampleView(viewModel: ExampleView.ViewModel())
        }
    }
}
