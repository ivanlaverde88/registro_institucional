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
| Obtener información del usuario
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        u.id,
        u.nombre,
        u.apellido,
        u.correo,
        u.correo_verificado,
        u.estado,
        u.fecha_registro,
        u.fecha_verificacion,
        u.fecha_aprobacion,
        u.aprobado_por,
        r.nombre AS rol,
        CONCAT(a.nombre, ' ', a.apellido) AS administrador
    FROM usuarios u

    INNER JOIN roles r
        ON u.rol_id = r.id

    LEFT JOIN usuarios a
        ON u.aprobado_por = a.id

    WHERE u.id = :id

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

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ver usuario</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>

        .user-detail {
            max-width: 850px;
            margin: 0 auto;
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
        }

        .detail-header h1 {
            color: #0f172a;
            font-size: 32px;
        }

        .back-button {
            text-decoration: none;
            background: #0f172a;
            color: white;
            padding: 10px 18px;
            border-radius: 7px;
            font-size: 14px;
        }

        .user-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .user-card-header {
            padding: 30px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .user-card-header h2 {
            color: #0f172a;
            margin-bottom: 5px;
        }

        .user-card-header p {
            color: #64748b;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }

        .detail-item {
            padding: 22px 30px;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-item:nth-child(odd) {
            border-right: 1px solid #e2e8f0;
        }

        .detail-label {
            display: block;
            color: #64748b;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .detail-value {
            color: #1e293b;
            font-size: 15px;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-activo {
            background: #dcfce7;
            color: #166534;
        }

        .status-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .status-rechazado {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-bloqueado {
            background: #e2e8f0;
            color: #334155;
        }

        .verified {
            color: #16a34a;
            font-weight: bold;
        }

        .not-verified {
            color: #dc2626;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 10px;
            padding: 30px;
            flex-wrap: wrap;
        }

        .action {
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: bold;
        }

        .approve {
            background: #16a34a;
            color: white;
        }

        .reject {
            background: #dc2626;
            color: white;
        }

        .approve:hover {
            background: #15803d;
        }

        .reject:hover {
            background: #b91c1c;
        }

        @media (max-width: 700px) {

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .detail-item:nth-child(odd) {
                border-right: none;
            }

            .detail-header {
                flex-direction: column;
                align-items: flex-start;
            }

        }

    </style>

</head>

<body>

<header class="admin-header">

    <div class="admin-logo">
        Panel Super Admin
    </div>

    <div class="admin-user">

        <span>
            <?php
            echo htmlspecialchars(
                $_SESSION['nombre'] ?? 'Super Admin'
            );
            ?>
        </span>

        <a href="../logout.php">
            Cerrar sesión
        </a>

    </div>

</header>


<main class="admin-container">

    <div class="user-detail">

        <div class="detail-header">

            <div>

                <h1>
                    Información del usuario
                </h1>

                <p style="color:#64748b; margin-top:5px;">
                    Detalles de la cuenta y estado de verificación.
                </p>

            </div>

            <a href="usuarios.php" class="back-button">
                ← Volver
            </a>

        </div>


        <div class="user-card">


            <!-- CABECERA -->

            <div class="user-card-header">

                <h2>

                    <?php
                    echo htmlspecialchars(
                        $usuario['nombre'] . ' ' . $usuario['apellido']
                    );
                    ?>

                </h2>

                <p>

                    <?php
                    echo htmlspecialchars(
                        $usuario['correo']
                    );
                    ?>

                </p>

            </div>


            <!-- INFORMACIÓN -->

            <div class="detail-grid">


                <div class="detail-item">

                    <span class="detail-label">
                        ID
                    </span>

                    <span class="detail-value">
                        #<?php echo (int)$usuario['id']; ?>
                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Rol
                    </span>

                    <span class="detail-value">
                        <?php echo htmlspecialchars($usuario['rol']); ?>
                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Correo
                    </span>

                    <span class="detail-value">
                        <?php echo htmlspecialchars($usuario['correo']); ?>
                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Correo verificado
                    </span>

                    <span class="detail-value">

                        <?php if ((int)$usuario['correo_verificado'] === 1): ?>

                            <span class="verified">
                                Verificado
                            </span>

                        <?php else: ?>

                            <span class="not-verified">
                                No verificado
                            </span>

                        <?php endif; ?>

                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Estado
                    </span>

                    <span class="detail-value">

                        <span class="status status-<?php echo htmlspecialchars($usuario['estado']); ?>">

                            <?php
                            echo ucfirst(
                                htmlspecialchars($usuario['estado'])
                            );
                            ?>

                        </span>

                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Fecha de registro
                    </span>

                    <span class="detail-value">

                        <?php
                        echo date(
                            'd/m/Y H:i',
                            strtotime($usuario['fecha_registro'])
                        );
                        ?>

                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Fecha de verificación
                    </span>

                    <span class="detail-value">

                        <?php if ($usuario['fecha_verificacion']): ?>

                            <?php
                            echo date(
                                'd/m/Y H:i',
                                strtotime($usuario['fecha_verificacion'])
                            );
                            ?>

                        <?php else: ?>

                            No verificado

                        <?php endif; ?>

                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Fecha de aprobación
                    </span>

                    <span class="detail-value">

                        <?php if ($usuario['fecha_aprobacion']): ?>

                            <?php
                            echo date(
                                'd/m/Y H:i',
                                strtotime($usuario['fecha_aprobacion'])
                            );
                            ?>

                        <?php else: ?>

                            Pendiente

                        <?php endif; ?>

                    </span>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Aprobado por
                    </span>

                    <span class="detail-value">

                        <?php
                        echo $usuario['administrador']
                            ? htmlspecialchars($usuario['administrador'])
                            : 'Pendiente';
                        ?>

                    </span>

                </div>


            </div>


            <!-- ACCIONES -->

            <?php if (
                $usuario['estado'] === 'pendiente'
                && (int)$usuario['correo_verificado'] === 1
            ): ?>

                <div class="actions">

                    <a
                        href="aprobar.php?id=<?php echo (int)$usuario['id']; ?>"
                        class="action approve"
                        onclick="return confirm('¿Está seguro de aprobar este usuario?');"
                    >
                        Aprobar usuario
                    </a>

                    <a
                        href="rechazar.php?id=<?php echo (int)$usuario['id']; ?>"
                        class="action reject"
                        onclick="return confirm('¿Está seguro de rechazar este usuario?');"
                    >
                        Rechazar usuario
                    </a>

                </div>

            <?php endif; ?>


        </div>

    </div>

</main>


<footer class="admin-footer"></footer>

</body>

</html>