FROM php:8.4-cli-bookworm

# System dependencies + Chromium
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libzip-dev libgd-dev libpng-dev libjpeg-dev \
    chromium fonts-liberation \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd zip opcache pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Puppeteer (skip Chrome download — usamos el Chromium del sistema)
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

RUN npm install -g puppeteer

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# PHP deps
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-scripts --no-interaction

# JS deps + build
COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build && composer run-script post-autoload-dump

EXPOSE 8000
CMD ["bash", "start.sh"]
