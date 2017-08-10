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
rabbitmq-server &
service php7.1-fpm start
service nginx start

if [ $# -eq 1 ]; then
  # if `docker run` only has one arguments, we assume user is running alternate command like `bash` to inspect the image
  echo "Executing custom command..."
  exec "$@"
else
  # Start LibreCores RabbitMQ
  # Duplicates service on common deployment
  source /opt/lc/site/app/config/symfony-env.sh
  
  
  echo "Checking MySQL status..."
  mysql=( mysql -uroot -ppassword )
  for i in {30..0}; do
    if echo 'SELECT 1' | "${mysql[@]}" &> /dev/null; then
      break
    fi
    echo "Waiting for MySQL to start ($i/30s)..."
    sleep 1
  done
  # Composer will fail without DB anyway
  
  rabbitmqctl start_app
  /usr/bin/php /var/www/lc/site/bin/console rabbitmq:consumer -w -l 256 -m 250   update_project_info &

  # Install dependencies through composer
  ps aux | grep mysql

  cd /var/www/lc/site
  composer install

  # Migrate database
  php bin/console doctrine:migrations:migrate -n
  # sudo_user: "{{ web_user }}"
  # environment: "{{ symfony_config }}"
  
  sleep 100500
fi

