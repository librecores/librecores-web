# generated 2020-05-10, Mozilla Guideline v5.4, nginx 1.10.3, OpenSSL 1.0.2g, intermediate configuration
# https://ssl-config.mozilla.org/#server=nginx&version=1.10.3&config=intermediate&openssl=1.0.2g&guideline=5.4
#
# Supports Firefox 27, Android 4.4.2, Chrome 31, Edge, IE 11 on Windows 7,
# Java 8u31, OpenSSL 1.0.1, Opera 20, and Safari 9

ssl_session_timeout 1d;
ssl_session_cache shared:SSL:10m;  # about 40000 sessions

# Diffie-Hellman parameter for DHE ciphersuites, recommended 2048 bits
ssl_dhparam /etc/ssl/certs/dhparam.pem;

# intermediate configuration
ssl_protocols TLSv1.2;
ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384;
ssl_prefer_server_ciphers off;

# HSTS (ngx_http_headers_module is required) (63072000 seconds)
add_header Strict-Transport-Security "max-age=63072000" always;

# OCSP stapling
ssl_stapling on;
ssl_stapling_verify on;

resolver 172.31.0.2 valid=300s;
resolver_timeout 5s;
