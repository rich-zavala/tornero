<?php
if(!isset($_GET[section]))
{
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
}
else
{
	//Inicia configuración
	titleset("Detalles del retiro de La Casa del Tornero [{$_GET[id]}]");
	//Fin de configuración
}

$s = "SELECT * FROM `comuni-k_tornero`.retiros_view
			WHERE id = {$_GET[id]}";
$q = mysql_query($s) or die (mysql_error());
$r = mysql_fetch_assoc($q);
?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" style="margin-top:16px">
	<tr>
		<th>Orden de trabajo</th>
		<td><?=(strlen(trim($r[orden])) > 0) ? nl2br($r[orden]) : 'No definido'?></td>
	</tr>
</table>
<br/>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla">
    <tr>
      <th>Folio</th>
      <td><?=$_GET[id]?></td>
      <th>Fecha</th>
      <td><?=FormatoFecha($r[fecha])." ".FormatoHora($r[fecha])?></td>
    </tr>
    <tr>
      <th>Usuario</th>
      <td><?=$r[nombre]?></td>
      <th>Status</th>
      <td><?php if($r[status]==0){echo "Normal"; } else {echo "<b><font color=\"#E60000\">Cancelado</font></b>";} ?></td>
    </tr>
  </table>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="tabla_productos" style="margin-top:16px">
  <tr>
  	<th>Código Barras</th>
    <th>Descripción</th>
    <th>Lote</th>
    <th>Cantidad</th>
  </tr>
<?php
$s = "SELECT * FROM `comuni-k_tornero`.retiros_productos_view
			WHERE retiro = {$_GET[id]}";
$q = mysql_query($s) or die (mysql_error());
while($r = mysql_fetch_assoc($q))
{
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1"; $i++;
?>
  <tr id="tr_<?=$r['id_traspaso']?>" class="<?=$class?>" onmouseover="this.setAttribute('class', 'tr_list_over');" onmouseout="this.setAttribute('class', '<?=$class?>');" >
  	<td><?=$r[codigo_barras]?></td>
    <td><?=$r[descripcion]?></td>
    <td style="text-align:center"><?=$r[lote]?></td>
    <td style="text-align:center"><?=$r[cantidad]?></td>
  </tr>
<?php
}
?>
</table>