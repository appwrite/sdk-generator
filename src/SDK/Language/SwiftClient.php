<?php

namespace Appwrite\SDK\Language;

class SwiftClient extends Swift {

    /**
     * @return string
     */
    public function getName()
    {
        return 'SwiftClient';
    }

    public function getFiles()
    {
        return \array_merge(parent::getFiles(), [
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/OAuth/WebAuthComponent.swift',
                'template'      => '/swift/Sources/OAuth/WebAuthComponent.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/OAuth/View+OAuth.swift',
                'template'      => '/swift/Sources/OAuth/View+OAuth.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Models/RealtimeModels.swift',
                'template'      => '/swift/Sources/Models/RealtimeModels.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Services/Realtime.swift',
                'template'      => '/swift/Sources/Services/Realtime.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebSockets/HTTPHandler.swift',
                'template'      => '/swift/Sources/WebSockets/HTTPHandler.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebSockets/MessageHandler.swift',
                'template'      => '/swift/Sources/WebSockets/MessageHandler.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebSockets/WebSocketClient.swift',
                'template'      => '/swift/Sources/WebSockets/WebSocketClient.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebSockets/WebSocketClientDelegate.swift',
                'template'      => '/swift/Sources/WebSockets/WebSocketClientDelegate.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebSockets/WebSocketClientError.swift',
                'template'      => '/swift/Sources/WebSockets/WebSocketClientError.swift.twig',
                'minify'        => false,
            ],
            // Config for project example-swiftui
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/iOS/Info.plist',
                'template'      => '/swift/example-swiftui/iOS/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/iOS/ImagePicker+iOS.swift',
                'template'      => '/swift/example-swiftui/iOS/ImagePicker+iOS.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/iOS/Keyboard.swift',
                'template'      => '/swift/example-swiftui/iOS/Keyboard.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/macOS/ImagePicker+macOS.swift',
                'template'      => '/swift/example-swiftui/macOS/ImagePicker+macOS.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/macOS/Info.plist',
                'template'      => '/swift/example-swiftui/macOS/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/macOS/macOS.entitlements',
                'template'      => '/swift/example-swiftui/macOS/macOS.entitlements',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/Assets.xcassets/AccentColor.colorset/Contents.json',
                'template'      => '/swift/example-swiftui/Shared/Assets.xcassets/AccentColor.colorset/Contents.json',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/Assets.xcassets/AppIcon.appiconset/Contents.json',
                'template'      => '/swift/example-swiftui/Shared/Assets.xcassets/AppIcon.appiconset/Contents.json',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/Assets.xcassets/Contents.json',
                'template'      => '/swift/example-swiftui/Shared/Assets.xcassets/Contents.json',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/Image/OSImage.swift',
                'template'      => '/swift/example-swiftui/Shared/Image/OSImage.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/ExampleApp.swift',
                'template'      => '/swift/example-swiftui/Shared/ExampleApp.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/ExampleView.swift',
                'template'      => '/swift/example-swiftui/Shared/ExampleView.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/ExampleViewModel.swift',
                'template'      => '/swift/example-swiftui/Shared/ExampleViewModel.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/ImagePicker.swift',
                'template'      => '/swift/example-swiftui/Shared/ImagePicker.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Example.xcodeproj/project.xcworkspace/xcshareddata/IDEWorkspaceChecks.plist',
                'template'      => '/swift/example-swiftui/Example.xcodeproj/project.xcworkspace/xcshareddata/IDEWorkspaceChecks.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Example.xcodeproj/project.pbxproj',
                'template'      => '/swift/example-swiftui/Example.xcodeproj/project.pbxproj',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Example.xcodeproj/project.xcworkspace/contents.xcworkspacedata',
                'template'      => '/swift/example-swiftui/Example.xcodeproj/project.xcworkspace/contents.xcworkspacedata',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Tests iOS/Info.plist',
                'template'      => '/swift/example-swiftui/Tests iOS/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Tests iOS/Tests_iOS.swift',
                'template'      => '/swift/example-swiftui/Tests iOS/Tests_iOS.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Tests macOS/Info.plist',
                'template'      => '/swift/example-swiftui/Tests macOS/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Tests macOS/Tests_macOS.swift',
                'template'      => '/swift/example-swiftui/Tests macOS/Tests_macOS.swift',
                'minify'        => false,
            ],
            // Config for project example-uikit
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Assets.xcassets/AccentColor.colorset/Contents.json',
                'template'      => '/swift/example-uikit/UIKitExample/Assets.xcassets/AccentColor.colorset/Contents.json',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Assets.xcassets/AppIcon.appiconset/Contents.json',
                'template'      => '/swift/example-uikit/UIKitExample/Assets.xcassets/AppIcon.appiconset/Contents.json',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Assets.xcassets/Contents.json',
                'template'      => '/swift/example-uikit/UIKitExample/Assets.xcassets/Contents.json',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Base.lproj/LaunchScreen.storyboard',
                'template'      => '/swift/example-uikit/UIKitExample/Base.lproj/LaunchScreen.storyboard',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Base.lproj/Main.storyboard',
                'template'      => '/swift/example-uikit/UIKitExample/Base.lproj/Main.storyboard',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/AppDelegate.swift',
                'template'      => '/swift/example-uikit/UIKitExample/AppDelegate.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/ImagePicker.swift',
                'template'      => '/swift/example-uikit/UIKitExample/ImagePicker.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Info.plist',
                'template'      => '/swift/example-uikit/UIKitExample/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/SceneDelegate.swift',
                'template'      => '/swift/example-uikit/UIKitExample/SceneDelegate.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/ViewController.swift',
                'template'      => '/swift/example-uikit/UIKitExample/ViewController.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/xcshareddata/swiftpm/Package.resolved',
                'template'      => '/swift/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/xcshareddata/swiftpm/Package.resolved',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/xcshareddata/IDEWorkspaceChecks.plist',
                'template'      => '/swift/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/xcshareddata/IDEWorkspaceChecks.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample.xcodeproj/project.pbxproj',
                'template'      => '/swift/example-uikit/UIKitExample.xcodeproj/project.pbxproj',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/contents.xcworkspacedata',
                'template'      => '/swift/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/contents.xcworkspacedata',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExampleTests/Info.plist',
                'template'      => '/swift/example-uikit/UIKitExampleTests/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExampleTests/UIKitExampleTests.swift',
                'template'      => '/swift/example-uikit/UIKitExampleTests/UIKitExampleTests.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExampleUITests/Info.plist',
                'template'      => '/swift/example-uikit/UIKitExampleUITests/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExampleUITests/UIKitExampleUITests.swift',
                'template'      => '/swift/example-uikit/UIKitExampleUITests/UIKitExampleUITests.swift',
                'minify'        => false,
            ],
        ]);
    }
}