<?php

namespace Appwrite\SDK\Language;

class Unity extends DotNet
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Unity';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
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

    /**
     * @return array
     */
    public function getFiles(): array
    {
        $files = [
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'unity/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/icon.png',
                'template'      => 'unity/icon.png',
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
            // Appwrite
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
            // Appwrite.Core
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
                'destination'   => 'Assets/Runtime/Core/Plugins/System.IO.Pipelines.dll',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.IO.Pipelines.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/System.Runtime.CompilerServices.Unsafe.dll',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.Runtime.CompilerServices.Unsafe.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/System.Text.Encodings.Web.dll',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/System.Text.Encodings.Web.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Runtime/Core/Plugins/Android/AndroidManifest.xml',
                'template'      => 'unity/Assets/Runtime/Core/Plugins/Android/AndroidManifest.xml',
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
            // Appwrite.Editor
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Editor/{{ spec.title | caseUcfirst }}.Editor.asmdef',
                'template'      => 'unity/Assets/Editor/Appwrite.Editor.asmdef.twig',
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
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/ProjectSettings.asset',
                'template'      => 'unity/ProjectSettings/ProjectSettings.asset',
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

        // Filter out problematic files in test mode
        // Check if we're in test mode by looking for a global variable
        if (isset($GLOBALS['UNITY_TEST_MODE']) && $GLOBALS['UNITY_TEST_MODE'] === true) {
            $excludeInTest = [
                'Assets/Runtime/Utilities/{{ spec.title | caseUcfirst }}Utilities.cs',
                'Assets/Runtime/{{ spec.title | caseUcfirst }}Config.cs',
                'Assets/Runtime/{{ spec.title | caseUcfirst }}Manager.cs',
                'Assets/Editor/{{ spec.title | caseUcfirst }}.Editor.asmdef',
                'Assets/Editor/{{ spec.title | caseUcfirst }}SetupAssistant.cs',
                'Assets/Editor/{{ spec.title | caseUcfirst }}SetupWindow.cs',
            ];

            $files = array_filter($files, function ($file) use ($excludeInTest): bool {
                return !in_array($file['destination'], $excludeInTest);
            });
        }

        return $files;
    }
}
