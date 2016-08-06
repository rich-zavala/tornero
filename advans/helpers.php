<?php
define(CONECTOR,'C:/uploader/');
define(SERVIDOR,'http://localhost/tornero/');

//Switch de diferentes tipos de  status de una factura
function factura_status($s)
{
	switch($s)
	{
		case 1: $msg = 'Esta factura está previamente timbrada.'; break;
		case 2: $msg = 'Esta factura está cancelada. No puede ser timbrada.'; break;
		case 3: $msg = 'Esta factura está en proceso de timbrado. Por favor, espere.'; break;
		case 4: $msg = 'Esta factura está en proceso de cancelación. Por favor, espere.'; break;
		case 5: $msg = 'Ha ocurrido un error al intentar timbrar esta factura. Favor de revisar el histórico de Advans.'; break;
		case 6: $msg = 'Ha ocurrido un error al intentar cancelar esta factura. Favor de revisar el histórico de Advans.'; break;
		case 7: $msg = 'Esta factura no está timbrada.'; break;
		case 8: $msg = 'Esta factura no está previamente cancelada.'; break;
	}
	return $msg;
}

//Buscar un XML dentro de la estructura de carpetas
function isHere($dir, $file)
{
	// ini_set("max_execution_time", 0);
	// ini_set("memory_limit", "120M");
	
	// $files = glob(CONECTOR . $dir . '/*.xml');
	// print_r($files);
	
	if($handle = opendir(CONECTOR . $dir))
	{
		while (false !== ($fileInDir = readdir($handle)))
		{
			// echo $file . " > " . $fileInDir . " > " . substr_count($fileInDir, $file) . "\n";
			if(substr_count($fileInDir, $file) > 0) return $fileInDir;
		}
		closedir($handle);
	}
	return false;
}

//Identificar si la factura tiene fecha suficiente para ser un cfdi
function isCFDIfecha($db, $folio, $serie)
{
	$s = "SELECT IF( (SELECT fecha_factura FROM facturas WHERE folio = '{$folio}' AND serie = '{$serie}' LIMIT 1) >= (SELECT fecha_inicio_cfdi FROM vars), 1,0 ) cfdi";
	$valor = $db->fetchCell($s);
	return ($valor == 1);
}

//Marcar una factura como timbrada
function factura_timbrada($db, $folio, $serie, $archivo)
{
	$descargados = CONECTOR . 'descargados/';
	if(substr_count('xml', $archivo) > 0) //Se encontró el XML
	{
		$archivoExplode = explode('.xml', $archivo);
	}
	else //Se encontró el PDF
	{
		$archivoExplode = explode('.pdf', $archivo);
	}
	$archivo = $archivoExplode[0];
	
	/*
	Nota: Hay veces que el archivo de Advans aún se está descargando cuando se ejecuta este script.
	Para evitar que se guarde una versión "incompleta" del archivo, se descarga dos veces y se compara.
	*/
	
	//Leer XML
	$dataXML = '';
	$xml = $descargados . $archivo . '.xml';
	$iXML = 0;
	while($iXML == 0)
	{
		$dataXML_tmp = file_get_contents($xml);
		sleep(1);
		$dataXML = file_get_contents($xml);
		if(strlen($dataXML_tmp) == strlen($dataXML)) $iXML++;
	}
	
	//Leer PDF
	$dataPDF = '';
	$pdf = $descargados . $archivo . '.pdf';
	$iPDF = 0;
	while($iPDF == 0)
	{
		$dataPDF_tmp = file_get_contents($pdf);
		sleep(1);
		$dataPDF = file_get_contents($pdf);
		if(strlen($dataPDF_tmp) == strlen($dataPDF)) $iPDF++;
	}
	
	if(strlen($dataXML) > 0 or strlen($dataPDF) > 0)
	{
		$datos = array(
			'folio' => $folio,
			'serie' => $serie
		);
		if(strlen($dataXML) > 0) $datos[xml] = $dataXML;
		if(strlen($dataPDF) > 0) $datos[pdf] = $dataPDF;
		
		//Verificar si existe
		$hecho = 0;
		$s = "SELECT IFNULL((SELECT id FROM cfdi WHERE folio = '{$folio}' AND serie = '{$serie}'), 0)";
		$id = $db->fetchCell($s);
		if($id > 0)
		{
			$db->update($datos, 'cfdi', 'id=?', array($id));
			$hecho++;
		}
		else
		{
			$db->insert($datos, 'cfdi');
			$hecho++;
		}
		
		//Actualizar status en el índice
		if($hecho > 0) $db->execute("UPDATE facturas SET status = 2 WHERE folio = '{$folio}' AND serie = '{$serie}' AND status = 0");
		return true;
	}
	else
	{
		return false;
	}
}
?>