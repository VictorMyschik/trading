FROM php:8.3-fpm

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN apt-get update

#RUN docker-php-ext-install pdo_mysql
# Install Postgre PDO
RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-install sockets

RUN set -x && \
    # install lib packages
    apt-get update &&\
    apt-get install --no-install-recommends --no-install-suggests -y \
        openssh-server \
        nodejs \
        default-mysql-client \
        npm \
        bc \
        libzip-dev \
        unzip \
        # for bz2
        #   - ref: https://github.com/docker-library/php/issues/47
        libbz2-dev \
        # for gd
        #   - ref: https://stackoverflow.com/questions/61228386/installing-gd-extension-in-docker
        libfreetype6-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        && \
    apt-get install --no-install-recommends --no-install-suggests -y \
        # for composer
        postgresql-client \
        mc \
        && \
    # docker-php
    #   - Removed `mbstring` on alpine: https://stackoverflow.com/questions/59251008/docker-laravel-configure-error-package-requirements-oniguruma-were-not-m/59253249#59253249
    #     Due to this error: `configure: error: Package requirements (oniguruma) were not met: Package 'oniguruma', required by 'virtual:world', not found`
    # for gd
    #   - ref: https://github.com/docker-library/php/pull/910#issuecomment-559383597
    docker-php-ext-configure gd --with-freetype --with-jpeg ; \
    docker-php-ext-install -j$(nproc) \
        bcmath \
        pdo \
        pdo_pgsql \
        bz2 \
        gd \
        exif \
        opcache \
        zip \
        && \
    docker-php-source delete && \
    # composer
    curl -sS https://getcomposer.org/installer | php -- --quiet --install-dir=/usr/local/bin --filename=composer && \
    # clean up
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /usr/share/nginx/html/*

WORKDIR /var/www/trading

#RUN curl -sl https://deb.nodesource.com/setup_18.x | bash -
#RUN apt-get install nodejs -y
#RUN npm install -g npm@10.8.2
