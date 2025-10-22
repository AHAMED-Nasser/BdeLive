<?php

    spl_autoload_register(function($className){
        $searchPaths = [
            // Models
            __DIR__ . '/../modules/models/' . $className . '.php',
            __DIR__ . '/../modules/models/users/' . $className . '.php',
            __DIR__ . '/../modules/models/pwd/' . $className . '.php',
            __DIR__ . '/../modules/models/public/' . $className . '.php',
            
            // Controllers
            __DIR__ . '/../modules/controllers/class/' . $className . '.php',
            __DIR__ . '/../modules/controllers/users/' . $className . '.php',
            __DIR__ . '/../modules/controllers/pwd/' . $className . '.php',
            __DIR__ . '/../modules/controllers/events/' . $className . '.php',
            __DIR__ . '/../modules/controllers/public/' . $className . '.php',
            
            // Core
            __DIR__ . '/../core/' . $className . '.php'
        ];

        foreach ($searchPaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    });
?>
