<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//autenticacion 

function usuarioAutenticado()
{
    return isset($_SESSION['usuario_id']);
}

//id del usuario
function usuarioActual()
{
    return $_SESSION['usuario_id'] ?? null;
}

// es superadmin o no 

function esSuperAdmin()
{
    return isset($_SESSION['rol_id'])
        && (int)$_SESSION['rol_id'] === 1;
}
//sesion
function requerirAutenticacion()
{
    if (!usuarioAutenticado()) {

        header(
            "Location: " .
            BASE_URL .
            "/auth/login.php"
        );

        exit;
    }
}
// superadmin
function requerirSuperAdmin()
{
    requerirAutenticacion();

    if (!esSuperAdmin()) {

        header(
            "Location: " .
            BASE_URL .
            "/index.php"
        );

        exit;
    }
}

?>