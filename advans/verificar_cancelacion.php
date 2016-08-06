<?php
include("../aceros/funciones/basedatos.php");
include('../aceros/funciones/dbFacile.php');
include('helpers.php');

$folio = trim($_GET['folio'].$_POST['folio']);
$serie = trim($_GET['serie'].$_POST['serie']);
$tipo = trim($_GET['tipo'].$_POST['tipo']);
$dataBase = trim($_GET['db'].$_POST['db']);

//Variable de retorno
$data = array(
	'error' => 0,
	'resultado' => 0,
	'msg' => ''
);

//Conectar a la base de datos solicitada
$db = dbFacile::open('mysql', $dataBase, DB_USER, DB_PASSWORD, DB_HOST);

//¿Es CFDI?
if(!isCFDIfecha($db, $folio, $serie))
{
	$data[error]++;
	$data[msg] = 'Esta factura no es un CFDI.';
}

if($data[error] == 0)
{
	//Nomenclatura primaria del archivo XML
	$rfc = $db->fetchCell("SELECT rfc FROM vars LIMIT 1");
	$advansFile = 'cancelacion_' . $rfc . '_' . $serie . '-' . $folio;

	//Verificar si se envió
	if(isHere('enviados', $advansFile))
	{
		$data[resultado] = 1;
		$db->execute("UPDATE facturas SET status = 1 WHERE folio = '{$folio}' AND serie = '{$serie}'");
	}
	else
	{
		//Verificar si hubo error
		if(isHere('erroneos', $advansFile))
		{
			$data[error]++;
			$data[msg] = factura_status(6);
		}

		//Intentar recargar si no se ha solicitado
		if($data[error] == 0)
		{
			if(!isHere('pendientes', $advansFile)) file_get_contents(SERVIDOR . "advans/cancelar.php?db={$dataBase}&folio={$folio}&serie={$serie}&origen=verificar_cancelacion", true);
			sleep(1);
		}
	}
}
echo json_encode($data);
?>