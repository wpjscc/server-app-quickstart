server {
    listen *:80;
    server_name {{domain}};

    root /www/sites/{{application}}/current/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri /index.php =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        #fastcgi_pass unix:/run/php/php7.2-fpm.sock;
        fastcgi_pass   php:9000;
	fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
	    fastcgi_read_timeout 600;
    }

    location ~ /.well-known {
        allow all;
    }

    access_log /var/log/nginx/{{domain}}.access.log;
    error_log  /var/log/nginx/{{domain}}.error.log;
    client_max_body_size 200M;
    location /storage/chat/files {
        add_header 'Access-Control-Allow-Origin' '*';
        add_header 'Access-Control-Expose-Headers' 'Content-Disposition';
        add_header Content-disposition "attachment";
    }

    {{ssl}}
#if ($scheme != "https") {
#     return 301 https://$host$request_uri;
#}

}