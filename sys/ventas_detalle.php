<?php
if(!isset($_GET[section]))
{
	header("Content-type: text/html; charset=iso-8859-1");
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
}
if($_GET[serie]== "")
{
  $serie= " AND (serie IS NULL OR serie = 'HAS')";
}
else
{
	$serie= " AND serie='{$_GET[serie]}'";
}

$s = "SELECT
f.*,
c.nombre,
a.descripcion,
u.nombre vendedor
FROM
facturas f
LEFT JOIN clientes c ON c.clave = f.id_cliente
INNER JOIN usuarios u ON u.id_usuario = f.id_facturista
INNER JOIN almacenes a ON a.id_almacen = f.id_almacen
WHERE folio = '{$_GET[folio]}' {$serie} LIMIT 1";
// echo nl2br($s);
$q = mysql_query($s) or die (mysql_error());
$r = mysql_fetch_assoc($q);
// print_pre($r);
$id_cliente = $r[id_cliente];
$importe = $r[importe];
$tipo = $r[tipo];
$status = $r[status];
$almacenes= $r[id_almacen];
$moneda = ($r[moneda] == "M.N.") ? "MN" : "USD";

//Inicia configuración
titleset("Detalles de la Venta &quot;{$_GET[folio]}&quot;");
//Fin de configuración
?>
<script src="../advans/jsTimbrar.js?v=<?=rand()?>" language="javascript"></script>
<center>
    <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla">
      <tr>
        <th>Status:</th>
        <td><?php if($r[status] == 0){ echo "Normal"; }else if($r[status] == 2){ echo "<b>Timbrada</b>"; }else{ echo "<b><font color=\"#E60000\">Cancelada</font></b>"; }?></td>
        <th>Tipo de Documento</th>
        <td ><?php if($tipo == "n"){echo "Nota";} else {echo "Factura";} ?></td>
      </tr>
      <tr>
        <th>Folio</th>
        <td><?=$_GET[folio]?></td>
        <th>Serie</th>
        <td><?=$_GET[serie]?></td>
      </tr>
      <tr>
        <th>Fecha</th>
        <td><?=FormatoFecha($r[fecha_factura])?></td>
        <th>Moneda</th>
        <td><?=$r[moneda]?></td>
      </tr>
      <tr>
        <th>Almac&eacute;n</th>
        <td><?=$r[descripcion]?></td>
        <th>Precios</th>
        <td><?php if($r[licitacion] == "_normal_"){echo "Normal";} else {echo $r[licitacion];}?></td>
      </tr>
			<?php if($tipo == "f"){ ?>
      <tr>
        <th>M&eacute;todo de pago</th>
        <td><?=$r[metodoDePago]?></td>
        <th>No. cta de pago</th>
        <td><?=$r[NumCtaPago]?></td>
      </tr>
			<?php } ?>
      <?php if($tipo == "f"){ ?>
      <tr>
        <th>Leyenda</th>
        <td style="white-space:normal" colspan="3"><?=nl2br($r[leyenda])?></td>
      </tr>
      <tr>
        <th>Cliente</th>
        <td colspan="3"><?=$r[nombre]?></td>
      </tr>
      <tr>
        <th>Datos del Cliente</th>
        <?php
        $cliente_data = explode("|",$r[datos_cliente]);
				if(strlen($cliente_data[3]) > 0 ){ $cliente_data[3] = " No. ".$cliente_data[3];}
				if(strlen($cliente_data[4]) > 0 ){ $cliente_data[4] = " Int. ".$cliente_data[4];}
				if(strlen($cliente_data[5]) > 0 ){ $cliente_data[5] = "Col./Fracc.: ".$cliente_data[5];}
				$datos .= "CALLE: ".$cliente_data[2].$cliente_data[3].$cliente_data[4]."\n";	
				$datos .= $cliente_data[5]." CP: ".$cliente_data[10]."\n";
				$datos .= $cliente_data[6].", ".$cliente_data[7].", ".$cliente_data[8].", ".$cliente_data[9]."\n";
				$datos .= "RFC: ".$cliente_data[0];
				?>
        <td colspan="3"><?=nl2br($datos)?></td>
      </tr>
      <?php } ?>
      <tr>
      	<th>Vendedor</th>
        <td colspan="3"><?=$r[vendedor]?></td>
      </tr>
			<?php
			$fInfo = $r;
			if($fInfo['recargo_id'] > 0){
			?>
      <tr>
      	<th><?=($fInfo['recargo_porcentaje'] > 0) ? 'Recargo' : 'Descuento'?></th>
        <td colspan="3"><?=$r['recargo_concepto']?> <b>(<?=$r['recargo_porcentaje']?>%)</b></td>
      </tr>
			<?php } ?>
    </table>
    <br />
    <table border="1" align="center" cellpadding="5" cellspacing="0" style="border-collapse:collapse;">
      <tr bgcolor="#BF2D2D" style="color: white">
        <th>C&oacute;digo</th>
        <th>Descripci&oacute;n</th>
        <th>Lote</th>
        <th>Cant.<br>Almacén</th>
        <th>Cant.<br>Factura</th>
        <th>Precio</th>
        <?php if($fInfo['recargo_id'] != ''){ ?>
				<th><?=($fInfo['recargo_porcentaje'] > 0) ? 'Recargo' : 'Descuento'?></th>
        <th>Precio<br>final</th>
				<?php } ?>
        <th>% IVA</th>
        <th>Total</th>
        <!--<th>Sub-Total</th>-->
      </tr>
  <?php
	$i=0;
	$s = "SELECT
		fp.cantidad,
		fp.canti_,
		fp.lote,
		fp.precio,
		fp.descuento,
		fp.iva,
		fp.importe,
		productos.id_producto,
		IF(especial='0' OR especial = '',productos.codigo_barras,'-') 'codigo_barras',
		IFNULL(productos.descripcion,fp.especial) 'descripcion',
		IFNULL(fp.complemento,NULL) 'complemento'#,
		#SUM(devoluciones_clientes_detalles.cantidad) 'devueltos',
		#devoluciones_clientes_detalles.id_devolucion 'devolucion'
		FROM
		facturas_productos fp
		LEFT JOIN productos ON fp.id_producto = productos.id_producto
		#LEFT JOIN devoluciones_clientes ON devoluciones_clientes.factura = '{$_GET[folio]}'
		/*LEFT JOIN devoluciones_clientes_detalles
			ON devoluciones_clientes_detalles.id_producto = fp.id_producto
			AND devoluciones_clientes_detalles.lote = fp.lote
			AND devoluciones_clientes_detalles.id_devolucion = devoluciones_clientes.id*/
		WHERE folio_factura = '{$_GET[folio]}' {$serie}
		GROUP BY fp.id_facturaproducto";
// echo $s;		
	$q = mysql_query($s) or die (mysql_error() . "<hr>" . $s);
	$ids_= array();
	while($r = mysql_fetch_assoc($q))
	{
		$ids_[] = $r['id_producto'];
		if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
		$i++;
		$cantidad = $r['canti_'];
	
		$sub_total_producto = $cantidad * $r['precio'];
		$sub_total += $sub_total_producto;
		
		//Recargo por producto individual
		$recargo_producto = $sub_total_producto * ($fInfo['recargo_porcentaje'] / 100);
		$recargo_producto_unit = $r['precio'] * ($fInfo['recargo_porcentaje'] / 100);
		
		$sub_total += $recargo_producto;
		$iva += ($sub_total_producto + $recargo_producto) * ($r['iva'] / 100);
		
		$total = (($r['precio'] + round($recargo_producto_unit, 3)) + (($r['precio'] + round($recargo_producto_unit, 3)) * ($r['iva'] / 100))) * $cantidad;
	?>
  <tr class="<?=$class?>"
  onMouseOver="this.setAttribute('class', 'tr_list_over');"
  onMouseOut="this.setAttribute('class', '<?=$class?>');"
  >
    <td><?=$r['codigo_barras']?></td>
    <td style="white-space:normal">
		<?php
			echo $r['descripcion'];
			if($r['complemento'] != "0" or $r['complemento'] != '')
				echo "<br /><i style='color:#1F7D9F; font-size:11px;'>" . nl2br(htmlentities($r['complemento']))."</i>";
		?>
    </td>
    <td style="text-align:center"><?=htmlentities($r['lote'])?></td>
    <td style="text-align:right"><?=$r['cantidad']?></td>
    <td style="text-align:right"><?=$r['canti_']?></td>
    <td style="text-align:right"><?=money($r['precio'])?></td>
    <?php if($fInfo['recargo_id'] != ''){ ?>
		<td style="text-align:right"><?=number_format($recargo_producto_unit, 3)?></td>
    <td style="text-align:right"><?=money($r['precio'] + round($recargo_producto_unit, 3))?></td>
		<?php } ?>
    <td style="text-align:right"><?=money($r['iva'])?></td>
    <td style="text-align:right"><?=money($total)?></td>
   <!-- <td style="text-align:right"><?=money($r['cantidad'] * $r['precio'])?></td>-->
  </tr>
  <?php
	}
	?>
    </table>
    <br>
		<table border="0" align="center" cellpadding="6" cellspacing="0" class="bordear_tabla">
      <tr>
        <th>Sub-Total</th>
        <td style="text-align:right"><?=money(round($sub_total,2))?></td>
      </tr>
			<!--<?php if($fInfo['recargo_id'] != null){ ?>
      <tr>
        <th><?=($fInfo['recargo_porcentaje'] > 0) ? 'Recargo' : 'Descuento'?></th>
        <td style="text-align:right"><?=money($fInfo['recargo_importe'])?></td>
      </tr>
			<?php } ?>-->
      <tr>
        <th>IVA</th>
        <td style="text-align:right"><?=money(round($iva,2))?></td>
      </tr>
      <tr>
        <th>Total <?=$moneda?></th>
        <td style="text-align:right"><?=money($importe)?></td>
      </tr>
    </table>
<?php
// Evitar imprimir llamando desde AJAX
if(isset($_GET[section]) && (Administrador() || ComprasVentas() || Ventas()))
{
	if($tipo == "n" && $status == "0")
	{
?>
<div style="margin-top:40px;">
	<a href="?section=venta_transform&folio=<?=$_GET[folio]?>&cliente=<?=$id_cliente?>">
		<img src="imagenes/transform.png" style="margin-bottom:-6px" />&nbsp;&nbsp;<b>Convertir Nota a Factura</b>
	</a>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="ventas_pdfnota.php?folio=<?=$_GET[folio]?>" target="_blank">
		<img src="imagenes/print-icon_over.gif" style="margin-bottom:-8px" />&nbsp;&nbsp;<b>Imprimir</b>
	</a>
</div>

<?php
	}
	
	if($tipo == "f")
	{
?>
<!--<div style="margin-top:10px;">
	<a href="ventas_pdf2.php?folio=<?=$_GET[folio]?>" target="_blank">
		<img src="imagenes/print-icon_over.gif" style="margin-bottom:-8px" />&nbsp;&nbsp;<b>Imprimir</b>
	</a>
</div>-->
<div class="vinculos_cfdi_contenedor">
<?php
/*
Ricardo 9 de Diciembre 2013
Comparar la fecha de esta factura con el parámetro de inicio de CFDI
*/
$s = "SELECT IF( (SELECT fecha_factura FROM facturas WHERE folio = '{$_GET[folio]}' {$serie} LIMIT 1) >= (SELECT fecha_inicio_cfdi FROM vars), 1,0 ) cfdi";
$q = mysql_query($s);
$r = mysql_fetch_assoc($q);
$cfdi = (int)$r['cfdi'];
if($cfdi > 0){ //Debe timbrarse
	//Verificar si está timbrada o no
	$s = "SELECT status FROM facturas WHERE folio = '{$_GET[folio]}' {$serie} LIMIT 1";	
	$q = mysql_query($s);
	$r = mysql_fetch_assoc($q);
	$status = (int)$r['status'];
	if($status == 0){ //No está timbrada
?>
	<a href="#" class="_timbrar_ vinculo_cfdi" data-folio="<?=$_GET[folio]?>" data-serie="<?=$_GET[serie]?>">Timbrar esta factura</a>
<?php
	}else if($status == 1){ //Está cancelada
?>
	<a href="#" class="vinculo_cfdi">Descargar XML de constancia de cancelación</a>
<?php
	}else{ $estaTimbrada = true; //Está timbrada y vigente
?>
	<a href="../advans/get_file.php?tipo=xml&db=<?=DB_NAME?>&folio=<?=$_GET[folio]?>&serie=<?=$_GET[serie]?>" class="vinculo_cfdi" target="_myxml">Descargar XML</a>
	<a href="../advans/get_file.php?tipo=pdf&db=<?=DB_NAME?>&folio=<?=$_GET[folio]?>&serie=<?=$_GET[serie]?>" class="vinculo_cfdi" target="_mypdf">Descargar PDF</a>	
	<a href="envio_mail.php?enviar=<?=$_GET[folio]?>&tipo=2&serie=<?=$_GET[serie]?>&cfdi" class="vinculo_cfdi"
					 onclick="return hs.htmlExpand(this,{
						forceAjaxReload: true,
						objectType: 'iframe',
						headingText: 'Enviar Factura Electr&oacute;nica \'<?=$_GET[folio]?>\'  por correo electrónico',
						wrapperClassName:'draggable-header no-footer',
						width:660,
						height:560,
						preserveContent: false
						} )">Enviar por e-mail
	</a>
	
<?php
	}
}else{
?>
	<table cellpadding="4">
		<tr>
			<td>
				<a href='ventas_create_files.php?folio=<?=$_GET[folio]?>&serie=<?=$_GET[serie]?>&infor=0&sinSello' target='_blank'>
					<img src="imagenes/print-icon_over.gif" style="margin-bottom:-8px" />&nbsp;&nbsp;<b>Factura[<?=$_GET[folio]?>].pdf</b>
				</a>
			</td>
			<td>
				<a href="envio_mail.php?enviar=<?=$_GET[folio]?>&tipo=2&serie=<?=$_GET[serie]?>"
								 onclick="return hs.htmlExpand(this,{
									forceAjaxReload: true,
									objectType: 'iframe',
									headingText: 'Enviar Factura Electr&oacute;nica \'<?=$_GET[folio]?>\'  por correo electrónico',
									wrapperClassName:'draggable-header no-footer',
									width:660,
									height:560,
									preserveContent: false
									} )">
					<img src="imagenes/mail_attach.png" style="margin-bottom:-8px" />
					&nbsp;&nbsp;
					<b>Enviar por e-mail</b>
				</a>
			</td>
		</tr>
	</table>
</div>
<?php
		}
	}
}

if($estaTimbrada)
{
?>
</div>
<div>
<center id="reseteo" style="font-size: 10px; font-weight: bold;"><a href="../advans/reset.php?tipo=xml&db=<?=DB_NAME?>&folio=<?=$_GET[folio]?>&serie=<?=$_GET[serie]?>">¿No se visualiza el PDF?</a></center>

<?php } ?>
<script>
$('#reseteo A').click(function(){ $(this).parent().html('Solicitando información. Por favor, espere...') });
</script>