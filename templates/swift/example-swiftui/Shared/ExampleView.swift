import SwiftUI
import Appwrite
import NIO

struct ExampleView: View {
    
    @ObservedObject var viewModel: ViewModel
    
    @State var imageToUpload = OSImage()
    
    var body: some View {
        
        VStack(spacing: 8) {

            viewModel.downloadedImage?
                .resizable()
                .aspectRatio(contentMode: .fit)
                .frame(height: 200)

            TextField("", text: $viewModel.response)
                .padding()

            Button("Login") {
                Task { await viewModel.login() }
            }

            Button("Login with Facebook") {
                Task { await viewModel.loginWithFacebook() }
            }

            Button("Register") {
                Task { await viewModel.register() }
            }

            Button("Download image") {
                Task { await viewModel.download() }
            }
            
            Button("Upload image") {
                viewModel.isShowPhotoLibrary = true
            }
            
            Button("Subscribe") {
                viewModel.subscribe()
            }
        }
        #if os(macOS)
        .onChange(of: viewModel.isShowPhotoLibrary) { showing in
            ImagePicker.present()
        }
        #endif
        #if os(iOS)
        .sheet(isPresented: $viewModel.isShowPhotoLibrary) {
            ImagePicker(selectedImage: $imageToUpload)
        }
        #endif
        .onChange(of: imageToUpload) { img in
            Task { await viewModel.upload(image: img) }
        }
        .registerOAuthHandler()
    }
}

struct ExampleView_Previews: PreviewProvider {
    static var previews: some View {
        ExampleView(viewModel: ExampleView.ViewModel())
    }
}
