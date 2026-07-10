import SwiftUI

struct ImagePicker {
    @Binding var selectedImage: OSImage
    
    @Environment(\.presentationMode) var presentationMode
}
