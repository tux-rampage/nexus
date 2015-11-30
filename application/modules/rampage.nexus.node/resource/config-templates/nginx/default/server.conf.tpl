include ${global_configs};

server {
    listen 80;
    server_name ${servername};

    ${server_aliases}

    include ${location_configs};
}

server {
    listen 443 ssl;
    server_name ${servername};

    ${server_aliases}

    ssl_certificate ${ssl_cert_file};
    ssl_certificate ${ssl_key_file};
    ${ssl_chain_directive}

    include ${location_configs};
}