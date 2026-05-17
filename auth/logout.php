<?php
/**
 * Cierre de sesión.
 *
 * Limpia la sesión actual y devuelve al usuario a la pantalla de login.
 */
require_once '../config/seguridad.php';

iniciar_sesion_segura();
$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
