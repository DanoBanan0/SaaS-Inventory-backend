FROM php:8.2-apache

# Instalar dependencias del sistema y PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip git unzip curl && \
    docker-php-ext-install gd pdo pdo_mysql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar archivos y configurar Apache
COPY . /var/www/html
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Instalar Composer y dependencias (incluyendo Faker para los seeders)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --optimize-autoloader

# Configurar Base de Datos SQLite y Permisos
RUN mkdir -p database && touch database/database.sqlite
RUN chmod -R 777 database storage bootstrap/cache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# Comando para limpiar, migrar con seeders e iniciar
CMD php artisan config:clear && \
    php artisan migrate:fresh --seed --force && \
    apache2-foreground