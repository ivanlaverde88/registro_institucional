<?php

require_once "../config/config.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Crear cuenta | Registro Institucional</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<!-- =========================
     NAVBAR
     ========================= -->

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
                class="nav-button"
            >
                Iniciar sesión
            </a>

        </nav>

    </div>

</header>



<!-- =========================
     CONTENIDO
     ========================= -->

<main class="page-container">

    <div class="content">


        <!-- =========================
             FORMULARIO
             ========================= -->

        <section class="form-container">


            <div class="form-title">

                <h2>
                    Crear cuenta
                </h2>

            </div>



            <?php if (!empty($_GET['error'])): ?>

                <div class="error">

                    <?= htmlspecialchars($_GET['error']) ?>

                </div>

            <?php endif; ?>



            <form
                action="procesar_registro.php"
                method="POST"
            >


                <!-- NOMBRE / APELLIDO -->

                <div class="form-row">


                    <div class="form-group">

                        <label for="nombre">
                            Nombre
                        </label>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            placeholder="Nombre"
                            autocomplete="given-name"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="apellido">
                            Apellido
                        </label>

                        <input
                            type="text"
                            id="apellido"
                            name="apellido"
                            placeholder="Apellido"
                            autocomplete="family-name"
                            required
                        >

                    </div>


                </div>



                <!-- CORREO -->

                <div class="form-group">

                    <label for="correo">
                        Correo institucional
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="nombre@campusucc.edu.co"
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
                        placeholder="Mínimo 8 caracteres"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                </div>



                <!-- CONFIRMAR CONTRASEÑA -->

                <div class="form-group">

                    <label for="password_confirm">
                        Confirmar contraseña
                    </label>

                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        placeholder="Repite tu contraseña"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                </div>



                <!-- TÉRMINOS -->

                <label class="terms">

                    <input
                        type="checkbox"
                        name="terminos"
                        required
                    >

                    <span>
                        Acepto la verificación y aprobación de la cuenta.
                    </span>

                </label>



                <!-- BOTÓN -->

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Crear cuenta
                </button>


            </form>



            <div class="login-text">

                ¿Ya tienes una cuenta?

                <a href="login.php">
                    Iniciar sesión
                </a>

            </div>


        </section>

    </div>

</main>



<!-- =========================
     FOOTER
     ========================= -->

<footer class="footer"></footer>



<!-- =========================
     VALIDACIÓN DE CONTRASEÑA
     ========================= -->

<script>

    const password =
        document.getElementById("password");

    const passwordConfirm =
        document.getElementById("password_confirm");


    function comprobarContraseñas() {

        if (
            passwordConfirm.value !== "" &&
            password.value !== passwordConfirm.value
        ) {

            passwordConfirm.setCustomValidity(
                "Las contraseñas no coinciden"
            );

        } else {

            passwordConfirm.setCustomValidity("");

        }

    }


    password.addEventListener(
        "input",
        comprobarContraseñas
    );


    passwordConfirm.addEventListener(
        "input",
        comprobarContraseñas
    );

</script>


</body>

</html>