<?php
/**
 * Administración de usuarios.
 *
 * Lista cuentas del sistema, expone acciones de edición y permite activar o
 * desactivar accesos sin eliminar registros.
 */
require '../auth/verificar.php';
require_role(['admin']);
require '../config/conexion.php';
include '../layouts/header.php';
include '../layouts/sidebar.php';

$stmt = $pdo->query("
    SELECT id, nombre, usuario, rol, activo
    FROM usuarios
    ORDER BY id DESC
");
$usuarios = $stmt->fetchAll();
?>

<div class="main-content users-page">
<div class="card-inventario">

<div class="inventario-header mb-3">
    <div>
        <span class="eyebrow">Accesos</span>
        <h4 class="mb-0">Usuarios del Sistema</h4>
        <p class="mb-0 text-muted">Registra cajeros, administra permisos y controla cuentas activas.</p>
    </div>
    <a href="editar.php" class="btn btn-primary">
        <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
    </a>
</div>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">Usuario guardado correctamente.</div>
<?php endif; ?>

<div class="table-shell users-table-scroll">
<table class="table table-hover table-sm align-middle">
<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Usuario</th>
    <th>Rol</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>
</thead>
<tbody>

<?php foreach($usuarios as $u): ?>
<tr>
    <td><?= e($u['id']) ?></td>
    <td><?= e($u['nombre']) ?></td>
    <td><?= e($u['usuario']) ?></td>
    <td>
        <span class="badge <?= $u['rol'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
            <?= e($u['rol']) ?>
        </span>
    </td>
    <td>
        <?php if($u['activo']): ?>
            <span class="badge bg-success">Activo</span>
        <?php else: ?>
            <span class="badge bg-danger">Inactivo</span>
        <?php endif; ?>
    </td>
    <td>
        <a href="editar.php?id=<?= e($u['id']) ?>" class="btn btn-sm btn-warning">
            Editar
        </a>

        <?php if((int) $u['id'] !== (int) ($_SESSION['usuario']['id'] ?? 0)): ?>
        <form action="estado.php" method="POST" class="d-inline"
              onsubmit="return confirm('¿Cambiar estado de este usuario?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($u['id']) ?>">
            <button class="btn btn-sm <?= $u['activo'] ? 'btn-danger' : 'btn-success' ?>">
                <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
            </button>
        </form>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>

</div>
</div>

<?php include '../layouts/footer.php'; ?>
