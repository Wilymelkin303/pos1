<?php
/**
 * Ticket imprimible de venta.
 *
 * Consulta la venta y su detalle para generar una vista compacta compatible
 * con impresión térmica o reimpresión desde historial.
 */
require '../auth/verificar.php';
require '../config/conexion.php';

$id = $_GET['id'] ?? 0;

$stmtVenta = $pdo->prepare("SELECT * FROM ventas WHERE id = ?");
$stmtVenta->execute([$id]);
$venta = $stmtVenta->fetch();

if(!$venta){
    die("Venta no encontrada");
}

$stmtDetalle = $pdo->prepare("
    SELECT d.*, p.nombre
    FROM detalle_venta d
    JOIN productos p ON d.id_producto = p.id
    WHERE d.id_venta = ?
");
$stmtDetalle->execute([$id]);
$detalles = $stmtDetalle->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Ticket</title>
<style>
body {
    font-family: monospace;
    width: 280px;
    margin: 0 auto;
    font-size: 12px;
}

.center {
    text-align: center;
}

hr {
    border: none;
    border-top: 1px dashed #000;
    margin: 5px 0;
}

.line {
    display: flex;
    justify-content: space-between;
}

@media print {
    button {
        display: none;
    }
}
</style>
</head>
<body>

<div class="center">
    <strong>PULPERÍA SAN RAMÓN</strong><br>
    San Ramon, Talgua<br>
    Tel: +504 9952-3951
</div>

<hr>

Fecha: <?= e($venta['fecha']) ?><br>
Cliente: <?= e($venta['cliente'] ?: 'Consumidor Final') ?>

<hr>

<?php foreach($detalles as $d): ?>
    <div><?= e($d['nombre']) ?></div>
    <div class="line">
        <span><?= e($d['cantidad']) ?> x L <?= number_format($d['precio_unitario'],2) ?></span>
        <span>L <?= number_format($d['subtotal'],2) ?></span>
    </div>
<?php endforeach; ?>

<hr>

<div class="line">
    <strong>Total:</strong>
    <strong>L <?= number_format($venta['total'],2) ?></strong>
</div>

<?php if($venta['metodo_pago'] == 'Efectivo'): ?>
<div class="line">
    <span>Efectivo:</span>
    <span>L <?= number_format($venta['efectivo_recibido'],2) ?></span>
</div>
<div class="line">
    <span>Cambio:</span>
    <span>L <?= number_format($venta['cambio'],2) ?></span>
</div>
<?php else: ?>
<div class="line">
    <span>Método:</span>
    <span><?= e($venta['metodo_pago']) ?></span>
</div>
<?php endif; ?>

<hr>

<div class="center">
    ¡Gracias por su compra!
</div>

<br>
<button onclick="window.print()">Imprimir</button>

<script>

</script>

</body>
</html>
