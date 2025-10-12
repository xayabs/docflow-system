#Use PHP 8.3-fpm-alpine to be base Image
FROM php:8.3-fpm-alpine

#Identify Working Directory
WORKDIR /var/www/html

#Install necesary Dependencies and PHP Extensions of the system
#Intall any things in a RUN to reduce number of Layers
#Not include nginx because service is used separately
RUN apk add --no-cache \
    build-base \
    shadow mysql-client curl git openssh-client \
    libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    libwebp-dev libxpm-dev \
    nodejs npm \
  && docker-php-ext-install pdo_mysql bcmath gd zip

#Install Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy only Composer file and first instal Dependencies
# *** This is important point of Caching ***
# If the file composer.json/lock is not modify, Docker will not install again
COPY composer.json composer.lock ./
# RUN composer install --no-interaction --prefer-dist --optimize-autoloader
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy all the project file after install Dependencies ready
COPY . .

# *** run scripts from copy code ***
RUN composer dump-autoload --optimize

# Port which PHP-FPM work
EXPOSE 9000

#Begin command when Container is Run
CMD ["php-fpm"]