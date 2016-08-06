<?php
$s = "SELECT
pedidos.id,
pedidos.folio,
pedidos.almacen,
pedidos.id_producto,
pedidos.cantidad,
pedidos.costo,
pedidos.iva,
pedidos.sub_importe,
pedidos.importe,
pedidos.obtenidos,
pedidos.compra,
moneda,
pedidos.status,
pedidos.comentario,
pedidos.complemento,
pedidos.tipo,
fecha,
proveedores.nombre 'proveedor',
productos.codigo_barras,
IFNULL(productos.descripcion,pedidos.especial) 'descripcion',
almacenes.descripcion 'almacen_desc'
FROM
pedidos
INNER JOIN proveedores ON pedidos.proveedor = proveedores.clave
INNER JOIN almacenes ON pedidos.almacen = almacenes.id_almacen
LEFT JOIN productos ON productos.id_producto = pedidos.id_producto
WHERE pedidos.folio = '{$_GET['id']}'";
$q = mysql_query($s) or die (mysql_error());
$q1 = mysql_query($s) or die (mysql_error());

$resultado = mysql_fetch_assoc($q1);
$proveedor = $resultado['proveedor'];
$almacen_desc = $resultado['almacen_desc'];
$fecha = $resultado['fecha'];
$estado = $resultado['status'];
$comentario = $resultado['comentario'];
$moneda = (@$r[moneda] == "M.N.") ? "MN" : "USD";

//Inicia configuración
titleset("Detalles del Pedido \\\"{$_GET[id]}\\\"");
//Fin de configuración
?>
<script type="text/javascript"language="javascript">
function mostrar(id){
	document.getElementById("span"+id).style.display = "";
	document.getElementById("td"+id).style.display = "none";
}
function ocultar(id){
	document.getElementById("span"+id).style.display = "none";
	document.getElementById("td"+id).style.display = "";
}

</script>

<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla">
  <tr>
    <th>Folio</th>
    <td><?=$_GET['id']?></td>
    <th>Proveedor</th>
    <td><?=$proveedor?></td>
  </tr>
  <tr>
    <th>Almac&eacute;n</th>
    <td><?=$almacen_desc?></td>
    <th>Fecha</th>
    <td><?=FormatoFecha($fecha)?></td>
  </tr>
  <tr>
    <th>Status</th>
    <?php if($estado=="0"){ ?>
    <td>Normal</td>
    <?php } else{ ?>
    <td><b><font color="#E60000">Cancelado</font></b></td>
    <?php } ?>
    <th>Tipo</th>
    <td><?php if( $resultado[tipo] == "p" || $resultado[tipo] == "" ){ echo "Pedido"; }else{ echo "Cotizaci&oacute;n"; } ?></td>
  </tr>
  <tr>
  	<th>Moneda</th>
  	<td><?=$resultado[moneda]?></td>
    <th >Comentarios</th>
    <td style="white-space:normal;"><?=nl2br($comentario)?></td>
  </tr>
</table>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" style="margin-top:10px">
  <tr>
    <th>C&oacute;digo Barras</th>
    <th>Descripci&oacute;n</th>
    <th>Cantidad</th>
    <th>Costo U.</th>
    <th>% IVA</th>
    <th>Sub-Importe</th>
    <th>Importe</th>
    <th>Obtenidos</th>
    <th class="header_com">&nbsp;</th>
  </tr>
  <?php
$x=0;
while($r = mysql_fetch_assoc($q)){
	if($x%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$x++;
	$total = $total + $r['importe'];
?>
  <tr id="tr_<?=$r['id']?>" class="<?=$class?>"
  	onMouseOver="this.setAttribute('class', 'tr_list_over'); document.getElementById('span<?=$r['id']?>').setAttribute('class', 'tr_list_over');"
    onMouseOut="this.setAttribute('class', '<?=$class?>'); document.getElementById('span<?=$r['id']?>').setAttribute('class', '<?=$class?>');"
  >
    <td><?=$r['codigo_barras']?></td>
    <td style="white-space:normal"><b>
		<?php
			//print_pre($r);
      echo htmlentities($r[descripcion]);
			if($r[complemento] != "0")
			{
				echo "<br /><i style='color:#1F7D9F; font-size:11px;'>".nl2br(htmlentities($r[complemento]))."</i>";
			}
		?>
    </b></td>
    <td style="text-align:right"><?=$r['cantidad']?></td>
    <td style="text-align:right"><?=money($r['costo'])?></td>
    <td style="text-align:right"><?=number_format($r['iva'],2)?></td>
    <td style="text-align:right"><?=money($r['sub_importe'])?></td>
    <td style="text-align:right"><?=money($r['importe'])?></td>
    <td style="text-align:center"><?=$r['obtenidos']?></td>
    <td>&nbsp;<span id="td<?=$r['id']?>">
      <?php if(strlen($r['compra'])>0){ ?>
      <img src="imagenes/search.png" class="manita" onclick="mostrar('<?=$r['id']?>');"/>
      <?php } ?>
      </span>&nbsp;</td>
  </tr>
  <tr style="display:none" id="span<?=$r['id']?>" class="<?=$class?>"
  onMouseOver="this.setAttribute('class', 'tr_list_over'); document.getElementById('tr_<?=$r['id']?>').setAttribute('class', 'tr_list_over');"
  onMouseOut="this.setAttribute('class', '<?=$class?>');  document.getElementById('tr_<?=$r['id']?>').setAttribute('class', '<?=$class?>'); "
  >
    <td colspan="9">
	    <div style="padding:0px; margin:0px 6px 0px 0px; width:16px; float:right;" class="manita" onclick="ocultar('<?=$r['id']?>');"><img src="imagenes/deleteX.png"/></div>
      <center>
        <span style="font-weight:bold">Costos reales de las unidades obtenidas</span>
      </center>
      <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
        <tr>
          <th>Compra</th>
          <th>Fecha</th>
          <th>Lote</th>
          <th>Cantidad</th>
          <th>Costo U.</th>
          <th>Importe</th>
        </tr>
        <?php
						$compras_string = explode(",",$r['compra']);
						foreach($compras_string AS $c){
							if(strlen($c)){
								$compras_explode = explode("|",$c);
								$compra[] = $compras_explode[0];
								$cantidad[] = $compras_explode[1];
							}
						}
						for($i=0; count($compra)>$i; $i++){
						if($i%2 == 0) $class2 = "tr_list_0"; else $class2 = "tr_list_1";
						?>
        <?php
                $compra_s = "SELECT
								folio_factura,
                DATE_FORMAT(fecha_factura,'%d-%m-%Y')'fecha',
                compras_detalle.costo,
                compras_detalle.lote,
                compras.fecha_factura
                FROM
                compras
                LEFT JOIN compras_detalle ON compras_detalle.id_compra = compras.id
                WHERE id_compra = '{$compra[$i]}'
                AND id_producto = '{$r['id_producto']}'";
                $compra_q = mysql_query($compra_s) or die (mysql_error());
                $compra_r = mysql_fetch_assoc($compra_q);
              ?>
        <tr id="tr_lista2_<?=$i?>" class="<?=$class2?>">
          <td style="text-align:center"><?=$compra_r[folio_factura]?></td>
          <td><?=FormatoFecha($compra_r['fecha'])?></td>
          <td style="text-align:center"><?=$compra_r['lote']?></td>
          <td style="text-align:right"><?=$cantidad[$i]?></td>
          <td style="text-align:right"><?=money($compra_r['costo'])?></td>
          <td style="text-align:right"><?=money($cantidad[$i]*$compra_r['costo'])?></td>
        </tr>
        <?php
							$total_producto += $cantidad[$i]*$compra_r['costo_uni'];
              }
							unset($detalle);
							unset($compras_explode);
							unset($compra);
							unset($cantidad);
							?>
        <tr bgcolor="#CCCCCC">
        <td colspan="5" style="text-align:right"><b>Total:</b></td>
        <td style="text-align:right"><b>
          <?=money($total_producto)?>
        </b></td>
        </tr>
      </table>
      </td>
  </tr>
<?php
	unset($total_producto);
}
?>
</table>
<table width="0" border="0" align="center" cellpadding="4" cellspacing="0" style="margin-top:10px"  class="bordear_tabla">
  <tr>
    <th class="header_com"><b>Total <?=$moneda?></b></th>
    <td><b>$
      <?=money($total)?>
      </b></td>
  </tr>
</table>
<center>
<div style="margin:6px;">
<a href="comuni-k.php?section=compras_formulario&pedido=<?=$_GET['id']?>&moneda=<?=$moneda?>">
<img src="iconos-mini/compras.png" style="margin-bottom:-8px" />&nbsp;&nbsp;<b>Registrar compra de éste pedido</b>
</a>
</div>
<div  style="margin-bottom:6px;">
<a href="pedidos_print.php?folio=<?=$_GET['id']?>" target="_blank">
<img src="imagenes/print-icon_over.gif" style="margin-bottom:-8px" />&nbsp;&nbsp;<b>Imprimir</b>
</a>
</div>
</center>