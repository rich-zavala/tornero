<?php
//Inicia configuración
$_POST[printable] = 0;
titleset("Facturas pendientes por Vendedor");
filter_display("reporte_vendedor");
//Fin de configuración
?>
<script language="javascript" type="text/javascript">
function gotothis(){
	window.location = "?section=reporte_vendedor&vendedor="+document.getElementById("vendedores").options[document.getElementById("vendedores").selectedIndex].value+"&fecha1="+document.getElementById("fecha1").value+"&fecha2="+document.getElementById("fecha2").value;
}
</script>
<div id="filtro" class="<?=$_POST[class_filtro]?>" style="margin-top:0px">
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
  <tr>
    <th>Vendedor</th>  <th>Rango de Fechas</th><th>&nbsp;</th>
  </tr>
  <tr>
    <td style="text-align:center">
      <select name="vendedores" id="vendedores">
      	<option value="0">Elija un Vendedor</option>
        <?php
								$query_vendedores = "SELECT id_usuario, nombre FROM usuarios WHERE id_tipousuario = 3 OR id_tipousuario = 1 ORDER BY nombre ASC";
								$vendedores = mysql_query($query_vendedores)or die(mysql_error());
								while($row = mysql_fetch_assoc($vendedores)){
								?>
        <option value="<?=$row[id_usuario]?>" <?=selected($_GET[vendedor],$row[id_usuario])?>>
          <?=$row[nombre]?>
        </option>
        <?php }?>
      </select>
    </td>
<td><input name="fecha1" id="fecha1" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha1]?>" />
<img src="imagenes/calendar.png" onclick="displayDatePicker('fecha1');" style="margin-bottom:-3px; cursor:pointer" />&nbsp;&nbsp;&nbsp;y&nbsp;&nbsp;
<input name="fecha2" id="fecha2" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha2]?>" />
<img src="imagenes/calendar.png" onclick="displayDatePicker('fecha2');" style="margin-bottom:-3px; cursor:pointer" /></td>
<td onclick="gotothis();" style="cursor:pointer"><img src="imagenes/search_button.gif" width="78" height="19" /></td>
</tr>
</table>
<p>
</div>
<?php
if(isset($_GET[vendedor]) && $_GET[vendedor]!=0){	
	if(strlen($_GET[fecha1])>0 && strlen($_GET[fecha2]>0)){
		$fecha = "AND f.fecha_factura BETWEEN '{$_GET[fecha1]}' AND '{$_GET[fecha2]}'";
	} else {
		unset($_GET[fecha1]);
		unset($_GET[fecha2]);
	}
	$strSQL = "SELECT c.nombre, f.folio, f.fecha_factura, f.importe,
	IFNULL(SUM(id.abono),0) 'abono',
	f.importe-IFNULL(SUM(CASE WHEN i.status = 0 THEN id.abono ELSE 0 END),0) AS 'saldo'
	FROM facturas f
	INNER JOIN  clientes c on f.id_cliente = c.clave
	LEFT JOIN ingresos_detalle id ON f.folio = id.factura
	LEFT JOIN ingresos i ON i.id = id.id_ingreso
	WHERE c.vendedor = '{$_GET['vendedor']}'
	{$fecha}
	AND f.status = 0
	GROUP BY f.folio
	HAVING saldo > 0";
	//echo nl2br($strSQL);
	$reg = mysql_query($strSQL)or die(mysql_error());
	
	if(mysql_num_rows($reg) > 0){
?>
<table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla lista">
  <tr>
    <th>Cliente</th>
    <th>Folio</th>
    <th>Fecha</th>
    <th>Importe</th>
    <th>Abono</th>
    <th>Saldo</th>
  </tr>
  <?php
while($r = mysql_fetch_assoc($reg)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
?>
  <tr class="<?=$class?>"
  onMouseOver="this.setAttribute('class', 'tr_list_over');"
  onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td><?=$r[nombre]?></td>
    <td><?=$r[folio]?></td>
    <td><?=FormatoFecha($r[fecha_factura])?></td>
    <td style="text-align:right"><?=money($r[importe])?></td>
    <td style="text-align:right"><?=money($r[abono])?></td>
    <td style="text-align:right"><?=money($r[saldo])?></td>
  </tr>
<?php }} ?>
</table>
<?php } ?>
