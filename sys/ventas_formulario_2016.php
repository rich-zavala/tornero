<?php
@session_start();
/*18 Mar 2016*/
$titulo = substr_count($_SERVER['PHP_SELF'], 'tornero') > 0 ? "La Casa Del Tornero" : "HC Aceros";
/*18 Mar 2016*/

if (!isset($_SESSION) or !isset($_SESSION['nombre']) or !isset($_SESSION['id_tipousuario'])) header("Location: index.php");

include("funciones/basedatos.php");
include("funciones/funciones.php");

/* Consulta genérica para cotizaciones y ediciones */
$cg = "SELECT
			%s c.id_producto, c.complemento, c.especial, c.cantidad, unidad_factura,
			%s c.precio, c.iva, c.importe, p.codigo_barras codigo, p.descripcion,
			IF( p.cantidad IS NULL, CONCAT('[{ \"lote\": ', '\"', c.lote,'\", \"cantidad\": 0}]'), GROUP_CONCAT(CONCAT('[{ \"lote\": ', '\"', p.lote,'\", \"cantidad\": ', p.cantidad, '}]'))) dispo
			FROM %s c
			LEFT JOIN (
				SELECT p.*, IFNULL(e.cantidad, 0) cantidad, IFNULL(e.lote, '') lote FROM productos p
				LEFT JOIN existencias e ON e.id_producto = p.id_producto AND e.id_almacen = 1
				GROUP BY p.id_producto
			) p ON p.id_producto = c.id_producto WHERE %s GROUP BY %s";
if(!isset($_GET['editar'])) //Nuevo
{
	/* Identificar información de folios para factura */
	//Encontrar el folio máximo de la serie actual
	$folios_disponibles = true;
	$s = "SELECT serie, folioi, foliof, ncsd, anoa, noa FROM vars LIMIT 1";
	$info = $db->fetchRow($s);

	//Definir el siguiente folio del consecutivo
	$s = "SELECT folio FROM facturas ORDER BY folio + 1 DESC LIMIT 1";
	$folio = (int)$db->fetchCell($s) + 1;
	$folios_disponibles = $folio >= $info; //Se han agotado los folios
	$edicion_productos = array();
}
else //Editar
{
	$folio = $_GET['folio'];
	$s = "SELECT serie, anoap anoa,
				noap noa, nocertificado ncsd,
				id_cliente cliente, NumCtaPago,
				metodoDePago, moneda, leyenda, fecha_factura,
				recargo_id, recargo_concepto, recargo_porcentaje, recargo_importe

				FROM facturas WHERE folio = '{$_GET['folio']}' AND serie = '{$_GET['serie']}'";
	$info = $db->fetchRow($s);
	
	$s = sprintf($cg, null, "c.canti_, c.lote, c.unidad,", "facturas_productos", "c.folio_factura = '{$_GET['folio']}' AND c.serie = '{$_GET['serie']}'", "c.id_facturaproducto");
	$edicion_productos = $db->fetch($s);
}

//Insert
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
if(count($request) > 0)
{
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	$f = $request->factura;
	$pago = $f->pago;
	unset($f->pago);
	
	//Establecer folio
	if($f->tipo == 'f')
	{
		if($request->edicion != 1) $f->folio = $folio;
	
		//Número de cuenta
		if(strlen(trim($f->NumCtaPago)) == 0)
		{
			$s = "SELECT NumCtaPago FROM clientes WHERE clave = '{$f->id_cliente}'";
			$f->NumCtaPago = trim($db->fetchCell($s));
		}
	}
	else
	{
		if($request->edicion != 1)
		{
			mysql_query("INSERT INTO notas_consecutivo () VALUES ()") or die(mysql_error());
			$_n = mysql_insert_id();
			$f->folio = "NOTA " . $_n;
		}
		$f->id_cliente = 0;
	}
	
	//18 julio 2016 > Si se deja vacío ya no poner "no identificado"
	// if(strlen(trim($f->NumCtaPago)) == 0) $f->NumCtaPago = 'NO IDENTIFICADO';
	
	//Insertar factura
	if($request->edicion != 1)
	{
		$db->insert('facturas', (array)$f);
	}
	else
	{
		$db->update('facturas', (array)$f, array( 'folio' => $f->folio, 'serie' => $f->serie ));
		
		//Resetear inventario eliminando productos
		$db->execute("DELETE FROM facturas_productos WHERE folio_factura = '{$f->folio}' AND serie = '{$f->serie}'");
	}
	
	//Inicializar produtos
	foreach($request->productos as $p)
	{
		if(strlen($p->factura->especial) > 0 || (int)$p->factura->id_producto > 0)
		{
			unset($p->factura->form);
			if(strlen($p->factura->especial) > 0) $p->factura->id_producto = 0;
			$p->factura->folio_factura = $f->folio;
			$p->factura->serie = $f->serie;
			$p->factura->almacen = $f->id_almacen;
			$db->insert('facturas_productos', utf8_deconverter((array)$p->factura));
		}
	}
	
	//Pago
	if($pago == 'Contado')
	{
		$s = "CALL facturaPagoContado('{$f->folio}', '{$f->serie}')";
		mysql_query($s);
	}
	
	header('Content-Type: application/json');
	echo json_encode_utf8(array( 'folio' => $f->folio, 'serie' => $f->serie ));
	exit;
}

/* Cargar cotización */
$cotizacion = isset($_GET['cotizacion']);
if($cotizacion)
{
	$s = sprintf($cg, "c.folio_cotizacion cotizacion, c.unidad unidad_factura, p.unidad,", "c.cantidad canti_,", "cotizaciones_productos_vista", "c.folio_cotizacion = '{$_GET['cotizacion']}'", "c.id_cotizacionproducto");
	$cotizacion_productos = $db->fetch($s);
	$cotizacion = count($cotizacion_productos) > 0;
}
if(!$cotizacion) $cotizacion_productos = array();

/* Cargar catálogo de recargos */
$recargos_vacio = array( array(
	'id' 					=> 0,
	'etiqueta'		=> 'Ninguno',
	'porcentaje'	=> '0'
));
$recargos_s = "SELECT id, etiqueta, porcentaje FROM recargos WHERE activo = 1 ORDER BY etiqueta";
$recargos_catalogo = array_merge( $recargos_vacio, $db->fetch($recargos_s) );

header("Content-type: text/html; charset=iso-8859-1");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Comuni-K 2016</title>
	<link rel="stylesheet" href="../librerias/bootstrap-3.3.5-dist/css/bootstrap.css">
	<link rel="stylesheet" href="../librerias/bootstrap-3.3.5-dist/css/bootstrap-theme.css">
	<link rel="stylesheet" href="../librerias/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="../librerias/bootrap-datepicker/bootstrap-datepicker.css">
	<link rel="stylesheet" href="../librerias/estilos.css">
	
	<script type="text/javascript" src="../librerias/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="../librerias/bootstrap-3.3.5-dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="../librerias/bootrap-datepicker/bootstrap-datepicker.min.js"></script>
	<script type="text/javascript" src="../librerias/bootrap-datepicker/bootstrap-datepicker.es.min.js"></script>
	<script type="text/javascript" src="../librerias/moment/moment.js"></script>
	<script type="text/javascript" src="../librerias/jqueryblock/jquery.blockUI.js"></script>
	
	<script type="text/javascript" src="../librerias/angular/angular.min.js"></script>
	<script type="text/javascript" src="../librerias/angular/angular-animate.min.js"></script>
	<script type="text/javascript" src="../librerias/angular/ui-bootstrap-tpls-0.14.3.js"></script>
	<script type="text/javascript" src="../librerias/angular/hotkeys.min.js"></script>	
	<script type="text/javascript" src="../librerias/venta-ng.js?v=<?=rand()?>"></script>	
	
	<script type="text/javascript" src="../librerias/funciones.js"></script>
	<script type="text/javascript" src="../librerias/formTools.js"></script>
	<script>
	//Definir variables
	var _usd = parseFloat(<?=getUSD()?>);
	var almacenes = <?=json_encode_utf8($_SESSION['almacenes'])?>;
	var _folio = '<?=$folio?>';
	var _serie = '<?=$info['serie']?>';
	var _anoap = '<?=$info['anoa']?>';
	var _noap = '<?=$info['noa']?>';
	var _nocertificado = '<?=$info['ncsd']?>';
	var _facturista = '<?=$_SESSION['id_usuario']?>';
	var _cotizacion = <?=json_encode(utf8ize($cotizacion_productos), JSON_NUMERIC_CHECK)?>;
	var _recargos = <?=json_encode(utf8ize($recargos_catalogo), JSON_NUMERIC_CHECK)?>;
	
	//Info de edición
	var _cliente = '<?=(isset($info['cliente'])) ? $info['cliente'] : ''?>';
	var _NumCtaPago = '<?=(isset($info['NumCtaPago'])) ? $info['NumCtaPago'] : ''?>';
	var _metodoDePago = '<?=(isset($info['metodoDePago'])) ? $info['metodoDePago'] : ''?>';
	var _moneda = '<?=(isset($info['moneda'])) ? $info['moneda'] : ''?>';
	var _leyenda = '<?=(isset($info['leyenda'])) ? $info['leyenda'] : ''?>';
	var _fecha_factura = '<?=(isset($info['fecha_factura'])) ? $info['fecha_factura'] : ''?>';
	var _productos = <?=json_encode(utf8ize($edicion_productos), JSON_NUMERIC_CHECK)?>;
	var _recargo = '<?=(isset($info['recargo_id'])) ? $info['recargo_id'] : ''?>';
	var _recargo_concepto = '<?=(isset($info['recargo_concepto'])) ? $info['recargo_concepto'] : ''?>';
	var _recargo_porcentaje = '<?=(isset($info['recargo_porcentaje'])) ? $info['recargo_porcentaje'] : ''?>';
	var _recargo_importe = '<?=(isset($info['recargo_importe'])) ? $info['recargo_importe'] : ''?>';
	
	//Colección de unidades
	var _unidades = <?=json_encode(utf8ize(unidades()))?>;
	</script>
</head>
<body>
<div ng-app="app">
	<div ng-controller="ventaFormulario" ng-cloak>
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Mostrar navegación</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="comuni-k.php?section=panel_control"><?=$titulo?>: Venta</a>
				</div>
				<div id="navbar" class="collapse navbar-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li><a href="comuni-k.php?section=ventas"><i class="fa fa-fw fa-backward"></i> Regresar</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-fw fa-user"></i> <?=$_SESSION[nombre] ?> <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="index.php"><i class="fa fa-fw fa-power-off"></i> Cerrar sesi&oacute;n</a></li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</nav>

		<div class="container-fluid">
			<div class="template">
				<form name="v" ng-submit="noSubmit($event)" ng-init="agregarProductos()" novalidate>
					<div class="panel panel-default">
						<table class="form-inline table venta-form-inicio">
							<tr>
								<th><label for="tipo">Tipo de venta</label></th>
								<td>
									<div class="form-group">
										<select class="form-control" id="tipo" name="tipo" ng-model="f.factura.tipo" required>
											<option value="f" ng-if="!f.edicion || (f.edicion && f.factura.tipo == 'f')">Factura</option>
											<option value="n" ng-if="!f.edicion || (f.edicion && f.factura.tipo == 'n')">Nota</option>
										</select>
									</div>
								</td>
								<th ng-if="f.factura.tipo == 'f'"><label for="folio">Folio</label></th>
								<td ng-if="f.factura.tipo == 'f'">
									<div class="form-group">
										<input type="text" class="form-control" id="folio" name="folio" ng-model="f.factura.folio" ng-change="folioVerificar()" readonly required>
									</div>
								</td>
								<th><label for="fecha_factura">Fecha</label></th>
								<td>
									<div class="form-group">
										<input type="text" class="form-control date" id="fecha_factura" name="fecha_factura" ng-model="f.factura.fecha_factura" readonly required>
									</div>
								</td>
								<th><label for="exampleInputName2">Almac&eacute;n</label></th>
								<td>
									<div class="form-group">
										<select class="form-control" id="almacen" name="id_almacen" ng-model="almacen_seleccionado" ng-options="item as item.descripcion for item in almacenes track by item.id" required></select>
									</div>
								</td>
							</tr>
							<tr ng-show="f.factura.tipo == 'f'">
								<th><label for="pago">Pago</label></th>
								<td>
									<div class="form-group">
										<select class="form-control" id="pago" name="pago" ng-model="f.factura.pago" required>
											<option>Cr&eacute;dito</option>
											<option>Contado</option>
										</select>
									</div>
								</td>
								<th><label for="moneda">Moneda</label></th>
								<td>
									<div class="form-group">
										<select class="form-control" id="moneda" name="moneda" ng-model="f.factura.moneda" required>
											<option>M.N.</option>
											<option>U.S.D.</option>
										</select>
									</div>
								</td>
								<th><label for="metodoDePago">M&eacute;t. de pago</label></th>
								<td>
									<div class="form-group">
										<select class="form-control" id="metodoDePago" name="metodoDePago" ng-model="f.factura.metodoDePago">
											<option value="01">01 - Efectivo</option>
											<option value="02">02 - Cheque</option>
											<option value="03">03 - Transferencia</option>
											<option value="04">04 - Tarjeta de Crédito</option>
											<option value="28">28 - Tarjeta de Débito</option>
											<option value="05">05 - Monedero Electrónico</option>
											<option value="06">06 - Dinero Electrónico</option>
											<option value="07">07 - Tarjeta Digitales</option>
											<option value="08">08 - Vales de Despensa</option>
											<option value="09">09 - Bienes</option>
											<option value="10">10 - Servicios</option>
											<option value="11">11 - Por cuenta de Terceros</option>
											<option value="12">12 - Dación en Pago</option>
											<option value="13">13 - Pago por Subrogación</option>
											<option value="14">14 - Pago por Consignación</option>
											<option value="15">15 - Condonación</option>
											<option value="16">16 - Cancelación</option>
											<option value="17">17 - Compensación</option>
											<option value="98">98 - &quot;NA&quot;</option>
											<option value="99">99 - Otros</option>
										</select>
									</div>
								</td>
								<th><label for="NumCtaPago">No. cuenta</label></th>
								<td>
									<div class="form-group">
										<input type="text" class="form-control" id="NumCtaPago" name="NumCtaPago" ng-model="f.factura.NumCtaPago" placeholder="Definido por cliente">
									</div>
								</td>
							</tr>
							<tr ng-show="f.factura.tipo == 'f'">
								<th><label for="id_cliente">Cliente</label></th>
								<td colspan="2">
									<div class="form-group">
										<input type="text" class="form-control" id="id_cliente" name="id_cliente" typeahead="cliop.clave for cliop in clienteBuscar($viewValue)" typeahead-append-to-body="true" typeahead-template-url="../librerias/angular-templates/ventas-clientes.html" typeahead-select-on-blur="true" typeahead-on-select="clienteSet($item)" typeahead-editable="false" typeahead-select-on-exact="true" ng-blur="clienteConfirmar()" typeahead-loading="cliente.cargando" ng-class="{ clienteAjax: cliente.cargando }" ng-model="cliente.id" ng-required="f.factura.tipo == 'f'" autofocus autocomplete="off" ng-init="focusMe('id_cliente', '', true)">
									</div>
								</td>
								<td colspan="3">
									<a href="comuni-k.php?section=clientes" class="btn btn-sm btn-default pull-right" tabindex="-1" target="_clientes"><i class="fa fa-w fa-list-alt"></i> Ir a clientes</a>
									<div class="text-info cliente-info" role="button" data-container="body" data-toggle="popover" data-placement="right" data-trigger="hover" data-html="true" data-title="N&uacute;mero de cuenta:<br><b>{{cliente.info.NumCtaPago}}</b>" data-content="{{cliente.data}}" ng-if="cliente.id > 0">{{cliente.info.nombre}}</div>
								</td>
								<th><label for="recargo">Recargo</label></th>
								<td>
									<div class="form-group">
										<select class="form-control" id="recargo" name="recargo" ng-model="f.recargo" 
										ng-options="(recargo.porcentaje + '% - ' + recargo.etiqueta) for recargo in recargos track by recargo.id" required>
										</select>
									</div>
								</td>
							</tr>
						</table>
					</div>
					
					<div class="panel panel-default">
						<table class="table table-hover table-bordered table-striped table-condensed">
							<thead class="table-productos-header">
								<tr class="info">
									<th><span title="Ingresar art&iacute;­culo manualmente" data-toggle="tooltip" data-placement="top"><i class="fa fa-w fa-i-cursor"></i> Tipo</span></th>
									<th><i class="fa fa-w fa-barcode"></i> C&oacute;digo</th>
									<th><i class="fa fa-w fa-cube"></i> Producto / Complemento</th>
									<th><span title="Disponibilidad en almac&eacute;n" data-toggle="tooltip" data-placement="top"><i class="fa fa-w fa-tasks"></i> Dis.</span></th>
									<th><span title="Cantidad a retirar del inventario" data-toggle="tooltip" data-placement="top" class="text-warning"><i class="fa fa-w fa-shopping-cart"></i> Cant A.</span></th>
									<th><span title="Unidad de medida desde el almacén" data-toggle="tooltip" data-placement="top"><i class="fa fa-w fa-cubes"></i> Unid. A.</span></th>
									<th><span title="Cantidad a mostrar en factura" data-toggle="tooltip" data-placement="top" class="text-danger"><i class="fa fa-w fa-cart-plus"></i> Cant. F.</span></th>
									<th><span title="Unidad de medida para la factura" data-toggle="tooltip" data-placement="top"><i class="fa fa-w fa-cubes"></i> Unid. F.</span></th>
									<th><i class="fa fa-w fa-usd"></i> Precio</th>
									<th><i class="fa fa-w">%</i> I.V.A.</th>
									<th></th>
								</tr>
							</thead>
							<tbody class="table-productos-listado">
								<tr ng-repeat="p in f.productos">
									<td class="text-center">
										<label class="btn btn-sm" for="esp{{$index}}" ng-class="{ 'btn-info': p.form.isEspecial, 'btn-warning': !p.form.isEspecial }">
											<i class="fa fa-fw fa-info"></i>
											<input type="checkbox" class="hidden" id="esp{{$index}}" ng-model="p.form.isEspecial" ng-true-value="true" ng-false-value="false" ng-change="productoSuficiente($index)" autocomplete="off">
										</label>
									</td>
									<td>
										<div class="form-group" ng-class="{ 'has-error': (!p.form.ajax && v['codigo_' + $index].$error.editable) }">
											<div class="input-group w100 producto-codigo">
												<input type="text" name="codigo_{{$index}}" class="form-control prod-cod" typeahead="prod.codigo for prod in productoBuscar($viewValue)" typeahead-append-to-body="true" typeahead-template-url="../librerias/angular-templates/ventas-producto-codigo.html" typeahead-select-on-blur="true" typeahead-on-select="productoSet($item, $model, $label, p, $index)" typeahead-editable="false" typeahead-select-on-exact="true" ng-blur="productoConfirmar($index)" typeahead-loading="p.form.ajax" ng-class="{ clienteAjax: p.form.ajax }" ng-init="setPop(); focusCode($index);" ng-keyup="productoEnter($event, $index)" ng-model="p.form.codigo" ng-disabled="p.form.isEspecial" autocomplete="false">
												<span class="input-group-btn">
													<button class="btn btn-default btn-sm fa-icon" type="button" tabindex="-1" title="Buscar producto en cat&aacute;logo" ng-click="productoBuscarModal($index)"><i class="fa fa-search"></i></button>
												</span>
											</div>
										</div>
									</td>
									<td class="campo-texto w100">
										<input name="especial" id="especial{{$index}}" class="form-control marginBottom5 w100" ng-if="p.form.isEspecial" ng-model="p.factura.especial" ng-init="focusMe('especial', $index, p.form.isEspecial)" placeholder="Descripci&oacute;n principal del producto" autocomplete="false" autofocus>
										<div class="text-muted" ng-if="!(p.factura.id_producto > 0) && !p.form.isEspecial">Defina el c&oacute;digo del producto.</div>
										<div ng-if="(!p.factura.id_producto == 0 && p.factura.id_producto > 0) || p.form.isEspecial">
											<div ng-if="!p.form.isEspecial">{{p.form.descripcion}}</div>
											<div class="producto-informacion-complementaria">
												<textarea name="complemento" placeholder="Informaci&oacute;n complementaria" class="form-control w100 h46" rows="2" id="complemento{{$index}}" ng-model="p.factura.complemento" ng-init="focusMe('complemento', $index, !p.form.isEspecial)"></textarea>
											</div>
										</div>
									</td>
									<td class="campo-texto" ng-class="{ 'text-right': !p.form.isEspecial, 'text-center': p.form.isEspecial }"><span ng-if="p.form.isEspecial">N/A</span><span ng-if="!p.form.isEspecial">{{p.form.disponible[0].cantidad}}</span></td>
									<td><div class="form-group" ng-class="{ 'has-error': v['p_cantidad_' + $index].$invalid }"><input type="text" class="form-control text-right producto-numerico" name="p_cantidad_{{$index}}" ng-disabled="!(p.factura.id_producto > 0) || p.form.isEspecial" ng-model="p.factura.cantidad" ng-blur="productoSuficiente($index, $event)" valid-number autocomplete="false"></div></td>
									<td class="text-center">{{p.factura.unidad}}</td>
									<td><div class="form-group" ng-class="{ 'has-error': v['p_canti_' + $index].$invalid }"><input type="text" class="form-control text-right producto-numerico" name="p_canti_{{$index}}" ng-disabled="!(p.factura.id_producto > 0) && !p.form.isEspecial" ng-model="p.factura.canti_" ng-blur="productoCantidad($index)" valid-number autocomplete="false"></div></td>
									<td><div class="form-group" ng-class="{ 'has-error': v['p_unidad_' + $index].$invalid }">
										<select type="text" class="form-control text-right producto-numerico select_unidad" name="p_unidad_{{$index}}" ng-disabled="!(p.factura.id_producto > 0) && !p.form.isEspecial" ng-model="p.factura.unidad_factura">
											<option ng-repeat="unidad in unidades">{{unidad}}</option>
										</select>
									</div></td>
									<td class="campo-precio">
										<!--<div class="form-group" ng-class="{ 'has-error': v['p_precio_' + $index].$invalid }"><input type="text" class="form-control text-right producto-numerico-largo" name="p_precio_{{$index}}"  ng-disabled="!(p.factura.id_producto > 0) && !p.form.isEspecial" ng-model="p.factura.precio" valid-number autocomplete="false"></div>-->
										
										<div class="input-group input-group-sm" ng-class="{ 'has-error': v['p_precio_' + $index].$invalid }">
											<input type="text" class="form-control text-right producto-numerico-largo" name="p_precio_{{$index}}"  ng-disabled="!(p.factura.id_producto > 0) && !p.form.isEspecial" ng-model="p.factura.precio" valid-number autocomplete="false">
											<span class="input-group-btn" title="Aplicar USD">
												<button class="btn btn-primary ventas-usd-btn" type="button" tabindex="-1" ng-disabled="!(p.factura.id_producto > 0) || usdAplicados[$index]" ng-click="aplicarUSD(p, $index)"><i class="fa fa-usd"></i></button>
											</span>
										</div>
									</td>
									<td><div class="form-group" ng-class="{ 'has-error': v['p_iva_' + $index].$invalid }"><input type="text" class="form-control text-right producto-numerico" name="p_iva_{{$index}}"  ng-disabled="!(p.factura.id_producto > 0) && !p.form.isEspecial" ng-model="p.factura.iva" valid-number autocomplete="false"></div></td>
									<td><button class="btn btn-xs btn-danger btn-table" tabindex="-1" type="button" ng-click="productoRemover($index)"><i class="fa fa-times"></i></button></td>
								</tr>
								
							</tbody>
						</table>
					</div>
					
					<div class="row">
						<div class="col-sm-8">
							<div class="form-group" ng-show="f.factura.tipo == 'f'">
								<label for="leyenda">Leyenda</label>
								<textarea class="form-control" id="leyenda" placeholder="Ingrese un texto para mostrarlo en el pie de la factura." ng-model="f.factura.leyenda"></textarea>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="panel panel-default">
								<table class="table table-bordered table-condensed table-hover">
									<tbody>
										<tr>
											<th width="40%" class="active nowrap">Sub-Total</th>
											<td class="text-right">{{ (sumas.subtotal >= 0) ? (sumas.subtotal | currency) : '~' }}</td>
										</tr>
										<tr ng-show="f.recargo.id > 0">
											<th class="active">{{ f.recargo.porcentaje > 0 ? 'Recargo' : 'Descuento' }} ({{ f.recargo.porcentaje }}%)</th>
											<td class="text-right">{{ (f.factura.recargo_importe != 0) ? (f.factura.recargo_importe | currency) : '~' }}</td>
										</tr>
										<tr>
											<th class="active">I.V.A.</th>
											<td class="text-right">{{ (sumas.iva >= 0) ? (sumas.iva | currency) : '~' }}</td>
										</tr>
										<tr>
											<th class="active">Total</th>
											<td class="text-right">{{ (sumas.iva >= 0) ? (sumas.subtotal + sumas.iva + f.factura.recargo_importe | currency) : '~' }}</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					
					<div class="row" ng-show="v.$submitted">
						<div class="col-sm-12">
							<div class="alert alert-danger" ng-if="alerts.productos"><i class="fa fa-w fa-warning"></i> No hay productos definidos.</div>
							<div class="alert alert-danger" ng-if="alerts.existencias"><i class="fa fa-w fa-warning"></i> Existen inconsistencias en las cantidades solicitadas.</div>
							<div class="alert alert-danger" ng-if="alerts.cliente"><i class="fa fa-w fa-warning"></i> El cliente no ha sido definido.</div>
							<div class="alert alert-danger" ng-if="v.$invalid && !alerts.cliente && !alerts.existencias"><i class="fa fa-w fa-warning"></i> Existe un error no identificado en el formulario. Envie la siguiente información a soporte técnico:<pre>{{errors}}</pre></div>
							<div class="alert alert-danger" ng-if="alerts.error"><i class="fa fa-w fa-warning"></i> Ha ocurrido un error. Intente de nuevo m&aacute;s tarde.</div>
							<div class="alert alert-info" ng-if="alerts.ongoing"><i class="fa fa-w fa-spin fa-spinner"></i> Registrando informaci&oacute;n...</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-sm-12">
							<hr>
							<div class="text-center">
								<button type="button" class="btn btn-primary" ng-click="formSubmit()"><i class="fa fa-w fa-floppy-o"></i> Registrar informaci&oacute;n de venta</button>
							</div>
						</div>
					</div>
				</form>
				
				<hr>
				
				<div class="pull-right negritas text-warning">Tarifa USD: <?=money(getUSD())?></div>
				
				<h5 class="negritas">Teclas r&aacute;pidas</h5>
				<ul class="list-inline">
					<li><small><b>F5</b> - Reiniciar formulario</small></li>
					<li><small><b>F8</b> - Agregar producto</small></li>
					<li ng-if="!f.edicion"><small><b>F9</b> - Cambiar tipo de venta</small></li>
					<li><small><b>F10</b> - Registrar venta</small></li>
				</ul>
				
			</div>
		</div>
	</div>
</div>
</body>
</html>