<?php
session_start();

$csrf = $_POST['csrf'] ?? '';
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !is_string($csrf) ||
    empty($_SESSION['csrf_admin']) ||
    !hash_equals($_SESSION['csrf_admin'], $csrf)
) {
    http_response_code(403);
    exit('No se pudo cerrar la sesión. Vuelve al panel y utiliza su botón Cerrar sesión.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
}
session_destroy();
header('Location: login.php', true, 303);
exit;