<?php
include_once "funciones.php";
$imagen = $_FILES["imagen"]["tmp_name"];
$marca = $_FILES["marca"]["tmp_name"];
$opacidad = floatval($_POST["opacidad"]);
$separacion = intval($_POST["separacion"]);
ponerMarcaDeAgua($imagen, $marca, $separacion, $opacidad);