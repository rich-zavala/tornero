<?php
if(isset($_GET['cancelar'])){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	$s = "UPDATE pedidos SET status = 1 WHERE folio = '{$_GET['folio']}'";
  if(mysql_query($s)){
		echo 0;
	} else {
		mysql_error();
	}
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

if(isset($_GET[almacen]) && $_GET[almacen] != "0"){
	$where .= " AND almacen = '$_GET[almacen]'";
}

if(isset($_GET[proveedor]) && $_GET[proveedor] != "0"){
	$where .= " AND proveedores.clave = '$_GET[proveedor]'";
}

if(isset($_GET[status]) && $_GET[status] != "x"){
	$where .= " AND pedidos.status = {$_GET[status]}";
}

$strSQL1 = "SELECT
*,
pedidos.status 'status',
fecha,
SUM(importe) 'importe',
nombre 'proveedor',
moneda,
almacenes.descripcion 'almacen'
FROM pedidos
INNER JOIN proveedores ON proveedores.clave = pedidos.proveedor
INNER JOIN almacenes ON almacenes.id_almacen = pedidos.almacen
{$where}
GROUP BY folio";
require_once('funciones/kgPager.class.php');
$sql = mysql_query($strSQL1) or die ($strSQL1.mysql_error());
$total_records = mysql_num_rows($sql);

$kgPagerOBJ = new kgPager();
$kgPagerOBJ -> pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$sql = $strSQL1." ORDER BY {$_GET[order]} {$_GET[direction]}, fecha DESC LIMIT ".$kgPagerOBJ -> start.", ".$kgPagerOBJ -> per_page;
$query = mysql_query($sql) or die ($sql.mysql_error());

//Parámetros para el ícono "REGRESAR" del menú superior
foreach($_GET as $k => $v){
	$params .= "&{$k}={$v}";
}
$params = substr($params,1,strlen($params));
$_SESSION[start] = "comuni-k.php?".$params;
//Fin de parámetros
//Inicia configuración
$_POST[printable] = 0;
titleset("Gesti&oacute;n de Pedidos");

/*==  CARNES ==*/
if(Administrador() || Compras()){
  $o = array(
             "0" => "<a href='?section=pedidos_formulario' ><img src='imagenes/add.png' /> Registrar Pedido</a>"
             );
  pop($o,"pedidos");
}
/*==  CARNES ==*/

filter_display("pedidos");
//Fin de configuración

//RESTRICCION
if(Administrador() || Compras()){
?>
<script type="text/javascript" language="javascript">
function cancelar(obj,folio){
  if(obj.value == 1 && confirm("¿Está seguro de querer cancelar este pedido?")){
    var url = "pedidos.php?cancelar&folio="+folio;
    var r = procesar(url);
    if(r == "0"){
      document.getElementById("cancelado"+folio).innerHTML = "Cancelado";
    }
    else{
      alert("Ha habido un error y no se pudo cancelar este pedido.\n"+r);
      obj.selectedIndex = 0;
    }
  }
  else{
    obj.selectedIndex = 0;
  }
}
</script>
<?php } ?>
<form name="filtro" method="GET" action="" id="filtro" class="<?=$_POST[class_filtro]?>">
  <p>Entre
    <input name="fecha1" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha1]?>" />
    <img src="imagenes/calendar.png" onClick="displayDatePicker('fecha1');" style="margin-bottom:-3px; cursor:pointer" />&nbsp;&nbsp;&nbsp;y&nbsp;&nbsp;
    <input name="fecha2" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha2]?>" />
  <img src="imagenes/calendar.png" onClick="displayDatePicker('fecha2');" style="margin-bottom:-3px; cursor:pointer" /></p>
  <p>Poveedor: 
    <select name="proveedor">
     <option value="0">Cualquier Proveedor</option>
      <?php
						$sql_proveedor = "SELECT clave, nombre FROM proveedores WHERE status = 0";
						$query_proveedor = mysql_query($sql_proveedor) or die (mysql_error());
						while($r = mysql_fetch_array($query_proveedor)){
					?>
      <option value="<?=$r[clave]?>" <?=selected($_GET[proveedor],$r[clave])?>><?=$r[nombre]?></option>
      <?php
						}
					?>
    </select>
  </p>
  <p>    Folio:
    <input name="folio" type="text" id="folio" onKeyPress="return submitenter(event,this,'filtro')" value="<?=$_GET[folio]?>" size="8"/>
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
      <option value="0" <?=selected($_GET[status],"0")?> >Normal</option>
      <option value="1" <?=selected($_GET[status],"1")?> >Cancelado</option>
    </select>
&nbsp;&nbsp;&nbsp; Ordenar por:
    <select name="order" id="select">
      <option value="folio" <?=selected($_GET[order],"folio")?>>Folio</option>
      <option value="fecha" <?=selected($_GET[order],"fecha")?>>Fecha</option>
      <option value="nombre" <?=selected($_GET[order],"nombre")?>>Proveedor</option>
      <option value="almacenes.descripcion" <?=selected($_GET[order],"almacenes.descripcion")?>>Almacen</option>
      <option value="importe" <?=selected($_GET[order],"importe")?>>Importe</option>
      <option value="status" <?=selected("status",$_GET[order])?>>Status</option>
    </select>
    <select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>
    &nbsp;&nbsp;
    <input name="section" type="hidden" id="section" value="pedidos" />
  <input name="Buscar" type="submit" value="Crear lista de Pedidos" style="font-size:11px"/>
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
    <th>Folio</th>
    <th>Fecha</th>
    <th>Proveedor</th>
    <th>Almac&eacute;n</th>
    <th>Importe</th>
		<?php
		if((Administrador() || Compras()) && $r[status] == 0)
		{
		?>
    <th>&nbsp;</th>
    <?php } ?>
    <th>Estado</th>
  </tr>
  <?php
$i = 0;
while($r = mysql_fetch_assoc($query)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;
?>
  <tr id="tr_<?=$r['id_usuario']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td style="text-align:center"><a href="?section=pedidos_detalle&id=<?=$r['folio']?>">
      <?=$r['folio']?>
      </a></td>
    <td><?=FormatoFecha($r['fecha'])?></td>
    <td><?=$r['proveedor']?></td>
    <td><?=ucfirst(strtolower($r['almacen']))?></td>
    <td style="text-align:right"><?=money($r['importe'])?><?=mon($r[moneda])?></td>
    <td>
    	<?php
			if((Administrador() || Compras()) && $r[status] == 0)
			{
			?>
    	<a href="?section=pedidos_formulario&editar=<?=$r[folio]?>"><img src="imagenes/pencil.png" /></a>
      <?php
			}
			?>
    </td>
    <td style="text-align:center"><span id="cancelado<?=$r['folio']?>" style="font-weight:bold">
      <?php
      //RESTRICCION
			if(Administrador() || Compras()){
				if($r[status] == 0){?>
				<select id="cancelar" onChange="cancelar(this,'<?=$r['folio']?>')">
					<option value="0">Normal</option>
					<option value="1">Cancelar</option>
				</select>
				<?php } else { ?>
        Cancelado
        <?php }
			} else { //No es Supervisor
				if($r[status] == 0){ echo "Normal"; }
				if($r[status] == 1){ echo "Cancelado"; }
			}
			?>
      </span></td>
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