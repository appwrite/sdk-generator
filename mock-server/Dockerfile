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

FROM phpswoole/swoole:6.2.2-php8.4-alpine AS final

RUN apk add docker

ENV _APP_REDIS_HOST=redis \
    _APP_REDIS_PORT=6379

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /usr/src/code

COPY --from=composer /usr/local/src/vendor /usr/src/code/vendor

# Add Source Code
COPY ./src /usr/src/code/src
COPY ./app /usr/src/code/app

EXPOSE 80

CMD ["php", "app/http.php"]
