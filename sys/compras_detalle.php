<?php  
if(!isset($_GET[section])){
	header("Content-type: text/html; charset=iso-8859-1");
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
}

$s = "SELECT
almacenes.descripcion,
proveedores.nombre,
compras.fecha_factura,
compras.fecha_captura,
compras.dias_credito,
compras.`status`,
compras.folio_factura,
moneda
FROM
compras
INNER JOIN almacenes ON almacenes.id_almacen = compras.id_almacen
INNER JOIN proveedores ON compras.id_proveedor = proveedores.clave
WHERE
compras.id = '{$_GET[id]}'";
$q = mysql_query($s);
$r = mysql_fetch_assoc($q);
$moneda = ($r[moneda] == 1) ? "MN" : "USD";
//Inicia configuración
titleset("Detalles de la Compra \\\"{$r[folio_factura]}\\\"");
//Fin de configuración
?>
  <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla" id="formulario">
    <tr>
      <th>Status</th>
      <td><?php if($r[status]==0) echo "Normal"; else echo "<b><font color=\"#E60000\">Cancelada</font></b>";;?></td>
    </tr>
    <tr>
      <th>Proveedor</th>
      <td><?=htmlentities($r[nombre])?></td>
    </tr>
    <tr>
      <th>Folio</th>
      <td><?=$r[folio_factura]?></td>
    </tr>
    <tr>
      <th>Almac&eacute;n</th>
      <td><?=htmlentities($r[descripcion])?></td>
    </tr>
      <tr>
        <th>Moneda</th>
        <td><?=$r[moneda]?></td>
      </tr>
    <tr>
      <th>Fecha de Factura</th>
      <td><?=FormatoFecha($r[fecha_factura])?></td>
    </tr>
    <tr >
      <th>Fecha de Captura</th>
      <td><?=FormatoFecha($r[fecha_captura])?></td>
    </tr>
    <tr>
      <th>D&iacute;as de Cr&eacute;dito</th>
      <td><?=$r[dias_credito]?></td>
    </tr>
  </table>
  <br>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
     <tr>
      <th>C&oacute;digo Barras</th>
      <th>Descripci&oacute;n</th>
      <th>Lote</th>
      <th>Cantidad</th>
      <th>Costo U.</th>
      <th>% IVA</th>
      <th>Sub-Importe</th>
      <th>Importe</th>
    </tr>
  <?php
	$i=0;
	$s = "SELECT * FROM compras_detalle INNER JOIN productos ON compras_detalle.id_producto = productos.id_producto WHERE id_compra = {$_GET[id]}";
	$q = mysql_query($s) or die (mysql_error());
	while($r = mysql_fetch_assoc($q)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
	$sub_total += $r[sub_importe];
	$iva += $r[importe]-$r[sub_importe];
	$total += $r[importe];
	?>
  <tr class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td><?=$r[codigo_barras]?></td>
    <td><?=htmlentities($r[descripcion])?></td>
    <td style="text-align:center"><?=htmlentities($r[lote])?></td>
    <td style="text-align:right"><?=$r[cantidad]?></td>
    <td style="text-align:right"><?=money($r[costo])?></td>
    <td style="text-align:right"><?=money($r[iva])?></td>
    <td style="text-align:right"><?=money($r[sub_importe])?></td>
    <td style="text-align:right"><?=money($r[importe])?></td>
  </tr>
  <?php
	}
	?>
	</table>
<br>
  <table border="0" align="center" cellpadding="6" cellspacing="0" class="bordear_tabla">
    <tr>
      <th>Sub-Total</th>
      <td style="text-align:right"><b>$ <?=money($sub_total)?></b></td>
    </tr>
    <tr>
      <th>IVA</th>
      <td style="text-align:right"><b>$ <?=money($iva)?></b></td>
    </tr>
    <tr>
      <th>Total <?=$moneda?></th>
      <td style="text-align:right"><b>$ <?=money($total)?></b></td>
    </tr>
  </table>