<?php
// Este archivo protege tanto la pantalla como las acciones del administrador.
session_start();

$idSesion = filter_var(
    $_SESSION['id_colaborador'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($idSesion === false || $idSesion === null) {
    header('Location: login.php', true, 303);
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    require __DIR__ . '/../conection.php';
    mysqli_set_charset($conection, 'utf8mb4');

    // Se comprueba el permiso real en la base, no en un campo del formulario.
    $consulta = mysqli_prepare(
        $conection,
        'SELECT id, nombre FROM colaborador
         WHERE id = ? AND id_tipo_usuario IN (1,2,3) AND id_status_colab = 3'
    );
    mysqli_stmt_bind_param($consulta, 'i', $idSesion);
    mysqli_stmt_execute($consulta);
    $administrador = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
    mysqli_stmt_close($consulta);
} catch (mysqli_sql_exception $error) {
    error_log('Error al verificar administrador: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo verificar el acceso. Intenta nuevamente.');
}

if (!$administrador) {
    http_response_code(403);
    exit('Esta sección requiere una cuenta de administrador activa.');
}