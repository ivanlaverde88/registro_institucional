<?php

require_once "../config/config.php";
require_once "../config/auth.php";

requerirAutenticacion();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Panel de usuario</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<header class="navbar">

    <div class="logo">

        <a href="../index.php">
            Registro Institucional
        </a>

    </div>


    <nav>

        <span>
            <?= htmlspecialchars($_SESSION['nombre']) ?>
        </span>

        <a href="../logout.php">
            Cerrar sesión
        </a>

    </nav>

</header>



<main>

<section class="information">

<div class="information-content">


    <h1>
        Bienvenido,
        <?= htmlspecialchars($_SESSION['nombre']) ?>
    </h1>


    <p>
        Has iniciado sesión correctamente.
    </p>


    <p>
        Correo:
        <strong>
            <?= htmlspecialchars($_SESSION['correo']) ?>
        </strong>
    </p>


    <p>
        Rol:
        <strong>
            <?= htmlspecialchars($_SESSION['rol_nombre']) ?>
        </strong>
    </p>


</div>

</section>

</main>


<footer class="footer"></footer>


</body>

</html>