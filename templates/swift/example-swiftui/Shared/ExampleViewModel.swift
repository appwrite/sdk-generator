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
        @Published public var databaseId: String = "test"
        @Published public var collectionId: String = "test"
        @Published public var isShowPhotoLibrary = false
        @Published public var response: String = ""
        
        func register() async {
            do {
                let user = try await account.create(
                    userId: userId,
                    email: username,
                    password: password
                )
                self.userId = user.id
                self.response = String(describing: user.toMap())
            } catch {
                self.response = error.localizedDescription
            }
        }
        
        func login() async {
            do {
                let session = try await account.createEmailSession(
                    email: username,
                    password: password
                )
                self.response = String(describing: session.toMap())
            } catch {
                self.response = error.localizedDescription
            }
        }
        
        func loginWithFacebook() async {
            do {
                _ = try await account.createOAuth2Session(provider: "facebook")

                self.response = "Success!"
            } catch {
                self.response = error.localizedDescription
            }
        }
        
        func download() async {
            do {
                let data = try await storage.getFileDownload(
                    bucketId: bucketId,
                    fileId: fileId
                )
                self.downloadedImage = Image(data: Data(buffer: data))
            } catch {
                self.response = error.localizedDescription
            }
        }
        
        func upload(image: OSImage) async {
            #if os(macOS)
            let fileName = "file.tiff"
            let mime = "image/tiff"
            #else
            let fileName = "file.png"
            let mime = "image/png"
            #endif
            
            let file = InputFile.fromData(
                image.data,
                filename: fileName,
                mimeType: mime
            )
            
            do {
                let file = try await storage.createFile(
                    bucketId: bucketId,
                    fileId: fileId,
                    file: file
                )
                self.response = String(describing: file.toMap())
            } catch {
                self.response = error.localizedDescription
            }
        }
        
        func subscribe() {
            _ = realtime.subscribe(channels: ["databases.\(databaseId).collections.\(collectionId).documents"]) { event in
                DispatchQueue.main.async {
                    self.response = String(describing: event.payload!)
                }
            }
        }
    }
}
