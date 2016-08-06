<?php
include("../aceros/funciones/basedatos.php");
include("../aceros/funciones/funciones.php");
include('../aceros/funciones/dbFacile.php');
include('helpers.php');

$folio = trim($_GET['folio'].$_POST['folio']);
$serie = trim($_GET['serie'].$_POST['serie']);
$dataBase = trim($_GET['db'].$_POST['db']);

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

//Conectar a la base de datos solicitada
$db = dbFacile::open('mysql', $dataBase, DB_USER, DB_PASSWORD, DB_HOST);

//¿Es CFDI?
if(!isCFDIfecha($db, $folio, $serie))
{
	$r[error]++;
	$r[msg] = 'Esta factura no es un CFDI.';
}

//Verificar que el folio exista
if($r[error] == 0)
{
	if(strlen($folio) > 0 and (int)$folio > 0 and strlen($serie) > 0)
	{
		//Nomenclatura primaria del archivo XML
		$rfc = $db->fetchCell("SELECT rfc FROM vars LIMIT 1");
		$advansFile = 'cancelacion_' . $rfc . '_' . $serie . '-' . $folio;
		
		//Verificar si ya ha sido enviado
		if(isHere('enviados', $advansFile))
		{
			$data[accion] = 1;
			$db->execute("UPDATE facturas SET status = 1 WHERE folio = '{$folio}' AND serie = '{$serie}'");
		}
		else
		{
			//Obtener toda la información de la factura
			$info = $db->fetchRow("SELECT * FROM facturas WHERE folio = '{$folio}' AND serie = '{$serie}'");

			//Eliminar archivos anteriores en el sistema de Advans
			if(!isset($_GET[origen]))
			{
				foreach($carpetas as $c)
				{
					$archivo = isHere($c, $advansFile);
					if($archivo)
					{
						$archivoNombre = explode('.', $archivo);
						unset($archivoNombre[count($archivoNombre) - 1]);
						$archivoNombre = implode('.', $archivoNombre);
						$ruta = CONECTOR . $c . '/' .  $archivoNombre. '.xml';
						if(file_exists($ruta)) unlink($ruta);
					}
				}
			}
			
			//Verificar status
			if((int)$info['status'] != 2)
			{
				$r['error']++;
				if($info['status'] == 0)
				{
					$r['msg'] = factura_status(7);
				}
				else if($info['status'] == 1)
				{
					$r['msg'] = factura_status(8);
					$r['accion'] = 1;
				}
			}
			
			//Generar el XML
			if($r['error'] == 0)
			{
				$xml = new SimpleXMLElement('<comprobante/>');
				$xml->addAttribute('serie', $serie);
				$xml->addAttribute('folio', $folio);
				$xml->addAttribute('fecha', '');
				$xml->addAttribute('cancelar', 1);
				$emisor = $xml->addChild('Emisor');
				$emisor->addAttribute('rfc', $rfc);
				$xml = $xml->asXML();
				
				$doc = new DOMDocument();
				$doc->preserveWhiteSpace = false;
				$doc->formatOutput = true;
				$doc->loadXML($xml, LIBXML_DTDLOAD | LIBXML_NOENT);
				$doc->encoding = 'UTF-8';
				$xml = $doc->saveXML();
				
				$advansPendiente = CONECTOR . 'pendientes/' .  $advansFile. '.xml';
				
				//Escribir archivo
				file_put_contents($advansPendiente, $xml);
			}
		}
	}
	else
	{
		$r['error']++;
		$r['msg'] = 'El folio o serie de la factura son incorrectos.';
	}
}

//Devolver información
$r['accion'] = 1;
echo json_encode($r);
?>