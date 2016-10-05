<?php
if(isset($_GET[cambiar_estado])){ //Cambiar el estado del Usuario
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	mysql_query("UPDATE clientes SET status = {$_GET[cambiar_estado]} WHERE clave = {$_GET[cliente]}") or die (mysql_error());
	mysql_query("CALL clientes2Memory()");
	exit();
}

if(isset($_GET[eliminar_cliente])){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	$strSQL = "UPDATE clientes SET status = 2 WHERE clave = ".$_GET[eliminar_cliente];
	mysql_query($strSQL) or die (mysql_error());
	mysql_query("CALL clientes2Memory()");
	exit();
}

if(!isset($_GET[order])){
	$_GET[order] = "nombre";
	$_GET[direction] = "ASC";
}

if(isset($_GET[campoFiltro]) && $_GET[campoFiltro] != "0") {  //Campo de filtro definido
	if($_GET[campoFiltro] != "ubicacion") {$where .= " AND ".$_GET[campoFiltro]." like '%".$_GET[filtro_data]."%'";}
	else {$where .= " AND (municipio LIKE '%".$_GET[filtro_data]."%' OR estado LIKE '%".$_GET[filtro_data]."%' OR pais_nombre LIKE '%".$_GET[filtro_data]."%')";}
}
else{ //Campo de filtro no definido. Filtrar TODOS los campos
	if(isset($_GET[campoFiltro])) { $where .= " AND (clave LIKE '%".$_GET[filtro_data]."%' OR nombre LIKE '%".$_GET[filtro_data]."%' OR municipio LIKE '%".$_GET[filtro_data]."%' OR estado LIKE '%".$_GET[filtro_data]."%' OR pais_nombre LIKE '%".$_GET[filtro_data]."%')"; }
}

if(isset($_GET[status]) && $_GET[status] != "x"){
	$where .= " AND status = {$_GET[status]}";
}

if(isset($_GET[grupos]) && $_GET[grupos] != "0"){
	$where .= " AND grupo = '{$_GET[grupos]}'";
}

if(!isset($_GET[registros])){
	$per_page = 20;
	$_GET[registros] = 20;
} else {
	$per_page = $_GET[registros];
}

$strSQL1 = "SELECT * FROM clientes_memory c LEFT JOIN paises ON paises.id = c.pais WHERE 1 ".$where." AND status <> 2";
require_once('funciones/kgPager.class.php');
$sql = mysql_query($strSQL1) or die ($strSQL1.mysql_error());
$total_records = mysql_num_rows($sql);

$kgPagerOBJ = new kgPager();
$kgPagerOBJ -> pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$sql = $strSQL1." ORDER BY {$_GET[order]} {$_GET[direction]}, clave ASC LIMIT ".$kgPagerOBJ -> start.", ".$kgPagerOBJ -> per_page;
$clientes = mysql_query($sql) or die ($sql.mysql_error());
//Parámetros para el ícono "REGRESAR" del menú superior
foreach($_GET as $k => $v){
	$params .= "&{$k}={$v}";
}
$params = substr($params,1,strlen($params));
$_SESSION[start] = "comuni-k.php?".$params;
//Fin de parámetros
//Inicia configuración
//RESTRICCION
if(Administrador() || Ventas()){
	$o = array(
						 "0" => "<a href='?section=clientes_formulario&registrar' ><img src='imagenes/add.png' /> Agregar Cliente</a>"
						 );
	pop($o,"clientes");
}
titleset("Gesti&oacute;n de Clientes");
$_POST[printable] = 0;
filter_display("clientes");
//Fin de configuración
?>
<script>	
function CambiarEstado(obj,id){
	var estado_nuevo = obj.options[obj.selectedIndex].value;
	if(estado_nuevo == 1){
		var estado_viejo = 0;
	} else {
		var estado_viejo = 1;
	}
	if(confirm(String.fromCharCode(191)+"Esta seguro que desea cambiar el estado de este cliente"+String.fromCharCode(63))){
		var url = "clientes.php?cambiar_estado="+estado_nuevo+"&cliente="+id;
		var r = procesar(url);
		if(r == ""){
			alert("Se ha realizado el cambio satisfactoriamente.");
		} else {
			alert("No se pudo realizar el cambio.\nIntente de nuevo.\n"+r);
			obj.selectedIndex = estado_viejo;
		}
	} else {
		obj.selectedIndex = estado_viejo;
	}
}

function eliminar(id){
	if(confirm("¿Seguro que desea eliminar este cliente?")){
		var url = "clientes.php?eliminar_cliente="+id;
		var r = procesar(url);
		if(r == ""){
			document.getElementById("tr_"+id).style.display="none";
		} else {
			alert("Ha ocurrido un error y el cliente no pudo se eliminado.\nIntente nuevamente más tarde.\n"+r);
		}
	}
}

</script>
<?php editNowJava(); ?>
<form name="filtro" method="GET" action="" id="filtro" class="<?=$_POST[class_filtro]?>">
  <p> Filtro:
    <input name="filtro_data" type="text" id="filtro_data" value="<?=$_GET[filtro_data]?>" size="14" />
<select name="campoFiltro" id="campoFiltro">
      <option value="0"  <?=selected($_GET[campoFiltro],0)?>>Cualquier campo</option>
      <option value="clave" <?=selected($_GET[campoFiltro],"clave")?>>Clave</option>
      <option value="nombre" <?=selected($_GET[campoFiltro],"nombre")?>>Nombre</option>      
    </select>
    &nbsp;&nbsp;&nbsp;Grupo:
    <select name="grupos" id="grupos">
      <option value="0">Todos</option>
      <?php
$grupos_s = "SELECT DISTINCT grupo FROM clientes_memory WHERE grupo != ' ' ORDER BY grupo ASC";
$grupos = mysql_query($grupos_s)or die(mysql_error());
while($row2 = mysql_fetch_assoc($grupos)){
?>
      <option value="<?=$row2[grupo]?>" <?=selected($_GET[grupos],$row2[grupo])?>>
      <?=$row2[grupo]?>
      </option>
      <?php
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
    <select name="status" id="select">
      <option value="x">Todos</option>
      <option value="0" <?=selected($_GET[status],"0")?> >Activos</option>
      <option value="1" <?=selected($_GET[status],"1")?> >Inactivos</option>
    </select>
&nbsp;&nbsp;&nbsp; Ordenar por:
    <select name="order" id="order">
      <option value="nombre" <?=selected("nombre",$_GET[order])?>>Nombre</option>
      <option value="grupo" <?=selected("grupo",$_GET[order])?>>Grupo</option>
      <option value="clave" <?=selected("clave",$_GET[order])?>>Clave</option>
      <option value="status" <?=selected("status",$_GET[order])?>>Status</option>
    </select>
<select name="direction" id="direction">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>
    &nbsp;&nbsp;&nbsp;
    <input type="submit" name="submit" id="submit" value="Crear lista de clientes" />
    <input name="section" type="hidden" id="section" value="clientes" />
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
  <?php if($total_records > 0) { ?>
</form>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
    <th>Clave</a></th>
    <th>Grupo</th>
    <th>Nombre</th>
    <th>Ciudad</th>
    <th>Tel&eacute;fono</th>
    <th>Status</th>
    <?php if(Administrador()) { ?>
    <th>Facturas</th>
    <?php
    }
		
		if(Administrador() || CCC()){
    ?>
    <th>Abonar</th>
    <?php
		}
		
		if(Administrador() || Ventas()){
		?>
    <th>Editar</th>
    <?php
		}
		
		if(Administrador()){
		?>
    <th>&nbsp;</th>
    <?php } ?>
  </tr>
  <?php
$i=0;
while($r = mysql_fetch_assoc($clientes)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
?>
  <tr id="tr_<?=$r[clave]?>" class="<?=$class?>" onmouseover="this.setAttribute('class', 'tr_list_over');" onmouseout="this.setAttribute('class', '<?=$class?>');">
    <td style="text-align:center"><?=$r[clave]?></td>
    <td><?=$r['grupo']?></td>
    <td><?=$r['nombre']?></td>
    <td style="text-align:center"><?=$r[municipio]?></td>
    <td style="text-align:center"><?=$r[telefonos]?></td>
    <td width="10" style="text-align:center; font-weight:bold">
    <?php if(Administrador() || Ventas()){ ?>
    	<select name="select" onchange="CambiarEstado(this,<?=$r[clave]?>);">
        <option value="0" <?=selected(0,$r[status]);?>>Activo</option>
        <option value="1" <?=selected(1,$r[status]);?>>Inactivo</option>
      </select>
    <?php
    } else {
			if($r[status] == 0){ echo "Activo"; }
			if($r[status] == 1){ echo "Inactivo"; }
		}
		?>
    </td>
    <?php
    if(Administrador()){
		?>
    <td style="text-align:center"><a href="?section=reporte_cliente&cliente=<?=$r[clave]?>"><img src="imagenes/document_icon.gif" alt="Ver facturas" /></a></td>
    <?php
		}
		if(Administrador() || CCC()){
		?>
    <td style="text-align:center"><a href="?section=ingresos_por_cliente&clave=<?=$r[clave]?>"><img src="imagenes/bundle-16x16x32b.png" width="16" height="16" /></a></td>
    <?php
		}
		if(Administrador() || Ventas()){ ?>
    <td style="text-align:center"><a href="?section=clientes_formulario&modificar=<?=$r[clave]?>&pagina=<?=$_GET[pagina]?>"><img src="imagenes/pencil.png" alt="Modificar datos"/></a></td>
    <?php
		}
		if(Administrador()){
		?>
    <td><a href="javascript: eliminar(<?=$r[clave]?>);"><img src="imagenes/deleteX.png" /></a></td>
    <?php } ?>
  </tr>
  <?php
    $i++;
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