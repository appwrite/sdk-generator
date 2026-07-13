<?php

declare(strict_types=1);

namespace Appwrite\SDK\Language;

use Override;

class ZedExtension extends Skills
{
    #[Override]
    public function getName(): string
    {
        return 'ZedExtension';
    }

    #[Override]
    public function getFiles(): array
    {
        return [
            [
                'scope'       => 'default',
                'destination' => 'extension.toml',
                'template'    => 'zed-extension/extension.toml.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => 'Cargo.toml',
                'template'    => 'zed-extension/Cargo.toml.twig',
            ],
            [
                'scope'       => 'copy',
                'destination' => 'Cargo.lock',
                'template'    => 'zed-extension/Cargo.lock',
            ],
            [
                'scope'       => 'copy',
                'destination' => 'docs/appwrite-mcp-working.png',
                'template'    => 'zed-extension/docs/appwrite-mcp-working.png',
            ],
            [
                'scope'       => 'copy',
                'destination' => 'docs/appwrite-mcp-write.png',
                'template'    => 'zed-extension/docs/appwrite-mcp-write.png',
            ],
            [
                'scope'       => 'default',
                'destination' => 'src/lib.rs',
                'template'    => 'zed-extension/src/lib.rs.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => 'README.md',
                'template'    => 'zed-extension/README.md.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => 'LICENSE',
                'template'    => 'plugin/LICENSE.twig',
            ],
        ];
    }
}
