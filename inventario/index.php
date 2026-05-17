<?php
/**
 * Pantalla principal de inventario.
 *
 * Consulta productos activos e inactivos, permite filtrar por nombre o código
 * y muestra la tabla operativa con acciones de edición y cambio de estado.
 */
require '../auth/verificar.php';
require '../config/conexion.php';
include '../layouts/header.php';
include '../layouts/sidebar.php';

$busqueda = $_GET['q'] ?? '';

if($busqueda){
    $stmt = $pdo->prepare("
        SELECT * FROM productos
        WHERE nombre LIKE ?
        OR codigo_barras LIKE ?
        ORDER BY id DESC
    ");
    $like = "%$busqueda%";
    $stmt->execute([$like,$like]);
    $productos = $stmt->fetchAll();
}else{
    $productos = $pdo->query("
        SELECT * FROM productos
        ORDER BY id DESC
    ")->fetchAll();
}
?>

<div class="main-content inventory-page">

<div class="card-inventario">

<div class="inventario-header mb-3">
    <div>
        <span class="eyebrow">Productos</span>
        <h4 class="mb-0">Gestión de Inventario</h4>
    </div>
</div>

<div class="toolbar-row">

    <form method="GET" class="d-flex">
        <input type="text"
               name="q"
               class="form-control me-2"
               placeholder="Buscar por nombre o código"
               value="<?= e($busqueda) ?>">
        <button class="btn btn-secondary">Buscar</button>
    </form>

    <a href="editar.php" class="btn btn-primary">
        Nuevo Producto
    </a>

</div>

<div class="table-shell inventory-table-scroll">
<table class="table table-hover table-sm align-middle inventory-table">
<colgroup>
    <col class="col-id">
    <col class="col-image">
    <col class="col-name">
    <col class="col-code">
    <col class="col-money">
    <col class="col-money">
    <col class="col-stock">
    <col class="col-min">
    <col class="col-status">
    <col class="col-actions">
</colgroup>
<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Imagen</th>
    <th>Nombre</th>
    <th>Código</th>
    <th>Precio</th>
    <th>Costo</th>
    <th>Stock</th>
    <th>Stock Mín.</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php foreach($productos as $p): ?>

<tr <?= ($p['stock'] <= $p['stock_minimo']) ? 'class="table-warning"' : '' ?>>

    <td><?= e($p['id']) ?></td>

    <td>
        <?php if($p['imagen_thumb']): ?>
            <img src="data:image/jpeg;base64,<?= base64_encode($p['imagen_thumb']) ?>"
                 style="width:50px;height:50px;object-fit:cover;">
        <?php else: ?>
            —
        <?php endif; ?>
    </td>

    <td><?= e($p['nombre']) ?></td>
    <td><?= e($p['codigo_barras']) ?></td>

    <td>L <?= number_format($p['precio_unidad'],2) ?></td>
    <td>L <?= number_format($p['costo_unitario'],2) ?></td>

    <td>
        <strong><?= e($p['stock']) ?></strong>
    </td>

    <td><?= e($p['stock_minimo']) ?></td>

    <td>
        <?php if($p['activo']): ?>
            <span class="badge bg-success">Activo</span>
        <?php else: ?>
            <span class="badge bg-danger">Inactivo</span>
        <?php endif; ?>
    </td>

    <td class="inventory-actions">
        <a href="editar.php?id=<?= e($p['id']) ?>"
           class="btn btn-sm btn-warning">
           Editar
        </a>

        <form action="eliminar.php" method="POST" class="d-inline"
              onsubmit="return confirm('¿Cambiar estado del producto?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($p['id']) ?>">
            <button class="btn btn-sm <?= $p['activo'] ? 'btn-danger' : 'btn-success' ?>">
                <?= $p['activo'] ? 'Desactivar' : 'Activar' ?>
            </button>
        </form>

    </td>

</tr>

<?php endforeach; ?>

</tbody>
</table>
</div>

</div>
</div>

<?php include '../layouts/footer.php'; ?>
