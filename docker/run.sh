#!/bin/bash
#
# Entry point for the Docker run.
# Here we keep a dynamic content which needs to be in /var/www/lc/site
#
####

set -e
set -x

echo "Starting services..."
echo "listen.allowed_clients = 127.0.0.1" >> /etc/php/7.1/fpm/pool.d/www.conf
mysqld &

service rabbitmq-server start
service php7.1-fpm start
service nginx start

echo "Initializing RabbitMQ"
export DEV_PASSWORD=password
rabbitmqctl add_user admin "${DEV_PASSWORD}"
rabbitmqctl set_user_tags admin administrator
rabbitmqctl set_permissions -p / admin ".*" ".*" ".*"
rabbitmqctl delete_user guest
rabbitmqctl add_user librecores "${DEV_PASSWORD}"
rabbitmqctl set_permissions -p / librecores ".*" ".*" ".*"

if [ $# -eq 1 ]; then
  # if `docker run` only has one arguments, we assume user is running alternate command like `bash` to inspect the image
  echo "Executing custom command..."
  exec "$@"
else
  export SYMFONY_ENV=dev
  if [ -n "$SYMFONY_DEBUG" ]; then
    echo "Running Symfony with SYMFONY_DEBUG=${SYMFONY_DEBUG}"
    export SYMFONY_DEBUG=$SYMFONY_DEBUG
  else
    echo "Running Symfony without Debug"
    export SYMFONY_DEBUG=0
  fi
  
  echo "Checking MySQL status..."
  mysql=( mysql -uroot -p"${DEV_PASSWORD}" )
  for i in {30..0}; do
    if echo 'SELECT 1' | "${mysql[@]}" &> /dev/null; then
      break
    fi
    echo "Waiting for MySQL to start ($i/30s)..."
    sleep 1
  done
  # Composer will fail without DB anyway
  
  #TODO: start Rabbit
  # rabbitmqctl start_app
  /usr/bin/php /var/www/lc/site/bin/console rabbitmq:consumer -w -l 256 -m 250   update_project_info &

  # Install dependencies through composer
  ps aux | grep mysql

  cd /var/www/lc/site
  composer install

  # Migrate database and initialize test data
  echo "Initializing LibreCores data..."
  php bin/console doctrine:migrations:migrate -n
  php bin/console doctrine:fixtures:load -n
  php bin/console librecores:update-repos
  php bin/console cache:warmup
  
  echo "Initializing LibreCores Planet data..."
  cd /var/www/lc/planet
  /var/www/lc/planet/generate.sh
  
  exec sleep 100500
fi

