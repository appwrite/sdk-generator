FROM composer:2.10 AS composer

LABEL maintainer="team@appwrite.io"

ARG TESTING=false
ENV TESTING=$TESTING

WORKDIR /usr/local/src/

COPY composer.lock /usr/local/src/
COPY composer.json /usr/local/src/

RUN set -eu; \
    extra=""; \
    if [ "${TESTING}" != "true" ]; then extra="--no-dev"; fi; \
    n=0; \
    until composer install --ignore-platform-reqs --optimize-autoloader \
        --no-plugins --no-scripts --prefer-dist --no-interaction ${extra}; do \
        n=$((n + 1)); \
        if [ "${n}" -ge 5 ]; then exit 1; fi; \
        echo "composer install failed, retrying ${n}/5..."; \
        sleep $((n * 5)); \
    done

FROM composer:2.10 AS composer_mqtt

# app/mqtt.php (the mock MQTT broker) has its own dependency set: utopia-php/mqtt needs
# utopia-php/telemetry ^0.4, which conflicts with the HTTP mock's utopia-php/framework (it
# pins the older telemetry line and only that version carries the param(model:) the mock
# uses). So it can't share the vendor above and is installed separately into ./vendor-mqtt.
# The manifest is inlined here (no committed composer.mqtt.json/.lock); the broker is
# test-only infra, so an unlocked resolve of a pinned utopia-php/mqtt tag is fine.
WORKDIR /usr/local/src/

RUN set -eu; \
    printf '%s' '{"repositories":[{"type":"vcs","url":"https://github.com/utopia-php/mqtt"}],"require":{"utopia-php/mqtt":"0.0.1"},"minimum-stability":"dev","prefer-stable":true,"config":{"vendor-dir":"vendor-mqtt","allow-plugins":{"php-http/discovery":true,"tbachert/spi":true}}}' > composer.json; \
    n=0; \
    until composer update --ignore-platform-reqs --optimize-autoloader \
        --no-plugins --no-scripts --prefer-dist --no-interaction; do \
        n=$((n + 1)); \
        if [ "${n}" -ge 5 ]; then exit 1; fi; \
        echo "composer update (mqtt) failed, retrying ${n}/5..."; \
        sleep $((n * 5)); \
    done

FROM phpswoole/swoole:6.2.2-php8.4-alpine AS final

RUN apk add docker

ENV _APP_REDIS_HOST=redis \
    _APP_REDIS_PORT=6379

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /usr/src/code

COPY --from=composer /usr/local/src/vendor /usr/src/code/vendor
COPY --from=composer_mqtt /usr/local/src/vendor-mqtt /usr/src/code/vendor-mqtt

# Add Source Code
COPY ./src /usr/src/code/src
COPY ./app /usr/src/code/app

EXPOSE 80

CMD ["php", "app/http.php"]
