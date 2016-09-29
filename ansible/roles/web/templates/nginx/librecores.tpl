server {
    listen 80 default_server;
    listen [::]:80 default_server;

    error_log   /var/log/nginx/librecores/error.log;
    access_log  /var/log/nginx/librecores/access.log;

    server_name www.{{ librecores_domain }} {{ librecores_domain }};

    {% if use_https %}
    # redirect all traffic to HTTPS
    return 301 https://$server_name$request_uri;
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
