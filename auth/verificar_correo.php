<?php

require_once "../config/config.php";
require_once "../config/database.php";


/* =========================================================
   PROCESAMIENTO DEL CÓDIGO
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = strtolower(
        trim($_POST['correo'] ?? '')
    );

    $codigo = trim(
        $_POST['codigo'] ?? ''
    );


    /* VALIDAR CORREO */

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        header(
            "Location: registro.php?error=" .
            urlencode("Correo inválido.")
        );

        exit;
    }


    /* VALIDAR CÓDIGO */

    if (!preg_match('/^[0-9]{6}$/', $codigo)) {

        header(
            "Location: verificar_correo.php?correo=" .
            urlencode($correo) .
            "&error=" .
            urlencode("El código debe tener 6 números.")
        );

        exit;
    }


    /* BUSCAR USUARIO */

    $sql = "
        SELECT
            id,
            nombre,
            token_verificacion,
            token_expira,
            intentos_verificacion,
            correo_verificado,
            estado
        FROM usuarios
        WHERE correo = :correo
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':correo' => $correo
    ]);

    $usuario = $stmt->fetch();


    /* USUARIO NO EXISTE */

    if (!$usuario) {

        header(
            "Location: registro.php?error=" .
            urlencode("No existe una cuenta con ese correo.")
        );

        exit;
    }


    /* CORREO YA VERIFICADO */

    if ((int)$usuario['correo_verificado'] === 1) {

        header(
            "Location: login.php?mensaje=" .
            urlencode("Este correo ya fue verificado.")
        );

        exit;
    }


    /* LÍMITE DE INTENTOS */

    if (
        (int)$usuario['intentos_verificacion']
        >= MAX_INTENTOS_VERIFICACION
    ) {

        header(
            "Location: verificar_correo.php?correo=" .
            urlencode($correo) .
            "&error=" .
            urlencode(
                "Has superado el número máximo de intentos. Solicita un nuevo código."
            )
        );

        exit;
    }


    /* CÓDIGO EXPIRADO */

    if (
        empty($usuario['token_expira']) ||
        strtotime($usuario['token_expira']) < time()
    ) {

        header(
            "Location: verificar_correo.php?correo=" .
            urlencode($correo) .
            "&error=" .
            urlencode(
                "El código ha expirado. Solicita uno nuevo."
            )
        );

        exit;
    }


    /* COMPROBAR CÓDIGO */

    if (
        empty($usuario['token_verificacion']) ||
        !password_verify(
            $codigo,
            $usuario['token_verificacion']
        )
    ) {

        $sql = "
            UPDATE usuarios
            SET
                intentos_verificacion =
                intentos_verificacion + 1
            WHERE id = :id
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->execute([
            ':id' => $usuario['id']
        ]);


        header(
            "Location: verificar_correo.php?correo=" .
            urlencode($correo) .
            "&error=" .
            urlencode("El código es incorrecto.")
        );

        exit;
    }


    /* CORREO VERIFICADO */

    $sql = "
        UPDATE usuarios
        SET
            correo_verificado = 1,
            token_verificacion = NULL,
            token_expira = NULL,
            intentos_verificacion = 0,
            fecha_verificacion = NOW(),
            estado = 'pendiente'
        WHERE id = :id
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':id' => $usuario['id']
    ]);


    /* REGISTRAR EN AUDITORÍA */

    $sql = "
        INSERT INTO auditoria (
            usuario_id,
            accion,
            descripcion,
            ip
        )
        VALUES (
            :usuario_id,
            'VERIFICACION_CORREO',
            'El usuario verificó correctamente su correo institucional.',
            :ip
        )
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([

        ':usuario_id' =>
            $usuario['id'],

        ':ip' =>
            $_SERVER['REMOTE_ADDR'] ?? null

    ]);


    /* REDIRECCIÓN */

    header(
        "Location: login.php?mensaje=" .
        urlencode(
            "Correo verificado correctamente. Tu cuenta está pendiente de aprobación por el Super Admin."
        )
    );

    exit;
}


/* =========================================================
   DATOS DE LA PÁGINA
   ========================================================= */

$correo = strtolower(
    trim($_GET['correo'] ?? '')
);

$error = $_GET['error'] ?? '';

$mensaje = $_GET['mensaje'] ?? '';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Verificar correo | Registro Institucional</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<!-- =====================================================
     NAVBAR
     ===================================================== -->

<header class="navbar">

    <div class="navbar-container">

        <div class="logo">

            <a href="../index.php">
                Registro Institucional
            </a>

        </div>


        <nav class="nav-menu">

            <a
                href="../index.php"
                class="nav-link"
            >
                Inicio
            </a>

            <a
                href="login.php"
                class="nav-link"
            >
                Iniciar sesión
            </a>

        </nav>

    </div>

</header>



<!-- =====================================================
     CONTENIDO
     ===================================================== -->

<main class="page-container">

    <div class="content">


        <!-- =================================================
             INFORMACIÓN
             ================================================= -->

        <section class="information">

            <h1>
                Verificación de correo
            </h1>

            <p>
                Confirma tu correo institucional utilizando
                el código que recibiste.
            </p>


            <div class="information-item">

                <strong>
                    Revisa tu correo
                </strong>

                <p>
                    Busca el mensaje enviado a tu correo
                    institucional.
                </p>

            </div>


            <div class="information-item">

                <strong>
                    Código de seguridad
                </strong>

                <p>
                    Introduce el código numérico de 6
                    dígitos recibido.
                </p>

            </div>


            <div class="information-item">

                <strong>
                    Después de verificar
                </strong>

                <p>
                    Tu solicitud quedará pendiente de
                    aprobación por el Super Admin.
                </p>

            </div>

        </section>



        <!-- =================================================
             FORMULARIO
             ================================================= -->

        <section class="form-container">


            <div class="form-title">

                <h2>
                    Verifica tu correo
                </h2>

                <p>
                    Hemos enviado un código de 6 dígitos a:
                </p>

            </div>


            <!-- CORREO -->

            <div class="verification-email">

                <?= htmlspecialchars($correo) ?>

            </div>



            <!-- ERROR -->

            <?php if ($error): ?>

                <div class="error">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>



            <!-- MENSAJE -->

            <?php if ($mensaje): ?>

                <div class="success">

                    <?= htmlspecialchars($mensaje) ?>

                </div>

            <?php endif; ?>



            <!-- FORMULARIO -->

            <form
                action="verificar_correo.php"
                method="POST"
            >


                <input
                    type="hidden"
                    name="correo"
                    value="<?= htmlspecialchars($correo) ?>"
                >


                <div class="form-group">

                    <label for="codigo">
                        Código de verificación
                    </label>

                    <input
                        type="text"
                        id="codigo"
                        name="codigo"
                        required
                        minlength="6"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="000000"
                        class="verification-code"
                    >

                    <span class="help">
                        Introduce los 6 números que recibiste por correo.
                    </span>

                </div>



                <button
                    type="submit"
                    class="btn-primary"
                >
                    Verificar correo
                </button>


            </form>



            <!-- REENVIAR -->

            <form
                action="reenviar_codigo.php"
                method="POST"
                class="resend-form"
            >

                <input
                    type="hidden"
                    name="correo"
                    value="<?= htmlspecialchars($correo) ?>"
                >

                <button
                    type="submit"
                    class="resend-button"
                >
                    Reenviar código
                </button>

            </form>


        </section>

    </div>

</main>



<!-- =====================================================
     FOOTER
     ===================================================== -->

<footer class="footer"></footer>


</body>

</html>