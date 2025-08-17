# Run the Docker containers
DOCKER_COMPOSE=docker compose -f docker-compose.yaml

#export DOCKER_BUILDKIT=1

help: ## Show help like directly using make
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## Install all dependencies
	$(DOCKER_COMPOSE) build
	$(DOCKER_COMPOSE) run --rm api composer install

run: ## Launch docker-compose stack
	$(DOCKER_COMPOSE) up --remove-orphans -d
	$(DOCKER_COMPOSE) run --rm api php bin/console doctrine:migrations:migrate --no-interaction

down: ## Delete the Docker containers and volumes
	$(DOCKER_COMPOSE) down -v

logs: ## Log the Docker containers
	$(DOCKER_COMPOSE) logs --tail=10 -f

fixtures: ## seed the database with core data
	$(DOCKER_COMPOSE) run --rm api php -d memory_limit=-1 bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate

test: ## run tests
	$(DOCKER_COMPOSE) run --rm api php bin/console d:d:d --force --env=test || true
	$(DOCKER_COMPOSE) run --rm api php bin/console d:d:c --env=test
	$(DOCKER_COMPOSE) run --rm api php bin/console d:mi:mi -n --env=test
	$(DOCKER_COMPOSE) run --rm api php bin/console d:fixture:load -n --env=test
	$(DOCKER_COMPOSE) run --rm api php bin/phpunit tests --testdox


#client_migration: ## migrate the client database
#	docker-compose -f $(DOCKER_COMPOSE_FILE) run --rm backend /bin/bash -c "npx sequelize-cli db:migrate --url \"mysql://\$MYSQL_USERNAME:\$MYSQL_PASSWORD@\$MYSQL_HOST:\$MYSQL_PORT/client1\" --seeders-path src/database/client/seeders/ --migrations-path src/database/client/migrations/"

ps:
	$(DOCKER_COMPOSE) ps

migration:
	$(DOCKER_COMPOSE) run --rm api php bin/console make:migration

latest-migration:
	$(DOCKER_COMPOSE) run --rm api php bin/console doctrine:migrations:migrate --no-interaction

cli:
	$(DOCKER_COMPOSE) exec api bash

prettier:
	docker run --rm -v ${PWD}:/project -w /project jakzal/phpqa php-cs-fixer fix src
    docker run --rm -v ${PWD}:/project -w /project jakzal/phpqa php-cs-fixer fix tests

entity:
	$(DOCKER_COMPOSE) run --rm api php bin/console make:entity
