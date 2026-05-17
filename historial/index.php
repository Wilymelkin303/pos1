<?php
/**
 * Historial de facturación.
 *
 * Permite consultar ventas por rango de fechas o cliente, reimprimir tickets
 * y anular ventas activas con devolución de stock.
 */
require '../auth/verificar.php';
require '../config/conexion.php';
include '../layouts/header.php';
include '../layouts/sidebar.php';

$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';
$cliente = $_GET['cliente'] ?? '';

$sql = "SELECT * FROM ventas WHERE 1=1";
$params = [];

if($desde){
    $sql .= " AND DATE(fecha) >= ?";
    $params[] = $desde;
}

if($hasta){
    $sql .= " AND DATE(fecha) <= ?";
    $params[] = $hasta;
}

if($cliente){
    $sql .= " AND cliente LIKE ?";
    $params[] = "%$cliente%";
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll();
?>

<div class="main-content invoice-page">
<div class="card-inventario">

<div class="page-heading compact">
<div>
<span class="eyebrow">Facturación</span>
<h4>Historial de Ventas</h4>
<p>Consulta ventas por fecha, cliente y estado.</p>
</div>
</div>

<form method="GET" class="row mb-3 invoice-filters">

<div class="col-md-3">
<label>Desde</label>
<input type="date" name="desde" class="form-control" value="<?= e($desde) ?>">
</div>

<div class="col-md-3">
<label>Hasta</label>
<input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>">
</div>

<div class="col-md-3">
<label>Cliente</label>
<input type="text" name="cliente" class="form-control" value="<?= e($cliente) ?>">
</div>

<div class="col-md-3 d-flex align-items-end">
<button class="btn btn-secondary w-100">Filtrar</button>
</div>

</form>

<div class="table-shell invoice-table-scroll">
<table class="table table-hover table-sm align-middle">
<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Fecha</th>
    <th>Cliente</th>
    <th>Método</th>
    <th>Total</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php 
$totalGeneral = 0;
foreach($ventas as $v): 
if($v['estado'] == 'ACTIVA'){
    $totalGeneral += $v['total'];
}
?>

<tr <?= $v['estado'] == 'ANULADA' ? 'class="table-danger"' : '' ?>>

    <td><?= e($v['id']) ?></td>
    <td><?= e($v['fecha']) ?></td>
    <td><?= e($v['cliente'] ?: 'Consumidor Final') ?></td>
    <td><?= e($v['metodo_pago']) ?></td>
    <td><strong>L <?= number_format($v['total'],2) ?></strong></td>

    <td>
        <?php if($v['estado'] == 'ACTIVA'): ?>
            <span class="badge bg-success">ACTIVA</span>
        <?php else: ?>
            <span class="badge bg-danger">ANULADA</span>
        <?php endif; ?>
    </td>

    <td>

        <?php if($v['estado'] == 'ACTIVA'): ?>

            <a href="detalle.php?id=<?= e($v['id']) ?>"
               class="btn btn-sm btn-info">
               Ver
            </a>

            <a href="../ventas/ticket.php?id=<?= e($v['id']) ?>"
               target="_blank"
               class="btn btn-sm btn-secondary">
               Reimprimir
            </a>

            <form action="anular.php" method="POST" class="d-inline"
                  onsubmit="return confirm('¿Seguro que deseas anular esta venta?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= e($v['id']) ?>">
                <button class="btn btn-sm btn-danger">Anular</button>
            </form>

        <?php else: ?>
            —
        <?php endif; ?>

    </td>
</tr>

<?php endforeach; ?>

</tbody>
</table>
</div>

<div class="invoice-total-bar">
<span>Total activo del filtro</span>
<strong>L <?= number_format($totalGeneral,2) ?></strong>
</div>

</div>
</div>

<?php include '../layouts/footer.php'; ?>
