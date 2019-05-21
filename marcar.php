<?php
/**
 * Poner marca de agua a múltiples archivos recibidos desde un formulario, 
 * ponerlos dentro de un zip
 * y enviar el zip como respuesta en el navegador
 * 
 * ======================================
 * Nota: este es un script que yo uso, no está
 * listo para producción; es para mi uso personal
 * y aunque funciona no recomiendo usarlo
 * ======================================
 *
 * @author parzibyte
 */

$rutaImagenOriginal = __DIR__ . "/original.png";
$rutaMarcaDeAgua = __DIR__ . "/marca.png";
define("DEBUG", false);
define("NOMBRE_IMAGEN_TEMPORAL", "marca.tmp.png");
define("OPACIDAD", 50);
define("PORCENTAJE_SEPARACION", 0); // Basado en la marca de agua
define("COLUMNAS", 3);
define("FILAS", 3);
define("SEPARACION_EN_PX", 20);
if (DEBUG) {
    echo "<pre>";
}
$imagenesEliminarAlTerminar = [__DIR__ . "/test.png", $rutaMarcaDeAgua];
$zip = new ZipArchive();
$archivo = "imagenes_" . uniqid() . ".zip";
if (file_exists($archivo)) {
    unlink($archivo);
}

if ($zip->open($archivo, ZipArchive::CREATE) !== true) {
    exit("No se puede crear el archivo <$archivo>\n");
}
var_dump($_FILES["original"]);
for ($c = 0; $c < count($_FILES["original"]["name"]); $c++) {
    $nombreOriginal = $_FILES["original"]["name"][$c];
    $nombreAbsoluto = __DIR__ . "/$nombreOriginal";
    array_push($imagenesEliminarAlTerminar, $nombreAbsoluto);
    move_uploaded_file($_FILES["original"]["tmp_name"][$c], $nombreOriginal);
    move_uploaded_file($_FILES["marca_de_agua"]["tmp_name"], $rutaMarcaDeAgua);
    ponerMarcaDeAgua($nombreOriginal, $rutaMarcaDeAgua);
    $zip->addFile($nombreAbsoluto, $nombreOriginal);
}
$zip->close();
foreach ($imagenesEliminarAlTerminar as $ruta) {
    unlink($ruta);
}
# Algunos encabezados que son justamente los que fuerzan la descarga
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=$archivo");
# Leer el archivo y sacarlo al navegador
readfile($archivo);
unlink($archivo);
exit;

function comenzar(){
    
}

function redimensionar($imagenOriginal, $nuevaAnchura, $nuevaAltura)
{
    $anchuraOriginal = imagesx($imagenOriginal);
    $alturaOriginal = imagesy($imagenOriginal);
    $nuevaImagen = imagecreatetruecolor($nuevaAnchura, $nuevaAltura);
    imagealphablending($nuevaImagen, false);
    imagesavealpha($nuevaImagen, true);
    $colorTransparente = imagecolorallocatealpha($nuevaImagen, 255, 255, 255, 127);
    imagefilledrectangle($nuevaImagen, 0, 0, $nuevaAnchura, $nuevaAltura, $colorTransparente);
    imagecopyresampled($nuevaImagen, $imagenOriginal, 0, 0, 0, 0, $nuevaAnchura, $nuevaAltura, $anchuraOriginal, $alturaOriginal);
    return $nuevaImagen;
}

function ponerMarcaDeAgua($rutaImagenOriginal, $rutaMarcaDeAgua)
{

    $original = imagecreatefrompng($rutaImagenOriginal);
    $marcaDeAgua = imagecreatefrompng($rutaMarcaDeAgua);

    # Como vamos a centrar  necesitamos sacar antes las anchuras y alturas
    $anchuraOriginal = imagesx($original);
    $alturaOriginal = imagesy($original);
    $alturaMarcaDeAgua = imagesy($marcaDeAgua);
    $anchuraMarcaDeAgua = imagesx($marcaDeAgua);

    # Recortar si es necesario
    $fraccionAnchuraOriginal = floor($anchuraOriginal / COLUMNAS) + SEPARACION_EN_PX;
    $fraccionAlturaOriginal = floor($alturaOriginal / FILAS) + SEPARACION_EN_PX;

    if ($fraccionAnchuraOriginal < $anchuraMarcaDeAgua || $fraccionAlturaOriginal < $alturaMarcaDeAgua) {
        # Obtener porcentaje de diferencia para redimensionar proporcionalmente
        $porcentajeAltura = (($fraccionAlturaOriginal) * 100) / $alturaMarcaDeAgua;
        $porcentajeAnchura = (($fraccionAnchuraOriginal) * 100) / $anchuraMarcaDeAgua;
        $porcentaje = $porcentajeAltura < $porcentajeAnchura ? $porcentajeAltura : $porcentajeAnchura;
        $porcentaje -= PORCENTAJE_SEPARACION;
        $nuevaAnchura = ($porcentaje * $anchuraMarcaDeAgua) / 100;
        $nuevaAltura = ($porcentaje * $alturaMarcaDeAgua) / 100;
        $nuevaAnchura = abs($nuevaAnchura);
        $nuevaAltura = abs($nuevaAltura);
        $marcaDeAgua = redimensionar($marcaDeAgua, $nuevaAnchura, $nuevaAltura);
        imagepng($marcaDeAgua, "test.png");
        $marcaDeAgua = imagecreatefrompng("test.png");
        if (DEBUG) {
            var_dump($nuevaAltura);
            var_dump($nuevaAnchura);
        }

        $alturaMarcaDeAgua = imagesy($marcaDeAgua);
        $anchuraMarcaDeAgua = imagesx($marcaDeAgua);
        if (DEBUG) {
            var_dump([
                "alturaMarcaDeAgua" => $alturaMarcaDeAgua,
                "anchuraMarcaDeAgua" => $anchuraMarcaDeAgua,
            ]);
        }
    }

    # Desde dónde comenzar a cortar la marca de agua (si son 0, se comienza desde el inicio)
    for ($fila = 0; $fila < $alturaOriginal; $fila += $alturaMarcaDeAgua + SEPARACION_EN_PX) {
        for ($columna = 0; $columna < $anchuraOriginal; $columna += $anchuraMarcaDeAgua + SEPARACION_EN_PX) {
            if (DEBUG) {
                var_dump([
                    "fila" => $fila,
                    "columna" => $columna,
                ]);
            }
            imagecopy($original, $marcaDeAgua,

                $columna, $fila,
                # No modificar, creo...
                0, 0,
                $anchuraMarcaDeAgua, $alturaMarcaDeAgua);

        }
    }

    imagealphablending($original, false);
    imagesavealpha($original, true);
    imagepng($original, $rutaImagenOriginal);
    imagedestroy($original);
    imagedestroy($marcaDeAgua);

}

if (DEBUG) {
    echo "</pre>";
}
