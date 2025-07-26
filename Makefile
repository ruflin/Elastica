DOCKER_COMPOSE_OPTIONS	= --project-name=elastica --file=docker/docker-compose.yml --file=docker/docker-compose.proxy.yml --file=docker/docker-compose.es.yml
DOCKER_COMPOSE_CMD := $(shell if [ ! -z "`docker compose version`" ]; then echo "docker compose"; else echo "docker-compose"; fi 2>/dev/null)

.PHONY: clean
clean:
	rm -fr tools vendor build composer.lock .php-cs-fixer.cache

tools/phive.phar:
	mkdir tools; \
	wget --no-clobber --output-document=tools/phive.phar "https://phar.io/releases/phive.phar" --quiet; \
    wget --no-clobber --output-document=tools/phive.phar.asc "https://phar.io/releases/phive.phar.asc" --quiet; \
    gpg --keyserver hkps.pool.sks-keyservers.net --recv-keys 0x9D8A98B29B2D5D79; \
    gpg --verify tools/phive.phar.asc tools/phive.phar; \
    rm tools/phive.phar.asc; \
    chmod +x tools/phive.phar;

vendor/autoload.php:
	# Installing Symfony Flex: parallel download of dependency libs
	composer global require --no-progress --no-scripts --no-plugins symfony/flex
	composer install --prefer-dist --no-interaction ${COMPOSER_FLAGS}

tools/php-cs-fixer.phar: tools/phive.phar
	tools/phive.phar install --copy --trust-gpg-keys 0xE82B2FB314E9906E,0x4AA394086372C20A

.PHONY: install-phpcs
install-phpcs: tools/php-cs-fixer.phar

.PHONY: composer-install
composer-install: vendor/autoload.php

.PHONY: composer-update
composer-update:
	composer update --prefer-dist --no-interaction ${COMPOSER_FLAGS}

.PHONY: install-tools
install-tools: install-phpcs

.PHONY: run-phpcs
run-phpcs: install-phpcs
	tools/php-cs-fixer.phar fix --diff --dry-run --allow-risky=yes -v --show-progress=dots

.PHONY: fix-phpcs
fix-phpcs: install-phpcs
	tools/php-cs-fixer.phar fix --allow-risky=yes -v --show-progress=dots

.PHONY: run-phpunit
run-phpunit: composer-install
	vendor/bin/phpunit ${PHPUNIT_OPTIONS}

.PHONY: run-phpunit-coverage
run-phpunit-coverage: composer-install
	EXIT_STATUS=0 ; \
	vendor/bin/phpunit --coverage-clover build/coverage/unit-coverage.xml --group unit || EXIT_STATUS=$$? ; \
	vendor/bin/phpunit --coverage-clover build/coverage/functional-coverage.xml --group functional || EXIT_STATUS=$$? ; \
	exit $$EXIT_STATUS

.PHONY: run-coveralls
run-coveralls:
	tools/php-coveralls.phar -v

tools/phpdocumentor.phar:
	curl http://www.phpdoc.org/phpDocumentor.phar -o tools/phpdocumentor.phar --silent -L; \
	chmod +x tools/phpdocumentor.phar

.PHONY: run-phpdoc
run-phpdoc: tools/phpdocumentor.phar
	tools/phpdocumentor.phar --directory=src --target=build/docs

.PHONY: run-phpstan
run-phpstan: composer-install
	vendor/bin/phpstan analyse --no-progress --no-interaction --memory-limit=1G

.PHONY: fix-phpstan-baseline
fix-phpstan-baseline: composer-install
	vendor/bin/phpstan analyse --no-progress --no-interaction --generate-baseline phpstan-baseline.neon --memory-limit=1G


##
## Docker commands
##

.PHONY: docker-start
docker-start:
	${DOCKER_COMPOSE_CMD} ${DOCKER_COMPOSE_OPTIONS} up ${DOCKER_OPTIONS}

.PHONY: docker-stop
docker-stop:
	${DOCKER_COMPOSE_CMD} ${DOCKER_COMPOSE_OPTIONS} down

.PHONY: docker-run-phpunit
docker-run-phpunit:
	${DOCKER_COMPOSE_CMD} ${DOCKER_COMPOSE_OPTIONS} exec php env TERM=xterm-256color make run-phpunit PHPUNIT_OPTIONS=${PHPUNIT_OPTIONS}

.PHONY: docker-run-phpcs
docker-run-phpcs:
	${DOCKER_COMPOSE_CMD} ${DOCKER_COMPOSE_OPTIONS} exec php env TERM=xterm-256color make run-phpcs

.PHONY: docker-fix-phpcs
docker-fix-phpcs:
	${DOCKER_COMPOSE_CMD} ${DOCKER_COMPOSE_OPTIONS} exec php env TERM=xterm-256color make fix-phpcs

.PHONY: docker-run-phpstan
docker-run-phpstan:
	${DOCKER_COMPOSE_CMD} ${DOCKER_COMPOSE_OPTIONS} exec php env TERM=xterm-256color vendor/bin/phpstan analyse --no-progress --no-interaction --memory-limit=1G

.PHONY: docker-fix-phpstan-baseline
docker-fix-phpstan-baseline:
	${DOCKER_COMPOSE_CMD} ${DOCKER_COMPOSE_OPTIONS} exec php env TERM=xterm-256color vendor/bin/phpstan analyse --no-progress --no-interaction --generate-baseline phpstan-baseline.neon --memory-limit=1G

.PHONY: docker-shell
docker-shell:
	${DOCKER_COMPOSE_CMD} ${DOCKER_COMPOSE_OPTIONS} exec php sh

## Additional commands

# Visualise repo, requires `gource`
.PHONY: gource
gource:
	gource --log-format git \
		--seconds-per-day 0.1 \
		--title 'Elastica (https://github.com/ruflin/Elastica)' \
		--user-scale 1 \
		--max-user-speed 50
