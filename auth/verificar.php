<?php
/**
 * Guardia de autenticación.
 *
 * Debe incluirse en toda pantalla o endpoint privado para impedir acceso sin
 * sesión activa.
 */
require_once __DIR__ . '/../config/seguridad.php';

iniciar_sesion_segura();

if(!isset($_SESSION['usuario'])){
    header("Location: /pos1/auth/login.php");
    exit;
}
