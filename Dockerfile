# Imagen para desplegar Mi Plataforma (Ubaté Eats) en Railway.
# Una sola imagen, reutilizada por 3 servicios de Railway (web, queue, reverb)
# que solo cambian el "Start Command" en el dashboard. Ver docs/DEPLOY_RAILWAY.md.

FROM php:8.3-cli

# Dependencias de sistema + extensiones de PHP que usa Laravel/el proyecto
RUN apt-get update && apt-get install -y \
        git unzip libzip-dev libpng-dev libonig-dev libxml2-dev default-mysql-client \
    && docker-php-ext-install pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js (para compilar assets con Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm install \
    && npm run build \
    && npm prune --omit=dev

# El "Start Command" real lo define cada servicio en el dashboard de Railway
# (web / queue worker / reverb). Este CMD es solo el valor por defecto.
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
