//
//  ViewController.swift
//  UIKitExample
//
//  Created by Jake Barnby on 10/08/21.
//
//

import UIKit
import NIO
import Appwrite

class ViewController: UIViewController {

    @IBOutlet weak var text: UITextView!
    @IBOutlet weak var register: UIButton!
    @IBOutlet weak var loginButton: UIButton!
    @IBOutlet weak var loginWithFacebook: UIButton!
    @IBOutlet weak var downloadButton: UIButton!
    @IBOutlet weak var uploadButton: UIButton!
    @IBOutlet weak var image: UIImageView!
    
    let client = Client()
        .setEndpoint("http://192.168.20.6:80/v1")
        .setProject("613b18dabf74a")
        .setSelfSigned(true)

    let COLLECTION_ID = "6149afd52ce3b"
    
    lazy var account = Account(client: client)
    lazy var storage = Storage(client: client)
    lazy var realtime = Realtime(client: client)
    lazy var database = Database(client: client)
    
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
        
        account.create("jake@appwrite.io", "password") { result in
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
        account.createSession("jake@appwrite.io", "password") { result in
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
            "facebook",
            "https://demo.appwrite.io/auth/oauth2/success",
            "https://demo.appwrite.io/auth/oauth2/failure") { result in
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
        storage.getFileDownload("614afaf579352") { result in
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
        _ = realtime.subscribe(channels:["collections.\(COLLECTION_ID).documents"], payloadType: Room.self) { message in
            print(message)
        }
    }
    
    @IBAction func createDocument(_ sender: Any) {
        database.createDocument(COLLECTION_ID, [
            "name": "Name \(Int.random(in: 0...Int.max))",
            "description": "Description \(Int.random(in: 0...Int.max))"
        ], ["*"], ["*"]) { result in
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
        
        storage.createFile(file, ["*"], ["*"]) { result in
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

class Room : Model {
}
