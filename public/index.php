<?php
require '../app/controllers/RegisterController.php';
try {
    if (filter_input(INPUT_GET, 'action')) {
        if ($_GET['action'] === 'register') {
            echo "register";
            (new RegisterController())->execute();
        }
        else {
            echo 'La page que vous recherchez n\'existe pas';
        }
    }
// (new \Blog\Controllers\Homepage\Homepage())->execute();
} catch (ControllerException $e) {
    echo $e->getMessage();
}

