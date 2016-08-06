<?php
$ingresos_sql = "SELECT
ingresos.importe,
ingresos.banco,
ingresos.tipo,
ingresos.referencia,
ingresos.fecha
FROM
ingresos
WHERE
ingresos.id =  '{$_GET['id']}'";
$ingresos_query = mysql_query($ingresos_sql);
$ingresos_result = mysql_fetch_assoc($ingresos_query);

$banco_sql = "SELECT nombre FROM bancos WHERE id = '{$ingresos_result['banco']}'";
$banco_query = mysql_query($banco_sql) or die(mysql_error());
$banco_result = mysql_fetch_assoc($banco_query);

if ($ingresos_result['tipo'] == "cheque"){
    $referencia = 'No. del Cheque';
}else {
   $referencia = 'Referencia';
}

//Inicia configuración
titleset("Detalles de Ingreso \\\"{$_GET[id]}\\\"");
//Fin de configuración
?>
<script language="javascript" type="text/javascript">
function ajax_this(url){
	document.getElementById("detalles_contenido").style.display = "none";
	document.getElementById("ajax_this").innerHTML = procesar(url);
	document.getElementById("ajax_content").style.display = "";
}

function cerrar_ajax(){
	document.getElementById("detalles_contenido").style.display = "";
	document.getElementById("ajax_this").innerHTML = "";
	document.getElementById("ajax_content").style.display = "none";
}
</script>
<div id="ajax_content" style="background-color:#224887; padding:10px; display:none;">
	<div style="background-color:#FFF; text-align:center; padding:10px">
  	<a href="javascript: cerrar_ajax();" style="text-decoration:none">
    	<img src="imagenes/update.png" style="margin:0px 6px -3px 0px;" /><b>Regresar a los detalles de la Nota de Cr&eacute;dito</b>
    </a>
  </div>
  <div id="ajax_this" style="background-color:#FFF; padding:10px"></div>
</div>
<span id="detalles_contenido">
<?php
if($ingresos_result['referencia'] == "PAGO DE CONTADO"){
?>
<table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla" align="center">
    <?php if($r[status] == "1"){ ?>
    <tr>
      <th colspan="2" style="background-color:#F00; font-weight:bold; color:#FFF"><center>Egreso Cancelado</center></th>
    </tr>
    <?php } ?>
   <tr>
    <th colspan="2"><center>Pago de Contado</center></th>
  </tr>
  <tr>
    <th>Fecha</th>
    <td><?=FormatoFecha($ingresos_result['fecha'])?></td>
  </tr>
  <tr>
    <td colspan="2" align="center" style="text-align:center">
    <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
  <tr>
    <th>Factura</th>
    <th>Importe</th>
  </tr>
<?php
$sql = "SELECT * FROM ingresos_detalle WHERE id_ingreso = '{$_GET['id']}'";
$query = mysql_query($sql) or die (mysql_error());
while ($r = mysql_fetch_assoc($query)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
?>
  <tr id="tr_<?=$r['id']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td><a href="javascript: ajax_this('ventas_detalle.php?folio=<?=$r[factura]?>')"><?=$r[factura]?></a></td>
    <td style="text-align:right"><?=money($r[abono])?></td>
  </tr>
<?php } ?>
</table>
</td></tr></table>
<?php
} else if($ingresos_result[tipo] == "desde_caja"){ //Es una transferencia CAJA > BANCO
?>
  <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla" id="formulario">
    <?php if($ingresos_result[status] == "1"){ ?>
    <tr>
      <th colspan="2" style="background-color:#F00; font-weight:bold; color:#FFF"><center>
           Cancelado
        </center></th>
    </tr>
    <?php } ?>
    <tr>
      <th>Fecha</th>
      <td><?=FormatoFecha($ingresos_result[fecha])?></td>
    </tr>
    <tr>
      <th>Importe</th>
      <td><?=money($ingresos_result[importe])?></td>
    </tr>
    <tr>
      <th>Referencia</th>
      <td><?=$ingresos_result[referencia]?></td>
    </tr>
  </table>
<br />
<center>Este es el registro de una transferencia entre <b>Caja</b> y un banco.</center>
<?php
} else {
?>
<table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla" align="center">
    <?php if($r[status] == "1"){ ?>
    <tr>
      <th colspan="2" style="background-color:#F00; font-weight:bold; color:#FFF"><center>Egreso Cancelado</center></th>
    </tr>
    <?php } ?>
   <tr>
    <th>Banco</th>
    <td ><?=$banco_result['nombre']?></td>
  </tr>
  <tr>
    <th>Importe</th>
    <td><?=money($ingresos_result[importe])?></td>
  </tr>
  <?php if($banco_result[nombre]!="Efectivo"){ ?>
  <tr>
    <th>Tipo de pago recibido</th>
    <td><?=ucwords($ingresos_result['tipo'])?></td>
  </tr>
  <tr>
    <th>Referencia Bancaria</th>
    <td><b><?=$referencia?></b><br />
      <?=$ingresos_result['referencia']?></td>
  </tr>
  <?php } ?>
  <tr>
    <th>Fecha</th>
    <td><?=FormatoFecha($ingresos_result['fecha'])?></td>
  </tr>
  <tr>
    <th>Detalles</th>
    <td>
    <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
  <tr>
    <th>Factura</th>
    <th>Importe</th>
  </tr>
<?php
$sql = "SELECT * FROM ingresos_detalle WHERE id_ingreso = '{$_GET['id']}'";
$query = mysql_query($sql) or die (mysql_error());
while ($r = mysql_fetch_assoc($query)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
?>
  <tr id="tr_<?=$r['id']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td><a href="javascript: ajax_this('ventas_detalle.php?folio=<?=$r[factura]?>&serie=<?=$r[serie_factura]?>&v=1')"><?=$r[factura]?></a></td>
    <td style="text-align:right"><?=money($r[abono])?></td>
  </tr>
<?php } ?>
</table>
</td></tr></table>
<?php } ?>
</span>