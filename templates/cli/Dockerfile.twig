FROM composer:2.0 as step0

WORKDIR /usr/local/code/
COPY composer.json /usr/local/code/
RUN composer update --ignore-platform-reqs --optimize-autoloader \
    --no-plugins --no-scripts --prefer-dist \
    `if [ "$TESTING" != "true" ]; then echo "--no-dev"; fi`


FROM php:7.4-alpine 

WORKDIR /usr/local/code/
# Add Source Code
COPY ./app /usr/local/code/app
COPY ./src /usr/local/code/src
COPY ./bin /usr/local/bin
COPY --from=step0 /usr/local/code/vendor /usr/local/code/vendor

# Executables
RUN chmod -R +x /usr/local/bin/