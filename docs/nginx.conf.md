```shell
server {
    listen 80;
    server_name barocco.by www.barocco.by;
    return 301 https://$host:443$request_uri;

    listen 10.1.1.88:80;
}
server {
    listen 443 ssl http2;
    server_name barocco.by www.barocco.by;

    ssl_certificate "/etc/nginx/ssl-certs/barocco.by.crt";
    ssl_certificate_key "/etc/nginx/ssl-certs/barocco.by.key";
    ssl_ciphers EECDH:+AES256:-3DES:RSA+AES:!NULL:!RC4;
    ssl_prefer_server_ciphers on;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;

    add_header Strict-Transport-Security "max-age=31536000;";
    ssl_dhparam /etc/ssl/certs/dhparam4096.pem;
    charset utf-8;
    index index.php;
    access_log /var/log/nginx/barocco.by.access.log;
    error_log /var/log/nginx/barocco.by.error.log notice;
    ssi on;
    set $root_path /var/www/barocco.by;
    root $root_path;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    gzip on;
    gzip_comp_level 5;
    gzip_disable "msie6";
    gzip_types text/plain text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript application/javascript;

    location = /livewire/livewire.js {
        expires off;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /livewire/livewire.min.js {
        expires off;
        try_files $uri $uri/ /index.php?$query_string;
    }

    ## Убираю index.php / index.html
    if ($request_uri ~ "^(.*)index\.(?:php|html)") {
        return 301 $1;
    }
    ## Убрать / с конца url
    location ~ .+/$ {
        rewrite (.+)/$ $1 permanent;
    }
    ## Все обрабатывает index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~* ^.+\.(jpg|jpeg|gif|png|svg|js|css|mp3|ogg|mpe?g|avi|zip|gz|bz2?|rar|swf)$ {
        expires 24h;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param PHP_ADMIN_VALUE "sendmail_path = /usr/sbin/sendmail -t -i -f info@barocco.by";
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_buffers 16 32k;
        fastcgi_buffer_size 64k;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        try_files $uri =404;
        include fastcgi_params;
    }

    listen 10.1.1.88:443 ssl http2;
}
```shell
