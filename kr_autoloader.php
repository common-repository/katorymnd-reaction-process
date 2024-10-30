<?php

spl_autoload_register(function ($class) {
    // The base directory for the namespace prefix
    $base_dir = plugin_dir_path(__FILE__);

    // The namespace prefixes we're using in our classes
    $prefixes = [
        'Kr_page_details\\Katorymnd_reaction\\' => $base_dir . 'kr_class/',
        'Kr_user_details\\Katorymnd_reaction\\' => $base_dir . 'kr_class/',
    ];

    // Loop through the namespace prefixes
    foreach ($prefixes as $prefix => $dir) {
        // Does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // No, move to the next prefix
            continue;
        }

        // Get the relative class name
        $relative_class = substr($class, $len);

        // Replace namespace separators with directory separators in the relative class name, append with .php
        $file = $dir . str_replace('\\', '/', $relative_class) . '.php';

        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
            break; // Break the loop after loading the class
        }
    }
});
