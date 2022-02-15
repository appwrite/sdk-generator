<?php

namespace Tests;

class RubyTest extends Base
{
    protected string $language = 'ruby';
    protected string $class = 'Appwrite\SDK\Language\Ruby';
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/ruby --env GEM_HOME=/app/vendor ruby:2.7-alpine sh -c "apk add git build-base && bundle install"',
    ];
    protected array $envs = [
        'ruby-2.7' => 'docker run --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:2.7-alpine ruby tests/languages/ruby/tests.rb',
        'ruby-3.0' => 'docker run --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:3.0-alpine ruby tests/languages/ruby/tests.rb',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES
    ];
}
