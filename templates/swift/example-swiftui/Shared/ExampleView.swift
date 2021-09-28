//
//  ContentView.swift
//  Shared
//
//  Created by Jake Barnby on 3/08/21.
//
//

import SwiftUI
import Appwrite
import NIO

let host = "http://localhost:80/v1"
let projectId = "613b18dabf74a"

let client = Client()
    .setEndpoint(host)
    .setProject(projectId)
    .setSelfSigned()

struct ExampleView: View {
    
    @ObservedObject var viewModel: ViewModel
    
    @State var imageToUpload = UIImage()
    
    @ObservedObject var keyboard: Keyboard = .init()
    
    var body: some View {
        
        VStack(spacing: 8) {

            viewModel.downloadedImage?
                .resizable()
                .aspectRatio(contentMode: .fit)
                .frame(height: 200)

            TextEditor(text: viewModel.$response)
                .padding()
                .padding(.bottom, keyboard.height)
                .edgesIgnoringSafeArea(keyboard.height > 0 ? .bottom : [])

            Button("Login") {
                viewModel.login()
            }

            Button("Login with Facebook") {
                viewModel.loginWithFacebook()
            }

            Button("Register") {
                viewModel.register()
            }

            Button("Download image") {
                viewModel.download()
            }
            
            Button("Upload image") {
                viewModel.isShowPhotoLibrary = true
            }
            
            Button("Subscribe") {
                viewModel.subscribe()
            }
        }
        .sheet(isPresented: viewModel.$isShowPhotoLibrary) {
            ImagePicker(sourceType: .photoLibrary, selectedImage: $imageToUpload)
        }
        .onChange(of: imageToUpload) { img in
            viewModel.upload(image: img)
        }
        .registerOAuthHandler()
    }

}

struct ExampleView_Previews: PreviewProvider {
    static var previews: some View {
        ExampleView(viewModel: ExampleView.ViewModel())
    }
}
