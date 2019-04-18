#!/bin/sh
./bin/console lint:yaml app && \
./bin/console lint:yaml src && \
./bin/console lint:twig app && \
./bin/console lint:twig src && \
./bin/console doctrine:schema:validate --skip-sync && \
./vendor/bin/phpunit --testsuite unit && \
./vendor/bin/phpunit --testsuite functional && \
./vendor/bin/phpcs

