FROM php:8.2-cli

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    librabbitmq-dev

# Instalar extensões
RUN docker-php-ext-install pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar diretório de trabalho
WORKDIR /app

# Copiar arquivos do projeto
COPY . .
# Instalar dependências para Postgres
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar dependências
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pdo pdo_pgsql
# Comando para executar o consumer
CMD ["php", "src/consumer.php"]