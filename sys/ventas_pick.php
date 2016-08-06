<?php
include("funciones/basedatos.php");
include("funciones/funciones.php");

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

if(isset($_GET[cliente]) && $_GET[cliente] != "0"){
	$where .= " AND clientes.clave = '$_GET[cliente]'";
}

if(isset($_GET[status]) && $_GET[status] != "x"){
	$where .= " AND facturas.status = '{$_GET[status]}'";
}

$strSQL1 = "SELECT
almacenes.descripcion 'almacen',
fecha_factura,
folio,
serie,
id_cliente,
facturas.status,
nombre,
facturas.importe AS 'total',
IF(facturas.status=0,IF(SUM(abono)=facturas.importe,'Saldada',IF(SUM(abono)>0,'Abonada','Normal')),'Cancelada') AS 'estado'
FROM
facturas
LEFT JOIN clientes ON clientes.clave=facturas.id_cliente
INNER JOIN almacenes ON facturas.id_almacen = almacenes.id_almacen
LEFT OUTER JOIN ingresos_detalle ON ingresos_detalle.factura = facturas.folio
LEFT OUTER JOIN ingresos ON ingresos.id = ingresos_detalle.id_ingreso
WHERE 1 
{$where}
AND facturas.tipo = 'f'
GROUP BY facturas.folio
HAVING estado = 'Normal'";
//echo nl2br($strSQL1);
require_once('funciones/kgPager.class.php');
$sql = mysql_query($strSQL1) or die ($strSQL1.mysql_error());
$total_records = mysql_num_rows($sql);

$kgPagerOBJ = new kgPager();
$kgPagerOBJ -> pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$sql = $strSQL1." ORDER BY {$_GET[order]} {$_GET[direction]}, fecha_factura DESC LIMIT ".$kgPagerOBJ -> start.", ".$kgPagerOBJ -> per_page;
$query = mysql_query($sql) or die (nl2br($sql).mysql_error());
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Comini-K</title>
<link rel="stylesheet" type="text/css" href="css/body.css" />
<link href="js/calendar/calendar.css" rel="stylesheet" type="text/css" />

<script language="JavaScript" type="text/javascript" src="js/calendar/calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/funciones.js"></script>
<script language="javascript" type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript">
function switcheo(){
	var div = document.getElementById("aviso");
	var contenido = document.getElementById("span_contenido");
	if(div.style.display == "none"){
		contenido.style.display = "none";
		div.style.display = ""
	} else {
		div.style.display = "none"
		contenido.style.display = "";
	}
}
</script>
</head>
<body>
<div id="aviso" style="display:none; text-align:center; font-size:16px; color:#039; font-weight:bold; margin:20px">
En esta lista s&oacute;lo se muestran aquellas facturas que no han<br />
recibido ning&uacute;n tipo de pago o nota de cr&eacute;dito.<br /><br />
<a href="javascript: switcheo();"><img src="imagenes/ico_return.png" style="margin-bottom:-4px" /></a></div>
<span id="span_contenido">
<form name="filtro" method="get" action="" id="filtro" class="filtro_principal">
  <p>Entre
    <input name="fecha1" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha1]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha1');" style="margin-bottom:-3px; cursor:pointer" />&nbsp;&nbsp;&nbsp;y&nbsp;&nbsp;
    <input name="fecha2" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha2]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha2');" style="margin-bottom:-3px; cursor:pointer" /></p>
  <p>Cliente:
    <select name="cliente">
      <option value="0">Cualquier Cliente</option>
      <?php
						$sql_cliente = "SELECT clave, nombre FROM clientes WHERE status = 0";
						$query_cliente = mysql_query($sql_cliente) or die (mysql_error());
						while($r = mysql_fetch_array($query_cliente)){
					?>
      <option value="<?=$r[clave]?>" <?=selected($_GET[cliente],$r[clave])?>>
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
  <p>Ordenar por:
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
    &nbsp;&nbsp;
    <input name="section" type="hidden" id="section" value="ventas" />
    <input name="Buscar" type="submit" value="Crear lista de Ventas" style="font-size:11px"/>
  </p>
  Encontrados: <?=$total_records?> coincidencias <a href="javascript: switcheo();"><img src="imagenes/icon_attention.png" style="margin-bottom:-4px" /></a>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
  <tr>
    <th>Folio</th>
    <th>Cliente</th>
    <th>Almacén</th>
    <th>Fecha</th>
    <th>Importe</th>
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
    <tr id="tr_<?=$r['id']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');"
  onclick="top.window.location = 'comuni-k.php?section=devoluciones_c_formulario&venta=<?=$r[folio]?>&ser=<?=$r[serie]?>'" style="cursor:pointer">

    <td style="text-align:center"><?=$r[folio]?>
      <?php 
			$ss = "SELECT id FROM devoluciones_clientes WHERE factura = '{$r[folio]}' AND status = 0";
			$qq = mysql_query($ss) or die (mysql_error());
			if(mysql_num_rows($qq) > 0){?>
      <br /><font size="1">Devuelta</font>
      <?php } ?>
    </td>
    <td><?php if($r[id_cliente] == "0"){ echo "Público"; } else { echo $r[nombre]; }?></td>
    <td><?=$r[almacen]?></td>
    <td><?=FormatoFecha($r[fecha_factura])?></td>
    <td style="text-align:right"><?=money($r[total])?></td>
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
</span>
</body>
</html>