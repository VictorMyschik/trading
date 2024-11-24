FROM php:8.3-rc-cli

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

RUN apt-get update

# Install Postgre PDO
RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

RUN set -x && \
    # install lib packages
    apt-get update &&\
    apt-get install --no-install-recommends --no-install-suggests -y \
        openssh-server \
        bc \
        libzip-dev \
        unzip \
        libbz2-dev \
        libfreetype6-dev \
        libpng-dev \
    && \
    apt-get install --no-install-recommends --no-install-suggests -y \
        # for composer
        postgresql-client \
        mc \
        && \
    docker-php-ext-configure gd --with-freetype --with-jpeg ; \
    docker-php-ext-install -j$(nproc) \
        bcmath \
        pdo \
        pdo_pgsql \
        bz2 \
        gd \
        exif \
        opcache \
        zip

WORKDIR /var/www/trading

# Supervisor config
RUN apt-get update && apt-get upgrade -y
RUN apt-get install supervisor -y

RUN mkdir -p /var/log/supervisor

COPY supervisor/conf.d/* /etc/supervisor/conf.d/
COPY supervisor/supervisord.conf /etc/supervisor/

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
