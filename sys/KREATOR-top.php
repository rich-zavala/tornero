<html>
<head>
<script type="text/javascript" language="javascript">
var hs = {
    expand: function (element, params, custom) {
        parent.window.focus(); // to allow keystroke listening
        return parent.window.hs.expand(element, params, custom);
    },
    htmlExpand: function (element, params, custom) {
        return parent.window.hs.htmlExpand(element, params, custom);
    }
}
</script>
<style>
body {
	font-size:12px;
	font-family: "Arial";
	margin:0px;
	padding:0px;
	overflow-y:hidden;
	overflow-x:auto;
}
img {
	border: 0px;
}
</style>
<link href="css/body.css" rel="stylesheet" type="text/css">
</head>
<body class="header_important">
<?php
/*/////////////////////////////////////////
Creador de vínculos para la barra de herramientas
Se divide por cada página definida por $_GET['section']
Valores:
[0] > Archivo al que se vincula. Incluye extensión y parámetros en método GET
[1] > Texto
[2] > Nombre de la imágen que se mostrará en el panel de control. SIN EXTENSIÓN.
[3] > Valores binarios booleanos para desactivar/activar HighSlide.
[4] > Altura de HighSlide.
[5] > Ancho de HighSlide.
*/////////////////////////////////////////
@session_start();
switch($_GET['section']){
	case "panel_control":
		$o = array();
	break;
	
	case (
				$_GET['section'] == "usuarios_formulario" ||
				$_GET['section'] == "clientes_formulario" ||
				$_GET['section'] == "proveedores_formulario" ||
				$_GET['section'] == "productos_formulario" ||
				$_GET['section'] == "productos_masivo" ||
				$_GET['section'] == "listas_de_precios" ||
				$_GET['section'] == "pedidos_formulario" ||
				$_GET['section'] == "pedidos_detalle" || 
				$_GET['section'] == "traspasos_formulario" || 
				$_GET['section'] == "traspasos_detalle" ||
				$_GET['section'] == "existencias_modificar" ||
				$_GET['section'] == "contrarecibos_formulario_compras" ||
				$_GET['section'] == "contrarecibos_formulario_servicios" ||
				$_GET['section'] == "contrarecibos_detalle" ||
				$_GET['section'] == "compras_formulario" ||
				$_GET['section'] == "compras_detalle" ||
				$_GET['section'] == "compra_pagos" ||
				$_GET['section'] == "compra_pagos_detalle" ||
				$_GET['section'] == "gastos_formulario" ||
				$_GET['section'] == "gastos_detalle" ||
				// $_GET['section'] == "devoluciones_p_formulario" ||
				// $_GET['section'] == "devoluciones_p_detalle" ||
				// $_GET['section'] == "devoluciones_c_formulario" ||
				// $_GET['section'] == "devoluciones_c_detalle" ||
				$_GET['section'] == "ventas_formulario" ||
				$_GET['section'] == "ventas_detalle" ||
				$_GET['section'] == "ventas_clon" ||
				$_GET['section'] == "ingresos_detalle" ||
				$_GET['section'] == "ingresos_por_cliente" ||
				$_GET['section'] == "ingresos_varios_clientes" ||
				$_GET['section'] == "notas_c_formulario" ||
				$_GET['section'] == "notas_c_detalle" ||
				$_GET['section'] == "notas_p_formulario" ||
				$_GET['section'] == "notas_p_detalle" ||
				$_GET['section'] == "reporte_utilidades" ||
				$_GET['section'] == "reporte_costos" ||
				$_GET['section'] == "reporte_movimientos" ||
				$_GET['section'] == "reporte_vendedor" ||
				$_GET['section'] == "reporte_grupo" ||
				$_GET['section'] == "reporte_cliente" ||
				$_GET['section'] == "vectors_factura" ||
				$_GET['section'] == "vectors_nota" ||
        $_GET['section'] == "vectors_banco" ||
        $_GET['section'] == "vectors_contrarecibo" ||
				$_GET['section'] == "vectors_credito" ||
				$_GET['section'] == "mermas_formulario" ||
				$_GET['section'] == "mermas_detalle" ||
				$_GET['section'] == "conversiones_formulario" ||
				$_GET['section'] == "ventas_edit" ||
				$_GET['section'] == "conversiones_detalle" ||
				$_GET['section'] == "cotizaciones_detalle" ||
				$_GET['section'] == "reporte_cobros" ||
				$_GET['section'] == "reporte_ventas_totales" ||
				$_GET['section'] == "cotizaciones_formulario" ||
				$_GET['section'] == "retiros_detalle"
				):
	$o = array(
						 array($_SESSION['start'],"Regresar","bak",0),
						 array("KREATOR.php?small","Módulos","panel",1,430,300)
						 );
	break;
	
	case ($_GET['section'] == "corrige_precios"):
	$o = array(
						 array($_SESSION['start'],"Inicio","bak",0),
						 array("KREATOR.php?small","Módulos","panel",1,430,300)
						 );
	break;
	
	case ($_GET['section'] == "venta_transform"):
	$o = array(
						 array($_SESSION['start'],"Lista de Ventas","bak",0),
						 array("KREATOR.php?small","Módulos","panel",1,430,300)
						 );
	break;
	
/*	case "bancos"://29/10/2010
	$o = array(
						 array("bancos_promt.php","Bancos","bancos",1,400,300),
						 array("KREATOR.php?small","Módulos","panel",1,430,300)
						 );
	break;*/
	
	default:
	$o = array(
						array("KREATOR.php?small","Módulos","panel",1,430,300)
						);
	break;
}

?>
<table height="100%" align="center">
<tr>
<?php
foreach($o AS $s){
	if($s[3]==0){
?>
<td align="center"
    style="padding:0px 10px 0px 10px; cursor:pointer; text-decoration: none; font-size:10px; color:#FFF;"
    valign="middle"
    class="icon_normal"
    onMouseOver="this.className = 'icon_over';"
    onMouseOut="this.className = 'header_important';"
    onClick="top.window.location='<?=$s[0]?>'"
    id="<?=$s[0]?>"
    nowrap>
<img src="iconos-mini/<?=$s[2]?>.png" /><br /><center id="top_title"><?=$s[1]?></center>
</td>
<?php
	} else {
  if(!function_exists("Administrador")){include('KREATOR-USUARIOS.php');}
	//Contar módulos de acceso
  if($s[1] == "Módulos")
	{
  	$c = ceil((count($o)+1)/5);
  	if($c == 1)
		{
  		$n = 180;
  	}
		else 
		{
  		$n = 180 + (66 * ($c-1));
  	}
  }
	else
	{
    $n = 0;
  }
?>
<td align="center"
    style="padding:0px 10px 0px 10px; cursor:pointer; text-decoration: none; font-size:10px; color:#FFF;"
    valign="middle"
    class="icon_normal"
    onMouseOver="this.className = 'icon_over';"
    onMouseOut="this.className = 'header_important';"
    onClick="return hs.htmlExpand(document.getElementById('xx<?=$s[0]?>'),{objectType:'ajax',headingText:'<?=$s[1]?>',width:800,height:<?=$n?>,wrapperClassName:'draggable-header no-footer'})"
    id="<?=$s[0]?>"
    nowrap>
<img src="iconos-mini/<?=$s[2]?>.png" /><br /><center id="top_title"><?=$s[1]?></center>
<a href="<?=$s[0]?>" style="display:none" id="xx<?=$s[0]?>">0</a>
</td>
<?php
	}
}
?>
</tr>
</table>
</body>
</html>