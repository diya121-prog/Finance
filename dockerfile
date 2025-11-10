# Use official PHP + Apache
FROM php:8.2-apache

# Enable PostgreSQL + PDO support
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Copy everything into Apache root
COPY . /var/www/html/

# Expose port 10000 (Render requirement)
EXPOSE 10000

# Start Apache server
CMD ["apache2-foreground"]