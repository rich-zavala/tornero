<?php
if(isset($_GET['cancelar'])){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	$dev = $_GET['folio'];
	
	$s = "SELECT id_compra, importe FROM devoluciones_proveedores WHERE id = '{$dev}'";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
	$compra = $r['id_compra'];
	$dev_importe = $r['importe'];
	
	$s = "SELECT id_almacen FROM compras WHERE id = '{$compra}'";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
	$alm = $r['id_almacen'];
	
	$s = "SELECT * FROM devoluciones_proveedores_detalles WHERE id_devolucion = '{$dev}'";
	$q = mysql_query($s) or die (mysql_error());
	while ($r = mysql_fetch_assoc($q)){ //Verificar disponibilidad -- No se verifica menudeo. BUG
		$r['lote'] = str_replace("Indeterminado","",$r['lote']);
		
		$se = "SELECT id_existencia FROM existencias WHERE id_almacen = '{$alm}' AND id_producto = '{$r['id_producto']}' AND lote = '{$r['lote']}'"; //Buscar existencia
		$qe = mysql_query($se) or die (mysql_error());
		if(mysql_num_rows($qe)>0){ //Existe
			$se2 = "UPDATE existencias SET cantidad=cantidad+{$r['cantidad']} WHERE id_almacen = '{$alm}' AND id_producto = '{$r['id_producto']}' AND lote = '{$r['lote']}'";
		} else { //No existe. Hay que crearlo
			$se2 = "INSERT INTO existencias VALUES (NULL, {$alm}, {$r['id_producto']}, {$r['cantidad']}, '{$r['lote']}', 1);";
		}
		mysql_query($se2) or die (mysql_error());//Actualizar existencia
	}
	
	$s = "UPDATE compras SET importe=importe+{$dev_importe} WHERE id = '{$compra}'"; 	//Actualizar IMPORTE TOTAL de la factura
	$q = mysql_query($s) or die (mysql_error());
	$s = "UPDATE devoluciones_proveedores SET status = 1 WHERE id = '{$dev}'";
	$q = mysql_query($s) or die (mysql_error());
	
	echo "cancelar";
	exit();
}


if(!isset($_GET['registros'])){
	$per_page = 20;
	$_GET[registros] = 20;
} else {
	$per_page = $_GET['registros'];
}

if($_GET[folio] != ""){
	$where = "AND folio LIKE '%{$_GET[folio]}%'";
}

if($_GET[factura] != ""){
	$where = "AND folio_factura LIKE '%{$_GET[factura]}%'";
}

if(!isset($_GET[order])){
	$_GET[order] = "fecha";
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
	$where = "AND dp.id LIKE '%{$_GET[folio]}%'";
}

if(isset($_GET[proveedor]) && $_GET[proveedor] != "0"){
	$where .= " AND proveedores.clave = '$_GET[proveedor]'";
}

$strSQL1 = "SELECT
dp.id,
dp.id_compra,
dp.importe,
fecha,
proveedores.nombre AS 'proveedor',
compras.folio_factura,
dp.status
FROM
devoluciones_proveedores AS dp
INNER JOIN compras ON dp.id_compra = compras.id
INNER JOIN proveedores ON compras.id_proveedor = proveedores.clave
WHERE 1
{$where}";
require_once('funciones/kgPager.class.php');
$sql = mysql_query($strSQL1) or die ($strSQL1.mysql_error());
$total_records = mysql_num_rows($sql);

$kgPagerOBJ = new kgPager();
$kgPagerOBJ -> pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$sql = $strSQL1." ORDER BY {$_GET[order]} {$_GET[direction]}, fecha DESC LIMIT ".$kgPagerOBJ -> start.", ".$kgPagerOBJ -> per_page;
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
titleset("Gesti&oacute;n de Devoluciones a Proveedor");
if(Administrador()){
	$o = array(
						 "0" => "<a href=\"compras_pick.php\" onclick=\"return hs.htmlExpand(this,{objectType: 'iframe',headingText:'Elija una Factura para devolver', width:750});\" ><img src='imagenes/add.png' /> Registrar Devolución</a>"
						 );
	pop($o,"devoluciones_p");
}
filter_display("devoluciones_p");
//Fin de configuración
?>
<script type="text/javascript" language="javascript">
function order(campo){
  var buscar = document.getElementById("buscar");
  window.location = "?direccion=<?=$direccionx?>&buscar="+buscar.value+"&order="+campo;
}
function submitenter(myfield,e){
  var keycode;
  if (window.event) keycode = window.event.keyCode;
  else if (e) keycode = e.which;
  else return true;
  if (keycode == 13)
  {
    xsub();
    return false;
  }
  else
  return true;
}

function cancelar(obj,id){
	var estado_nuevo = obj.options[obj.selectedIndex].value;
	if(estado_nuevo == 1){
		var estado_viejo = 0;
	} else {
		var estado_viejo = 1;
	}
	if(estado_nuevo == 1 && confirm("¿Está seguro que desea cancelar la devolución "+id+"?")){
		var url = "devoluciones_p.php?cancelar&folio="+id;
		var r = procesar(url);
		if(r == "cancelar"){
			document.getElementById("status_"+id).innerHTML = "Cancelado";
		}
		else if(r == "no_disponible"){
			alert("Uno o más de los productos de esta devolución no están disponibles en almacén.\nVerifique sus existencias.");
			obj.selectedIndex = estado_viejo;
		}
		else{
			alert("Ha ocurrido un error y la cancelación no pudo ser realizada.\n"+r);
			obj.selectedIndex = estado_viejo;
		}
	} else {
		obj.selectedIndex = estado_viejo;
	}
	//alert(r);
}
</script>
<center>
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
&nbsp;&nbsp;&nbsp; Factura:
      <input name="factura" type="text" id="factura" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[factura]?>" size="8"/>
&nbsp;&nbsp;&nbsp; Mostrar:
<select name="registros" id="registros">
        <option value="10" <?=selected($_GET[registros],10)?>>10</option>
        <option value="20" <?=selected($_GET[registros],20)?>>20</option>
        <option value="50" <?=selected($_GET[registros],50)?>>50</option>
        <option value="100" <?=selected($_GET[registros],100)?>>100</option>
        <option value="500" <?=selected($_GET[registros],500)?>>500</option>
        <option value="0" <?=selected($_GET[registros],0)?>>Todos</option>
      </select>
      &nbsp;&nbsp;&nbsp; Status:
      <select name="status" id="status">
        <option value="x">Todos</option>
        <option <?=selected($_GET[status],"Normal")?>>Normal</option>
        <option <?=selected($_GET[status],"Cancelada")?>>Cancelada</option>
        <option <?=selected($_GET[status],"Abonada")?>>Abonada</option>
        <option <?=selected($_GET[status],"Saldada")?>>Saldada</option>
      </select>
    </p>
    <p>Ordenar por:
      <select name="order" id="select">
        <option value="folio_factura" <?=selected($_GET[order],"folio_factura")?>>Folio</option>
        <option value="fecha" <?=selected($_GET[order],"fecha")?>>Fecha</option>
        <option value="nombre" <?=selected($_GET[order],"nombre")?>>Proveedor</option>
        <option value="factura" <?=selected($_GET[order],"factura")?>>Proveedor</option>
        <option value="importe" <?=selected($_GET[order],"importe")?>>Importe</option>
        <option value="nombre" <?=selected($_GET[order],"nombre")?>>Proveedor</option>
        <option value="estado" <?=selected("estado",$_GET[order])?>>Status</option>
      </select>
      <select name="direction" id="select2">
        <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
        <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
      </select>
      &nbsp;&nbsp;
      <input name="section" type="hidden" id="section" value="devoluciones_p" />
      <input name="Buscar" type="submit" value="Crear lista de Devoluciones" style="font-size:11px"/>
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
  </form>
<?php
if(mysql_num_rows($query)>0){
?>
  <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
    <tr>
      <th>Folio</th>
      <th>Fecha</th>
      <th>Factura</th>
      <th>Proveedor</th>
      <th>Importe</th>
      <th>Status</th>
    </tr>
    <?php
$i=0;
while($r = mysql_fetch_assoc($query)){
if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
$i++;
?>
    <tr id="tr_<?=$r['id']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
      <td><a href="?section=devoluciones_p_detalle&id=<?=$r['id']?>">
        <?=$r['id']?>
        </a></td>
      <td><?=FormatoFecha($r[fecha])?></td>
      <td><?=$r['folio_factura']?></td>
      <td style="text-align:center"><?=$r['proveedor']?></td>
      <td style="text-align:right"><?=money($r['importe'])?></td>
      <td style="text-align:center; font-weight:bold" id="status_<?=$r['id']?>"><?php
		if($r[status] == "0"){
		?>
        <select name="select" onchange="cancelar(this,<?=$r['id']?>);">
          <option value="0" <?=selected(0,$r[status]);?>>Normal</option>
          <option value="1" <?=selected(1,$r[status]);?>>Cancelado</option>
        </select>
        <?php
		} else {
		?>
        Cancelado
        <?php
		}
		?></td>
    </tr>
    <?php
}
?>
  </table>
</center>
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