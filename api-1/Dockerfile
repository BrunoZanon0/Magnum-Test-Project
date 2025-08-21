FROM php:8.2-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    libssl-dev \
    librabbitmq-dev \
    zip \
    unzip

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões do PHP
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Instalar extensão Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instalar extensão AMQP (RabbitMQ)
RUN pecl install amqp && docker-php-ext-enable amqp

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar diretório de trabalho
WORKDIR /var/www/api-1

# Copiar arquivos do projeto
COPY . .

# ✅ CORRIGIDO: Remover a parte que tenta criar api-2/composer.json
# (Isso não faz sentido aqui, deve estar no Dockerfile da API-2)

# Instalar dependências do Composer
RUN composer install --no-dev --optimize-autoloader

# Expor porta
EXPOSE 9000

# Comando para iniciar o servidor
CMD ["php-fpm"]