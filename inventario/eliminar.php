<?php
/**
 * Cambio de estado de productos.
 *
 * Alterna productos entre activo e inactivo mediante POST protegido por CSRF,
 * conservando el historial relacionado.
 */
require '../auth/verificar.php';
require '../config/conexion.php';

require_post();

if(!csrf_validate($_POST['csrf_token'] ?? null)){
    die("La sesión expiró. Regresa e intenta nuevamente.");
}

$id = $_POST['id'] ?? null;

if(!$id){
    header("Location: index.php");
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT activo
        FROM productos
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();

    if(!$producto){
        throw new Exception("Producto no encontrado.");
    }

    $nuevoEstado = $producto['activo'] ? 0 : 1;

    $stmt = $pdo->prepare("
        UPDATE productos
        SET activo = ?
        WHERE id = ?
    ");
    $stmt->execute([$nuevoEstado, $id]);

} catch(Exception $e){
    die("Error al cambiar estado");
}

header("Location: index.php");
