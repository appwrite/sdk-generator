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
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'swift/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'swift/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'swift/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Package.swift',
                'template'      => 'apple/Package.swift.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'swift/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Tests/{{ spec.title | caseUcfirst}}Tests/Tests.swift',
                'template'      => 'swift/Tests/Tests.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Models/{{ spec.title | caseUcfirst}}Error.swift',
                'template'      => '/swift/Sources/Models/Error.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Models/Payload.swift',
                'template'      => 'swift/Sources/Models/Payload.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Permission.swift',
                'template'      => 'swift/Sources/Permission.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Role.swift',
                'template'      => 'swift/Sources/Role.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/ID.swift',
                'template'      => 'swift/Sources/ID.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Query.swift',
                'template'      => 'swift/Sources/Query.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Models/UploadProgress.swift',
                'template'      => 'swift/Sources/Models/UploadProgress.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Extensions/Codable+JSON.swift',
                'template'      => 'swift/Sources/Extensions/Codable+JSON.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Extensions/Cookie+Codable.swift',
                'template'      => 'swift/Sources/Extensions/Cookie+Codable.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Extensions/HTTPClientRequest+Cookies.swift',
                'template'      => 'swift/Sources/Extensions/HTTPClientRequest+Cookies.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Extensions/String+MimeTypes.swift',
                'template'      => 'swift/Sources/Extensions/String+MimeTypes.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/StreamingDelegate.swift',
                'template'      => 'swift/Sources/StreamingDelegate.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Services/Service.swift',
                'template'      => 'swift/Sources/Service.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/iOS/IOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/iOS/IOSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/iOS/UIDevice+ModelName.swift',
                'template'      => 'swift/Sources/DeviceInfo/iOS/UIDevice+ModelName.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/Linux/LinuxDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/Linux/LinuxDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/macOS/MacOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/macOS/MacOSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/watchOS/WatchOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/watchOS/WatchOSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/watchOS/WKInterfaceDevice+ModelName.swift',
                'template'      => 'swift/Sources/DeviceInfo/watchOS/WKInterfaceDevice+ModelName.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/macOS/CwlSysCtl.swift',
                'template'      => 'swift/Sources/DeviceInfo/macOS/CwlSysCtl.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/Windows/WindowsDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/Windows/WindowsDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/OSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/OSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/Apple/PackageInfo+Apple.swift',
                'template'      => 'swift/Sources/PackageInfo/Apple/PackageInfo+Apple.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/Linux/PackageInfo+Linux.swift',
                'template'      => 'swift/Sources/PackageInfo/Linux/PackageInfo+Linux.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/Windows/PackageInfo+Windows.swift',
                'template'      => 'swift/Sources/PackageInfo/Windows/PackageInfo+Windows.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/OSPackageInfo.swift',
                'template'      => 'swift/Sources/PackageInfo/OSPackageInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/PackageInfo.swift',
                'template'      => 'swift/Sources/PackageInfo/PackageInfo.swift',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Services/{{service.name | caseUcfirst}}.swift',
                'template'      => 'apple/Sources/Services/Service.swift.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Models/{{ definition.name | caseUcfirst }}.swift',
                'template'      => '/swift/Sources/Models/Model.swift.twig',
            ],
            [
                'scope' => 'enum',
                'destination' => '/Sources/{{ spec.title | caseUcfirst}}Enums/{{ enum.name | caseUcfirst }}.swift',
                'template' => '/swift/Sources/Enums/Enum.swift.twig',
            ],
            // Apple specific
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Client.swift',
                'template'      => '/apple/Sources/Client.swift.twig',
            ],
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
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Models/RealtimeModels.swift',
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
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/test (iOS).entitlements',
                'template'      => '/swift/example-swiftui/test (iOS).entitlements',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/test (tvOS).entitlements',
                'template'      => '/swift/example-swiftui/test (tvOS).entitlements',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-swiftui/test (watchOS).entitlements',
                'template'      => '/swift/example-swiftui/test (watchOS).entitlements',
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
        ];
    }

    protected function getReturnType(array $method, array $spec, string $generic = 'T'): string
    {
        if ($method['type'] === 'webAuth') {
            return 'Bool';
        }
        return parent::getReturnType($method, $spec, $generic);
    }
}
