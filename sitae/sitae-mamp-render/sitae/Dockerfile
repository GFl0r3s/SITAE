FROM php:8.3-apache
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*
# Protege carpetas y archivos sensibles aunque falten los .htaccess en GitHub
RUN printf '%s\n' \
  '<Directory /var/www/html>' \
  '    Options -Indexes' \
  '</Directory>' \
  '<DirectoryMatch "^/var/www/html/(sql|herramientas)(/|$)">' \
  '    Require all denied' \
  '</DirectoryMatch>' \
  '<FilesMatch "\.(sql|md|log|zip)$">' \
  '    Require all denied' \
  '</FilesMatch>' > /etc/apache2/conf-available/sitae.conf \
 && a2enconf sitae
# Render indica el puerto en la variable PORT (por defecto 10000)
ENV PORT=10000
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html/
