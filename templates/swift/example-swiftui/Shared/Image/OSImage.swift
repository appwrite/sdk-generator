//
//  OSImage.swift
//  Example
//
//  Created by Jake on 4/10/21.
//
import SwiftUI

#if os(macOS)
import AppKit
public typealias OSImage = NSImage
#elseif os(iOS) || os(tvOS) || os(watchOS)
import UIKit
public typealias OSImage = UIImage
#endif

extension Image {
    public init(data: Data) {
        #if os(macOS)
        self.init(nsImage: NSImage(data: data)!)
        #elseif os(iOS) || os(tvOS) || os(watchOS)
        self.init(uiImage: UIImage(data: data)!)
        #endif
    }
}

extension OSImage {
    public var data: Data {
        #if os(macOS)
        return self.tiffRepresentation!
        #elseif os(iOS) || os(tvOS) || os(watchOS)
        return self.pngData()!
        #endif
    }
}
