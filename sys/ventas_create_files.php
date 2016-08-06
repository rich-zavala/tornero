<?php
$_GET[enviar] = $_GET[folio];
$_GET[folio] = $_GET[folio];
$_GET[serie] = $_GET[serie];

$smtp_file_xml = "XML_TEMPORAL_[{$_GET[enviar]}].xml";
include("ventas_xml.php");

$smtp_file = $_POST[ruta]."Factura_TEMPORAL_[{$_GET[enviar]}].pdf";
include("ventas_spdf.php");
?>