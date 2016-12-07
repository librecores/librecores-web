server {
    listen 80 default_server;
    listen [::]:80 default_server;

    error_log   /var/log/nginx/librecores/error.log;
    access_log  /var/log/nginx/librecores/access.log;

    server_name www.{{ librecores_domain }} {{ librecores_domain }};


    {% if use_https %}
    # Let's Encrypt cert-checking location must be accessible over plain HTTP
    location ^~ /.well-known/acme-challenge/ {
        default_type "text/plain";
        root         /var/www/letsencrypt;
    }

    # Hide /acme-challenge subdirectory and return 404 on all requests.
    # It is somewhat more secure than letting Nginx return 403.
    # Ending slash is important!
    location = /.well-known/acme-challenge/ {
        return 404;
    }

    # redirect all traffic to HTTPS
    location / {
        return 301 https://$server_name$request_uri;
    }

    {% else %}
    include snippets/{{ librecores_domain }}.conf;
    {% endif %}
}

{% if use_https %}
server {
    listen 443 ssl http2 default_server;
    listen [::]:443 ssl http2 default_server;

    include snippets/ssl-{{ librecores_domain }}.conf;
    include snippets/ssl-params.conf;

    error_log   /var/log/nginx/librecores/error.log;
    access_log  /var/log/nginx/librecores/access.log;

    include snippets/{{ librecores_domain }}.conf;
}
{% endif %}
