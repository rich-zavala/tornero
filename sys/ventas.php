<?php
//Eliminación de factura
if(isset($_GET['eliminar']))
{
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	if($_GET['series'] == "")
		$serie= " AND serie IS NULL";
	else
		$serie= " AND serie='{$_GET['series']}'";
	
	$s = array();
	$s[] = "DELETE FROM facturas WHERE folio = '{$_GET['eliminar']}' {$serie}";
	$s[] = "DELETE FROM facturas_productos WHERE folio_factura = '{$_GET['eliminar']}' {$serie}";
	$s[] = "DELETE FROM movimientos WHERE id_tipomovimiento = 9 AND folio = '{$_GET['eliminar']}' {$serie}";
	foreach($s as $k) mysql_query($k) or die (mysql_error());
	exit();
}

//Cancelación de factura
if(isset($_GET['cancelar']))
{
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	
	if($_GET['series'] == "")
	{
		$serie= " AND serie IS NULL";
	}
	else
	{
		$serie= " AND serie = '{$_GET['series']}'";
	}
	
	//Verificar que no haya sido pagada
	$s = "SELECT
					i.referencia,
					i.id,
					IF(f.status=0,IF(SUM(IF(i.status = 0, abono, 0))=f.importe,'Saldada',IF(SUM(IF(i.status = 0, abono, 0))>0,'Abonada','Normal')),'Cancelada') estado
				FROM
				facturas f
				LEFT OUTER JOIN ingresos_detalle id ON id.factura = f.folio
				LEFT OUTER JOIN ingresos i ON i.id = id.id_ingreso
				WHERE folio = '{$_GET['cancelar']}' {$serie}
				GROUP BY f.folio";
	// echo $s; exit;
	$q = mysql_query($s) or die (mysql_error());
	$n = mysql_num_rows($q);
	$r = mysql_fetch_assoc($q);
	if($r[estado]!="Normal" or $n == 0)
	{
		if($r['referencia'] != "PAGO DE CONTADO")
		{
			echo "error";
			exit();
		}
		else
		{
			$s = "UPDATE ingresos SET status = 1 WHERE id = {$r['id']}";
			mysql_query($s) or die (mysql_error());
		}
	}
	
	$s = "UPDATE facturas SET status = 1 WHERE folio = '{$_GET['cancelar']}' {$serie}";
	mysql_query($s) or die (mysql_error());
	
	//14 nov 2015 > Las existencias se manejan a nivel de triggeres en BDD
	$db->execute("CALL facturaCancelarProductos('{$_GET[cancelar]}', '{$_GET['series']}')");
	echo 1;
	exit();
} //Termina Cancelación

/*Inicia consulta principal*/
if(!isset($_GET[registros]))
{
	$_GET[registros] = 20;
	$pagi= " LIMIT ".($_GET[pagina]*$_GET[registros]).",".$_GET[registros];
}
else
{
	$pagi= " LIMIT ".($_GET[pagina]*$_GET[registros]).",".$_GET[registros];
}

if(!isset($_GET[order])){
	$_GET[order] = "folio";
	$_GET[direction] = "DESC";
}

$where_factura = "";
if(isset($_GET[fecha1]) and strlen($_GET[fecha1]) > 0 and strlen($_GET[fecha2] > 0)){
	$where_factura .= " AND fecha_factura BETWEEN '{$_GET[fecha1]}' AND '{$_GET[fecha2]}'";
} else {
	unset($_GET[fecha1]);
	unset($_GET[fecha2]);
}

if($_GET[folio] != "") $where_factura .= " AND folio LIKE '%{$_GET[folio]}%'";
if($_GET[serie] != "") $where_factura .= " AND serie LIKE '%{$_GET[serie]}%'";
if(isset($_GET[almacen]) and $_GET[almacen] != "0") $where_factura .= " AND id_almacen = '$_GET[almacen]'";
if(isset($_GET[status]) and $_GET[status] != "x") $where_factura .= " AND f.status = '{$_GET[status]}'";
if(isset($_GET[tipo]) and $_GET[tipo] != "x") $where_factura .= " AND tipo = '{$_GET[tipo]}'";

//Si hay búsqueda por cliente, obtener sus claves
$where_clientes_array = array( -1 );
$where_clientes = "";
if(isset($_GET[cliente]) and $_GET[cliente] != "0" and strlen(trim($_GET[cliente])) > 0)
{
	$s = "SELECT clave FROM clientes_memory WHERE nombre LIKE '%{$_GET[cliente]}%' OR clave = '{$_GET[cliente]}'";
	foreach($db->fetch($s) as $r) $where_clientes_array[] = $r[clave];
	$where_clientes = " IN (" . implode(',', $where_clientes_array) . ")";
	$where_factura .= " AND id_cliente {$where_clientes}";
	$where_clientes = "WHERE clave {$where_clientes}";
	
	// echo $s;
}

//Query principal de consulta
$s = "SELECT
				a.descripcion almacen,
				fecha_factura,
				f.folio,
				f.serie,
				id_cliente,
				moneda,
				f.status,
				c.nombre cliente,
				f.importe total,
				f.tipo,
				u.nombre usuario,
				#IF(dev.id IS NOT NULL, 1, 0) devuelta,
				IF(i.id IS NOT NULL, 1, 0) isCFDI,
				IF(fecha_factura >= fecha_inicio_cfdi AND IF(INSTR(f.folio, 'NOTA') = 0, 1, 0) = 1, 1, 0) isCFDI_fecha
			FROM (
				SELECT * FROM facturas
				WHERE 1 {$where_factura}
				ORDER BY fecha_captura DESC, {$_GET[order]} + 0 {$_GET[direction]}
				{$pagi}
			) f
			INNER JOIN (
				SELECT clave, nombre FROM clientes_memory {$where_clientes}
				UNION
				SELECT 0, CONVERT('Público'  using latin1) COLLATE latin1_general_ci
			) c ON c.clave = f.id_cliente
			INNER JOIN almacenes a ON f.id_almacen = a.id_almacen
			INNER JOIN usuarios u ON f.id_facturista = u.id_usuario
			/*LEFT JOIN (
				SELECT id, factura FROM devoluciones_clientes WHERE status = 0
			) dev ON dev.factura = f.folio*/
			LEFT JOIN cfdi i ON i.folio = f.folio AND i.serie = f.serie
			JOIN vars v ORDER BY fecha_factura DESC, folio DESC";
 //echo $s;
require_once('funciones/kgPager.class.php');
$query = mysql_query($s) or die ($s.mysql_error());
$total_records = mysql_num_rows($query);

//Parámetros para el ícono "REGRESAR" del menú superior
foreach($_GET as $k => $v){
	$params .= "&{$k}={$v}";
}
$params = substr($params,1,strlen($params));
$_SESSION[start] = "comuni-k.php?".$params;
//Fin de parámetros
//Inicia configuración
$_POST[printable] = 0;
titleset("Gesti&oacute;n de Ventas");
if(Administrador() || ComprasVentas() || Ventas()){
	$o = array(
							"<a href='?section=ventas_formulario' ><img src='imagenes/add.png' /> Registrar Venta</a>",
							"<a href='ventas_formulario_2016.php' ><img src='imagenes/add.png' /> Registrar Venta v2</a>",
							"<a href='?section=timbrado_masivo' ><img src='imagenes/database.png' /> Timbrado masivo</a>"
						 );
	pop($o,"ventas");
}
filter_display("ventas");
//Fin de configuración
?>

<form name="filtro" id="filtro" method="get" action="" class="<?=$_POST[class_filtro]?>">
  <p>Entre
    <input name="fecha1" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha1]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha1');" style="margin-bottom:-3px; cursor:pointer" />&nbsp;&nbsp;&nbsp;y&nbsp;&nbsp;
    <input name="fecha2" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha2]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha2');" style="margin-bottom:-3px; cursor:pointer" /></p>
  <p>Cliente: <input type="text" name="cliente" value="<?=$_GET[cliente]?>" style="width: 300px;" /></p>
  <p> Folio:
    <input name="folio" type="text" id="folio" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[folio]?>" size="8"/>
    &nbsp;&nbsp;&nbsp;
    Serie:<input name="serie" type="text" id="serie" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[serie]?>" size="8"/>
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
&nbsp;&nbsp;&nbsp;Tipo:
<select name="tipo" id="tipo">
	<option value="x">Todos</option>
  <option value="f" <?=selected($_GET[tipo],"f")?>>Facturas</option>
  <option value="n" <?=selected($_GET[tipo],"n")?>>Notas</option>
</select>
    &nbsp;&nbsp;&nbsp;Mostrar:
    <select name="registros" id="registros">
      <option value="10" <?=selected($_GET[registros],10)?>>10</option>
      <option value="20" <?=selected($_GET[registros],20)?>>20</option>
      <option value="50" <?=selected($_GET[registros],50)?>>50</option>
      <option value="100" <?=selected($_GET[registros],100)?>>100</option>
      <option value="500" <?=selected($_GET[registros],500)?>>500</option>
      <option value="<?=(9999 * 9999)?>" <?=selected($_GET[registros], 9999 * 9999)?>>Todos</option>
    </select>
  </p>
  <p>Status:
    <select name="status" id="status">
      <option value="x">Todos</option>
      <option <?=selected($_GET[status],"0")?> value="0">Normal</option>
      <option <?=selected($_GET[status],"1")?> value="1">Cancelada</option>
    </select>
    &nbsp;&nbsp;&nbsp; Ordenar por:
    <select name="order" id="select">
      <option value="folio" <?=selected($_GET[order],"folio")?>>Folio</option>
      <option value="fecha_factura" <?=selected($_GET[order],"fecha_factura")?>>Fecha</option>
      <option value="cliente" <?=selected($_GET[order],"cliente")?>>Cliente</option>
      <option value="usuario" <?=selected($_GET[order],"usuario")?>>Vendedor</option>
      <option value="almacenes.descripcion" <?=selected($_GET[order],"almacenes.descripcion")?>>Almacen</option>
      <option value="importe" <?=selected($_GET[order],"importe")?>>Importe</option>
      <option value="status" <?=selected("status",$_GET[order])?>>Status</option>
    </select>
<select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>
    &nbsp;&nbsp;
    <input name="section" type="hidden" id="section" value="ventas" />
    <input name="Buscar" type="submit" value="Crear lista de Ventas" style="font-size:11px"/>
  </p>
  <!--<p>Encontrados: <?=$total_records?> coincidencias</p>-->
</form>

<?php 
//13 ene 2015 > No sé qué es eso del reporte mensual...
if(Administrador())
{
	if(!isset($_GET[si]) && !isset($_GET[no]))
	{
?>
	<div style="text-align:center; display: none">
		<form name="form2" id="form2" method="post" action="mensual_reporte.php" class="<?=$_POST[class_filtro]?>">
			<?php
			if(isset($_GET[fecha1]) && isset($_GET[fecha2]))
			{
			?>
			<hr />
			<input type="submit" name="reportem" value="Generar reporte mensual"/>
			<input name="repofech1" type="hidden" id="repofech1" value="<?=$_GET[fecha1]?>" />
			<input name="repofech2" type="hidden" id="repofech2" value="<?=$_GET[fecha2]?>" />
			<hr />
			<?php
			}
			?>
		</form>
	</div>
	<?php
	}
	else
	{
		if(isset($_GET[si]))
		{
		?>
		<div style="text-align:center;"><span style="color:GREEN;"><b>Archivo creado correctamente.</b></span></div>
		<?php
		}
		else
		{
		?>
		<div style="text-align:center;"><span style="color:RED;"><b>Hubo un error al crear el archivo, intente de nuevo.</b></span></div>
		<?php
		}
	}
}
?>

<?php if($total_records > 0) { ?>
<div style="text-align: center;">
	<div style="display: inline-block;">
		<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
			<tr>   
				<th>Serie-Folio</th>   
				<th>Cliente</th>
				<th>Almacén</th>
				<th>Fecha</th>
				<th>Vendedor</th>
				<th>Importe</th>
				<?php if(Administrador()) { ?>
				<th></th>
				<th>Clonar</th>
				<?php } ?>
				<th>Status</th>
			</tr>
			<?php
			while($r = mysql_fetch_assoc($query))
			{
				$isCFDI = (int)$r[isCFDI] == 1;
				$isCFDIfecha = (int)$r[isCFDI_fecha] == 1;
				$sf = (substr_count($r[folio], 'NOTA') == 0) ? "{$r[serie]}-{$r[folio]}" : $r[folio];
		?>
			<tr id="tr_<?=$r['folio']?>" class="<?=$class?>">
				<td class="text-center">
					<a href="?section=ventas_detalle&folio=<?=$r[folio]?>&serie=<?=$r[serie]?>"><?=$sf?></a>
					<?php
					if((int)$r[devuelta] == 1)
					{
					?>
					<br />
					<font size="1">Devuelta</font>
					<?php
					}
					?>
				</td>   
				<td><?=$r[cliente]?></td>
				<td><?=$r[almacen]?></td>
				<td><?=FormatoFecha($r[fecha_factura])?></td>
				<td><?=$r[usuario]?></td>
				<td style="text-align:right"><?=money($r[total])?><?=mon($r[moneda])?></td>
				<?php
				if(Administrador())
				{
				?>
				<td style="text-align:center">
				<?php if($r[status] == "0") { ?>
					<a href="?section=ventas_edit&folio=<?=$r[folio]?>&serie=<?=$r[serie]?>" style="margin-right: 4px;"><img src="imagenes/pencil.png" /></a>
					<a href="ventas_formulario_2016.php?editar&folio=<?=$r[folio]?>&serie=<?=$r[serie]?>"><img src="imagenes/pencil2.png" /></a>
				<?php } ?>
				</td>
				 <td style="text-align:center">
				<?php
					if($r[status] == "0" or ($r[status] == '2' and $isCFDI))
					{
				 ?>
				<a href="?section=ventas_clon&folio=<?=$r[folio]?>&serie=<?=$r[serie]?>"><img src="imagenes/clon.png" /></a>
				<?php
					}
				}
				?>
				<td style="font-weight:bold; text-align:center">
				<?php
				//Switches de status y cancelaciones
				if($r[status] == 0 and !$isCFDI and !$isCFDIfecha and (Administrador() or ComprasVentas() or Ventas())) //Normal cancelable
				{
				?>
				<span id="estado_span<?=$r[folio]?>">
					<select id="status_list_<?=$r['folio']?>" onchange="cancelar_compra('<?=$r[folio]?>','<?=$r[serie]?>',this);">
						<option value="0">Normal</option>
						<option value="1">Cancelar</option>
					</select>
				</span>
				<?php
				}
				else if($r[status] == 0 and !$isCFDIfecha) //Normal no cancelable
				{
					echo 'Normal';
				}
				else if($r[status] == 2 and $isCFDI and (Administrador() or ComprasVentas() or Ventas())) //Timbrada cancelable
				{
				?>
				<span id="estado_span<?=$r[folio]?>">
					<select id="status_list_<?=$r['folio']?>" onchange="cancelar_cfdi('<?=$r[folio]?>','<?=$r[serie]?>',this);">
						<option value="0">Timbrada</option>
						<option value="1">Cancelar</option>
					</select>
				</span>
				<?php
				}
				else if($r[status] == 2 and $isCFDI) //Timbrada no cancelable
				{
					echo 'Timbrada';
				}
				else if($r[status] == 1 and !$isCFDI and (Administrador() or ComprasVentas() or Ventas())) //Cancelada eliminable
				{
					echo "<a href=\"javascript:eliminar('{$r[folio]}','{$r[serie]}')\" title=\"Eliminar esta factura\">Cancelada</a>";
				}
				else if($r[status] == 1 and $isCFDI) //CFDI cancelado
				{
					echo "Cancelada";
				}
				else if($r[status] == 0 and $isCFDIfecha and (Administrador() or ComprasVentas() or Ventas())) //CFDI no timbrado y eliminable
				{
					echo "<a href=\"javascript:eliminar('{$r[folio]}','{$r[serie]}')\" title=\"Eliminar esta factura\">Pendiente</a>";
				}
				else if($r[status] == 0 and $isCFDIfecha) //CFDI no timbrado
				{
					echo "Pendiente";
				}
				else
				{
					$dataBase = 'comuni-k_' . (substr_count($_SERVER['PHP_SELF'], 'aceros') > 0) ? 'aceros' : 'tornero';
					echo "<a href='../advans/timbrar.php?db={$dataBase}&folio={$r[folio]}&serie={$r[serie]}&origen=ventas' target='_error'>Error</a>";
				}
				?>
				</td>
			</tr>
			<?php
			}
			?>
		</table>
		<div style="float: left; margin-top: 12px; font-weight: bold;">Mostrando <?=$total_records?> registros</div>
		<div style="float: right;" id="_pagination">
			<p id="pager_links">
				
				<?php
				$link = '';
				foreach($_GET as $k => $v) $link .= "{$k}={$v}&";
				
				if($_GET[pagina] > 0)
				{
				?>
				<span><a href="?<?=$link?>pagina=<?=intval($_GET[pagina]-1)?>">Anterior</a></span>
				<?php
				}

				if($total_records >= intval($_GET[registros]) && isset($_GET[registros]) && isset($pagi))
				{
				?>  
				<span><a href="?<?=$link?>pagina=<?=intval($_GET[pagina]+1)?>">Siguiente</a></span>
				<?php
				}
				?>
			</p>
		</div>
	</div>
</div>
<?php } ?>
<script src="js/libraries/ventas.js" language="javascript" charset="UTF-8"></script>
<script src="../advans/jsCancelar.js" language="javascript" charset="UTF-8"></script>