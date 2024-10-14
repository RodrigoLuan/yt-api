# Use a imagem oficial PHP com PHP-FPM
FROM php:8.2-fpm

# Instalar dependências do sistema, extensões PHP e netcat-openbsd
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    netcat-openbsd \
    && docker-php-ext-install pdo pdo_pgsql

# Copiar os arquivos da aplicação
COPY . /var/www/html

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html

# Expor a porta 9000 para o PHP-FPM
EXPOSE 9000

# Comando padrão para iniciar o PHP-FPM
CMD ["php-fpm"]
