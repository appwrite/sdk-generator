<?php

declare(strict_types=1);

namespace Tests;

use Override;
use Appwrite\SDK\Language\Go;

final class Go112Test extends Base
{
    #[Override]
    protected string $sdkName = 'go';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'go';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'go';
    #[Override]
    protected string $class = Go::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/tmp/go/src/github.com/repoowner/reponame',
        'cp -Rf tests/sdks/go/* tests/tmp/go/src/github.com/repoowner/reponame/'
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app golang:1.12 sh -c "cd tests/languages/go/ && ./test.sh"';
    #[Override]
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES,
    ];
}
