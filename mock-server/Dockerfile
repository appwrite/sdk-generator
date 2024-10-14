FROM composer:2.0 as composer

LABEL maintainer="team@appwrite.io"

ARG TESTING=false
ENV TESTING=$TESTING

WORKDIR /usr/local/src/

COPY composer.lock /usr/local/src/
COPY composer.json /usr/local/src/

RUN composer install --ignore-platform-reqs --optimize-autoloader \
    --no-plugins --no-scripts --prefer-dist \
    `if [ "$TESTING" != "true" ]; then echo "--no-dev"; fi`

FROM phpswoole/swoole:5.1.2-php8.3-alpine as final

RUN apk add docker

ENV _APP_REDIS_HOST=redis \
    _APP_REDIS_PORT=6379

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /usr/src/code

COPY --from=composer /usr/local/src/vendor /usr/src/code/vendor

# Add Source Code
COPY ./src /usr/src/code/src
COPY ./app /usr/src/code/app
COPY ./resources /usr/src/code/resources

EXPOSE 80

CMD ["php", "app/http.php"]
