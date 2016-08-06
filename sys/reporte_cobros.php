<?php
//Inicia configuración
$_POST[printable] = 0;
titleset("Reporte de cobros");
filter_display("reporte_vendedor");
//Fin de configuración
?>
<script language="javascript" type="text/javascript">
function gotothis(){
	window.location = "?section=reporte_cobros&vendedor="+$("#vendedores").val()+"&fecha1="+$("#fecha1").val()+"&fecha2="+$("#fecha2").val()+"&cliente="+$("#cliente").val();
}

function ajax_this(url){
	$(".lista").hide();
	$("#ajax_this").load(url);
	document.getElementById("ajax_content").style.display = "";
}

function cerrar_ajax(){
	$(".lista").show();
	document.getElementById("ajax_this").innerHTML = "";
	document.getElementById("ajax_content").style.display = "none";
}
</script>

<div id="ajax_content" style="background-color:#224887; padding:10px; display:none;">
	<div style="background-color:#FFF; text-align:center; padding:10px">
  	<a href="javascript: cerrar_ajax();" style="text-decoration:none">
    	<img src="imagenes/update.png" style="margin:0px 6px -3px 0px;" /><b>Regresar al reporte</b>
    </a>
  </div>
  <div id="ajax_this" style="background-color:#FFF; padding:10px"></div>
</div>

<div id="filtro" class="<?=$_POST[class_filtro]?>" style="margin-top:0px">
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
  <tr>
    <th>Vendedor</th>  <th>Rango de Fechas</th><th>&nbsp;</th>
  </tr>
  <tr>
    <td style="text-align:center">
      <select name="vendedores" id="vendedores">
      	<option value="0">Todos los vendedores</option>
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
<tr>
<td colspan="4">
<select name="cliente" id="cliente">
      <option value="0">Cualquier Cliente</option>
      <?php
								$query_clientes = "SELECT clave, nombre FROM clientes ORDER BY nombre";
								$clientes = mysql_query($query_clientes)or die(mysql_error());
								while($row2 = mysql_fetch_assoc($clientes)){
								?>
      <option value="<?=$row2[clave]?>" <?=selected($row2[clave],$_GET[cliente])?>><?=$row2[nombre]?></option>
      <?php }?>
    </select>
</td>
</tr>
</table>
<p>
</div>
<?php
if(isset($_GET[vendedor])){	
	if(strlen($_GET[fecha1])>0 && strlen($_GET[fecha2]>0)){
		$fecha = "AND f.fecha_factura BETWEEN '{$_GET[fecha1]}' AND '{$_GET[fecha2]}'";
	}
	if($_GET[vendedor] != 0)
	{
		$vendedor = "AND u.id_usuario = {$_GET[vendedor]}";
	}
	if($_GET[cliente] != 0)
	{
		$cliente = "AND clientes.clave = {$_GET[cliente]}";
	}
	$strSQL = "SELECT
						ingresos_detalle.factura folio,
						ingresos.fecha,
						abono,
						clientes.nombre cliente,
						usuarios.nombre vendedor,
						facturas.importe,
						fecha_factura,
						facturas.moneda,
						referencia,
						bancos.nombre banco
						FROM
						ingresos_detalle
						Inner Join ingresos ON ingresos_detalle.id_ingreso = ingresos.id
						Inner Join facturas ON ingresos_detalle.factura = facturas.folio
						Inner Join clientes ON facturas.id_cliente = clientes.clave
						Inner Join usuarios ON facturas.id_facturista = usuarios.id_usuario
						Inner Join movimientos_bancos ON movimientos_bancos.id_mov = ingresos.id
						Inner Join bancos ON bancos.id = movimientos_bancos.banco
						WHERE
						bancos.id <> 2 {$fecha} {$vendedor} {$cliente} ORDER BY folio";
	//echo nl2br($strSQL);
	$reg = mysql_query($strSQL)or die(mysql_error());
	
	if(mysql_num_rows($reg) > 0){
?>
<table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
	  <th>Folio</th>
    <th>Cliente</th>
    <th>Vendedor</th>
    <th>Fecha Factura</th>
    <th>Importe Factura</th>
    <th>Referencia Abono</th>
    <th>Fecha Abono</th>
    <th>Importe Abono</th>
    <th>Banco</th>
  </tr>
  <?php
$contado = 0;
$credito = 0;
$abonos_mn = 0;
$abonos_dll = 0;
$pagos = 0;
while($r = mysql_fetch_assoc($reg)){
	if($r[moneda] == "M.N.")
	{
		$abonos_mn += $r[abono];
	}
	else
	{
		$abonos_dll += $r[abono];
	}
	$pagos++;
	if($r[referencia] == "PAGO DE CONTADO")
	{
		$contado++;
	}
	else
	{
		$credito++;
	}
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
?>
  <tr class="<?=$class?>"
  onMouseOver="this.setAttribute('class', 'tr_list_over');"
  onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td><a href="javascript:ajax_this('ventas_detalle.php?folio=<?=$r[folio]?>')"><?=$r[folio]?></a></td>
    <td><?=$r[cliente]?></td>
    <td><?=$r[vendedor]?></td>
    <td><?=FormatoFecha($r[fecha_factura])?></td>
    <td style="text-align:right"><?=money($r[importe])?><?=mon($r[moneda])?></td>
    <td><?=$r[referencia]?></td>
    <td><?=FormatoFecha($r[fecha])?></td>
    <td style="text-align:right"><?=money($r[abono])?><?=mon($r[moneda])?></td>
    <td><?=$r[banco]?></td>
  </tr>
<?php }} ?>
</table>
<br />
<table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla lista">
	<?php if($abonos_mn > 0) { ?>
	<tr><th style="text-align:right">Abonos M.N.</th><td style="text-align:right"><?=money($abonos_mn)?><?=mon("M.N.")?></td></tr>
  <?php } if($abonos_dll > 0) { ?>
  <tr><th style="text-align:right">Abonos U.S.D.</th><td style="text-align:right"><?=money($abonos_dll)?><?=mon("U.S.D.")?></td></tr>
  <?php } ?>
  <tr><th style="text-align:right">Abonos recibidos</th><td style="text-align:right"><?=money($pagos)?></td></tr>
  <tr><th style="text-align:right">Pagados de contado</th><td style="text-align:right"><?=money($contado)?></td></tr>
  <tr><th style="text-align:right">Pagados con cr&eacute;dito</th><td style="text-align:right"><?=money($credito)?></td></tr>
</table>
<?php } ?>
