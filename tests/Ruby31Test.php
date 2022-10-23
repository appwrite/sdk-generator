<?php

namespace Tests;

class Ruby31Test extends Base
{
    protected string $sdkName = 'ruby';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'ruby';
    protected string $version = '0.0.1';

    protected string $language = 'ruby';
    protected string $class = 'Appwrite\SDK\Language\Ruby';
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/ruby --env GEM_HOME=/app/vendor ruby:3.1-alpine sh -c "apk add git build-base && bundle install"',
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:3.1-alpine ruby tests/languages/ruby/tests.rb';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}
