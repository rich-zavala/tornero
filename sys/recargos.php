<?php
@session_start();
if (!isset($_SESSION) or !isset($_SESSION['nombre']) or !isset($_SESSION['id_tipousuario']))
  header("Location: index.php");

//Cambiar el estado
if (isset($_GET['cambiar_estado']))
{
	include("funciones/basedatos.php");
	include("KREATOR-USUARIOS-ACCESS.php");
	if(!Administrador()) exit;
	$db->update('recargos', array( 'activo' => $_GET['cambiar_estado'] ), array( 'id' => $_GET['id'] ) );
	exit();
}

//Filtros
if(isset($_GET['activo']) && $_GET['activo'] != "x")
  $where.= " AND activo = {$_GET['activo']}";

if(!isset($_GET['order']))
{
	$_GET['order'] = "etiqueta";
	$_GET['direction'] = "ASC";
}

if($_GET['etiqueta'] != "")
  $where.= " AND etiqueta LIKE '%{$_GET['etiqueta']}%'";

$s = "SELECT * FROM recargos WHERE 1 {$where} AND activo <> 2";
$registros = mysql_query($s) or die(nl2br($s) . "<p>" . mysql_error());

//Opciones
$o = array("0" => "<a href='?section=recargos_formulario&registrar' ><img src='imagenes/add.png' /> Agregar Recargo</a>");
pop($o, "recargos_gestion");
// FIN de Opciones

//Paginación
if(!isset($_GET['registros']))
  $per_page = $_GET['registros'] = 20;
else
  $per_page = $_GET['registros'];

require_once ('funciones/kgPager.class.php');
$sql = mysql_query($s) or die($s . mysql_error());
$total_records = mysql_num_rows($sql);
$kgPagerOBJ = new kgPager();
$kgPagerOBJ->pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$registros = mysql_query($s . "  ORDER BY {$_GET[order]} {$_GET[direction]}, etiqueta ASC LIMIT " . $kgPagerOBJ->start . ", " . $kgPagerOBJ->per_page . "") or die(mysql_error());
//FIN de Paginación

//Parmetros para el cono "REGRESAR" del men superior
foreach ($_GET as $k => $v)
	if ($k != "eliminar_usuario" && $k != "eliminar") //Evitar eliminar usuario nuevamente
		$params.= "&{$k}={$v}";

$params = substr($params, 1, strlen($params));
$_SESSION[start] = "comuni-k.php?" . $params;
titleset("Gesti&oacute;n de Recargos");
filter_display("recargos");
//FIN de parmetros

?>
<script language="JavaScript" type="text/javascript">
function CambiarEstado(obj, user)
{
	var estado_nuevo = obj.options[obj.selectedIndex].value;
	var estado_viejo = (estado_nuevo == 1) ? 0 : 1;	
	var url = "recargos.php?cambiar_estado=" + estado_nuevo + "&id=" + user;
	var r = procesar(url);
	if(r != "")
	{
		alert("No se pudo realizar el cambio. Intente de nuevo ms tarde." + r);
		obj.selectedIndex = estado_viejo;
	}
}

function eliminar(id)
{
	if(confirm("Confirme la eliminación de este registro."))
	{
		var url = "recargos.php?cambiar_estado=2&id=" + id;
		var r = procesar(url);
		if(r == "")
			document.getElementById("tr_"+id).style.display ="none";
		else
			alert("Ha ocurrido un error y el registro no pudo se eliminado. Intente nuevamente ms tarde. " + r);
	}
}
</script>
<form name="filtro" method="GET" action="" id="filtro" class="<?=$_POST['class_filtro'] ?>">
  <p>
		Etiqueta:
		<input name="etiqueta" type="text" id="etiqueta" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET['etiqueta'] ?>" size="14"/>
		&nbsp;&nbsp;&nbsp;Mostrar:
		<select name="registros" id="registros">
			<option value="10" <?=selected($_GET['registros'], 10) ?>>10</option>
			<option value="20" <?=selected($_GET['registros'], 20) ?>>20</option>
			<option value="50" <?=selected($_GET['registros'], 50) ?>>50</option>
			<option value="100" <?=selected($_GET['registros'], 100) ?>>100</option>
			<option value="500" <?=selected($_GET['registros'], 500) ?>>500</option>
			<option value="0" <?=selected($_GET['registros'], 0) ?>>Todos</option>
		</select>
  </p>
  <p>
		Estatus:
    <select name="activo" id="activo">
      <option value="x">Todos</option>
      <option value="1" <?=selected($_GET['activo'], "1") ?> >Activos</option>
      <option value="0" <?=selected($_GET['activo'], "0") ?> >Inactivos</option>
    </select>
		&nbsp;&nbsp;&nbsp; Ordenar por:
    <select name="order" id="select">
      <option value="etiqueta" <?=selected("etiqueta", $_GET['order']) ?>>Etiqueta</option>
      <option value="porcentaje" <?=selected("porcentaje", $_GET['order']) ?>>Porcentaje</option>
      <option value="fechaRegistro" <?=selected("fechaRegistro", $_GET['order']) ?>>Registro</option>
      <option value="activo" <?=selected("activo", $_GET['activo']) ?>>Estado</option>
    </select>
    <select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC", $_GET['direction']) ?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC", $_GET['direction']) ?>>Descendente</option>
    </select>
    &nbsp;&nbsp;
    <input name="section" type="hidden" id="section" value="recargos" />
		<input name="Buscar" type="submit" value="Crear lista de registros" style="font-size:11px"/>
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
</form>

<?php if(mysql_num_rows($registros) > 0){ ?>
<form action="?section=<?=$_GET['section']?>" method="post" name="form1" id="form1" onsubmit="return ValidaForm(this);">
  <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" width="600">
    <tr>
      <th>Etiqueta</th>
      <th>Porcentaje</th>
      <th>Fecha de registro</th>
      <th>Estado</th>
      <th width="1">&nbsp;</th>
      <th width="1">&nbsp;</th>
    </tr>
    <?php while($r = mysql_fetch_assoc($registros)){ ?>
		
    <tr id="tr_<?=$r['id'] ?>">
      <td nowrap="nowrap"><?=htmlentities($r['etiqueta'])?></td>
      <td nowrap="nowrap" style="text-align:center"><?=number_format((float)$r['porcentaje'], 2, '.', '')?> %</td>
      <td style="text-align:center"><?=FormatoFecha2($r['fechaRegistro'])?></td>
      <td style="text-align:center">
      	<select onchange="CambiarEstado(this,<?=$r['id'] ?>);">
          <option value="1" <?=selected(1, $r['activo'])?>>Activo</option>
          <option value="0" <?=selected(0, $r['activo'])?>>Inactivo</option>
      	</select>
      </td>
      <td><a href="?section=recargos_formulario&modificar=<?=$r['id']?>"><img src="imagenes/pencil.png"/></a></td>
      <td><a href="javascript:eliminar(<?=$r['id'] ?>);"><img src="imagenes/cancel_editnow.png" /></a></td>
    </tr>
    <?php
    }
		?>
  </table>
</form>
<div style="text-align:center">
<?php
    echo '<p id="pager_links">';
    echo $kgPagerOBJ->first_page;
    echo $kgPagerOBJ->previous_page;
    echo $kgPagerOBJ->page_links;
    echo $kgPagerOBJ->next_page;
    echo $kgPagerOBJ->last_page;
    echo '</p>';
?>
</div>
<?php
} ?>