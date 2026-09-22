<?php

session_start();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Registro Institucional</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>


<body>


<!-- =====================================================
     BARRA DE NAVEGACIÓN
     ===================================================== -->

<header class="navbar">

    <div class="navbar-container">

        <div class="logo">

            <a href="index.php">
                Registro Institucional
            </a>

        </div>


        <nav class="nav-menu">

            <a
                href="index.php"
                class="nav-link"
            >
                Inicio
            </a>

            <a
                href="auth/login.php"
                class="nav-link"
            >
                Iniciar sesión
            </a>

            <a
                href="auth/registro.php"
                class="nav-button"
            >
                Registrarse
            </a>

        </nav>

    </div>

</header>



<!-- =====================================================
     PRESENTACIÓN PRINCIPAL
     ===================================================== -->

<main>

    <section class="institutional-home">

        <div class="institutional-container">

            <h1>
                Acceso institucional
            </h1>

            <p>
                Inicia sesión o crea tu cuenta con tu correo institucional.
            </p>

            <div class="institutional-actions">

                <a
                    href="auth/registro.php"
                    class="btn-primary"
                >
                    Crear cuenta
                </a>

                <a
                    href="auth/login.php"
                    class="btn-outline"
                >
                    Iniciar sesión
                </a>

            </div>

        </div>

    </section>

</main>


</body>

</html>