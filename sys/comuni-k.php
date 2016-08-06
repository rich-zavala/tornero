<?php
header("Content-type: text/html; charset=iso-8859-1");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
session_start();
if (!isset($_SESSION) or !isset($_SESSION['nombre']) or !isset($_SESSION['id_tipousuario']))
  header("Location: index.php");

include ("funciones/basedatos.php");
include ("funciones/funciones.php");

//Obtener información de la sección
ob_start();
include ("KREATOR-USUARIOS-ACCESS.php");
include ($_GET[section] . ".php");
$seccion = ob_get_clean();

//El parámetro "ok" sirve para alertar al usuario que un registro ha sido ingresado correctamente
if (isset($_GET[ok])) alerttogo("get", "ok", "Registro aprobado.");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Comuni-K</title>
	<link rel="stylesheet" type="text/css" href="css/body.css" />
	<link rel="stylesheet" type="text/css" href="css/exclusivo.css" />
	<link rel="stylesheet" type="text/css" href="js/highslide/highslide.css" />
	<link rel="stylesheet" type="text/css" href="js/calendar/calendar.css" />
	<script type="text/javascript" src="js/calendar/calendar.js"></script>
	<script type="text/javascript" src="js/ajax.js"></script>
	<script type="text/javascript" src="js/php.js"></script>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/highslide/rich.js"></script>
	<script type="text/javascript" src="js/highslide/comuni-k_settings.js"></script>
	<script type="text/javascript" src="js/funciones.js"></script>
</head>
<body>
	<span id="_extra"></span>
	<?php
	include ("header.php");

	//Limpiar POST para mysql
	if (count($_POST) > 0) foreach ($_POST as $k => $v) if (!is_array($v)) $_POST[$k] = mysql_real_escape_string($v);
	?>

	<div style="margin-top:6px;">
	<span id="contenido_espera">
	<center><b>Espere mientras se carga la informaci&oacute;n...</b></center>
	</span>
	<span id="contenido_principal" style="display:none;">
	<?php
	if ($__acceso > 0) {
		echo $seccion;
	} else {
		echo "<center><h4>No cuenta con los permisos suficientes para acceder a este m&oacute;dulo</h4></center>";
		titleset("M&oacute;dulo no disponible.");
	}
	?>
	</span>
	</div>
	<div style="clear:both; text-align:center; font-size:10px; color:#999; margin-top: 40px;" id="footer">&copy; Comuni-K. Vizión Empresarial. Todos los derechos reservados 2009.</div>
	<script type="text/javascript" language="javascript">
	</script>
	</body>
</html>