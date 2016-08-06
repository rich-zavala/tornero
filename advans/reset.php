<?php
include("../aceros/funciones/basedatos.php");
include("../aceros/funciones/funciones.php");
include('../aceros/funciones/dbFacile.php');
include('helpers.php');

$folio = trim($_GET['folio'].$_POST['folio']);
$serie = trim($_GET['serie'].$_POST['serie']);
$dataBase = trim($_GET['db'].$_POST['db']);

//Conectar a la base de datos solicitada
$db = dbFacile::open('mysql', $dataBase, DB_USER, DB_PASSWORD, DB_HOST);

//Resetear registros
$db->execute("DELETE FROM cfdi WHERE folio = '{$folio}' AND serie = '{$serie}'");
$db->execute("UPDATE facturas SET status = 0 WHERE folio = '{$folio}' AND serie = '{$serie}'");

//Regenerar archivos
file_get_contents(SERVIDOR . "advans/timbrar.php?db={$dataBase}&folio={$folio}&serie={$serie}&reset", true);
sleep(4);

file_get_contents(SERVIDOR . "advans/verificar_timbre.php?db={$dataBase}&folio={$folio}&serie={$serie}", true);
sleep(4);

//Reinsertar información
$advansFile = $db->fetchCell("SELECT rfc FROM vars LIMIT 1") . '_' . $serie . '-' . $folio;
$fileInDir = isHere('descargados', $advansFile);
@unlink($fileInDir);
factura_timbrada($db, $folio, $serie, $fileInDir);

header("location: {$_SERVER['HTTP_REFERER']}");
?>