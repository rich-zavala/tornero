<?php
if(isset($_POST[registrar])){
	if($_POST['bancos'] == 1){ //Efectivo
		$_POST['referencia'] = "Pago en Efectivo";
	}
	
	$s = "INSERT INTO ingresos VALUES (
		NULL,
		'{$_POST['total']}',
		'{$_POST['bancos']}',
		'{$_POST['tipo_egreso']}',
		'{$_POST['referencia']}',
		'{$_POST[fecha]}',
    0
		)";
  mysql_query($s) or die(mysql_error());
	$id = mysql_insert_id();

	$s = "INSERT INTO movimientos_bancos VALUES (
							NULL,
							{$id},
							'{$_POST['bancos']}',
							'{$_POST[fecha]}',
							'{$_POST['total']}',
							NULL
							)";
	mysql_query($s) or die(mysql_error());

	foreach($_POST['folio'] as $key=>$folios){
		if($_POST['abono'][$key] > 0){
			$s = "INSERT INTO ingresos_detalle VALUES (
				NULL,
				'{$id}',
				'{$_POST['folio'][$key]}',
				'{$_POST['abono'][$key]}',
				'{$_POST['serie'][$key]}'
				)";
			mysql_query($s) or die(mysql_error());
		}
	}
	relocation("?section=ingresos_detalle&id={$id}");
	exit();
}

$query_cxc = "SELECT folio, serie, tipo, importe, moneda FROM facturas WHERE id_cliente = '{$_GET['clave']}' AND status = 0 AND tipo = 'f' ORDER BY fecha_factura ASC";
$cxc = mysql_query($query_cxc)or die(mysql_error());

//Inicia configuración
titleset("Cuentas por Cobrar");
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
  function submit_form1(){
    abonos = document.getElementsByName('abono[]');
    folios = document.getElementsByName('folio[]');
    series = document.getElementsByName('serie[]');
    cantidad = abonos.length;
    for(i = 0; i < cantidad; ++i){
      num = parseFloat(abonos[i].value);
      if (num<=0){
        abonos[i].disabled = true;
        folios[i].disabled = true;
        series[i].disabled = true;
      }
    }
    return true;
  }
	
  if(document.getElementById('referencia').value == "" && document.getElementById("bancos").value != "1"){
		alert("Es necesario escribir una referencia bancaria.");
		document.getElementById('referencia').focus();
    return false;
  }
  sum = parseFloat(document.getElementById('total').value);
  if (sum>0){
        submit_form1();
    } else {
    alert("El formulario está en ceros.");
		return false;
  }
}

function change_saldo(){
<?php
$bancosx = "SELECT id,nombre FROM bancos ORDER BY nombre";
$bancos_queryx = mysql_query($bancosx) or die (mysql_error());
while ($r = mysql_fetch_assoc($bancos_queryx)) {
	$sql_banco = "SELECT
	COALESCE((SELECT saldo FROM bancos WHERE id = {$r['id']}),0)
	+
	COALESCE((SELECT SUM(ingresos) FROM movimientos_bancos WHERE movimientos_bancos.banco = {$r['id']} GROUP BY movimientos_bancos.banco),0)
	-
	COALESCE((SELECT SUM(importe) FROM egresos WHERE status = 0 AND banco = {$r['id']} GROUP BY banco),0)
	AS 'saldo'";
	$query_banco = mysql_query($sql_banco) or die (mysql_error());
	$saldo_banco = mysql_fetch_assoc($query_banco);
	$saldo_banco = $saldo_banco['saldo'];
?>
	if (document.getElementById("bancos").value == "<?=$r['id']?>"){
		document.getElementById("saldo_banco").innerHTML = "<?=money($saldo_banco)?>";
		document.getElementById("saldo_banco_input").value = "<?=$saldo_banco?>";
	}
<?php }?>
  if (document.getElementById("bancos").value == 1){ //Efectivo
    document.getElementById("tr_tipo_abono").style.display = "none";
    document.getElementById("show_saldo").style.display = "none";
		document.getElementById("tr_referencia").style.display = "none";
  }
  else{
    document.getElementById("tr_referencia").style.display = "";
    document.getElementById("show_saldo").style.display = "";
    
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
        <td><select name="cliente" id="cliente" onchange="window.location='?section=ingresos_por_cliente&clave='+this.value">
            <option value="0">Seleccione un Cliente</option>
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
      </tr>
<?php 
$i = 0;
while($r = mysql_fetch_assoc($cxc)){
$folio = $r['folio'];
$importe = $r['importe'];
	
	//Buscar Saldo de ESTA Factura
	$sql_saldo = "SELECT
  ({$importe}-IFNULL(SUM(abono),0)) AS 'saldo'
  FROM ingresos_detalle
  INNER JOIN ingresos ON ingresos.id = ingresos_detalle.id_ingreso
  WHERE factura = '{$r[folio]}'
  AND status = 0";
	$query_saldo = mysql_query($sql_saldo) or die (mysql_error());
	$saldo = mysql_fetch_assoc($query_saldo);
	if ($saldo[saldo] > 0){
			if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	?>
      <tr id="tr_<?=$row_pagos['id']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
        <td><?=$i+1?></td>
        <td>
					<a href="javascript:ajax_this('ventas_detalle.php?folio=<?=$r[folio]?>&serie=<?=$r[serie]?>&v=1')"><?=$r[folio]?></a>
          <input type="hidden" value="<?=$r[folio]?>" name="folio[]" />
          <input type="hidden" value="<?=$r[serie]?>" name="serie[]" />
        </td>
        <td><?=money($r[importe])?><?=mon($r[moneda])?></td>
        <td><?=money($saldo[saldo])?><?=mon($r[moneda])?>
          <input type="hidden" value="<?=$saldo[saldo]?>" name="saldo<?=$r[folio]?>" id="saldo<?=$r[folio]?>" /></td>
        <td><input name="abono[]" type="text" id="abono<?=$r[folio]?>" onblur="numero(this,2); valida_abono(this,<?=$saldo[saldo]?>);" value="0.00" size="9" style="text-align:right; background-color:#FFFFCA; border-style:groove;"/></td>
      </tr>
      <?php
	$i++; 
	}
}?>
      <tr>
        <th colspan="5"> TOTAL $ <span id="total_span">0.00</span>
          <input name="total" type="hidden" id="total" value="0"/></th>
      </tr>
    </table>
    <p>
    <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla">
          <caption id="aviso">
          <b>Detalles de movimiento bancario.</b>
          </caption>
          <tr>
        <th>Banco</th>
        <td>
        	<select id="bancos" name="bancos" onchange="change_saldo();">
          <?php
					$bancos_queryx = mysql_query($bancosx) or die (mysql_error());
					while ($row_banco = mysql_fetch_assoc($bancos_queryx)){ 
						if($row_banco['id'] <> '2'){
					?>
          <option value="<?=$row_banco['id']?>"><?=$row_banco['nombre']?></option>
          <?php
						}
					}
					?>
          </select>
          <span id="show_saldo">
          	<br />
	          <b>Saldo: $</b> <span id="saldo_banco"></span>
   	       <input type="hidden" name="saldo_banco_input" id="saldo_banco_input" value=""/>
          </span>
        </td>
      </tr>
          <tr id="tr_tipo_abono">
            <th>Tipo de Abono</th>
            <td><select name="tipo_egreso" onchange="cambio2();" id="tipo_egreso">
                <option value="cheque">Cheque</option>
                <option value="deposito">Dep&oacute;sito Bancario</option>
                <option value="transferencia">Transferencia</option>
              </select></td>
          </tr>
          <tr id="tr_referencia">
            <th>Referencia</th>
            <td><span id="tag_prov2">N&uacute;mero de Cheque</span><br />
              <input type="text" id="referencia" name="referencia" value="" size="26" />
    				</td>
          </tr>
          <tr>
            <th>Fecha</th>
            <td><input name="fecha" size="10" maxlength="10" readonly="readonly" value="<?=date("Y-m-d")?>" />
            <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha');" style="margin-bottom:-3px; cursor:pointer" /></td>
          </tr>
        </table>
        <p>
          <input type="submit" value="Registrar Pago" name="registrar"/>
    </span>
      </form>
</center>
<?php } ?>
<script language="JavaScript" type="text/javascript">
	var elementos = document.getElementsByName("abono[]");
	var cliente = document.getElementById("cliente");
	if(elementos.length==0 && cliente.selectedIndex != 0){
		document.getElementById("formulario_content").innerHTML = "<b>No hay facturas pendientes de pago para este cliente.</b>";
	} else if(cliente.selectedIndex != 0){
		change_saldo();
	}
</script>