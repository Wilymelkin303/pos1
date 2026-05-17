<?php
/**
 * Persistencia de productos.
 *
 * Valida datos del formulario, evita códigos de barras duplicados y guarda
 * altas o actualizaciones de inventario con imagen opcional.
 */
require '../auth/verificar.php';
require '../config/conexion.php';

require_post();

if(!csrf_validate($_POST['csrf_token'] ?? null)){
    header("Location: editar.php?error=" . urlencode("La sesión expiró. Intenta guardar nuevamente."));
    exit;
}

$id = !empty($_POST['id']) ? (int) $_POST['id'] : null;

$codigo = trim($_POST['codigo_barras'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$precio = (float) ($_POST['precio_unidad'] ?? 0);
$costo = (float) ($_POST['costo_unitario'] ?? 0);
$stock = (int) ($_POST['stock'] ?? 0);
$stock_minimo = (int) ($_POST['stock_minimo'] ?? 0);
$activo = (int) ($_POST['activo'] ?? 1);

$imagenBlob = null;
$imagenThumb = null;

if(!empty($_FILES['imagen']['tmp_name'])){

    $imagenBlob = file_get_contents($_FILES['imagen']['tmp_name']);

    $img = imagecreatefromstring($imagenBlob);
    if(!$img){
        header("Location: editar.php?error=" . urlencode("La imagen no es válida.") . ($id ? "&id=$id" : ""));
        exit;
    }
    ob_start();
    imagejpeg($img,null,50);
    $imagenThumb = ob_get_clean();
    imagedestroy($img);
}

try {

    if($codigo === '' || $nombre === ''){
        throw new Exception("Código y nombre son obligatorios.");
    }

    if($precio < 0 || $costo < 0 || $stock < 0 || $stock_minimo < 0){
        throw new Exception("Precios y stock no pueden ser negativos.");
    }

    $activo = $activo === 1 ? 1 : 0;

    // 🔎 VALIDAR SI CÓDIGO YA EXISTE (pero permitir si es el mismo producto editándose)
    if($id){
        $stmt = $pdo->prepare("
            SELECT id FROM productos 
            WHERE codigo_barras = ? AND id != ?
        ");
        $stmt->execute([$codigo,$id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT id FROM productos 
            WHERE codigo_barras = ?
        ");
        $stmt->execute([$codigo]);
    }

    if($stmt->fetch()){
        throw new Exception("El código de barras ya existe.");
    }

    if($id){

        $sql="UPDATE productos SET
            codigo_barras=?,
            nombre=?,
            precio_unidad=?,
            costo_unitario=?,
            stock=?,
            stock_minimo=?,
            activo=?";

        $params=[$codigo,$nombre,$precio,$costo,$stock,$stock_minimo,$activo];

        if($imagenBlob){
            $sql.=", imagen_blob=?, imagen_thumb=?";
            $params[]=$imagenBlob;
            $params[]=$imagenThumb;
        }

        $sql.=" WHERE id=?";
        $params[]=$id;

        $stmt=$pdo->prepare($sql);
        $stmt->execute($params);

    }else{

        $stmt=$pdo->prepare("
            INSERT INTO productos
            (codigo_barras,nombre,precio_unidad,costo_unitario,
             stock,stock_minimo,activo,imagen_blob,imagen_thumb)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");

        $stmt->execute([
            $codigo,$nombre,$precio,$costo,
            $stock,$stock_minimo,$activo,
            $imagenBlob,$imagenThumb
        ]);
    }

    header("Location: index.php?success=1");
    exit;

} catch(Exception $e){

    header("Location: editar.php?error=" . urlencode($e->getMessage()) . ($id ? "&id=" . urlencode((string) $id) : ""));
    exit;
}
