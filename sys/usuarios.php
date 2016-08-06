<?php
if (isset($_GET[cambiar_estado])) { //Cambiar el estado del Usuario
    include ("funciones/basedatos.php");
    include ("funciones/funciones.php");
    mysql_query("UPDATE usuarios SET status = {$_GET[cambiar_estado]} WHERE id_usuario = {$_GET[id_usuario]}") or die(mysql_error());
    exit();
}
if (isset($_GET[eliminar_usuario])) {
    include ("funciones/basedatos.php");
    include ("funciones/funciones.php");
    $strSQL = "UPDATE usuarios SET username ='ELIMINADO', status = 2 WHERE id_usuario = " . $_GET['eliminar_usuario'];
    mysql_query($strSQL) or die(mysql_error());
    exit();
}
if (isset($_POST['eliminar'])) { //Eliminar usuarios MASIVAMENTE (En realidad no se elimina. Slo se cambia el nombre de usuario y al status 2)
    for ($i = 0;$i < count($_POST['eliminar']);$i++) {
        // Elimino los datos de la base de datos.
        $strSQL = "UPDATE usuarios SET username ='ELIMINADO', status = 2 WHERE id_usuario = " . $_POST['eliminar'][$i];
        mysql_query($strSQL);
    }
}
if (isset($_GET[status]) && $_GET[status] != "x") {
    $where.= " AND status = {$_GET[status]}";
}
if (!isset($_GET[order])) {
    $_GET[order] = "nombre";
    $_GET[direction] = "ASC";
}
if ($_GET['usuario'] != "") {
    $where.= " AND username LIKE '%{$_GET['usuario']}%' OR nombre LIKE '%{$_GET['usuario']}%' OR correo LIKE '%{$_GET['usuario']}%'";
}
$strSQL1 = "SELECT u. * , tu.descripcion AS 'tipousuario' 
			FROM usuarios u
			LEFT JOIN tipos_usuarios tu ON tu.id_tipousuario = u.id_tipousuario
			WHERE 1
			{$where}
			AND u.status <> 2";
$usuarios = mysql_query($strSQL1) or die(nl2br($strSQL1) . "<p>" . mysql_error());
//Opciones
if (Administrador() || CCC() || Ventas() || Compras()) {
    $o = array("0" => "<a href='?section=usuarios_formulario&registrar' ><img src='imagenes/usuarios_agregar.gif' /> Agregar Usuario</a>");
}
pop($o, "usuarios_gestion");
// FIN de Opciones

if (!isset($_GET['registros'])) {
    $per_page = 20;
    $_GET[registros] = 20;
} else {
    $per_page = $_GET['registros'];
}
require_once ('funciones/kgPager.class.php');
$sql = mysql_query($strSQL1) or die($strSQL1 . mysql_error());
$total_records = mysql_num_rows($sql);
$kgPagerOBJ = new kgPager();
$kgPagerOBJ->pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$usuarios = mysql_query($strSQL1 . "  ORDER BY {$_GET[order]} {$_GET[direction]}, u.username ASC LIMIT " . $kgPagerOBJ->start . ", " . $kgPagerOBJ->per_page . "") or die(mysql_error());
//Parmetros para el cono "REGRESAR" del men superior
foreach ($_GET as $k => $v) {
    if ($k != "eliminar_usuario" && $k != "eliminar") { //Evitar eliminar usuario nuevamente
        $params.= "&{$k}={$v}";
    }
}
$params = substr($params, 1, strlen($params));
$_SESSION[start] = "comuni-k.php?" . $params;
titleset("Gesti&oacute;n de Usuarios");
filter_display("usuarios");
//Fin de parmetros

?>
<script language="JavaScript" type="text/javascript">
function Seleccionar(f, valor){
	for (i=0; i<f.elements.length; i++) {
		objeto = f.elements[i];
		if(objeto.type == "checkbox" && objeto.name == "eliminar[]"){
			objeto.checked = valor;
		}
	}
}

function CambiarEstado(obj,user){
	var estado_nuevo = obj.options[obj.selectedIndex].value;
	if(estado_nuevo == 1){
		var estado_viejo = 0;
	} else {
		var estado_viejo = 1;
	}
	if(confirm(String.fromCharCode(191)+"Esta seguro que desea cambiar el estado de este usuario"+String.fromCharCode(63))){
		var url = "usuarios.php?cambiar_estado="+estado_nuevo+"&id_usuario="+user;
		var r = procesar(url);
		if(r == ""){
			alert("Se ha realizado el cambio satisfactoriamente.");
		} else {
			alert("No se pudo realizar el cambio.
Intente de nuevo ms tarde.
"+r);
			obj.selectedIndex = estado_viejo;
		}
	} else {
		obj.selectedIndex = estado_viejo;
	}
}

function eliminar(id){
	if(confirm("Seguro que desea eliminar este usuario?")){
		var url = "usuarios.php?eliminar_usuario="+id;
		var r = procesar(url);
		if(r == ""){
			document.getElementById("tr_"+id).style.display="none";
		} else {
			alert("Ha ocurrido un error y el usuario no pudo se eliminado.
Intente nuevamente ms tarde.
"+r);
		}
	}
}
</script>
<form name="filtro" method="GET" action="" id="filtro" class="<?=$_POST[class_filtro] ?>">
  <p>Usuario Espec&iacute;fico:
  <input name="usuario" type="text" id="usuario" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[usuario] ?>" size="14"/>
&nbsp;&nbsp;&nbsp;Mostrar:
<select name="registros" id="registros">
  <option value="10" <?=selected($_GET[registros], 10) ?>>10</option>
  <option value="20" <?=selected($_GET[registros], 20) ?>>20</option>
  <option value="50" <?=selected($_GET[registros], 50) ?>>50</option>
  <option value="100" <?=selected($_GET[registros], 100) ?>>100</option>
  <option value="500" <?=selected($_GET[registros], 500) ?>>500</option>
  <option value="0" <?=selected($_GET[registros], 0) ?>>Todos</option>
</select>
  </p>
  <p>Status:
    <select name="status" id="status">
      <option value="x">Todos</option>
      <option value="0" <?=selected($_GET[status], "0") ?> >Activos</option>
      <option value="1" <?=selected($_GET[status], "1") ?> >Inactivos</option>
    </select>
&nbsp;&nbsp;&nbsp; Ordenar por:
    <select name="order" id="select">
      <option value="nombre" <?=selected("nombre", $_GET[order]) ?>>Nombre</option>
      <option value="fecha_registro" <?=selected("fecha_registro", $_GET[order]) ?>>Registro</option>
      <option value="tipousuario" <?=selected("tipousuario", $_GET[order]) ?>>Tipo</option>
      <option value="status" <?=selected("status", $_GET[order]) ?>>Estado</option>
    </select>
    <select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC", $_GET[direction]) ?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC", $_GET[direction]) ?>>Descendente</option>
    </select>
    &nbsp;&nbsp;
    <input name="section" type="hidden" id="section" value="usuarios" />
  <input name="Buscar" type="submit" value="Crear lista de Usuarios" style="font-size:11px"/>
  </p>
  <p>Encontrados: <?=$total_records ?> coincidencias</p>
</form>
<?php if (mysql_num_rows($usuarios) > 0) { ?>
<form action="?section=<?=$_GET[section] ?>" method="post" name="form1" id="form1" onsubmit="return ValidaForm(this);">
  <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
    <tr>
      <?php if (Administrador() || Ventas() || Compras()) { ?><th><input type="checkbox" name="checkbox" id="checkbox" onclick="Seleccionar(form1, this.checked);" /></th><?php
    } ?>
      <th>Nombre</th>
      <th>Registro</th>
      <th>Tipo</th>
      <th>Estado</th>
      <?php if (Administrador() || CCC() || Ventas() || Compras()) { ?>
      <th>&nbsp;</th>
      <?php
        if (Administrador() || Ventas() || Compras()) {
?>
      <th>&nbsp;</th>
			<?php
        }
    } ?>
    </tr>
    <?php $i = 0;
    while ($r = mysql_fetch_assoc($usuarios)) {
        if ($i % 2 == 0) $class = "tr_list_0";
        else $class = "tr_list_1"; ?>
    <tr id="tr_<?=$r['id_usuario'] ?>" class="<?=$class ?>" onmouseover="this.setAttribute('class', 'tr_list_over');" onmouseout="this.setAttribute('class', '<?=$class ?>');">
      <?php if (Administrador() || Ventas() || Compras()) { ?><td style="text-align:center"><input name="eliminar[]" type="checkbox" id="eliminar[]" value="<?=$r['id_usuario'] ?>" /></td><?php
        } ?>
      <td nowrap="nowrap"><b>
        <?=htmlentities($r['nombre']) ?>
        </b><br />
        <span style="color:#009999;">[
        <?=$r['username'] ?>
        ]</span><br />
        <span class="correo">
        <?=$r['correo'] ?>
        </span></td>
      <td style="text-align:center"><?=FormatoFecha2($r['fecha_registro']) ?></td>
      <td><?=$r[tipousuario] ?></td>
      <td>
      	<?php if (Administrador() || Ventas() || Compras()) { ?>
      	<select onchange="CambiarEstado(this,<?=$r['id_usuario'] ?>);">
          <option value="0" <?=selected(0, $r[status]); ?>>Activo</option>
          <option value="1" <?=selected(1, $r[status]); ?>>Inactivo</option>
      	</select>
        <?php
        } else {
            if ($r[status] == 0) {
                echo "<b>Activo</b>";
            } else {
                echo "<b>Inactivo</b>";
            }
        }
?>
      </td>
      <?php if (Administrador() || CCC() || Ventas() || Compras()) { ?>
      <td><a href="?section=usuarios_formulario&modificar=<?=$r['id_usuario'] ?>"><img src="imagenes/usuarios_modificar.gif"/></a></td>
      <?php
            if (Administrador() || Ventas() || Compras()) {
?>
      <td><a href="javascript:eliminar(<?=$r['id_usuario'] ?>);"><img src="imagenes/usuarios_eliminar.gif" /></a></td>
      <?php
            }
        } ?>
    </tr>
    <?php $i++;
    } ?>
  </table>
</form>
<?php if (Administrador() || Ventas() || Compras()) { ?>
<p style="text-align:center"><a href="javascript:if(confirm('Est seguro de querer eliminar a todos los usuarios seleccionados?')){document.form1.submit();}">Eliminar seccionados</a></p>
<?php
    } ?>
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