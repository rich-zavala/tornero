<?php
include("../aceros/funciones/basedatos.php");
include('helpers.php');

$folio = trim($_GET['folio'].$_POST['folio']);
$serie = trim($_GET['serie'].$_POST['serie']);
$tipo = trim($_GET['tipo'].$_POST['tipo']);
$dataBase = trim($_GET['db'].$_POST['db']);

//Conectar a la base de datos solicitada
$db = dbFacile::open('mysql', $dataBase, DB_USER, DB_PASSWORD, DB_HOST);

//Verificar si existen los archivos
$id = (int)$db->fetchCell("SELECT IFNULL((SELECT id FROM cfdi WHERE folio = '{$folio}' AND serie = '{$serie}'), 0)");
if($id > 0)
{
	$contenido = $db->fetchCell("SELECT {$tipo} FROM cfdi WHERE folio = '{$folio}' AND serie = '{$serie}'");
	header('Content-type: application/{$tipo}');
	header("Content-Disposition: inline; filename={$serie}_{$folio}.{$tipo}");
	echo $contenido;
}
else //Error. Volver a cargar.
{
	$dataBase = (substr_count($_SERVER['PHP_SELF'], 'aceros') > 0) ? 'aceros' : 'tornero';
	file_get_contents(SERVIDOR . "advans/timbrar.php?db={$dataBase}&folio={$folio}&serie={$serie}&origen=verificar_timbre", true);
	echo "<h3>Ha ocurrido un error. Intente nuevamente.</h3>";
}
?>