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

$chars = array("nota","Nota");
if($_GET[folio] != ""){
	$where = "AND folio LIKE '%".str_replace($chars,"",$_GET[folio])."%'";
}

if(isset($_GET[almacen]) && $_GET[almacen] != "0"){
	$where .= " AND almacenes.id_almacen = '$_GET[almacen]'";
}
if(isset($_GET[cliente]) && $_GET[cliente] != "-1"){
	$where .= " AND facturas.id_cliente = '$_GET[cliente]'";
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
facturas.fecha_factura,
facturas.importe,
almacenes.descripcion almacen,
facturas.id_cliente,
clientes.nombre cliente,
usuarios.nombre vendedor
FROM
facturas
INNER JOIN almacenes ON almacenes.id_almacen = facturas.id_almacen
LEFT JOIN clientes ON facturas.id_cliente = clientes.clave
LEFT JOIN usuarios ON usuarios.id_usuario = facturas.id_facturista
WHERE facturas.tipo= 'n' AND facturas.status = 0
{$where}";
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
<title>Comuni-K</title>
<link rel="stylesheet" type="text/css" href="css/body.css" />
<link href="js/calendar/calendar.css" rel="stylesheet" type="text/css" />
<script language="JavaScript" type="text/javascript" src="js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="js/calendar/calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/funciones.js"></script>
<script language="javascript" type="text/javascript" src="js/ajax.js"></script>
<script language="JavaScript" type="text/javascript" src="js/php.js"></script>
<script language="javascript" type="text/javascript">
function dataX(data)
{	
  $("#cliente_datos").val(data[0]+"|"+data[1]+"|"+data[2]+"|"+data[3]+"|"+data[4]+"|"+data[5]+"|"+data[6]+"|"+data[7]+"|"+data[8]+"|"+data[9]+"|"+data[10]);	
	
	if(data[4].length > 0)
	{
	  	data[4] = " Int. "+data[4];
	}
	if(data[3].length > 0)
	{
	  	data[3] = " No. "+data[3];
	}	
	//$("#cliente_data").val("CALLE: "+data[2]+data[3]+ data[4]+"\nCOLONIA: "+data[5]+"\nC.P.: "+data[10]+"\n"+data[6]+", "+data[7]+", "+data[8]+", "+data[9]+"\nRFC: "+data[0]);
}

function cliente_data_ajax(obj){	
	var cliente = document.getElementById("cliente");
	var url = "ajax_tools.php?cliente_data2="+obj.options[obj.selectedIndex].value;
	data = array("","","","","","","","","","");
	r = procesar(url);		
	data = r.split("|");
	dataX(data);
}
$(document).ready(function(){
	cliente_data_ajax($("#cliente").get(0));
	$("#id_clientes").val(top.window.$("#id_client").val());
});
function createX(folio,importe,fecha,cliente,obj){
	obj.onmouseover = "";
	obj.onmouseout = "";
	obj.style.backgroundColor = "#4B4B4B";
	obj.style.color = "#C3C3C3"

	error = 0;
	var folio_data = top.window.document.getElementsByName('folio[]');
	for(var i = 0; i <  folio_data.length; ++i){
		var folio_this = folio_data[i].value;
		var folio_this = folio_this.split("|");
		var folio_this = folio_this[0];
		if (folio == folio_this){
			alert("Esta venta ya ha sido incluida anteriormente.");
			error = 1;
		}
	}
    var client_data = top.window.document.getElementsByName('clientes[]');
	for(var e = 0; e <  client_data.length; ++e){
		var client_this = client_data[e].value;
		var client_this = client_this.split("|");
		var client_this = client_this[0];
		if (cliente != client_this){
			alert("Solo se permiten Notas del primer \ncliente seleccionado.");
			error = 1;
			break;
		}
	}	
	
	if (error == 0){
		$("#cliente").val(cliente);			
		cliente_data_ajax($("#cliente").get(0));
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
		celda_folio.style.textAlign = "center";
		folio_txt = document.createElement("a");
		folio_txt.setAttribute("href","javascript:ajax_this('ventas_detalle.php?folio="+folio+"&serie=CTO1');");
		folio_txt.innerHTML = folio;
		celda_folio.appendChild(folio_txt);
		
		folio_input = document.createElement("input");
		folio_input.value = folio;
		folio_input.type = "hidden";
		folio_input.name = "folio[]";
		celda_folio.appendChild(folio_input);	
		
		cliente_input = document.createElement("input");
		cliente_input.value = cliente;
		cliente_input.type = "hidden";
		cliente_input.className  = "client";
		cliente_input.name = "clientes[]";
		celda_folio.appendChild(cliente_input);	
		
		celda_fecha = fila.insertCell(1);
		celda_fecha.style.textAlign = "center";
		celda_fecha.innerHTML = fecha;
		
		celda_importe = fila.insertCell(2);
		celda_importe.style.textAlign = "right";
		celda_importe.innerHTML=money(importe);
		
		importe_input = document.createElement("input");
		importe_input.value = importe;
		importe_input.type = "hidden";
		importe_input.name = "importe[]";
		celda_importe.appendChild(importe_input);		

		top.window.document.getElementById('control').value = control+1;
		importes = top.window.document.getElementsByName('importe[]');
		total = 0;
		for(i=0;importes.length>i;i++){
			total+=parseFloat(importes[i].value);
		}
		top.window.document.getElementById('total_span').innerHTML = money(total);
		top.window.document.getElementById('total').value = total;	
		//top.window.document.getElementById('total_span').innerHTML = "CHRIS";
		var index = $("#cliente")[0].selectedIndex -2;		
		var datos = $("#cliente_datos").val();		
		if(index >= 0)
		{	
   		 if($("#id_clientes").val()	== ""){
		  window.top.$("#cliente_datos").val(datos);
		  window.top.$("#cliente").attr("selectedIndex",$("#cliente")[0].selectedIndex -2);
		  window.top.$("#id_client").val(index);		
		 }
		 if($("#id_clientes").val() == index){
			window.top.$("#cliente_datos").val(datos);
			window.top.$("#cliente").attr("selectedIndex",$("#cliente")[0].selectedIndex -2);
			window.top.$("#id_client").val(index);		 
		 }
		}
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
<p>Cliente:  <select name="cliente" id="cliente" onchange="cliente_data_ajax(this);">
    		  <option value="-1">Elije...</option>
              <option value="0">P&uacute;blico</option>
        <?php
$sql_cliente = "SELECT clave, nombre, credito, dias_credito FROM clientes WHERE status = 0 ORDER BY nombre";
$query_cliente = mysql_query($sql_cliente) or die (mysql_error());
if(mysql_num_rows($query_cliente)>0){
	while($r = mysql_fetch_array($query_cliente)){
		echo "<option value='{$r[clave]}' ".selected($_GET[cliente],$r[clave])." title='{$r[credito]}~{$disponible}'>{$r[nombre]}</option>";
	}
}
?>
      </select><input type="hidden" name="cliente_datos" value="" id="cliente_datos" /><input type="hidden" name="id_clientes" value="" id="id_clientes" /></p>
  <p> Folio:
    <input name="folio" type="text" id="folio" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[folio]?>" size="8"/>
    &nbsp;&nbsp;&nbsp;
    
    Almac&eacute;n:
        <select name="id_almacen" id="id_almacen">
            <?php
						foreach($_SESSION[almacenes] as $k => $v){
							/*echo "<option value=\"{$v[id]}\" ".selected($_GET[id_almacen],$v[id]).">{$v[descripcion]}</option>\r\n";*/
						if($k == 0){
							$append = "<option value=\"{$v[id]}\" ".selected($_GET[almacen],$v[id]).">{$v[descripcion]}</option>\r\n";}	
						if($k == 1){
							$appen = "<option value=\"{$v[id]}\" ".selected($_GET[almacen],$v[id]).">{$v[descripcion]}</option>\r\n";}							
						}
						echo $appen;
						echo $append;							
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
<table border="0" align="center" cellpadding="5" cellspacing="0" id="tablanotas" class="bordear_tabla lista">
<tr>
  <th>Folio</th>
  <th>Fecha</th>
  <th>Cliente</th>
  <th>Vendedor</th>
  <th>Almacen</th>
  <th>Importe</th>
  </tr>
<?php
/*////////////////////////////
INICIA CICLO DE FACTURAS
///////////////////////////*/
while ($r = mysql_fetch_assoc($query)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;	
	$vendedor = explode(" ",$vendedor);
	foreach($vendedor as $v)
	{
		$vendedor_v .= $v[0];
	}

?>
<tr
id="tr_<?=$r['id']?>"
class="<?=$class?>"
onMouseOver="this.setAttribute('class', 'tr_list_over');"
onMouseOut="this.setAttribute('class', '<?=$class?>');"
onClick="createX('<?=$r[folio]?>','<?=$r[importe]?>','<?=FormatoFecha($r[fecha_factura])?>','<?=$r[id_cliente]?>',this)"
style="cursor:pointer">
  <td style="text-align:center"><?=$r[folio]?></td>
  <td><?=FormatoFecha($r[fecha_factura])?></td>
  <td><?php if($r[id_cliente] == "0"){ echo "Público"; } else { echo $r[cliente]; }?></td>
  <td><?=$r[vendedor]?></td>
  <td><?=$r[almacen]?></td>
  <td style="text-align:right"><?=money($r[importe])?></td>
  </tr>
<?php } } ?>
</table>
</body>
</html>