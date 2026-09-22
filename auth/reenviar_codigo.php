<?php

require_once "../config/config.php";
require_once "../config/database.php";
require_once "../config/mail.php";


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: login.php");

    exit;
}


$correo =
    strtolower(
        trim($_POST['correo'] ?? '')
    );


if (
    !filter_var(
        $correo,
        FILTER_VALIDATE_EMAIL
    )
) {

    header(
        "Location: registro.php?error=" .
        urlencode(
            "Correo inválido."
        )
    );

    exit;
}


$sql = "
    SELECT
        id,
        nombre,
        correo_verificado,
        estado
    FROM usuarios
    WHERE correo = :correo
    LIMIT 1
";


$stmt =
    $conexion->prepare($sql);


$stmt->execute([
    ':correo' => $correo
]);


$usuario =
    $stmt->fetch();



if (!$usuario) {

    header(
        "Location: registro.php?error=" .
        urlencode(
            "No existe una cuenta con ese correo."
        )
    );

    exit;
}

if (
    (int)$usuario['correo_verificado'] === 1
) {

    header(
        "Location: login.php?mensaje=" .
        urlencode(
            "Este correo ya está verificado."
        )
    );

    exit;
}

// generar un nuevo codigo 
$codigo =
    (string)random_int(
        100000,
        999999
    );


$tokenHash =
    password_hash(
        $codigo,
        PASSWORD_DEFAULT
    );


$tokenExpira =
    date(
        'Y-m-d H:i:s',
        strtotime(
            '+' .
            TOKEN_EXPIRACION_MINUTOS .
            ' minutes'
        )
    );

// actualizar 
$sql = "
    UPDATE usuarios

    SET
        token_verificacion = :token,
        token_expira = :expira,
        intentos_verificacion = 0

    WHERE id = :id
";


$stmt =
    $conexion->prepare($sql);


$stmt->execute([

    ':token' => $tokenHash,

    ':expira' => $tokenExpira,

    ':id' => $usuario['id']

]);

//enviar 
$correoEnviado = enviarCodigoVerificacion(
    $correo,
    $usuario['nombre'],
    $codigo
);

if (!$correoEnviado) {
    header(
        "Location: verificar_correo.php?correo=" .
        urlencode($correo) .
        "&error=" .
        urlencode(
            "No se pudo enviar el código. Verifica la configuración del correo institucional."
        )
    );

    exit;
}
// volver 

header(
    "Location: verificar_correo.php?correo=" .
    urlencode($correo) .
    "&mensaje=" .
    urlencode(
        "Se ha enviado un nuevo código."
    )
);

exit;

?>