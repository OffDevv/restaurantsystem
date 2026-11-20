<?php
require __DIR__ . '/procesos_php/verificar_admin.php';

// Escapar textos antes de mostrarlos en HTML.
function texto($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function nombrePuesto($id, $nombre): string
{
    $nombres = [2 => 'Supervisor', 3 => 'Gerente', 4 => 'Capitán', 5 => 'Hostess', 6 => 'Mesero'];
    return $nombres[$id] ?? $nombre;
}

if (empty($_SESSION['csrf_admin'])) {
    $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
}

$mensaje = $_SESSION['mensaje_admin'] ?? '';
unset($_SESSION['mensaje_admin']);

$vista = ($_GET['vista'] ?? '') === 'colaboradores' ? 'colaboradores' : 'solicitudes';
$estados = [1 => 'Pendientes', 3 => 'Aceptados', 2 => 'Inactivos / rechazados'];
$estado = filter_var($_GET['estado'] ?? 1, FILTER_VALIDATE_INT);
if (!isset($estados[$estado])) {
    $estado = 1;
}

$buscar = is_string($_GET['buscar'] ?? null) ? trim($_GET['buscar']) : '';
$puesto = filter_var($_GET['puesto'] ?? 0, FILTER_VALIDATE_INT);
if (!in_array($puesto, [0, 2, 3, 4, 5, 6], true)) {
    $puesto = 0;
}

$titulo = $vista === 'colaboradores' ? 'Colaboradores registrados' : 'Solicitudes de registro';
$totales = [1 => 0, 2 => 0, 3 => 0];

try {
    $resultado = mysqli_query(
        $conection,
        'SELECT id_status_colab, COUNT(*) AS cantidad
         FROM colaborador WHERE id_tipo_usuario <> 1
         GROUP BY id_status_colab'
    );
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $totales[(int) $fila['id_status_colab']] = (int) $fila['cantidad'];
    }

    $puestos = mysqli_query(
        $conection,
        'SELECT id, tipo_usuario_ FROM tipo_usuario WHERE id IN (2, 3, 4, 5, 6) ORDER BY id'
    );

    // Los filtros se envían como parámetros; no se concatenan al SQL.
    $estadoConsulta = $vista === 'colaboradores' ? 0 : $estado;
    $patron = '%' . $buscar . '%';
    $consulta = mysqli_prepare(
        $conection,
        'SELECT c.id, c.nombre, c.num_colab, c.id_tipo_usuario,
                c.id_status_colab, t.tipo_usuario_, s.statusc
         FROM colaborador AS c
         INNER JOIN tipo_usuario AS t ON t.id = c.id_tipo_usuario
         INNER JOIN status_colab AS s ON s.id = c.id_status_colab
         WHERE c.id_tipo_usuario <> 1
           AND (? = 0 OR c.id_status_colab = ?)
           AND (? = 0 OR c.id_tipo_usuario = ?)
           AND (c.nombre LIKE ? OR CAST(c.num_colab AS CHAR) LIKE ?)
         ORDER BY c.id DESC'
    );
    mysqli_stmt_bind_param(
        $consulta, 'iiiiss',
        $estadoConsulta, $estadoConsulta, $puesto, $puesto, $patron, $patron
    );
    mysqli_stmt_execute($consulta);
    $usuarios = mysqli_stmt_get_result($consulta);
    $cantidad = mysqli_num_rows($usuarios);
} catch (mysqli_sql_exception $error) {
    error_log('Error al consultar solicitudes: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudieron consultar los colaboradores. Intenta nuevamente.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= texto($titulo) ?> | Administración</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="panel-admin">
        <aside class="barra-lateral">
            <a class="marca" href="admin.php" aria-label="Administración Valentín Imperial">
                <img src="images/logo.png" alt="Valentín Imperial Riviera Maya">
            </a>
            <p class="etiqueta-menu">ADMINISTRACIÓN</p>
            <nav aria-label="Navegación de administración">
                <a href="admin.php" class="enlace-menu <?= $vista === 'solicitudes' ? 'seleccionado' : '' ?>"
                   <?= $vista === 'solicitudes' ? 'aria-current="page"' : '' ?>>
                    Solicitudes <span class="contador"><?= $totales[1] ?></span>
                </a>
                <a href="admin.php?vista=colaboradores" class="enlace-menu <?= $vista === 'colaboradores' ? 'seleccionado' : '' ?>"
                   <?= $vista === 'colaboradores' ? 'aria-current="page"' : '' ?>>Colaboradores</a>
            </nav>
            <div class="perfil">
                <strong><?= texto($administrador['nombre']) ?></strong>
                <span>Administrador · Valentín Imperial</span>
                <form action="procesos_php/cerrar_sesion.php" method="POST">
                    <input type="hidden" name="csrf" value="<?= texto($_SESSION['csrf_admin']) ?>">
                    <button class="salir" type="submit">Cerrar sesión</button>
                </form>
            </div>
        </aside>

        <main class="contenido-admin">
            <header class="barra-superior">
                <p>Administración / <?= $vista === 'colaboradores' ? 'Colaboradores' : 'Solicitudes' ?></p>
                <span class="distintivo">Acceso de administrador</span>
            </header>
            <section class="encabezado" aria-labelledby="titulo">
                <p class="antetitulo">CONTROL DE ACCESOS</p>
                <h1 id="titulo"><?= texto($titulo) ?></h1>
                <p><?= $vista === 'colaboradores'
                    ? 'Consulta las cuentas del personal, sus puestos y su estado actual.'
                    : 'Revisa los datos de tus colaboradores y decide quién puede acceder a la plataforma.' ?></p>
            </section>

            <?php if ($mensaje !== ''): ?>
                <p class="mensaje" role="status"><?= texto($mensaje) ?></p>
            <?php endif; ?>

            <section class="tarjeta" aria-label="Lista de colaboradores">
                <?php if ($vista === 'solicitudes'): ?>
                    <nav class="estados" aria-label="Filtrar por estado">
                        <?php foreach ($estados as $idEstado => $nombreEstado): ?>
                            <?php $enlace = 'admin.php?' . http_build_query([
                                'estado' => $idEstado, 'buscar' => $buscar, 'puesto' => $puesto
                            ]); ?>
                            <a href="<?= texto($enlace) ?>"
                               class="<?= $estado === $idEstado ? 'estado-activo' : '' ?>"
                               <?= $estado === $idEstado ? 'aria-current="page"' : '' ?>>
                                <?= texto($nombreEstado) ?> (<?= $totales[$idEstado] ?>)
                            </a>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>

                <form class="filtros" action="admin.php" method="GET">
                    <input type="hidden" name="vista" value="<?= texto($vista) ?>">
                    <input type="hidden" name="estado" value="<?= $estado ?>">
                    <div class="campo-buscar">
                        <label for="buscar">Buscar colaborador</label>
                        <input type="search" id="buscar" name="buscar" value="<?= texto($buscar) ?>"
                               placeholder="Nombre o número de colaborador">
                    </div>
                    <div class="campo-puesto">
                        <label for="puesto">Puesto</label>
                        <select id="puesto" name="puesto">
                            <option value="0">Todos los puestos</option>
                            <?php while ($cargo = mysqli_fetch_assoc($puestos)): ?>
                                <option value="<?= (int) $cargo['id'] ?>" <?= $puesto === (int) $cargo['id'] ? 'selected' : '' ?>>
                                    <?= texto(nombrePuesto((int) $cargo['id'], $cargo['tipo_usuario_'])) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button class="boton boton-filtrar" type="submit">Filtrar</button>
                </form>

                <div class="contenedor-tabla">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Nombre del colaborador</th>
                                <th scope="col">N.º colaborador</th>
                                <th scope="col">Puesto</th>
                                <th scope="col"><?= $vista === 'solicitudes' && $estado === 1 ? 'Acciones' : 'Estado' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($cantidad === 0): ?>
                                <tr><td colspan="4" class="sin-resultados">No hay colaboradores que coincidan con esta consulta.</td></tr>
                            <?php endif; ?>
                            <?php while ($usuario = mysqli_fetch_assoc($usuarios)): ?>
                                <tr>
                                    <td><?= texto($usuario['nombre']) ?></td>
                                    <td><?= texto($usuario['num_colab']) ?></td>
                                    <td><?= texto(nombrePuesto((int) $usuario['id_tipo_usuario'], $usuario['tipo_usuario_'])) ?></td>
                                    <td>
                                        <?php if ($vista === 'solicitudes' && $estado === 1): ?>
                                            <form class="acciones" action="procesos_php/procesar_solicitud.php" method="POST">
                                                <input type="hidden" name="csrf" value="<?= texto($_SESSION['csrf_admin']) ?>">
                                                <input type="hidden" name="id_colaborador" value="<?= (int) $usuario['id'] ?>">
                                                <button type="submit" name="accion" value="aceptar" class="boton aceptar"
                                                        aria-label="Aceptar solicitud de <?= texto($usuario['nombre']) ?>">Aceptar</button>
                                                <button type="submit" name="accion" value="rechazar" class="boton rechazar"
                                                        aria-label="Rechazar solicitud de <?= texto($usuario['nombre']) ?>">Rechazar</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="estado-cuenta estado-<?= (int) $usuario['id_status_colab'] ?>"><?= texto(ucfirst($usuario['statusc'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pie-tabla">
                    <span><?= $cantidad ?> resultado<?= $cantidad === 1 ? '' : 's' ?> en esta consulta</span>
                    <span>Últimos registros primero</span>
                </div>
            </section>
            <footer class="ayuda">
                <strong>Cada registro necesita tu aprobación</strong>
                <p>Al aceptar una solicitud, autorizas el acceso de esa cuenta con el puesto indicado.</p>
            </footer>
        </main>
    </div>
</body>
</html>