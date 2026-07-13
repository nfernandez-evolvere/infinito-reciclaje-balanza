# syntax=docker/dockerfile:1.7
# =============================================================================
#  Imagen de producción — Sistema de Gestión de Balanza (Laravel 13 / PHP 8.4)
#  Build multi-stage: las dependencias y el toolchain de build NO llegan al
#  runtime. La imagen final = php-fpm + nginx + Chromium (Browsershot) + el
#  driver pdo_sqlsrv para SQL Server.
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1 — vendor de Composer (sin dev, autoloader optimizado)
# -----------------------------------------------------------------------------
FROM composer:2 AS composer-deps
WORKDIR /app

# Capa cacheada por composer.lock — solo se reconstruye si cambian las deps.
# --ignore-platform-reqs: la imagen composer:2 no trae ext-gd (la requieren mpdf y
# phpspreadsheet). Acá solo resolvemos/descargamos deps; el runtime SÍ tiene gd.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --ignore-platform-reqs

# El código fuente entra después para no invalidar la capa de deps.
# --no-scripts: evita disparar post-autoload-dump (artisan package:discover),
# que bootearía Laravel en esta imagen sin extensiones. El discovery ocurre en
# runtime vía entrypoint (config:cache).
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts

# -----------------------------------------------------------------------------
# Stage 2 — build de assets (Vite + Tailwind v4). Solo sale public/build
# -----------------------------------------------------------------------------
FROM node:22-bookworm AS asset-build
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

# Tailwind v4 escanea los blade/PHP por clases usadas → necesita todo el fuente
COPY . .
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 3 — runtime
# -----------------------------------------------------------------------------
FROM php:8.4-fpm-bookworm AS runtime

ENV DEBIAN_FRONTEND=noninteractive \
    PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/local/bin/chrome \
    NODE_PATH=/usr/lib/node_modules

# --- Librerías de runtime + repos de Microsoft/NodeSource + libs de Chrome ----
# NO se instala el paquete `chromium` de Debian: su build de Bookworm
# (150.0.7871.46) crashea con SIGTRAP al arrancar en este entorno (WSL2/Docker),
# tanto en headless new/old como con --no-sandbox/--single-process. En su lugar
# se hornea Chrome for Testing (build oficial de Google, ver más abajo) y acá
# solo se instalan las librerías compartidas que ese binario necesita — el set
# canónico de dependencias de Chrome headless en Debian.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates curl gnupg unzip \
        nginx supervisor \
        libzip4 libpng16-16 libjpeg62-turbo libfreetype6 libicu72 \
        fonts-liberation \
        libnss3 libnspr4 libdbus-1-3 libglib2.0-0 \
        libatk1.0-0 libatk-bridge2.0-0 libatspi2.0-0 \
        libcups2 libdrm2 libgbm1 libasound2 \
        libpango-1.0-0 libcairo2 \
        libx11-6 libxcb1 libxext6 libxi6 libxrender1 \
        libxcomposite1 libxdamage1 libxfixes3 libxrandr2 libxkbcommon0; \
    # Repositorio Microsoft (ODBC 18) — clave dearmorada a keyring propio +
    # signed-by explícito (determinístico; evita el "repository is not signed")
    curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
        | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg; \
    echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
        > /etc/apt/sources.list.d/mssql-release.list; \
    # Repositorio NodeSource (Node 22, runtime de Browsershot)
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash -; \
    apt-get update; \
    ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 nodejs; \
    npm install -g puppeteer@25; \
    # Chrome for Testing (build oficial de Google) — reemplaza al `chromium` de
    # Debian, que crashea en runtime. Se instala a /opt y se expone por un
    # symlink estable en /usr/local/bin/chrome (independiente de la versión, que
    # queda embebida en la ruta de destino). Para actualizarlo: bumpear la
    # versión pineada acá y reconstruir la imagen.
    npx --yes @puppeteer/browsers install chrome@150.0.7871.115 --path /opt/chrome-for-testing; \
    ln -sf "$(find /opt/chrome-for-testing -type f -name chrome | head -1)" /usr/local/bin/chrome; \
    chmod -R a+rX /opt/chrome-for-testing; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

# --- Extensiones PHP (las build-deps se purgan al final de la capa) ----------
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS unixodbc-dev \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev; \
    docker-php-ext-configure gd --with-jpeg --with-freetype; \
    docker-php-ext-install -j"$(nproc)" gd zip opcache pcntl bcmath intl; \
    pecl install sqlsrv pdo_sqlsrv; \
    docker-php-ext-enable sqlsrv pdo_sqlsrv; \
    # Purga de headers/compiladores: las libs de runtime quedaron en la capa previa
    apt-get purge -y --auto-remove \
        $PHPIZE_DEPS unixodbc-dev \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/pear

WORKDIR /app

# --- Configuración de PHP / php-fpm / nginx / supervisor ---------------------
COPY docker/php/php.ini        /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/opcache.ini    /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php-fpm/www.conf   /usr/local/etc/php-fpm.d/zz-www.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/        /etc/supervisor/conf.d/
RUN rm -f /etc/nginx/sites-enabled/default
COPY docker/entrypoint.sh      /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# --- Código de la app + artefactos de los stages previos ---------------------
COPY . .
COPY --from=composer-deps /app/vendor      ./vendor
COPY --from=asset-build   /app/public/build ./public/build

# Permisos: las rutas escribibles en runtime quedan a nombre de www-data
RUN set -eux; \
    mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R ug+rw storage bootstrap/cache; \
    # Logs de nginx → stdout/stderr del contenedor
    ln -sf /dev/stdout /var/log/nginx/access.log; \
    ln -sf /dev/stderr /var/log/nginx/error.log

EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["entrypoint"]
# CMD por defecto = grupo web (nginx + php-fpm). El worker stack lo sobreescribe.
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/web.conf", "-n"]
