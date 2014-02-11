<?php

Phar::webPhar('rampage-nexus.phar', 'index.php', 'public/404.php', null, function($path) {
    return 'public/' . ltrim($path . '/');
});

require 'phar://' . __FILE__ . '/cli.php';