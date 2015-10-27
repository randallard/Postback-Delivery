server {
	listen 80;
	listen [::]:80;

	root /var/www/postback-agent.com/html;
	index index.php index.html index.htm;

	server_name postback-agent postback-agent.com;

	location / {
		try_files $uri $uri/ =404;
	}

	error_page 404 /404.html;

	error_page 500 502 503 504 /50x.html;
	location = /50x.html {
		root /usr/share/nginx/html;
	}

    	location /i {
		return 301 $scheme://postback-agent.com/ingest.php;
    	}	

	location ~ \.php$ {
		try_files $uri =404;
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		include fastcgi_params;
	}

}
