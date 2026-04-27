# Torque Studio ERP - Dockerfile
FROM php:8.1-apache

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Copiar configuración de Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copiar el proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Puerto
EXPOSE 80
