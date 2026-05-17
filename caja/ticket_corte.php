<?php
/**
 * Ticket imprimible de corte de caja.
 *
 * Genera un resumen diario en formato angosto para impresión o revisión rápida
 * desde el módulo de caja.
 */
require '../auth/verificar.php';
require '../config/conexion.php';

date_default_timezone_set('America/Tegucigalpa');

$fecha = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT * FROM ventas
    WHERE fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY)
");
$stmt->execute([$fecha, $fecha]);
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

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Corte de Caja</title>
<style>
body{
    font-family: monospace;
    width: 280px;
    margin: 0 auto;
    font-size: 12px;
}

.center{text-align:center;}

hr{
    border:none;
    border-top:1px dashed #000;
    margin:5px 0;
}

.line{
    display:flex;
    justify-content:space-between;
}
</style>
</head>
<body>

<div class="center">
<strong>PULPERÍA SAN RAMÓN</strong><br>
Corte de Caja<br>
<?= e($fecha) ?>
</div>

<hr>

<div class="line">
<span>Ventas Activas:</span>
<span><?= e($cantidadVentas) ?></span>
</div>

<div class="line">
<span>Total Vendido:</span>
<span>L <?= number_format($totalGeneral,2) ?></span>
</div>

<div class="line">
<span>Efectivo:</span>
<span>L <?= number_format($totalEfectivo,2) ?></span>
</div>

<div class="line">
<span>Transferencia:</span>
<span>L <?= number_format($totalTransferencia,2) ?></span>
</div>

<div class="line">
<span>Anuladas:</span>
<span>L <?= number_format($totalAnuladas,2) ?></span>
</div>

<hr>

<div class="center">
Fin del Reporte
</div>

</body>
</html>
