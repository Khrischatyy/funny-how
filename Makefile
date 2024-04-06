# DEV

build:
	@docker-compose -f docker-compose.yml -f dev.yml build

start:
	@docker-compose -f docker-compose.yml -f dev.yml up -d

stop:
	@docker-compose -f docker-compose.yml -f dev.yml stop

clean:
	@docker-compose -f docker-compose.yml -f dev.yml down

npm-install:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm frontend sh -c "npm i"

migrate:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan migrate"

seeds:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "php artisan db:seed"


# PROD

build-prod:
	@docker-compose -f docker-compose.yml -f prod.yml build

start-prod:
	docker-compose -f docker-compose.yml -f prod.yml up

clean-prod:
	@docker-compose -f docker-compose.yml -f prod.yml down

npm-install-prod:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm frontend sh -c "npm i"

certificate:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm certbot certonly --webroot --webroot-path /var/www/certbot/ --dry-run -d funny-how.com