//
//  ContentView.swift
//  Shared
//
//  Created by Jake Barnby on 3/08/21.
//
//

import SwiftUI
import Appwrite

let client = Client()
    .setEndpoint("https://demo.appwrite.io/v1")
    .setProject("60f6a0d6e2a52")

struct ContentView: View {

    @State var response: String = ""
    @ObservedObject var keyboard: Keyboard = .init()

    let account = Account(client: client)

    var body: some View {
        VStack(spacing: 8) {

            TextEditor(text: $response)
                .textFieldStyle(RoundedBorderTextFieldStyle())
                .padding(.horizontal)
                .padding(.top, 100)
                .padding(.bottom, keyboard.height)
                .edgesIgnoringSafeArea(keyboard.height > 0 ? .bottom : [])

            Divider()

            HStack {

                Button("Login") {
                    do {
                        try account.createSession("test@test.test", "password") { result in
                            switch result {
                            case .failure(let error): self.response = error.localizedDescription
                            case .success(var response): self.response = response.body!.readString() ?? ""
                            }
                        }
                    } catch let error {
                        print(error)
                    }
                }

                Button("Login with Facebook") {
                    do {
                        try account.createOAuth2Session(
                            "facebook",
                            "https://demo.appwrite.io/auth/oauth2/success",
                            "https://demo.appwrite.io/auth/oauth2/success"
                        ) { result in
                            switch result {
                            case .failure: self.response = "false"
                            case .success(let response): self.response = response.description
                            }
                        }
                    } catch let error {
                        print(error)
                    }
                }

                Button("Register") {
                    do {
                        try account.create("test@test.test", "password") { result in
                            switch result {
                            case .failure(let error): self.response = error.localizedDescription
                            case .success(var response): self.response = response.body!.readString()
                            }
                        }
                    } catch let error {
                        print(error)
                    }
                }

                Button("Realtime") {

                }
            }
        }.registerOauthHandler()
    }
}

struct ContentView_Previews: PreviewProvider {
    static var previews: some View {
        ContentView()
    }
}
