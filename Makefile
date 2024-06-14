# GIT

pull:
	git pull

# MANIPULATE CONTAINER

update-dev-container: stop clean build composer db start
update-prod-container: stop-prod clean-prod pull build-prod composer-prod migrate-prod start-prod optimize-prod

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

npm-install-package:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm frontend sh -c "npm i ${p}"

npm-install-save-dev:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm frontend sh -c "npm install --save-dev"

migrate:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan migrate"

rollback:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan migrate:rollback"

seeds:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan db:seed"

db: migrate seeds

composer:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "composer install"

artisan:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan ${c}"

optimize:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan optimize:clear"

route:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan route:list"

tinker:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan tinker"

swagger:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan l5-swagger:generate"

scribe:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan scribe:generate"

#user:
#	@docker exec -it --user www-data nginx /bin/sh

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
	@docker-compose -f docker-compose.yml -f prod.yml run --rm backend sh -c "composer install --no-dev --optimize-autoloader"

migrate-prod:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm backend sh -c "php artisan migrate --force"

seeds-prod:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm backend sh -c "php artisan db:seed"

optimize-prod:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm backend sh -c "php artisan optimize"
