# Default version to start ES on Docker, overide with `START_ES_VERSION=75 make start-docker`
START_ES_VERSION?=72

.PHONY: clean
clean:
	rm tools -fr \
	rm vendor -fr \
	rm build -fr

tools/phive.phar:
	mkdir tools; \
	wget --no-clobber --output-document=tools/phive.phar "https://phar.io/releases/phive.phar" --quiet; \
    wget --no-clobber --output-document=tools/phive.phar.asc "https://phar.io/releases/phive.phar.asc" --quiet; \
    gpg --keyserver hkps.pool.sks-keyservers.net --recv-keys 0x9D8A98B29B2D5D79; \
    gpg --verify tools/phive.phar.asc tools/phive.phar; \
    rm tools/phive.phar.asc; \
    chmod +x tools/phive.phar;

vendor/autoload.php:
	composer update --prefer-dist --no-interaction

tools/phpunit.phar tools/php-cs-fixer.phar: tools/phive.phar
	tools/phive.phar install --copy --trust-gpg-keys 0xE82B2FB314E9906E,0x4AA394086372C20A

.PHONY: install-phpcs
install-phpcs: tools/php-cs-fixer.phar

.PHONY: install-phpunit
install-phpunit: tools/phpunit.phar

.PHONY: composer-update
composer-update: vendor/autoload.php

.PHONY: install-tools
install-tools: install-phpcs install-phpunit

.PHONY: run-phpcs
run-phpcs: composer-update install-phpcs
	tools/php-cs-fixer.phar fix --dry-run --allow-risky=yes -v

.PHONY: fix-phpcs
fix-phpcs: composer-update install-phpcs
	tools/php-cs-fixer.phar fix --allow-risky=yes -v

.PHONY: run-phpunit
run-phpunit: composer-update install-phpunit
	tools/phpunit.phar

.PHONY: run-phpunit-coverage
run-phpunit-coverage: composer-update install-phpunit
	EXIT_STATUS=0 ; \
	tools/phpunit.phar --coverage-clover build/coverage/unit-coverage.xml --group unit || EXIT_STATUS=$$? ; \
	tools/phpunit.phar --coverage-clover build/coverage/functional-coverage.xml --group functional || EXIT_STATUS=$$? ; \
	exit $$EXIT_STATUS

.PHONY: run-coveralls
run-coveralls:
	tools/php-coveralls.phar -v

tools/phpdocumentor.phar:
	curl https://gitreleases.dev/gh/phpDocumentor/phpDocumentor/latest/phpDocumentor.phar -o tools/phpdocumentor.phar --silent -L
	chmod +x tools/phpdocumentor.phar

.PHONY: run-phpdoc
run-phpdoc: tools/phpdocumentor.phar
	tools/phpdocumentor.phar --directory=lib --target=build/docs --template=clean

.PHONY: start-docker
start-docker:
	docker-compose --file=docker/docker-compose.yml --file=docker/docker-compose.es${START_ES_VERSION}.yml up

## Additional commands

# Visualise repo, requires `gource`
.PHONY: gource
gource:
	gource --log-format git \
		--seconds-per-day 0.1 \
		--title 'Elastica (https://github.com/ruflin/Elastica)' \
		--user-scale 1 \
		--max-user-speed 50
