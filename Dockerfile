# Dockerfile para iniciar a API em php

FROM php:8.3-cli-alpine

# Instala extensão pdo_mysql
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . .

# Expõe a porta 8000 para a API
EXPOSE 8000

# Executa o servidor embutido do PHP apontando para o router public/index.php
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
