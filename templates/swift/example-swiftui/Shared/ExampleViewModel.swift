import Foundation
import SwiftUI
import Appwrite
import NIO

let host = "https://localhost/v1"
let projectId = "60f6a0d6e2a52"

extension ExampleView {
    
    class ViewModel : ObservableObject {

        let client = Client()
            .setEndpoint(endPoint: host)
            .setProject(value: projectId)
        
        lazy var account = Account(client: client)
        lazy var storage = Storage(client: client)
        lazy var realtime = Realtime(client: client)
        
        @Published var downloadedImage: Image? = nil

        @Published public var username: String = "test@test.test"
        @Published public var password: String = "password"
        @Published public var fileId: String = "60f7a0178c3e5"
        @Published public var collectionId: String = "6155742223662"
        @Published public var isShowPhotoLibrary = false
        @Published public var response: String = ""
        
        func register() {
            account.create(email: username, password: password) { result in
                DispatchQueue.main.async {
                    switch result {
                    case .failure(let error):
                        self.response = error.message
                    case .success(var response):
                        self.response = response.body!.readString(length: response.body!.readableBytes) ?? ""
                    }
                }
            }
        }
        
        func login() {
            account.createSession(email: username, password: password) { result in
                DispatchQueue.main.async {
                    switch result {
                    case .failure(let error):
                        self.response = error.message
                    case .success(var response):
                        self.response = response.body!.readString(length: response.body!.readableBytes) ?? ""
                    }
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
                DispatchQueue.main.async {
                    switch result {
                    case .failure(let error): self.response = error.message
                    case .success(var response):
                        self.downloadedImage = Image(
                            data: response.body!.readData(
                                length: response.body!.readableBytes)!)
                    }
                }
            }
        }
        
        func upload(image: OSImage) {
            let imageBuffer = ByteBufferAllocator()
                .buffer(data: image.data)
                
            #if os(macOS)
            let fileName = "file.tiff"
            #else
            let fileName = "file.png"
            #endif
            
            let file = File(
                name: fileName,
                buffer: imageBuffer
            )
            
            storage.createFile(file: file) { result in
                DispatchQueue.main.async {
                    switch result {
                    case .failure(let error):
                        self.response = error.message
                    case .success(var response):
                        self.response = response.body!.readString(length: response.body!.readableBytes) ?? ""
                    }
                }
            }
        }
        
        func subscribe() {
            _ = realtime.subscribe(channels: ["collections.\(collectionId).documents"]) { response in
                DispatchQueue.main.async {
                    self.response = String(describing: response.payload!)
                }
            }
        }
    }
}
