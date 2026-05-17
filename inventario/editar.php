<?php
/**
 * Formulario de alta y edición de productos.
 *
 * Carga datos actuales cuando se edita un producto y prepara el formulario
 * para guardar precios, stock, estado e imagen.
 */
require '../auth/verificar.php';
require '../config/conexion.php';
include '../layouts/header.php';
include '../layouts/sidebar.php';

$id = $_GET['id'] ?? null;

$producto = [
    'codigo_barras'=>'',
    'nombre'=>'',
    'precio_unidad'=>0,
    'costo_unitario'=>0,
    'stock'=>0,
    'stock_minimo'=>5,
    'activo'=>1
];

if($id){
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id=?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
}
?>

<div class="main-content">
<div class="card-inventario">

<div class="page-heading compact">
<div>
<span class="eyebrow">Inventario</span>
<h4><?= $id ? 'Editar Producto' : 'Nuevo Producto' ?></h4>
<p>Actualiza precios, costos, stock y estado del producto.</p>
</div>
</div>

<!-- MENSAJES -->
<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?= e($_GET['error']) ?>
    </div>
<?php endif; ?>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Producto guardado correctamente.
    </div>
<?php endif; ?>

<form action="guardar.php" method="POST" enctype="multipart/form-data">

<?= csrf_field() ?>

<input type="hidden" name="id" value="<?= e($id) ?>">

<div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Código de Barras</label>
<input type="text"
       name="codigo_barras"
       class="form-control"
       value="<?= e($producto['codigo_barras']) ?>"
       required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Nombre</label>
<input type="text"
       name="nombre"
       class="form-control"
       value="<?= e($producto['nombre']) ?>"
       required>
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Precio</label>
<input type="number"
       step="0.01"
       name="precio_unidad"
       value="<?= e($producto['precio_unidad']) ?>"
       class="form-control">
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Costo</label>
<input type="number"
       step="0.01"
       name="costo_unitario"
       value="<?= e($producto['costo_unitario']) ?>"
       class="form-control">
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Stock</label>
<input type="number"
       name="stock"
       value="<?= e($producto['stock']) ?>"
       class="form-control">
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Stock Mínimo</label>
<input type="number"
       name="stock_minimo"
       value="<?= e($producto['stock_minimo']) ?>"
       class="form-control">
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Imagen</label>
<input type="file"
       name="imagen"
       class="form-control">

<?php if(!empty($producto['imagen_thumb'])): ?>
    <div class="mt-2">
        <img src="data:image/jpeg;base64,<?= base64_encode($producto['imagen_thumb']) ?>"
             style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
    </div>
<?php endif; ?>
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Estado</label>
<select name="activo" class="form-control">
<option value="1" <?= $producto['activo'] ? 'selected' : '' ?>>Activo</option>
<option value="0" <?= !$producto['activo'] ? 'selected' : '' ?>>Inactivo</option>
</select>
</div>

</div>

<div class="mt-4 d-flex justify-content-end gap-2">

<a href="index.php" class="btn btn-outline-secondary">
    Cancelar
</a>

<button class="btn btn-success">
    <?= $id ? 'Actualizar Producto' : 'Guardar Producto' ?>
</button>

</div>

</form>

</div>
</div>

<?php include '../layouts/footer.php'; ?>
