.PHONY: clean
clean:
	rm tools -fr \
	rm vendor -fr

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

.PHONY: run-phpunit
run-phpunit: composer-update install-phpunit
	tools/phpunit.phar

