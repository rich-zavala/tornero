<?php
if(isset($_POST[registrar])){
	$s = "INSERT INTO egresos VALUES (
								NULL,
								'{$_POST[proveedor]}',
								'{$_POST[total]}',
								'{$_POST[bancos]}',
								'{$_POST[fecha]}',
								'cheque',
								'proveedor',
								'{$_POST[referencia]}',
								NULL,
								0)";
	mysql_query($s) or die(mysql_error());
	$id = mysql_insert_id();
		
	foreach($_POST[folio] as $key=>$folios){
		if($_POST[abono][$key] > 0){
			$s = "INSERT INTO egresos_detalle (id_egreso, factura, abono) VALUES ('{$id}', '{$_POST[folio][$key]}', '{$_POST[abono][$key]}')";
			mysql_query($s) or die(mysql_error());
		}
	}
	
	$s = "INSERT INTO movimientos_bancos (banco, fecha, egresos, id_mov) VALUES ('{$_POST[bancos]}', '{$_POST[fecha]}', '{$_POST[total]}', '{$id}')";
	mysql_query($s) or die(mysql_error());
	
	relocation("?section=compra_pagos_detalle&id={$id}");
	exit();
}

$s_facturas = "SELECT
folio_factura,
importe,
id,
moneda,
importe-(SELECT IFNULL(SUM(ed.abono),0)  FROM egresos_detalle ed INNER JOIN egresos e ON ed.id_egreso = e.id WHERE factura = compras.id AND status = 0) 'saldo'

FROM compras
WHERE id_proveedor = '{$_GET[proveedor]}'
AND status = 0
HAVING saldo>0
ORDER BY fecha_factura ASC";

$q_facturas = mysql_query($s_facturas)or die(mysql_error());

//Inicia configuración
titleset("Registro de Pagos a Proveedor");
//Fin de configuración

?>
<script type="text/javascript" language="javascript">
function valida_abono(abono,saldo,total){
	num = parseFloat(document.getElementById(abono).value);
	if(document.getElementById(saldo).value - document.getElementById(abono).value < 0){
		alert("No puede abonar una cantidad mayor al saldo");
		document.getElementById(abono).value = "0.00";
		document.getElementById(abono).focus();
		document.getElementById(abono).select();
		}
	else{
		if (num<=0){document.getElementById(abono).style.backgroundColor = "#FFFFCA";}
		else{document.getElementById(abono).style.backgroundColor = "#CEE7FF";}
		
		abonos = document.getElementsByName('abono[]'); 
		cantidad = abonos.length;
		sum = 0;
		for(i = 0; i < cantidad; ++i)
		{
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
    cantidad = abonos.length;
    for(i = 0; i < cantidad; ++i){
      num = parseFloat(abonos[i].value);
      if (num<=0){
        abonos[i].disabled = true;
        folios[i].disabled = true;
      }
    }
		return true;
  }
	
  if (document.getElementById("bancos").value != 1){
    if(document.getElementById('referencia').value == ""){
      alert("Es necesario escribir una referencia bancaria.");
			document.getElementById('referencia').focus();
      return false;
    }
  }
  sum = parseFloat(document.getElementById('total').value);
  if (sum>0){
    //Comparar Saldo del Banco con el total de esta transacción
    saldo = parseFloat(document.getElementById('saldo_banco_input').value);
    if(document.getElementById("bancos").value != 1){ //Si es efectivo, no se hace la coprobación
      if(sum > saldo){
        if (confirm("El importe de esta transacción es mayor al disponible en el banco seleccionado.\n¿Desea crear un cheque posfechado?")){
          submit_form1();
        } else {
        	return false;
        }
      } else {
        submit_form1();
      }
    }
  } else {
    alert("No hay nada que pagar.");
		return false;
  }
}

function change_saldo(){
<?php
$bancosx = "SELECT id,nombre FROM bancos ORDER BY nombre";
$bancos_queryx = mysql_query($bancosx) or die (mysql_error());
while ($r = mysql_fetch_assoc($bancos_queryx)) {
	$sql_banco = "SELECT
	COALESCE((SELECT saldo FROM bancos WHERE id = {$r[id]}),0)
	+
	COALESCE((SELECT SUM(ingresos) FROM movimientos_bancos WHERE movimientos_bancos.banco = {$r[id]} GROUP BY movimientos_bancos.banco),0)
	-
	COALESCE((SELECT SUM(importe) FROM egresos WHERE status = 0 AND banco = {$r[id]} GROUP BY banco),0)
	AS 'saldo'";
	$query_banco = mysql_query($sql_banco) or die (mysql_error());
	$saldo_banco = mysql_fetch_assoc($query_banco);
	$saldo_banco = $saldo_banco[saldo];
?>
	if (document.getElementById("bancos").value == "<?=$r[id]?>"){
		document.getElementById("saldo_banco").innerHTML = "<?=money($saldo_banco)?>";
		document.getElementById("saldo_banco_input").value = "<?=$saldo_banco?>";
	}
<?php }?>
  if (document.getElementById("bancos").value == 1){
    document.getElementById("tr_referencia").style.display = "none";
    document.getElementById("show_saldo").style.display = "none";
  }
  else{
    document.getElementById("tr_referencia").style.display = "";
    document.getElementById("show_saldo").style.display = "";
    
  }
}

function gotothis(obj){
	window.location = "?section=compra_pagos&proveedor="+obj.options[obj.selectedIndex].value;
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
<center>
  <form action="" method="post" name="form1" id="form1" onsubmit="return disable_zero();">
    <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
      <tr>
        <th>Proveedor</th>
      </tr>
      <tr>
        <td>
        	<select name="proveedor" onchange="gotothis(this);" id="proveedor">
            <option value="0">Elija un proveedor</option>
            <?php
						$sql_proveedor = "SELECT clave, nombre FROM proveedores ORDER BY nombre";
						$query_proveedor = mysql_query($sql_proveedor) or die (mysql_error());
						while($r = mysql_fetch_array($query_proveedor)){
						?>
            <option value="<?=$r[clave]?>" <?=selected($_GET[proveedor],$r[clave])?>>
            <?=$r[nombre]?>
            </option>
            <?php
						}
						?>
          </select>
        </td>
      </tr>
    </table>
    <br>
    <span id="formulario_content">
    <?php
    		if(mysql_num_rows($q_facturas) == 0){
					if(isset($_GET[proveedor])){
						if($_GET[proveedor] != 0){
		?>
     <center>
       <b>No hay facturas pendientes de pago para este proveedor.</b>
     </center>
    <?php
    			}
				}
			} else {
		?>
    </span>
    <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
      <caption id="aviso">
      <b>Relaci&oacute;n de Facturas sin saldar. </b>
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
			while($row_pagos = mysql_fetch_assoc($q_facturas)){
				$folio = $row_pagos[folio_factura];
				$ident = $row_pagos[id];
				$saldo = $row_pagos[saldo];

					if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
			?>
      <tr id="tr_<?=$row_pagos[id]?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
        <td><?=$i+1;?></td>
        <td>
        	<a href="javascript:ajax_this('compras_detalle.php?&id=<?=$row_pagos[id]?>')"><?=$folio?></a>
        	<input type="hidden" value="<?=$ident?>" name="folio[]" />
        </td>
        <td style="text-align:right"><?=money($row_pagos[importe])?><?=mon($row_pagos[moneda])?></td>
        <td style="text-align:right">
					<?=money($saldo)?><?=mon($row_pagos[moneda])?>
          <input type="hidden" value="<?=$saldo;?>" name="saldo<?=$folio?>" id="saldo<?=$folio?>" />
        </td>
        <td>
        	<input name="abono[]" type="text" id="abono<?=$folio?>" onblur="numero(this,2); valida_abono('abono<?=$folio?>','saldo<?=$folio?>','total');" value="0.00" size="9" style="text-align:right; background-color:#FFFFCA; border-style:groove;"/>
        </td>
      </tr>
      <?php
	$i++;
} ?>
      <tr>
        <th colspan="5">
        	TOTAL $ <span id="total_span">0.00</span>
          <input name="total" type="hidden" id="total" value="0"/>
        </th>
      </tr>
    </table>
    <p>
    <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla">
      <caption id="aviso"><b>Detalles del Pago.</b></caption>
      <tr>
        <th>Banco</th>
        <td>
        	<select id="bancos" name="bancos" onchange="change_saldo();">
          <?php
					$bancos_queryx = mysql_query($bancosx) or die (mysql_error());
					while ($row_banco = mysql_fetch_assoc($bancos_queryx)){ 
						if($row_banco[id] <> '2'){
					?>
          <option value="<?=$row_banco[id]?>"><?=$row_banco[nombre]?></option>
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
      <tr id="tr_referencia">
        <th>No. de Cheque</th>
        <td><input type="text" id="referencia" name="referencia" value="" size="26" /></td>
      </tr>
      <tr>
        <th>Fecha</th>
        <td>
        	<input name="fecha" size="10" maxlength="10" readonly="readonly" value="<?=date("Y-m-d")?>" />
          <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha');" style="margin-bottom:-3px; cursor:pointer" />
        </td>
      </tr>
    </table>
    <p>
    <input type="submit" value="Registrar Pago" name="registrar"/>
    </span>
    <?php }?>
  </form>
</center>
<script language="JavaScript" type="text/javascript">
	var elementos = document.getElementsByName("abono[]");
	var proveedor = document.getElementById("proveedor");
	if(elementos.length>0 && proveedor.selectedIndex != 0){
		change_saldo();
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