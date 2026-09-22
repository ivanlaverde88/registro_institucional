<?php

// ==========================================
// CONFIGURACIÓN GENERAL
// ==========================================

define('BASE_URL', '/registro_institucional');

date_default_timezone_set('America/Bogota');


// ==========================================
// CARGAR VARIABLES DEL ARCHIVO .ENV
// ==========================================

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();


// ==========================================
// CONFIGURACIÓN BREVO
// ==========================================

define(
    'BREVO_API_KEY',
    $_ENV['BREVO_API_KEY'] ?? ''
);

define(
    'BREVO_FROM_EMAIL',
    $_ENV['BREVO_FROM_EMAIL'] ?? ''
);

define(
    'BREVO_FROM_NAME',
    $_ENV['BREVO_FROM_NAME'] ?? 'Registro Institucional'
);

define(
    'BREVO_REPLY_TO',
    $_ENV['BREVO_REPLY_TO'] ?? BREVO_FROM_EMAIL
);


// ==========================================
// DOMINIO INSTITUCIONAL
// ==========================================

define(
    'DOMINIO_INSTITUCIONAL',
    $_ENV['DOMINIO_INSTITUCIONAL'] ?? 'campusucc.edu.co'
);


// ==========================================
// CONFIGURACIÓN DEL TOKEN
// ==========================================

define('TOKEN_EXPIRACION_MINUTOS', 10);

define('MAX_INTENTOS_VERIFICACION', 5);

?>