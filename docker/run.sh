#!/bin/bash
#
# Entry point for the Docker run.
# Here we keep a dynamic content which needs to be in /var/www/lc/site
#
####

set -e
set -x
cd /var/www/lc/site

# Install dependencies through composer
composer install

# Migrate database
php bin/console doctrine:migrations:migrate -n
#sudo_user: "{{ web_user }}"
#environment: "{{ symfony_config }}"

exec supervisord -n