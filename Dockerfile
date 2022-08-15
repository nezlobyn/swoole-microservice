#
# Composer Depencies
#
FROM composer as vendor

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM php:8.1.0-cli

RUN apt-get update && apt-get install vim -y && \
    apt-get install openssl -y && \
    apt-get install libssl-dev -y && \
    apt-get install wget -y && \
    apt-get install git -y && \
    apt-get install procps -y && \
    apt-get install htop -y && \
    apt-get install -y libcurl4-openssl-dev

RUN cd /tmp && git clone https://github.com/openswoole/swoole-src.git && \
    cd swoole-src && \
    git checkout v4.11.0 && \
    phpize  && \
    ./configure --enable-openssl --enable-swoole-curl --enable-http2 --enable-mysqlnd && \
    make && make install

RUN touch /usr/local/etc/php/conf.d/openswoole.ini && \
    echo 'extension=openswoole.so' > /usr/local/etc/php/conf.d/zzz_openswoole.ini

RUN apt-get install -y libyaml-dev
RUN pecl install yaml && docker-php-ext-enable yaml

RUN mkdir -p /app
COPY . /app
COPY --from=vendor /app/vendor /app/vendor

WORKDIR /app

EXPOSE 9500

CMD ["/usr/local/bin/php", "/app/bin/run.php"]