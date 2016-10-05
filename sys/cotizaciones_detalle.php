<?php
if(!isset($_GET[section])){
	header("Content-type: text/html; charset=iso-8859-1");
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
} 
$s = "SELECT
c.folio,
c.id_facturista,
c.fecha,
c.cliente,
c.datos_cliente,
c.importe,
c.status,
moneda,
u.nombre vendedor
FROM
cotizaciones c
LEFT JOIN usuarios u ON u.id_usuario = c.id_facturista
WHERE folio = '{$_GET[folio]}'";
$q = mysql_query($s) or die (mysql_error());
$r = mysql_fetch_assoc($q);
$id_cliente = $r[id_cliente];
$importe = $r[importe];
$tipo = $r[tipo];
$status = $r[status];
$moneda = ($r[moneda] == "M.N.") ? "MN" : "USD";

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
		
    <table border="1" align="center" cellpadding="5" cellspacing="0" style="border-collapse:collapse;">
      <tr bgcolor="#BF2D2D" style="color: white">
        <th>C&oacute;digo Barras</th>
        <th>Descripci&oacute;n</th>
        <th>Unidad</th>
        <th>Cantidad</th>
        <th>Precio</th>
        <th>% IVA</th>
        <th>Importe</th>
      </tr>
  <?php
	$s = "SELECT
		c.cantidad,
		c.unidad,
		c.precio,
		c.iva,
		c.importe,
		IF(especial='0',p.codigo_barras,'-') 'codigo_barras',
		IFNULL(p.descripcion,c.especial) 'descripcion',
		IFNULL(c.complemento,NULL) 'complemento'
		FROM
		cotizaciones_productos c
		LEFT JOIN productos p ON c.id_producto = p.id_producto
		WHERE folio_cotizacion = '{$_GET[folio]}'
		GROUP BY c.id_cotizacionproducto";
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
    <td style="text-align:center"><?=$r['unidad']?></td>
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
<table cellpadding="10" style="margin-top:10px;">
	<tr>
		<td>
			<a href="?section=ventas_formulario&cotizacion=<?=$_GET[folio]?>&moneda=<?=$moneda?>">
				<img src="imagenes/transform.png" style="margin-bottom:-6px" />&nbsp;&nbsp;<b>Convertir a venta</b>
			</a>
		</td>
		<td>
			<a href="ventas_formulario_2016.php?cotizacion=<?=$_GET['folio']?>">
				<img src="imagenes/transform.png" style="margin-bottom:-6px" />&nbsp;&nbsp;<b>Convertir a venta v2</b>
			</a>
		</td>
		<td>
			<a href="envio_mail.php?enviar=<?=$_GET[folio]?>" class="vinculo_cfdi"
					 onclick="return hs.htmlExpand(this,{
						forceAjaxReload: true,
						objectType: 'iframe',
						headingText: 'Enviar Cotizaci&oacute; \'<?=$_GET[folio]?>\'  por correo electrónico',
						wrapperClassName:'draggable-header no-footer',
						width:660,
						height:560,
						preserveContent: false
						} )">
				<img src="imagenes/shownote.png" style="margin-bottom:-6px" />&nbsp;&nbsp;<b>Enviar por e-mail</b>
			</a>
		</td>
	</tr>
</table>
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