'use strict';
var url = "ajax_tools_2016.php";
var app = angular.module('app', ['ui.bootstrap', 'cfp.hotkeys']);
app.controller('ventaFormulario', ['$scope', '$rootScope', '$http', '$filter', '$modal', '$timeout', 'hotkeys', function($scope, $rootScope, $http, $filter, $modal, $timeout, hotkeys) {
	//Herramientas
	$scope.moment = moment;
	$scope.setPops = setPops;
	$scope.unidades = _unidades;
	
	//Definir almacenes
	$scope.almacenes = almacenes;
	$scope.almacen_seleccionado = $scope.almacenes[0];
	$scope.$watch('almacen_seleccionado', function(){ //El selector asigna el id del almacén al objeto de factura
		$scope.f.factura.id_almacen = $scope.almacen_seleccionado.id;
	});
	
	//Información inicial de factura
	$scope.f = {
		edicion: false,
		factura: {
			folio: _folio,
			serie: _serie,
			tipo: 'f',
			fecha_factura: moment().format("YYYY-MM-DD"),
			id_cliente: null,
			id_almacen: $scope.almacen_seleccionado.id,
			licitacion: '_normal_',
			leyenda: null,
			NumCtaPago: null,
			metodoDePago: '01',
			pago: 'Crédito',
			moneda: 'M.N.',
			anoap: _anoap,
			noap: _noap,
			nocertificado: _nocertificado,
			id_facturista: _facturista,
			importe: 0,
			recargo_id: _recargos[0].id,
			recargo_concepto: _recargos[0].etiqueta,
			recargo_porcentaje: _recargos[0].porcentaje,
			recargo_importe: 0
		},
		productos: [],
		recargo: _recargos[0]
	};
	
	//Actualizar folio de los productos cuando cambie el folio
	$scope.$watch('f.factura.folio', function(f){
		angular.forEach($scope.f.productos, function(p, index){ p.folio(); });
	});
	
	//Información regular de producto
	var producto_info = function(){
		this.factura = {
			folio_factura: _folio,
			serie: _serie,
			almacen: $scope.f.id_almacen,
			usuario: _facturista,
			id_producto: null,
			lote: null,
			cantidad: 0,
			canti_: 0,
			unidad: 'PZA',
			unidad_factura: 'PZA',
			especial: '',
			complemento: '',
			precio: 0,
			iva: 16,
			importe: 0
		},
		
		this.form = {
			ajax: false,
			notFound: false,
			isEspecial: false,
			codigo: '',
			descripcion: '',
			disponible: []
		}
		
		this.folio = function(){
			this.factura.folio_factura = $scope.f.factura.folio;
		};
		
		this.folio();
	};
	
	//Nuevo producto
	var focusProducto = false;
	var ignorarCambios = false;
	$scope.productoAgregar = function(focusOn){
		if(focusOn)
		{
			focusProducto = focusOn;
			block();
		}
		
		$scope.f.productos.push( new producto_info() );

		//Identificar cambios en el selector
		var index = $scope.f.productos.length - 1;
		
		$scope.$watch('f.productos[' + index + '].form.codigo', function(o){
			if(typeof o == 'undefined') $scope.productoConfirmar(index);
		});
		
		if(!ignorarCambios)
		{
			//Resetear información al cambiar tipo
			$scope.$watch('f.productos[' + index + '].form.isEspecial', function(o){
				try{
					var isEspecial = o;
					var producto = $scope.f.productos[index];
					producto.form.isEspecial = o;
				}catch(e){ c(e); };
			});
		}
		
		//Sumar
		try{
			$scope.$watch('f.productos[' + index + '].factura.canti_', function(o){ $scope.sumar(); });
			$scope.$watch('f.productos[' + index + '].factura.precio', function(o){ $scope.sumar(); });
			$scope.$watch('f.productos[' + index + '].factura.iva', function(o){ $scope.sumar(); });
		}catch(e){ c(e); };
		
		return $scope.f.productos[index];
	};
	
	/* Controlador de cotización */
	$scope.agregarProductos = function()
	{
		if(_cotizacion.length > 0)
		{
			ignorarCambios = true;
			for(var i in _cotizacion) $scope.productoInicial(_cotizacion[i], 0);
			ignorarCambios = false;
		}
		else if(_productos.length > 0)
		{
			ignorarCambios = true;
			for(var i in _productos)
			{
				$scope.productoInicial(_productos[i], parseFloat(_productos[i].cantidad));
			}
			ignorarCambios = false;
		}
		else
		{	
			//Agregar 10 productos por default :P
			for(var i = 0; i < 2; i++) $scope.productoAgregar();
		}
	}
	
	// Agregador de productos de cotización y edición
	$scope.productoInicial = function(item, cantidad_agregativa){
		var dispo = angular.fromJson(item.dispo);
		var isEspecial = parseInt(item.especial) != 0;
		if(isEspecial || (!isEspecial && dispo.length > 0))
		{
			var producto = $scope.productoAgregar();
			producto.factura.almacen = $scope.f.factura.id_almacen;
			producto.factura.id_producto = item.id_producto;
			producto.factura.canti_ = item.canti_;
			producto.factura.unidad = (item.unidad != null) ? item.unidad : 'PZA';
			producto.factura.unidad_factura = producto.factura.unidad;
			producto.factura.complemento = item.complemento;
			producto.factura.precio = item.precio;
			producto.factura.iva = item.iva;
			producto.factura.importe = item.importe;
			producto.form.codigo = item.codigo;
			producto.form.descripcion = item.descripcion;
			producto.form.disponible = dispo;
			
			//Producto especial
			if(parseInt(item.especial) != 0 && item.especial.length > 0)
			{
				producto.form.isEspecial = true;
				producto.factura.especial = item.especial;
			}
			else
			{
				dispo[0].cantidad += cantidad_agregativa;
				producto.factura.cantidad = (dispo[0].cantidad >= item.cantidad) ? item.cantidad : dispo[0].cantidad;
				producto.factura.lote = dispo[0].lote;
			}
		}
	}
	
	/* Manejador de producto */
	$scope.productoBuscar = function(productoInput){
		return $http.get(url, {
      params: {
        producto: true,
				producto_codigo: productoInput,
				almacen: $scope.f.factura.id_almacen
      }
    }).then(function(response){
      return response.data.map(function(item){
        return item;
      });
    });
	}
	
	//TypeAhead seleccionado
	$scope.productoSet = function(item, model, label, producto, index){
		//Verificar que el producto no esté duplicado
		var duplicado = false;
		angular.forEach($scope.f.productos, function(p, index){
			if(!duplicado && p.factura.id_producto == item.id_producto)
			{
				duplicado = true;
				alert("Este producto ya ha sido ingresado con anterioridad.");
			}
		});
		
		if(!duplicado)
		{
			producto.factura.id_producto = item.id_producto;
			producto.factura.lote = item.dispo[0].lote;
			producto.factura.precio = item.precio;
			producto.factura.iva = item.iva;
			producto.factura.unidad = item.unidad;
			producto.factura.unidad_factura = producto.factura.unidad;
			producto.form.descripcion = item.descripcion;
			producto.form.disponible = item.dispo;
			
			//Focus a siguiente elemento;
			$timeout(function() {
				$('#complemento' + index).focus();
			}, 200);
		}
	};
	
	//Confirmar que hay producto válido. Sino, vaciar campo
	$scope.productoConfirmar = function(index){
		var o = $scope.f.productos[index];
		try
		{
			var codigo = o.form.codigo;
			if(typeof codigo == 'undefined' || codigo === null || String(codigo).length == 0)
			{
				$scope.productoSetNotFound(index, true);
				$scope.f.productos[index].form.descripcion = '';
				$scope.f.productos[index].form.disponible = [];
			}
			else
			{
				$scope.productoSetNotFound(index, false);
			}
		} catch(e){ c(e); };
	};
	$scope.productoSetNotFound = function(index, val){ $scope.f.productos[index].form.notFound = val; };
	
	//Focus de complemento
	$scope.focusMe = function(name, index, go){
		if(go)
		{
			$timeout(function(){
				var element = window.document.getElementById(name + index);
        if(element)
          element.focus();
			});
		}
	}
	
	//¡Producto sin enter!
	$scope.productoEnter = function(keyEvent, index) {
		if($(v['codigo_' + index]).val() == null || $(v['codigo_' + index]).val() == '')
		{
			if($scope.f.productos[index].factura.id_producto != null)
			{
				$scope.f.productos[index] = new producto_info();
				delete $scope.usdAplicados[index];
				$timeout(function() {
					$(v['codigo_' + index]).focus();
				}, 50);
			}
		}
		else if(keyEvent.which === 13)
		{
			$scope.focusMe(index);
			keyEvent.preventDefault();
		}
		else if(keyEvent.which === 9 && $scope.f.productos[index].form.ajax)
		{
			keyEvent.preventDefault();
		}
	}
	
	//Verificar producto suficiente
	$scope.productoSuficiente = function(index, e){
		try{
			var o = $scope.f.productos[index];
			if(!o.form.isEspecial && o.factura.cantidad > o.form.disponible[0].cantidad) o.factura.cantidad = o.form.disponible[0].cantidad;
		}catch(e){}
	}
	
	//Igualar cantidades
	$scope.productoCantidad = function(index){
		var c_ = $scope.f.productos[index].factura.canti_;
		if(c_ == 0 || c_ == '' || isNaN(c_)) $scope.f.productos[index].factura.canti_ = $scope.f.productos[index].factura.cantidad;
	}

	//Modal de productos
	$scope.productoBuscarModal = function(index){
		block();
		var data = {
			data: index,
			almacen: $scope.f.factura.id_almacen
		};
		var modalInstance = $modal.open({
			templateUrl: '../librerias/angular-templates/producto-buscador.html',
			controller: 'formularioController',
			size: 'lg',
			resolve:  {
				data: data,
			}
		}).rendered.then(function(){
			setDatePicker();
			unBlock();
		});
	};
	
	//Agregar producto desde modal
	$scope.$on('agregarProductoModal', function (event, data) {
		var producto = $scope.f.productos[data.index];
		$scope.productoSet(data.info, null, null, producto, data.index);
		producto.form.codigo = data.info.codigo;
	});
	
	//Remover TR de producto
	$scope.productoRemover = function($index){
		$scope.f.productos.splice($index, 1);
		if($scope.f.productos.length == 0) $scope.productoAgregar();
	}
	
	/* Manejador de cliente */
	//Sin cliente
	$scope.clienteReset = function(){
		$scope.cliente = {
			id: null,
			data: '',
			info: {}
		};
	};
	$scope.clienteReset();
	
	//Buscador
	$scope.clienteBuscar = function(clienteInput) {
		return $http.get(url, {
      params: {
        clientes: clienteInput
      }
    }).then(function(response){
      return response.data.map(function(item){
        return item;
      });
    });
  };
	
	//TypeAhead seleccionado
	$scope.clienteSet = function(item){
		$scope.cliente.id = parseInt(item.clave);
		$scope.cliente.info = item;
		
		//Formatear dirección
		try{
			if(item.noexterior.length > 0) item.noexterior = " No. " + item.noexterior;
			if(item.nointerior.length > 0) item.nointerior = " Int. " + item.nointerior;
		} catch(e){}
		$scope.cliente.data = "CALLE: " + item.calle + item.noexterior + item.nointerior + '<br>Colonia: ' + item.colonia + '<br>C.P. ' + item.cp + '<br>' + item.localidad + ', ' + item.municipio + ', ' + item.estado + '. ' + item.pais + '<br>R.F.C. ' + item.RFC;
		
		//Mostrar tooltip
		setTimeout(function(){ setPops(); }, 500);
	};
	
	//Confirmar que hay cliente. Sino, vaciar campo
	$scope.clienteConfirmar = function(){
		var id = $scope.cliente.id;
		if(parseInt(id) <= 0 || isNaN(id)) $scope.clienteReset();
	}
	
	//Asignar cliente
	$scope.$watch('cliente.id', function(){ $scope.f.factura.id_cliente = $scope.cliente.id; });
	
	/*Manejador de SUBMIT*/
	$scope.noSubmit = function(e){ e.preventDefault(); }
	
	//Focus nuevo producto
	$scope.focusCode = function(index){
		if(focusProducto)
		{
			$timeout(function(){
				unBlock();
				$('[name="codigo_' + index + '"]').focus();
			});
		}
	}
	
	/* Sumatorias */
	$scope.sumar = function(){
		$scope.sumas = {
			subtotal: 0,
			iva: 0
		};
		$scope.f.factura.recargo_importe = 0;
		angular.forEach($scope.f.productos, function(p){
			var p = p.factura;
			var subtotal_producto = Math.round((p.canti_ * p.precio) * 100) / 100;
			$scope.sumas.subtotal += subtotal_producto;
			
			var recargo_producto = Math.round((subtotal_producto * ($scope.f.recargo.porcentaje / 100)) * 100) / 100;
			$scope.f.factura.recargo_importe += recargo_producto;
			
			$scope.sumas.iva += Math.round(( (subtotal_producto + recargo_producto) * ( p.iva / 100 ) ) * 100) / 100;
		});
	}
	
	/* Manejador de edición */
	if(_cliente.length > 0)
	{
		$scope.f.edicion = true;
		$scope.cliente.id = _cliente;
		$scope.clienteBuscar(_cliente).then(function(data){
			$scope.clienteSet(data[0]);
		});
		$scope.f.factura.NumCtaPago = _NumCtaPago;
		$scope.f.factura.metodoDePago = _metodoDePago;
		$scope.f.factura.moneda = _moneda;
		$scope.f.factura.leyenda = _leyenda;
		$scope.f.factura.fecha_factura = _fecha_factura;
		$scope.f.factura.tipo = (_folio.indexOf('NOTA') >= 0) ? 'n' : 'f';
		
		/* Recargo */
		if(_recargo > 0)
		{
			$scope.f.recargo = _recargos.find(function(r){ if(r.id == _recargo) return r; });
			if(typeof $scope.f.recargo == 'undefined') //El recargo está inactivo o eliminado
			{
				var recargo_temporal = {
					id: _recargo,
					etiqueta: _recargo_concepto,
					porcentaje: parseFloat(_recargo_porcentaje)
				};
				_recargos.push(recargo_temporal);
				$scope.f.recargo = recargo_temporal;
			}
		}
	}
	
	/* Manejador de recargos */
	$scope.recargos = _recargos;
	$scope.$watch('f.recargo', function(o){
		$scope.sumar();
		$scope.f.factura.recargo_id = $scope.f.recargo.id;
		$scope.f.factura.recargo_concepto = $scope.f.recargo.etiqueta;
		$scope.f.factura.recargo_porcentaje = $scope.f.recargo.porcentaje;
	});
	
	/* Aplicar tasa USD */
	$scope.usdAplicados = {};
	$scope.aplicarUSD = function(producto, index){
		producto.factura.precio = (producto.factura.precio * _usd).toFixed(2);
		$scope.usdAplicados[index] = true;
	};
	
	/* Submit! */
	//Set alerts
	$scope.alerts = { ongoing: false };
	$scope.setAlerts = function(){
		$scope.alerts = {
			productos: false,
			existencias: false,
			cliente: false,
			error: false,
			ongoing: false
		};
	};
	
	$scope.formSubmit = function(){
		$scope.errors = {
			form: $scope.f,
			validation: $scope.v,
			alerts: $scope.alerts,
			ajaxError: null
		};
		if(!$scope.alerts.ongoing) $scope.setAlerts();
		if(!$scope.alerts.ongoing)
		{
			$scope.v.$setSubmitted();
			
			if($scope.v.$invalid)
			{
				for(var i in $scope.v.$error.required)
					if($scope.v.$error.required[i].$name == 'id_cliente')
						$scope.alerts.cliente = true;
			}
			else
			{
				//Corregir productos
				var conProductos = false;
				var corregirProductos = function(){
					angular.forEach($scope.f.productos, function(p, index){
						//Remover productos especiales vacíos
						if(p.form.isEspecial && $.trim(p.factura.especial).length == 0)
						{
							$scope.productoRemover(index);
							corregirProductos(); //Reiniciar verificación
							return;
						}
						else if(p.form.id_producto > 0)
						{
							//Cantidades
							if(isNaN(p.factura.canti_)) p.factura.canti_ = 0;
							if(!p.form.isEspecial && p.factura.cantidad > p.form.disponible[0].cantidad)
							{
								$scope.alerts.existencias = true;
								return;
							}
							$scope.productoSuficiente(index);
							$scope.productoCantidad(index);
						}
						
						if((p.form.isEspecial && $.trim(p.factura.especial).length > 0) || p.factura.id_producto > 0)
						{
							//Unidades
							conProductos = true;
							if($.trim(p.factura.unidad.length) == 0)
							{
								p.factura.unidad = 'PZA';
								p.unidad_factura = p.factura.unidad;
							}
						}
					});
					$scope.alerts.productos = !conProductos;
				}
				
				corregirProductos();
			}
			
			//Proseguir
			if($scope.v.$valid && !$scope.alerts.existencias && !$scope.alerts.cliente && !$scope.alerts.productos)
			{
				block();
				$scope.alerts.ongoing = true;
				
				//Limpiar valores
				var valores = angular.copy($scope.f);
				
				try{
					$http.post('ventas_formulario_2016.php', valores)
					.error(function(response){
						$scope.alerts.error = true;
						unBlock();
					}).success(function(response){
						if(typeof response.folio != "undefined" && (response.folio > 0 || response.folio.indexOf('NOTA') >= 0))
							window.location = "comuni-k.php?section=ventas_detalle&folio=" + response.folio + "&serie=" + response.serie;
						else
						{
							$scope.alerts.error = true;
							$scope.error.http = { error: response };
							unBlock();
						}
					}).error(function (error, status){
						$scope.error.http = { error: error, status: status };
					}).finally(function(){
						$scope.alerts.ongoing = false;
					});
				} catch(e){					
					$scope.alerts.error = true;
					$scope.alerts.ongoing = false;
					$scope.error.http = { error: e };
				}
			}
		}
	};
	
	/* Hot Keys */	
	hotkeys.add({
    combo: 'f8',
		allowIn: ['INPUT', 'SELECT', 'TEXTAREA'],
    callback: function() {
      $scope.productoAgregar(true);
    }
  });
	
	hotkeys.add({
		combo: 'f9',
		allowIn: ['INPUT', 'SELECT', 'TEXTAREA'],
		callback: function() {
			if($scope.edicion)
			{
				$scope.f.factura.tipo = ($scope.f.factura.tipo == 'f') ? 'n' : 'f';
				
				if($scope.f.factura.tipo == 'f')
					$('#id_cliente').focus();
				else
					$('.prod-cod:first').focus();
			}
		}
	});
	
	hotkeys.add({
    combo: 'f10',
		allowIn: ['INPUT', 'SELECT', 'TEXTAREA'],
    callback: function() {
      $scope.formSubmit();
    }
  });
	
}]);