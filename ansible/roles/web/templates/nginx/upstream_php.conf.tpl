upstream php{{ php_version }} {
    server unix:{{ php_fpm_sock }};
}
