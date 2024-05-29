import Foundation
import SwiftUI
import Appwrite
import NIO

extension ExampleView {

    class ViewModel : ObservableObject {

        @Published var downloadedImage: Image? = nil
        @Published public var username: String = "test@test.test"
        @Published public var password: String = "password"
        @Published public var userId: String = "unique()"
        @Published public var bucketId: String = "test"
        @Published public var fileId: String = "test"
        @Published public var databaseId: String = "test"
        @Published public var collectionId: String = "test"
        @Published public var collectionId2: String = "test2"
        @Published public var isShowPhotoLibrary = false
        @Published public var response: String = ""
        @Published public var response2: String = ""

        func register() async {
            do {
                let user = try await account.create(
                    userId: userId,
                    email: username,
                    password: password
                )
                self.userId = user.id

                DispatchQueue.main.async {
                    self.response = String(describing: user.toMap())
                }
            } catch {
                DispatchQueue.main.async {
                    self.response = error.localizedDescription
                }
            }
        }

        func login() async {
            do {
                let session = try await account.createEmailPasswordSession(
                    email: username,
                    password: password
                )

                guard let token = UserDefaults.standard.string(forKey: "fcmToken") else {
                    return
                }

                guard let target = try? await account.createPushTarget(
                    targetId: ID.unique(),
                    identifier: token
                ) else {
                    return
                }

                UserDefaults.standard.set(target.id, forKey: "targetId")

                DispatchQueue.main.async {
                    self.response = String(describing: session.toMap())
                }
            } catch {
                DispatchQueue.main.async {
                    self.response = error.localizedDescription
                }
            }
        }

        func loginWithFacebook() async {
            do {
                _ = try await account.createOAuth2Session(provider: .facebook)

                DispatchQueue.main.async {
                    self.response = "Success!"
                }
            } catch {
                DispatchQueue.main.async {
                    self.response = error.localizedDescription
                }
            }
        }

        func download() async {
            do {
                let data = try await storage.getFileDownload(
                    bucketId: bucketId,
                    fileId: fileId
                )
                DispatchQueue.main.async {
                    self.downloadedImage = Image(data: Data(buffer: data))
                }
            } catch {
                DispatchQueue.main.async {
                    self.response = error.localizedDescription
                }
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
                DispatchQueue.main.async {
                    self.response = String(describing: file.toMap())
                }
            } catch {
                DispatchQueue.main.async {
                    self.response = error.localizedDescription
                }
            }
        }

        func subscribe() async {
            let sub1 = try? await realtime.subscribe(channels: ["databases.\(databaseId).collections.\(collectionId).documents"]) { event in
                DispatchQueue.main.async {
                    self.response = String(describing: event.payload!)
                }
            }

            try? await Task.sleep(nanoseconds: UInt64(500_000_000))

            _ = try? await realtime.subscribe(channels: ["databases.\(databaseId).collections.\(collectionId2).documents"]) { event in
                DispatchQueue.main.async {
                    self.response2 = String(describing: event.payload!)
                }
            }

            try? await Task.sleep(nanoseconds: UInt64(500_000_000))

            try? await sub1?.close()
        }
    }
}
