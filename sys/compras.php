<?php
if(isset($_GET[cancelar])){ //Inicia Cancelación
	include("funciones/basedatos.php");
	include("funciones/funciones.php");

	$query_cantidad_comprada = "SELECT
		c.id_almacen,
		cd.id_producto,
		cd.lote,
		cd.cantidad
		FROM
		compras c
		INNER JOIN compras_detalle cd ON c.id = cd.id_compra
		WHERE
		c.id =  '{$_GET['id']}'";
	$cantidad_comprada = mysql_query($query_cantidad_comprada)or die(mysql_error());
	while($px = mysql_fetch_assoc($cantidad_comprada)){ //Verificar existencias
		$exS = "SELECT cantidad FROM existencias WHERE id_producto = '{$px['id_producto']}' AND lote = '{$px['lote']}' AND id_almacen = '{$px['id_almacen']}'";
		$exQ = mysql_query($exS) or die (mysql_error());
		$exR = mysql_fetch_assoc($exQ);
		$ex = $exR['cantidad'];
		if($px['cantidad']>$ex){ //No hay existencias suficientes para cancelar.
			echo "X";
			exit();
		}
	}
	
	$cantidad_comprada = mysql_query($query_cantidad_comprada)or die(mysql_error());
	
	$s = "UPDATE compras SET status = 1 WHERE id = '{$_GET['id']}'";
	$db->execute($s);
	
	$s = "CALL compraCancelarProductos({$_GET['id']})";
	$db->execute($s);
	
	while($p = mysql_fetch_assoc($cantidad_comprada))
	{
		/*$updater = "UPDATE
		existencias
		SET cantidad = cantidad-{$p['cantidad']}
		WHERE id_producto = '{$p['id_producto']}'
		AND lote = '{$p['lote']}'
		AND id_almacen = '{$p['id_almacen']}'";
		mysql_query($updater)or die($updater.mysql_error());
	
		//Registrar el movimiento de SALIDA de existencias
		$folioS = "SELECT folio_factura FROM compras WHERE id = '{$_GET['id']}'";
		$folioQ = mysql_query($folioS) or die (mysql_error());
		$folioR = mysql_fetch_assoc($folioQ);
		$folio = $folioR['folio_factura'];
		$insert_cancelacion_movimientos = "INSERT INTO movimientos
		VALUES(
		null,
		'{$p['id_almacen']}',
		'0',
		'11',
		'{$p['id_producto']}',
		'{$p['cantidad']}',
		'{$p['lote']}',
		NOW(),
		'{$_SESSION['id_usuario']}',
		'{$folio}')";
		
		mysql_query($insert_cancelacion_movimientos) or die(mysql_error());*/
		
		//Buscar PMV Actual
		$s = "SELECT pmv FROM vars";
		$q = mysql_query($s);
		$pmv = mysql_fetch_assoc($q);
		$pmv = 1+($pmv[pmv]/100);

		$query_minimo = "SELECT (SUM(compras.importe)/SUM(cantidad))*{$pmv} AS 'minimo' FROM compras INNER JOIN compras_detalle ON compras.id = compras_detalle.id_compra WHERE id_producto = '{$p['id_producto'][$i]}' AND status = 0";
		$minimo = mysql_query($query_minimo)or die(mysql_error());
		$row_minimo = mysql_fetch_assoc($minimo);
		$min = number_format($row_minimo['minimo'], 2, '.', '');
		
		$pmv_sql = "SELECT id_producto FROM precio_minimo WHERE id_producto = '{$p['id_producto'][$i]}'";
		$pmv_query = mysql_query($pmv_sql) or die (mysql_error());
		if(mysql_num_rows($pmv_query)==0){
			$pmv_tabla = "INSERT INTO precio_minimo VALUES ('{$p['id_producto'][$i]}','{$min}')";
		}
		else{
			$pmv_tabla = "UPDATE precio_minimo SET pmv = '{$min}' WHERE id_producto = '{$_POST['id_producto'][$i]}'";
		}
		mysql_query($pmv_tabla) or die (mysql_error());
		unset($min);
	}
	
	echo "1";
	exit();
} //Termina Cancelación
///////////////////////

if(!isset($_GET['registros'])){
	$per_page = 20;
	$_GET[registros] = 20;
} else {
	$per_page = $_GET['registros'];
}

if($_GET[folio] != ""){
	$where = "AND folio LIKE '%{$_GET[folio]}%'";
}

if(!isset($_GET[order])){
	$_GET[order] = "fecha_factura";
	$_GET[direction] = "DESC";
}

if(isset($_GET[fecha1])){
	if(strlen($_GET[fecha1])>0 && strlen($_GET[fecha2]>0))
	{
		$where .= " AND fecha_factura BETWEEN '{$_GET[fecha1]}' AND '{$_GET[fecha2]}'";
	}
	else
	{
		unset($_GET[fecha1]);
		unset($_GET[fecha2]);
	}
}

if($_GET[folio] != ""){
	$where = "AND folio_factura LIKE '%{$_GET[folio]}%'";
}

if(isset($_GET[almacen]) && $_GET[almacen] != "0"){
	$where .= " AND a.id_almacen = '$_GET[almacen]'";
}

if(isset($_GET[proveedor]) && $_GET[proveedor] != "0"){
	$where .= " AND p.clave = '$_GET[proveedor]'";
}

if(isset($_GET[status]) && $_GET[status] != "x"){
	$having .= " AND estado = '{$_GET[status]}'";
}

$strSQL1 = "SELECT
a.descripcion 'almacen',
c.id,
fecha_factura,
DATEDIFF(ADDDATE(fecha_factura, dias_credito),NOW()) 'dias',
folio_factura 'folio',
id_proveedor,
c.status,
moneda,
nombre,
c.importe AS 'total',

IFNULL(abono, 0) AS 'abonos',
c.importe -IFNULL(abono,0) AS 'saldo',

CASE
	WHEN c.status <> 0 THEN 'Cancelada'
	WHEN abono IS NOT NULL AND abono = c.importe THEN 'Saldada'
	WHEN abono IS NOT NULL AND abono < c.importe THEN 'Abonada'
	ELSE 'Normal'
END estado,

DATE_ADD(fecha_factura, INTERVAL dias_credito DAY) AS 'dia_limite',
dias_credito
FROM
compras c
INNER JOIN proveedores p ON p.clave = c.id_proveedor
INNER JOIN almacenes a ON c.id_almacen = a.id_almacen
LEFT JOIN (
	SELECT SUM(ed.abono) abono, ed.factura
	FROM egresos e
	INNER JOIN egresos_detalle ed ON ed.id_egreso = e.id
	WHERE e.status = 0
	GROUP BY ed.factura
) eg ON eg.factura = c.id
WHERE 1 
{$where}
GROUP BY c.id
HAVING 1
{$having}";
// echo nl2br($strSQL1);
require_once('funciones/kgPager.class.php');
$sql = mysql_query($strSQL1) or die ($strSQL1.mysql_error());
$total_records = mysql_num_rows($sql);

$kgPagerOBJ = new kgPager();
$kgPagerOBJ -> pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$sql = $strSQL1." ORDER BY {$_GET[order]} {$_GET[direction]}, fecha_factura DESC LIMIT ".$kgPagerOBJ -> start.", ".$kgPagerOBJ -> per_page;
$query = mysql_query($sql) or die (nl2br($sql).mysql_error());

//Parámetros para el ícono "REGRESAR" del menú superior
foreach($_GET as $k => $v){
	$params .= "&{$k}={$v}";
}
$params = substr($params,1,strlen($params));
$_SESSION[start] = "comuni-k.php?".$params;
//Fin de parámetros
//Inicia configuración
$_POST[printable] = 0;
titleset("Gesti&oacute;n de Compras");

//RESTRICCION
if(Administrador() || Compras()){
	$o = array(
						 "0" => "<a href='?section=compras_formulario' ><img src='imagenes/add.png' /> Registrar Compra</a>",
						 "1" => "<a href='?section=compra_pagos' ><img src='imagenes/add.png' /> Registrar Pago a Proveedor</a>"
						 );
}
if(ComprasVentas()){
	$o = array(
						 "0" => "<a href='?section=compras_formulario' ><img src='imagenes/add.png' /> Registrar Compra</a>"
						 );
}

pop($o,"compras");

filter_display("compras");
//Fin de configuración

//RESTRICCION
if(Administrador() || ComprasVentas()){
?>
<script type="text/javascript">
function cancelar_compra(folio,obj){
	var e = obj.value;
	if(e == 2){
		if(confirm("¿Seguro que quiere cancelar la compra?")){
			url = "compras.php?cancelar&id="+folio
			var r = procesar(url);
			if(r == '1'){
				document.getElementById('estado_span'+folio).innerHTML = "<center><b>Cancelada</b></center>";
			}
      if(r == 'X'){
        alert("La cantidad de uno o más productos de esta compra no existen en el almacén.\nVerifique sus existencias e intente nuevamente.");
        obj.selectedIndex = 0;
      }
			if(r != '1' && r != 'X'){
        alert("Hubo un problema y no se pudo cancelar esta compra.\n"+r);
        obj.selectedIndex = 0;
      }
		}
		else{
			obj.selectedIndex = 0;
		}
	}
	
}
</script>
<?php } ?>
<form name="filtro" method="get" action="" id="filtro" class="<?=$_POST[class_filtro]?>">
  <p>Entre
    <input name="fecha1" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha1]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha1');" style="margin-bottom:-3px; cursor:pointer" />&nbsp;&nbsp;&nbsp;y&nbsp;&nbsp;
    <input name="fecha2" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha2]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha2');" style="margin-bottom:-3px; cursor:pointer" /></p>
  <p>Poveedor:
    <select name="proveedor">
      <option value="0">Cualquier Proveedor</option>
      <?php
						$sql_proveedor = "SELECT clave, nombre FROM proveedores WHERE status = 0";
						$query_proveedor = mysql_query($sql_proveedor) or die (mysql_error());
						while($r = mysql_fetch_array($query_proveedor)){
					?>
      <option value="<?=$r[clave]?>" <?=selected($_GET[proveedor],$r[clave])?>>
        <?=$r[nombre]?>
      </option>
      <?php
						}
					?>
    </select>
  </p>
  <p> Folio:
    <input name="folio" type="text" id="folio" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[folio]?>" size="8"/>
    &nbsp;&nbsp;&nbsp;
    Almac&eacute;n:
    <select name="almacen" id="almacen">
      <option value="0">Cualquier Almac&eacute;n</option>
      <?php
foreach($_SESSION[almacenes] as $k => $v){
	echo "<option value=\"{$v[id]}\" ".selected($_GET['almacen'],$v[id]).">{$v[descripcion]}</option>\r\n";
}
?>
    </select>
    &nbsp;&nbsp;&nbsp;Mostrar:
    <select name="registros" id="registros">
      <option value="10" <?=selected($_GET[registros],10)?>>10</option>
      <option value="20" <?=selected($_GET[registros],20)?>>20</option>
      <option value="50" <?=selected($_GET[registros],50)?>>50</option>
      <option value="100" <?=selected($_GET[registros],100)?>>100</option>
      <option value="500" <?=selected($_GET[registros],500)?>>500</option>
      <option value="0" <?=selected($_GET[registros],0)?>>Todos</option>
    </select>
  </p>
  <p>Status:
    <select name="status" id="status">
      <option value="x">Todos</option>
      <option <?=selected($_GET[status],"Normal")?>>Normal</option>
      <option <?=selected($_GET[status],"Cancelada")?>>Cancelada</option>
      <option <?=selected($_GET[status],"Abonada")?>>Abonada</option>
      <option <?=selected($_GET[status],"Saldada")?>>Saldada</option>
    </select>
    &nbsp;&nbsp;&nbsp; Ordenar por:
    <select name="order" id="select">
      <option value="folio" <?=selected($_GET[order],"folio")?>>Folio</option>
      <option value="fecha_factura" <?=selected($_GET[order],"fecha_factura")?>>Fecha</option>
      <option value="nombre" <?=selected($_GET[order],"nombre")?>>Proveedor</option>
      <option value="almacenes.descripcion" <?=selected($_GET[order],"almacenes.descripcion")?>>Almacen</option>
      <option value="importe" <?=selected($_GET[order],"importe")?>>Importe</option>
      <option value="nombre" <?=selected($_GET[order],"nombre")?>>Proveedor</option>
      <option value="dias" <?=selected($_GET[order],"dias")?>>D&iacute;as de Cr&eacute;d</option>
      <option value="estado" <?=selected("estado",$_GET[order])?>>Status</option>
    </select>
<select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>
    &nbsp;&nbsp;
    <input name="section" type="hidden" id="section" value="compras" />
    <input name="Buscar" type="submit" value="Crear lista de Compras" style="font-size:11px"/>
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
    <th>Folio</th>
    <th>Proveedor</th>
    <th>Almacén</th>
    <th>Fecha</th>
    <th>Importe</th>
    <?php
		//RESTRICCION
		if(Administrador() || ComprasVentas() || Compras()){
		?>
    <th>Saldo</th>
    <th>D&iacute;as de Cr&eacute;d.</th>
    <?php } ?>
    <th>Status</th>
  </tr>
  <?php
$i=0;
while($r = mysql_fetch_assoc($query)){
if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
$i++;
if ($r[dias] < 0){unset($color);$color = "#FFFFFF";$colorTD = "#FF0000";}
else if ($r[dias] < 4){unset($color);unset($colorTD);$color = "#FF0000";}
else {unset($color);unset($colorTD);$color = "#000000";}
?>
  <tr id="tr_<?=$r['id']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td style="text-align:center"><a href="?section=compras_detalle&id=<?=$r[id]?>"><?=$r[folio]?></a></td>
    <td><a href="?section=compra_pagos&proveedor=<?=$r[id_proveedor]?>"><?=$r[nombre]?></a></td>
    <td><?=$r[almacen]?></td>
    <td><?=FormatoFecha($r[fecha_factura])?></td>
    <td style="text-align:right"><?=money($r['total'])?><?=mon($r[moneda])?></td>
    
<?php //RESTRICCION
if(Administrador() || ComprasVentas() || Compras()){
?>
    <td style="text-align:right"><?php if ($r[saldo] != 0){echo money($r[saldo]).mon($r[moneda]);}else{echo "0.00".mon($r[moneda]); }?></td>
    <?php if($r[saldo] != 0){?>
      <td style="text-align:center" bgcolor="<?=$colorTD?>"><b><font color="<?=$color?>"><?=$r[dias]?></font></b></td>
  	<?php } else { ?>
      <td style="text-align:center">~</td>
    <?php } 
}
?>
		<td style="text-align:center; font-weight:bold">
    <?php
		if(Administrador() || ComprasVentas() || Compras()){
	    if($r[estado] == "Normal"){
    ?>
      <span id="estado_span<?=$r[id]?>">
        <select name="status_list" id="status_list" onchange="cancelar_compra('<?=$r[id]?>',this);">
          <option value="1">Normal</option>
          <option value="2">Cancelar</option>
        </select>
      </span>
    <?php
			} else {
				echo $r['estado'];
			}
		} else { // No es CXC
			echo $r[estado];
		}
		?>
    </td>
  </tr>
  <? }?>
</table>
<div style="text-align:center; margin-top:10px" id="_pagination">
  <?php
  echo '<p id="pager_links">';
  echo $kgPagerOBJ -> first_page;
  echo $kgPagerOBJ -> previous_page;
  echo $kgPagerOBJ -> page_links;
  echo $kgPagerOBJ -> next_page;
  echo $kgPagerOBJ -> last_page;
  echo '</p>';
  ?>
</div>
<?php } ?>