<?php
if(isset($_GET['cancelar'])){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	$dev = $_GET['folio'];
	
	$s = "SELECT factura,serie_f,importe FROM devoluciones_clientes WHERE id = '{$dev}'";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
	$venta = $r[factura];	
	if($r[serie_f] == "")
	{
	  $serie= " AND serie IS NULL";
	}
	else
	{
		$serie= " AND serie='{$r[serie_f]}'";
	}
	$dev_importe = $r[importe];
	
	$s = "SELECT id_almacen FROM facturas WHERE folio = '{$venta}' {$serie}";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
	$alm = $r[id_almacen];
	
	$s = "SELECT * FROM devoluciones_clientes_detalles WHERE id_devolucion = '{$dev}'";
	$q = mysql_query($s) or die (mysql_error());
	while ($r = mysql_fetch_assoc($q)){ //Verificar que las existencias sean suficientes para sacarlas del almacén
		$se = "SELECT cantidad FROM existencias WHERE id_almacen = '{$alm}' AND id_producto = '{$r['id_producto']}' AND lote = '{$r['lote']}'";
		$qe = mysql_query($se) or die (mysql_error());
		$re = mysql_fetch_assoc($qe);
		if($re[cantidad] < $r[cantidad]){
			echo "error";
			exit();
		}
	}
	
	$q = mysql_query($s) or die (mysql_error());
	while ($r = mysql_fetch_assoc($q)){ //Verificar que las existencias sean suficientes para sacarlas del almacén
		if(strlen($re[cantidad])>0){ //Existe
			$se2 = "UPDATE existencias SET cantidad=cantidad-{$r['cantidad']} WHERE id_almacen = '{$alm}' AND id_producto = '{$r['id_producto']}' AND lote = '{$r['lote']}'";
		} else { //No existe. Hay que crearlo
			$se2 = "INSERT INTO existencias VALUES (NULL, {$alm}, {$r['id_producto']}, {$r['cantidad']}, '{$r['lote']}', 1);";
		}
		mysql_query($se2) or die (mysql_error());//Actualizar existencia
	}

	$s = "UPDATE facturas SET importe=importe+{$dev_importe} WHERE folio = '{$venta}' {$serie}"; 	//Actualizar IMPORTE TOTAL de la factura
	$q = mysql_query($s) or die (mysql_error());
	$s = "UPDATE devoluciones_clientes SET status = 1 WHERE id = '{$dev}'";
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
	$where = "AND factura LIKE '%{$_GET[folio]}%'";
}

if($_GET[factura] != ""){
	$where = "AND facturas.folio LIKE '%{$_GET[factura]}%'";
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
	$where = "AND dc.id LIKE '%{$_GET[folio]}%'";
}

if(isset($_GET[cliente]) && $_GET[cliente] != "0"){
	$where .= " AND cliente.clave = '$_GET[cliente]'";
}

$strSQL1 = "SELECT
dc.id,
dc.factura,
dc.importe,
fecha,
clientes.nombre AS 'cliente',
facturas.folio,
facturas.serie,
dc.status,
moneda
FROM
devoluciones_clientes AS dc
INNER JOIN facturas ON dc.factura = facturas.folio
INNER JOIN clientes ON facturas.id_cliente = clientes.clave
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
titleset("Gesti&oacute;n de Devoluciones de Clientes");
if(Administrador()){
	$o = array(
						 "0" => "<a href=\"ventas_pick.php\" onclick=\"return hs.htmlExpand(this,{objectType: 'iframe',headingText:'Elija una Factura para devolver', width:750});\" ><img src='imagenes/add.png' /> Registrar Devolución</a>"
						 );
	pop($o,"devoluciones_c");
}
filter_display("devoluciones_c");
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
	if(obj.selectedIndex == 1 && confirm("¿Está seguro que desea cancelar la devolución "+id+"?")){
		var url = "devoluciones_c.php?cancelar&folio="+id;
		var r = procesar(url);
		if(r == "cancelar"){
			document.getElementById("status_"+id).innerHTML = "Cancelado";
		}
		else if(r == "error"){
			alert("Uno o más de los productos de esta devolución no están disponibles en almacén.\nVerifique sus existencias.");
			obj.selectedIndex = 0;
		}
		else{
			alert("Ha ocurrido un error y la cancelación no pudo ser realizada.\n"+r);
			obj.selectedIndex = 0;
		}
	} else {
		obj.selectedIndex = 0;
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
    <p>Cliente:
      <select name="cliente">
        <option value="0">Cualquier Cliente</option>
        <?php
						$s = "SELECT clave, nombre FROM clientes WHERE status = 0";
						$q = mysql_query($s) or die (mysql_error());
						while($r = mysql_fetch_array($q)){
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
      </select>
    </p>
    <p>Ordenar por:
      <select name="order" id="select">
        <option value="folio" <?=selected($_GET[order],"folio")?>>Folio</option>
        <option value="fecha" <?=selected($_GET[order],"fecha")?>>Fecha</option>
        <option value="nombre" <?=selected($_GET[order],"nombre")?>>Cliente</option>
        <option value="factura" <?=selected($_GET[order],"factura")?>>Factura</option>
        <option value="importe" <?=selected($_GET[order],"importe")?>>Importe</option>
        <option value="estado" <?=selected("estado",$_GET[order])?>>Status</option>
      </select>
      <select name="direction" id="select2">
        <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
        <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
      </select>
      &nbsp;&nbsp;
      <input name="section" type="hidden" id="section" value="devoluciones_c" />
      <input name="Buscar" type="submit" value="Crear lista de Devoluciones" style="font-size:11px"/>
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
  </form>
<?php
if(mysql_num_rows($query)>0){
?>
  <table border="0" cellpadding="5" cellspacing="0" class="bordear_tabla lista"   id="_lista">
    <tr>
      <th>Folio</th>
      <th>Fecha</th>
      <th>Factura</th>
      <th>Cliente</th>
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
      <td><a href="?section=devoluciones_c_detalle&id=<?=$r['id']?>">
        <?=$r['id']?>
        </a></td>
      <td><?=FormatoFecha($r[fecha])?></td>
      <td><?=$r['folio']?></td>
      <td style="text-align:center"><?=$r['cliente']?></td>
      <td style="text-align:right"><?=money($r['importe'])?><?=mon($r[moneda])?></td>
      <td id="status_<?=$r['id']?>" style="text-align:center; font-weight:bold"><?php
		if($r[status] == "0"){
		?>
        <select name="select" onchange="cancelar(this,'<?=$r['id']?>');">
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