# Docker Setup для VPN Service

## Что поднимается

- `app`: контейнер с `php-fpm` для Laravel
- `postgres`: PostgreSQL с постоянным volume

`nginx` в `docker-compose` не добавляется, потому что он уже работает на хосте.

## Что происходит при `docker compose up`

При каждом старте контейнера `app` автоматически:

1. ждёт готовности PostgreSQL;
2. выполняет `composer install`;
3. запускает `php artisan migrate --force`;
4. поднимает `php-fpm` на порту `9000`.

## Подключение к БД

Конфигурация взята из `.env.example`:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=vpnservice
DB_USERNAME=vpnservice_user
DB_PASSWORD=vpnservice_password
```

Сервис базы в `docker-compose.yml` специально называется `postgres`, а переменные контейнера `app` зафиксированы на значениях из `.env.example`, чтобы Docker не зависел от локального `.env` на хосте.

## Запуск

```bash
docker compose up -d --build
docker compose logs -f
```

Остановить контейнеры:

```bash
docker compose down
```

Если нужно удалить контейнеры, но оставить данные PostgreSQL:

```bash
docker compose down
```

Если нужно удалить и контейнеры, и volume базы:

```bash
docker compose down -v
```

## Что добавить в nginx на хосте

Минимально важная директива для PHP:

```nginx
fastcgi_pass 127.0.0.1:9000;
```

Пример блока целиком:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/VPNservice2.0/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;
        fastcgi_pass 127.0.0.1:9000;
    }
}
```

## Volumes

- `postgres_data`: постоянное хранение данных PostgreSQL
- `composer_cache`: кеш Composer
- `./`: код проекта монтируется в контейнер `app`

## Важно

В шаблонах используется `@vite(...)`, поэтому для production-режима статические ассеты должны быть заранее собраны в `public/build`. Docker-конфиг выше поднимает PHP и PostgreSQL, но сборку фронтенда не выполняет.

Порт PostgreSQL наружу не публикуется, потому что он нужен только контейнеру `app`. Это снижает шанс конфликта с локальной БД на хосте.