<?php
/**
 * Formulario de usuarios.
 *
 * Permite crear usuarios nuevos o modificar datos, rol, estado y contraseña de
 * cuentas existentes.
 */
require '../auth/verificar.php';
require_role(['admin']);
require '../config/conexion.php';
include '../layouts/header.php';
include '../layouts/sidebar.php';

$id = !empty($_GET['id']) ? (int) $_GET['id'] : null;

$usuario = [
    'id' => '',
    'nombre' => '',
    'usuario' => '',
    'rol' => 'cajero',
    'activo' => 1,
];

if($id){
    $stmt = $pdo->prepare("
        SELECT id, nombre, usuario, rol, activo
        FROM usuarios
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if(!$usuario){
        die("Usuario no encontrado.");
    }
}
?>

<div class="main-content">
<div class="card-inventario user-form-card">

<div class="page-heading compact">
<div>
<span class="eyebrow">Usuarios</span>
<h4><?= $id ? 'Editar Usuario' : 'Nuevo Usuario' ?></h4>
<p><?= $id ? 'Actualiza datos, rol o contraseña.' : 'Crea una cuenta para acceder al sistema.' ?></p>
</div>
</div>

<?php if(isset($_GET['error'])): ?>
<div class="alert alert-danger"><?= e($_GET['error']) ?></div>
<?php endif; ?>

<form action="guardar.php" method="POST">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= e($id) ?>">

<div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Nombre</label>
<input type="text" name="nombre" class="form-control" value="<?= e($usuario['nombre']) ?>" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Usuario</label>
<input type="text" name="usuario" class="form-control" value="<?= e($usuario['usuario']) ?>" required>
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Rol</label>
<select name="rol" class="form-control">
<option value="cajero" <?= $usuario['rol'] === 'cajero' ? 'selected' : '' ?>>Cajero</option>
<option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
</select>
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Estado</label>
<select name="activo" class="form-control">
<option value="1" <?= $usuario['activo'] ? 'selected' : '' ?>>Activo</option>
<option value="0" <?= !$usuario['activo'] ? 'selected' : '' ?>>Inactivo</option>
</select>
</div>

<div class="col-md-4 mb-3">
<label class="form-label"><?= $id ? 'Nueva contraseña' : 'Contraseña' ?></label>
<input type="password" name="password" class="form-control" <?= $id ? '' : 'required' ?>>
<?php if($id): ?>
<small class="text-muted">Déjala vacía si no quieres cambiarla.</small>
<?php endif; ?>
</div>

</div>

<div class="mt-4 d-flex justify-content-end gap-2">
<a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
<button class="btn btn-success"><?= $id ? 'Actualizar Usuario' : 'Guardar Usuario' ?></button>
</div>

</form>

</div>
</div>

<?php include '../layouts/footer.php'; ?>
