<?php
if(isset($_POST[cliente]))
{
	//Número de cuenta del cliente
	if(strlen(trim($_POST[NumCtaPago])) == 0)
	{
		$s = "SELECT NumCtaPago FROM clientes WHERE clave = '{$_POST[cliente]}'";
		$q = mysql_query($s) or die (mysql_error());
		$r = mysql_fetch_assoc($q);
		$_POST[NumCtaPago] = $r[NumCtaPago];
	}
	$s = "UPDATE facturas SET
		folio = '{$_POST[folio]}',
		leyenda = '{$_POST[leyenda]}',
		id_facturista = {$_SESSION[id_usuario]},
		fecha_factura = '{$_POST[fecha]}',
		id_cliente = '{$_POST[cliente]}',
		datos_cliente = '{$_POST[cliente_datos]}',
		metodoDePago='{$_POST[metodoDePago]}',
		NumCtaPago='{$_POST[NumCtaPago]}',
		serie='{$_POST[serie_f]}',
		tipo = 'f'
		WHERE folio = '{$_GET[folio]}'";
	mysql_query($s) or die (mysql_error());

	$s = "UPDATE facturas_productos SET folio_factura = '{$_POST[folio]}', serie='{$_POST[serie_f]}' WHERE folio_factura = '{$_GET[folio]}'";
	mysql_query($s);

	$s = "UPDATE ingresos_detalle SET factura = '{$_POST[folio]}', serie_factura='{$_POST[serie_f]}'  WHERE factura = '{$_GET[folio]}'";
	mysql_query($s);

	relocation("?section=ventas_detalle&folio={$_POST[folio]}&serie={$_POST[serie_f]}&ok");

}	

$sql_fes="SELECT serie,folioi,foliof FROM vars LIMIT 1";
$query_fes = mysql_query($sql_fes);
$r_fes = mysql_fetch_assoc($query_fes);		


$sf = "SELECT folio FROM facturas ORDER BY folio+1 DESC LIMIT 1";
$qf = mysql_query($sf);
$rf = mysql_fetch_assoc($qf);
if($rf[folio] >= $r_fes[foliof])
{
	$proximo_folio ="";
	$seagoto=1;
}
else
{
	if($rf[folio] == "")
	{
		 $proximo_folio=$r_fes[folioi];
	}
	else{		
		$proximo_folio =number_format(($rf[folio]+1),0,"","");
	}
}

//Inicia configuración
titleset("Nota &gt; Factura: <font color='#CCFF00'>[{$_GET[folio]}]</font>");
//Fin de configuración
?>
<script language="JavaScript" type="text/javascript">
function verificar_folio(){	
	var folio = document.getElementById("folio");
	//folio.value = str_replace("Nota ","",folio.value);
	if(folio.value.length == 0){
		alert("Defina un folio para la nueva factura.");
		return false;
	} else {		
		var f_b=$("#folio").val(); 
		var s_f=$("#serie_f").val(); 	
		var url = "ajax_tools.php?folio_venta="+f_b+"&serie="+s_f+"&tipo=1";		
		var r = procesar(url);
		if(r > 0){
			alert("Este folio ha sido previamente usado.\nIntente nuevamente.");
			var url = "ajax_tools.php?obtener_folio_fallido="+s_f;
			var r = procesar(url);
			$("#folio").val(r); 
			folio.focus();
			folio.select();
			return false;
		} else {
			return true;
		}
	}
}
function dataX(data)
{
  $("#cliente_datos").val(data[0]+"|"+data[1]+"|"+data[2]+"|"+data[3]+"|"+data[4]+"|"+data[5]+"|"+data[6]+"|"+data[7]+"|"+data[8]+"|"+data[9]+"|"+data[10]);	
	
	if(data[4].length > 0)
	{
	  	data[4] = " Int. "+data[4];
	}
	if(data[3].length > 0)
	{
	  	data[3] = " No. "+data[3];
	}	
	//$("#cliente_data").val("CALLE: "+data[2]+data[3]+ data[4]+"\nCOLONIA: "+data[5]+"\nC.P.: "+data[10]+"\n"+data[6]+", "+data[7]+", "+data[8]+", "+data[9]+"\nRFC: "+data[0]);
}

function cliente_data_ajax(obj){
	var cliente = document.getElementById("cliente");
	var url = "ajax_tools.php?cliente_data2="+obj.options[obj.selectedIndex].value;
	data = array("","","","","","","","","","");
	r = procesar(url);		
	data = r.split("|");
	dataX(data);
}
$(document).ready(function(){
	cliente_data_ajax($("#cliente").get(0));
});

</script>
<form name="form1" method="post" action="" onSubmit="return verificar_folio();">
  <table border="0" align="center" cellpadding="6" cellspacing="0" class="bordear_tabla">
    <tr>
      <th>Nuevo Folio</th>
			<?php
			if(isset($seagoto))
			{
				$valor = "<span style='color:#F00; font-size:9px;'>Los folios se agotaron. Solicite otros.</span>";
				$sololectura = "readonly = 'readonly'";
			}
			else
			{
				$valor = "";
			}
			?>
			<td>
				<input name="folio" type="text" <?=$sololectura?> id="folio" size="10" value="<?=$proximo_folio?>" onblur="return verificar_folio();" />
				<input name="serie_f" type="hidden" value="<?=$r_fes[serie]?>" id="serie_f" size="10" />        
			</td>
			<th>Nueva Fecha</th>
			<td>
				<input name="fecha" size="10" maxlength="10" readonly="readonly" value="<?=date("Y-m-d")?>" />
				<img src="imagenes/calendar.png" onclick="displayDatePicker('fecha');" style="margin-bottom:-3px; cursor:pointer" />
			</td>
    </tr>
    <tr>
      <th>Cliente</th>
      <td colspan="3">
				<select name="cliente" id="cliente" onchange="cliente_data_ajax(this);">
				<?php
					$sql_cliente = "SELECT clave, nombre FROM clientes WHERE status = 0 ORDER BY nombre";
					$query_cliente = mysql_query($sql_cliente) or die (mysql_error());
					while($r = mysql_fetch_array($query_cliente))
					{
				?>
				<option value="<?=$r[clave]?>" <?=selected($_GET[cliente],$r[clave])?>>
					<?=$r[nombre]?>
				</option>
				<?php
					}
				?>
				</select>
				<input type="hidden" name="cliente_datos" value="" id="cliente_datos" />
			</td>
    </tr>
		<tr id="tr_fecha">
		</tr>
		<tr id="tr_metodo">
			<th>Método de pago</th>
			<td>
				<select name="metodoDePago">
					<option>EFECTIVO</option>
					<option>TARJETA DE CREDITO</option>
					<option>TARJETA DE DEBITO</option>
					<option>CHEQUE NOMINATIVO</option>
					<option>TRANSFERENCIA BANCARIA</option>
					<option>NO IDENTIFICADO</option>
				</select>
			</td>
			<th>No. de cuenta</th>
			<td>
				<input name="NumCtaPago" id="NumCtaPago" placeholder="Definido por el cliente">
			</td>
		</tr>
    <tr>
      <th>Leyenda</th>
      <td colspan="3"><textarea name="leyenda" cols="60" rows="3" id="leyenda"></textarea></td>
    </tr>
    <tr>
      <td colspan="4">
				<center><input type="submit" name="button" id="button" value="Convertir Nota a Factura"></center>
      </td>
    </tr>
  </table>
</form>
