FROM php:8.1-fpm

# Arguments defined in docker-compose.yml
ARG WWWUSER
ARG WWWUID

# Set working directory
WORKDIR /app/

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

# Install system dependencies and clear cache
RUN apt-get -y update \
    && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libxml2-dev \
    libgmp-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    vim \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) iconv mysqli pdo_pgsql pdo_mysql soap bcmath gmp intl opcache zip sockets

# Install Imagick extension
RUN apt-get update && apt-get install -y libmagickwand-dev --no-install-recommends \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $WWWUID -d /home/$WWWUSER $WWWUSER
RUN mkdir -p /home/$WWWUSER/.composer && \
    chown -R $WWWUSER:$WWWUSER /home/$WWWUSER

COPY php.ini /usr/local/etc/php/conf.d/zz-custom.ini
RUN chown -R www-data:www-data /app/

USER $WWWUSER