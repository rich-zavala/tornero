<?
if(isset($_GET[folio])){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	
	$s = "SELECT id FROM devoluciones_clientes WHERE id = '{$_GET[folio]}'";
	$q = mysql_query($s);
	echo mysql_num_rows($q);
	exit();
}
if(isset($_POST[submit])){
  $sql = "INSERT INTO devoluciones_clientes VALUES ('{$_POST[folio]}', NOW(), '{$_POST[venta]}', '{$_POST[importe]}','{$_POST[ser]}', 0)";
  mysql_query($sql) or die ($sql."-".mysql_error());
  for($i=0; count($_POST[id_producto])>$i; $i++){
		if($_POST[cantidad][$i]>0){
			$sql0 = "INSERT INTO devoluciones_clientes_detalles VALUES(NULL,'{$_POST[folio]}','{$_POST[id_producto][$i]}','{$_POST[lote][$i]}',{$_POST[cantidad][$i]},{$_POST[precio][$i]})";
			mysql_query($sql0) or die ($sql0."-".mysql_error());
			//Verificar que el registro de existencias está vigente
			$s = "SELECT id_existencia FROM existencias WHERE id_producto = '{$_POST[id_producto][$i]}' AND lote = '{$_POST[lote][$i]}' AND id_almacen = '{$_POST[id_almacen]}'";
			$q = mysql_query($s) or die (mysql_error());
			if(mysql_num_rows($q)>0){
				$r = mysql_fetch_assoc($q);
				$s = "UPDATE existencias SET cantidad=cantidad+{$_POST[cantidad][$i]} WHERE id_existencia = {$r[id_existencia]}";
			} else {
				$s = "INSERT INTO existencias VALUES (NULL,{$_POST[id_almacen]},{$_POST[id_producto][$i]},{$_POST[cantidad][$i]},'{$_POST[lote][$i]}')";
			}
			mysql_query($s);
			// Para movimientos
			
			$s_mov = "INSERT INTO movimientos
			VALUES(
			null,
			'{$_POST[id_almacen]}',
			'{$_POST[id_almacen]}',
			'15',
			'{$_POST[id_producto][$i]}',
			'{$_POST[cantidad][$i]}',
			'{$_POST[lote][$i]}',
			NOW(),
			'{$_SESSION[id_usuario]}',
			'{$_POST[folio]}',
			'{$_POST[ser]}'
			)";
			mysql_query($s_mov) or die (nl2br($s_mov)."<p>".mysql_error());
		}
  }
	if($_POST[ser] == "")
	{
	  $serie= " AND serie IS NULL";
	}
	else
	{
		$serie= " AND serie='{$_POST[ser]}'";
	}
	
  $sql3 = "UPDATE facturas SET importe=importe-{$_POST[importe]} WHERE folio='{$_POST[venta]}' {$serie}";
  mysql_query($sql3) or die (mysql_error());
  echo	$_POST[venta];
  relocation("?section=devoluciones_c_detalle&id={$_POST[folio]}");
	exit();
}

$strSQL = "SELECT
f.folio,
f.serie,
fp.lote,
fp.iva,
fp.importe,
fp.cantidad,
fp.precio,
fp.descuento,
fp.id_producto,
pr.codigo_barras,
pr.descripcion,
almacenes.descripcion 'almacen',
f.id_almacen,
clientes.nombre
FROM
facturas f
INNER JOIN facturas_productos fp ON fp.folio_factura = f.folio
INNER JOIN productos AS pr ON fp.id_producto = pr.id_producto
INNER JOIN almacenes ON f.id_almacen = almacenes.id_almacen
INNER JOIN clientes ON f.id_cliente = clientes.clave
WHERE folio = '{$_GET[venta]}'";
//echo nl2br($strSQL);exit();
$query_1 = mysql_query($strSQL) or die (nl2br($strSQL).mysql_error());

$data = mysql_fetch_assoc($query_1);
$id_almacen = $data[id_almacen];
$almacen = $data[almacen];
$cliente = $data[nombre];

$productos_q = mysql_query($strSQL) or die (mysql_error());

//Productos YA DEVUELTOS
$devs = "SELECT
dcd.*
FROM
devoluciones_clientes dc
INNER JOIN devoluciones_clientes_detalles dcd ON dc.id = dcd.id_devolucion
WHERE
dc.factura = '{$_GET[venta]}'
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
titleset("Devolver la Venta \\\"{$_GET[venta]}\\\"");
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
		alert("La cantidad a devolver no puede ser mayor a la cantidad entregada con la factura.");
		obj.value = document.getElementById("cantidad_x"+num).value;
		obj.focus();
		obj.select();
    sumatoria();
		return false;
	}
	else
	{
    obj.value = parseFloat(obj.value).toFixed(3);
		sumatoria();
	}
}
function cambiarcolor(row)
{
	document.getElementById("row"+row).style.backgroundColor = "#FEEE96";
}
function cambiarcolor2(row)
{
	document.getElementById("row"+row).style.backgroundColor = "#FFFFFF";
}
function sumatoria()
{
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
	span.innerHTML = money(total);
  importe.value = total.toFixed(2);
}

function verificar_folio(){
	var folio = document.getElementById("folio");
	var url = "devoluciones_c_formulario.php?folio="+folio.value;
	var r = procesar(url);
	if(parseFloat(r)>0){
		alert("El folio \""+folio.value+"\" ya ha sido utilizado.\nIntente otro diferente.");
		folio.value = "";
		folio.focus();
		return false;
	} else {
		return true;
	}
}

function hay_algo(){
	if(document.getElementById("folio").value.length == 0){
		alert("Defina el folio de esta devolución.");
		return false;
	} else {
		return verificar_folio();
	}
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
      <th>Cliente:</th>
      <td><?=$cliente?></td>
    </tr>
    <tr>
      <th>Factura:</th>
      <td><?=$_GET[venta]?></td>
    </tr>
    <tr>
    	<th>Almac&eacute;n</th>
      <td><?=$almacen?></td>
    </tr>
    <tr>
    	<th>Folio:</th>
      <td><input name="folio" type="text" size="8" id="folio" onblur="return verificar_folio();" /></td>
    </tr>
   </table>
   <br />
  <input name="id_almacen" type="hidden" value="<?=$id_almacen?>" />
  <input name="venta" type="hidden" value="<?=$_GET[venta]?>" />
   <input name="ser" type="hidden" value="<?=$_GET[ser]?>" />
  <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla lista" style="text-align:center">
    <tr>
      <th>C&oacute;digo Barras</th>
      <th>Descripci&oacute;n</th>
      <th>Lote</th>
      <th>Cantidad</th>
      <th>Precio</th>
      <th>% IVA</th>
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
    <tr class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');" <?php if($r[tipo] == 1){ ?> style="color:#888" <?php } ?>>
      <td>
	      <?=$r[codigo_barras]?>
      </td>
      <td><b><?=nl2br(htmlentities($r[descripcion]))?></b></td>
      <td style="text-align:center"><?=$r[lote]?></td>
      <td style="text-align:center"><?=$cantidad?></td>
      <td style="text-align:center"><?=money($r[precio])?></td>
      <td style="text-align:right"><?=money($r[iva])?></td>
      <td style="text-align:center"><input name="activate" type="checkbox" id="checkbox<?=$i?>" onclick="activar('<?=$i?>');" <?php if($r[tipo] == 1){ ?> disabled="disabled" <?php } ?> /></td>
      <td style="text-align:center">
	      <input name="id_producto[]" id="id_producto<?=$i?>" type="hidden" value="<?=$r[id_producto]?>" disabled="disabled"/>
        <input name="lote[]" id="lote<?=$i?>" type="hidden" value="<?=$r[lote]?>" disabled="disabled"/>
        <input name="cantidad_x[]" id="cantidad_x<?=$i?>" type="hidden" value="<?=$cantidad?>"  disabled="disabled"/>
        <input name="precio[]" id="precio<?=$i?>" type="hidden" value="<?=$r[importe]/$r[cantidad]?>" disabled="disabled" />
      	<input
        name="cantidad[]"
        type="text"
        value="<?=number_format($cantidad,3,".","")?>"
        size="6"
        id="cantidad<?=$i?>"
        onblur="numero(this,3); checar_cantidad(this,'<?=$i?>');"
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