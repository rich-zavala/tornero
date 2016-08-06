<?php
$strSQL = "SELECT
dc.*,
dc.status AS 'status',
dcd.*,
f.folio,
f.serie,
almacenes.descripcion AS 'almacen',
productos.descripcion,
productos.codigo_barras AS 'barras',
clientes.nombre
FROM
devoluciones_clientes AS dc
INNER JOIN devoluciones_clientes_detalles AS dcd ON dc.id = dcd.id_devolucion
INNER JOIN facturas f ON dc.factura = f.folio
INNER JOIN almacenes ON f.id_almacen = almacenes.id_almacen
INNER JOIN productos ON dcd.id_producto = productos.id_producto
INNER JOIN clientes ON f.id_cliente = clientes.clave
WHERE dc.id = '{$_GET[id]}'";
//echo nl2br($strSQL);
$query_1 = mysql_query($strSQL) or die (mysql_error());
$x = mysql_fetch_assoc($query_1);
$productos_q = mysql_query($strSQL) or die (mysql_error());

//Inicia configuración
titleset("Detalles de la Devoluci&oacute;n \\\"{$_GET[id]}\\\"");
//Fin de configuración
?>
<center>
  <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla lista" style="text-align:center">
    <?php if($x[status] == "1"){ ?>
    <tr>
      <th colspan="2" style="background-color:#F00; font-weight:bold; color:#FFF"><center>
          Devoluci&oacute;n Cancelada
        </center></th>
    </tr>
    <?php } ?>
    <tr>
      <th>Cliente:</th>
      <td><?=$x[nombre]?></td>
    </tr>
    <tr>
      <th>Factura:</th>
      <td><?=$x[folio]?></td>
    </tr>
    <tr>
      <th>Almac&eacute;n</th>
      <td><?=$x[almacen]?></td>
    </tr>
  </table>
  <br>
  <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla lista" style="text-align:center">
    <tr>
      <th>C&oacute;digo Barras</th>
      <th>Descripci&oacute;n</th>
      <th>Lote</th>
      <th>Costo U.</th>
      <th>Cantidad</th>
      <th>Importe</th>
    </tr>
    <?php
$i=0;
while($r = mysql_fetch_assoc($productos_q)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$importe = intval($r[cantidad])*$r[precio];
	$importe_unitario = $r[precio];
	$importe_final += $importe;
?>
    <tr class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
      <td><?=$r[barras]?></td>
      <td><?=$r[descripcion]?></td>
      <td style="text-align:center"><?=$r[lote]?></td>
      <td style="text-align:right"><?=money($importe_unitario)?></td>
      <td style="text-align:center"><?=$r[cantidad]?></td>
      <td style="text-align:right"><?=money($importe)?></td>
    </tr>
    <?php
$i++;
}
?>
  </table>
  <br>
  <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla" style="text-align:center">
    <tr>
      <th style="text-align:right"><b>Total:</b></th>
      <td><b>$ <?=money($importe_final)?></td>
    </tr>
  </table>
</center>
<script type="text/javascript">
	var str = procesar("tool.php?str="+parent.parent.location);
	var f = str.search("devoluciones_p_detalle");
	if(f == -1){
		document.getElementById("link").style.display="none";
	}
</script>
