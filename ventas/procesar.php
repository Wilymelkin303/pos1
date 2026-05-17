<?php
/**
 * Procesamiento transaccional de ventas.
 *
 * Recibe el carrito desde la interfaz, valida sesión y CSRF, recalcula totales
 * en servidor, descuenta inventario de forma atómica y registra la venta.
 */
require '../auth/verificar.php';
require '../config/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

header('Content-Type: application/json; charset=utf-8');

if(!is_array($data)){
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']);
    exit;
}

$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if(!csrf_validate($csrfHeader)){
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'La sesión expiró. Recarga la página e intenta de nuevo.']);
    exit;
}

try {

    $carrito = $data['carrito'] ?? [];
    $cliente = trim((string) ($data['cliente'] ?? ''));
    $metodoPago = (string) ($data['metodo_pago'] ?? 'Efectivo');
    $efectivo = (float) ($data['efectivo'] ?? 0);

    if(!in_array($metodoPago, ['Efectivo', 'Transferencia'], true)){
        throw new Exception("Método de pago inválido.");
    }

    if(!is_array($carrito) || count($carrito) === 0){
        throw new Exception("No hay productos en el carrito.");
    }

    $pdo->beginTransaction();

    $itemsProcesados = [];
    $total = 0;

    foreach($carrito as $item){

        $idProducto = (int) ($item['id'] ?? 0);
        $cantidad = (int) ($item['cantidad'] ?? 0);

        if($idProducto <= 0 || $cantidad <= 0){
            throw new Exception("Producto o cantidad inválida.");
        }

        $stmtProducto = $pdo->prepare("
            SELECT id, nombre, precio_unidad, stock
            FROM productos
            WHERE id = ?
            AND activo = 1
            LIMIT 1
        ");
        $stmtProducto->execute([$idProducto]);
        $producto = $stmtProducto->fetch();

        if(!$producto){
            throw new Exception("Producto no encontrado o inactivo.");
        }

        $stmtUpdate = $pdo->prepare("
            UPDATE productos
            SET stock = stock - ?
            WHERE id = ?
            AND stock >= ?
        ");
        $stmtUpdate->execute([$cantidad, $idProducto, $cantidad]);

        if($stmtUpdate->rowCount() !== 1){
            throw new Exception("Stock insuficiente para " . $producto['nombre']);
        }

        $precioBase = (float) $producto['precio_unidad'];
        $precioSolicitado = isset($item['precio']) ? (float) $item['precio'] : $precioBase;

        if($precioSolicitado <= 0){
            throw new Exception("Precio inválido para " . $producto['nombre']);
        }

        $precio = round($precioSolicitado, 2);
        $subtotal = $precio * $cantidad;
        $total += $subtotal;

        $itemsProcesados[] = [
            'id' => $idProducto,
            'nombre' => $producto['nombre'],
            'cantidad' => $cantidad,
            'precio' => $precio,
            'subtotal' => $subtotal,
        ];
    }

    if($metodoPago === 'Efectivo' && $efectivo < $total){
        throw new Exception("Efectivo insuficiente.");
    }

    if($metodoPago !== 'Efectivo'){
        $efectivo = 0;
    }

    $cambio = $metodoPago === 'Efectivo' ? max(0, $efectivo - $total) : 0;

    $stmtVenta = $pdo->prepare("
        INSERT INTO ventas 
        (fecha, total, cliente, metodo_pago, efectivo_recibido, cambio)
        VALUES (NOW(), ?, ?, ?, ?, ?)
    ");

    $stmtVenta->execute([
        $total,
        $cliente,
        $metodoPago,
        $efectivo,
        $cambio
    ]);

    $idVenta = $pdo->lastInsertId();

    foreach($itemsProcesados as $item){

        $stmtDetalle = $pdo->prepare("
            INSERT INTO detalle_venta
            (id_venta, id_producto, cantidad, precio_unitario, subtotal, nombre_cliente)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmtDetalle->execute([
            $idVenta,
            $item['id'],
            $item['cantidad'],
            $item['precio'],
            $item['subtotal'],
            $cliente
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'idVenta' => $idVenta,
        'total' => round($total, 2),
        'cambio' => round($cambio, 2),
    ]);

} catch(Exception $e){

    if($pdo->inTransaction()){
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
