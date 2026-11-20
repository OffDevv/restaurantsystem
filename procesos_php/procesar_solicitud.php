<?php
require __DIR__ . '/verificar_admin.php';

function regresar(string $mensaje): void
{
    $_SESSION['mensaje_admin'] = $mensaje;
    header('Location: ../admin.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php', true, 303);
    exit;
}

$csrf = $_POST['csrf'] ?? '';
if (
    !is_string($csrf) ||
    empty($_SESSION['csrf_admin']) ||
    !hash_equals($_SESSION['csrf_admin'], $csrf)
) {
    regresar('El formulario expiró. Recarga el panel e intenta nuevamente.');
}

$id = filter_var(
    $_POST['id_colaborador'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$accion = $_POST['accion'] ?? '';
if (!$id || !in_array($accion, ['aceptar', 'rechazar'], true)) {
    regresar('La solicitud recibida no es válida.');
}

// Tus estados reales: 1 = pendiente, 2 = inactivo, 3 = activo.
$estadoNuevo = $accion === 'aceptar' ? 3 : 2;

try {
    // Solo modifica cuentas pendientes que no sean de administrador.
    // La condición evita sobrescribir una decisión realizada por otra persona.
    $consulta = mysqli_prepare(
        $conection,
        'UPDATE colaborador SET id_status_colab = ?
         WHERE id = ? AND id_status_colab = 1 AND id_tipo_usuario <> 1'
    );
    mysqli_stmt_bind_param($consulta, 'ii', $estadoNuevo, $id);
    mysqli_stmt_execute($consulta);
    $cambios = mysqli_stmt_affected_rows($consulta);
    mysqli_stmt_close($consulta);

    if ($cambios !== 1) {
        regresar('No se modificó la cuenta: ya fue revisada o no es una solicitud válida.');
    }

    regresar($accion === 'aceptar'
        ? 'Solicitud aceptada. La cuenta ahora está activa.'
        : 'Solicitud rechazada. La cuenta quedó inactiva.');
} catch (mysqli_sql_exception $error) {
    error_log('Error al actualizar solicitud: ' . $error->getMessage());
    regresar('No se pudo actualizar la solicitud. Intenta nuevamente.');
}