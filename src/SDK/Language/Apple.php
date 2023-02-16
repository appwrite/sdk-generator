<?php

namespace Appwrite\SDK\Language;

class Apple extends Swift
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Apple';
    }

    public function getFiles(): array
    {
        return \array_merge(parent::getFiles(), [
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/OAuth/WebAuthComponent.swift',
                'template'      => '/swift/Sources/OAuth/WebAuthComponent.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/OAuth/View+OAuth.swift',
                'template'      => '/swift/Sources/OAuth/View+OAuth.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Models/RealtimeModels.swift',
                'template'      => '/swift/Sources/Models/RealtimeModels.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Services/Realtime.swift',
                'template'      => '/swift/Sources/Services/Realtime.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebSockets/HTTPHandler.swift',
                'template'      => '/swift/Sources/WebSockets/HTTPHandler.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebSockets/MessageHandler.swift',
                'template'      => '/swift/Sources/WebSockets/MessageHandler.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebSockets/WebSocketClient.swift',
                'template'      => '/swift/Sources/WebSockets/WebSocketClient.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebSockets/WebSocketClientDelegate.swift',
                'template'      => '/swift/Sources/WebSockets/WebSocketClientDelegate.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebSockets/WebSocketClientError.swift',
                'template'      => '/swift/Sources/WebSockets/WebSocketClientError.swift.twig',
            ],
            // Config for project example-swiftui
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/iOS/Info.plist',
                'template'      => '/swift/example-swiftui/iOS/Info.plist',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/iOS/ImagePicker+iOS.swift',
                'template'      => '/swift/example-swiftui/iOS/ImagePicker+iOS.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/iOS/Keyboard.swift',
                'template'      => '/swift/example-swiftui/iOS/Keyboard.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/macOS/ImagePicker+macOS.swift',
                'template'      => '/swift/example-swiftui/macOS/ImagePicker+macOS.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/macOS/Info.plist',
                'template'      => '/swift/example-swiftui/macOS/Info.plist',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/macOS/macOS.entitlements',
                'template'      => '/swift/example-swiftui/macOS/macOS.entitlements',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/Assets.xcassets/AccentColor.colorset/Contents.json',
                'template'      => '/swift/example-swiftui/Shared/Assets.xcassets/AccentColor.colorset/Contents.json',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/Assets.xcassets/AppIcon.appiconset/Contents.json',
                'template'      => '/swift/example-swiftui/Shared/Assets.xcassets/AppIcon.appiconset/Contents.json',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/Assets.xcassets/Contents.json',
                'template'      => '/swift/example-swiftui/Shared/Assets.xcassets/Contents.json',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/Image/OSImage.swift',
                'template'      => '/swift/example-swiftui/Shared/Image/OSImage.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/ExampleApp.swift',
                'template'      => '/swift/example-swiftui/Shared/ExampleApp.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/ExampleView.swift',
                'template'      => '/swift/example-swiftui/Shared/ExampleView.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/ExampleViewModel.swift',
                'template'      => '/swift/example-swiftui/Shared/ExampleViewModel.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Shared/ImagePicker.swift',
                'template'      => '/swift/example-swiftui/Shared/ImagePicker.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Example.xcodeproj/project.xcworkspace/xcshareddata/IDEWorkspaceChecks.plist',
                'template'      => '/swift/example-swiftui/Example.xcodeproj/project.xcworkspace/xcshareddata/IDEWorkspaceChecks.plist',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/xcshareddata/swiftpm/Package.resolved',
                'template'      => '/swift/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/xcshareddata/swiftpm/Package.resolved',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Example.xcodeproj/xcshareddata/xcschemes/test (iOS).xcscheme',
                'template'      => '/swift/example-swiftui/Example.xcodeproj/xcshareddata/xcschemes/test (iOS).xcscheme',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Example.xcodeproj/xcshareddata/xcschemes/test (macOS).xcscheme',
                'template'      => '/swift/example-swiftui/Example.xcodeproj/xcshareddata/xcschemes/test (macOS).xcscheme',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Example.xcodeproj/xcshareddata/xcschemes/test (tvOS).xcscheme',
                'template'      => '/swift/example-swiftui/Example.xcodeproj/xcshareddata/xcschemes/test (tvOS).xcscheme',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Example.xcodeproj/xcshareddata/xcschemes/test (watchOS).xcscheme',
                'template'      => '/swift/example-swiftui/Example.xcodeproj/xcshareddata/xcschemes/test (watchOS).xcscheme',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Example.xcodeproj/project.pbxproj',
                'template'      => '/swift/example-swiftui/Example.xcodeproj/project.pbxproj',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Example.xcodeproj/project.xcworkspace/contents.xcworkspacedata',
                'template'      => '/swift/example-swiftui/Example.xcodeproj/project.xcworkspace/contents.xcworkspacedata',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Tests iOS/Info.plist',
                'template'      => '/swift/example-swiftui/Tests iOS/Info.plist',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Tests iOS/Tests_iOS.swift',
                'template'      => '/swift/example-swiftui/Tests iOS/Tests_iOS.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Tests macOS/Info.plist',
                'template'      => '/swift/example-swiftui/Tests macOS/Info.plist',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/Tests macOS/Tests_macOS.swift',
                'template'      => '/swift/example-swiftui/Tests macOS/Tests_macOS.swift',
            ],
            // Config for project example-uikit
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Assets.xcassets/AccentColor.colorset/Contents.json',
                'template'      => '/swift/example-uikit/UIKitExample/Assets.xcassets/AccentColor.colorset/Contents.json',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Assets.xcassets/AppIcon.appiconset/Contents.json',
                'template'      => '/swift/example-uikit/UIKitExample/Assets.xcassets/AppIcon.appiconset/Contents.json',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Assets.xcassets/Contents.json',
                'template'      => '/swift/example-uikit/UIKitExample/Assets.xcassets/Contents.json',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Base.lproj/LaunchScreen.storyboard',
                'template'      => '/swift/example-uikit/UIKitExample/Base.lproj/LaunchScreen.storyboard',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Base.lproj/Main.storyboard',
                'template'      => '/swift/example-uikit/UIKitExample/Base.lproj/Main.storyboard',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/AppDelegate.swift',
                'template'      => '/swift/example-uikit/UIKitExample/AppDelegate.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/ImagePicker.swift',
                'template'      => '/swift/example-uikit/UIKitExample/ImagePicker.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/Info.plist',
                'template'      => '/swift/example-uikit/UIKitExample/Info.plist',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/SceneDelegate.swift',
                'template'      => '/swift/example-uikit/UIKitExample/SceneDelegate.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample/ViewController.swift',
                'template'      => '/swift/example-uikit/UIKitExample/ViewController.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/xcshareddata/swiftpm/Package.resolved',
                'template'      => '/swift/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/xcshareddata/swiftpm/Package.resolved',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/xcshareddata/IDEWorkspaceChecks.plist',
                'template'      => '/swift/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/xcshareddata/IDEWorkspaceChecks.plist',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample.xcodeproj/project.pbxproj',
                'template'      => '/swift/example-uikit/UIKitExample.xcodeproj/project.pbxproj',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/contents.xcworkspacedata',
                'template'      => '/swift/example-uikit/UIKitExample.xcodeproj/project.xcworkspace/contents.xcworkspacedata',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExampleTests/Info.plist',
                'template'      => '/swift/example-uikit/UIKitExampleTests/Info.plist',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExampleTests/UIKitExampleTests.swift',
                'template'      => '/swift/example-uikit/UIKitExampleTests/UIKitExampleTests.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExampleUITests/Info.plist',
                'template'      => '/swift/example-uikit/UIKitExampleUITests/Info.plist',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-uikit/UIKitExampleUITests/UIKitExampleUITests.swift',
                'template'      => '/swift/example-uikit/UIKitExampleUITests/UIKitExampleUITests.swift',
            ],
        ]);
    }
}
