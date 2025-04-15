FROM php:latest

RUN apt-get update && apt-get install libffi-dev -y \
    && docker-php-ext-install \
        ffi \
    && docker-php-ext-enable \
        ffi
