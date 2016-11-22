include ${global_configs};

server {
    listen 443 ssl;
    server_name ${servername};

    ${server_aliases}

    include ssl/${servername}.conf;
    include ${location_configs};
}
