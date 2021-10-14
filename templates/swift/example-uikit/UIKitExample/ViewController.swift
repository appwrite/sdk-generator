import UIKit
import NIO
import Appwrite

let host = "https://demo.appwrite.io/v1"

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
        .setProject("60f6a0d6e2a52")

    let COLLECTION_ID = "6155742223662"
    
    lazy var account = Account(client)
    lazy var storage = Storage(client)
    lazy var realtime = Realtime(client)
    lazy var database = Database(client)
    
    var picker: ImagePicker?
    
    required init?(coder: NSCoder) {
        super.init(coder: coder)
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        picker = ImagePicker(presentationController: self, delegate: self)
    }

    @IBAction func registerClick(_ sender: Any) {
        let disptch = DispatchGroup()
        disptch.enter()
        var string: String = ""
        
        account.create(email: "jake@appwrite.io", password: "password") { result in
            switch result {
            case .failure(let error): string = error.message
            case .success(var response): string = response.body!.readString(length: response.body!.readableBytes) ?? ""
            }
            disptch.leave()
        }
        disptch.wait()
        self.text.text = string
    }
    
    @IBAction func loginClick(_ sender: Any) {
        account.createSession(email: "jake@appwrite.io", password: "password") { result in
            var string: String = ""
            
            switch result {
            case .failure(let error): string = error.message
            case .success(var response):
                string = response.body!.readString(length: response.body!.readableBytes) ?? ""
            }

            DispatchQueue.main.async {
                self.text.text = string
            }
        }
    }
    
    @IBAction func loginWithFacebook(_ sender: UIButton) {
        account.createOAuth2Session(
            provider: "facebook",
            success: "https://demo.appwrite.io/auth/oauth2/success",
            failure: "https://demo.appwrite.io/auth/oauth2/failure") { result in
                var string: String = ""
                
                switch result {
                case .failure(let error): string = error.message
                case .success(let response): string = response.description
                }

                DispatchQueue.main.async {
                    self.text.text = string
                }
            }
        
    }
    
    @IBAction func download(_ sender: Any) {
        storage.getFileDownload(fileId: "614afaf579352") { result in
            switch result {
            case .failure(let error):
                DispatchQueue.main.async {
                    self.text.text = error.message
                }
            case .success(var response):
                let data = response.body!.readData(length: response.body!.readableBytes)!
                DispatchQueue.main.async {
                    self.image.image = UIImage(data: data)
                }
            }
        }
    }
    
    @IBAction func upload(_ sender: Any) {
        picker?.present()
        
    }
    
    @IBAction func subscribe(_ sender: Any) {
        _ = realtime.subscribe(channel:"collections.\(COLLECTION_ID).documents") { message in
            DispatchQueue.main.async {
                self.text.text = String(describing: message)
            }
        }
    }
    
    @IBAction func createDocument(_ sender: Any) {
        database.createDocument(
            collectionId: COLLECTION_ID,
            data: [
                "name": "Name \(Int.random(in: 0...Int.max))",
                "description": "Description \(Int.random(in: 0...Int.max))"
            ]
        ) { result in
            var string: String = ""
            
            switch result {
            case .failure(let error): string = error.message
            case .success(var response):
                string = response.body!.readString(length: response.body!.readableBytes) ?? ""
            }

            DispatchQueue.main.async {
                self.text.text = string
            }
        }
    }
    
}

extension ViewController: ImagePickerDelegate {
    func didSelect(image: UIImage?) {
        var output = ""
        
        let buffer = ByteBufferAllocator()
            .buffer(data: image!.jpegData(compressionQuality: 1)!)
        
        let file = File(name: "my_image.jpg", buffer: buffer)
        
        storage.createFile(file: file) { result in
            switch result {
            case .failure(let error):
                output = error.message
            case .success(var response):
                output = response.body!.readString(length: response.body!.readableBytes) ?? ""
            }
            
            DispatchQueue.main.async {
                self.text.text = output
            }
        }
    }
}
