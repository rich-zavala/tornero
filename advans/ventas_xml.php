<?php
include("../sys/funciones/basedatos.php");
include("../sys/funciones/funciones.php");
error_reporting(E_ERROR | E_PARSE);

//Conectar a la base de datos solicitada
unset($db);
$db = dbFacile::open('mysql', $_GET['db'], DB_USER, DB_PASSWORD, DB_HOST);

//Obtener información de la factura
$f = factura_data($_GET[folio],$_GET[serie]);

//Reparar codificación
foreach($f[empresa] as $k => $v) $f[empresa][$k] = strtoupper(utf8_encode($v));
foreach($f[cliente] as $k => $v) $f[cliente][$k] = strtoupper(utf8_encode($v));
foreach($f[productos] as $kp => $producto) foreach($producto as $k => $v) $f[productos][$kp][$k] = strtoupper(utf8_encode(htmlspecialchars_decode($v)));

$xml = new SimpleXMLElement('<Comprobante/>');
$xml->addAttribute('xmlns', 'http://www.sat.gob.mx/cfd/2');
$xml->addAttribute('xmlns__DOSPUNTOS__xsi', 'http://www.w3.org/2001/XMLSchema-instance');
$xml->addAttribute('xsi__DOSPUNTOS__schemaLocation', 'http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv22.xsd');
$xml->addAttribute('LugarExpedicion', 'YUCATAN, MERIDA');
$xml->addAttribute('version', '2.2');
$xml->addAttribute('metodoDePago', $f[cliente][metodoDePago]);
$xml->addAttribute('NumCtaPago', $f[cliente][NumCtaPago]);
$xml->addAttribute('folio', $_GET[folio]);
$xml->addAttribute('serie', $_GET[serie]);
$xml->addAttribute('fecha', $f[factura][fecha_sat]);
$xml->addAttribute('noAprobacion', $f[factura][noaprobacion]);
$xml->addAttribute('anoAprobacion', $f[factura][anoaprobacion]);
$xml->addAttribute('formaDePago', 'PAGO EN UNA SOLA EXHIBICION');
$xml->addAttribute('noCertificado', $f[factura][nocertificado]);
$xml->addAttribute('subTotal', nu($f[factura][subtotal]));
$xml->addAttribute('total', nu($f[factura][importe]));
$xml->addAttribute('tipoDeComprobante', 'ingreso');
$xml->addAttribute('sello', $f[sello]);
$xml->addAttribute('Moneda', $f[factura][moneda]);
$xml->addAttribute('TipoCambio', $f[empresa][dolar]);
$xml->addAttribute('certificado', '');

//¿Tiene descuento?
if(nu($f[factura][descuento]) != "0.00") $xml->addAttribute('descuento', nu($f[factura][descuento]));

//Emisor
$emisor = $xml->addChild('Emisor');
$emisor->addAttribute('rfc', $f[empresa][rfc]);
$emisor->addAttribute('nombre', $f[empresa][nombre]);
$emisorRegimen = $emisor->addChild('RegimenFiscal');
$emisorRegimen->addAttribute('Regimen', 'REGIMEN GENERAL DE LEY PERSONAS MORALES');

//Receptor
$receptor = $xml->addChild('Receptor');
$receptor->addAttribute('rfc', $f[cliente][rfc]);
$receptor->addAttribute('nombre', $f[cliente][nombre]);
$receptorDomicilio = $receptor->addChild('Domicilio');
$receptorDomicilio->addAttribute('calle', $f[cliente][calle]);
$receptorDomicilio->addAttribute('noExterior', $f[cliente][noe]);
if(strlen(trim($f[cliente][noi])) > 0) $receptorDomicilio->addAttribute('noInterior', $f[cliente][noi]);
$receptorDomicilio->addAttribute('colonia', $f[cliente][colonia]);
$receptorDomicilio->addAttribute('localidad', $f[cliente][localidad]);
$receptorDomicilio->addAttribute('municipio', $f[cliente][municipio]);
$receptorDomicilio->addAttribute('estado', $f[cliente][estado]);
$receptorDomicilio->addAttribute('pais', $f[cliente][pais]);
$receptorDomicilio->addAttribute('codigoPostal', $f[cliente][cp]);

//Conceptos
$conceptos = $xml->addChild('Conceptos');
foreach($f[productos] as $p)
{
	$conceptosItem = $conceptos->addChild('Concepto');
	$conceptosItem->addAttribute('cantidad', $p[cantidad]);
	$conceptosItem->addAttribute('unidad', $p[unidad]);
	$conceptosItem->addAttribute('descripcion', $p[descripcion]);
	$conceptosItem->addAttribute('valorUnitario', $p[precio]);
	$conceptosItem->addAttribute('importe', $p[cantidad]*$p[precio]);
}

//Impuestos
$impuestos = $xml->addChild('Impuestos');
$impuestos->addAttribute('totalImpuestosTrasladados', nu($f[factura][iva]));
$traslados = $impuestos->addChild('Traslados');
$trasladosItem = $traslados->addChild('Traslado');
$trasladosItem->addAttribute('impuesto', 'IVA');
$trasladosItem->addAttribute('tasa', '16.00');
$trasladosItem->addAttribute('importe', nu($f[factura][iva]));

echo $xml->asXML();
exit;