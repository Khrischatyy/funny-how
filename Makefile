# DEV

build:
	@docker-compose -f docker-compose.yml -f dev.yml build

start:
	@docker-compose -f docker-compose.yml -f dev.yml up

stop:
	@docker-compose -f docker-compose.yml -f dev.yml stop

clean:
	@docker-compose -f docker-compose.yml -f dev.yml down

test-backend:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "python manage.py wait_for_db && python manage.py test"

lint-backend:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "flake8"

npm-install:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm frontend sh -c "npm i"

migrations:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "python manage.py makemigrations"

migrate:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "python manage.py migrate"

seeds:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "python manage.py loaddata core/seeds/*"

start-app-django:
	@docker-compose -f docker-compose.yml -f dev.yml run --rm backend sh -c "python manage.py startapp $(newapp)"


# PROD

build-prod:
	@docker-compose -f docker-compose.yml -f prod.yml build

start-prod:
	docker-compose -f docker-compose.yml -f prod.yml up

clean-prod:
	@docker-compose -f docker-compose.yml -f prod.yml down

npm-install-prod:
	@docker-compose -f docker-compose.yml -f prod.yml run --rm frontend sh -c "npm i"