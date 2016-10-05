<?php
// Aplico el filtro en caso de ser necesario
if(isset($_GET['filtro_data']) && $_GET['filtro_data'] != "0") {  //Campo de filtro definido
	$filtroWhere .= " AND (p.descripcion LIKE '%{$_GET['filtro_data']}%' OR p.codigo_barras LIKE '%{$_GET['filtro_data']}%')";
}
if(isset($_GET['id_almacen']) && $_GET['id_almacen'] != "0") { $filtroWhere .= " AND e.id_almacen = '{$_GET['id_almacen']}'";	} 

if($_GET['existencias'] == 1){
	$filtroWhere = "AND cantidad > 0";
} else if($_GET['existencias'] == 2){
	$filtroWhere = "AND cantidad <= 0";
}

if(!isset($_GET['registros'])){
	$per_page = 20;
	$_GET[registros] = 20;
} else {
	$per_page = $_GET['registros'];
}

if(!isset($_GET[order])){
	$_GET[order] = "descripcion";
	$_GET[direction] = "ASC";
}

$strSQL1 = "SELECT
p.codigo_barras,
p.precio_publico precio,
e.id_producto,
e.id_almacen,
e.lote,
a.descripcion AS 'almacen',
p.descripcion,
SUM(IF(e.cantidad<0,0,e.cantidad)) AS 'cantidad'
FROM
existencias AS e
INNER JOIN almacenes AS a ON a.id_almacen = e.id_almacen
INNER JOIN productos AS p ON p.id_producto = e.id_producto
WHERE 1
{$filtroWhere}
GROUP BY p.codigo_barras, p.id_producto";
//echo nl2br($strSQL1);
require_once('funciones/kgPager.class.php');
$sql = mysql_query($strSQL1) or die ($strSQL1.mysql_error());
$total_records = mysql_num_rows($sql);

$kgPagerOBJ = new kgPager();
$kgPagerOBJ -> pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$sql = $strSQL1." ORDER BY {$_GET[order]} {$_GET[direction]}, codigo_barras ASC LIMIT ".$kgPagerOBJ -> start.", ".$kgPagerOBJ -> per_page;
$existencias = mysql_query($sql) or die ($sql.mysql_error());
//Parámetros para el ícono "REGRESAR" del menú superior
foreach($_GET as $k => $v){
	$params .= "&{$k}={$v}";
}
$params = substr($params,1,strlen($params));
$_SESSION[start] = "comuni-k.php?".$params;
//Fin de parámetros
//Inicia configuración
$_POST[printable] = 0;
titleset("Gesti&oacute;n de Existencias");

//RESTRICCION
if(Administrador()){
	$o = array(
						 "0" => "<a href='?section=existencias_modificar' ><img src='imagenes/pencil.png' /> Modificar Existencias</a>",
						 "1" => "<a href='#' onclick='eliminar_lotes();'><img src='imagenes/deleteX.png' /> Eliminar Lotes sin existencias</a>"
						 );
}
pop($o,"existencias");
filter_display("existencias");
//Fin de configuración
?>
<script type="text/javascript" language="javascript">
function eliminar_lotes(){
	if(confirm("Los lotes que no tengan existencias serán eliminados.\n¿Está seguro que desea ejecutar ésta acción?")){
		var php = procesar("ajax_tools.php?eliminar_lotes");
		alert(php);
		window.location = '<?=$_SERVER[REQUEST_URI]?>';
	}
}
</script>
<form name="filtro" method="GET" action="" id="filtro" class="<?=$_POST[class_filtro]?>">
  <p>Filtro:
    <input name="filtro_data" type="text" id="filtro_data" value="<?=$_GET[filtro_data]?>" size="14" />
&nbsp;&nbsp;&nbsp;Cantidad:
    <select name="existencias" id="existencias">
      <option value="0" <?=selected($_GET['existencias'],"0")?>>Todos</option>
      <option value="1" <?=selected($_GET['existencias'],"1")?>>Con Existencias</option>
      <option value="2" <?=selected($_GET['existencias'],"2")?>>Sin Existencias</option>
    </select>
    &nbsp;&nbsp;&nbsp;Almac&eacute;n:
  <select name="id_almacen" id="id_almacen">
  	<option value="0">Todos los Almacenes</option>
<?php
foreach($_SESSION[almacenes] as $k => $v){
	echo "<option value=\"{$v[id]}\" ".selected($_GET['id_almacen'],$v[id]).">{$v[descripcion]}</option>\r\n";
}
?>
	</select>
  </p>
  <p>Mostrar:
    <select name="registros" id="registros">
      <option value="10" <?=selected($_GET[registros],10)?>>10</option>
      <option value="20" <?=selected($_GET[registros],20)?>>20</option>
      <option value="50" <?=selected($_GET[registros],50)?>>50</option>
      <option value="100" <?=selected($_GET[registros],100)?>>100</option>
      <option value="500" <?=selected($_GET[registros],500)?>>500</option>
      <option value="0" <?=selected($_GET[registros],0)?>>Todos</option>
    </select>
&nbsp;&nbsp;&nbsp;Ordenar por:
    <select name="order" id="order">
      <option value="codigo_barras" <?=selected("codigo_barras",$_GET[order])?>>C&oacute;digo de Barras</option>
      <option value="descripcion" <?=selected("descripcion",$_GET[order])?>>Descripci&oacute;n</option>
      <option value="cantidad" <?=selected("cantidad",$_GET[order])?>>Cantidad</option>
    </select>
    <select name="direction" id="direction">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>    
    &nbsp;&nbsp;&nbsp;
    <input type="submit" name="submit" id="submit" value="Crear lista de Existencias" />
    <input name="section" type="hidden" id="section" value="existencias" />
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
    <th nowrap="nowrap">Codigo de Barras</th>
    <th nowrap="nowrap">Descripci&oacute;n</th>
    <th nowrap="nowrap">Almacen</th>
    <th nowrap="nowrap">Cantidad</th>
    <th nowrap="nowrap">Precio</th>
		<th></th>
  </tr>
  <?php $i=0; while($r = mysql_fetch_assoc($existencias)){ if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1"; $i++; ?>
  <tr id="tr_<?=$r['id_producto']?>">
    <td><?=$r['codigo_barras']?></td>
    <td><b><?=$r['descripcion']?></b></td>
    <td style="text-align:center">
<?php
	if($_GET['id_almacen'] == 0){
		$strSQL = "SELECT e.* ,
		a.descripcion AS 'nombreAlmacen'
		FROM existencias e
		INNER JOIN almacenes a ON a.id_almacen = e.id_almacen
		INNER JOIN productos AS p ON p.id_producto = e.id_producto
		WHERE 1 {$filtroWhere} AND e.id_producto ='{$r['id_producto']}'
		GROUP BY id_almacen
		ORDER BY nombreAlmacen, lote ASC";
		//echo nl2br($strSQL);
		$reg = mysql_query($strSQL) or die (mysql_error());
		if(mysql_num_rows($reg)>1){
			echo "Varios";
		}
		else{
			while($r1 = mysql_fetch_assoc($reg)){
				echo $r1['nombreAlmacen'];
			}
		}
	}
	else{
		echo $r['almacen'];
	}
?>
	</td>
    <td style="text-align:right"><a href="existencias_detalles.php?id_producto=<?=$r['id_producto']?>&id_almacen=<?=$_GET[id_almacen]?>" target="_new"><?=$r['cantidad']?></a></td>
    <td style="text-align:right"><?=money($r['precio'])?></td>
		<td><a href="../librerias/barcode/bc.php?text=<?=$r['codigo_barras'] ?>" target="_<?=$r['codigo_barras']?>" title="Generar código de barras"><img src="../librerias/assets/barcode.png"></a></td>
  </tr>
  <?php } ?>
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