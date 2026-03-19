import UIKit
import NIO
import Appwrite

let host = "https://cloud.appwrite.io/v1"

class ViewController: UIViewController {

    @IBOutlet weak var text: UITextView!
    @IBOutlet weak var register: UIButton!
    @IBOutlet weak var loginButton: UIButton!
    @IBOutlet weak var loginWithFacebook: UIButton!
    @IBOutlet weak var downloadButton: UIButton!
    @IBOutlet weak var uploadButton: UIButton!
    @IBOutlet weak var image: UIImageView!
    
    let client = Client()
        .setEndpoint(host)
        .setProject("test")

    let collectionId = "test"
    var bucketId = "test"
    
    var fileId = "unique()"
    var documentId = "unique()"
    
    lazy var account = Account(client)
    lazy var storage = Storage(client)
    lazy var realtime = Realtime(client)
    lazy var database = Database(client)
    
    var response = ""
    
    var picker: ImagePicker?
    
    required init?(coder: NSCoder) {
        super.init(coder: coder)
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        picker = ImagePicker(presentationController: self, delegate: self)
    }

    @IBAction func registerClick(_ sender: Any) async {
        do {
            let response = try await account.create(
                userId: "unique()",
                email: "jake@appwrite.io",
                password: "password"
            )
            self.response = String(describing: response.toMap())
        } catch {
            self.response = String(describing: error)
        }
    }
    
    @IBAction func loginClick(_ sender: Any) async {
        do {
            let response = try await account.createEmailSession(
                email: "jake@appwrite.io",
                password: "password"
            )
            self.response = String(describing: response.toMap())
        } catch {
            self.response = String(describing: error)
        }
    }
    
    @IBAction func loginWithFacebook(_ sender: UIButton) async {
        do {
            let response = try await account.createOAuth2Session(
                provider: "facebook",
                success: "https://cloud.appwrite.io/auth/oauth2/success",
                failure: "https://cloud.appwrite.io/auth/oauth2/failure"
            )
            self.response = String(describing: response)
        } catch {
            self.response = String(describing: error)
        }
    }
    
    @IBAction func download(_ sender: Any) async {
        do {
            let response = try await storage.getFileDownload(
                bucketId: bucketId,
                fileId: fileId
            )
            let data = response.getData(at: 0, length: response.readableBytes)!
            self.image.image = UIImage(data: data)
        } catch {
            self.response = String(describing: error)
        }
    }
    
    @IBAction func upload(_ sender: Any) {
        picker?.present()
        
    }
    
    @IBAction func subscribe(_ sender: Any) {
        _ = realtime.subscribe(channel:"collections.\(collectionId).documents") { message in
            DispatchQueue.main.async {
                self.text.text = String(describing: message)
            }
        }
    }
    
    @IBAction func createDocument(_ sender: Any) async {
        do {
            let response = try await database.createDocument(
                collectionId: collectionId,
                documentId: documentId,
                data: [
                    "name": "Name \(Int.random(in: 0...Int.max))",
                    "description": "Description \(Int.random(in: 0...Int.max))"
                ],
                read: ["role:all"],
                write: []
            )
            self.response = String(describing: response)
        } catch {
            self.response = String(describing: error)
        }
    }
    
}

extension ViewController: ImagePickerDelegate {
    func didSelect(image: UIImage?) async {
        let buffer = ByteBufferAllocator()
            .buffer(data: image!.jpegData(compressionQuality: 1)!)
        
        let file = File(name: "my_image.jpg", buffer: buffer)
        
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
}
