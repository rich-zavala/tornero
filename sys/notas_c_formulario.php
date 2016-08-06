<?php
if(isset($_GET[folio])){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	$s = "SELECT id FROM notas_de_credito WHERE folio = '{$_GET[folio]}'";
	$q = mysql_query($s);
	echo mysql_num_rows($q);
	exit();
}

if(isset($_POST[registrar])){
	$s = "INSERT INTO ingresos VALUES (
		NULL,
		'{$_POST[total]}',
		2,
		'nota_de_credito',
		'Nota de Crédito: {$_POST[referencia]}',
		'{$_POST[fecha]}',
    0
		)";
  mysql_query($s) or die($s.mysql_error());
	$id = mysql_insert_id();
	
	$s = "INSERT INTO movimientos_bancos VALUES (NULL,{$id},2,'{$_POST[fecha]}','{$_POST[total]}',NULL)";
	mysql_query($s) or die($s.mysql_error());
	
	$s = "INSERT INTO movimientos_bancos VALUES (
	NULL,
	{$id},
	'{$_POST[bancos]}',
	'{$_POST[fecha]}',
	'{$_POST[total]}',
	NULL
	)";
	mysql_query($s) or die($s.mysql_error());
	
	$s = "INSERT INTO notas_de_credito VALUES(
	NULL,
	{$id},
	'{$_POST[referencia]}',
	'cliente',
	{$_POST[cliente]},
	'{$_POST[reviso]}',
	'{$_POST[autorizo]}',
	0)";
	mysql_query($s) or die($s.mysql_error());
	$id_nota = mysql_insert_id();
	
	foreach($_POST[folio] as $key=>$folios){
		if($_POST[abono][$key] > 0){
			$s = "INSERT INTO ingresos_detalle VALUES (NULL,'{$id}','{$_POST[folio][$key]}','{$_POST[abono][$key]}','{$_POST[serie][$key]}')";
			mysql_query($s) or die($s.mysql_error());
			
			$s = "INSERT INTO notas_de_credito_detalle VALUES (NULL,'{$id_nota}','{$_POST[folio][$key]}','{$_POST[descripcion][$key]}','{$_POST[abono][$key]}','{$_POST[serie][$key]}')";
			mysql_query($s) or die($s.mysql_error());
		}
	}
	relocation("?section=notas_c_detalle&folio={$_POST[referencia]}");
	exit();
}
$query_cxc = "SELECT folio, tipo, importe, moneda,serie FROM facturas WHERE id_cliente = '{$_GET[clave]}' AND status = 0 AND tipo = 'f' ORDER BY fecha_factura ASC";
$cxc = mysql_query($query_cxc)or die(mysql_error());

//Inicia configuración
titleset("Registro de Nota de Cr&eacute;dito para Cliente");
//Fin de configuración
?>
<script type="text/javascript" language="javascript">
function cambio2(){
	if(document.getElementById("tipo_egreso").value == "transferencia"){
		document.getElementById("tag_prov2").innerHTML = "Folio de Transferencia";
	}
	else if (document.getElementById("tipo_egreso").value == "cheque"){
		document.getElementById("tag_prov2").innerHTML = "N&uacute;mero de Cheque";
	} else if(document.getElementById("tipo_egreso").value == "deposito"){
		document.getElementById("tag_prov2").innerHTML = "N&uacute;mero de Dep&oacute;sito";
	}
}

function valida_abono(obj,saldo){
	abono = parseFloat(obj.value);
	saldo = parseFloat(saldo);
	if(saldo - abono < 0){
		alert("No puede abonar una cantidad mayor al saldo");
		obj.value = "0.00";
		obj.focus();
		obj.select();
		}
	else{
		if (abono<=0){obj.style.backgroundColor = "#FFFFCA";}
		else{obj.style.backgroundColor = "#CEE7FF";}
		
		abonos = document.getElementsByName('abono[]'); 
		cantidad = abonos.length;
		sum = 0;
		for(i = 0; i < cantidad; ++i){
			num = abonos[i].value;
			sum = sum + parseFloat(num);
		}
		sum = sum.toFixed(2);
		document.getElementById('total_span').innerHTML = money(sum);
		document.getElementById('total').value = sum;
	}
}

function disable_zero(){	
	if(document.getElementById('referencia').value.length==0){
		alert("Elija un folio para esta nota de crédito.");
		document.getElementById('referencia').focus();
		return false;
	} else {
		verificar_folio(document.getElementById('referencia'));	
	}
	sum = parseFloat(document.getElementById('total').value);
	if (sum>0){
				return true;
		} else {
		alert("El formulario está en ceros.");
		return false;
	}
}

function verificar_folio(obj){
	if(obj.value.length>0){
		var url = "notas_c_formulario.php?folio="+obj.value;
		var r = procesar(url);
		if(r > 0){
			alert("El folio ya ha sido ocupado.\nIntente con otro.");
			obj.focus();
			obj.select();
			return false;
		} else {
			return true;
		}
	} else {
		return false;
	}
}

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

</script>
<div id="ajax_content" style="background-color:#224887; padding:10px; display:none;">
	<div style="background-color:#FFF; text-align:center; padding:10px">
  	<a href="javascript: cerrar_ajax();" style="text-decoration:none">
    	<img src="imagenes/update.png" style="margin:0px 6px -3px 0px;" /><b>Regresar al formulario de la Nota de Cr&eacute;dito</b>
    </a>
  </div>
  <div id="ajax_this" style="background-color:#FFF; padding:10px"></div>
</div>
<center>
  <form action="" method="post" name="form1" id="form1" onsubmit="return disable_zero();">
    <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla" style="margin-bottom:12px;">
      <tr>
        <th>Cliente</th>
        <td><select name="cliente" id="cliente" onchange="window.location='?section=notas_c_formulario&clave='+this.value">
            <option value="0">Seleccione</option>
            <?php
						$sql_cliente = "SELECT clave, nombre FROM clientes WHERE status = 0 ORDER BY nombre";
						$query_cliente = mysql_query($sql_cliente) or die (mysql_error());
						while($r = mysql_fetch_array($query_cliente)){
					?>
            <option value="<?=$r[clave]?>" <?=selected($_GET[clave],$r[clave])?>>
            <?=$r[nombre]?>
            </option>
            <?php
						}
					?>
          </select></td>
      </tr>
    </table>
    <?php if(isset($_GET[clave]) && $_GET[clave] != "0"){ ?>
    <span id="formulario_content">
    <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
      <caption id="aviso">
      <b>Facturas pendientes de pago.</b>
      </caption>
      <tr>
        <th>&nbsp;</th>
        <th>Folio Factura</th>
        <th>Importe</th>
        <th>Saldo</th>
        <th>Abono</th>
        <th>Descripci&oacute;n</th>
      </tr>
      <?php 
$i = 0;
while($r = mysql_fetch_assoc($cxc)){
$folio = $r[folio];
$importe = $r[importe];
	
	if($r[serie] == "")
	{
	 $serie= " AND serie_factura IS NULL";
	}
	else
	{
		$serie= " AND serie_factura='{$r[serie]}'";
	}
	
	//Buscar Saldo de ESTA Factura
	$sql_saldo = "SELECT
  ({$importe}-IFNULL(SUM(abono),0)) AS 'saldo'
  FROM ingresos_detalle
  INNER JOIN ingresos ON ingresos.id = ingresos_detalle.id_ingreso
  WHERE factura = '{$r[folio]}' {$serie}
  AND status = 0";
	$query_saldo = mysql_query($sql_saldo) or die (mysql_error());
	$saldo = mysql_fetch_assoc($query_saldo);
	if ($saldo[saldo] > 0){
			if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	?>
      <tr id="tr_<?=$row_pagos[id]?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
        <td><?=$i+1?></td>
        <td><a href="javascript:ajax_this('ventas_detalle.php?folio=<?=$r[folio]?>&serie=<?=$r[serie]?>&v=1')"><?=$r[folio]?></a>
          <input type="hidden" value="<?=$r[folio]?>" name="folio[]" />
          <input type="hidden" value="<?=$r[serie]?>" name="serie[]" />
          </td>
        <td><?=money($r[importe])?><?=mon($r[moneda])?></td>
        <td><?=money($saldo[saldo])?><?=mon($r[moneda])?>
          <input type="hidden" value="<?=$saldo[saldo]?>" name="saldo<?=$r[folio]?>" id="saldo<?=$r[folio]?>" /></td>
        <td><input name="abono[]" type="text" id="abono<?=$r[folio]?>" onblur="numero(this,2); valida_abono(this,<?=$saldo[saldo]?>);" value="0.00" size="9" style="text-align:right; background-color:#FFFFCA; border-style:groove;"/></td>
        <td><textarea name="descripcion[]" id="descripcion<?=$r[folio]?>" cols="26" rows="2"></textarea></td>
      </tr>
      <?php
	$i++; 
	}
}?>
      <tr>
        <th colspan="6"> TOTAL $ <span id="total_span">0.00</span>
          <input name="total" type="hidden" id="total" value="0"/></th>
      </tr>
    </table>
    <p>
    <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla">
      <caption id="aviso">
      <b>Detalles de la Nota de Cr&eacute;dito.</b>
      </caption>
      <tr>
        <th>Folio</th>
        <td><input type="text" id="referencia" name="referencia" value="" size="10" onBlur="return verificar_folio(this);" /></td>
      </tr>
      <tr>
        <th>Revis&oacute;</th>
        <td><input name="reviso" type="text" id="reviso" size="30" /></td>
      </tr>
      <tr>
        <th>Autoriz&oacute;</th>
        <td><input name="autorizo" type="text" id="autorizo" size="30" /></td>
      </tr>
      <tr>
        <th>Fecha</th>
        <td><input name="fecha" size="10" maxlength="10" readonly="readonly" value="<?=date("Y-m-d")?>" />
          <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha');" style="margin-bottom:-3px; cursor:pointer" /></td>
      </tr>
    </table>
    <p>
      <input type="submit" value="Registrar Nota de Crédito" name="registrar"/>
    </span>
  </form>
</center>
<?php } ?>
<script language="JavaScript" type="text/javascript">
	var elementos = document.getElementsByName("abono[]");
	var cliente = document.getElementById("cliente");
	if(elementos.length==0 && cliente.selectedIndex != 0){
		document.getElementById("formulario_content").innerHTML = "<b>No hay facturas pendientes de pago para este cliente.</b>";
	}
</script>
