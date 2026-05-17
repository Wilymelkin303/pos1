<!--
    Navegación lateral principal.
    Muestra módulos disponibles según rol y mantiene visible la identidad del usuario actual.
-->
<div class="sidebar">

    <div class="sidebar-header">

        <div class="brand-lockup">
            <img src="/pos1/assets/img/logo.ico" class="logo-sidebar" alt="Logo POS">
            <div>
                <h5>Pulpería POS</h5>
                <small>Control del negocio</small>
            </div>
        </div>

    </div>

    <?php $currentPath = $_SERVER['REQUEST_URI'] ?? ''; ?>

    <div class="sidebar-menu">

        <a href="/pos1/index.php" class="<?= strpos($currentPath, '/pos1/index.php') !== false || $currentPath === '/pos1/' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-house-door-fill"></i></span>
            <span>Inicio</span>
        </a>

        <a href="/pos1/venta.php" class="<?= strpos($currentPath, '/pos1/venta.php') !== false ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-cart-check-fill"></i></span>
            <span>Ventas</span>
        </a>

        <a href="/pos1/inventario/index.php" class="<?= strpos($currentPath, '/pos1/inventario/') !== false ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-box-seam-fill"></i></span>
            <span>Inventario</span>
        </a>

        <a href="/pos1/historial/index.php" class="<?= strpos($currentPath, '/pos1/historial/') !== false ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-receipt-cutoff"></i></span>
            <span>Facturas</span>
        </a>

        <a href="/pos1/caja/index.php" class="<?= strpos($currentPath, '/pos1/caja/') !== false ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-wallet2"></i></span>
            <span>Corte de Caja</span>
        </a>

        <a href="/pos1/dashboard/index.php" class="<?= strpos($currentPath, '/pos1/dashboard/') !== false ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-bar-chart-line-fill"></i></span>
            <span>Estadísticas</span>
        </a>

        <?php if(($_SESSION['usuario']['rol'] ?? '') === 'admin'): ?>
        <a href="/pos1/usuarios/index.php" class="<?= strpos($currentPath, '/pos1/usuarios/') !== false ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-people-fill"></i></span>
            <span>Usuarios</span>
        </a>
        <?php endif; ?>

    </div>

    <div class="sidebar-footer">
        <div class="user-pill">
            <span class="user-avatar">
                <i class="bi bi-person-fill"></i>
            </span>
            <div>
                <strong><?= e($_SESSION['usuario']['nombre'] ?? 'Usuario') ?></strong>
                <small><?= e($_SESSION['usuario']['rol'] ?? 'Sistema') ?></small>
            </div>
        </div>

        <a href="/pos1/auth/logout.php" class="logout-link">
            <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span>
            <span>Cerrar sesión</span>
        </a>
    </div>
<script>

function toggleMenu(){

let sidebar = document.querySelector(".sidebar");

sidebar.classList.toggle("active");

}

</script>
</div>
