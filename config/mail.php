<?php

require_once __DIR__ . '/config.php';

use Brevo\Brevo;
use Brevo\Exceptions\BrevoApiException;
use Brevo\Exceptions\BrevoException;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;


/**
 * Envía el código de verificación mediante Brevo.
 */
function enviarCodigoVerificacion(
    string $correo,
    string $nombre,
    string $codigo
): bool {

    // ==========================================
    // VERIFICAR CONFIGURACIÓN
    // ==========================================

    if (
        empty(BREVO_API_KEY) ||
        empty(BREVO_FROM_EMAIL)
    ) {

        error_log(
            'Brevo no está configurado correctamente.'
        );

        return false;
    }


    // ==========================================
    // PROTEGER DATOS PARA HTML
    // ==========================================

    $nombreSeguro = htmlspecialchars(
        $nombre,
        ENT_QUOTES,
        'UTF-8'
    );

    $codigoSeguro = htmlspecialchars(
        $codigo,
        ENT_QUOTES,
        'UTF-8'
    );


    // ==========================================
    // MENSAJE HTML
    // ==========================================

    $mensaje = '
    <!DOCTYPE html>

    <html lang="es">

    <head>

        <meta charset="UTF-8">

        <title>
            Código de verificación
        </title>

    </head>


    <body style="
        margin:0;
        padding:0;
        background:#f4f4f4;
        font-family:Arial,Helvetica,sans-serif;
    ">


        <div style="
            max-width:600px;
            margin:40px auto;
            background:#ffffff;
            border:1px solid #dddddd;
            border-radius:8px;
            overflow:hidden;
        ">


            <!-- ENCABEZADO -->

            <div style="
                background:#111111;
                color:#ffffff;
                padding:25px;
                text-align:center;
            ">

                <h1 style="
                    margin:0;
                    font-size:24px;
                ">

                    Registro Institucional

                </h1>

            </div>


            <!-- CONTENIDO -->

            <div style="
                padding:30px;
                color:#333333;
            ">

                <p>

                    Hola
                    <strong>
                        ' . $nombreSeguro . '
                    </strong>,

                </p>


                <p>

                    Has solicitado crear una cuenta
                    en el Sistema de Registro Institucional.

                </p>


                <p>

                    Tu código de verificación es:

                </p>


                <!-- CÓDIGO -->

                <div style="
                    text-align:center;
                    margin:30px 0;
                ">

                    <span style="
                        display:inline-block;
                        background:#f2f2f2;
                        border:1px solid #dddddd;
                        border-radius:8px;
                        padding:15px 30px;
                        font-size:32px;
                        font-weight:bold;
                        letter-spacing:8px;
                        color:#111111;
                    ">

                        ' . $codigoSeguro . '

                    </span>

                </div>


                <p>

                    Este código tiene una vigencia de

                    <strong>
                        ' . TOKEN_EXPIRACION_MINUTOS . ' minutos
                    </strong>.

                </p>


                <p>

                    Si no realizaste esta solicitud,
                    puedes ignorar este mensaje.

                </p>

            </div>


            <!-- PIE -->

            <div style="
                background:#f7f7f7;
                padding:15px;
                text-align:center;
                color:#777777;
                font-size:12px;
            ">

                Sistema de Registro Institucional

            </div>


        </div>

    </body>

    </html>
    ';


    // ==========================================
    // MENSAJE DE TEXTO
    // ==========================================

    $mensajeTexto =
        "Hola $nombre,\n\n" .
        "Has solicitado crear una cuenta " .
        "en el Sistema de Registro Institucional.\n\n" .
        "Tu código de verificación es: $codigo\n\n" .
        "El código tiene una vigencia de " .
        TOKEN_EXPIRACION_MINUTOS .
        " minutos.\n\n" .
        "Si no realizaste esta solicitud, " .
        "puedes ignorar este mensaje.\n\n" .
        "Sistema de Registro Institucional";


    // ==========================================
    // CREAR CLIENTE BREVO
    // ==========================================

    try {

        $brevo = new Brevo(
            apiKey: BREVO_API_KEY,
            options: [
                'timeout' => 30
            ]
        );


        // ======================================
        // CONFIGURAR SOLICITUD
        // ======================================

        $request = new SendTransacEmailRequest([

            'subject' =>
                'Código de verificación - Registro Institucional',

            'htmlContent' =>
                $mensaje,

            'textContent' =>
                $mensajeTexto,

            'sender' =>
                new SendTransacEmailRequestSender([

                    'name' =>
                        BREVO_FROM_NAME,

                    'email' =>
                        BREVO_FROM_EMAIL

                ]),

            'to' => [

                new SendTransacEmailRequestToItem([

                    'email' =>
                        $correo,

                    'name' =>
                        $nombre

                ])

            ]

        ]);


        // ======================================
        // ENVIAR
        // ======================================

        $resultado =
            $brevo
                ->transactionalEmails
                ->sendTransacEmail(
                    $request
                );


        // ======================================
        // REGISTRAR MESSAGE ID
        // ======================================

        if (
            isset($resultado->messageId) &&
            !empty($resultado->messageId)
        ) {

            error_log(
                'Brevo: correo enviado correctamente. ' .
                'Message ID: ' .
                $resultado->messageId
            );

        }


        return true;


    } catch (BrevoApiException $e) {

        error_log(
            'Brevo API Error [' .
            $e->getCode() .
            ']: ' .
            $e->getMessage() .
            ' | Body: ' .
            $e->getBody()
        );

        return false;


    } catch (BrevoException $e) {

        error_log(
            'Brevo SDK Error: ' .
            $e->getMessage()
        );

        return false;


    } catch (Throwable $e) {

        error_log(
            'Error general enviando correo con Brevo: ' .
            $e->getMessage()
        );

        return false;
    }
}

?>