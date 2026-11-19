<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

// La déconnexion ne supprime aucun compte : seule la session est fermée.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

redirect(BASE_URL . '/index.php?logged_out=1');
