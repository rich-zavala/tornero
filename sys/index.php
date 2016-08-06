<?php
session_start();
session_destroy();

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	Conectar();
	
	//Inicializar clientes
	$s = "CALL clientes2Memory();";
	mysql_query($s) or die ("{$s}" . mysql_error());
	$s = "CALL productos2Memory();";
	mysql_query($s) or die ("{$s}" . mysql_error());

		$strSQL = "SELECT u.*, tu.almacenes AS 'usuario_almacenes', tu.descripcion AS 'tipousuario' 
					FROM usuarios u
					INNER JOIN tipos_usuarios tu ON tu.id_tipousuario = u.id_tipousuario
					WHERE u.username LIKE '{$_POST['username']}' AND u.contrasena = MD5('{$_POST['contrasena']}') AND u.status = 0 GROUP BY id_usuario";
		//echo nl2br($strSQL);
		$reg = mysql_query($strSQL) or die (mysql_error());
		if(mysql_num_rows($reg)==1){
			session_unset();
			
			$_SESSION = array_merge($_SESSION, mysql_fetch_array($reg, MYSQL_ASSOC));
			//Inicio de creación de arraglo de almacenes
			$almacenes_todos[] = array();
			$x=0;
			$query_a = mysql_query("SELECT * FROM almacenes WHERE status = 0 ORDER BY descripcion");
			while($a = mysql_fetch_array($query_a)){
				$almacenes_todos[$x][id] = $a[id_almacen];
				$almacenes_todos[$x][descripcion] = $a[descripcion];
				$x++;
			}
			if($_SESSION[almacenes] == "*"){ //Para Almacenes Definidos
				$almacenes = $almacenes_todos;
			} else {
				$a = explode("-",$_SESSION[almacenes]);
				foreach($a as $k => $v){
					foreach($almacenes_todos as $kk => $vv){
						if($almacenes_todos[$kk][id] == $v){
							$almacenes[$k][id] = $vv[id];
							$almacenes[$k][descripcion] = $vv[descripcion];
						}
					}
				}
			}
			$_SESSION[almacenes] = $almacenes;
			
			$s = "SELECT dolar FROM vars";
			$q = mysql_query($s);
			$dolar = mysql_fetch_assoc($q);
			$_SESSION[dolar] = $dolar[dolar];
			//Fin de creación de arreglo de almacenes
			$_SESSION['loggin'] = true;
			//Verificar Variables de Entorno
			$s = "SELECT * FROM vars WHERE nombre != ''";
			$q = mysql_query($s) or die (mysql_error());
			
			if(mysql_num_rows($q) == 0){
				header("location: comuni-k.php?section=vars");
			} else {
	      header("location: comuni-k.php?section=panel_control");
			}
		} else {
			$error = true;
			$msg = "Nombre de usuario o contrase&ntilde;a incorrecta.";
			$_SESSION['loggin'] = false;
		}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Comuni-K</title>
<link rel="stylesheet" type="text/css" href="css/body.css">
<!--[if IE 6]><script language="javascript" type="text/javascript">alert("Usted está usando IE 6.\nEl sistema Comuni-K puede no funcionar correctamente.\nEs recomendable utilizar:\nIE 8 o Superior\nFirefox\nGoogle Chrome")</script><![endif]-->  
</head>
<body>
<center>
  <img src="imagenes/comuni-k.png" width="400" height="154" />
</center>
<form id="form1" name="form1" method="post" action="">
  <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla">
    <tr>
      <td colspan="2" style="color:#FFF; border:0px; border-bottom:#000 1px solid" class="header_h1"><center><h2 style="margin:0px; padding:0px;">Inicio de sesi&oacute;n</h2></center></td>
    </tr>
    <?php if(isset($error)){?>
    <tr>
      <td colspan="2" id="aviso"><center>
        <b>Nombre de usuario o contrase&ntilde;a incorrecto.</b>
      </center></td>
    </tr>
    <?php } ?>
    <tr>
      <th style="text-align:right">Nombre de usuario </th>
      <td><input name="username" type="text"  id="username" value="" size="30" autofocus /></td>
    </tr>
    <tr>
      <th style="text-align:right">Contrase&ntilde;a</th>
      <td><input name="contrasena" type="password"  id="contrasena" value="" size="30" /></td>
    </tr>
    <tr>
      <td colspan="2" style="color:#FFF; border:0px; border-bottom:1px solid #000" class="header_h1"><center><input name="Submit" type="submit" class="boton" value="Iniciar sesi&oacute;n" /></center></td>
    </tr>
  </table>
  <input type="hidden" name="MM_insert" value="form1" />
</form><br />
<br />

<div style="clear:both; text-align:center; font-size:10px; color:#999;" id="footer">&copy; Comuni-K. Vizión Empresarial. Todos los derechos reservados 2009.</div>
</body>
</html>