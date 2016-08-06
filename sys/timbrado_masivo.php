<?php
$db = dbconnect();
$s = "SELECT * FROM facturas f
			LEFT JOIN clientes c ON c.clave = f.id_cliente
			WHERE fecha_captura >= (SELECT fecha_inicio_cfdi FROM vars) AND f.status = 0 AND folio NOT LIKE '%NOTA%'";
$facturas = $db->fetch($s);
titleset("Timbrado masivo");

if(count($facturas) > 0)
{
?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
		<th>Serie-Folio</th>
		<th>Fecha</th>
		<th>Cliente</th>
		<th>Importe</th>
		<th></th>
	</tr>
<?php
	$i = 0;
	foreach($facturas as $r)
	{
		$class = "tr_list_1";
		if($i%2 == 0) $class = "tr_list_0";
		$i++;
?>
	<tr class="<?=$class?>">
		<td><?=$r[serie]?>-<?=$r[folio]?></td>
		<td><?=FormatoFecha($r[fecha_factura])?></td>
		<td><?php if($r[id_cliente] == "0"){ echo "Público"; } else { echo $r[nombre]; }?></td>
		<td style="text-align:right"><?=money($r[importe])?><?=mon($r[moneda])?></td>
		<td class="tdCheck" style="text-align: center"><input type="checkbox" class="checkFactura" data-folio="<?=$r[folio]?>" data-serie="<?=$r[serie]?>"></td>
	</tr>
	<?php
	}
	?>
</table>
<div class='vinculos_cfdi_contenedor'><b><center><button id="timbrar">Timbrar facturas seleccionadas</button><span id="wait">Espere mientras se env&iacute;a la informaci&oacute;n...</span></center></b></div>
<script src="../advans/jsTimbradoMasivo.js" language="javascript" charset="UTF-8"></script>
<?
}
else
{
	echo "<div class='vinculos_cfdi_contenedor'><b><center>No hay facturas pendientes por timbrar.</center></b></div>";
}
?>