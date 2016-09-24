<?php
if(!function_exists("Administrador")){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	include("KREATOR-USUARIOS-ACCESS.php");
}
	
Administrador('index.php');

if(isset($_GET['activo'])){
	$s = "UPDATE recargos SET activo = IF(activo = 1, 0, 1) WHERE id = '{$_GET['activo']}'";
	mysql_query($s) or die (mysql_error());
	echo 1;
	exit();
}

if(isset($_GET['eliminar'])){
	$s = "DELETE FROM recargos WHERE id = ".$_GET['eliminar'];
	mysql_query($s) or die (mysql_error());
	echo 1;
	exit();
}

if(!isset($_GET['registros'])){
	$per_page = 20;
	$_GET['registros'] = 20;
} else {
	$per_page = $_GET['registros'];
}

if(!isset($_GET['order'])){
	$_GET['order'] = "etiqueta";
	$_GET['direction'] = "DESC";
}

if($_GET['etiqueta'] != ""){
	$where = "AND etiqueta LIKE '%{$_GET['etiqueta']}%'";
}

if(isset($_GET['_activo']) && $_GET['_activo'] != "x"){
	$where .= " AND activo = '{$_GET['_activo']}'";
}

$strSQL1 = "
SELECT * FROM recargos WHERE 1 {$where}";
//echo nl2br($strSQL1);

require_once('funciones/kgPager.class.php');
$sql = mysql_query($strSQL1) or die ($strSQL1.mysql_error());
$total_records = mysql_num_rows($sql);

$kgPagerOBJ = new kgPager();
$kgPagerOBJ -> pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$sql = $strSQL1." ORDER BY {$_GET['order']} {$_GET['direction']}, etiqueta DESC LIMIT ".$kgPagerOBJ -> start.", ".$kgPagerOBJ -> per_page;
$query = mysql_query($sql) or die (nl2br($sql).mysql_error());

//Parámetros para el ícono "REGRESAR" del menú superior
foreach($_GET as $k => $v){
	$params .= "&{$k}={$v}";
}
$params = substr($params,1,strlen($params));
$_SESSION['start'] = "comuni-k.php?".$params;
//Fin de parámetros
//Inicia configuración
$_POST['printable'] = 0;
titleset("Gesti&oacute;n de Recargos");
if(Administrador()){
	$o = array(
						 "0" => "<a href='?section=recargos_formulario&registrar' ><img src='imagenes/add.png' /> Registrar Recargo</a>"
						 );
	pop($o,"recargos");
}
filter_display("recargos");
//Fin de configuración
?>
<script type="text/javascript" language="javascript">
function cambiar(id, obj){
	var e = obj.value;
	url = "recargos.php?activo=" + id;
	var r = procesar(url);
	if(r != '1')
	{
		alert("Ha ocurrido un error.");
		obj.disabled = true;
	}
}

function eliminar(id){
	if(confirm("¿Seguro que desea eliminar este registro?")){
		var url = "recargos.php?eliminar="+id;
		var r = procesar(url);
		if(r == '1')
			document.getElementById("tr_" + id).style.display="none";
		else
		{
			alert("Ha ocurrido un error.");
			obj.disabled = true;
		}
	}
}
</script>
<form name="filtro" method="get" action="" id="filtro"  class="<?=$_POST['class_filtro']?>">
  <p> Etiqueta:
    <input name="etiqueta" type="text" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET['etiqueta']?>" size="8"/>
    &nbsp;&nbsp;&nbsp;
    Activo:
    <select name="_activo">
      <option value="x">Todos</option>
      <option <?=selected($_GET['_activo'],"1")?> value="1">Activos</option>
      <option <?=selected($_GET['_activo'],"0")?> value="0">Inactivos</option>
    </select>
&nbsp;&nbsp;&nbsp; Mostrar:
<select name="registros" id="registros">
      <option value="10" <?=selected($_GET['registros'],10)?>>10</option>
      <option value="20" <?=selected($_GET['registros'],20)?>>20</option>
      <option value="50" <?=selected($_GET['registros'],50)?>>50</option>
      <option value="100" <?=selected($_GET['registros'],100)?>>100</option>
      <option value="500" <?=selected($_GET['registros'],500)?>>500</option>
      <option value="0" <?=selected($_GET['registros'],0)?>>Todos</option>
    </select>
  </p>
  <p>Ordenar por:
		<select name="order">
      <option value="etiqueta" <?=selected($_GET['order'],"etiqueta")?>>Etiqueta</option>
      <option value="porcentaje" <?=selected($_GET['order'],"porcentaje")?>>Porcentaje</option>
      <option value="activo" <?=selected($_GET['activo'], "activo")?>>Activo</option>
    </select>
    <select name="direction">
      <option value="ASC" <?=selected("ASC",$_GET['direction'])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET['direction'])?>>Descendente</option>
    </select>
    <input name="section" type="hidden" value="recargos" />
    <button type="submit" style="font-size:11px">Crear listado</button>
  </p>
  <p>Encontrados:
    <?=$total_records?>
    coincidencias</p>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr id="tr_<?=$r['id']?>">
    <th>Etiqueta</th>
    <th>Porcentaje</th>
    <th>Fecha de creación</th>
    <th>Activo</th>
    <th></th>
  </tr>
  <?php
	while ($r = mysql_fetch_assoc($query)){
	?>
  <tr id="tr_<?=$r['id']?>">
    <td><a href="?section=recargos_formulario&modificar=<?=$r['id']?>"><?=$r['etiqueta']?></a></td>
    <td style="text-align:right"><?=money($r['porcentaje'])?></td>
    <td><?=FormatoFecha($r['fechaRegistro'])?></td>
    <td>
      <select onchange="cambiar('<?=$r['id']?>',this);">
        <option value="1" <?=selected("1",$r['activo'])?>>Activo</option>
        <option value="0" <?=selected("0",$r['activo'])?>>Inactivo</option>
      </select>
    </td>
    <td><a href="javascript: eliminar(<?=$r['id']?>);"><img src="imagenes/deleteX.png" /></a></td>
  </tr>
  <?php }?>
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