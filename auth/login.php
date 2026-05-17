<?php
/**
 * Inicio de sesión del POS.
 *
 * Valida credenciales, migra contraseñas antiguas en texto plano a hashes
 * seguros y crea la sesión de usuario usada por el resto del sistema.
 */
require_once '../config/seguridad.php';

iniciar_sesion_segura();
require '../config/conexion.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    if(!csrf_validate($_POST['csrf_token'] ?? null)){
        $error = "La sesión expiró. Intenta nuevamente.";
    } else {

        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("
            SELECT *
            FROM usuarios
            WHERE usuario = ?
            AND activo = 1
            LIMIT 1
        ");

        $stmt->execute([$usuario]);
        $user = $stmt->fetch();

        $passwordValida = $user && password_verify($password, $user['password']);

        if($user && !$passwordValida && hash_equals((string) $user['password'], $password)){
            $passwordValida = true;

            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmtUpdate = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $stmtUpdate->execute([$hash, $user['id']]);
            } catch (PDOException $e) {
                error_log("No se pudo migrar contraseña a hash para usuario {$user['id']}: " . $e->getMessage());
            }
        }

        if($passwordValida){

            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'id'=>$user['id'],
                'nombre'=>$user['nombre'],
                'rol'=>$user['rol']
            ];

            csrf_token();

            header("Location: ../index.php");
            exit;

        } else {
            $error = "Credenciales incorrectas";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Punto de Venta</title>
<link rel="icon" type="image/x-icon" href="../assets/img/logo.ico">

<style>

*{
box-sizing:border-box;
font-family:'Segoe UI',system-ui,-apple-system,BlinkMacSystemFont,sans-serif;
}

body{

min-height:100vh;
margin:0;

display:flex;
align-items:center;
justify-content:center;
padding:22px;

background:
radial-gradient(circle at top left, rgba(59,130,246,0.32), transparent 28rem),
radial-gradient(circle at 88% 16%, rgba(20,184,166,0.18), transparent 28rem),
linear-gradient(135deg,#0f172a,#1e293b 62%,#111827);
color:#111827;

}

/* TARJETA */

.login-card{

width:min(100%,390px);

background:rgba(255,255,255,0.95);

padding:38px;

border-radius:8px;
border:1px solid rgba(255,255,255,0.65);

box-shadow:
0 24px 60px rgba(0,0,0,0.30);

animation:fade 0.5s ease;
backdrop-filter:blur(12px);

}

@keyframes fade{

from{
opacity:0;
transform:translateY(-20px);
}

to{
opacity:1;
transform:translateY(0);
}

}

/* LOGO */

.logo{

width:84px;
height:84px;

display:block;
margin:auto;
margin-bottom:15px;
padding:10px;
border-radius:16px;
background:#ffffff;
box-shadow:0 12px 26px rgba(15,23,42,0.14);

}

/* TITULO */

.titulo{

text-align:center;
font-size:20px;
font-weight:800;
margin-bottom:5px;

}

.subtitulo{

text-align:center;
font-size:13px;
color:#64748b;
margin-bottom:25px;

}

/* INPUT */

.input{

width:100%;
height:45px;

border-radius:8px;
border:1px solid #cbd5e1;

padding:0 40px 0 12px;
font-size:14px;

margin-bottom:15px;

transition:0.2s;

}

.input:focus{

outline:none;
border-color:#3b82f6;
box-shadow:0 0 0 4px rgba(59,130,246,0.14);

}

/* PASSWORD CON ICONO */

.password-box{

position:relative;

}

.toggle{

position:absolute;
right:10px;
top:10px;

cursor:pointer;
width:26px;
height:26px;
display:grid;
place-items:center;
border-radius:6px;
font-size:13px;
color:#475569;
background:#f1f5f9;

}

/* BOTON */

.btn{

width:100%;
height:45px;

border:none;
border-radius:8px;

background:#2563eb;
color:white;

font-size:15px;
font-weight:800;

cursor:pointer;

transition:0.2s;

}

.btn:hover{

background:#1d4ed8;
transform:translateY(-1px);

}

/* ERROR */

.error{

background:#fee2e2;
color:#991b1b;

padding:8px;
border-radius:8px;
border:1px solid #fecaca;

font-size:13px;
margin-bottom:15px;

text-align:center;

}

/* HORA */

.hora{

text-align:center;
font-size:12px;
color:#64748b;

margin-bottom:20px;

}

.footer{

text-align:center;
font-size:11px;
color:#94a3b8;

margin-top:20px;

}

@media (max-width:420px){
    .login-card{
        padding:28px 22px;
    }
}

</style>

</head>

<body>

<div class="login-card">

<img src="../assets/img/logo.ico" class="logo">

<div class="titulo">Pulpería San Ramón</div>
<div class="subtitulo">Sistema Punto de Venta</div>

<div class="hora" id="hora"></div>

<?php if($error): ?>
<div class="error"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST">

<?= csrf_field() ?>

<input 
type="text"
name="usuario"
class="input"
placeholder="Usuario"
required>

<div class="password-box">

<input
type="password"
name="password"
id="password"
class="input"
placeholder="Contraseña"
required>

<span class="toggle" onclick="togglePassword()">👁</span>

</div>

<button class="btn">
Ingresar
</button>

</form>

<div class="footer">
POS v1.0
</div>

</div>

<script>

function togglePassword(){

let input=document.getElementById("password");

if(input.type==="password"){
input.type="text";
}else{
input.type="password";
}

}

function reloj(){

let now=new Date();

let fecha=now.toLocaleDateString();
let hora=now.toLocaleTimeString();

document.getElementById("hora").innerHTML=fecha+" | "+hora;

}

setInterval(reloj,1000);
reloj();

</script>

</body>
</html>
