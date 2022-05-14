```shell
server {
    listen 80;
    server_name barocco.by www.barocco.by;
    charset utf-8;
    index index.php;
    include /etc/nginx/vhosts-includes/*.conf;
    include /etc/nginx/vhosts-resources/barocco.by/*.conf;
    access_log /var/www/httpd-logs/barocco.by.access.log;
    error_log /var/www/httpd-logs/barocco.by.error.log notice;
    ssi on;
    set $root_path /var/www/www-root/data/www/barocco.by;
    root $root_path;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    gzip on;
    gzip_comp_level 5;
    gzip_disable "msie6";
    gzip_types text/plain text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript application/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~* ^.+\.(jpg|jpeg|gif|png|svg|js|css|mp3|ogg|mpe?g|avi|zip|gz|bz2?|rar|swf)$ {
        expires 24h;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    return 301 https://$host:443$request_uri;
    location ~ \.php$ {
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param PHP_ADMIN_VALUE "sendmail_path = /usr/sbin/sendmail -t -i -f info@barocco.by";
        fastcgi_pass unix:/var/www/php-fpm/1.sock;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        try_files $uri =404;
        include fastcgi_params;
    }
    listen 10.1.1.86:80;
}
server {
    listen 443 ssl;
    server_name barocco.by www.barocco.by;
    ssl_certificate "/var/www/httpd-cert/www-root/barocco.by_custom_1.crtca";
    ssl_certificate_key "/var/www/httpd-cert/www-root/barocco.by_custom_1.key";
    ssl_ciphers EECDH:+AES256:-3DES:RSA+AES:!NULL:!RC4;
    ssl_prefer_server_ciphers on;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    add_header Strict-Transport-Security "max-age=31536000;";
    ssl_dhparam /etc/ssl/certs/dhparam4096.pem;
    charset utf-8;
    index index.php;
    include /etc/nginx/vhosts-includes/*.conf;
    include /etc/nginx/vhosts-resources/barocco.by/*.conf;
    access_log /var/www/httpd-logs/barocco.by.access.log;
    error_log /var/www/httpd-logs/barocco.by.error.log notice;
    ssi on;
    set $root_path /var/www/www-root/data/www/barocco.by;
    root $root_path;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    gzip on;
    gzip_comp_level 5;
    gzip_disable "msie6";
    gzip_types text/plain text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript application/javascript;

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
        fastcgi_pass unix:/var/www/php-fpm/1.sock;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        try_files $uri =404;
        include fastcgi_params;
    }

    listen 10.1.1.86:443 ssl;
}
```shell
