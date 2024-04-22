composer-install:
	docker-compose exec php composer install --optimize-autoloader --no-suggest --no-dev

database-migrate:
	docker-compose run --rm php php artisan migrate --force


install-dev: build-dev composer-install-dev laravel-storage

laravel-cache:
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:cache
	docker-compose exec php php artisan event:cache
	docker-compose exec php php artisan route:cache
	docker-compose exec php php artisan view:cache

laravel-storage:
	docker-compose run --rm php php artisan storage:link

restart: stop start

restart-dev: stop-dev start-dev


stop:
	docker-compose down --remove-orphans

stop-dev:
	docker-compose -f docker-compose-dev.yml down --remove-orphans


test-create:
	docker-compose run --rm php php artisan make:test $(name)



upgrade: stop git-pull pull build composer-install laravel-cache database-migrate start

upgrade-dev: stop-dev git-pull pull-dev build-dev composer-install-dev database-migrate start-dev
