location ${location} {
    alias ${document_root};
    set $php_docroot "${document_root}";
    include includes/deployment/php-fastcgi-alias.conf;
}
