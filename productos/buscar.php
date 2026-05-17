<?php
/**
 * Búsqueda AJAX de productos para el punto de venta.
 *
 * Devuelve productos activos por código o nombre, incluyendo miniatura en
 * base64 cuando existe imagen asociada.
 */
require '../auth/verificar.php';
require '../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$busqueda = $_GET['q'] ?? '';

if(strlen($busqueda) < 1){
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, nombre, precio_unidad,
           stock, imagen_thumb
    FROM productos 
    WHERE activo = 1
    AND (
        codigo_barras LIKE ? 
        OR nombre LIKE ?
    )
    LIMIT 10
");

$like = "%$busqueda%";
$stmt->execute([$like, $like]);

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔥 CONVERTIR IMAGEN A BASE64
foreach($productos as &$p){
    if(!empty($p['imagen_thumb'])){
        $p['imagen_thumb'] = base64_encode($p['imagen_thumb']);
    } else {
        $p['imagen_thumb'] = null;
    }
}

echo json_encode($productos);
