<?php
/**
 * Dashboard operativo.
 *
 * Agrupa indicadores de ventas, promedio diario, productos críticos y ranking
 * de productos vendidos para una lectura rápida del negocio.
 */
require '../auth/verificar.php';
require '../config/conexion.php';
include '../layouts/header.php';
include '../layouts/sidebar.php';

date_default_timezone_set('America/Tegucigalpa');

$hoy = date('Y-m-d');
$mesActual = date('Y-m');

$stmt = $pdo->prepare("
    SELECT SUM(total) as total
    FROM ventas
    WHERE estado = 'ACTIVA'
    AND fecha >= ?
    AND fecha < DATE_ADD(?, INTERVAL 1 DAY)
");
$stmt->execute([$hoy, $hoy]);
$ventaHoy = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("
    SELECT SUM(total) as total
    FROM ventas
    WHERE estado = 'ACTIVA'
    AND DATE_FORMAT(fecha, '%Y-%m') = ?
");
$stmt->execute([$mesActual]);
$ventaMes = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("
    SELECT nombre, stock, stock_minimo
    FROM productos
    WHERE stock <= stock_minimo
    AND activo = 1
    ORDER BY stock ASC, nombre ASC
");
$productosCriticos = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT DATE(fecha) as dia, SUM(total) as total
    FROM ventas
    WHERE estado='ACTIVA'
    AND DATE_FORMAT(fecha,'%Y-%m') = ?
    GROUP BY DATE(fecha)
    ORDER BY DATE(fecha)
");
$stmt->execute([$mesActual]);
$ventasPorDia = $stmt->fetchAll();

$dias = [];
$totales = [];

foreach($ventasPorDia as $v){
    $dias[] = $v['dia'];
    $totales[] = $v['total'];
}

$stmt = $pdo->query("
    SELECT p.nombre, SUM(d.cantidad) as total_vendido
    FROM detalle_venta d
    JOIN productos p ON d.id_producto = p.id
    JOIN ventas v ON d.id_venta = v.id
    WHERE v.estado='ACTIVA'
    GROUP BY d.id_producto
    ORDER BY total_vendido DESC
    LIMIT 5
");
$topProductos = $stmt->fetchAll();

$diasConVentas = count($ventasPorDia);
$promedioDiario = $diasConVentas > 0 ? $ventaMes / $diasConVentas : 0;
?>

<div class="main-content dashboard-page">

<div class="dashboard-hero">
<div>
<span class="eyebrow">Resumen</span>
<h3>Dashboard</h3>
<p>Ventas, productos críticos y movimiento del negocio en una sola vista.</p>
</div>
<div class="dashboard-date">
<i class="bi bi-calendar3"></i>
<span><?= e($hoy) ?></span>
</div>
</div>

<?php if(count($productosCriticos) > 0): ?>
<div class="stock-alert-panel">
<div class="stock-alert-title">
<i class="bi bi-exclamation-triangle-fill"></i>
<strong>Productos con stock bajo</strong>
</div>
<div class="stock-alert-list">
<?php foreach($productosCriticos as $p): ?>
<span><?= e($p['nombre']) ?>: <?= e($p['stock']) ?></span>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<div class="dashboard-metrics">

<div class="metric-card metric-green">
<span class="metric-icon"><i class="bi bi-cash-stack"></i></span>
<h6>Ventas Hoy</h6>
<h4>L <?= number_format($ventaHoy,2) ?></h4>
</div>

<div class="metric-card metric-blue">
<span class="metric-icon"><i class="bi bi-calendar-month-fill"></i></span>
<h6>Ventas del Mes</h6>
<h4>L <?= number_format($ventaMes,2) ?></h4>
</div>

<div class="metric-card metric-amber">
<span class="metric-icon"><i class="bi bi-box-seam-fill"></i></span>
<h6>Productos Críticos</h6>
<h4><?= count($productosCriticos) ?></h4>
</div>

<div class="metric-card metric-slate">
<span class="metric-icon"><i class="bi bi-graph-up-arrow"></i></span>
<h6>Promedio Diario</h6>
<h4>L <?= number_format($promedioDiario,2) ?></h4>
</div>

</div>

<div class="dashboard-grid">

<div class="dashboard-panel chart-panel">
<div class="panel-heading">
<div>
<span class="eyebrow">Movimiento</span>
<h5>Ventas del Mes</h5>
</div>
<span class="panel-chip"><?= e($mesActual) ?></span>
</div>
<div class="chart-wrap">
<canvas id="graficoVentas"></canvas>
</div>
</div>

<div class="dashboard-panel">
<div class="panel-heading">
<div>
<span class="eyebrow">Ranking</span>
<h5>Top 5 Productos</h5>
</div>
</div>

<div class="table-shell">
<table class="table table-sm table-hover align-middle">
<thead class="table-dark">
<tr>
<th>Producto</th>
<th>Cantidad</th>
</tr>
</thead>
<tbody>

<?php if(count($topProductos) === 0): ?>
<tr>
<td colspan="2" class="text-muted">Aún no hay ventas activas.</td>
</tr>
<?php endif; ?>

<?php foreach($topProductos as $p): ?>
<tr>
<td><?= e($p['nombre']) ?></td>
<td><span class="rank-pill"><?= e($p['total_vendido']) ?></span></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>
</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('graficoVentas');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($dias, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        datasets: [{
            label: 'Ventas Lempiras',
            data: <?= json_encode($totales, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
            backgroundColor: 'rgba(37, 99, 235, 0.78)',
            borderColor: '#1d4ed8',
            borderWidth: 1,
            borderRadius: 7,
            maxBarThickness: 42
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#64748b' }
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(148, 163, 184, 0.22)' },
                ticks: { color: '#64748b' }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return ' L ' + Number(context.raw || 0).toFixed(2);
                    }
                }
            }
        }
    }
});
</script>

<?php include '../layouts/footer.php'; ?>
