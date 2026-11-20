<?php

session_start();

function volverAlRegistro(string $mensaje): void
{
    $_SESSION["mensaje_registro"] = $mensaje;

    header("Location: ../register.php", true, 303);
    exit;
}

// Este archivo únicamente recibe envíos del formulario.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../register.php");
    exit;
}

// Comprobar el token del formulario.
$csrf = $_POST["csrf"] ?? "";

if (
    !is_string($csrf) ||
    empty($_SESSION["csrf_registro"]) ||
    !hash_equals($_SESSION["csrf_registro"], $csrf)
) {
    volverAlRegistro(
        "El formulario expiró. Intenta enviar la solicitud nuevamente."
    );
}

// Comprobar que cada campo sea un texto.
$campos = [
    "nombre",
    "num_colaborador",
    "puesto",
    "password",
    "confirmar_password"
];

foreach ($campos as $campo) {
    if (
        !isset($_POST[$campo]) ||
        !is_string($_POST[$campo])
    ) {
        volverAlRegistro("Completa todos los campos del formulario.");
    }
}

// Recuperar datos.
// No aplicamos trim a las contraseñas.
$nombre = trim($_POST["nombre"]);
$numeroTexto = trim($_POST["num_colaborador"]);
$puesto = $_POST["puesto"];
$password = $_POST["password"];
$confirmacion = $_POST["confirmar_password"];

// Validar el nombre según VARCHAR(35).
if (
    $nombre === "" ||
    mb_strlen($nombre, "UTF-8") > 35
) {
    volverAlRegistro(
        "El nombre es obligatorio y debe tener máximo 35 caracteres."
    );
}

// Validar un entero positivo compatible con tu columna INT.
if (!preg_match('/^[0-9]+$/D', $numeroTexto)) {
    volverAlRegistro("El número de colaborador debe contener solo números.");
}

$numColaborador = filter_var(
    $numeroTexto,
    FILTER_VALIDATE_INT,
    [
        "options" => [
            "min_range" => 1,
            "max_range" => 2147483647
        ]
    ]
);

if ($numColaborador === false) {
    volverAlRegistro("Ingresa un número de colaborador válido.");
}

// Solo se admiten los puestos autorizados.
// El administrador no está incluido.
$puestosPermitidos = [
    "supervisor" => 2,
    "gerente" => 3,
    "capitan" => 4,
    "hostess" => 5,
    "mesero" => 6
];

if (!array_key_exists($puesto, $puestosPermitidos)) {
    volverAlRegistro("Selecciona un puesto válido.");
}

$idTipoUsuario = $puestosPermitidos[$puesto];

// Siempre pendiente al registrarse.
$idStatusColab = 1;

// Bcrypt admite como máximo 72 bytes sin truncamiento.
// strlen cuenta bytes, no caracteres.
if (
    $password === "" ||
    strlen($password) > 72 ||
    strpos($password, "\0") !== false
) {
    volverAlRegistro(
        "La contraseña es obligatoria y no debe superar 72 bytes."
    );
}

if ($password !== $confirmacion) {
    volverAlRegistro("Las contraseñas no coinciden.");
}

try {
    require __DIR__ . "/../conection.php";

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "
        INSERT INTO colaborador (
            nombre,
            contrasena,
            num_colab,
            id_tipo_usuario,
            id_status_colab
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $consulta = mysqli_prepare($conection, $sql);

    mysqli_stmt_bind_param(
        $consulta,
        "ssiii",
        $nombre,
        $hash,
        $numColaborador,
        $idTipoUsuario,
        $idStatusColab
    );

    mysqli_stmt_execute($consulta);
    mysqli_stmt_close($consulta);
    mysqli_close($conection);

    // Renovar el token después del registro.
    unset($_SESSION["csrf_registro"]);

    volverAlRegistro(
        "Solicitud enviada. Tu cuenta está pendiente de aprobación."
    );

} catch (mysqli_sql_exception $error) {
    if ((int) $error->getCode() === 1062) {
        volverAlRegistro(
            "Ese número de colaborador ya tiene una cuenta registrada."
        );
    }

    error_log("Error al registrar colaborador: " . $error->getMessage());

    volverAlRegistro(
        "No se pudo guardar la solicitud. Intenta nuevamente."
    );

} catch (Throwable $error) {
    error_log("Error en registro: " . $error->getMessage());

    volverAlRegistro(
        "No se pudo procesar la solicitud. Intenta nuevamente."
    );
}