<?php
require("funciones/basedatos.php");
require("funciones/funciones.php");
Conectar();

if(isset($_GET[factura]))
{
	$s = "SELECT * FROM facturas WHERE folio = '{$_GET[factura]}'";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
	if($r[status] == "1") //Eliminar
	{
		$s = "DELETE FROM facturas WHERE folio = '{$_GET[factura]}'";
		mysql_query($s) or die (mysql_error());

		$s = "DELETE FROM facturas_productos WHERE folio_factura = '{$_GET[factura]}'";
		mysql_query($s) or die (mysql_error());
		
		echo 0;
	}
	else //No se puede eliminar
	{
		echo 1;
	}
}

?>