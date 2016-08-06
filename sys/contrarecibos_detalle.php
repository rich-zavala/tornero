<?php
if(!isset($_GET[section])){
	header("Content-type: text/html; charset=iso-8859-1");
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
} 
$s = "SELECT
cotizaciones.folio,
cotizaciones.id_facturista,
cotizaciones.fecha,
cotizaciones.cliente,
cotizaciones.datos_cliente,
cotizaciones.importe,
cotizaciones.status,
moneda,
usuarios.nombre vendedor
FROM
cotizaciones
LEFT JOIN usuarios ON usuarios.id_usuario = cotizaciones.id_facturista
WHERE folio = '{$_GET[folio]}'";
$q = mysql_query($s) or die (mysql_error());
$r = mysql_fetch_assoc($q);
$id_cliente = $r[id_cliente];
$importe = $r[importe];
$tipo = $r[tipo];
$status = $r[status];
$moneda = ($r[moneda] == 1) ? "MN" : "USD";

//Inicia configuración
titleset("Detalles de la Cotizaci&oacute;n \\\"{$_GET[folio]}\\\"");
//Fin de configuración
?>
<center>
    <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla">
      <tr>
        <th>Status</th>
        <td><?php if($r[status]==0) echo "Normal"; else echo "<b><font color=\"#E60000\">Cancelada</font></b>";;?></td>
      </tr>
      <tr>
        <th>Folio</th>
        <td><?=$_GET[folio]?></td>
      </tr>
      <tr>
        <th>Fecha</th>
        <td><?=FormatoFecha($r[fecha])?></td>
      </tr>
      <tr>
        <th>Moneda</th>
        <td><?=$r[moneda]?></td>
      </tr>
      <tr>
        <th>Cliente</th>
        <td><?=$r[cliente]?></td>
      </tr>
      <tr>
        <th>Datos del Cliente</th>
        <td><pre style="margin:0px; padding:0px"><?=$r[datos_cliente]?></pre></td>
      </tr>
      <tr>
      	<th>Vendedor</th>
        <td><?=$r[vendedor]?></td>
      </tr>
    </table>
    <br />
    <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
      <tr>
        <th>C&oacute;digo Barras</th>
        <th>Descripci&oacute;n</th>
        <th>Cantidad</th>
        <th>Precio</th>
        <th>% IVA</th>
        <th>Importe</th>
      </tr>
  <?php
	$i=0;
	$s = "SELECT
		cotizaciones_productos.cantidad,
		cotizaciones_productos.precio,
		cotizaciones_productos.iva,
		cotizaciones_productos.importe,
		IF(especial='0',productos.codigo_barras,'-') 'codigo_barras',
		IFNULL(productos.descripcion,cotizaciones_productos.especial) 'descripcion',
		IFNULL(cotizaciones_productos.complemento,NULL) 'complemento'
		FROM
		cotizaciones_productos
		LEFT JOIN productos ON cotizaciones_productos.id_producto = productos.id_producto
		WHERE folio_cotizacion = '{$_GET[folio]}'
		GROUP BY cotizaciones_productos.id_cotizacionproducto";
	$q = mysql_query($s) or die (mysql_error());
	while($r = mysql_fetch_assoc($q)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
	$sub_total += round($r[cantidad]*$r[precio],2);
	$iva += round($r[cantidad]*$r[precio]*($r[iva]/100),2);
	?>
  <tr class="<?=$class?>"
  onMouseOver="this.setAttribute('class', 'tr_list_over');"
  onMouseOut="this.setAttribute('class', '<?=$class?>');"
  >
    <td><?=$r[codigo_barras]?></td>
    <td style="white-space:normal">
		<?php
      echo htmlentities($r[descripcion]);
			if($r[complemento] != "0")
			{
				echo "<br /><i style='color:#1F7D9F; font-size:11px;'>".nl2br(htmlentities($r[complemento]))."</i>";
			}
		?>
    </td>
    <td style="text-align:center"><?=$r[cantidad]?></td>
    <td style="text-align:right"><?=$r[precio]?></td>
    <td style="text-align:right"><?=money($r[iva])?></td>
    <td style="text-align:right"><?=money($r[cantidad]*$r[precio])?></td>
  </tr>
  <?php
	}
	?>
    </table>
      <br>
      <?php	
      //$iva  = redon($iva,2);
 ?>
<table border="0" align="center" cellpadding="6" cellspacing="0" class="bordear_tabla">
      <tr>
        <th>Sub-Total</th>
        <td style="text-align:right"><?=money($sub_total)?></td>
      </tr>
      <tr style="display:none;">
        <th>Descuento</th>
        <td style="text-align:right"><?=money($descuento)?></td>
      </tr>
      <tr>
        <th>IVA</th>
        <td style="text-align:right"><?=money($iva)?></td>
      </tr>
      <?php if(isset($devolucion)){ ?>
      <tr>
        <th>Devoluciones</th>
        <td style="text-align:right"><?=money($devolucion)?></td>
      </tr>
      <?php } ?>
      <tr>
        <th>Total <?=$moneda?></th>
        <td style="text-align:right"><?=money($importe)?></td>
      </tr>
    </table>
<?php
// Evitar imprimir llamando desde AJAX
if(isset($_GET[section]) && (Administrador() || ComprasVentas() || Ventas())){
	if($status == "0"){
?>
<div style="margin-top:10px;">
<a href="?section=ventas_formulario&cotizacion=<?=$_GET[folio]?>&moneda=<?=$moneda?>">
<img src="imagenes/transform.png" style="margin-bottom:-6px" />&nbsp;&nbsp;<b>Convertir Cotizaci&oacute;n a Venta</b>
</a>
</div>
<?php }
if($status == 0)
{
?>
<div style="margin-top:10px;">

<a href="cotizaciones_pdf.php?folio=<?=$_GET[folio]?>" target="_blank">
<img src="imagenes/print-icon_over.gif" style="margin-bottom:-8px" />&nbsp;&nbsp;<b>Imprimir</b>
</a>

</div>
<?php }} ?>
<br />