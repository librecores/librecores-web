server {
    listen 80;

    server_name www.{{ librecores_domain }} {{ librecores_domain }};
    root        /var/www/lc/site/web;

    error_log   /var/log/nginx/librecores/error.log;
    access_log  /var/log/nginx/librecores/access.log;

    rewrite     ^/(app|app_dev)\.php/?(.*)$ /$1 permanent;

    {% if symfony_devel %}
    location / {
        index       app_dev.php;
        try_files   $uri @rewriteapp;
    }


    location @rewriteapp {
        rewrite     ^(.*)$ /app_dev.php/$1 last;
    }
    {% else %}
    location / {
        index       app.php;
        try_files   $uri @rewriteapp;
    }


    location @rewriteapp {
        rewrite     ^(.*)$ /app.php/$1 last;
    }
    {% endif %}

    location ~ ^/(app|app_dev|config)\.php(/|$) {
        fastcgi_pass            php7.0;
        fastcgi_buffer_size     16k;
        fastcgi_buffers         4 16k;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include                 fastcgi_params;
        fastcgi_param           SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param           HTTPS           off;
    }
}
