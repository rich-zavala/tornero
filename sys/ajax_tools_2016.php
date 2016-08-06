<?php
header('Content-Type: application/json');
include("funciones/basedatos.php");
include("funciones/funciones.php");
$db->execute("SET NAMES 'utf8'");

//Búsqueda de clientes
if(isset($_GET['clientes']))
{
	$c = mysql_real_escape_string($_GET['clientes']);
	$s = "SELECT * FROM clientes_pais WHERE clave = '{$c}' OR nombre LIKE '%{$c}%' LIMIT 10";
	echo json_encode($db->fetch($s));
}

//Buscador de productos
if(isset($_GET['producto']))
{
	/*
	26 nov 2015 > No valida almacén. Si no existe pone 1
	*/
	if(!isset($_GET['almacen'])) $_GET['almacen'] = 1;
	
	$s_productos = "SELECT p.id_producto, codigo_barras codigo, descripcion, precio_publico precio, iva,
									IF( cantidad IS NULL,
										'[{ \"lote\": 1, \"cantidad\": 0 }]',
										GROUP_CONCAT(CONCAT('[{ \"lote\": ', '\"', lote,'\", \"cantidad\": ', cantidad, '}]'))
									) dispo
									FROM productos p
									LEFT JOIN existencias e ON e.id_producto = p.id_producto AND e.id_almacen = %s
									WHERE %s AND status = 0
									GROUP BY p.id_producto, lote ORDER BY codigo, descripcion LIMIT 50";
	//Por código
	if(isset($_GET['producto_codigo']))
	{
		$c = mysql_real_escape_string($_GET['producto_codigo']);
		$s = sprintf($s_productos, $_GET['almacen'], "codigo_barras = '{$c}'");
	}
	else if($_GET['producto_string']) //Búsqueda general
	{
		$_GET['producto_string'] = trim(str_replace('"', '', $_GET['producto_string']));
		$_GET['producto_string'] = trim(str_replace('\\', '', $_GET['producto_string']));
		$c = mysql_real_escape_string($_GET['producto_string']);
		$s = sprintf($s_productos, $_GET['almacen'], "(codigo_barras LIKE '%{$c}%' OR descripcion LIKE '%{$c}%')");
	}

	// echo $s;
	$r = $db->fetch($s);
	foreach($r as $k => $d) $r[$k]['dispo'] = json_decode($d['dispo']);
	echo json_encode($r);
}
?>