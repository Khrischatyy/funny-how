#!/bin/bash
set -e

# Проверяем наличие домена
if [ -z "$1" ]; then
  echo "Использование: $0 <домен>"
  exit 1
fi

DOMAIN=$1
EMAIL="khrischatyy@gmail.com"

echo "Настраиваем получение SSL-сертификата для домена $DOMAIN..."

# Создаем директории для certbot
mkdir -p ../certbot/www
mkdir -p ../certbot/conf

# Создаем временную конфигурацию для http
cat > ../nginx-temp.conf << 'EOF'
events {
    worker_connections 1024;
}

http {
    server {
        listen 80;
        server_name funny-how.com www.funny-how.com;

        location /.well-known/acme-challenge/ {
            root /var/www/certbot;
        }

        location / {
            return 301 https://$host$request_uri;
        }
    }
}
EOF

echo "Запускаем временный Nginx для проверки домена..."
# Запускаем временный nginx для проверки домена
docker run --rm -d \
  --name nginx-certbot \
  -p 80:80 \
  -v $PWD/../nginx-temp.conf:/etc/nginx/nginx.conf \
  -v $PWD/../certbot/www/:/var/www/certbot/ \
  nginx

echo "Получаем SSL-сертификат с помощью certbot..."
# Запускаем certbot с опцией force-renewal
docker run --rm \
  -v $PWD/../certbot/conf/:/etc/letsencrypt/ \
  -v $PWD/../certbot/www/:/var/www/certbot/ \
  certbot/certbot certonly \
  --webroot \
  --webroot-path=/var/www/certbot \
  --email $EMAIL \
  --agree-tos \
  --no-eff-email \
  --force-renewal \
  -d $DOMAIN \
  -d www.$DOMAIN

echo "Останавливаем временный Nginx..."
# Останавливаем временный nginx
docker stop nginx-certbot

# Отображаем содержимое директории с сертификатами
echo "Проверяем полученные сертификаты:"
ls -la ../certbot/conf/live/$DOMAIN/ || echo "Директория live/$DOMAIN не найдена"

# Обновляем права на файлы сертификатов
chmod -R 755 ../certbot/conf/

# Создаем обновленную конфигурацию nginx-prod.conf
cat > ../nginx-prod.conf << 'EOF'
events {
    worker_connections 1024;
}

http {
    map_hash_max_size 262144;
    map_hash_bucket_size 2048;
    client_max_body_size 10M;

    map $sent_http_content_type $expires {
        "text/html"                 epoch;
        "text/html; charset=utf-8"  epoch;
        default                     off;
    }

    # HTTP server для Let's Encrypt и редиректа на HTTPS
    server {
        listen 80;
        server_name funny-how.com www.funny-how.com;

        # Let's Encrypt challenge
        location /.well-known/acme-challenge/ {
            root /var/www/certbot;
        }

        # Редирект на HTTPS
        location / {
            return 301 https://$host$request_uri;
        }
    }

    # HTTPS server
    server {
        listen [::]:443 ssl;
        listen 443 ssl;

        server_name funny-how.com www.funny-how.com;
        root /app/public;

        index index.php;

        error_log  /var/log/nginx/error.log;
        access_log /var/log/nginx/access.log;

        location /api {
            try_files $uri $uri/ /index.php$is_args$args;
        }

        location / {
            expires $expires;
            add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
            add_header X-Frame-Options "SAMEORIGIN";
            add_header X-Cache-Status $upstream_cache_status;

            proxy_redirect                      off;
            proxy_set_header Host               $host;
            proxy_set_header X-Real-IP          $remote_addr;
            proxy_set_header X-Forwarded-For    $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto  $scheme;
            proxy_ignore_headers        Cache-Control;
            proxy_http_version          1.1;
            proxy_read_timeout          1m;
            proxy_connect_timeout       1m;
            proxy_pass                  http://frontend:3000;
            proxy_cache_bypass          $arg_nocache;
            proxy_cache_valid           200 302  60m;
            proxy_cache_valid           404      1m;
            proxy_cache_lock            on;
            proxy_cache_use_stale error timeout http_500 http_502 http_503 http_504;
            proxy_cache_key             $uri$is_args$args;
        }

        location ~ \.php$ {
            fastcgi_pass                php:9000;
            fastcgi_index           index.php;
            fastcgi_split_path_info       ^(.+\.php)(/.+)$;
            fastcgi_param           SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param           PHP_ADMIN_VALUE open_basedir=/app/:/usr/lib/php/:/tmp/;
            fastcgi_intercept_errors   off;

            fastcgi_buffer_size             128k;
            fastcgi_buffers                256 16k;
            fastcgi_busy_buffers_size     256k;
            fastcgi_temp_file_write_size   256k;

            include fastcgi_params;
        }

        # SSL configuration
        ssl_certificate /etc/nginx/ssl/live/funny-how.com/fullchain.pem;
        ssl_certificate_key /etc/nginx/ssl/live/funny-how.com/privkey.pem;
        ssl_session_timeout 5m;
        ssl_protocols TLSv1.2 TLSv1.3;
        ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384';
        ssl_prefer_server_ciphers on;
        ssl_session_cache shared:SSL:10m;

        charset utf-8;

        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }
        location ~ /\.ht {
            deny all;
        }
    }
}
EOF

# Удаляем временную конфигурацию
rm -f ../nginx-temp.conf

echo "✅ Конфигурация Nginx обновлена"
echo ""
echo "Теперь можно перезапустить docker-compose из корневой директории проекта:"
echo "cd /srv/funny-how"
echo "docker-compose down"
echo "docker-compose up -d"
echo ""
echo "Если вы хотите настроить автоматическое обновление сертификатов, рекомендуется добавить в crontab:"
echo "0 12 * * * cd /srv/funny-how && docker run --rm -v \$PWD/proxy/certbot/conf/:/etc/letsencrypt/ -v \$PWD/proxy/certbot/www/:/var/www/certbot/ certbot/certbot renew --quiet && docker-compose exec -T nginx nginx -s reload"
