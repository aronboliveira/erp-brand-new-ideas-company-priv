FROM php:8.1-fpm
RUN apt-get update && apt-get install -y \
		git curl libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
		libonig-dev libxml2-dev zip unzip libzip-dev \
	&& docker-php-ext-configure zip \
	&& docker-php-ext-install \
		bcmath exif gd mbstring pcntl pdo_mysql xml zip
COPY --from=composer:2.5 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-dev --no-interaction --optimize-autoloader
COPY . .
RUN composer dump-autoload --optimize
RUN chown -R www-data:www-data storage bootstrap/cache \
	&& chmod -R 775 storage bootstrap/cache
EXPOSE 9000
CMD ["php-fpm"]