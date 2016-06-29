location ${location} {
    alias ${document_root};
    include fastcgi.conf;

    location ~ \.php$ {
        include fastcgi.conf;

        fastcgi_param ALIAS_ROOT ${document_root};
        fastcgi_param SCRIPT_FILENAME ${document_root}$fastcgi_script_name;
    }
}
