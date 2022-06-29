import Foundation
import SwiftUI
import Appwrite
import NIO

let host = "https://localhost/v1"
let projectId = "test"

extension ExampleView {
    
    class ViewModel : ObservableObject {

        let client = Client()
            .setEndpoint(host)
            .setProject(projectId)
        
        lazy var account = Account(client)
        lazy var storage = Storage(client)
        lazy var realtime = Realtime(client)
        
        @Published var downloadedImage: Image? = nil

        @Published public var username: String = "test@test.test"
        @Published public var password: String = "password"
        @Published public var userId: String = "unique()"
        @Published public var bucketId: String = "test"
        @Published public var fileId: String = "test"
        @Published public var collectionId: String = "test"
        @Published public var isShowPhotoLibrary = false
        @Published public var response: String = ""
        
        func register() async {
            do {
                let response = try await account.create(
                    userId: userId,
                    email: username,
                    password: password
                )
                self.userId = response.id
                self.response = String(describing: response.toMap())
            } catch {
                self.response = String(describing: error)
            }
        }
        
        func login() async {
            do {
                let response = try await account.createSession(
                    email: username,
                    password: password
                )
                self.response = String(describing: response.toMap())
            } catch {
                self.response = String(describing: error)
            }
        }
        
        func loginWithFacebook() async {
            do {
                let response = try await account.createOAuth2Session(
                    provider: "facebook",
                    success: "\(host)/auth/oauth2/success",
                    failure: "\(host)/auth/oauth2/failure"
                )
                self.response = String(describing: response)
            } catch {
                self.response = String(describing: error)
            }
        }
        
        func download() async {
            do {
                let response = try await storage.getFileDownload(
                    bucketId: bucketId,
                    fileId: fileId
                )
                self.downloadedImage = Image(data: Data(buffer: response))
            } catch {
                self.response = String(describing: error)
            }
        }
        
        func upload(image: OSImage) async {
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
            
            do {
                let response = try await storage.createFile(
                    bucketId: bucketId,
                    fileId: fileId,
                    file: file
                )
                self.response = String(describing: response.toMap())
            } catch {
                self.response = String(describing: error)
            }
        }
        
        func subscribe() {
            _ = realtime.subscribe(channels: ["collections.\(collectionId).documents"]) { event in
                DispatchQueue.main.async {
                    self.response = String(describing: event.payload!)
                }
            }
        }
    }
}
