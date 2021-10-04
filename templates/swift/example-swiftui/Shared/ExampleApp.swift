//
//  testApp.swift
//  Shared
//
//  Created by Jake Barnby on 3/08/21.
//
//

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
