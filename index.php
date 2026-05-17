<?php
/**
 * Pantalla de inicio del sistema.
 *
 * Presenta accesos rápidos a los módulos principales y muestra opciones
 * adicionales según el rol del usuario autenticado.
 */
require 'auth/verificar.php';
include 'layouts/header.php';
include 'layouts/sidebar.php';
?>

<div class="main-content">

    <div class="hero-panel surface-panel">
        <div>
            <span class="eyebrow">Panel principal</span>
            <h2>Bienvenido al Sistema</h2>
            <p>Accesos rápidos para vender, revisar inventario y consultar el movimiento del negocio.</p>
        </div>
        <div class="hero-stats" aria-label="Accesos principales">
            <div class="hero-stat">
                <strong>POS</strong>
                <span>Venta rápida</span>
            </div>
            <div class="hero-stat">
                <strong>24/7</strong>
                <span>Control diario</span>
            </div>
        </div>
    </div>

    <div class="home-grid">

        <a href="venta.php" class="module-card module-sales">
            <span class="module-icon"><i class="bi bi-cart-check-fill"></i></span>
            <div>
                    <h4>Venta</h4>
                    <p>Ir al Punto de Venta</p>
            </div>
        </a>

        <a href="/pos1/dashboard/index.php" class="module-card module-stats">
            <span class="module-icon"><i class="bi bi-bar-chart-line-fill"></i></span>
            <div>
                    <h4>Estadísticas</h4>
                    <p>Ver estadísticas</p>
            </div>
        </a>

        <a href="/pos1/inventario/index.php" class="module-card module-inventory">
            <span class="module-icon"><i class="bi bi-box-seam-fill"></i></span>
            <div>
                    <h4>Inventario</h4>
                    <p>Administrar productos</p>
            </div>
        </a>

        <a href="/pos1/caja/index.php" class="module-card module-cash">
            <span class="module-icon"><i class="bi bi-wallet2"></i></span>
            <div>
                    <h4>Corte de Caja</h4>
                    <p>Ver cierre diario</p>
            </div>
        </a>

        <a href="/pos1/historial/index.php" class="module-card module-history">
            <span class="module-icon"><i class="bi bi-receipt-cutoff"></i></span>
            <div>
                    <h4>Historial de facturas</h4>
                    <p>Ver ventas anteriores</p>
            </div>
        </a>

        <a href="/pos1/auth/logout.php" class="module-card module-card-danger">
            <span class="module-icon"><i class="bi bi-box-arrow-right"></i></span>
            <div>
                    <h4>Cerrar Sesión</h4>
                    <p>Salir de la cuenta actual</p>
            </div>
        </a>

        <?php if(($_SESSION['usuario']['rol'] ?? '') === 'admin'): ?>
        <a href="/pos1/usuarios/index.php" class="module-card module-users">
            <span class="module-icon"><i class="bi bi-people-fill"></i></span>
            <div>
                    <h4>Usuarios</h4>
                    <p>Registrar y administrar accesos</p>
            </div>
        </a>
        <?php endif; ?>

    </div>

</div>

<?php include 'layouts/footer.php'; ?>
