<?php
if(isset($_POST[registrar])){
	/*$s = "SELECT clientes.*, pais_nombre FROM clientes INNER JOIN paises ON clientes.pais = paises.id WHERE clave = '{$_POST[cliente]}'";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
	$cliente = "{$r[direccion]}
{$r[ciudad]}, {$r[estado]}. {$r[pais_nombre]}.
RFC: {$r[RFC]}";*/
	
	$sql_fe ="SELECT ncsd,anoa,noa,serie FROM vars LIMIT 1";
	$query_fe = mysql_query($sql_fe);
	$r_fe = mysql_fetch_assoc($query_fe);	
	
	//Aqui se inserta la nueva factura BY CHRIS	
	$s = "INSERT INTO facturas VALUES
	 ('{$_POST[folio_factura]}',
	 '{$_POST[leyenda]}',
	 $_SESSION[id_usuario],
	 {$_POST[id_almacen]},
	 '{$_POST[fecha]}',
	 NOW(),
	 '_normal_',
	 {$_POST[cliente]},
	 '{$_POST[cliente_datos]}',
	 {$_POST[total]},
	 'f',
	 'M.N.',
	 '{$r_fe[anoa]}',
	 '{$r_fe[noa]}',
	 '{$r_fe[ncsd]}',
	 '{$_POST[serie_f]}',		
	 0)";
	mysql_query($s) or die ($s."<p>".mysql_error());
	
	foreach($_POST[folio] as $k => $v){
  	  $s2 = "SELECT * FROM facturas_productos WHERE folio_factura= '{$v}'";
	  $q2 = mysql_query($s2);
	  while($r2 = mysql_fetch_assoc($q2)){	
	    $s3 = "INSERT INTO facturas_productos 
		VALUES(
		null, 
		'{$_POST[folio_factura]}',
		'{$r2[id_producto]}',
		'{$r2[cantidad]}',
		'{$r2[lote]}',
		'{$r2[precio]}',
		'{$r2[descuento]}',
		'{$r2[iva]}',
		'{$r2[importe ]}',
		'{$r2[especial]}',
		'{$r2[complemento]}',
		'{$r2[canti_]}',
		'{$r2[unidad]}',
		'{$r2[serie]}'
		 )";		 		
		 mysql_query($s3) or die("facturas_productos: ".mysql_error());		   
	  }
		
		/*$s = "UPDATE facturas_productos SET folio_factura = '{$_POST[folio_factura]}',serie='{$_POST[serie_f]}' WHERE folio_factura = '{$v}'";
		mysql_query($s) or die ($s."<p>".mysql_error());*/
		/*$s = "DELETE FROM facturas WHERE folio = '{$v}'";
		mysql_query($s) or die ($s."<p>".mysql_error());*/
		$s = "UPDATE ingresos_detalle SET factura = '{$_POST[folio_factura]}', serie_factura='{$_POST[serie_f]}' WHERE factura = '{$v}'";
		mysql_query($s) or die ($s."<p>".mysql_error());

	}
	relocation("?section=ventas_detalle&folio={$_POST[folio_factura]}&serie={$_POST[serie_f]}");
	exit();
}

$sql_fes="SELECT serie,folioi,foliof FROM vars LIMIT 1";
$query_fes = mysql_query($sql_fes);
$r_fes = mysql_fetch_assoc($query_fes);		


$sf = "SELECT folio FROM facturas WHERE serie ='{$r_fes[serie]}' ORDER BY folio+1 DESC LIMIT 1";
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
titleset("Facturar varias Notas de venta");
//Fin de configuración
?>
<script language="javascript" type="text/javascript">
function ajax_this(url){
	document.getElementById("form1").style.display = "none";
	document.getElementById("ajax_this").innerHTML = procesar(url);
	document.getElementById("ajax_content").style.display = "";
}

function cerrar_ajax(){
	document.getElementById("form1").style.display = "";
	document.getElementById("ajax_this").innerHTML = "";
	document.getElementById("ajax_content").style.display = "none";
}

function verificar_folio(){	
	var folio = document.getElementById("folio_factura");
	//folio.value = str_replace("Nota ","",folio.value);
	if(folio.value.length == 0){
		alert("Defina un folio para la nueva factura.");
		return false;
	} else {		
		var f_b=$("#folio_factura").val(); 
		var s_f=$("#serie_f").val(); 	
		var url = "ajax_tools.php?folio_venta="+f_b+"&serie="+s_f+"&tipo=1";		
		var r = procesar(url);
		if(r > 0){
			alert("Este folio ha sido previamente usado.\nIntente nuevamente.");
			var url = "ajax_tools.php?obtener_folio_fallido="+s_f;
			var r = procesar(url);
			$("#folio_factura").val(r); 
			folio.focus();
			folio.select();
			return false;
		} else {
			return true;
		}
	}	
}

function disable_zero(){
	if(verificar_folio()){
			if(document.getElementById('folio_factura').value == ""){
				alert("Escriba el folio de la nueva Factura.");
				document.getElementById('folio').focus();
				return false;
			}
		
			if(parseFloat(document.getElementById('total').value)>0){
				return true;
			} else {
				alert("El formulario está en ceros.");
				return false;
			}
	}
	else {
		return false;
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
 if($("#id_client").val() == ""){
	var cliente = document.getElementById("cliente");
	var url = "ajax_tools.php?cliente_data2="+obj.options[obj.selectedIndex].value;
	data = array("","","","","","","","","","");
	r = procesar(url);		
	data = r.split("|");
	dataX(data);
 }else{
	$("#cliente").attr("selectedIndex",$("#id_client").val());
 }
}
$(document).ready(function(){
	cliente_data_ajax($("#cliente").get(0));
});
</script>
<div id="ajax_content" style="background-color:#224887; padding:10px; display:none;">
	<div style="background-color:#FFF; text-align:center; padding:10px">
  	<a href="javascript: cerrar_ajax();" style="text-decoration:none">
    	<img src="imagenes/update.png" style="margin:0px 6px -3px 0px;" /><b>Regresar al formulario</b>
    </a>
  </div>
  <div id="ajax_this" style="background-color:#FFF; padding:10px"></div>
</div>

<center>
<form action="" method="post" name="form1" id="form1" onsubmit="return disable_zero();">
<input name="control" type="hidden" value="0" id="control">
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="facturator">
  <tr>
    <td colspan="4" style="text-align:center" valign="middle" id="aviso">
    	<a href="facturar_notas_pick.php" onclick="return hs.htmlExpand(this,{objectType:'iframe',headingText:'Facturas con Saldo',minWidth:750,height:600,preserveContent:false,cacheAjax:false})">
      	<img src="imagenes/add.png" style="margin-bottom:-3px;" /> Elegir Notas de Venta
      </a>
    </td>
  </tr>
  <tr id="tr_vacio">
  <th colspan="4">Sin notas seleccionadas.</th>
  </tr>
  <tr id="tr_cabecera" style="display:none">
    <th>Nota de Venta</th>
    <th>Fecha</th>
    <th>Importe</th>
  </tr>
  <tr>
  <th colspan="5" id="tr_total" style="display:none"> TOTAL $ <span id="total_span">0.00</span>
    <input name="total" type="hidden" id="total" value="0"/></th>
	</tr>
</table>
<p>
<table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla">
  <caption id="aviso">
  <b>Detalles de la nueva Factura</b>
  </caption>
  <tr>
    <th>Folio</th>
    <?php if(isset($seagoto)){
					 $valor= "<span style='color:#F00; font-size:9px;'>Los folios se agotaron. Solicite otros.</span>";
					  $sololectura="readonly='readonly'";
					}
					else
					{
					 $valor="";	
					}
					?>          
        <td><input name="folio_factura" type="text" <?=$sololectura?> id="folio_factura" size="10" value="<?=$proximo_folio?>" onblur="return verificar_folio();" /> <?=$valor?>      
        <input name="serie_f" type="hidden" value="<?=$r_fes[serie]?>" id="serie_f" size="10" />         
    
    
   <!--<input name="folio_factura" type="text" id="folio_factura" size="10" onblur="return verificar_folio();" />--></td>
  </tr>
  <tr>
    <th>Almac&eacute;n</th>
    <td>        <select name="id_almacen" id="id_almacen">
            <?php
						foreach($_SESSION[almacenes] as $k => $v){
						/*	echo "<option value=\"{$v[id]}\" ".selected($_GET[almacen],$v[id]).">{$v[descripcion]}</option>\r\n";
						}*/
						if($k == 0){
							$append = "<option value=\"{$v[id]}\" ".selected($_GET[almacen],$v[id]).">{$v[descripcion]}</option>\r\n";}	
						if($k == 1){
							$appen = "<option value=\"{$v[id]}\" ".selected($_GET[almacen],$v[id]).">{$v[descripcion]}</option>\r\n";}							
						}
						echo $appen;
						echo $append;	
						?>
          </select></td>
  </tr>

  <tr id="tr_referencia">
    <th>Cliente</th>
    <td>
      <select name="cliente" id="cliente" onchange="cliente_data_ajax(this);" >
        <?php
$sql_cliente = "SELECT clave, nombre, credito, dias_credito FROM clientes WHERE status = 0 ORDER BY nombre";
$query_cliente = mysql_query($sql_cliente) or die (mysql_error());
if(mysql_num_rows($query_cliente)>0){
	while($r = mysql_fetch_array($query_cliente)){
		echo "<option value='{$r[clave]}' title='{$r[credito]}~{$disponible}'>{$r[nombre]}</option>";
	}
}
?>
      </select><input type="hidden" name="cliente_datos" value="" id="cliente_datos" /><input type="hidden" name="id_client" value="" id="id_client" />
    </td>
  </tr>
  <tr id="tr_referencia">
    <th>Fecha</th>
    <td><input name="fecha" size="10" maxlength="10" readonly="readonly" value="<?=date("Y-m-d")?>" />
      <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha');" style="margin-bottom:-3px; cursor:pointer" /></td>
  </tr>
  <tr>
    <th>Leyenda</th>
    <td><textarea name="leyenda" cols="30" rows="3" id="leyenda"></textarea></td>
  </tr>
</table>
        <p>
          <input type="submit" value="Crear Factura" name="registrar"/>
</form>
</center>