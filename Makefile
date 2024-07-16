# GIT

pull:
	git pull

# MANIPULATE CONTAINER

update-dev-container: stop clean build composer db start
update-prod-container: stop-prod clean-prod pull build-prod composer-prod migrate-prod seeds-prod declare-queue-prod optimize-prod start-prod
#after db is ready
#update-prod-container: stop-prod pull composer-prod migrate-prod declare-queue-prod optimize-prod start-prod

# DEV

status:
	@docker-compose -f dev.yml ps

build:
	@docker-compose -f dev.yml build

start:
	@docker-compose -f dev.yml up

logs:
	@docker-compose -f dev.yml logs -f $(container)

logs-all:
	@docker-compose -f dev.yml logs -f

stop:
	@docker-compose -f dev.yml stop

restart: stop start

clean:
	@docker-compose -f dev.yml down

nginx-reload:
	@docker-compose -f dev.yml exec nginx nginx -s reload

npm-install:
	@docker-compose -f dev.yml run --rm frontend sh -c "npm i"

npm-install-package:
	@docker-compose -f dev.yml run --rm frontend sh -c "npm i ${p}"

npm-install-save-dev:
	@docker-compose -f dev.yml run --rm frontend sh -c "npm install --save-dev"

update-frontend:
	@docker-compose -f dev.yml stop frontend
	@docker-compose -f dev.yml up -d frontend


update-frontend-prod:
	@docker-compose -f prod.yml stop frontend
	@docker-compose -f prod.yml up -d frontend

migrate:
	@docker-compose -f dev.yml run --rm backend sh -c "php artisan migrate"

rollback:
	@docker-compose -f dev.yml run --rm backend sh -c "php artisan migrate:rollback"

seeds:
	@docker-compose -f dev.yml run --rm backend sh -c "php artisan db:seed"

db: drop-dev-db migrate seeds

composer:
	@docker-compose -f dev.yml run --rm backend sh -c "composer install"

artisan:
	@docker-compose -f dev.yml run --rm backend sh -c "php artisan ${c}"

queue:
	@docker-compose -f dev.yml run --rm backend sh -c "php artisan queue:work"

optimize:
	@docker-compose -f dev.yml run --rm backend sh -c "php artisan optimize:clear"

route:
	@docker-compose -f dev.yml run --rm backend sh -c "php artisan route:list"

tinker:
	@docker-compose -f dev.yml run --rm backend sh -c "php artisan tinker"

swagger:
	@docker-compose -f dev.yml run --rm backend sh -c "php artisan l5-swagger:generate"

drop-dev-db:
	@docker-compose -f dev.yml run --rm backend sh -c "php artisan migrate:reset"

#scribe:
#	@docker-compose -f dev.yml run --rm backend sh -c "php artisan scribe:generate"

#user:
#	@docker exec -it --user www-data nginx /bin/sh

# PROD

status-prod:
	@docker-compose -f prod.yml ps

build-prod:
	@docker-compose -f prod.yml build

start-prod:
	docker-compose -f prod.yml up -d

stop-prod:
	@docker-compose -f prod.yml stop

logs-prod:
	@docker-compose -f prod.yml logs -f $(container)

logs-all-prod:
	@docker-compose -f prod.yml logs -f

restart-prod: stop-prod start-prod

declare-queue-prod:
	@docker-compose -f prod.yml run --rm backend sh -c "php artisan rabbitmq:declare-queue"

clean-prod:
	@docker-compose -f prod.yml down --remove-orphans

npm-install-prod:
	@docker-compose -f prod.yml run --rm frontend sh -c "npm i"

composer-prod:
	@docker-compose -f prod.yml run --rm backend sh -c "composer install --no-dev --optimize-autoloader"

migrate-prod:
	@docker-compose -f prod.yml run --rm backend sh -c "php artisan migrate --force"

optimize-prod:
	@docker-compose -f prod.yml run --rm backend sh -c "php artisan optimize"

nginx-reload-prod:
	@docker-compose -f prod.yml exec nginx nginx -s reload

seeds-prod:
	@docker-compose -f prod.yml run --rm backend sh -c "php artisan db:seed --class=DatabaseSeederProd --force"


# CAUTION: This will remove all Docker containers, volumes, and networks.
clean-all:
	@docker ps -a -q | xargs -r docker rm -f
	@docker volume ls -q | xargs -r docker volume rm
	@docker network ls --format '{{.Name}}' | awk '$$1 !~ /^(bridge|host|none)$$/' | xargs -r docker network rm