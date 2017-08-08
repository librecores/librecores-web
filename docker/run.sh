#!/bin/bash
#
# Entry point for the Docker run.
# Here we keep a dynamic content which needs to be in /var/www/lc/site
#
####

set -e
set -x

# Start LibreCores RabbitMQ
# Duplicates service on common deployment
source /opt/lc/site/app/config/symfony-env.sh
/usr/bin/php /var/www/lc/site/bin/console rabbitmq:consumer -w -l 256 -m 250 update_project_info &

# Install dependencies through composer
ps aux | grep mysql

cd /var/www/lc/site
composer install

# Migrate database
php bin/console doctrine:migrations:migrate -n
#sudo_user: "{{ web_user }}"
#environment: "{{ symfony_config }}"

exec supervisord -n