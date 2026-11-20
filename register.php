<?php
session_start();

// Crear el token del formulario.
if (empty($_SESSION["csrf_registro"])) {
    $_SESSION["csrf_registro"] = bin2hex(random_bytes(32));
}

// Recuperar el mensaje del registro anterior.
$mensaje = $_SESSION["mensaje_registro"] ?? "";
unset($_SESSION["mensaje_registro"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registro de colaborador</title>

    <link rel="stylesheet" href="styles.css">
    <script src="scriptsjs/registro.js" defer></script>
</head>

<body class="pagina-registro">

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
                    Registro de colaboradores
                </span>

                <h1>Bienvenido nuevo colaborador</h1>

                <p class="subtitulo">
                    Ingresa tus datos para enviar tu solicitud
                    de cuenta de colaborador.
                </p>

                <?php if ($mensaje !== ""): ?>
                    <p class="aviso-aprobacion" role="status">
                        <?= htmlspecialchars(
                            $mensaje,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </p>
                <?php endif; ?>

                <form
                    id="form-registro"
                    action="procesos_php/guardar_registro.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= htmlspecialchars(
                            $_SESSION["csrf_registro"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                    >

                    <label for="nombre">
                        Nombre del colaborador
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        placeholder="Agrega tu nombre"
                        maxlength="35"
                        autocomplete="name"
                        required
                    >

                    <label for="num_colaborador">
                        Número de colaborador
                    </label>

                    <input
                        type="number"
                        id="num_colaborador"
                        name="num_colaborador"
                        placeholder="12345678"
                        min="1"
                        max="2147483647"
                        step="1"
                        required
                    >

                    <label for="puesto">
                        Elige un cargo
                    </label>

                    <select id="puesto" name="puesto" required>
                        <option value="" disabled selected>
                            Selecciona tu puesto
                        </option>
                        <option value="supervisor">Supervisor</option>
                        <option value="gerente">Gerente</option>
                        <option value="capitan">Capitán</option>
                        <option value="hostess">Hostess</option>
                        <option value="mesero">Mesero</option>
                    </select>

                    <label for="password">
                        Contraseña
                    </label>

                    <div class="campo-password">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="ojo"
                            data-password="password"
                            aria-controls="password"
                            aria-label="Mostrar contraseña"
                        >
                            <svg
                                width="18"
                                height="18"
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

                    <label for="confirmar_password">
                        Repetir contraseña
                    </label>

                    <div class="campo-password">
                        <input
                            type="password"
                            id="confirmar_password"
                            name="confirmar_password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="ojo"
                            data-password="confirmar_password"
                            aria-controls="confirmar_password"
                            aria-label="Mostrar confirmación de contraseña"
                        >
                            <svg
                                width="18"
                                height="18"
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

                    <p class="aviso-aprobacion">
                        Tu solicitud será revisada por el administrador.
                    </p>

                    <button type="submit" class="btn-login">
                        Enviar solicitud
                        <span aria-hidden="true">→</span>
                    </button>

                </form>

                <p class="registro volver-login">
                    <a href="login.php">Regresar al login</a>
                </p>

            </div>
        </div>

    </main>

    <footer class="pie">
        © Valentín Imperial Riviera Maya
    </footer>

</body>
</html>