<?php
/**
 * Anulación de ventas.
 *
 * Marca una venta como anulada y reintegra al inventario las cantidades de su
 * detalle dentro de una transacción.
 */
require '../auth/verificar.php';
require '../config/conexion.php';

require_post();

if(!csrf_validate($_POST['csrf_token'] ?? null)){
    die("La sesión expiró. Regresa e intenta nuevamente.");
}

$id = $_POST['id'] ?? 0;

try {

    $pdo->beginTransaction();

    // Verificar venta
    $stmt = $pdo->prepare("SELECT estado FROM ventas WHERE id = ?");
    $stmt->execute([$id]);
    $venta = $stmt->fetch();

    if(!$venta){
        throw new Exception("Venta no encontrada.");
    }

    if($venta['estado'] == 'ANULADA'){
        throw new Exception("La venta ya fue anulada.");
    }

    // Obtener detalle
    $stmt = $pdo->prepare("
        SELECT id_producto, cantidad
        FROM detalle_venta
        WHERE id_venta = ?
    ");
    $stmt->execute([$id]);
    $detalles = $stmt->fetchAll();

    // Devolver stock
    foreach($detalles as $d){

        $stmt = $pdo->prepare("
            UPDATE productos
            SET stock = stock + ?
            WHERE id = ?
        ");
        $stmt->execute([
            $d['cantidad'],
            $d['id_producto']
        ]);
    }

    // Marcar como anulada
    $stmt = $pdo->prepare("
        UPDATE ventas
        SET estado = 'ANULADA'
        WHERE id = ?
    ");
    $stmt->execute([$id]);

    $pdo->commit();

} catch(Exception $e){

    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}

header("Location: index.php");
