<?php
set_time_limit(0);
include("funciones/basedatos.php");
require("funciones/funciones.php");
if(isset($_POST[reportem]))
{
	 reporte_mensual_data($_POST[repofech1], $_POST[repofech2]);
	 header("Location: comuni-k.php?section=ventas&si");
}
else
{
header("Location comuni-k.php&section=ventas&no");	
}
?>