<?php 
include("funciones/basedatos.php");
include("funciones/funciones.php");

//print_pre($_GET);
/*
En Comuni-K, el precio de los productos puede tomarse de la lista de precios, o del precio público.
Esto se logra saber con $_GET['precios'].
Si es "_normal_", el precio se toma del precio público.
De lo contrario, se busca en la tabla de "precios_particular"
*/

$sql = "SELECT
				p.*
				FROM productos p
				WHERE 1
				{$si_hay_cliente}
				AND status = 0
				AND (p.descripcion LIKE '%{$_GET['producto']}%' OR p.codigo_barras LIKE '%{$_GET['producto']}%')
				GROUP BY p.id_producto
				ORDER BY descripcion";

if(isset($_GET[venta]))
{
	$sql = "SELECT p.*, SUM(e.cantidad) ex FROM productos p
					INNER JOIN existencias e ON p.id_producto = e.id_producto
					WHERE id_almacen = {$_GET[almacen]} AND cantidad > 0
					AND (p.descripcion LIKE '%{$_GET['producto']}%' OR p.codigo_barras LIKE '%{$_GET['producto']}%')
					GROUP BY p.id_producto
					ORDER BY descripcion";
}

// echo nl2br($sql);
$mysql = mysql_query($sql) or die (mysql_error());
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" href="css/body.css">
<script language="JavaScript" type="text/javascript" src="js/funciones.js"></script>
<script language="javascript" type="text/javascript" src="js/ajax.js"></script>
<script language="javascript" type="text/javascript">
function submitenter(myfield,e){
	var keycode;
	if(window.event){
		keycode = window.event.keyCode;
	} else if(e){
		keycode = e.which;
	} else {
		return true;
	}
	if(keycode == 13){
		go();
	} else {
		return true;
	}
}
<?php
//Para selector de productos en formulario grande
if(isset($_GET[n])){
?>
function elegir(id_producto){
	top.window.resetear(<?=$_GET[n]?>);
	top.window.document.getElementById("id_producto<?=$_GET[n]?>").value = id_producto;
	parent.buscar_datos_pop(<?=$_GET[n]?>);
	if(top.window.document.getElementById("barras<?=$_GET[n]?>").value.length>0){
		parent.window.hs.close();
	} else {
		top.window.document.getElementById("id_producto<?=$_GET[n]?>").value = "";
	}
}
<?php
}

//Para selector de producto en formulario chico. Sólo necesitamos el nombre y el id_producto
//Con variable "$_GET[field]" se define el campo receptor en la ventana padre
if(isset($_GET[field])){
?>
function elegir(id_producto){
	var url = "ajax_tools.php?ajax_producto="+id_producto+"&field=descripcion&ident=id_producto";
	var r = procesar(url);
	top.window.document.getElementById("<?=$_GET[field]?>").value = r;
	top.window.document.getElementById("id_producto").value = id_producto;
	parent.window.hs.close();
}
<?php } ?>
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
<center>
  <b>Escriba el nombre de producto o el código de barras</b>.
</center>
<form method="get" action="" name="form1" id="form1">
<table border="0" align="center" cellpadding="4" cellspacing="0">
  <tr>
    <td><input name="producto" type="text" id="producto" style="font-size:11px" size="34" onKeyPress="return submitenter(this,event)" value="<?=$_GET['producto']?>"/></td>
    <td><img src="imagenes/search_button.gif" class="manita" onclick="go();"/></td>
  </tr>
</table>
<?php if(isset($_GET[n])){ ?>
<input value="<?=$_GET[n]?>" name="n" type="hidden" />
<?php } if(isset($_GET[field])){ ?>
<input value="<?=$_GET[field]?>" name="field" type="hidden" />
<?php } if(isset($_GET[venta])){ ?>
<input value="<?=$_GET[venta]?>" name="venta" type="hidden" />
<input value="<?=$_GET[almacen]?>" name="almacen" type="hidden" />
<?php } ?>
</form>
<?php
  if($_GET['producto'] != "" && mysql_num_rows($mysql) >0){
?>
<p>
</p>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
  <tr>
    <th>C&oacute;digo de Barras</th>
    <th>Descripci&oacute;n</th>
    <?php if(isset($_GET[venta])){ ?><th>Disponible</th><?php } ?>
  </tr>
  <?php
	$i=0;
	while($r = mysql_fetch_assoc($mysql)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
	?>
  <tr onclick="elegir('<?=$r['id_producto']?>');" onmouseover="this.setAttribute('class', 'tr_list_over manita');" onmouseout="this.setAttribute('class', '<?=$class?>');" class="<?=$class?> manita">
    <td style="width:1%; white-space:normal;"><?=$r['codigo_barras']?></td>
    <td style="whidth:99%; white-space:normal;"><?=$r['descripcion']?></td>
    <?php if(isset($_GET[venta])){ ?><td style="text-align:right"><?=$r[ex]?></td><?php } ?>
  </tr>
<?php
}
?>
</table>
<?php
}elseif(isset($_GET['producto'])){
?>
<center><b><p>Búsqueda sin resultados.</p></b></center>
<?php } ?>
</body>
</html>