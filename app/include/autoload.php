<?php

    spl_autoload_register(function($className){
        $paths = [
            __DIR__ . '/../config/' . $className . '.php',
            __DIR__ . '/../modules/models/' . $className . '.php',
            __DIR__ . '/../modules/controllers/' . $className . '.php',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    });
?>
