<?php

require_once "../config/config.php";
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: login.php");

    exit;
}


$correo =
    strtolower(
        trim($_POST['correo'] ?? '')
    );


$password =
    $_POST['password'] ?? '';

    // validacion 

if (
    $correo === '' ||
    $password === ''
) {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Todos los campos son obligatorios."
        )
    );

    exit;
}


$sql = "
    SELECT
        u.*,
        r.nombre AS rol_nombre
    FROM usuarios u

    INNER JOIN roles r
        ON u.rol_id = r.id

    WHERE u.correo = :correo

    LIMIT 1
";


$stmt =
    $conexion->prepare($sql);


$stmt->execute([
    ':correo' => $correo
]);


$usuario =
    $stmt->fetch();

// si el usuario no existe 
if (!$usuario) {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Correo o contraseña incorrectos."
        )
    );

    exit;
}

//contraseña 

if (
    !password_verify(
        $password,
        $usuario['password']
    )
) {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Correo o contraseña incorrectos."
        )
    );

    exit;
}

// correo no verificado 

if (
    (int)$usuario['correo_verificado'] !== 1
) {

    header(
        "Location: verificar_correo.php?correo=" .
        urlencode($correo) .
        "&error=" .
        urlencode(
            "Primero debes verificar tu correo."
        )
    );

    exit;
}

// cuanta pendiente 

if ($usuario['estado'] === 'pendiente') {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Tu correo está verificado, pero tu cuenta todavía está pendiente de aprobación por el Super Admin."
        )
    );

    exit;
}
// cuanta rechazada 

if ($usuario['estado'] === 'rechazado') {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Tu solicitud de registro fue rechazada."
        )
    );

    exit;
}
// cuenta bloqueada 

if ($usuario['estado'] === 'bloqueado') {

    header(
        "Location: login.php?error=" .
        urlencode(
            "Tu cuenta está bloqueada."
        )
    );

    exit;
}


if ($usuario['estado'] !== 'activo') {

    header(
        "Location: login.php?error=" .
        urlencode(
            "No puedes iniciar sesión con el estado actual de tu cuenta."
        )
    );

    exit;
}
// creasion de la sesion 

session_start();

session_regenerate_id(true);


$_SESSION['usuario_id'] =
    $usuario['id'];

$_SESSION['nombre'] =
    $usuario['nombre'];

$_SESSION['apellido'] =
    $usuario['apellido'];

$_SESSION['correo'] =
    $usuario['correo'];

$_SESSION['rol_id'] =
    $usuario['rol_id'];

$_SESSION['rol_nombre'] =
    $usuario['rol_nombre'];

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
        'INICIO_SESION',
        'El usuario inició sesión correctamente.',
        :realizado_por,
        :ip
    )
";


$stmt =
    $conexion->prepare($sql);


$stmt->execute([

    ':usuario_id' =>
        $usuario['id'],

    ':realizado_por' =>
        $usuario['id'],

    ':ip' =>
        $_SERVER['REMOTE_ADDR'] ?? null

]);
//redireccion 
if (
    (int)$usuario['rol_id'] === 1
) {

    header(
        "Location: ../admin/index.php"
    );

    exit;
}


header(
    "Location: ../dashboard/index.php"
);

exit;

?>