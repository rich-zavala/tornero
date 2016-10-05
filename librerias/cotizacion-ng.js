'use strict';
var url = "ajax_tools_2016.php";
var app = angular.module('app', ['ui.bootstrap', 'cfp.hotkeys']);
app.controller('cotizacionFormulario', ['$scope', '$rootScope', '$http', '$filter', '$modal', '$timeout', 'hotkeys', function($scope, $rootScope, $http, $filter, $modal, $timeout, hotkeys) {
	//Herramientas
	$scope.moment = moment;
	$scope.setPops = setPops;
	$scope.unidades = _unidades;
	
	//Información inicial de factura
	$scope.f = {
		edicion: false,
		factura: {
			folio: _folio,
			cliente: null,
			datos_cliente: null,
			fecha: moment().format("YYYY-MM-DD"),
			moneda: 'M.N.',
			id_facturista: _facturista,
			importe: 0
		},
		productos: []
	};
	
	//Actualizar folio de los productos cuando cambie el folio
	$scope.$watch('f.factura.folio', function(f){
		angular.forEach($scope.f.productos, function(p, index){ p.folio(); });
	});
	
	//Información regular de producto
	var producto_info = function(){
		this.factura = {
			folio_cotizacion: null,
			id_producto: null,
			cantidad: 0,
			unidad: 'PZA',
			especial: '',
			complemento: '',
			precio: 0,
			iva: 16,
			importe: 0
		};
		
		this.form = {
			ajax: false,
			notFound: false,
			isEspecial: false,
			codigo: '',
			descripcion: ''
		};
		
		this.folio = function(){
			this.factura.folio_cotizacion = $scope.f.factura.folio;
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
			$scope.$watch('f.productos[' + index + '].factura.cantidad', function(o){ $scope.sumar(); });
			$scope.$watch('f.productos[' + index + '].factura.precio', function(o){ $scope.sumar(); });
			$scope.$watch('f.productos[' + index + '].factura.iva', function(o){ $scope.sumar(); });
		}catch(e){ c(e); };
		
		return $scope.f.productos[index];
	};
	
	/* Controlador de cotización */
	$scope.agregarProductos = function()
	{
		if(_productos.length > 0)
		{
			ignorarCambios = true;
			for(var i in _productos)
			{
				$scope.productoInicial(_productos[i]);
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
	$scope.productoInicial = function(item){
		var dispo = angular.fromJson(item.dispo);
		var isEspecial = parseInt(item.especial) != 0;
		
		var producto = $scope.productoAgregar();
		producto.factura.almacen = $scope.f.factura.id_almacen;
		producto.factura.id_producto = item.id_producto;
		producto.factura.unidad = (item.unidad != null) ? item.unidad : 'PZA';
		producto.factura.complemento = item.complemento;
		producto.factura.cantidad = item.cantidad;
		producto.factura.precio = item.precio;
		producto.factura.iva = item.iva;
		producto.factura.importe = item.importe;
		producto.form.codigo = item.codigo;
		producto.form.descripcion = item.descripcion;
		
		if(parseInt(item.especial) != 0 && item.especial.length > 0)
		{
			producto.form.isEspecial = true;
			producto.factura.especial = item.especial;
		}
	}
	
	/* Manejador de producto */
	$scope.productoBuscar = function(productoInput){
		return $http.get(url, {
      params: {
        producto: true,
				producto_codigo: productoInput
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
			producto.factura.precio = item.precio;
			producto.factura.iva = item.iva;
			producto.factura.unidad = item.unidad;
			producto.form.descripcion = item.descripcion;
			
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
		if(keyEvent.which === 13)
		{
			$scope.focusMe(index);
			keyEvent.preventDefault();
		}
		else if(keyEvent.which === 9 && $scope.f.productos[index].form.ajax)
		{
			keyEvent.preventDefault();
		}
	}

	//Modal de productos
	$scope.productoBuscarModal = function(index){
		block();
		var data = {
			data: index
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
		angular.forEach($scope.f.productos, function(p){			
			var p = p.factura;
			$scope.sumas.subtotal += Math.round((p.cantidad * p.precio) * 100) / 100;
			$scope.sumas.iva += Math.round((p.cantidad * (p.precio * ( p.iva / 100 ))) * 100) / 100;
		});
	}
	
	/* Manejador de edición */
	if(_edicion)
	{
		$scope.f.edicion = true;
		$scope.f.factura.cliente = _cliente;
		$scope.f.factura.fecha = _fecha;
		$scope.f.factura.datos_cliente = _datos_cliente;
		$scope.f.factura.moneda = _moneda;
	}
	
	/* Verificador de folio */
	// PENDIENTE
	
	/* Submit! */
	//Set alerts
	$scope.alerts = { ongoing: false };
	$scope.setAlerts = function(){
		$scope.alerts = {
			productos: false,
			existencias: false,
			cliente: false,
			error: false,
			errorDefinido: false,
			errorDetalle: null,
			ongoing: false
		};
	};
	
	$scope.formSubmit = function(){
		if(!$scope.alerts.ongoing) $scope.setAlerts();
		if(!$scope.alerts.ongoing)
		{
			$scope.v.$setSubmitted();
			
			if($scope.v.$invalid)
			{
				for(var i in $scope.v.$error.required)  if($scope.v.$error.required[i].$name == 'cliente') $scope.alerts.cliente = true;
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
						
						if((p.form.isEspecial && $.trim(p.factura.especial).length > 0) || p.factura.id_producto > 0)
						{
							//Unidades
							if($.trim(p.factura.unidad.length) == 0) p.factura.unidad = 'PZA';
							conProductos = true;
						}
						
						//Corregir folio
						// console.log(p);
						// console.log($scope.f.factura.folio_cotizacion);
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
				
				$http.post('cotizaciones_formulario_2016.php', valores)
				.error(function(response){
					$scope.alerts.error = true;
					unBlock();
				}).success(function(response){
					if(response.error == 0)
					{
						if(response.folio > 0)
						{
							window.location = "comuni-k.php?section=cotizaciones_detalle&folio=" + response.folio;
						}
						else
						{
							$scope.alerts.error = true;
							unBlock();
						}
					}
					else
					{
						$scope.alerts.errorDefinido = true;
						$scope.alerts.errorDetalle = response.msg;
						unBlock();
					}
				}).finally(function(){
					$scope.alerts.ongoing = false;
				});
			}
		}
	}
	
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
      $scope.f.factura.tipo = ($scope.f.factura.tipo == 'f') ? 'n' : 'f';
			
			if($scope.f.factura.tipo == 'f')
				$('#id_cliente').focus();
			else
				$('.prod-cod:first').focus();
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