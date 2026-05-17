<?php
/**
 * Recuperación privada de contraseña.
 *
 * Ruta oculta que permite cambiar la contraseña de un usuario mediante una
 * llave de recuperación configurada fuera del menú principal.
 */
require_once '../config/seguridad.php';

iniciar_sesion_segura();
require '../config/conexion.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!csrf_validate($_POST['csrf_token'] ?? null)){
        $error = "La sesión expiró. Intenta nuevamente.";
    } else {
        $clave = $_POST['clave'] ?? '';
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmar = $_POST['confirmar'] ?? '';

        if(!hash_equals(recovery_key(), $clave)){
            $error = "Llave de recuperación incorrecta.";
        } elseif($usuario === '' || $password === ''){
            $error = "Usuario y nueva contraseña son obligatorios.";
        } elseif($password !== $confirmar){
            $error = "Las contraseñas no coinciden.";
        } else {
            $stmt = $pdo->prepare("
                UPDATE usuarios
                SET password = ?
                WHERE usuario = ?
            ");
            $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $usuario]);

            if($stmt->rowCount() === 1){
                $success = "Contraseña actualizada. Ya puedes iniciar sesión.";
            } else {
                $error = "No se encontró ese usuario.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Recuperar acceso</title>
<link rel="icon" type="image/x-icon" href="../assets/img/logo.ico">
<style>
*{box-sizing:border-box;font-family:'Segoe UI',system-ui,-apple-system,BlinkMacSystemFont,sans-serif}
body{
min-height:100vh;margin:0;display:flex;align-items:center;justify-content:center;padding:22px;
background:radial-gradient(circle at top left,rgba(59,130,246,.32),transparent 28rem),
linear-gradient(135deg,#0f172a,#1e293b 62%,#111827);color:#111827}
.card{width:min(100%,420px);background:rgba(255,255,255,.96);padding:34px;border-radius:8px;
box-shadow:0 24px 60px rgba(0,0,0,.30);border:1px solid rgba(255,255,255,.65)}
.logo{width:72px;height:72px;display:block;margin:0 auto 14px;padding:10px;border-radius:16px;background:#fff;
box-shadow:0 12px 26px rgba(15,23,42,.14)}
h1{text-align:center;font-size:21px;margin:0 0 6px;font-weight:900}
p{text-align:center;margin:0 0 22px;color:#64748b;font-size:13px}
label{display:block;margin:0 0 6px;color:#334155;font-size:12px;font-weight:900;text-transform:uppercase}
.input{width:100%;height:43px;border-radius:8px;border:1px solid #cbd5e1;padding:0 12px;font-size:14px;margin-bottom:14px}
.input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 4px rgba(59,130,246,.14)}
.btn{width:100%;height:45px;border:none;border-radius:8px;background:#2563eb;color:white;font-size:15px;font-weight:900;cursor:pointer}
.btn:hover{background:#1d4ed8}
.message{padding:9px;border-radius:8px;font-size:13px;margin-bottom:14px;text-align:center}
.error{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}
.success{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
.back{display:block;margin-top:16px;text-align:center;color:#64748b;font-size:13px;text-decoration:none;font-weight:800}
</style>
</head>
<body>
<div class="card">
<img src="../assets/img/logo.ico" class="logo" alt="Logo POS">
<h1>Recuperar acceso</h1>
<p>Ruta privada para cambiar contraseña con llave de recuperación.</p>

<?php if($error): ?>
<div class="message error"><?= e($error) ?></div>
<?php endif; ?>

<?php if($success): ?>
<div class="message success"><?= e($success) ?></div>
<?php endif; ?>

<form method="POST">
<?= csrf_field() ?>

<label>Llave de recuperación</label>
<input type="password" name="clave" class="input" required>

<label>Usuario</label>
<input type="text" name="usuario" class="input" required>

<label>Nueva contraseña</label>
<input type="password" name="password" class="input" required>

<label>Confirmar contraseña</label>
<input type="password" name="confirmar" class="input" required>

<button class="btn">Actualizar contraseña</button>
</form>

<a class="back" href="login.php">Volver al login</a>
</div>
</body>
</html>
