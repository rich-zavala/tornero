<?php
include("../sys/funciones/basedatos.php");

$folio = trim($_GET['folio'].$_POST['folio']);
$serie = trim($_GET['serie'].$_POST['serie']);
$dataBase = trim($_GET['db'].$_POST['db']);

//Conectar a la base de datos solicitada
unset($db);
$db = dbFacile::open('mysql', $dataBase, DB_USER, DB_PASSWORD, DB_HOST);

include('helpers.php');
include("../sys/funciones/funciones.php");

$r = array(
	'error' => 0,
	'accion' => 0,
	'msg' => ''
);

$carpetas = array(
	3 => 'pendientes',
	5 => 'erroneos',
	1 => 'descargados'
);

error_reporting(E_ALL);
ini_set('display_errors', 1);

//¿Es CFDI?
if(!isCFDIfecha($db, $folio, $serie))
{
	$r['error']++;
	$r['msg'] = 'Esta factura no es un CFDI.';
}

//Nomenclatura primaria del archivo XML
$advansFile = $db->fetchCell("SELECT rfc FROM vars LIMIT 1") . '_' . $serie . '-' . $folio;

//Verificar que el folio exista
if(strlen($folio) > 0 and (int)$folio > 0 and strlen($serie) > 0 and $r['error'] == 0)
{
	//Obtener toda la información de la factura
	$info = $db->fetchRow("SELECT * FROM facturas WHERE folio = '{$folio}' AND serie = '{$serie}'");

	//Eliminar archivos anteriores en el sistema de Advans
	if(!isset($_GET['origen']))
	{
		foreach($carpetas as $c)
		{
			$archivo = isHere($c, $advansFile);
			if($archivo)
			{
				$archivoNombre = explode('.', $archivo);
				unset($archivoNombre[count($archivoNombre) - 1]);
				$archivoNombre = implode('.', $archivoNombre);
				foreach(array( 'xml', 'pdf' ) as $ext)
				{
					$ruta = CONECTOR . $c . '/' .  $archivoNombre. '.' . $ext;
					if(file_exists($ruta)) unlink($ruta);
				}
			}
		}
	}
	
	//Verificar status
	if((int)$info['status'] != 0) //Está timbrada, cancelada, pendiente o con error.
	{
		$r['error']++;
		if($info['status'] == 2)
		{
			$r['msg'] = factura_status(1);
			$fileInDir = isHere('descargados', $advansFile);
			if($fileInDir)
			{
				factura_timbrada($db, $folio, $serie, $fileInDir);
				$r['accion'] = 1;
			}
			else //No está en descargados. Solicitar a conector.
			{
				$r['error'] = 0;
			}
		}
		else if($info['status'] == 1)
		{
			$r['msg'] = factura_status(2);
		}
	}
	
	//Verificar la fecha de la factura
	if($r['error'] == 0)
	{
		$fecha_error = $db->fetchCell("SELECT IF( (SELECT fecha_factura FROM facturas WHERE folio = '{$folio}' AND serie ='{$serie}' LIMIT 1) >= (SELECT fecha_inicio_cfdi FROM vars), 1,0 ) cfdi");
		$fecha_error_cfdi = $db->fetchCell("SELECT TIME_FORMAT(TIMEDIFF(NOW(), fecha_captura), '%H') FROM facturas WHERE folio = '{$folio}' AND serie ='{$serie}' LIMIT 1");
		if($fecha_error == 0 or $fecha_error_cfdi > 71)
		{
			$r['error']++;
			$r['msg'] = 'Esta factura tiene una fecha de emisión demasiado antigua. Edítela para poder proceder.';
		}
	}
	
	//Ubicar el CFDI en la estructura de carpetas
	foreach($carpetas as $errorID => $carpeta)
	{
		$fileInDir = isHere($carpeta, $advansFile);
		if($r['error'] == 0 and $fileInDir)
		{
			$r['error']++;
			$r['msg'] = factura_status($errorID);
			
			//Actualizar status de factura ya timbrada
			if($errorID == 1 or $errorID == 3)
			{
				factura_timbrada($db, $folio, $serie, $fileInDir);
				$r['accion'] = 1;
			}
		}
	}
	
	//Generar el XML
	if($r['error'] == 0)
	{
		$ruta = SERVIDOR . "advans/ventas_xml.php?db={$dataBase}&folio={$folio}&serie={$serie}";
		// echo $ruta;
		$xml = str_replace('__DOSPUNTOS__', ':', file_get_contents($ruta, true));
		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->loadXML($xml, LIBXML_DTDLOAD | LIBXML_NOENT);
		$doc->encoding = 'UTF-8';
		$xml = $doc->saveXML();
		
		$r['xml'] = str_replace("&#10;","\n", $xml);
		$xml = CONECTOR . 'pendientes/' .  $advansFile. '.xml';
		
		//25 ago 2016 - Cliente con RFC especial
		$r['xml'] = str_replace("AP&amp;AMP;000225V19","AP&000225V19", $r['xml']);
		$r['xml'] = str_replace("ARRENDADORA P&amp;AMP;C SA DE CV","ARRENDADORA P&C SA DE CV", $r['xml']);
		
		//Escribir archivo
		file_put_contents($xml, $r['xml']);
	}
}
else if($r['error'] == 0)
{
	$r['error']++;
	$r['msg'] = 'El folio o serie de la factura son incorrectos.';
}

//Devolver información
$r['accion'] = 1;

if(isset($_GET['origen']) and $_GET['origen'] == 'ventas') //Solicitado desde listado de ventas mostrando un error
{
	echo "<h3>El documento ha sido solicitado nuevamente.<br>Refresque el listado de factura y verifique la informaci&oacute;n.</h3>";
}
else
{
	echo json_encode($r);
}
?>