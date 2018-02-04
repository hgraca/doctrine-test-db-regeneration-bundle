CURRENT_BRANCH="$(shell git rev-parse --abbrev-ref HEAD)"

default: help

help:
	@echo "Usage:"
	@echo "     make [command]"
	@echo "Available commands:"
	@grep '^[^#[:space:]].*:' Makefile | grep -v '^default' | grep -v '^_' | sed 's/://' | xargs -n 1 echo ' -'

build-container-tst:
	docker build -t hgraca/doctrine-test-db-regeneration-bundle:tst.php_7_1 -f ./tests/build/container/tst/dockerfile ./tests/build/container/tst
	docker push hgraca/doctrine-test-db-regeneration-bundle:tst.php_7_1

coverage:
	composer dumpautoload
	bin/coverage
	bin/fix_cs

dep-install:
	composer install

dep-update:
	composer update

fix-cs:
	bin/fix_cs

test:
	composer dumpautoload
	bin/test
	bin/fix_cs
