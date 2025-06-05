.PHONY: lint test

lint:
    vendor/bin/phpcs --standard=PSR12 src tests bin/gendiff

test:
    vendor/bin/phpunit --colors=always
setup:
	composer install --prefer-dist --no-interaction