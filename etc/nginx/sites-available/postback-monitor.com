upstream monitor_node {
    server postback-monitor.com:3000;
}

server {
	server_name postback-monitor postback-monitor.com;

	location / {
	        proxy_set_header Upgrade $http_upgrade;
        	proxy_set_header Connection "upgrade";
        	proxy_http_version 1.1;
	        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
	        proxy_set_header Host $host;
	        proxy_pass http://monitor_node;
	}
}
