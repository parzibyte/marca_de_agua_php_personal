<?php
/*

  ____          _____               _ _           _       
 |  _ \        |  __ \             (_) |         | |      
 | |_) |_   _  | |__) |_ _ _ __ _____| |__  _   _| |_ ___ 
 |  _ <| | | | |  ___/ _` | '__|_  / | '_ \| | | | __/ _ \
 | |_) | |_| | | |  | (_| | |   / /| | |_) | |_| | ||  __/
 |____/ \__, | |_|   \__,_|_|  /___|_|_.__/ \__, |\__\___|
         __/ |                               __/ |        
        |___/                               |___/         
    
____________________________________
/ Si necesitas ayuda, contáctame en \
\ https://parzibyte.me               /
 ------------------------------------
        \   ^__^
         \  (oo)\_______
            (__)\       )\/\
                ||----w |
                ||     ||
Creado por Parzibyte (https://parzibyte.me).
------------------------------------------------------------------------------------------------
            | IMPORTANTE |
Si vas a borrar este encabezado, considera:
Seguirme: https://parzibyte.me/blog/sigueme/
Y compartir mi blog con tus amigos
También tengo canal de YouTube: https://www.youtube.com/channel/UCroP4BTWjfM0CkGB6AFUoBg?sub_confirmation=1
Twitter: https://twitter.com/parzibyte
Facebook: https://facebook.com/parzibyte.fanpage
Instagram: https://instagram.com/parzibyte
Hacer una donación vía PayPal: https://paypal.me/LuisCabreraBenito
------------------------------------------------------------------------------------------------
*/ ?>
<?php
/*
 * @param resource $imageSrc Image resource. Not being modified.
 * @param float $opacity Opacity to set from 0 (fully transparent) to 1 (no change)
 * @return resource Transparent image resource
 */
function imagesetopacity($imageSrc, $opacity)
{
    $width  = imagesx($imageSrc);
    $height = imagesy($imageSrc);

    // Duplicate image and convert to TrueColor
    $imageDst = imagecreatetruecolor($width, $height);
    imagealphablending($imageDst, false);
    imagefill($imageDst, 0, 0, imagecolortransparent($imageDst));
    imagecopy($imageDst, $imageSrc, 0, 0, 0, 0, $width, $height);

    // Set new opacity to each pixel
    for ($x = 0; $x < $width; ++$x)
        for ($y = 0; $y < $height; ++$y) {
            $pixelColor = imagecolorat($imageDst, $x, $y);
            $pixelOpacity = 127 - (($pixelColor >> 24) & 0xFF);
            if ($pixelOpacity > 0) {
                $pixelOpacity = $pixelOpacity * $opacity;
                $pixelColor = ($pixelColor & 0xFFFFFF) | ((int)round(127 - $pixelOpacity) << 24);
                imagesetpixel($imageDst, $x, $y, $pixelColor);
            }
        }

    return $imageDst;
}

function ponerMarcaDeAgua($rutaImagenOriginal, $rutaMarcaDeAgua, $separacionPixeles, $opacidad)
{

    $imagenEsPng = false;
    $marcaEsPng = false;
    if (mime_content_type($rutaImagenOriginal) === "image/png") {
        $imagenEsPng = true;
    }
    if (mime_content_type($rutaMarcaDeAgua) === "image/png") {
        $marcaEsPng = true;
    }
    $original = $imagenEsPng ? imagecreatefrompng($rutaImagenOriginal) : imagecreatefromjpeg($rutaImagenOriginal);
    $marcaDeAgua = $marcaEsPng ?  imagecreatefrompng($rutaMarcaDeAgua) : imagecreatefromjpeg($rutaMarcaDeAgua);
    $marcaDeAgua = imagesetopacity($marcaDeAgua, $opacidad);


    # Como vamos a centrar  necesitamos sacar antes las anchuras y alturas
    $anchuraOriginal = imagesx($original);
    $alturaOriginal = imagesy($original);
    $alturaMarcaDeAgua = imagesy($marcaDeAgua);
    $anchuraMarcaDeAgua = imagesx($marcaDeAgua);

    # Desde dónde comenzar a cortar la marca de agua (si son 0, se comienza desde el inicio)
    for ($fila = 0; $fila < $alturaOriginal; $fila += $alturaMarcaDeAgua + $separacionPixeles) {
        for ($columna = 0; $columna < $anchuraOriginal; $columna += $anchuraMarcaDeAgua + $separacionPixeles) {

            imagecopy(
                $original,
                $marcaDeAgua,

                $columna,
                $fila,
                # No modificar, creo...
                0,
                0,
                $anchuraMarcaDeAgua,
                $alturaMarcaDeAgua
            );
        }
    }

    imagealphablending($original, false);
    imagesavealpha($original, true);
    header("Content-Type: " . mime_content_type($rutaImagenOriginal));
    if ($imagenEsPng) {
        imagepng($original);
    } else {
        imagejpeg($original);
    }
    imagedestroy($original);
    imagedestroy($marcaDeAgua);
}