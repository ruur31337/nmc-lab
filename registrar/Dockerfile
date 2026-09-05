FROM php:8.1-apache

# Enable mysqli and mod_rewrite
RUN docker-php-ext-install mysqli \
 && a2enmod rewrite

# Apache config
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Application files
COPY www/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
