<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Ruby;

final class Ruby27Test extends Base
{
    #[Override]
    protected string $sdkName = 'ruby';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'ruby';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'ruby';
    #[Override]
    protected string $class = Ruby::class;
    #[Override]
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/ruby --env GEM_HOME=/app/vendor ruby:2.7-alpine sh -c "apk add git build-base && bundle install"',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:2.7-alpine ruby tests/e2e/languages/ruby/tests.rb';

    #[Override]
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}
