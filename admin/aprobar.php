<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Verificar sesión
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Verificar Super Admin
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: ../index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Verificar ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$usuario_id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Buscar usuario
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id, nombre, apellido, correo, correo_verificado, estado
    FROM usuarios
    WHERE id = :id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);
$stmt->execute([
    ':id' => $usuario_id
]);

$usuario = $stmt->fetch();

if (!$usuario) {
    header("Location: usuarios.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Validar que el correo esté verificado
|--------------------------------------------------------------------------
*/

if ((int)$usuario['correo_verificado'] !== 1) {
    die("No se puede aprobar el usuario porque su correo no ha sido verificado.");
}

/*
|--------------------------------------------------------------------------
| Validar estado
|--------------------------------------------------------------------------
*/

if ($usuario['estado'] !== 'pendiente') {
    header("Location: usuarios.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Aprobar usuario
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE usuarios
    SET
        estado = 'activo',
        fecha_aprobacion = NOW(),
        aprobado_por = :aprobado_por
    WHERE id = :id
    AND correo_verificado = 1
    AND estado = 'pendiente'
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':aprobado_por' => $_SESSION['usuario_id'],
    ':id' => $usuario_id
]);

/*
|--------------------------------------------------------------------------
| Registrar auditoría
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO auditoria (
        usuario_id,
        accion,
        descripcion,
        realizado_por,
        ip
    )
    VALUES (
        :usuario_id,
        'APROBACION_USUARIO',
        :descripcion,
        :realizado_por,
        :ip
    )
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':usuario_id' => $usuario_id,
    ':descripcion' =>
        'El usuario ' .
        $usuario['nombre'] . ' ' .
        $usuario['apellido'] .
        ' fue aprobado por el Super Admin.',
    ':realizado_por' => $_SESSION['usuario_id'],
    ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
]);

/*
|--------------------------------------------------------------------------
| Redirigir
|--------------------------------------------------------------------------
*/

header("Location: usuarios.php?estado=pendiente&mensaje=aprobado");
exit;

?>