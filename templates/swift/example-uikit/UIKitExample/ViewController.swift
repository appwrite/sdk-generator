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
        .setEndpoint("https://localhost/v1")
        .setProject("613720f65c5fa")
        .setSelfSigned(true)

    lazy var account = Account(client: client)
    lazy var storage = Storage(client: client)
    
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
            "https://demo.appwrite.io/auth/oauth2/failure",
            completion: { result in
                var string: String = ""
                
                switch result {
                case .failure(let error): string = error.message
                case .success(let response): string = response.description
                }

                DispatchQueue.main.async {
                    self.text.text = string
                }
            }
        )
        
    }
    
    @IBAction func download(_ sender: Any) {
        storage.getFileDownload("60f7a0178c3e5", completion: { result in
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
        })
    }
    
    @IBAction func upload(_ sender: Any) {
        picker?.present()
        
    }

}

extension ViewController: ImagePickerDelegate {
    func didSelect(image: UIImage?) {
        var output = ""
        
        storage.createFile(ByteBuffer(data: image!.pngData()!), ["*"], ["61372ecd9a8f3"]) { result in
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
