<?php
if(isset($_GET['small'])){
	session_start();
}
/*/////////////////////////////////////////
Creador de vínculos para el panel de control según id_tipousuario
Valores:
[0] > Archivo al que se vincula. Incluye extensión y parámetros en método GET
[1] > Título que se mostrará en el panel de control.
[2] > Nombre de la imagen que se mostrará en el panel de control. SIN EXTENSIÓN.
[3] > Valores binarios booleanos para desactivar/activar LightBox.
[4] > Altura de LightBox.
[5] > Ancho de LightBox.
*/////////////////////////////////////////
$almacenes=array("comuni-k.php?section=almacenes","Almacenes","almacenes",0);
$bancos_promt=array("bancos_promt.php","Bancos","bancos",1,400,300);//29/10/2010
$productos=array("comuni-k.php?section=productos","Cat&aacute;logo de Productos","productos",0);
$clientes_vendedor=array("comuni-k.php?section=clientes_vendedor","Clientes","clientes",0);
$clientes=array("comuni-k.php?section=clientes","Clientes","clientes",0);
$compras=array("comuni-k.php?section=compras","Compras y Cuentas por pagar","compras",0);
// // $contrarecibos=array("comuni-k.php?section=contrarecibos","Contrarecibos","contrarecibos",0);
$cxc=array("comuni-k.php?section=cxc","Cuentas por Cobrar","cobros",0);
$cxp=array("comuni-k.php?section=compras","Cuentas por Pagar","cobros",0);
// $devoluciones=array("devoluciones_promt.php","Devoluciones","devoluciones",1,210,300);
// $devoluciones_p=array("comuni-k.php?section=devoluciones_p","Devoluciones","devoluciones",0);
// $devoluciones_c=array("comuni-k.php?section=devoluciones_c","Devoluciones","devoluciones",0);
$existencias=array("comuni-k.php?section=existencias","Existencias","existencias",0);
// $gastos=array("comuni-k.php?section=gastos","Pagar Gastos","pagos",0);
$ventas=array("comuni-k.php?section=ventas","Ventas","ventas",0);
$notas_de_credito=array("notas_promt.php","Notas de Cr&eacute;dito","credito",1,400,400);
$notas_c=array("comuni-k.php?section=notas_c","Notas de Cr&eacute;dito","credito",0);
$notas_p=array("comuni-k.php?section=notas_p","Notas de Cr&eacute;dito","credito",0);
$pedidos=array("comuni-k.php?section=pedidos","Pedidos","pedidos",0);
$proveedores=array("comuni-k.php?section=proveedores","Proveedores","proveedores",0);
$reportes=array("comuni-k.php?section=reportes","Reportes","reportes",0);
// $traspasos=array("comuni-k.php?section=traspasos","Traspasos","traspasos",0);
$usuarios=array("comuni-k.php?section=usuarios","Usuarios","usuarios",0);
// $mermas=array("comuni-k.php?section=mermas","Mermas","mermas",0);
// $conversiones=array("comuni-k.php?section=conversiones","Convertir Mermas","conversiones",0);
$facturar=array("comuni-k.php?section=facturar","Facturar varias Notas","facturar",0);
$cotizar=array("comuni-k.php?section=cotizaciones","Cotizaciones","cotizaciones",0);
$retiros=array("comuni-k.php?section=retiros","Retiros de LACATOSA","retiros",0);

//Agosto 2016
$recargos=array("comuni-k.php?section=recargos","Recargos","recargos",0);

$panel=array("comuni-k.php?section=panel_control","Inicio","panel",0);

switch($_SESSION['id_tipousuario']){
  case "1": //Administrador
    $o = array(
      $ventas,
			$cotizar,
      $cxc,
      // $contrarecibos,
      // $devoluciones,
      // $gastos,
      $compras,
			$bancos_promt,//29/10/2010
      $notas_de_credito,
      $pedidos,
      // $traspasos,
      $productos,
      $existencias,
      $almacenes,
      $usuarios,
			// $mermas,
			// $conversiones,
      $facturar,
      $reportes,
      $clientes,
      $proveedores,
			$retiros,
			$recargos
    );
  break;
  case "2": //Proveedores
    $o = array(
      $proveedores
    );
  break;
  case "3": //C.C.C.
    $o = array(
			$cxc,
			// $contrarecibos,
			// $gastos,
			$usuarios,
			$clientes,
			$proveedores
    );
  break;
  case "4": //Contador
    $o = array(
			$ventas,
			$reportes
    );
  break;
  case "5": //Compras y Ventas
    $o = array(
			$ventas,
			$compras,
			$facturar,
			$existencias,
			$productos,
			$clientes,
			$proveedores,
			$retiros
    );
  break;
  case "6": //Ventas
    $o = array(
			$ventas,
			$cxc,
			$notas_c,
			$facturar,
			$existencias,
			$productos,
			$clientes,
			$retiros
    );
  break;
  case "7": //compras
    $o = array(
			$compras,
			// $devoluciones_p,
			$notas_p,

			$pedidos,
			// $traspasos,
			$productos,
			$existencias,
			$usuarios,
			// $mermas,
			// $conversiones,
			$proveedores
    );
  break;
}
?>