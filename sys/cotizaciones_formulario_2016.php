<?php
@session_start();
/*18 Mar 2016*/
$titulo = substr_count($_SERVER['PHP_SELF'], 'tornero') > 0 ? "La Casa Del Tornero" : "HC Aceros";
/*18 Mar 2016*/
if (!isset($_SESSION) or !isset($_SESSION['nombre']) or !isset($_SESSION['id_tipousuario'])) header("Location: index.php");

include("funciones/basedatos.php");
include("funciones/funciones.php");
$edicion_productos = array();

//Edición
if(!isset($_GET['editar']))
{
	/* Identificar información de folios para factura */
	$s = "SELECT folio FROM cotizaciones ORDER BY folio + 1 DESC LIMIT 1";
	$folio_final = (int)$db->fetchCell($s) + 1;
	$folio = number_format($folio_final, 0 , "", "");
}
else
{
	$folio = $_GET['folio'];
	
	$s = "SELECT DATE_FORMAT(fecha, '%Y-%m-%d') fecha, cliente, datos_cliente, moneda FROM cotizaciones WHERE folio = '{$_GET['folio']}'";
	$info = $db->fetchRow($s);
	
	$s = "SELECT c.id_producto, c.cantidad,
				c.unidad, c.especial, c.complemento,
				c.precio, c.iva, c.importe,
				p.codigo_barras codigo, p.descripcion
				FROM cotizaciones_productos AS c
				LEFT JOIN productos AS p ON c.id_producto = p.id_producto
				WHERE c.folio_cotizacion = '{$_GET['folio']}' ORDER BY c.id_cotizacionproducto";
	$edicion_productos = $db->fetch($s);
}

//Insert
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
if(count($request) > 0)
{
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	$r = array(
		'error' => 0,
		'msg' => ''
	);
	
	//Verificar que el folio esté disponible
	$r['folio'] = $request->factura->folio;
	if($request->edicion != 1) //Nuevo
	{
		$s = "SELECT COUNT(1) c FROM cotizaciones WHERE folio = '{$r['folio']}'";
		$folioDisponible = $db->fetchCell($s) == 0;
	}
	else $folioDisponible = true;
	
	if($folioDisponible)
	{
		//Insertar factura
		$f = $request->factura;
		if($request->edicion != 1)
		{
			$db->insert('cotizaciones', (array)$f);
		}
		else
		{
			$db->update('cotizaciones', (array)$f, array( 'folio' => $f->folio ));
			
			//Resetear inventario eliminando productos
			$db->execute("DELETE FROM cotizaciones_productos WHERE folio_cotizacion = '{$f->folio}'");
		}
		
		//Inicializar produtos
		foreach($request->productos as $p)
		{
			if(strlen($p->factura->especial) > 0 or (int)$p->factura->id_producto > 0)
			{
				unset($p->factura->form);
				if(strlen($p->factura->especial) > 0)
					$p->factura->id_producto = 0;
				else
					$p->factura->especial = 0;
				
				$db->insert('cotizaciones_productos', utf8_deconverter((array)$p->factura));
			}
		}
	}
	else
	{
		$r['error']++;
		$r['msg'] = "El folio no se encuentra disponible. El siguiente consecutivo es \"{$folio}\"";
	}
	
	header('Content-Type: application/json');
	echo json_encode_utf8( $r );
	exit;
}


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
	<script type="text/javascript" src="../librerias/cotizacion-ng.js?v=<?=rand()?>"></script>	
	
	<script type="text/javascript" src="../librerias/funciones.js"></script>
	<script type="text/javascript" src="../librerias/formTools.js"></script>
	<script>
	//Definir variables
	var _folio = parseInt('<?=$folio?>');
	var _facturista = '<?=$_SESSION['id_usuario']?>';
	
	//Info de edición
	var _edicion = <?=(isset($_GET['editar'])) ? 'true' : 'false'?>;
	var _fecha = '<?=(isset($info['fecha'])) ? $info['fecha'] : ''?>';
	var _cliente = '<?=(isset($info['cliente'])) ? $info['cliente'] : ''?>';
	var _datos_cliente = <?=(isset($info['datos_cliente'])) ? json_encode_utf8($info['datos_cliente']) : '""'?>;
	var _moneda = '<?=(isset($info['moneda'])) ? $info['moneda'] : ''?>';
	var _productos = <?=json_encode(utf8ize($edicion_productos), JSON_NUMERIC_CHECK)?>;
	
	//Colección de unidades
	var _unidades = <?=json_encode(utf8ize(unidades()))?>;
	</script>
</head>
<body>
<div ng-app="app">
	<div ng-controller="cotizacionFormulario" ng-cloak>
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#"><?=$titulo?>: Cotización</a>
				</div>
				<div id="navbar" class="collapse navbar-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li><a href="comuni-k.php?section=cotizaciones"><i class="fa fa-fw fa-backward"></i> Regresar</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-fw fa-user"></i> <?=$_SESSION['nombre']?> <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="index.php"><i class="fa fa-fw fa-power-off"></i> Cerrar sesi&oacute;n</a></li>
							</ul>
						</li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</nav>

		<div class="container-fluid">
			<div class="template">
				<!--<pre>{{f}}</pre>-->
				<form name="v" ng-submit="noSubmit($event)" ng-init="agregarProductos()" novalidate>
					<div class="panel panel-default">
						<table class="form-inline table venta-form-inicio">
							<tr>
								<th><label for="folio">Folio</label></th>
								<td>
									<div class="form-group">
										<input type="number" class="form-control" id="folio" name="folio" ng-model="f.factura.folio" ng-readonly="f.edicion" required>
									</div>
								</td>
								<th><label for="fecha_factura">Fecha</label></th>
								<td>
									<div class="form-group">
										<input type="text" class="form-control date" id="fecha_factura" name="fecha_factura" ng-model="f.factura.fecha" readonly required>
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
							</tr>
							<tr>
								<th><label for="cliente">Cliente</label></th>
								<td colspan="6">
									<div class="form-group">
										<input type="text" class="form-control" id="cliente" name="cliente" ng-model="f.factura.cliente" required autofocus autocomplete="off" ng-init="focusMe('cliente', '', true)">
									</div>
								</td>
							</tr>
							<tr>
								<th>
									<label for="datos_cliente">Datos del cliente</label>
								</th>
								<td colspan="8">
									<textarea id="datos_cliente" class="form-control w100i" ng-model="f.factura.datos_cliente"></textarea>
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
									<th><span  data-toggle="tooltip" data-placement="top"><i class="fa fa-w fa-shopping-cart"></i> Cantidad</span></th>
									<th><span title="Unidad de medida" data-toggle="tooltip" data-placement="top"><i class="fa fa-w fa-cubes"></i> Unidad</span></th>
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
												<input type="text" name="codigo_{{$index}}" class="form-control prod-cod" typeahead="prod.codigo for prod in productoBuscar($viewValue)" typeahead-append-to-body="true" typeahead-template-url="../librerias/angular-templates/ventas-producto-codigo.html" typeahead-select-on-blur="true" typeahead-on-select="productoSet($item, $model, $label, p, $index)" typeahead-editable="false" typeahead-select-on-exact="true" ng-blur="productoConfirmar($index)" typeahead-loading="p.form.ajax" ng-class="{ clienteAjax: p.form.ajax }" ng-init="setPop(); focusCode($index);" ng-keydown="productoEnter($event, $index)" ng-model="p.form.codigo" ng-disabled="p.form.isEspecial" autocomplete="false">
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
												<textarea name="complemento" placeholder="Informaci&oacute;n complementaria" class="form-control w100" id="complemento{{$index}}" ng-model="p.factura.complemento" ng-init="focusMe('complemento', $index, !p.form.isEspecial)"></textarea>
											</div>
										</div>
									</td>
									<td><div class="form-group" ng-class="{ 'has-error': v['p_cantidad_' + $index].$invalid }"><input type="text" class="form-control text-right producto-numerico" name="p_cantidad_{{$index}}" ng-disabled="!(p.factura.id_producto > 0) && !p.form.isEspecial" ng-model="p.factura.cantidad" valid-number autocomplete="false"></div></td>
									<!--<td class="text-center">{{p.factura.unidad}}</td>-->
									<td>
										<div class="form-group" ng-class="{ 'has-error': v['p_unidad_' + $index].$invalid }">
											<select type="text" class="form-control text-right producto-numerico select_unidad" name="p_unidad_{{$index}}" ng-disabled="!(p.factura.id_producto > 0) && !p.form.isEspecial" ng-model="p.factura.unidad">
												<option ng-repeat="unidad in unidades">{{unidad}}</option>
											</select>
										</div>
									</td>
									<td><div class="form-group" ng-class="{ 'has-error': v['p_precio_' + $index].$invalid }"><input type="text" class="form-control text-right producto-numerico-largo" name="p_precio_{{$index}}"  ng-disabled="!(p.factura.id_producto > 0) && !p.form.isEspecial" ng-model="p.factura.precio" valid-number autocomplete="false"></div></td>
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
											<tr>
												<th class="active">I.V.A.</th>
												<td class="text-right">{{ (sumas.iva >= 0) ? (sumas.iva | currency) : '~' }}</td>
											</tr>
											<tr>
												<th class="active">Total</th>
												<td class="text-right">{{ (sumas.iva >= 0) ? (sumas.subtotal + sumas.iva | currency) : '~' }}</td>
											</tr>
										</tbody>
									</table>
							</div>
						</div>
					</div>
					
					<!--{{v.$submitted}}x{{v.$invalid}}xx{{alerts}}
					<pre>{{v}}</pre>-->
					<div class="row" ng-show="v.$submitted">
						<div class="col-sm-12">
							<div class="alert alert-danger" ng-if="alerts.productos"><i class="fa fa-w fa-warning"></i> No hay productos definidos.</div>
							<div class="alert alert-danger" ng-if="alerts.existencias"><i class="fa fa-w fa-warning"></i> Existen inconsistencias en las cantidades solicitadas.</div>
							<div class="alert alert-danger" ng-if="alerts.cliente"><i class="fa fa-w fa-warning"></i> El cliente no ha sido definido.</div>
							<!--<div class="alert alert-danger" ng-if="alerts.folioNoValido"><i class="fa fa-w fa-warning"></i> Especifique un folio v&aacute;lido.</div>-->
							<!--<div class="alert alert-danger" ng-if="alerts.folioUtilizado"><i class="fa fa-w fa-warning"></i> El folio especificado ya se encuentra utilizado.</div>-->
							<!--<div class="alert alert-danger" ng-if="alerts.duplicados"><i class="fa fa-w fa-warning"></i> Existen productos duplicados. Verifique la informaci&oacute;n.</div>-->
							<div class="alert alert-danger" ng-if="v.$invalid && !alerts.cliente && !alerts.existencias"><i class="fa fa-w fa-warning"></i> Existe un error no identificado en el formulario.</div>
							<div class="alert alert-danger" ng-if="alerts.error"><i class="fa fa-w fa-warning"></i> Ha ocurrido un error. Intente de nuevo m&aacute;s tarde.</div>
							<div class="alert alert-danger" ng-if="alerts.errorDefinido"><i class="fa fa-w fa-warning"></i> {{alerts.errorDetalle}}</div>
							<div class="alert alert-info" ng-if="alerts.ongoing"><i class="fa fa-w fa-spin fa-spinner"></i> Registrando informaci&oacute;n...</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-sm-12">
							<hr>
							<div class="text-center">
								<button type="button" class="btn btn-primary" ng-click="formSubmit()"><i class="fa fa-w fa-floppy-o"></i> Registrar informaci&oacute;n de cotizaci&oacute;n</button>
							</div>
						</div>
					</div>
				</form>
				
				<!--<pre>{{v}}</pre>-->
				
				<hr>
				
				<h5 class="negritas">Teclas r&aacute;pidas</h5>
				<ul class="list-inline">
					<!--<li><small><b>F1</b> - Buscar cliente</small></li>-->
					<li><small><b>F5</b> - Reiniciar formulario</small></li>
					<li><small><b>F8</b> - Agregar producto</small></li>
					<li><small><b>F10</b> - Registrar cotización</small></li>
					<!--<li><small><b>F3</b> - Buscar producto</small></li>-->
				</ul>
				
			</div>
		</div>
	</div>
</div>
</body>
</html>