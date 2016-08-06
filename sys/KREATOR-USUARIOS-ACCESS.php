<?php
@session_start();

$_permisos = array();
$_restric = array();

switch($_SESSION[id_tipousuario]){
  case "2": //Proveedores
    $_permisos = array(
      "proveedores"
      );
    //$_restric = array("cxc","contrarecibos", "gastos", "bancos","notas","almacenes","pedidos_formulario","productos_formulario","existencias_modificar","clientes_formulario","proveedor","trans");
  break;
  
  case "3": //C.C.C.
    $_permisos = array(
			"cxc", "ingresos_por_cliente", "ingresos_varios_clientes", "ventas_detalle",
			"contrarecibos",
			"gastos",
			"usuarios",
			"clientes",
			"proveedores"
      );
    $_restric = array("clientes_formulario", "proveedores_formulario");
  break;
  
  case "4": //Contador
    $_permisos = array(
		"ventas",
        "reporte"
      );
    $_restric = array("formulario","banco","conver");
  break;
  
  case "5": //Compras y Ventas
    $_permisos = array(
			"ventas",
			"compras",
			"facturar",
			"existencias",
      "productos",
			"clientes",
			"proveedores"
      );
    $_restric = array("clientes_formulario","modificar");
  break;
  
  case "6": //Ventas
    $_permisos = array(
			"ventas",
			"cxc",
			"notas_c",
			"facturar",
			"existencias",
			"productos",
			"clientes"
      );
    $_restric = array("ingresos_por_cliente", "ingresos_varios_clientes");
  break;
  
  case "7": //Compras
    $_permisos = array(
			"compras", "compra_pagos",
			// "devoluciones_p",
			"notas_p",
			"pedidos",
			"traspasos",
			"productos",
			"existencias",
			"usuarios",
			"mermas",
			"conversiones",
			"proveedores"
      );
    $_restric = array("ingresos_por_cliente", "ingresos_varios_clientes", "modificar");
  break;
}

$_permisos[] = "panel_control";
$_permisos[] = "usuarios_perfil";

//RESTRICCIONES
$__acceso = 0;
foreach($_permisos as $k => $v){
  $__acceso += substr_count($_GET[section],$v); //<< BUG CON "clientes" y "ingresos_varios_clientes"
}

foreach($_restric as $k => $v){
  if(substr_count($_GET[section],$v)>0){
    $__acceso = 0;
  }
}

if($_SESSION[id_tipousuario] == 1){
  $__acceso = 1;
}

function Administrador(){if($_SESSION[id_tipousuario] == 1){return true;}};
function Proveedores(){if($_SESSION[id_tipousuario] == 2){return true;}}
function CCC(){if($_SESSION[id_tipousuario] == 3){return true;}}
function Contador(){if($_SESSION[id_tipousuario] == 4){return true;}}
function ComprasVentas(){if($_SESSION[id_tipousuario] == 5){return true;}}
function Ventas(){if($_SESSION[id_tipousuario] == 6){return true;}}
function Compras(){if($_SESSION[id_tipousuario] == 7){return true;}}

/*
function Almacenista(){if($_SESSION[id_tipousuario] == 1 || $_SESSION[id_tipousuario] == 2 || $_SESSION[id_tipousuario] == 3){return true;}}
function Almacenista_Supervisor(){if($_SESSION[id_tipousuario] == 1 || $_SESSION[id_tipousuario] == 3){return true;}}
function Compras(){if($_SESSION[id_tipousuario] == 1 || $_SESSION[id_tipousuario] == 2){return true;}}
function Compras_Supervisor(){if($_SESSION[id_tipousuario] == 1 || $_SESSION[id_tipousuario] == 2 || $_SESSION[id_tipousuario] == 3){return true;}}
function CXC(){if($_SESSION[id_tipousuario] == 1){return true;}}
function CXC_Supervisor(){if($_SESSION[id_tipousuario] == 1){return true;}}
function CXP(){if($_SESSION[id_tipousuario] == 1){return true;}}
function CXP_Supervisor(){if($_SESSION[id_tipousuario] == 1){return true;}}
function Contrarecibos(){if($_SESSION[id_tipousuario] == 1){return true;}}
function Contrarecibos_Supervisor(){if($_SESSION[id_tipousuario] == 1){return true;}}
function Ventas(){if($_SESSION[id_tipousuario] == 1 || $_SESSION[id_tipousuario] == 2){return true;}}
function Ventas_Supervisor(){if($_SESSION[id_tipousuario] == 1){return true;}}
*/
?>