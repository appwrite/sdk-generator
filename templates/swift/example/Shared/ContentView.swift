//
//  ContentView.swift
//  Shared
//
//  Created by Jake Barnby on 3/08/21.
//
//

import SwiftUI
import Appwrite

struct ContentView: View {

    @State var host: String = "https://demo.appwrite.io/v1"
    @State var projectId: String = "60f6a0d6e2a52"
    @State var username: String = "test@test.test"
    @State var password: String = "password"
    @State var fileId: String = "60f7a0178c3e5"

    @State var response: String = ""

    @State var image: Image? = nil

    @ObservedObject var keyboard: Keyboard = .init()

    var body: some View {
        VStack(spacing: 8) {

            image?
                .resizable()
                .aspectRatio(contentMode: .fit)
                .frame(height: 200)

            TextField("Host", text: $host)
                .textFieldStyle(RoundedBorderTextFieldStyle())
                .padding()

            TextField("Project ID", text: $projectId)
                .textFieldStyle(RoundedBorderTextFieldStyle())
                .padding()

            TextField("Username", text: $username)
                .textFieldStyle(RoundedBorderTextFieldStyle())
                .padding()

            TextField("Password", text: $password)
                .textFieldStyle(RoundedBorderTextFieldStyle())
                .padding()

            TextField("File ID", text: $fileId)
                .textFieldStyle(RoundedBorderTextFieldStyle())
                .padding()

            TextEditor(text: $response)
                .padding()
                .padding(.bottom, keyboard.height)
                .edgesIgnoringSafeArea(keyboard.height > 0 ? .bottom : [])

            HStack(spacing: 8) {

                let client = Client()
                    .setEndpoint(host)
                    .setProject(projectId)

                let account = Account(client: client)
                let storage = Storage(client: client)

                Button("Login") {
                    account.createSession(username, password) { result in
                        switch result {
                        case .failure(let error): self.response = error.message
                        case .success(var response): self.response = response.body!.readString(length: response.body!.readableBytes) ?? ""
                        }
                    }
                }

                Button("Login with Facebook") {
                    account.createOAuth2Session(
                        "facebook",
                        "\(host)/auth/oauth2/success",
                        "\(host)/auth/oauth2/failure"
                    ) { result in
                        switch result {
                        case .failure: self.response = "false"
                        case .success(let response): self.response = response.description
                        }
                    }
                }

                Button("Register") {
                    account.create(username, password) { result in
                        switch result {
                        case .failure(let error): self.response = error.message
                        case .success(var response): self.response = response.body!.readString(length: response.body!.readableBytes) ?? ""
                        }
                    }
                }


                Button("Download image") {
                    storage.getFileDownload(fileId) { result in
                        switch result {
                        case .failure(let error):
                            print(error)
                        case .success(var response):
                            image = Image(uiImage: UIImage(
                                data: response.body!.readData(
                                    length: response.body!.readableBytes
                                )!
                            )!)
                        }
                    }
                }
            }
        }.registerOAuthHandler()
    }
}

struct ContentView_Previews: PreviewProvider {
    static var previews: some View {
        ContentView()
    }
}
