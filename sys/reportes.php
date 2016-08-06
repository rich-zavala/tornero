<?php
$reps = array(
							array("Reporte de Utilidades","reporte_utilidades"),
							array("Reporte de Costos","reporte_costos"),
							array("Reporte de Movimientos","reporte_movimientos"),
							array("Facturas pendientes por Vendedor","reporte_vendedor"),
							array("Facturas pendientes por Grupo","reporte_grupo"),
							array("Estado de cuenta de Cliente","reporte_cliente"),
							array("Reporte de Cobros","reporte_cobros"),
							array("Ventas Totales","reporte_ventas_totales")
							);
if(Contador())
{
$reps = array(
							array("Reporte de Movimientos","reporte_movimientos"),
							array("Facturas pendientes por Vendedor","reporte_vendedor"),
							array("Facturas pendientes por Grupo","reporte_grupo"),
							array("Estado de cuenta de Cliente","reporte_cliente"),
							array("Reporte de Cobros","reporte_cobros"),
							array("Ventas Totales","reporte_ventas_totales")
							);
}
//Parámetros para el ícono "REGRESAR" del menú superior
foreach($_GET as $k => $v){
	$params .= "&{$k}={$v}";
}
$params = substr($params,1,strlen($params));
$_SESSION[start] = "comuni-k.php?".$params;
//Fin de parámetros
//Inicia configuración
titleset("Reportes");
//Fin de configuración
?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
<?php
$i=0;
foreach($reps as $k => $v){
  if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
  $i++;
?>
  <tr class="<?=$class?>"
  onMouseOver="this.setAttribute('class', 'tr_list_over');"
  onMouseOut="this.setAttribute('class', '<?=$class?>');"
  onclick="window.location='?section=<?=$v[1]?>'"
  style="cursor:pointer">
    <th><img src="imagenes/database.png" /></th>
    <td><?=$v[0]?></td>
  </tr>
<?php } ?>
</table>