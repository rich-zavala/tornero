<?php
if(isset($_GET[cancelar])){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	
	$s = "UPDATE notas_de_credito SET status = 1 WHERE folio = '{$_GET[cancelar]}' AND tipo='cliente'";
	mysql_query($s) or die (mysql_error());
	$s = "UPDATE ingresos SET status = 1 WHERE id = '{$_GET[id_mov]}'";
	mysql_query($s) or die (mysql_error());
	echo 1;
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
	$where = "AND folio LIKE '%{$_GET[folio]}%'";
}

if(isset($_GET[id_cliente]) && $_GET[id_cliente] != "0"){
	$where .= " AND notas_de_credito.persona = '$_GET[id_cliente]'";
}

if(isset($_GET[status]) && $_GET[status] != "x"){
	$where .= " AND notas_de_credito.status = {$_GET[status]}";
}

$strSQL1 = "
SELECT
ingresos.importe,
ingresos.referencia,
ingresos.fecha,
notas_de_credito.id_mov,
notas_de_credito.folio,
notas_de_credito.status,
clientes.nombre
FROM
ingresos_detalle
INNER JOIN ingresos ON ingresos_detalle.id_ingreso = ingresos.id
INNER JOIN movimientos_bancos ON movimientos_bancos.id_mov = ingresos.id
INNER JOIN notas_de_credito ON notas_de_credito.id_mov = ingresos.id
INNER JOIN clientes ON clientes.clave = notas_de_credito.persona
WHERE
notas_de_credito.tipo = 'cliente'
{$where}
GROUP BY
notas_de_credito.id";
//echo nl2br($strSQL1);

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
titleset("Gesti&oacute;n de Notas de Cr&eacute;dito para Clientes");
if(Administrador() || Ventas()){
	$o = array(
						 "0" => "<a href='?section=notas_c_formulario' ><img src='imagenes/add.png' /> Registrar Nota de Cr&eacute;dito</a>"
						 );
	pop($o,"notas_c");
}
filter_display("notas_c");
//Fin de configuración
?>
<script type="text/javascript" language="javascript">
function cancelar_compra(folio,id_mov,obj){
	var e = obj.value;
	if(e == 1){
		if(confirm("¿Seguro que quiere cancelar esta Nota de Crédito?")){
			url = "notas_c.php?cancelar="+folio+"&id_mov="+id_mov;
			var r = procesar(url);
			if(r == '1'){
				document.getElementById('spann'+folio).innerHTML = "Cancelada";
			}
		}
		else{
			obj.selectedIndex = 0;
		}
	}
}
</script>
<form name="filtro" method="get" action="" id="filtro"  class="<?=$_POST[class_filtro]?>">
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
    Status:
    <select name="status" id="status">
      <option value="x">Todos</option>
      <option <?=selected($_GET[status],"0")?> value="0">Normal</option>
      <option <?=selected($_GET[status],"1")?> value="1">Cancelada</option>
    </select>
&nbsp;&nbsp;&nbsp; Mostrar:
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
      <option value="fecha" <?=selected($_GET[order],"fecha")?>>Fecha</option>
      <option value="importe" <?=selected($_GET[order],"importe")?>>Importe</option>
      <option value="status" <?=selected("status",$_GET[order])?>>Status</option>
    </select>
    <select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>
    <input name="section" type="hidden" id="section" value="notas_c" />
    <input name="Buscar" type="submit" value="Crear lista de Ventas" style="font-size:11px"/>
  </p>
  <p>Encontrados:
    <?=$total_records?>
    coincidencias</p>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr id="tr_<?=$r['id']?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <th>Folio</th>
    <th>Factura(s)</th>
    <th>Cliente</th>
    <th>Fecha</th>
    <th>Importe</th>
    <th>Status</th>
  </tr>
  <?php
while ($r = mysql_fetch_assoc($query)){
	if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1";
	$i++;

//Buscar Folios
  $fs = "SELECT factura, moneda FROM ingresos_detalle INNER JOIN facturas ON folio = factura WHERE id_ingreso = '{$r[id_mov]}'";
  $fq = mysql_query($fs) or die (mysql_error());
  if (mysql_num_rows($fq) == 1){
		$fr = mysql_fetch_assoc($fq);
		$folios = $fr[factura];
		$moneda = $fr[moneda];
  } else {
	  $folios = "<i>Varias</i>";
  }
?>
  <tr id="tr_<?=$r[folio]?>" class="<?=$class?>" onMouseOver="this.setAttribute('class', 'tr_list_over');" onMouseOut="this.setAttribute('class', '<?=$class?>');">
    <td style="text-align:center"><a href="?section=notas_c_detalle&folio=<?=$r[folio]?>"><?=$r[folio]?></a></td>
    <td style="text-align:center" ><?=$folios?></td>
    <td><?=$r[nombre]?></td>
    <td><?=FormatoFecha($r[fecha])?></td>
    <td style="text-align:right"><?=money($r['importe'])?><?=mon($moneda)?></td>
    <td id="spann<?=$r[folio]?>" style="font-weight:bold; text-align:center">
		<?php
    if($r[status] == "1"){
			echo "Cancelada";
		} else {
			if(Administrador() || Ventas()){
		?>
      <select id="id_cancelada" onchange="cancelar_compra('<?=$r[folio]?>','<?=$r[id_mov]?>',this);">
        <option value="0">Normal</option>
        <option value="1">Cancelada</option>
      </select>
    <?php 
			} else {
				echo "Normal";
			}
		}
		?>
    </td>
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