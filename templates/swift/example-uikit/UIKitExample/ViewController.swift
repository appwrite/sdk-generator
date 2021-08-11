//
//  ViewController.swift
//  UIKitExample
//
//  Created by Jake Barnby on 10/08/21.
//
//

import UIKit
import Appwrite

class ViewController: UIViewController {

    @IBOutlet weak var text: UITextView!
    @IBOutlet weak var register: UIButton!
    @IBOutlet weak var loginButton: UIButton!
    @IBOutlet weak var loginWithFacebook: UIButton!
    @IBOutlet weak var downloadButton: UIButton!
    
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
    
    @IBAction func downloadClick(_ sender: Any) {
    }
    
}
