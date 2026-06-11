<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Go;

final class Go118Test extends Base
{
    #[Override]
    protected string $sdkName = 'go';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'go';
    #[Override]
    protected string $version = '2.0.0';

    #[Override]
    protected string $language = 'go';
    #[Override]
    protected string $class = Go::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/e2e/tmp/go/src/github.com/repoowner/reponame/v2',
        'cp -Rf tests/e2e/sdks/go/* tests/e2e/tmp/go/src/github.com/repoowner/reponame/v2/'
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app golang:1.18 sh -c "cd tests/e2e/languages/go-v2/ && ./test.sh"';
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
