<?php
/**
 * Configuración central de conexión a base de datos.
 *
 * Lee credenciales desde variables de entorno cuando existen y conserva
 * valores locales por defecto para facilitar el trabajo en Laragon.
 */
$host = getenv('DB_HOST') ?: "localhost";
$db   = getenv('DB_NAME') ?: "pulperia";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "1234";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("Error de conexión a base de datos: " . $e->getMessage());
    die("Error de conexión a base de datos.");
}
