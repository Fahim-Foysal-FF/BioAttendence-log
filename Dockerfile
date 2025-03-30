FROM php:8.2-apache

# Install PostgreSQL extensions and dependencies
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy all files to the container
COPY . /var/www/html/

# Set proper permissions (only on existing directories)
RUN chown -R www-data:www-data /var/www/html && \
    if [ -d "/var/www/html/storage" ]; then chmod -R 755 /var/www/html/storage; fi

# Configure Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s \
  CMD curl -f http://localhost/ || exit 1
