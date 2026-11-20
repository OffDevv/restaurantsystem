<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numero = filter_var($_POST["num_colaborador"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

    $password = $_POST["password"] ?? "";

    if (
        $numero === false ||
        !is_string($password) ||
        $password === ""
    ) {
        $error = "Ingresa un número de colaborador válido y tu contraseña.";
    } else {
        try {
            require __DIR__ . "/conection.php";

            $consulta = mysqli_prepare(
                $conection,
                "SELECT id, contrasena, id_tipo_usuario, id_status_colab
                 FROM colaborador
                 WHERE num_colab = ?"
            );

            mysqli_stmt_bind_param($consulta, "i", $numero);
            mysqli_stmt_execute($consulta);

            $resultado = mysqli_stmt_get_result($consulta);
            $usuario = mysqli_fetch_assoc($resultado);

            mysqli_stmt_close($consulta);

            if (
                !$usuario ||
                !is_string($usuario["contrasena"]) ||
                !password_verify($password, $usuario["contrasena"])
            ) {
                $error = "Número de colaborador o contraseña incorrectos.";
            } elseif ((int) $usuario["id_status_colab"] !== 3) {
                $error = "Tu cuenta está pendiente de aprobación o inactiva.";
            }else {
    session_regenerate_id(true);

    $_SESSION["id_colaborador"] = (int) $usuario["id"];

    $tipo = (int) $usuario["id_tipo_usuario"];

    if ($tipo === 1 || $tipo === 2 || $tipo === 3) {
        header("Location: admin.php");
    } else {
        header("Location: view_colaborador.php");
    }

    exit;
}

            mysqli_close($conection);
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $error = "No se pudo iniciar sesión. Intenta nuevamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login de colaborador</title>

    <link rel="stylesheet" href="styles.css">
    <script src="scriptsjs/logueo.js" defer></script>
</head>

<body>
    <div class="fondo-decorativo" aria-hidden="true">
        <div class="circulo-blanco"></div>
    </div>

    <img
        class="logo"
        src="images/logo.png"
        alt="Valentín Imperial"
    >

    <main class="principal-content">
        <img
            class="recorte1"
            src="images/im.jpeg"
            alt="Restaurante L’Alsace"
        >

        <div class="formulario-login-colaborador">
            <div class="login-box">
                <span class="eyebrow">
                    Acceso de colaboradores
                </span>

                <h1>Bienvenido de nuevo</h1>

                <p class="subtitulo">
                    Ingresa tus credenciales para acceder como colaborador.
                </p>

                <?php if ($error !== ""): ?>
                    <p class="aviso-aprobacion" role="alert">
                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </p>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <label for="num_colaborador">
                        Número de colaborador
                    </label>

                    <input
                        type="text"
                        id="num_colaborador"
                        name="num_colaborador"
                        placeholder="12345678"
                        inputmode="numeric"
                        pattern="[0-9]+"
                        autocomplete="username"
                        required
                    >

                    <label for="password">
                        Contraseña
                    </label>

                    <div class="campo-password">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="ojo"
                            id="togglePassword"
                            aria-controls="password"
                            aria-label="Mostrar contraseña"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>

                    <div class="fila-opciones">
                        <label class="recordarme">
                            <input
                                type="checkbox"
                                name="recordarme"
                                disabled
                            >
                            Recordarme (próximamente)
                        </label>
                    </div>

                    <button type="submit" class="btn-login">
                        Iniciar sesión
                    </button>
                </form>

                <p class="registro">
                    ¿Aún no tienes cuenta?
                    <a href="register.php">Crear cuenta</a>
                </p>
            </div>
        </div>
    </main>

    <footer class="pie">
        © Valentín Imperial Riviera Maya
    </footer>
</body>
</html>