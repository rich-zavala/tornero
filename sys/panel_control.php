<?php
titleset("Elige una opci&oacute;n");
/*if ($_SESSION['id_tipousuario'] == 1 || $_SESSION['id_tipousuario'] == 3 || $_SESSION['id_tipousuario'] == 4) {
    $query_productos = "SELECT p.id_producto, p.precio_publico, m.pmv  FROM productos p INNER JOIN precio_minimo m on p.id_producto = m.id_producto WHERE p.precio_publico < m.pmv";
    $productos = mysql_query($query_productos) or die(mysql_error());
    $row_productos = mysql_num_rows($productos);
    if ($row_productos > 0) {
        $x = 1;
    }
}*/
$usuario = "{$_SESSION['nombres']} {$_SESSION['apellidop']} {$_SESSION['apellidom']}";
$tipo_s = "SELECT descripcion FROM tipos_usuarios WHERE id_tipousuario = '{$_SESSION['id_tipousuario']}'";
$tipo_q = mysql_query($tipo_s) or die(mysql_error());
$tipo_r = mysql_fetch_assoc($tipo_q);
$tipo_usuario = $tipo_r['descripcion'];
if (Administrador())
{
    /*$alarm_query = mysql_query("SELECT y FROM alarm") or die(mysql_error());
    $alarm = mysql_fetch_assoc($alarm_query);
    if ($x == 1 && $alarm['y'] == 1)
		{
			if ($_SESSION['id_tipousuario'] == 1)
			{
				$query_productos = "SELECT
														descripcion 'productos',
														p.id_producto,
														p.codigo_barras,
														p.precio_publico,
														m.pmv
														FROM productos p
														INNER JOIN precio_minimo m on p.id_producto = m.id_producto
														WHERE p.precio_publico < m.pmv
														ORDER BY codigo_barras ASC";
				$productos = mysql_query($query_productos) or die(mysql_error());
				//-------------------------------------------------------------------------------------------
				if (mysql_num_rows($productos) > 0) {
				echo "if(confirm('Existen productos con precio al publico incorrectos.
							¿Desea Actualizarlos?')){document.location = '?section=corrige_precios';}";
				}
				}
				if ($_SESSION['id_tipousuario'] == 3 || $_SESSION['id_tipousuario'] == 4) {
				echo "alert('Existen productos con precio al publico incorrectos.
							Contacte al administrador.');";
				}
    }*/
?>
<script language="javascript" type="text/javascript">
/*function alarm(x){
	var alarm_box = document.getElementById("alarm");
	if (x == 1){
		alarm_box.checked = !alarm_box.checked;
	}
	if(alarm_box.checked){
		var url = "alarm.php?x=1";
	} else {
		var url = "alarm.php?x=0";
	}
	var r = procesar(url);
}*/
</script>
<?php
}
include ("KREATOR.php");
?>
<?php
if (Administrador()) {
?>
<!--<div style="clear:both; padding:20px;">
<center>
  <input type="checkbox" name="alarm" id="alarm" onclick="alarm();" <?php if ($alarm['y'] == 1) {
        echo "checked";
    } ?> />
  <b>Alarma de precios m&iacute;nimos<br />
  <a href="?section=corrige_precios">Verificar</a></b><br />
</center>
</div>-->
<?php
}
?>
<br>
<br>