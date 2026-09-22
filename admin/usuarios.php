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
| Filtro por estado
|--------------------------------------------------------------------------
*/

$estado = $_GET['estado'] ?? 'todos';

$estadosPermitidos = [
    'todos',
    'pendiente',
    'activo',
    'rechazado',
    'bloqueado'
];

if (!in_array($estado, $estadosPermitidos, true)) {
    $estado = 'todos';
}

/*
|--------------------------------------------------------------------------
| Obtener usuarios
|--------------------------------------------------------------------------
*/

if ($estado === 'todos') {

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
            r.nombre AS rol
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        ORDER BY u.fecha_registro DESC
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

} else {

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
            r.nombre AS rol
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        WHERE u.estado = :estado
        ORDER BY u.fecha_registro DESC
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':estado' => $estado
    ]);
}

$usuarios = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Usuarios - Super Admin</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>

        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .users-header h1 {
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

        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .filter {
            text-decoration: none;
            padding: 9px 15px;
            border-radius: 7px;
            background: white;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 14px;
        }

        .filter:hover,
        .filter.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .table-container {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            text-align: left;
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .status-activo {
            background: #dcfce7;
            color: #166534;
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
            gap: 7px;
            flex-wrap: wrap;
        }

        .action {
            text-decoration: none;
            padding: 7px 11px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .view {
            background: #e0f2fe;
            color: #0369a1;
        }

        .approve {
            background: #dcfce7;
            color: #166534;
        }

        .reject {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty {
            text-align: center;
            padding: 50px;
            color: #64748b;
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
            <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Super Admin'); ?>
        </span>

        <a href="../logout.php">
            Cerrar sesión
        </a>

    </div>

</header>


<main class="admin-container">

    <div class="users-header">

        <div>
            <h1>Usuarios</h1>

            <p style="color:#64748b; margin-top:5px;">
                Gestión de usuarios y solicitudes de registro.
            </p>
        </div>

        <a href="index.php" class="back-button">
            ← Volver
        </a>

    </div>


    <!-- FILTROS -->

    <div class="filters">

        <a
            href="usuarios.php"
            class="filter <?php echo $estado === 'todos' ? 'active' : ''; ?>"
        >
            Todos
        </a>

        <a
            href="usuarios.php?estado=pendiente"
            class="filter <?php echo $estado === 'pendiente' ? 'active' : ''; ?>"
        >
            Pendientes
        </a>

        <a
            href="usuarios.php?estado=activo"
            class="filter <?php echo $estado === 'activo' ? 'active' : ''; ?>"
        >
            Activos
        </a>

        <a
            href="usuarios.php?estado=rechazado"
            class="filter <?php echo $estado === 'rechazado' ? 'active' : ''; ?>"
        >
            Rechazados
        </a>

        <a
            href="usuarios.php?estado=bloqueado"
            class="filter <?php echo $estado === 'bloqueado' ? 'active' : ''; ?>"
        >
            Bloqueados
        </a>

    </div>


    <!-- TABLA -->

    <div class="table-container">

        <?php if (count($usuarios) > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Usuario</th>

                        <th>Correo</th>

                        <th>Correo verificado</th>

                        <th>Estado</th>

                        <th>Registro</th>

                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach ($usuarios as $usuario): ?>

                    <tr>

                        <td>
                            #<?php echo (int)$usuario['id']; ?>
                        </td>

                        <td>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $usuario['nombre'] . ' ' . $usuario['apellido']
                                );
                                ?>
                            </strong>

                            <br>

                            <small style="color:#64748b;">
                                <?php echo htmlspecialchars($usuario['rol']); ?>
                            </small>

                        </td>

                        <td>
                            <?php echo htmlspecialchars($usuario['correo']); ?>
                        </td>

                        <td>

                            <?php if ((int)$usuario['correo_verificado'] === 1): ?>

                                <span class="verified">
                                    Verificado
                                </span>

                            <?php else: ?>

                                <span class="not-verified">
                                    No verificado
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <span class="status status-<?php echo htmlspecialchars($usuario['estado']); ?>">

                                <?php
                                echo ucfirst(
                                    htmlspecialchars($usuario['estado'])
                                );
                                ?>

                            </span>

                        </td>

                        <td>

                            <?php
                            echo date(
                                'd/m/Y H:i',
                                strtotime($usuario['fecha_registro'])
                            );
                            ?>

                        </td>

                        <td>

                            <div class="actions">

                                <a
                                    href="ver_usuario.php?id=<?php echo (int)$usuario['id']; ?>"
                                    class="action view"
                                >
                                    Ver
                                </a>

                                <?php if (
                                    $usuario['estado'] === 'pendiente'
                                    && (int)$usuario['correo_verificado'] === 1
                                ): ?>

                                    <a
                                        href="aprobar.php?id=<?php echo (int)$usuario['id']; ?>"
                                        class="action approve"
                                        onclick="return confirm('¿Está seguro de aprobar este usuario?');"
                                    >
                                        Aprobar
                                    </a>

                                    <a
                                        href="rechazar.php?id=<?php echo (int)$usuario['id']; ?>"
                                        class="action reject"
                                        onclick="return confirm('¿Está seguro de rechazar este usuario?');"
                                    >
                                        Rechazar
                                    </a>

                                <?php endif; ?>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                <h3>
                    No hay usuarios
                </h3>

                <p>
                    No existen usuarios con el filtro seleccionado.
                </p>

            </div>

        <?php endif; ?>

    </div>

</main>


</body>

</html>