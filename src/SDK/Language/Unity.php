<?php

namespace Appwrite\SDK\Language;

use Override;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use RuntimeException;
use SplFileInfo;

class Unity extends DotNet
{
    #[Override]
    protected $params = [
        'packageName' => 'package-name',
    ];

    public function setPackageName(string $name): self
    {
        $this->setParam('packageName', $name);

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return 'Unity';
    }

    /**
     * Get Language Keywords List
     */
    #[Override]
    public function getKeywords(): array
    {
        $base = parent::getKeywords();
        $unity = [
            'GameObject',
            'MonoBehaviour',
            'Transform',
            'Component',
            'ScriptableObject',
            'UnityEngine',
            'UnityEditor',
        ];
        return array_values(array_unique(array_merge($base, $unity)));
    }

    #[Override]
    public function getFiles(): array
    {
        $files = [
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'unity/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'unity/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'unity/README.md.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'Assets/docs~/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'unity/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/package.json',
                'template'      => 'unity/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/{{ spec.title | caseUcfirst }}.asmdef',
                'template'      => 'unity/Assets/Runtime/Appwrite.asmdef.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/{{ spec.title | caseUcfirst }}.asmdef.meta',
                'template'      => 'unity/Assets/Runtime/Appwrite.asmdef.meta.twig',
            ],
            // Runtime
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/{{ spec.title | caseUcfirst }}Config.cs',
                'template'      => 'unity/Assets/Runtime/AppwriteConfig.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/{{ spec.title | caseUcfirst }}Manager.cs',
                'template'      => 'unity/Assets/Runtime/AppwriteManager.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Realtime.cs',
                'template'      => 'unity/Assets/Runtime/Realtime.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Channel.cs',
                'template'      => 'unity/Assets/Runtime/Channel.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Utilities/{{ spec.title | caseUcfirst }}Utilities.cs',
                'template'      => 'unity/Assets/Runtime/Utilities/AppwriteUtilities.cs.twig',
            ],
            // Runtime core
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/csc.rsp',
                'template'      => 'unity/Assets/Runtime/Core/csc.rsp',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/{{ spec.title | caseUcfirst }}.Core.asmdef',
                'template'      => 'unity/Assets/Runtime/Core/Appwrite.Core.asmdef.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/{{ spec.title | caseUcfirst }}.Core.asmdef.meta',
                'template'      => 'unity/Assets/Runtime/Core/Appwrite.Core.asmdef.meta.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Client.cs',
                'template'      => 'unity/Assets/Runtime/Core/Client.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/{{ spec.title | caseUcfirst }}Exception.cs',
                'template'      => 'dotnet/Package/Exception.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/ID.cs',
                'template'      => 'dotnet/Package/ID.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Permission.cs',
                'template'      => 'dotnet/Package/Permission.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Query.cs',
                'template'      => 'dotnet/Package/Query.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Role.cs',
                'template'      => 'dotnet/Package/Role.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Operator.cs',
                'template'      => 'dotnet/Package/Operator.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/CookieContainer.cs',
                'template'      => 'unity/Assets/Runtime/Core/CookieContainer.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Converters/ValueClassConverter.cs',
                'template'      => 'dotnet/Package/Converters/ValueClassConverter.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Converters/ObjectToInferredTypesConverter.cs',
                'template'      => 'dotnet/Package/Converters/ObjectToInferredTypesConverter.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Extensions/Extensions.cs',
                'template'      => 'dotnet/Package/Extensions/Extensions.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Models/OrderType.cs',
                'template'      => 'dotnet/Package/Models/OrderType.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Models/UploadProgress.cs',
                'template'      => 'dotnet/Package/Models/UploadProgress.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Models/InputFile.cs',
                'template'      => 'dotnet/Package/Models/InputFile.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Services/Service.cs',
                'template'      => 'dotnet/Package/Services/Service.cs.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'Assets/Runtime/Core/Services/{{service.name | caseUcfirst}}.cs',
                'template'      => 'unity/Assets/Runtime/Core/Services/ServiceTemplate.cs.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => 'Assets/Runtime/Core/Models/{{ definition.name | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'dotnet/Package/Models/Model.cs.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => 'Assets/Runtime/Core/Models/{{ requestModel.name | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'dotnet/Package/Models/RequestModel.cs.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'Assets/Runtime/Core/Enums/{{ enum.name | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'dotnet/Package/Enums/Enum.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/WebAuthComponent.cs',
                'template'      => 'unity/Assets/Runtime/Core/WebAuthComponent.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Enums/IEnum.cs',
                'template'      => 'dotnet/Package/Enums/IEnum.cs.twig',
            ],
            // Plugins
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/Microsoft.Bcl.AsyncInterfaces.dll',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/Microsoft.Bcl.AsyncInterfaces.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/Microsoft.Bcl.AsyncInterfaces.dll.meta',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/Microsoft.Bcl.AsyncInterfaces.dll.meta',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/System.IO.Pipelines.dll',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.IO.Pipelines.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/System.IO.Pipelines.dll.meta',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.IO.Pipelines.dll.meta',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/System.Runtime.CompilerServices.Unsafe.dll',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.Runtime.CompilerServices.Unsafe.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/System.Runtime.CompilerServices.Unsafe.dll.meta',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.Runtime.CompilerServices.Unsafe.dll.meta',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/System.Text.Encodings.Web.dll',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.Text.Encodings.Web.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/System.Text.Encodings.Web.dll.meta',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.Text.Encodings.Web.dll.meta',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Core/Plugins/Android/AndroidManifest.xml',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/Android/AndroidManifest.xml.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/WebGLCookies.jslib',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/WebGLCookies.jslib',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/System.Text.Json.dll',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.Text.Json.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/System.Text.Json.dll.meta',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.Text.Json.dll.meta',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/THIRD_PARTY_NOTICES.md',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/THIRD_PARTY_NOTICES.md',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/dependencies.lock.json',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/dependencies.lock.json',
            ],
            // Editor
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Editor/{{ spec.title | caseUcfirst }}.Editor.asmdef',
                'template'      => 'unity/Assets/Editor/Appwrite.Editor.asmdef.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Editor/{{ spec.title | caseUcfirst }}.Editor.asmdef.meta',
                'template'      => 'unity/Assets/Editor/Appwrite.Editor.asmdef.meta.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Editor/{{ spec.title | caseUcfirst }}SetupAssistant.cs',
                'template'      => 'unity/Assets/Editor/AppwriteSetupAssistant.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Editor/{{ spec.title | caseUcfirst }}SetupWindow.cs',
                'template'      => 'unity/Assets/Editor/AppwriteSetupWindow.cs.twig',
            ],
            // Samples
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Samples~/{{ spec.title | caseUcfirst }}Example/{{ spec.title | caseUcfirst }}Example.cs',
                'template'      => 'unity/Assets/Samples~/AppwriteExample/AppwriteExample.cs.twig',
            ],
            // Packages
            [
                'scope'         => 'copy',
                'destination'   => 'Packages/manifest.json',
                'template'      => 'unity/Packages/manifest.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Packages/packages-lock.json',
                'template'      => 'unity/Packages/packages-lock.json',
            ],
            // ProjectSettings
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/AudioManager.asset',
                'template'      => 'unity/ProjectSettings/AudioManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/boot.config',
                'template'      => 'unity/ProjectSettings/boot.config',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/ClusterInputManager.asset',
                'template'      => 'unity/ProjectSettings/ClusterInputManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/DynamicsManager.asset',
                'template'      => 'unity/ProjectSettings/DynamicsManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/EditorBuildSettings.asset',
                'template'      => 'unity/ProjectSettings/EditorBuildSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/EditorSettings.asset',
                'template'      => 'unity/ProjectSettings/EditorSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/GraphicsSettings.asset',
                'template'      => 'unity/ProjectSettings/GraphicsSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/InputManager.asset',
                'template'      => 'unity/ProjectSettings/InputManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/MemorySettings.asset',
                'template'      => 'unity/ProjectSettings/MemorySettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/NavMeshAreas.asset',
                'template'      => 'unity/ProjectSettings/NavMeshAreas.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/NetworkManager.asset',
                'template'      => 'unity/ProjectSettings/NetworkManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/PackageManagerSettings.asset',
                'template'      => 'unity/ProjectSettings/PackageManagerSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/Physics2DSettings.asset',
                'template'      => 'unity/ProjectSettings/Physics2DSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/PresetManager.asset',
                'template'      => 'unity/ProjectSettings/PresetManager.asset',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'ProjectSettings/ProjectSettings.asset',
                'template'      => 'unity/ProjectSettings/ProjectSettings.asset.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/ProjectVersion.txt',
                'template'      => 'unity/ProjectSettings/ProjectVersion.txt',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/QualitySettings.asset',
                'template'      => 'unity/ProjectSettings/QualitySettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/TagManager.asset',
                'template'      => 'unity/ProjectSettings/TagManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/TimeManager.asset',
                'template'      => 'unity/ProjectSettings/TimeManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/UnityConnectSettings.asset',
                'template'      => 'unity/ProjectSettings/UnityConnectSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/VersionControlSettings.asset',
                'template'      => 'unity/ProjectSettings/VersionControlSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/VFXManager.asset',
                'template'      => 'unity/ProjectSettings/VFXManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/XRSettings.asset',
                'template'      => 'unity/ProjectSettings/XRSettings.asset',
            ],
        ];

        $inTest = isset($GLOBALS['UNITY_TEST_MODE']) && $GLOBALS['UNITY_TEST_MODE'] === true;

        foreach ($files as &$file) {
            if (
                str_starts_with($file['destination'], 'Packages/')
                || str_starts_with($file['destination'], 'ProjectSettings/')
            ) {
                $file['test'] = true;
            }

            if (!$inTest && str_starts_with($file['destination'], 'Assets/')) {
                $file['destination'] = substr($file['destination'], \strlen('Assets/'));
            }
        }
        unset($file);

        // Filter out problematic files in test mode
        // Check if we're in test mode by looking for a global variable
        if ($inTest) {
            $excludeInTest = [
                'Assets/Runtime/Utilities/{{ spec.title | caseUcfirst }}Utilities.cs',
                'Assets/Runtime/{{ spec.title | caseUcfirst }}Config.cs',
                'Assets/Runtime/{{ spec.title | caseUcfirst }}Manager.cs',
                'Assets/Editor/{{ spec.title | caseUcfirst }}.Editor.asmdef',
                'Assets/Editor/{{ spec.title | caseUcfirst }}SetupAssistant.cs',
                'Assets/Editor/{{ spec.title | caseUcfirst }}SetupWindow.cs',
            ];

            $files = array_filter($files, fn(array $file): bool => !in_array($file['destination'], $excludeInTest));
        }

        return $files;
    }

    /**
     * Emit a .meta for every asset that lacks one.
     *
     * Immutable UPM packages need committed .meta files (Unity won't generate
     * them), else assets are ignored. GUIDs are path-derived for stable diffs;
     * existing metas (asmdef, .dll plugins) are left untouched.
     */
    public function postGenerate(string $target): void
    {
        if (isset($GLOBALS['UNITY_TEST_MODE']) && $GLOBALS['UNITY_TEST_MODE'] === true) {
            return;
        }

        if (!is_dir($target)) {
            return;
        }

        $normalizedTarget = rtrim(str_replace('\\', '/', $target), '/');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            /** @var SplFileInfo $item */
            $path     = $item->getPathname();
            $relative = ltrim(substr(str_replace('\\', '/', $path), strlen($normalizedTarget)), '/');

            if ($relative === '') {
                continue;
            }
            $skip = array_any(explode('/', $relative), fn($segment): bool => $segment === '' || $segment[0] === '.' || str_ends_with((string) $segment, '~'));
            if ($skip) {
                continue;
            }

            // Never emit a meta for a meta, and never clobber an existing one.
            if (str_ends_with($path, '.meta')) {
                continue;
            }

            $meta = $path . '.meta';
            if (file_exists($meta)) {
                continue;
            }

            if (file_put_contents($meta, $this->getMetaContents($relative, $item->isDir())) === false) {
                throw new RuntimeException("Failed to write meta file: {$meta}");
            }
        }
    }

    /**
     * Build the contents of a .meta file for a given asset.
     *
     * @param string $relativePath Package-relative path (forward slashes).
     * @param bool   $isDir        Whether the asset is a directory.
     */
    private function getMetaContents(string $relativePath, bool $isDir): string
    {
        $guid = md5($relativePath);

        if ($isDir) {
            $lines = [
                'fileFormatVersion: 2',
                "guid: {$guid}",
                'folderAsset: yes',
                'DefaultImporter:',
                '  externalObjects: {}',
                '  userData:',
                '  assetBundleName:',
                '  assetBundleVariant:',
            ];

            return implode("\n", $lines) . "\n";
        }

        // Plugins/Android/AndroidManifest.xml must import as an Android
        // plugin so Unity merges it into the build manifest; a plain
        // DefaultImporter would import it as an inert text asset.
        if (str_ends_with($relativePath, 'Plugins/Android/AndroidManifest.xml')) {
            return implode("\n", $this->pluginImporterMeta($guid, 'Android')) . "\n";
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        $lines = match ($extension) {
            'cs' => [
                'fileFormatVersion: 2',
                "guid: {$guid}",
                'MonoImporter:',
                '  externalObjects: {}',
                '  serializedVersion: 2',
                '  defaultReferences: []',
                '  executionOrder: 0',
                '  icon: {instanceID: 0}',
                '  userData:',
                '  assetBundleName:',
                '  assetBundleVariant:',
            ],
            // WebGL JavaScript plugin: enable on WebGL only so the cookie
            // shim is actually compiled into WebGL builds.
            'jslib' => $this->pluginImporterMeta($guid, 'WebGL'),
            default => [
                'fileFormatVersion: 2',
                "guid: {$guid}",
                'DefaultImporter:',
                '  externalObjects: {}',
                '  userData:',
                '  assetBundleName:',
                '  assetBundleVariant:',
            ],
        };

        return implode("\n", $lines) . "\n";
    }

    /**
     * Build PluginImporter meta lines enabling a single target platform
     * (and the editor stub), with every other platform disabled.
     *
     * @param string $platform Unity platform key (e.g. 'WebGL', 'Android').
     * @return array<string>
     */
    private function pluginImporterMeta(string $guid, string $platform): array
    {
        return [
            'fileFormatVersion: 2',
            "guid: {$guid}",
            'PluginImporter:',
            '  externalObjects: {}',
            '  serializedVersion: 2',
            '  iconMap: {}',
            '  executionOrder: {}',
            '  defineConstraints: []',
            '  isPreloaded: 0',
            '  isOverridable: 0',
            '  isExplicitlyReferenced: 0',
            '  validateReferences: 1',
            '  platformData:',
            '  - first:',
            '      Any:',
            '    second:',
            '      enabled: 0',
            '      settings: {}',
            '  - first:',
            '      Editor: Editor',
            '    second:',
            '      enabled: 0',
            '      settings:',
            '        DefaultValueInitialized: true',
            '  - first:',
            "      {$platform}: {$platform}",
            '    second:',
            '      enabled: 1',
            '      settings: {}',
            '  userData:',
            '  assetBundleName:',
            '  assetBundleVariant:',
        ];
    }
}
