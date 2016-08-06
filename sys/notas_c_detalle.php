<?php
$s = "SELECT
clientes.*,
ingresos.id id,
ingresos.importe,
ingresos.banco,
ingresos.tipo,
ingresos.referencia,
ingresos.fecha,
notas_de_credito.status,
notas_de_credito.reviso,
notas_de_credito.autorizo,
notas_de_credito.id 'id_nota'
FROM
notas_de_credito
INNER JOIN ingresos ON notas_de_credito.id_mov = ingresos.id
LEFT JOIN clientes ON clientes.clave = notas_de_credito.persona
WHERE
folio =  '{$_GET['folio']}'
AND notas_de_credito.tipo = 'cliente'";
$q = mysql_query($s) or die (mysql_error());
$r = mysql_fetch_assoc($q);
$total = $r[importe];
//Inicia configuración
titleset("Detalles de Nota de Cr&eacute;dito \\\"{$_GET[folio]}\\\"");
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
<center>
<span id="detalles_contenido">
<table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla" style="text-align:center">
    <?php if($r[status] == "1"){ ?>
    <tr>
      <th colspan="2" style="background-color:#F00; font-weight:bold; color:#FFF"><center>
          Nota de Cr&eacute;dito  Cancelada
      </center></th>
    </tr>
    <?php } ?>
   <tr>
    <th>Cliente</th>
    <td><b><?=$r[nombre]?></b></td>
  </tr>
   <tr>
    <th>Folio</th>
    <td><b><?=$_GET[folio]?></b></td>
  </tr>
  <tr>
    <th>Importe</th>
    <td><?=money($r[importe])?></td>
  </tr>
  <tr>
    <th>Fecha</th>
    <td><?=FormatoFecha($r[fecha])?></td>
  </tr>
  <tr>
    <th>Revis&oacute;</th>
    <td><?=nl2br($r[reviso])?></td>
  </tr>
  <tr>
    <th>Autoriz&oacute;</th>
    <td><?=nl2br($r[autorizo])?></td>
  </tr>
  <tr>
    <th><a href="javascript:ajax_this('ventas_detalle.php?folio=<?=$r[factura]?>')">
      <?=$r[factura]?>
    </a>Detalles</th>
    <td>
    <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
  <tr>
    <th>Factura</th>
    <th>Descripci&oacute;n</th>
    <th>Importe</th>
  </tr>
<?php
$sql = "SELECT * FROM notas_de_credito_detalle WHERE nota = '{$r['id_nota']}'";
$query = mysql_query($sql) or die (mysql_error());
while ($r = mysql_fetch_assoc($query)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
?>
  <tr id="tr_<?=$r['id']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td><a href="javascript:ajax_this('ventas_detalle.php?folio=<?=$r[folio]?>&serie=<?=$r[serie]?>&v=1')"><?=$r[folio]?></a></td>
    <td style="white-space: normal;"><?=nl2br($r[descripcion])?></td>
    <td style="text-align:right"><?=money($r[importe])?></td>
  </tr>
<?php } ?>
  <tr>
  	<th colspan="2" style="text-align:right">Total: </th>
    <th style="text-align:right"><?=money($total)?></th>
  </tr>
</table>
</td></tr></table>
</span>
<br />
<a href="notas_c_pdf.php?folio=<?=$_GET[folio]?>" target="_blank">
<img src="imagenes/print-icon_over.gif" style="margin-bottom:-8px" />&nbsp;&nbsp;<b>Imprimir</b>
</a>
</center>
<br />