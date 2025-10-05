<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../modules/models/PasswordReset.php';

try {
    $passwordReset = new PasswordReset();
    $result = $passwordReset->cleanExpiredTokens();
    
    if ($result) {
        echo date('Y-m-d H:i:s') . " - Tokens expires nettoyes avec succes\n";
    } else {
        echo date('Y-m-d H:i:s') . " - Aucun token a nettoyer\n";
    }
    
} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - Erreur : " . $e->getMessage() . "\n";
}
