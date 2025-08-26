FROM php:8.1-apache

# Installe les extensions nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Active les modules Apache si besoin
RUN a2enmod rewrite

# Copie ton index.php dans le dossier web
COPY index.php /var/www/html/
