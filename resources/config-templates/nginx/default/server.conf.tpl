include ${global_configs};

server {
    listen 80;
    server_name ${servername};

    ${server_aliases}

    include ${location_configs};
}
