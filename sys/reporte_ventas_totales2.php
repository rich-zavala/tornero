<?php
//Inicia configuración
$_POST[printable] = 0;
titleset("Ventas Totales");
filter_display("reporte_vendedor");
//Fin de configuración
?>
<script language="javascript" type="text/javascript">
function gotothis(){
	//ORIGINAL
	//window.location = "?section=reporte_ventas_totales&vendedor="+document.getElementById("vendedores").options[document.getElementById("vendedores").selectedIndex].value+"&fecha1="+document.getElementById("fecha1").value+"&fecha2="+document.getElementById("fecha2").value
	window.location = "?section=reporte_ventas_totales&vendedor="+document.getElementById("vendedores").options[document.getElementById("vendedores").selectedIndex].value+"&fecha1="+document.getElementById("fecha1").value+"&fecha2="+document.getElementById("fecha2").value+"&tipo="+document.getElementById("tipo").options[document.getElementById("tipo").selectedIndex].value;
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
    <th>Vendedor</th> <th>Documento</th>  <th>Rango de Fechas</th><th>&nbsp;</th>
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
<!-- MOD -->
<td>
	<select id="tipo" name="tipo">
    	<option value="xtipo">Documento</option>
        <option value="f" <?=selected($_GET[tipo],f)?>>Facturas</option>
        <option value="n" <?=selected($_GET[tipo],n)?>>Notas</option>
    </select>
</td>
<!-- /MOD -->
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
if(isset($_GET[vendedor])){	
	if(strlen($_GET[fecha1])>0 && strlen($_GET[fecha2]>0)){
		$fecha = "AND f.fecha_factura BETWEEN '{$_GET[fecha1]}' AND '{$_GET[fecha2]}'";
	}
	if($_GET[vendedor] != 0)
	{
		$vendedor = "AND u.id_usuario = {$_GET[vendedor]}";
	}
	/* MOD  */
	if(isset( $_GET[tipo] ) && $_GET[tipo] != 'xtipo' ){
		$tipo = "AND f.tipo = '{$_GET[tipo]}'";
	}
	/* /MOD  */
	$strSQL = "SELECT
			folio,
			serie,
			fecha_factura,
			c.nombre cliente,
			u.nombre vendedor,
			importe,
			IF(f.status = 0,'Normal','<b>Cancelada</b>') status,
			f.moneda,
			f.tipo
			FROM
			facturas f
			LEFT Join clientes c ON f.id_cliente = c.clave
			Inner Join usuarios u ON f.id_facturista = u.id_usuario
			WHERE 1 {$fecha} {$vendedor} {$tipo} ORDER BY fecha_factura DESC,folio DESC";
	// echo nl2br($strSQL);
	$reg = mysql_query($strSQL)or die(mysql_error());
	
	if(mysql_num_rows($reg) > 0){
		$data = array();
		$data["Documentos_en_período"] = 0;
		$data["Facturas_normales"] = 0;
		$data["Notas_normales"] = 0;
		$data["Facturas_canceladas"] = 0;
		$data["Notas_canceladas"] = 0;
		$data["Importe_en_período_M.N."] = 0;
		$data["Importe_en_período_U.S.D."] = 0;
		$data["Importe_normal_Facturas_M.N."] = 0;
		$data["Importe_normal_Notas_M.N."] = 0;
		$data["Importe_normal_Facturas_U.S.D."] = 0;
		$data["Importe_normal_Notas_U.S.D."] = 0;
		$data["Importe_cancelado_Facturas_M.N."] = 0;
		$data["Importe_cancelado_Facturas_U.S.D."] = 0;
		$data["Importe_cancelado_Notas_M.N."] = 0;
		$data["Importe_cancelado_Notas_U.S.D."] = 0;
?>
<table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
	  <th>Folio</th>
    <th>Serie</th>
    <th>Cliente</th>
    <th>Vendedor</th>
    <th>Fecha</th>
    <th>Importe</th>
    <th>Status</th>
  </tr>
	<?php
	while($r = mysql_fetch_assoc($reg)){
		$data["Documentos_en_período"]++;
		
		if($r[status] == "Normal") {
			if( $r[tipo] == 'n' ){
				$data["Notas_normales"]++;
			}else{
				$data["Facturas_normales"]++;
			}
			
			if($r[moneda] == "M.N."){
				if( $r[tipo] == 'n' ){
					$data["Importe_normal_Notas_M.N."] += $r[importe];
				}else{
					$data["Importe_normal_Facturas_M.N."] += $r[importe];
				}
			}else{
				if( $r[tipo] == 'n' ){
					$data["Importe_normal_Notas_U.S.D."] += $r[importe];
				}else{
					$data["Importe_normal_Facturas_U.S.D."] += $r[importe];
				}
			}
		}else{
			$data["Facturas_canceladas"]++;
			if($r[moneda] == "M.N."){
				if( $r[tipo] == 'f' ){
					$data["Importe_cancelado_Facturas_M.N."] += $r[importe];
				}else{
					$data["Importe_cancelado_Notas_M.N."] += $r[importe];
				}
			}else{
				if( $r['tipo'] == 'f' ){
					$data["Importe_cancelado_Facturas_U.S.D."] += $r[importe];
				}else{
					$data["Importe_cancelado_Notas_U.S.D."] += $r[importe];
				}
			}
		}
		
		if($r[moneda] == "M.N."){
			$data["Importe_en_período_M.N."] += $r[importe];
		}else{
			$data["Importe_en_período_U.S.D."] += $r[importe];
		}
			
		if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
		$i++;
	?>
  <tr class="<?=$class?>"
  onMouseOver="this.setAttribute('class', 'tr_list_over');"
  onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td><a href="javascript:ajax_this('ventas_detalle.php?folio=<?=str_replace(' ','+',$r[folio])?>&serie=<?=$r[serie]?>')"><?=$r[folio]?></a></td>
    <td><?=$r[serie]?></a></td>
    <td style="white-space:normal"><?=$r[cliente]?></td>
    <td><?=$r[vendedor]?></td>
    <td><?=FormatoFecha($r[fecha_factura])?></td>
    <td style="text-align:right"><?=money($r[importe])?><?=mon($r[moneda])?></td>
    <td><?=$r[status]?></td>
  </tr>
<?php }}else{ ?>
	
  <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla lista" id="_lista">
    <tr>
    	<td><strong>No se encontraron coincidencias.</strong></td>
    </tr>
  </table>

<?php } ?>
</table><br />

<table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla lista">
<?php
if( $data != 0 ){
foreach($data as $label => $valor){
	if($valor > 0){
?>
	<tr>
  	<th style="text-align:right"><?=htmlentities(str_replace("_"," ",$label))?></th>
    <td style="text-align:right">
	<?php
		if($label == "Documentos_en_período" || $label == "Facturas_normales" || $label == "Notas_normales" || $label == "Facturas_canceladas" || $label == "Notas_canceladas"){
			echo $valor;
		}else{
			if(substr_count($label,"M.N.") > 0){
				echo money($valor).mon("M.N.");
			}else{
				echo money($valor).mon("U.S.D.");
			}
		}
	?>
	</td>
  </tr>
<?php } } ?>
</table>
<?php } }?>
