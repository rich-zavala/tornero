<?php
include("../tornero/funciones/basedatos.php");
include("../tornero/funciones/funciones.php");
include('helpers.php');

$folio = trim($_GET['folio'].$_POST['folio']);
$serie = trim($_GET['serie'].$_POST['serie']);
$tipo = trim($_GET['tipo'].$_POST['tipo']);
$dataBase = trim($_GET['db'].$_POST['db']);

//Conectar a la base de datos solicitada
$db = dbFacile::open('mysql', $dataBase, DB_USER, DB_PASSWORD, DB_HOST);

//Nomenclatura primaria del archivo XML
$advansFile = $db->fetchCell("SELECT rfc FROM vars LIMIT 1") . '_' . $serie . '-' . $folio;

//Variable de retorno
$data = array(
	'error' => 0,
	'resultado' => 0,
	'msg' => ''
);

//¿Es CFDI?
if(!isCFDIfecha($db, $folio, $serie))
{
	$r['error']++;
	$r['msg'] = 'Esta factura no es un CFDI.';
}

//Verificar que no haya sido regresada por error en el conector
if($r['error'] == 0)
{
	//echo (isHere('descargados', $advansFile) ? 1 : 2) . "xxx";
	if(isHere('erroneos', $advansFile))
	{
		$data['error']++;
		$data['msg'] = factura_status(5);
	}
	else
	{
		//Recuperar el ID del registro del CFDI para verificar que existan los archivos
		$s = "SELECT IFNULL((SELECT id FROM cfdi WHERE folio = '{$folio}' AND serie = '{$serie}'),0)";
		$r = (int)$db->fetchCell($s);

		//Intentar recargar si no se ha solicitado
		if($r == 0)
		{
			if(!isHere('pendientes', $advansFile)) file_get_contents(SERVIDOR . "advans/timbrar.php?db={$dataBase}&folio={$folio}&serie={$serie}&origen=verificar_timbre", true);
			sleep(1);
		}
		else
		{
			$db->execute("UPDATE facturas SET status = 2 WHERE folio = '{$folio}' AND serie = '{$serie}' AND status = 0");
			$data['resultado'] = $r;
		}
	}
}
echo json_encode($data);
?>