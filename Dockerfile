FROM dunglas/frankenphp

RUN install-php-extensions \
    pdo_mysql \
    mysqli \
    opcache

COPY . /app
