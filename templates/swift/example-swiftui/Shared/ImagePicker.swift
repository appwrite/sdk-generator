//
//  ImagePicker.swift
//  test (iOS)
//
//  Created by Jake Barnby on 23/09/21.
//
import SwiftUI

struct ImagePicker {
    @Binding var selectedImage: OSImage
    
    @Environment(\.presentationMode) var presentationMode
}
