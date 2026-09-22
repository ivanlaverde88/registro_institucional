<?php

require_once "../config/config.php";

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Iniciar sesión | Registro Institucional</title>

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
                href="registro.php"
                class="nav-button"
            >
                Registrarse
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
             FORMULARIO
             ================================================= -->

        <section class="form-container">


            <div class="form-title">

                <h2>
                    Iniciar sesión
                </h2>

            </div>



            <!-- MENSAJE DE ÉXITO -->

            <?php if ($mensaje): ?>

                <div class="success">

                    <?= htmlspecialchars($mensaje) ?>

                </div>

            <?php endif; ?>



            <!-- MENSAJE DE ERROR -->

            <?php if ($error): ?>

                <div class="error">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>



            <!-- FORMULARIO -->

            <form
                action="procesar_login.php"
                method="POST"
            >


                <!-- CORREO -->

                <div class="form-group">

                    <label for="correo">
                        Correo institucional
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="usuario@campusucc.edu.co"
                        autocomplete="email"
                        required
                    >

                </div>



                <!-- CONTRASEÑA -->

                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        required
                    >

                </div>



                <!-- BOTÓN -->

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Iniciar sesión
                </button>


            </form>



            <!-- REGISTRO -->

            <div class="login-text">

                No tienes cuenta

                <a href="registro.php">
                    Regístrate
                </a>

            </div>


        </section>

    </div>

</main>



<!-- =====================================================
     FOOTER
     ===================================================== -->

<footer class="footer"></footer>


</body>

</html>