<?php

declare(strict_types=1);
/**
 * A script for reseting the token that are expired (clean the table PASSWORD_RESET_TOKEN every 3 hours)
 */
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
