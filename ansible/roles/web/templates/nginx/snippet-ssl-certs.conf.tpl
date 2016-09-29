ssl_certificate /etc/letsencrypt/live/{{ librecores_domain }}/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/{{ librecores_domain }}/privkey.pem;
ssl_trusted_certificate /etc/letsencrypt/live/{{ librecores_domain }}/chain.pem;
