//
//  ExampleViewModel.swift
//  test (iOS)
//
//  Created by Jake Barnby on 28/09/21.
//

import Foundation
import SwiftUI
import Appwrite
import NIO

extension ExampleView {
    
    class Test : Decodable {
        public let name: String
        public let description: String
        
        public init(name: String, description: String) {
            self.name = name
            self.description = description
        }
    }
    
    class ViewModel : ObservableObject {
        let account = Account(client: client)
        let storage = Storage(client: client)
        let realtime = Realtime(client: client)
        
        @State var downloadedImage: Image? = nil
        
        @State var username: String = "test@test.test"
        @State var password: String = "password"
        @State var fileId: String = "614c1f5864841"
        @State var collectionId: String = "6149afd52ce3b"
        @State var isShowPhotoLibrary = false
        @State var response: String = ""
        
        func register() {
            account.create(username, password) { result in
                switch result {
                case .failure(let error): self.response = error.message
                case .success(var response): self.response = response.body!.readString(length: response.body!.readableBytes) ?? ""
                }
            }
        }
        
        func login() {
            account.createSession(username, password) { result in
                switch result {
                case .failure(let error): self.response = error.message
                case .success(var response): self.response = response.body!.readString(length: response.body!.readableBytes) ?? ""
                }
            }
        }
        
        func loginWithFacebook() {
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
        
        func download() {
            storage.getFileDownload(fileId) { result in
                switch result {
                case .failure(let error):
                    print(error)
                case .success(var response):
                    self.downloadedImage = Image(uiImage: UIImage(
                        data: response.body!.readData(
                            length: response.body!.readableBytes
                        )!
                    )!)
                }
            }
        }
        
        func upload(image: UIImage) {
            let imageBuffer = ByteBufferAllocator()
                .buffer(data: image.jpegData(compressionQuality: 1)!)
                
            let file = File(
                name: "file.png",
                buffer: imageBuffer
            )
            
            storage.createFile(file) { result in
                switch result {
                case .failure(let error):
                    print(error)
                case .success(var response):
                    self.response = response.body!.readString(length: response.body!.readableBytes) ?? ""
                }
            }
        }
        
        func subscribe() {
            _ = realtime.subscribe(channels: ["collections.\(collectionId).documents"], payloadType: Test.self) { message in
                print(String(describing: message))
            }
        }
    }
}
