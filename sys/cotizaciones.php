<?php
if(isset($_GET[cancelar])){ //Inicia Cancelación
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	
	$s = "UPDATE cotizaciones SET status = 1 WHERE folio ={$_GET[cancelar]}";
	mysql_query($s) or die (mysql_error());
	echo mysql_affected_rows();
	exit();
} //Termina Cancelación
///////////////////////

if(!isset($_GET['registros'])){
	$per_page = 20;
	$_GET[registros] = 20;
} else {
	$per_page = $_GET['registros'];
}

if($_GET['cliente'] != "")
	$where = "AND cliente LIKE '%{$_GET['cliente']}%'";

if($_GET[folio] != "")
	$where = "AND folio LIKE '%{$_GET[folio]}%'";

if(!isset($_GET[order])){
	$_GET[order] = "folio";
	$_GET[direction] = "DESC";
}

if(isset($_GET[fecha1])){
	if(strlen($_GET[fecha1])>0 && strlen($_GET[fecha2]>0)){
		$where .= " AND fecha BETWEEN '{$_GET[fecha1]}' AND '{$_GET[fecha2]}'";
	} else {
		unset($_GET[fecha1]);
		unset($_GET[fecha2]);
	}
}

if($_GET[folio] != ""){
	$where = "AND folio LIKE '%{$_GET[folio]}%'";
}

if(isset($_GET[status]) && $_GET[status] != "x"){
	$where .= " AND c.status = '{$_GET[status]}'";
}

if(isset($_GET[tipo]) && $_GET[tipo] != "x"){
	$where .= " AND c.tipo = '{$_GET[tipo]}'";
}

$s = "SELECT COUNT(1) c FROM cotizaciones c WHERE 1 {$where}";
$total_records = $db->fetchCell($s);

$s = "SELECT
fecha,
folio,
c.status,
cliente,
moneda,
c.importe AS 'total',
u.nombre usuario
FROM
cotizaciones c
INNER JOIN usuarios u ON c.id_facturista = u.id_usuario
WHERE 1 
{$where}
GROUP BY c.folio";
require_once('funciones/kgPager.class.php');

$kgPagerOBJ = new kgPager();
$kgPagerOBJ -> pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$s = $s . " ORDER BY {$_GET[order]} + 0 {$_GET[direction]}, fecha DESC LIMIT ".$kgPagerOBJ -> start.", ".$kgPagerOBJ -> per_page;
$query = mysql_query($s) or die (nl2br($sql).mysql_error());

//Parámetros para el ícono "REGRESAR" del menú superior
foreach($_GET as $k => $v){
	$params .= "&{$k}={$v}";
}
$params = substr($params,1,strlen($params));
$_SESSION[start] = "comuni-k.php?".$params;
//Fin de parámetros
//Inicia configuración
$_POST[printable] = 0;
titleset("Gesti&oacute;n de Cotizaciones");
if(Administrador() || ComprasVentas() || Ventas()){
	$o = array(
		"<a href='?section=cotizaciones_formulario'><img src='imagenes/add.png' /> Registrar Cotizaci&oacute;n</a>",
		"<a href='cotizaciones_formulario_2016.php'><img src='imagenes/add.png' /> Registrar Cotizaci&oacute;n v2</a>"
	);
	pop($o,"cotizaciones");
}
filter_display("cotizaciones");
//Fin de configuración
?>
<script type="text/javascript">
function cancelar_compra(folio,obj){
	var e = obj.value;
	if(e == 1)
	{
		if(confirm("¿Seguro que quiere cancelar la cotización?"))
		{
			url = "cotizaciones.php?cancelar="+folio
			var r = procesar(url);
			if(r != '0')
			{
				document.getElementById('estado_span'+folio).innerHTML = "Cancelada";
			}
			else
			{
				alert("Ha ocurrido un error y esta cotización no se puede cancelar.\n"+r);
				obj.selectedIndex = 0;
			}
		}
		else
		{
			obj.selectedIndex = 0;
		}
	}
}
</script>
<form name="filtro" id="filtro" method="get" action="" class="<?=$_POST[class_filtro]?>">
	<p>Cliente: <input type="text" name="cliente" value="<?=$_GET[cliente]?>" style="width: 300px;" /></p>
  <p>
		Folio:
    <input name="folio" type="text" id="folio" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[folio]?>" size="8"/>
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
      <option value="folio" <?=selected($_GET[order],"folio+1")?>>Folio</option>
      <option value="fecha" <?=selected($_GET[order],"fecha_factura")?>>Fecha</option>
      <option value="cliente" <?=selected($_GET[order],"cliente")?>>Cliente</option>
      <option value="usuario" <?=selected($_GET[order],"usuario")?>>Vendedor</option>
      <option value="importe" <?=selected($_GET[order],"importe")?>>Importe</option>
      <option value="status" <?=selected("status",$_GET[order])?>>Status</option>
    </select>
		<select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>
    &nbsp;&nbsp;
    <input name="section" type="hidden" id="section" value="cotizaciones" />
    <button style="font-size:11px" type="submit">Crear lista de cotizaciones</button>
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
    <th>Folio</th>
    <th>Cliente</th>
    <th>Fecha</th>
    <th>Vendedor</th>
    <th>Importe</th>
    <th>Esitar</th>
    <th>Status</th>
  </tr>
  <?php

while($r = mysql_fetch_assoc($query)){
?>
  <tr id="tr_<?=$r['id']?>">
    <td style="text-align:center"><a href="?section=cotizaciones_detalle&folio=<?=$r[folio]?>"><?=$r[folio]?></a></td>    
    </td>
    <td><?php if($r[id_cliente] == "0"){ echo "Público"; } else { echo $r[cliente]; }?></td>
    <td><?=FormatoFecha($r[fecha])?></td>
    <td><?=$r[usuario]?></td>
    <td style="text-align:right"><?=money($r[total])?><?=mon($r[moneda])?></td>
    <td><a href="cotizaciones_formulario_2016.php?editar&folio=<?=$r[folio]?>"><img src="imagenes/pencil2.png" /></a></td>
    <td style="font-weight:bold; text-align:center">
      <?php if($r[status] == "0"){ ?>
      <span id="estado_span<?=$r[folio]?>">
      <select id="status_list" onchange="cancelar_compra('<?=$r[folio]?>',this);">
        <option value="0">Normal</option>
        <option value="1">Cancelar</option>
      </select>
      </span>
      <?php } else { echo "Cancelada"; } ?>
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