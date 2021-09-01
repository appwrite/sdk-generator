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
        .setEndpoint("https://demo.appwrite.io/v1")
        .setProject("60f6a0d6e2a52")

    lazy var account = Account(client: client)
    lazy var storage = Storage(client: client)
    
    required init?(coder: NSCoder) {
        super.init(coder: coder)
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        registerOAuthHandler()
    }

    @IBAction func registerClick(_ sender: Any) {
        let disptch = DispatchGroup()
        disptch.enter()
        var string: String = ""
        account.create("test@email.com", "password") { result in
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
        storage.getFile("60f7a0178c3e5", completion: { result in
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
        let url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../resources/file.png")
        let buffer = ByteBuffer(data: url.dataRepresentation)
        var output = ""
        storage.createFile(buffer, ["*"], ["*"], completion: { result in
            switch result {
            case .failure(let error):
                output = error.message
            case .success(var response):
                output = response.body!.readString(length: response.body!.readableBytes) ?? ""
            }
            
            DispatchQueue.main.async {
                self.text.text = output
            }
        })
    }
}
