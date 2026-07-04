FROM php:8.3-apache

# Enable required PHP extensions: pdo_mysql (database), zip (docx parsing), curl (OpenAI calls)
RUN apt-get update && apt-get install -y libzip-dev poppler-utils \
    && docker-php-ext-install pdo_mysql zip \
    && docker-php-ext-enable pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Apache should serve this project's root as the web root
COPY . /var/www/html/
RUN mkdir -p /var/www/html/uploads/lesson_notes \
    && chown -R www-data:www-data /var/www/html/uploads

# Railway/Render inject $PORT - point Apache at it
RUN sed -i 's/80/${PORT:-80}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
ENV PORT=80
EXPOSE 80

CMD ["apache2-foreground"]
