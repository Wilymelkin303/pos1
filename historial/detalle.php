<?php
/**
 * Detalle de venta.
 *
 * Muestra información general de una factura y cada producto vendido para
 * revisión desde el historial.
 */
require '../auth/verificar.php';
require '../config/conexion.php';
include '../layouts/header.php';
include '../layouts/sidebar.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM ventas WHERE id = ?");
$stmt->execute([$id]);
$venta = $stmt->fetch();

if(!$venta){
    die("Venta no encontrada.");
}

$stmt = $pdo->prepare("
    SELECT d.*, p.nombre
    FROM detalle_venta d
    JOIN productos p ON d.id_producto = p.id
    WHERE d.id_venta = ?
");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll();
?>

<div class="main-content">
<div class="card-inventario">

<h4>Detalle de Venta #<?= e($venta['id']) ?></h4>

<p><strong>Fecha:</strong> <?= e($venta['fecha']) ?></p>
<p><strong>Cliente:</strong> <?= e($venta['cliente'] ?: 'Consumidor Final') ?></p>
<p><strong>Método:</strong> <?= e($venta['metodo_pago']) ?></p>

<table class="table table-bordered table-sm">
<thead class="table-dark">
<tr>
    <th>Producto</th>
    <th>Cant.</th>
    <th>Precio</th>
    <th>Subtotal</th>
</tr>
</thead>
<tbody>

<?php foreach($detalles as $d): ?>
<tr>
    <td><?= e($d['nombre']) ?></td>
    <td><?= e($d['cantidad']) ?></td>
    <td>L <?= number_format($d['precio_unitario'],2) ?></td>
    <td>L <?= number_format($d['subtotal'],2) ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<div class="text-end mt-3">
<h5>Total: <strong>L <?= number_format($venta['total'],2) ?></strong></h5>
</div>

<a href="index.php" class="btn btn-secondary mt-3">
Volver
</a>

</div>
</div>

<?php include '../layouts/footer.php'; ?>
