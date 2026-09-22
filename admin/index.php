<?php

session_start();

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
| Verificar rol de Super Admin
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header("Location: ../index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel Super Admin</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

</head>

<body>

    <header class="admin-header">

        <div class="admin-logo">
            Panel Super Admin
        </div>

        <div class="admin-user">

            <span>
                <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Super Admin'); ?>
            </span>

            <a href="../logout.php">
                Cerrar sesión
            </a>

        </div>

    </header>


    <main class="admin-container">

        <section class="admin-welcome">

            <h1>
                Panel de administración
            </h1>

        </section>


        <section class="admin-cards">

            <a href="usuarios.php" class="admin-card">

                <h2>
                    Usuarios
                </h2>

                <p>
                    Consulta y gestión general.
                </p>

            </a>


            <a href="usuarios.php?estado=pendiente" class="admin-card">

                <h2>
                    Pendientes
                </h2>

                <p>
                    Solicitudes por revisar.
                </p>

            </a>


            <a href="usuarios.php?estado=activo" class="admin-card">

                <h2>
                    Activos
                </h2>

                <p>
                    Usuarios autorizados.
                </p>

            </a>


            <a href="usuarios.php?estado=rechazado" class="admin-card">

                <h2>
                    Rechazados
                </h2>

                <p>
                    Solicitudes denegadas.
                </p>

            </a>

        </section>

    </main>


</body>

</html>