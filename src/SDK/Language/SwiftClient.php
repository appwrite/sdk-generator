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
        return array_merge(parent::getFiles(), [
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebAuthComponent.swift',
                'template'      => '/swift/Sources/WebAuthComponent.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/{{ spec.title | caseUcfirst}}Error.swift',
                'template'      => '/swift/Sources/Error.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/View+OAuth.swift',
                'template'      => '/swift/Sources/View+OAuth.swift.twig',
                'minify'        => false,
            ],
            // Config for project :example
            [
                'scope'         => 'default',
                'destination'   => '/example/iOS/Info.plist',
                'template'      => '/swift/example/iOS/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/macOS/Info.plist',
                'template'      => '/swift/example/macOS/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/macOS/macOS.entitlements',
                'template'      => '/swift/example/macOS/macOS.entitlements',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Shared/Assets.xcassets/AccentColor.colorset/Contents.json',
                'template'      => '/swift/example/Shared/Assets.xcassets/AccentColor.colorset/Contents.json',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Shared/Assets.xcassets/AppIcon.appiconset/Contents.json',
                'template'      => '/swift/example/Shared/Assets.xcassets/AppIcon.appiconset/Contents.json',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Shared/Assets.xcassets/Contents.json',
                'template'      => '/swift/example/Shared/Assets.xcassets/Contents.json',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Shared/ContentView.swift',
                'template'      => '/swift/example/Shared/ContentView.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Shared/ExampleApp.swift',
                'template'      => '/swift/example/Shared/ExampleApp.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Shared/Keyboard.swift',
                'template'      => '/swift/example/Shared/Keyboard.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Example.xcodeproj/project.xcworkspace/xcshareddata/swiftpm/Package.resolved',
                'template'      => '/swift/example/Example.xcodeproj/project.xcworkspace/xcshareddata/swiftpm/Package.resolved',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Example.xcodeproj/project.xcworkspace/xcshareddata/IDEWorkspaceChecks.plist',
                'template'      => '/swift/example/Example.xcodeproj/project.xcworkspace/xcshareddata/IDEWorkspaceChecks.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Example.xcodeproj/project.pbxproj',
                'template'      => '/swift/example/Example.xcodeproj/project.pbxproj',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Example.xcodeproj/project.xcworkspace/contents.xcworkspacedata',
                'template'      => '/swift/example/Example.xcodeproj/project.xcworkspace/contents.xcworkspacedata',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Tests iOS/Info.plist',
                'template'      => '/swift/example/Tests iOS/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Tests iOS/Tests_iOS.swift',
                'template'      => '/swift/example/Tests iOS/Tests_iOS.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Tests macOS/Info.plist',
                'template'      => '/swift/example/Tests macOS/Info.plist',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/Tests macOS/Tests_macOS.swift',
                'template'      => '/swift/example/Tests macOS/Tests_macOS.swift',
                'minify'        => false,
            ],
        ]);
    }
}