#!/bin/sh
./bin/console lint:yaml app && \
./bin/console lint:yaml src && \
./bin/console lint:twig app && \
./bin/console lint:twig src && \
./bin/console doctrine:schema:validate && \
./vendor/bin/phpunit && \
./vendor/bin/phpcs

