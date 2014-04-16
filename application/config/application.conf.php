<?php
// This is the default ZF2 config
// Refer to http://framework.zend.com/ for further information
return array(
    // Fetch modules defintion
    'modules' => require __DIR__ . '/modules.conf.php',

    // Define additional pathmanager locations
    'path_manager' => array(
        'root' => RAMPAGE_PREFIX,
        'app' => APPLICATION_DIR,
        'etc' => '{{root_dir}}/etc',
    ),

    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => array(
        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => array(
            __DIR__ . '/conf.d/{,*.}{global,local}.php',
            RAMPAGE_PREFIX . '/etc/conf.d/*.conf',
        ),
    ),
);
