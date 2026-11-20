<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login colaborador</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="fondo-decorativo" aria-hidden="true">
    <div class="circulo-blanco"></div>
    </div>
    <img class="logo" src="images/logo.png" alt="logo valentin">

    <main class="principal-content">
        <!--Esto va a la izquierda-->
        <img class="recorte1" src="images/im.jpeg" alt="restaurante L'Alsace">
        <!--Esto va a la derecha-->
        <div class="formulario-login-colaborador">
    <div class="login-box">
        <span class="eyebrow">Acceso de colaboradores</span>
        <h1>Bienvenido de nuevo</h1>
        <p class="subtitulo">Ingresa tus credenciales para acceder como colaborador</p>

        <form action="login.php" method="POST">
            <label for="num_colaborador">Número colaborador</label>
            <input type="text" id="num_colaborador" name="num_colaborador" placeholder="12345678" required>

            <label for="password">Contraseña</label>
            <div class="campo-password">
                <input type="password" id="password" name="password" required>
                <button type="button" class="ojo" id="togglePassword" aria-label="Mostrar contraseña">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            <div class="fila-opciones">
                <label class="recordarme">
                    <input type="checkbox" name="recordarme"> Recordarme
                </label>
                <!--<a href="#">¿Olvidaste tu contraseña?</a> -->
            </div>

            <button type="submit" class="btn-login">
                Iniciar sesión <span>→</span>
            </button>
        </form>

        <p class="registro">¿Aún no tienes cuenta? <a href="register.php">Crear cuenta</a></p>
    </div>
</div>



    </main>
    <footer class="pie">© Valentin Imperial Riviera Maya</footer>
    
    <script>
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    togglePassword.addEventListener('click', () => {
    const mostrar = password.type === 'password';
    password.type = mostrar ? 'text' : 'password';
    togglePassword.setAttribute(
        'aria-label',
        mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña'
    );
});
</script>
</body>
</html>
<?php
?>

