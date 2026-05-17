<?php
/**
 * Persistencia de usuarios.
 *
 * Valida datos administrativos, evita usuarios duplicados y guarda contraseñas
 * usando hash seguro.
 */
require '../auth/verificar.php';
require_role(['admin']);
require '../config/conexion.php';

require_post();

if(!csrf_validate($_POST['csrf_token'] ?? null)){
    header("Location: editar.php?error=" . urlencode("La sesión expiró. Intenta nuevamente."));
    exit;
}

$id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
$nombre = trim($_POST['nombre'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? 'cajero';
$activo = (int) ($_POST['activo'] ?? 1);

try {
    if($nombre === '' || $usuario === ''){
        throw new Exception("Nombre y usuario son obligatorios.");
    }

    if(!in_array($rol, ['admin', 'cajero'], true)){
        throw new Exception("Rol inválido.");
    }

    $activo = $activo === 1 ? 1 : 0;

    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE usuario = ?
        AND (? IS NULL OR id != ?)
        LIMIT 1
    ");
    $stmt->execute([$usuario, $id, $id]);

    if($stmt->fetch()){
        throw new Exception("Ese nombre de usuario ya existe.");
    }

    if($id){
        $sql = "
            UPDATE usuarios
            SET nombre = ?, usuario = ?, rol = ?, activo = ?
        ";
        $params = [$nombre, $usuario, $rol, $activo];

        if($password !== ''){
            $sql .= ", password = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if((int) ($_SESSION['usuario']['id'] ?? 0) === $id){
            $_SESSION['usuario']['nombre'] = $nombre;
            $_SESSION['usuario']['rol'] = $rol;
        }
    } else {
        if($password === ''){
            throw new Exception("La contraseña es obligatoria.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, usuario, password, rol, activo)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nombre,
            $usuario,
            password_hash($password, PASSWORD_DEFAULT),
            $rol,
            $activo,
        ]);
    }

    header("Location: index.php?success=1");
    exit;

} catch(Exception $e) {
    header("Location: editar.php?error=" . urlencode($e->getMessage()) . ($id ? "&id=" . urlencode((string) $id) : ""));
    exit;
}
