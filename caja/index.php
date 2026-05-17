<?php
/**
 * Corte de caja diario.
 *
 * Resume ventas activas, efectivo, transferencias y anulaciones del día para
 * apoyar el cierre operativo.
 */
require '../auth/verificar.php';

require '../config/conexion.php';
include '../layouts/header.php';
include '../layouts/sidebar.php';

date_default_timezone_set('America/Tegucigalpa');

$hoy = date('Y-m-d');

// Traer ventas usando rango de fecha completo
$stmt = $pdo->prepare("
    SELECT * FROM ventas
    WHERE fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY)
");
$stmt->execute([$hoy, $hoy]);
$ventas = $stmt->fetchAll();

$totalGeneral = 0;
$totalEfectivo = 0;
$totalTransferencia = 0;
$totalAnuladas = 0;
$cantidadVentas = 0;

foreach($ventas as $v){

    $estado = $v['estado'] ?? 'ACTIVA';

    if($estado == 'ACTIVA'){

        $totalGeneral += $v['total'];
        $cantidadVentas++;

        if(strtolower($v['metodo_pago']) == 'efectivo'){
            $totalEfectivo += $v['total'];
        }

        if(strtolower($v['metodo_pago']) == 'transferencia'){
            $totalTransferencia += $v['total'];
        }

    } else {
        $totalAnuladas += $v['total'];
    }
}
?>

<div class="main-content">
<div class="card-inventario">

<div class="page-heading compact">
<div>
<span class="eyebrow">Caja diaria</span>
<h4>Corte de Caja - <?= e($hoy) ?></h4>
<p>Resumen del efectivo, transferencias y ventas anuladas del día.</p>
</div>
</div>

<?php if(count($ventas) == 0): ?>
<div class="alert alert-warning">
No hay ventas registradas hoy.
</div>
<?php endif; ?>

<div class="row">

<div class="col-md-3 mb-3">
<div class="metric-card metric-green">
<h6>Total Vendido</h6>
<h4>L <?= number_format($totalGeneral,2) ?></h4>
</div>
</div>

<div class="col-md-3 mb-3">
<div class="metric-card metric-blue">
<h6>Efectivo</h6>
<h4>L <?= number_format($totalEfectivo,2) ?></h4>
</div>
</div>

<div class="col-md-3 mb-3">
<div class="metric-card metric-amber">
<h6>Transferencia</h6>
<h4>L <?= number_format($totalTransferencia,2) ?></h4>
</div>
</div>

<div class="col-md-3 mb-3">
<div class="metric-card metric-red">
<h6>Ventas Anuladas</h6>
<h4>L <?= number_format($totalAnuladas,2) ?></h4>
</div>
</div>

</div>

<hr>

<div class="row">

<div class="col-md-6">
<h6>Cantidad de Ventas Activas</h6>
<h5><?= e($cantidadVentas) ?></h5>
</div>

<div class="col-md-6 text-end">
<button onclick="imprimirCorte()" class="btn btn-primary">
Imprimir Corte
</button>
</div>

</div>

</div>
<script>
function imprimirCorte(){

    let iframe = document.createElement("iframe");

    iframe.style.position = "fixed";
    iframe.style.right = "0";
    iframe.style.bottom = "0";
    iframe.style.width = "0";
    iframe.style.height = "0";
    iframe.style.border = "0";

    iframe.src = "ticket_corte.php";

    document.body.appendChild(iframe);

    iframe.onload = function(){

        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        setTimeout(function(){
            document.body.removeChild(iframe);
        }, 1000);
    };
}
</script>
</div>

<?php include '../layouts/footer.php'; ?>
