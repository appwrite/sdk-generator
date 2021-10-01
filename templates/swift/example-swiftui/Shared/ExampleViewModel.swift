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

     class Test : Model {
        
        enum CodingKeys: String, CodingKey {
            case name
            case description
        }
        
        public var name: String?
        public var description: String?
        
        required init(from decoder: Decoder) throws {
            let values = try decoder.container(keyedBy: CodingKeys.self)
            name = try values.decode(String.self, forKey: .name)
            description = try values.decode(String.self, forKey: .description)
            
            try! super.init(from: decoder)
        }
    }
    
    class ViewModel : ObservableObject {
        let account = Account(client: client)
        let storage = Storage(client: client)
        let realtime = Realtime(client: client)
        
        @Published var downloadedImage: Image? = nil
        
        @Published public var username: String = "test@test.test"
        @Published public var password: String = "password"
        @Published public var fileId: String = "614c1f5864841"
        @Published public var collectionId: String = "6156aba4e2b84"
        @Published public var isShowPhotoLibrary = false
        @Published public var response: String = ""
        
        func register() {
            account.create(email: username, password: password) { result in
                switch result {
                case .failure(let error): self.response = error.message
                case .success(var response): self.response = response.body!.readString(length: response.body!.readableBytes) ?? ""
                }
            }
        }
        
        func login() {
            account.createSession(email: username, password: password) { result in
                switch result {
                case .failure(let error): self.response = error.message
                case .success(var response): self.response = response.body!.readString(length: response.body!.readableBytes) ?? ""
                }
            }
        }
        
        func loginWithFacebook() {
            account.createOAuth2Session(
                provider: "facebook",
                success: "\(host)/auth/oauth2/success",
                failure: "\(host)/auth/oauth2/failure"
            ) { result in
                switch result {
                case .failure: self.response = "false"
                case .success(let response): self.response = response.description
                }
            }
        }
        
        func download() {
            storage.getFileDownload(fileId: fileId) { result in
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
            
            storage.createFile(file: file) { result in
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
