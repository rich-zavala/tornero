<?php
if (isset($_GET[cambiar_estado])) { //Cambiar el estado del Usuario
    include ("funciones/basedatos.php");
    include ("funciones/funciones.php");
    mysql_query("UPDATE productos SET status = {$_GET[cambiar_estado]} WHERE id_producto = {$_GET[producto]}") or die(mysql_error());
    exit();
}
if (isset($_GET[eliminar_producto])) {
    include ("funciones/basedatos.php");
    include ("funciones/funciones.php");
    $strSQL = "UPDATE productos SET status = 2 WHERE id_producto = " . $_GET[eliminar_producto];
    mysql_query($strSQL) or die(mysql_error());
    exit();
}
// Aplico el filtro en caso de ser necesario
if (isset($_GET[filtro_data]) && $_GET[filtro_data] != "") {
    $where.= " AND (descripcion LIKE '%" . $_GET[filtro_data] . "%' OR codigo_barras LIKE '%" . $_GET[filtro_data] . "%')";
}
if (!isset($_GET[order])) {
    $_GET[order] = "descripcion";
    $_GET[direction] = "ASC";
}
if (isset($_GET[status]) && $_GET[status] != "x") {
    $where.= " AND status = {$_GET[status]}";
}
if (!isset($_GET[registros])) {
    $per_page = 20;
    $_GET[registros] = 20;
} else {
    $per_page = $_GET[registros];
}
$strSQL1 = "SELECT * FROM productos WHERE 1 " . $where . " AND status <> 2";
require_once ('funciones/kgPager.class.php');
$sql = mysql_query($strSQL1) or die($strSQL1 . mysql_error());
$total_records = mysql_num_rows($sql);
$kgPagerOBJ = new kgPager();
$kgPagerOBJ->pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$sql = $strSQL1 . " ORDER BY {$_GET[order]} {$_GET[direction]}, BINARY(TRIM(descripcion)) ASC LIMIT " . $kgPagerOBJ->start . ", " . $kgPagerOBJ->per_page;

// echo $sql;
$productos = mysql_query($sql) or die($sql . mysql_error());
//Parámetros para el ícono "REGRESAR" del menú superior
foreach ($_GET as $k => $v) {
    $params.= "&{$k}={$v}";
}
$params = substr($params, 1, strlen($params));
$_SESSION[start] = "comuni-k.php?" . $params;
//Fin de parámetros
//Inicia configuración
$_POST[printable] = 0;
titleset("Gesti&oacute;n de Productos");
//RESTRICCION
if (Administrador() || Compras()) {
    $o = array("0" => "<a href='?section=productos_formulario&registrar' ><img src='imagenes/add.png' /> Agregar Producto</a>", "1" => "<a href='?section=listas_de_precios' ><img src='imagenes/document_icon.gif' /> Gestionar listas de precios</a>");
}
if (ComprasVentas()) {
    $o = array("0" => "<a href='?section=productos_formulario&registrar' ><img src='imagenes/add.png' /> Agregar Producto</a>");
}
pop($o, "productos");
filter_display("productos");
//Fin de configuración
//RESTRICCION
if (Administrador() || ComprasVentas() || Compras()) {
?>
<script type="text/javascript" language="javascript">
function CambiarEstado(obj,id){
	var estado_nuevo = obj.options[obj.selectedIndex].value;
	if(estado_nuevo == 1){
		var estado_viejo = 0;
	} else {
		var estado_viejo = 1;
	}
	if(confirm(String.fromCharCode(191)+"Esta seguro que desea cambiar el estado de este producto"+String.fromCharCode(63))){
		var url = "productos.php?cambiar_estado="+estado_nuevo+"&producto="+id;
		var r = procesar(url);
		if(r != ""){
			alert("No se pudo realizar el cambio. Intente de nuevo." + r);
			obj.selectedIndex = estado_viejo;
		}
	} else {
		obj.selectedIndex = estado_viejo;
	}
}

function eliminar(id){
	if(confirm("¿Seguro que desea eliminar este producto?")){
		var url = "productos.php?eliminar_producto="+id;
		var r = procesar(url);
		if(r == ""){
			document.getElementById("tr_"+id).style.display="none";
		} else {
			alert("Ha ocurrido un error y el producto no pudo se eliminado. Intente nuevamente más tarde. "+r);
		}
	}
}

function todos()
{
	$(".box_masivo").attr("checked",true);
}

function ninguno()
{
	$(".box_masivo").attr("checked",false);
}

function invertir()
{
	$(".box_masivo").each(function(){
		if($(this).attr("checked"))
		{
			$(this).attr("checked",false);
		}
		else
		{
			$(this).attr("checked",true);
		}
	});
}

$(document).ready(function() {
	 $("#masivo_form").bind("keypress", function(e) {
			 if (e.keyCode == 13) {
					 //search($("#searchTerm").attr('value'));
					 return false;
			}
	 });
}); 
</script>
<?php editNowJava(); ?>
<?php
} ?>
<form name="filtro" method="GET" action="" id="filtro" class="<?=$_POST[class_filtro] ?>">
  <p>Filtro:
    <input name="filtro_data" type="text" id="filtro_data" value="<?=$_GET[filtro_data] ?>" size="14">
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
    <select name="status" id="select">
      <option value="x">Todos</option>
      <option value="0" <?=selected($_GET[status], "0") ?> >Activos</option>
      <option value="1" <?=selected($_GET[status], "1") ?> >Inactivos</option>
    </select>
&nbsp;&nbsp;&nbsp; Ordenar por:
    <select name="order" id="order">
      <option value="codigo_barras" <?=selected("codigo_barras", $_GET[order]) ?>>C&oacute;digo de Barras</option>
      <option value="descripcion" <?=selected("descripcion", $_GET[order]) ?>>Descripci&oacute;n</option>
      <option value="status" <?=selected("status", $_GET[order]) ?>>Status</option>
      <option value="precio_publico" <?=selected("precio_publico", $_GET[order]) ?>>Precio</option>
    </select>
    <select name="direction" id="direction">
      <option value="ASC" <?=selected("ASC", $_GET[direction]) ?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC", $_GET[direction]) ?>>Descendente</option>
    </select>
    &nbsp;&nbsp;&nbsp;
    <input name="section" type="hidden" id="section" value="productos">
    <input type="submit" name="Submit" id="button" value="Crear lista de Productos">
  </p>
  <p>Encontrados: <?=$total_records ?> coincidencias</p>
  <?php if (mysql_num_rows($productos) > 0) { ?>
</form>
<form action="?section=productos_masivo" method="post" id="masivo_form" onsubmit="return false;" >
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
  	<?php if (Administrador() || ComprasVentas() || Compras()) { ?>
    <th>&nbsp;</th>
    <?php
    } ?>
    <th>C&oacute;digo de barras</th>
    <th>Descripci&oacute;n</th>
    <th>Precio</th>
    <th>IVA</th>
    <th>Status</th>
    <?php
    //RESTRICCION
    if (Administrador() || ComprasVentas() || Compras()) {
?>
    <th colspan="2">&nbsp;</th>
    <th>&nbsp;</th>
    <?php
    } ?>
  </tr>
  <?php $i = 0;
    while ($r = mysql_fetch_assoc($productos)) {
        if ($i % 2 == 0) $class = "tr_list_0";
        else $class = "tr_list_1"; ?>
  <tr id="tr_<?=$r[id_producto] ?>" class="<?=$class ?>" onmouseover="this.setAttribute('class', 'tr_list_over');" onmouseout="this.setAttribute('class', '<?=$class ?>');">
   	<?php if (Administrador() || ComprasVentas() || Compras()) { ?>
    <th><input type="checkbox" name="box[<?=$r[id_producto] ?>]" class="box_masivo"></th>
    <?php
        } ?>
    <td nowrap><?php editNow("productos", "id_producto", "codigo_barras", $r[id_producto], "codigo", $r[codigo_barras], -1, 2, "90px"); ?></td>
    <td nowrap><b><?php editNow("productos", "id_producto", "descripcion", $r[id_producto], "descripcion", $r[descripcion], -1, 2, "100%"); ?></b></td>
    <td style="white-space:nowrap; text-align:right"><?php editNow("productos", "id_producto", "precio_publico", $r[id_producto], "precio", $r[precio_publico], 3, 1, "50px"); ?></td>
    <td style="white-space:nowrap; text-align:right"><?php editNow("productos", "id_producto", "iva", $r[id_producto], "iva", $r[iva], 2, 1, "40px"); ?>%</td>
    <td width="10" nowrap>
    <?php
        //RESTRICCION
        if (Administrador() || ComprasVentas() || Compras()) {
?>
      <select name="select" onchange="CambiarEstado(this,<?=$r[id_producto] ?>);">
        <option value="0" <?=selected(0, $r[status]); ?>>Activo</option>
        <option value="1" <?=selected(1, $r[status]); ?>>Inactivo</option>
      </select>
    <?php
        } else {
            if ($r[status] == 0) {
                echo "Activo";
            }
            if ($r[status] == 1) {
                echo "Inactivo";
            }
        }
?>
    </td>
    <?php
        //RESTRICCION
        if (Administrador() || ComprasVentas() || Compras()) {
?>
    <td><a href="?section=productos_formulario&modificar=<?=$r['id_producto'] ?>" title="Editar"><img src="imagenes/pencil.png"></a></td>
    <td><a href="../librerias/barcode/bc.php?text=<?=$r['codigo_barras'] ?>" target="_<?=$r['codigo_barras']?>" title="Generar código de barras"><img src="../librerias/assets/barcode.png"></a></td>
    <td><a href="javascript: eliminar(<?=$r['id_producto'] ?>);"><img src="imagenes/deleteX.png"></a>
    <?php
        } ?>
  </tr>
  <?php
        $i++;
        // ob_flush();
        // flush();
    }
    if (Administrador() || ComprasVentas() || Compras()) {
?>
 	<tr>
  	<td colspan="10" valign="middle" style="margin:0px; padding:0px;">
    	<style>
				#botones_seleccion{width:142px; float:left; text-align:center; font-size:10px; padding:0px; border-right:solid #000 1px; background-color:#BF2D2D; color:#FFF}
				#botones_seleccion button{font-size:10px; margin:0px; padding:0px;}
      </style>
    	<div id="botones_seleccion">
	    	<span style="font-weight:bold;">Selecci&oacute;n</span><br />
        <button onclick="todos();" type="button">Todos</button>
        <button onclick="ninguno();" type="button">Ninguno</button>
        <button onclick="invertir();" type="button">Invertir</button>
      </div>
      <button onclick="document.getElementById('masivo_form').submit();" style="float:right; margin-top:4px;">Modificaci&oacute;n masiva</button>
    </td>
  </tr>
  <?php
    } ?>
</table>
</form>
<div style="text-align:center; margin-top:10px" id="_pagination">
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
} else { ?>
&nbsp;
<p style="text-align:center">No existen productos disponibles</p>
<?php
}
// ob_end_flush();
?>