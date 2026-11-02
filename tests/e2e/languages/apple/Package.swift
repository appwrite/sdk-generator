// swift-tools-version:6.1

import PackageDescription

let package = Package(
    name: "E2E",
    platforms: [
        .iOS("15.0"),
        .macOS("12.0"),
        .watchOS("8.0"),
        .tvOS("15.0"),
    ],
    dependencies: [
        .package(path: "../../sdks/apple-sdk"),
        .package(url: "https://github.com/apple/swift-nio.git", exact: "2.104.0"),
    ],
    targets: [
        .testTarget(
            name: "E2ETests",
            dependencies: [
                .product(name: "Appwrite", package: "apple-sdk"),
                .product(name: "NIO", package: "swift-nio"),
            ]
        ),
    ],
    swiftLanguageModes: [.v5]
)
