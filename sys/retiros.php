<?php
if(!isset($_GET['registros']))
{
	$per_page = 20;
	$_GET[registros] = 20;
}
else
{
	$per_page = $_GET['registros'];
}

if(!isset($_GET[order]))
{
	$_GET[order] = "id";
	$_GET[direction] = "DESC";
}

if(isset($_GET[fecha1]))
{
	if(strlen($_GET[fecha1])>0 && strlen($_GET[fecha2]>0))
	{
		$where .= " AND fecha BETWEEN '{$_GET[fecha1]}' AND '{$_GET[fecha2]}'";
	}
	else
	{
		unset($_GET[fecha1]);
		unset($_GET[fecha2]);
	}
}

if($_GET[id] != "")
{
	$where = "AND id LIKE '%{$_GET[folio]}%'";
}

if(isset($_GET[status]) && $_GET[status] != "x")
{
	$where .= " AND r.status = '{$_GET[status]}'";
}

$s = "SELECT * FROM `comuni-k_tornero`.retiros_view
			WHERE 1 
			{$where}";
//echo nl2br($s);
require_once('funciones/kgPager.class.php');
$sql = mysql_query($s) or die ($s.mysql_error());
$total_records = mysql_num_rows($sql);

$kgPagerOBJ = new kgPager();
$kgPagerOBJ -> pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$sql = $s." ORDER BY 0+{$_GET[order]}+0 {$_GET[direction]}, fecha DESC LIMIT ".$kgPagerOBJ -> start.", ".$kgPagerOBJ -> per_page;
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
titleset("Gesti&oacute;n de retiros de La Casa del Tornero");
if(Administrador() || ComprasVentas() || Ventas()){
	$o = array(
						 "0" => "<a href='?section=retiros_formulario' ><img src='imagenes/add.png' /> Registrar retiro</a>"
						 );
	pop($o,"retiros");
}
filter_display("retiros");
//Fin de configuración
?>
<form name="filtro" id="filtro" method="get" action="" class="<?=$_POST[class_filtro]?>">
  <p>
		Folio:
    <input name="id" type="text" id="id" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[id]?>" size="8"/>
    &nbsp;&nbsp;&nbsp;

		Entre
    <input name="fecha1" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha1]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha1');" style="margin-bottom:-3px; cursor:pointer" />&nbsp;&nbsp;&nbsp;y&nbsp;&nbsp;
    <input name="fecha2" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha2]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha2');" style="margin-bottom:-3px; cursor:pointer" />
		
		&nbsp;&nbsp;&nbsp;
    Mostrar:
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
      <option <?=selected($_GET[status],"0")?> value="0">Normal</option>
      <option <?=selected($_GET[status],"1")?> value="1">Cancelada</option>
    </select>
    &nbsp;&nbsp;&nbsp; Ordenar por:
    <select name="order" id="select">
      <option value="id" <?=selected($_GET[order],"id")?>>Folio</option>
      <option value="fecha" <?=selected($_GET[order],"fecha")?>>Fecha</option>
      <option value="a.descripcion" <?=selected($_GET[order],"a.descripcion")?>>Almacen</option>
      <option value="u.nombre" <?=selected($_GET[order],"u.nombre")?>>Usuario</option>
      <option value="r.status" <?=selected("r.status",$_GET[order])?>>Status</option>
    </select>
		<select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>
    &nbsp;&nbsp;
    <input name="section" type="hidden" id="section" value="cotizaciones" />
    <input name="Buscar" type="submit" value="Crear lista de Cotizaciones" style="font-size:11px"/>
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
    <th>Folio</th>
    <th>Fecha</th>
    <th>Almacén</th>
    <th>Usuario</th>
    <th>Status</th>
  </tr>
  <?php
$i=0;
while($r = mysql_fetch_assoc($query))
{
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
	if ($r[dias] < 0){unset($color);$color = "#FFFFFF";$colorTD = "#FF0000";}
	else if ($r[dias] < 4){unset($color);unset($colorTD);$color = "#FF0000";}
	else {unset($color);unset($colorTD);$color = "#000000";}
?>
  <tr id="tr_<?=$r['id']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td style="text-align:center"><a href="?section=retiros_detalle&id=<?=$r[id]?>"><?=$r[id]?></a></td>
    <td><?=FormatoFecha($r[fecha])?><br /><?=FormatoHora($r[fecha])?></td>
    <td><?=$r[descripcion]?></td>
    <td><?=$r[nombre]?></td>
    <td style="font-weight:bold; text-align:center">
      <?php
			if($r[status] == "0")
			{
				echo "Normal";
			}
			else
			{
				echo "Cancelada";
			}
			?>
		</td>
  </tr>
<?php
}
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