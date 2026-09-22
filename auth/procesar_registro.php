<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: registro.php');

    exit;
}


// =====================================
// RECIBIR DATOS
// =====================================

$nombre = trim(
    $_POST['nombre'] ?? ''
);

$apellido = trim(
    $_POST['apellido'] ?? ''
);

$correo = strtolower(
    trim($_POST['correo'] ?? '')
);

$password =
    $_POST['password'] ?? '';

$passwordConfirm =
    $_POST['password_confirm'] ?? '';


// =====================================
// VALIDAR CAMPOS
// =====================================

if (
    $nombre === '' ||
    $apellido === '' ||
    $correo === '' ||
    $password === '' ||
    $passwordConfirm === ''
) {

    header(
        'Location: registro.php?error=' .
        urlencode(
            'Todos los campos son obligatorios.'
        )
    );

    exit;
}


// =====================================
// VALIDAR CORREO
// =====================================

if (!filter_var(
    $correo,
    FILTER_VALIDATE_EMAIL
)) {

    header(
        'Location: registro.php?error=' .
        urlencode(
            'El correo electrónico no es válido.'
        )
    );

    exit;
}


// =====================================
// VALIDAR DOMINIO INSTITUCIONAL
// =====================================

if (!str_ends_with(
    $correo,
    strtolower(DOMINIO_INSTITUCIONAL)
)) {

    header(
        'Location: registro.php?error=' .
        urlencode(
            'Debes utilizar un correo institucional ' .
            DOMINIO_INSTITUCIONAL
        )
    );

    exit;
}


// =====================================
// VALIDAR CONTRASEÑA
// =====================================

if (strlen($password) < 8) {

    header(
        'Location: registro.php?error=' .
        urlencode(
            'La contraseña debe tener mínimo 8 caracteres.'
        )
    );

    exit;
}


if ($password !== $passwordConfirm) {

    header(
        'Location: registro.php?error=' .
        urlencode(
            'Las contraseñas no coinciden.'
        )
    );

    exit;
}


// =====================================
// GENERAR CÓDIGO
// =====================================

$codigo = (string) random_int(
    100000,
    999999
);

$tokenHash = password_hash(
    $codigo,
    PASSWORD_DEFAULT
);

$expira = date(
    'Y-m-d H:i:s',
    strtotime(
        '+' .
        TOKEN_EXPIRACION_MINUTOS .
        ' minutes'
    )
);

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// =====================================
// BUSCAR USUARIO
// =====================================

try {

    $sql = "
        SELECT *
        FROM usuarios
        WHERE correo = ?
        LIMIT 1
    ";

    $stmt =
        $conexion->prepare($sql);

    $stmt->execute([
        $correo
    ]);

    $usuarioExistente =
        $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log(
        'Error buscando usuario: ' .
        $e->getMessage()
    );

    header(
        'Location: registro.php?error=' .
        urlencode(
            'No fue posible consultar la base de datos.'
        )
    );

    exit;
}


// =====================================
// SI EL USUARIO YA EXISTE
// =====================================

if ($usuarioExistente) {

    $estado = strtolower(
        $usuarioExistente['estado'] ?? ''
    );


    // ---------------------------------
    // CUENTA ACTIVA
    // ---------------------------------

    if ($estado === 'activo') {

        header(
            'Location: registro.php?error=' .
            urlencode(
                'Este correo ya tiene una cuenta registrada.'
            )
        );

        exit;
    }


    // ---------------------------------
    // CUENTA BLOQUEADA
    // ---------------------------------

    if ($estado === 'bloqueado') {

        header(
            'Location: registro.php?error=' .
            urlencode(
                'Esta cuenta se encuentra bloqueada.'
            )
        );

        exit;
    }


    // ---------------------------------
    // ACTUALIZAR CUENTA PENDIENTE
    // ---------------------------------

    try {

        $sql = "
            UPDATE usuarios
            SET
                nombre = ?,
                apellido = ?,
                password = ?,
                token_verificacion = ?,
                token_expira = ?,
                intentos_verificacion = 0,
                correo_verificado = 0,
                estado = 'pendiente'
            WHERE correo = ?
        ";

        $stmt =
            $conexion->prepare($sql);

        $stmt->execute([

            $nombre,
            $apellido,
            $passwordHash,
            $tokenHash,
            $expira,
            $correo

        ]);

    } catch (PDOException $e) {

        error_log(
            'Error actualizando usuario: ' .
            $e->getMessage()
        );

        header(
            'Location: registro.php?error=' .
            urlencode(
                'No fue posible actualizar el registro.'
            )
        );

        exit;
    }

} else {

    // =================================
    // CREAR NUEVO USUARIO
    // =================================

    $rolId = 3;

    $correoVerificado = 0;

    $intentos = 0;

    $estado = 'pendiente';


    try {

        $sql = "
            INSERT INTO usuarios (
                nombre,
                apellido,
                correo,
                password,
                rol_id,
                correo_verificado,
                token_verificacion,
                token_expira,
                intentos_verificacion,
                estado
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt =
            $conexion->prepare($sql);

        $stmt->execute([

            $nombre,
            $apellido,
            $correo,
            $passwordHash,
            $rolId,
            $correoVerificado,
            $tokenHash,
            $expira,
            $intentos,
            $estado

        ]);

    } catch (PDOException $e) {

        error_log(
            'Error creando usuario: ' .
            $e->getMessage()
        );

        header(
            'Location: registro.php?error=' .
            urlencode(
                'No fue posible crear la cuenta.'
            )
        );

        exit;
    }
}


// =====================================
// OBTENER ID DEL USUARIO
// =====================================

try {

    if ($usuarioExistente) {

        $usuarioId =
            $usuarioExistente['id'];

    } else {

        $usuarioId =
            $conexion->lastInsertId();
    }

} catch (Throwable $e) {

    $usuarioId = null;
}


// =====================================
// AUDITORÍA
// =====================================

try {

    $accion =
        'Registro de usuario pendiente de verificación';

    $sqlAuditoria = "
        INSERT INTO auditoria (
            accion,
            usuario_id
        )
        VALUES (?, ?)
    ";

    $stmtAuditoria =
        $conexion->prepare(
            $sqlAuditoria
        );

    $stmtAuditoria->execute([

        $accion,
        $usuarioId

    ]);

} catch (Throwable $e) {

    error_log(
        'Error auditoría registro: ' .
        $e->getMessage()
    );
}


// =====================================
// ENVIAR CÓDIGO CON BREVO
// =====================================

$correoEnviado =
    enviarCodigoVerificacion(
        $correo,
        $nombre,
        $codigo
    );


// =====================================
// SI NO SE PUDO ENVIAR
// =====================================

if (!$correoEnviado) {

    header(
        'Location: registro.php?error=' .
        urlencode(
            'La cuenta fue registrada, pero no se pudo enviar el código de verificación. Revisa la configuración de Brevo.'
        )
    );

    exit;
}


// =====================================
// CORREO ENVIADO
// =====================================

header(
    'Location: verificar_correo.php?correo=' .
    urlencode($correo)
);

exit;

?>