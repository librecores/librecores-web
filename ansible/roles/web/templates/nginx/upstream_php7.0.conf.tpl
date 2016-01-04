upstream php7.0 {
    server unix:{{ php_fpm_sock }};
}
