<?php
header("Content-type: text/html; charset=iso-8859-1");
session_start();
include("funciones/basedatos.php");
include("funciones/funciones.php");
Conectar();

//Indexar el estado del menú POP
if(isset($_GET[pop])){
	$_SESSION[$_GET[pop]."_menu"] = $_GET[val];
	exit();
}

//Indexar el estado del DISPLAY de filtros
if(isset($_GET[filtro_listado])){
	$_SESSION[$_GET[filtro_listado]."_filter"] = $_GET[val];
	exit();
}

//Búsqueda de productos desde formulario
if(isset($_GET[ajax_producto])){
	$data = "";
	$sql = "SELECT {$_GET[field]} FROM productos WHERE {$_GET[ident]} = '{$_GET[ajax_producto]}' AND status = 0 GROUP BY id_producto";
	//echo $sql;
	$query = mysql_query($sql);
	if(mysql_num_rows($query)>0){
		while($r = mysql_fetch_assoc($query)){
			$data .= "~".$r[$_GET[field]];
		}
		echo substr($data,1,strlen($data));
	} else {
		echo "NULO";
	}
}

//Búsqueda de lotes desde formulario
if(isset($_GET[lotes])){
	$data = array();
	$sql = "SELECT lote,cantidad FROM existencias WHERE id_producto='{$_GET[lotes]}' AND id_almacen='{$_GET[ad1]}' GROUP BY lote";
	//echo $sql;
	$query = mysql_query($sql);
	if(mysql_num_rows($query)>0){
		while($r = mysql_fetch_assoc($query)){
			$data[] = "{$r[lote]}:::{$r[cantidad]}";
		}
		echo implode("|||",$data);
	} else {
		echo "NULO";
	}
	exit();
}

//Eliminación de lotes en ceros
if(isset($_GET[eliminar_lotes])){
  if(mysql_query("DELETE FROM existencias WHERE cantidad <= 0")){
	  echo "Los registros de existencias en 0 han sido eliminados.";
	} else {
		echo "Ha ocurrido un error y las existencias no fueron actualizadas.\\nIntente de nuevo más tarde.";
	}
	exit();
}

if(isset($_GET[pedido])){
	$s = "SELECT folio FROM pedidos WHERE folio = {$_GET[pedido]} GROUP BY folio";
	$q = mysql_query($s) or die (mysql_error());
	echo mysql_num_rows($q);
}

if(isset($_GET[folio_cotizacion])){
	$s = "SELECT folio FROM cotizaciones WHERE folio = '{$_GET[folio_cotizacion]}' GROUP BY folio";
	$q = mysql_query($s) or die (mysql_error());
	echo mysql_num_rows($q);
}

//Datos de cliente desde Ventas
if(isset($_GET[cliente_data])){
	$s = "SELECT clientes.*, pais_nombre FROM clientes INNER JOIN paises ON clientes.pais = paises.id WHERE clave = '{$_GET[cliente_data]}'";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
echo "{$r[direccion]}
{$r[municipio]}, {$r[estado]}. {$r[pais_nombre]}.
RFC: {$r[RFC]}";
	exit();
}

//datos del cleinte con factura electronica
//Datos de cliente desde Ventas
if(isset($_GET[cliente_data2])){
	$s = "SELECT clientes.*, pais_nombre FROM clientes INNER JOIN paises ON clientes.pais = paises.id WHERE clave = '{$_GET[cliente_data2]}'";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
//este cambio se hace para el menejo de la info en la factura electronica
echo "{$r[RFC]}|{$r[nombre]}|{$r[calle]}|{$r[noexterior]}|{$r[nointerior]}|{$r[colonia]}|{$r[localidad]}|{$r[municipio]}|{$r[estado]}|{$r[pais_nombre]}|{$r[cp]}|{$r[NumCtaPago]}";
	exit();
}

//Verificar folio Existente
if(isset($_GET[folio_venta])){
	if($_GET[tipo] == "0"){ //Nota de Venta
		$s = "SELECT folio FROM facturas WHERE folio = 'Nota {$_GET[folio_venta]}'";
	} else { //Factura
	  $s = "SELECT serie FROM facturas WHERE folio = '{$_GET[folio_venta]}'";
		$q = mysql_query($s);
		$r= mysql_fetch_assoc($q);
		if($r[serie] == "")
		{
		  $serie= " AND serie IS NULL";
		}
		else
		{
			$serie= " AND serie='{$_GET[serie]}'";
		}
	
		$s = "SELECT folio FROM facturas WHERE folio = '{$_GET[folio_venta]}'{$serie}";
	}
	$q = mysql_query($s);
	echo mysql_num_rows($q);
	exit();
}


if(isset($_GET[obtener_folio_fallido])){	
	$sf = "SELECT folio FROM facturas WHERE serie ='{$_GET[obtener_folio_fallido]}' ORDER BY folio+1 DESC LIMIT 1";
	$qf = mysql_query($sf);
	$rf = mysql_fetch_assoc($qf);
	$proximo_folio = number_format(($rf[folio]+1),0,"","");
	echo $proximo_folio;
	exit();
}

//Checar precio de producto
if(isset($_GET[precio])){
	if($_GET[lista] == "_normal_"){ //Es precio público
		$s = "SELECT precio_publico 'precio' FROM productos WHERE id_producto = {$_GET[id_producto]}";
	} else { //Es precio de lista
		$s = "SELECT precio FROM precios WHERE id_producto = {$_GET[id_producto]} AND cliente = '{$_GET[lista]}'";	
	}
	$q = mysql_query($s) or die (mysql_error());
	if(mysql_num_rows($q)==0){
		exit();
	}
	$r = mysql_fetch_assoc($q);
	echo number_format($r[precio],2,'.','');
	exit();
}

//Establecer el Folio de la Próxima Nota de Venta
if(isset($_GET[ultima_nota])){
	$s = "SELECT nota + 1 n FROM notas_consecutivo ORDER BY nota DESC LIMIT 1";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
	echo $r[n];
	exit;
	$folios = array();
	while($r = mysql_fetch_assoc($q))
	{
		$d = explode("NOTA ",$r[folio]);
		$folios[] = $d[1];
    } 
	rsort($folios);
	/*foreach ($folios as $key => $val) {
		echo "folios[" . $key . "] = " . $val . "\n";
	}*/
	echo $folios[0]+1;
	   //print_r($folios); 
	  //echo 10;
	//$nota = intval(str_replace("NOTA ","",$r[folio]));
	//echo $nota+1;
  exit();
}

//PMV desde Ventas
if(isset($_GET[pmv])){
	$s = "SELECT pmv FROM precio_minimo WHERE id_producto = {$_GET[pmv]}";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
	echo number_format($r[pmv], 2, '.', '');
  exit();
}
 
//Pasar pedidos a compra =)
if(isset($_GET[pedido_compra]))
{
	$data = "";
	$s = "SELECT *, DATE_FORMAT(fecha, '%Y-%m-%d') fecha FROM pedidos WHERE folio = '{$_GET[pedido_compra]}'";
	$q = mysql_query($s) or die (mysql_error());
	while($r = mysql_fetch_assoc($q))
	{
		$proveedor = $r[proveedor];
		$almacen = $r[almacen];
		$fecha = $r[fecha];
		$comentario = str_replace("\r\n","~",$r[comentario]);
		$data .= "{$r[id_producto]}~{$r[cantidad]}~{$r[costo]}~{$r[iva]}~{$r[especial]}~{$r[complemento]}\n";
	}
	echo $proveedor."\n".$almacen."\n".$fecha."\n".$comentario."\n".substr($data,0,strlen($data)-1);
}

if(isset($_GET[morosos]))
{
	exit; //14 nov 2016 > No se hará caso a los morosos
	$n = array();
	$d = "SELECT clave
				FROM clientes c
				LEFT JOIN
				(
					SELECT *, SUM(saldo) saldo_final FROM
					(
						SELECT
						id_cliente,
						fecha_factura fecha_antigua,
						f.importe,
						f.importe - IFNULL(SUM(abono),0) saldo
						FROM facturas f
						LEFT JOIN ingresos_detalle d ON d.factura = f.folio
						WHERE f.status = 0
						GROUP BY folio
						ORDER BY fecha_factura
					) facturas_sin_pago
					WHERE saldo > 0
					GROUP BY id_cliente
				) s ON s.id_cliente = c.clave
				WHERE
					status = 0
					AND IFNULL(DATEDIFF(fecha_antigua, CURDATE() - dias_credito) * -1,0) <= dias_credito
				GROUP BY clave
				ORDER BY nombre ASC";
	$q = mysql_query($d) or die (mysql_error());
	while($r = mysql_fetch_assoc($q))
	{
		$n[] = $r[clave];
	}
	
	$s = "SELECT clave, nombre, credito, dias_credito, credito disponible, dias_credito dias
				FROM clientes c
				WHERE clave NOT IN (".implode(',', $n).") AND status = 0 AND LENGTH(TRIM(nombre)) > 0
				ORDER BY nombre ASC";
				// echo $s."<hr>";
	$q = mysql_query($s) or die (mysql_error());
	while($r = mysql_fetch_assoc($q))
	{
		$r[nombre] = utf8_encode($r[nombre]);
		$c[] = $r;
	}
	echo json_encode($c);
	exit;
}
?>