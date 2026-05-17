<?php
/**
 * Cambio de estado de usuarios.
 *
 * Activa o desactiva cuentas mediante POST protegido por CSRF, evitando que el
 * usuario actual desactive su propia cuenta accidentalmente.
 */
require '../auth/verificar.php';
require_role(['admin']);
require '../config/conexion.php';

require_post();

if(!csrf_validate($_POST['csrf_token'] ?? null)){
    die("La sesión expiró. Regresa e intenta nuevamente.");
}

$id = (int) ($_POST['id'] ?? 0);

if($id <= 0 || $id === (int) ($_SESSION['usuario']['id'] ?? 0)){
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT activo FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if($usuario){
    $nuevoEstado = $usuario['activo'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
    $stmt->execute([$nuevoEstado, $id]);
}

header("Location: index.php");
exit;
