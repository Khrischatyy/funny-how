#MANIPULATE CONTAINER

update-dev-container: stop clean build start
update-prod-container: stop-prod clean-prod build-prod start-prod

exec:
	@docker exec -it ${c} /bin/sh

# DEPLOY

add:
	@git add .

commit:
	@git commit -m "${m}"

pull:
	@git pull

push:
	@git push

# DEV

status:
	@docker-compose -f docker-compose.yml -f dev.yml ps

build:
	@docker-compose -f docker-compose.yml -f dev.yml build

start:
	@docker-compose -f docker-compose.yml -f dev.yml up

stop:
	@docker-compose -f docker-compose.yml -f dev.yml stop

restart: stop start

clean:
	@docker-compose -f docker-compose.yml -f dev.yml down

npm-install:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm frontend sh -c "npm i"

migrate:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan migrate"

seeds:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan db:seed"

db: migrate seeds

composer:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "composer install"

backend-exec:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan ${c}"

optimize:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan optimize"



# PROD

status-prod:
	@docker-compose -f docker-compose.yml -f prod.yml ps

build-prod:
	@docker-compose -f docker-compose.yml -f prod.yml build

start-prod:
	docker-compose -f docker-compose.yml -f prod.yml up -d

stop-prod:
	@docker-compose -f docker-compose.yml -f prod.yml stop

restart-prod: stop-prod start-prod

clean-prod:
	@docker-compose -f docker-compose.yml -f prod.yml down

npm-install-prod:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm frontend sh -c "npm i"

composer-prod:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm backend sh -c "composer install"

migrate-prod:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm backend sh -c "php artisan migrate"

seeds-prod:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm backend sh -c "php artisan db:seed"

optimize-prod:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm backend sh -c "php artisan optimize"



# SSL

certificate-test:
	@docker-compose -f prod.yml -f docker-compose.yml run --rm certbot certonly --webroot --webroot-path /app --dry-run -d funny-how.com

certificate:
	@docker-compose -f prod.yml -f docker-compose.yml run --rm certbot certonly --webroot --webroot-path /app -d funny-how.com