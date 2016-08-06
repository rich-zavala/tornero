<?php
include("funciones/basedatos.php");
include("funciones/funciones.php");
session_start();

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
facturas.serie,
facturas.id_almacen,
facturas.fecha_factura,
facturas.status,
facturas.importe,
IFNULL(SUM(CASE WHEN ingresos.status = 0 THEN ingresos_detalle.abono ELSE 0 END),0) AS 'abonos',
facturas.importe-IFNULL(SUM(CASE WHEN ingresos.status = 0 THEN ingresos_detalle.abono ELSE 0 END),0) AS 'saldo',
IF(facturas.status = 0, IF(SUM(abono)=facturas.importe,'Saldada',IF(SUM(abono)>0,'Abonada','Normal')), 'Cancelada') AS 'estado',
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
<script language="javascript" type="text/javascript">
function createX(folio,serie,importe,saldo,obj){
	error = 0;
	var folio_data = top.window.document.getElementsByName('folio[]');
	var folio_length = folio_data.length;
	for(var i = 0; i < folio_length; ++i){
		var folio_this = folio_data[i].value;
		var folio_this = folio_this.split("|");
		var folio_this = folio_this[0];
		if (folio == folio_this){
			alert("Esta venta ya ha sido incluida anteriormente.");
			error = 1;
		}
	}
	
	if (error == 0){
		top.window.document.getElementById("tr_vacio").style.display = "none";
		top.window.document.getElementById("tr_cabecera").style.display = "";
		top.window.document.getElementById("tr_total").style.display = "";
		
		control = parseFloat(top.window.document.getElementById('control').value);
		tabla = top.window.document.getElementById("facturator");
		
		if(control%2 == 0){
			var color = "tr_list_0";
		} else {
			var color = "tr_list_1";
		}
		
		fila = tabla.insertRow(3);
		fila.name = "row" + control;
		fila.id = "row" + control;
		fila.setAttribute("class",color);
		fila.onmouseover = function(){this.setAttribute('class','tr_list_over');}
		fila.onmouseout = function(){this.setAttribute('class',color);}

		celda_folio = fila.insertCell(0);
		celda_folio.style.textAlign= "center";
		folio_txt = document.createElement("a");
		folio_txt.setAttribute("href","javascript:ajax_this('ventas_detalle.php?folio="+folio+"&serie="+serie+"&v=1');");
		folio_txt.innerHTML = folio;
		celda_folio.appendChild(folio_txt);
		
		folio_input = document.createElement("input");
		folio_input.value =folio;
		folio_input.type = "hidden";
		folio_input.name = "folio[]";
		celda_folio.appendChild(folio_input);		
		
		serie_input = document.createElement("input");
		serie_input.value =serie;
		serie_input.type = "hidden";
		serie_input.name = "serie[]";
		celda_folio.appendChild(serie_input);		
		
		celda_importe = fila.insertCell(1);
		celda_importe.style.textAlign= "center";
		celda_importe.innerHTML=money(importe);
		
		celda_saldo = fila.insertCell(2);
		celda_saldo.style.textAlign= "right";
		celda_saldo.innerHTML=money(saldo);
		
		celda_abono = fila.insertCell(3);
		celda_abono.style.textAlign= "center";

		abono = document.createElement("input");
		abono.id = "abono"+control;
		abono.name = "abono[]";
		abono.type = "text";
		abono.size = 9;
		abono.value = "0.00";
		abono.style.textAlign = "right";
		abono.style.backgroundColor = "#FFFFCA";
		abono.style.borderStyle = "groove";
		celda_abono.appendChild(abono);
		
		top.window.blurring(control,saldo);
		top.window.document.getElementById('control').value = control+1;
		obj.onmouseover = "";
		obj.onmouseout = "";
		obj.style.backgroundColor = "#4B4B4B";
		obj.style.color = "#C3C3C3"
	}
}
</script>
</head>
<body>
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
						$sql_cliente = "SELECT clave, nombre FROM clientes WHERE status = 0";
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
  <p>Ordenar por:
<select name="order" id="select">
      <option value="folio" <?=selected($_GET[order],"folio")?>>Folio</option>
      <option value="fecha_factura" <?=selected($_GET[order],"fecha_factura")?>>Fecha</option>
      <option value="nombre" <?=selected($_GET[order],"nombre")?>>Cliente</option>
      <option value="almacenes.descripcion" <?=selected($_GET[order],"almacenes.descripcion")?>>Almacen</option>
      <option value="importe" <?=selected($_GET[order],"importe")?>>Importe</option>
    </select>
    <select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>
    <input name="section" type="hidden" id="section" value="cxc" />
    <input name="Buscar" type="submit" value="Crear lista de Ventas"/>
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
<tr>
  <th>Folio</th>
  <th>Serie</th>
  <th>Fecha</th>
  <th>Almacen</th>
  <th>Importe</th>
  <th>Saldo</th>
</tr>
<?php
/*////////////////////////////
INICIA CICLO DE FACTURAS
///////////////////////////*/
while ($r = mysql_fetch_assoc($query)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
?>
<tr
id="tr_<?=$r['id']?>"
class="<?=$class?>"
onMouseOver="this.setAttribute('class', 'tr_list_over');"
onMouseOut="this.setAttribute('class', '<?=$class?>');"
onClick="createX('<?=$r[folio]?>','<?=$r[serie]?>','<?=$r[importe]?>','<?=$r[saldo]?>',this)"
style="cursor:pointer">
  <td style="text-align:center"><?=$r[folio]?></td>
  <td style="text-align:center"><?=$r[serie]?></td>
  <td><?=FormatoFecha($r[fecha_factura])?></td>
  <td><?=$r[cliente]?></td>
  <td style="text-align:right"><?=money($r[importe])?></td>
  <td style="text-align:right"><?=money($r[saldo])?></td>
</tr>
<?php } } ?>
</table>
</body>
</html>