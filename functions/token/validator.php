<?php
function checkBearerToken() {
    // Ellenőrizzük, hogy a kérés tartalmazza-e a token-t
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $results['status'] = 401;
        $results['errorInfo'] = 'Hiányzó token';
        return $results;
    }

    // Kinyerjük a token-t a fejlécből
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];

    // Ellenőrizzük, hogy megfelelő-e a formátuma
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches) !== 1) {
        $results['status'] = 401;
        $results['errorInfo'] = 'Hibás token formátum';
        return $results;
    }

    // A token
    $token = $matches[1];

    $results['status'] = 200;
    $results['token'] = $token;

    // Ha minden rendben van, visszatérünk a token-nel
    return $results;
}