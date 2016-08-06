<?
/*NOTA
El usuario previamente eligió
una factura por medio de Greybox
*/
if(isset($_POST[submit])){
	existencias($_POST);
  $sql = "INSERT INTO devoluciones_proveedores VALUES (NULL, NOW(), '{$_POST[compra]}', '{$_POST[importe]}', 0)";
  mysql_query($sql) or die ($sql."-".mysql_error());
  $ID = mysql_insert_id();
  for($i=0; count($_POST[id_producto])>$i; $i++){
		if($_POST[cantidad][$i]>0){
			$sql0 = "INSERT INTO devoluciones_proveedores_detalles VALUES(NULL, '{$ID}', '{$_POST[id_producto][$i]}', '{$_POST[lote][$i]}', {$_POST[cantidad][$i]}, {$_POST[precio][$i]})";
			mysql_query($sql0) or die ($sql0."-".mysql_error());
			$sql1 = "UPDATE existencias SET cantidad=cantidad-{$_POST[cantidad][$i]} WHERE id_producto = '{$_POST[id_producto][$i]}' AND lote = '{$_POST[lote][$i]}' AND id_almacen = '{$_POST[id_almacen]}'";
			mysql_query($sql1) or die (mysql_error());
			// Para movimientos
			$insert_movimientos = "INSERT INTO movimientos
			VALUES(
			null,
			'{$_POST[id_almacen]}',
			'',
			'13',
			'{$_POST[id_producto][$i]}',
			'{$_POST[cantidad][$i]}',
			'{$_POST[lote][$i]}',
			NOW(),
			'{$_SESSION[id_usuario]}',
			'{$ID}',
			'CTO1'			
			)";
			mysql_query($insert_movimientos) or die (mysql_error());
		}
  }
  $sql3 = "UPDATE compras SET importe=importe-{$_POST[importe]} WHERE id = '{$_POST[compra]}'";
  mysql_query($sql3) or die (mysql_error());
  relocation("?section=devoluciones_p_detalle&id={$ID}");
	exit();
}

$strSQL = "SELECT
c.folio_factura,
COALESCE(existencias.cantidad)'existencia',
cd.lote,
cd.sub_importe,
cd.iva,
cd.importe,
cd.cantidad,
cd.id_producto,
pr.codigo_barras,
pr.descripcion,
almacenes.descripcion'almacen',
c.id_almacen,
proveedores.nombre
FROM
compras AS c
INNER JOIN compras_detalle AS cd ON cd.id_compra = c.id
INNER JOIN productos AS pr ON pr.id_producto = cd.id_producto
INNER JOIN existencias ON existencias.id_producto = cd.id_producto AND existencias.id_almacen = c.id_almacen AND existencias.lote = cd.lote
INNER JOIN almacenes ON c.id_almacen = almacenes.id_almacen
INNER JOIN proveedores ON c.id_proveedor = proveedores.clave
WHERE c.id = '{$_GET[compra]}'";
//echo nl2br($strSQL);exit();
$query_1 = mysql_query($strSQL) or die (nl2br($strSQL).mysql_error());

$data = mysql_fetch_assoc($query_1);
$id_almacen = $data[id_almacen];
$almacen = $data[almacen];
$folio = $data[folio_factura];
$proveedor = $data[nombre];

$productos_q = mysql_query($strSQL) or die (mysql_error());

//Productos YA DEVUELTOS
$devs = "SELECT
dpd.*
FROM
devoluciones_proveedores dp
INNER JOIN devoluciones_proveedores_detalles dpd ON dp.id = dpd.id_devolucion
WHERE
dp.id_compra = '{$_GET[compra]}'
AND status = 0";
//echo nl2br($devs);exit();
$devq = mysql_query($devs) or die (mysql_error());
$devueltos = array();
while($d = mysql_fetch_assoc($devq)){
  $devueltos[$d[id_producto]][id_producto] = $d[id_producto];
  $devueltos[$d[id_producto]][lote] = $d[lote];
  $devueltos[$d[id_producto]][cantidad] = $d[cantidad];
}

//Inicia configuración
titleset("Devolver la Compra \\\"{$folio}\\\"");
//Fin de configuración
?>
<script language="javascript" type="text/javascript" src="rich/text_change.js"></script>
<script language="JavaScript" type="text/javascript">
function activar(num){
	var check = document.getElementById("checkbox"+num);
  var id_producto = document.getElementById("id_producto"+num);
  var lote = document.getElementById("lote"+num);
	var cantidad = document.getElementById("cantidad"+num);
	var precio = document.getElementById("precio"+num);
	if(check.checked){
    id_producto.disabled = false;
    lote.disabled = false;
		cantidad.disabled = false;
		precio.disabled = false;
    cantidad.focus();
    cantidad.select();
	}
	else{
    id_producto.disabled = true;
    lote.disabled = true;
		cantidad.disabled = true;
		precio.disabled = true;
	}
	sumatoria();
}
function checar_cantidad(obj,num){
	var cantidad = obj.value;
	var cantidad_x = document.getElementById("cantidad_x"+num).value;
	if(isNaN(cantidad) || cantidad < 0 || parseFloat(cantidad) > parseFloat(cantidad_x)){
		alert("La cantidad a devolver no puede ser mayor a la cantidad rentregada con la factura.");
		obj.value = document.getElementById("cantidad_x"+num).value;
		obj.focus();
		obj.select();
    sumatoria();
		return false;
	}
	else{
    obj.value = parseInt(obj.value);
		sumatoria();
	}
}
function cambiarcolor(row){
	document.getElementById("row"+row).style.backgroundColor = "#FEEE96";
}
function cambiarcolor2(row){
	document.getElementById("row"+row).style.backgroundColor = "#FFFFFF";
}
function sumatoria(){
	var span = document.getElementById("total");
  var importe = document.getElementById("importe");
	var productos = document.getElementsByName("cantidad[]");
	var total = 0;
	for(var i=0; productos.length > i; i++){
		if(document.getElementById("cantidad"+i).disabled == false){
			var cantidad = parseFloat(document.getElementById("cantidad"+i).value);
			var precio = parseFloat(document.getElementById("precio"+i).value);
			var subtotal = cantidad*precio;
			total = total+subtotal;
		}
	}
	span.innerHTML = money(total.toFixed(2));
  importe.value = total.toFixed(2);
}
function checar_existencia(obj,existencia,num){
  //alert(obj.value+"<"+existencia);
  if(parseFloat(obj.value)>parseFloat(existencia)){
    alert("No hay esta cantidad de productos en el almacén.\nVerifique el inventario.");
    obj.disabled = "disabled";
    document.getElementById("checkbox"+num).checked = false;
    document.getElementById("id_producto"+num).disabled = true;
    document.getElementById("lote"+num).disabled = true;
    document.getElementById("cantidad"+num).disabled = true;
    document.getElementById("precio"+num).disabled = true;
    sumatoria();
    return false
  }
}

function hay_algo(){
	sumatoria();
  if(document.getElementById("importe").value <= 0){
    alert("No hay nada que devolver.");
    return false;
  }
}
</script>
<center>
<form action="" method="post" onsubmit="return hay_algo();" id="form1">
  <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla" style="text-align:center">
    <tr>
      <th>Proveedor:</th>
      <td><?=$proveedor?></td>
    </tr>
    <tr>
      <th>Factura:</th>
      <td><?=$folio?></td>
    </tr>
    <tr>
    	<th>Almac&eacute;n</th>
      <td><?=$almacen?></td>
    </tr>
   </table>
   <br />
  <input name="id_almacen" type="hidden" value="<?=$id_almacen?>" />
  <input name="compra" type="hidden" value="<?=$_GET[compra]?>" />
  <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla lista" style="text-align:center">
    <tr>
      <th>C&oacute;digo Barras</th>
      <th>Descripci&oacute;n</th>
      <th>Lote</th>
      <th>Cantidad</th>
      <th>% IVA</th>
      <th>Sub-Imp U.</th>
      <th>Importe U.</th>
      <th>&nbsp;</th>
      <th>Cant</th>
    </tr>
    <?php
$i=0;
while($r = mysql_fetch_assoc($productos_q)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
  $cantidad = $r[cantidad];
  if(isset($devueltos[$r[id_producto]])){
    if($r[lote] == $devueltos[$r[id_producto]][lote]){
      $cantidad = $r[cantidad]-$devueltos[$r[id_producto]][cantidad];
    }
  }
  if($cantidad > 0){
?>
    <tr class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
      <td>
	      <?=$r[codigo_barras]?>
      </td>
      <td><b><?=$r[descripcion]?></b></td>
      <td style="text-align:center"><?=$r[lote]?></td>
      <td style="text-align:center"><?=$cantidad?></td>
      <td style="text-align:right"><?=money($r[iva])?></td>
      <td style="text-align:right"><?=money($r[sub_importe]/$r[cantidad])?></td>
      <td style="text-align:right"><?=money($r[importe]/$r[cantidad])?></td>
      <td style="text-align:center"><input name="activate" type="checkbox" id="checkbox<?=$i?>" onclick="activar('<?=$i?>');" /></td>
      <td style="text-align:center">
	      <input name="id_producto[]" id="id_producto<?=$i?>" type="hidden" value="<?=$r[id_producto]?>" disabled="disabled"/>
        <input name="lote[]" id="lote<?=$i?>" type="hidden" value="<?=$r[lote]?>" disabled="disabled"/>
        <input name="cantidad_x[]" id="cantidad_x<?=$i?>" type="hidden" value="<?=$cantidad?>"  disabled="disabled"/>
        <input name="precio[]" id="precio<?=$i?>" type="hidden" value="<?=round($r[importe]/$r[cantidad],2)?>" disabled="disabled" />
      	<input
        name="cantidad[]"
        type="text"
        value="<?=$cantidad?>"
        size="3"
        id="cantidad<?=$i?>"
        onblur="numero(this,0); checar_cantidad(this,'<?=$i?>'); checar_existencia(this,'<?=$r[existencia]?>','<?=$i?>');"
        disabled="disabled" />
      </td>
    </tr>
    <?php
    $i++;
  }
}
?>
  </table>
    <br />
    <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla" style="text-align:center">
      <tr>
        <th style="text-align:right"><b>Total:</b></th>
        <td>
          <input type="hidden" value="0" name="importe" id="importe" />
        <b>$ <span id="total">0.00</span></b></td>
      </tr>
    </table>
    <br />
    <input name="submit" type="submit" id="submit" value="Registrar Devoluci&oacute;n" />
</form>
</center>
<script language="javascript" type="text/javascript">
	var elementos = document.getElementsByName("precio[]");
	if(elementos.length==0){
		document.forms.form1.innerHTML = "<b>No hay productos para devolver en esta factura.</b>";
	}
</script>