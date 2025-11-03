<?php

/**
 * Script to clean expired tokens (every 3 hours).
 */

declare(strict_types=1);

try {
    $passwordReset = new \App\Modules\Models\Pwd\PasswordReset();
    $result = $passwordReset->cleanExpiredTokens();

    if ($result) {
        echo date('Y-m-d H:i:s') . " - Tokens expires nettoyes avec succes\n";
    } else {
        echo date('Y-m-d H:i:s') . " - Aucun token a nettoyer\n";
    }
} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - Erreur : " . $e->getMessage() . "\n";
}
