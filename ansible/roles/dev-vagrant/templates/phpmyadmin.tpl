server {
    listen 80;
    listen [::]:80;

    error_log   /var/log/nginx/phpmyadmin/error.log;
    access_log  /var/log/nginx/phpmyadmin/access.log;

    server_name pma.{{ librecores_domain }};

    root /var/www/phpmyadmin;
    index index.php index.html;

    location ~ [^/]\.php(/|$) {

        fastcgi_pass php{{ php_version }};

        fastcgi_split_path_info ^(.+?\.php)(/.*)$;

        if (!-f $document_root$fastcgi_script_name) {
            return 404;
        }

        # Mitigate https://httpoxy.org/ vulnerabilities
        fastcgi_param HTTP_PROXY "";
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;

        include fastcgi_params;
    }
}