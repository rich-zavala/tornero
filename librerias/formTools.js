$(document).ready(function(){
	//Inicializar formulario
	setDatePicker();
	setPops();
});

//Buscador de productos
app.controller('formularioController', [ '$scope', '$rootScope', '$http', '$modalInstance', 'data', function ($scope, $rootScope, $http, $modalInstance, data) {
	$scope.destino = angular.copy(data.data);
	$scope.gui = {
		buscando: false,
		sin_resultados: false,
		error: false
	};
	
	//Buscar
	$scope.buscar = function(e){
		e.preventDefault();
		$scope.resultados = [];
		$scope.gui.error = false;
		$scope.gui.sin_resultados = false;
		
		$http.get(url, {
      params: {
        producto: true,
				producto_string: $scope.param,
				almacen: data.almacen
      }
    })
		.error(function(response){
			$scope.gui.error = true;
    }).success(function(response){
      if(response.length > 0)
			{
				$scope.resultados = response;
			}
			else
			{
				$scope.gui.sin_resultados = true;
			}
    }).finally(function(){
			$scope.gui.buscando = false;
		});
	}
	
	//Agregar a la venta
	$scope.agregar = function(p){
		$rootScope.$broadcast('agregarProductoModal', { index: $scope.destino, info: p });
		$scope.cancelar();
	}
	
	//Cerrar modal
	$scope.cancelar = function () {
		$modalInstance.dismiss('cancel');
	};
}]);

//Campo numérico
app.directive('validNumber', function() {
	return {
		require: '?ngModel',
		link: function(scope, element, attrs, ngModelCtrl) {
			if(!ngModelCtrl) {
				return; 
			}

			ngModelCtrl.$parsers.push(function(val) {
				if (angular.isUndefined(val)) {
						var val = '';
				}
				var clean = val.replace(/[^0-9\.]/g, '');
				var decimalCheck = clean.split('.');

				if(!angular.isUndefined(decimalCheck[1])) {
						decimalCheck[1] = decimalCheck[1].slice(0,3);
						clean =decimalCheck[0] + '.' + decimalCheck[1];
				}

				if (val !== clean) {
					ngModelCtrl.$setViewValue(clean);
					ngModelCtrl.$render();
				}
				return clean;
			});

			element.bind('keypress', function(event) {
				if(event.keyCode === 32) {
					event.preventDefault();
				}
			});
		}
	};
});