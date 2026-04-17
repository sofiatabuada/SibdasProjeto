FROM dunglas/frankenphp

RUN install-php-extensions \
    pdo_mysql \
    mysqli \
    opcache

WORKDIR /app

COPY . .
