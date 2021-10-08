//
//  File.swift
//  
//
//  Created by Jake Barnby on 8/10/21.
//

import Foundation


public class OSShell {
    
    private init(){}
    
    public static func exec(_ command: String) -> String {
        let task = Process()
        let pipe = Pipe()
        
        task.standardOutput = pipe
        task.standardError = pipe
        
        #if os(macOS)
        task.launchPath = "/bin/zsh"
        task.arguments = ["-c", command]
        #elseif os(Linux)
        task.launchPath = "/bin/bash"
        task.arguments = ["-c", command]
        #elseif os(Windows)
        task.launchPath = "cmd.exe"
        task.arguments = [command]
        #endif
        task.launch()
        
        let data = pipe.fileHandleForReading.readDataToEndOfFile()
        let output = String(data: data, encoding: .utf8)!
        
        return output
    }
}
