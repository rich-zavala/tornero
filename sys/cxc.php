<?php
if(!isset($_GET['registros'])){
	$per_page = 20;
	$_GET[registros] = 20;
} else {
	$per_page = $_GET['registros'];
}

if(!isset($_GET[status])){
	$_GET[status] = "y";
}

if($_GET[folio] != ""){
	$where = "AND folio LIKE '%{$_GET[folio]}%'";
}

if(!isset($_GET[order])){
	$_GET[order] = "fecha_factura";
	$_GET[direction] = "DESC";
}

if(isset($_GET[fecha1])){
	if(strlen($_GET[fecha1])>0 && strlen($_GET[fecha2]>0)){
		$where .= " AND fecha_factura BETWEEN '{$_GET[fecha1]}' AND '{$_GET[fecha2]}'";
	} else {
		unset($_GET[fecha1]);
		unset($_GET[fecha2]);
	}
}

if($_GET[folio] != ""){
	$where = "AND folio LIKE '%{$_GET[folio]}%'";
}

if(isset($_GET[almacen]) && $_GET[almacen] != "0"){
	$where .= " AND almacenes.id_almacen = '$_GET[almacen]'";
}

if(isset($_GET[id_cliente]) && $_GET[id_cliente] != "0"){
	$where .= " AND facturas.id_cliente = '$_GET[id_cliente]'";
}

if(isset($_GET[status]) && $_GET[status] != "x"){
	if($_GET[status] == "y"){
		$having .= " AND saldo > 0";
	} else {
		$having .= " AND estado = '{$_GET[status]}'";
	}
}

//Consulta de registros
$strSQL1 = "SELECT
facturas.folio,
facturas.id_almacen,
facturas.fecha_factura,
facturas.status,
facturas.importe,
moneda,
datediff(ADDDATE(fecha_factura,dias_credito),now()) AS diferencia,
IFNULL(SUM(CASE WHEN ingresos.status = 0 THEN ingresos_detalle.abono ELSE 0 END),0) AS 'abonos',
facturas.importe-IFNULL(SUM(CASE WHEN ingresos.status = 0 THEN ingresos_detalle.abono ELSE 0 END),0) AS 'saldo',
IF(
	facturas.status = 0,
	IF(
		facturas.importe-IFNULL(SUM(CASE WHEN ingresos.status = 0 THEN ingresos_detalle.abono ELSE 0 END),0) = 0,'Saldada',
			IF(
				(CASE WHEN ingresos.status = 0 THEN SUM(ingresos_detalle.abono) ELSE 0 END)>0,'Abonada','Normal'
			)
	), 'Cancelada'
) AS 'estado',
almacenes.descripcion AS 'almacen',
nombre AS 'cliente',
clientes.clave
FROM
facturas
INNER JOIN almacenes ON facturas.id_almacen = almacenes.id_almacen
LEFT JOIN clientes ON facturas.id_cliente = clientes.clave
LEFT OUTER JOIN ingresos_detalle ON ingresos_detalle.factura = facturas.folio
LEFT OUTER JOIN ingresos ON ingresos.id = ingresos_detalle.id_ingreso
WHERE
facturas.status = 0
AND facturas.tipo = 'f'
{$where}
GROUP BY facturas.folio
HAVING 1
{$having}";
#echo nl2br($strSQL1);
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
titleset("Gesti&oacute;n de Cuentas por Cobrar");
if(Administrador() || CCC()){
	$o = array(
						 "0" => "<a href='?section=ingresos_por_cliente' ><img src='imagenes/add.png' /> Abono por Cliente</a>",
						 "1" => "<a href='?section=ingresos_varios_clientes' ><img src='imagenes/add.png' /> Abono Múltiple</a>"
						 );
	pop($o,"cxc");
}
filter_display("cxc");
//Fin de configuración
?>
<form name="filtro" method="get" action="" id="filtro" class="filtro_principal">
  <p>Entre
    <input name="fecha1" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha1]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha1');" style="margin-bottom:-3px; cursor:pointer" />&nbsp;&nbsp;&nbsp;y&nbsp;&nbsp;
    <input name="fecha2" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha2]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha2');" style="margin-bottom:-3px; cursor:pointer" /></p>
  <p>Cliente:
    <select name="id_cliente">
      <option value="0">Cualquier Cliente</option>
      <?php
						$sql_cliente = "SELECT clave, nombre FROM clientes WHERE status = 0 ORDER BY nombre";
						$query_cliente = mysql_query($sql_cliente) or die (mysql_error());
						while($r = mysql_fetch_array($query_cliente)){
					?>
      <option value="<?=$r[clave]?>" <?=selected($_GET[id_cliente],$r[clave])?>>
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
      <option <?=selected($_GET[status],"Normal")?> value="Normal">Normal</option>
      <option <?=selected($_GET[status],"Abonada")?> value="Abonada">Abonada</option>
      <option <?=selected($_GET[status],"Saldada")?> value="Saldada">Saldada</option>
      <option <?=selected($_GET[status],"y")?> value="y">Saldo Pendiente</option>
    </select>
    &nbsp;&nbsp;&nbsp; Ordenar por:
    <select name="order" id="select">
      <option value="folio" <?=selected($_GET[order],"folio")?>>Folio</option>
      <option value="fecha_factura" <?=selected($_GET[order],"fecha_factura")?>>Fecha</option>
      <option value="nombre" <?=selected($_GET[order],"nombre")?>>Cliente</option>
      <option value="almacenes.descripcion" <?=selected($_GET[order],"almacenes.descripcion")?>>Almacen</option>
      <option value="importe" <?=selected($_GET[order],"importe")?>>Importe</option>
      <option value="status" <?=selected("status",$_GET[order])?>>Status</option>
    </select>
    <select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>
    <input name="section" type="hidden" id="section" value="cxc" />
    <input name="Buscar" type="submit" value="Crear lista de Ventas" style="font-size:11px"/>
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
<tr>
  <th>Folio</th>
  <th>Fecha</th>
  <th>Cliente</th>
  <th>Almacen</th>
  <th>Importe</th>
  <th>Saldo</th>
  <th>D&iacute;as Cr.</th>
  <th>Status</th>
</tr>
<?php
/*////////////////////////////
INICIA CICLO DE FACTURAS
///////////////////////////*/
while ($r = mysql_fetch_assoc($query)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
	unset($color);
	unset($colorTD);
	if ($r[diferencia] == 1 && $r[saldo]>0){$color = "#FF0000";$colorTD = "#FFFFFF";}
	else if ($r[diferencia] <= 0 && $r[saldo]>0){$color = "#FFFFFF";$colorTD = "#FF0000";}
?>
<tr id="tr_<?=$r['id']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
  <td style="text-align:center"><a href="?section=ventas_detalle&folio=<?=$r[folio]?>"><?=$r[folio]?></a></td>
  <td><?=FormatoFecha($r[fecha_factura])?></td>
  <td><a href="?section=ingresos_por_cliente&clave=<?=$r[clave]?>"><?=$r[cliente]?></a></td>
  <td><?=$r[almacen]?></td>
  <td style="text-align:right"><?=money($r[importe])?><?=mon($r[moneda])?></td>
  <td style="text-align:right"><?php if($r[saldo]>0){echo money($r[saldo]).mon($r[moneda]);} else {echo "~";} ?></td>
  <td style="text-align:center" bgcolor="<?=$colorTD?>">
  	<?php if($r[saldo]>0){?><font color="<?=$color?>" ><b><?=$r[diferencia]?></b></font><?php } else { echo "~";} ?>
  </td>
  <td style="text-align:center"><?=$r[estado]?></td>
</tr>
<?php }

?>
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
