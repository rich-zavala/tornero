<?php
/*
Este archivo tambien funge como inicializador de configuraciones.
Se encuentra incluido en cada script del sistema
*/

//Inicializador de sesiones
//if (!isset($_GET['sinsession']) and session_status() == PHP_SESSION_NONE)
@session_start();

//No mostrar reportes de notices o deprecated (Sí que es viejo el sistema :/ )
// error_reporting(E_ALL &~ E_NOTICE &~ E_DEPRECATED &~ E_WARNING);
error_reporting( error_reporting() & ~E_NOTICE );

//Inicializador de constantes
define('PATH', 'http://localhost/tornero/sys/'); //Ruta de acceso al sistema
define('DIR', 'C:/xampp/htdocs/tornero/sys/'); //Ruta de raíz absoluta del sistema

//Variables de configuracion de base de datos
define('DB_NAME', 'tornero_2016');
define('DB_USER', 'root');
define('DB_PASSWORD', 'rich2011');
define('DB_HOST', 'localhost');

//Conectar a la base de datos
$db;
function Conectar(){
	$cn = mysql_connect(DB_HOST, DB_USER,DB_PASSWORD);
	if(!$cn)
	{
		die('<br>No se pudo conectar a la base de datos: ' . mysql_error());
		return 0;
	}
	
	//Conectar de manera nativa
	mysql_query("SET NAMES latin1") or die(mysql_error());
	mysql_select_db(DB_NAME, $cn);
	
	//Establecer objeto dbFacile
	//Include para comuni-k y para advans
	foreach(array('funciones/dbFacile.php', '../sys/funciones/dbFacile.php', 'dbFacile.php') as $file) if(file_exists($file) && !class_exists('dbFacile_mssql')) include($file);
	
	global $db;
	// $db = dbFacile::open('mysql', DB_NAME, DB_USER, DB_PASSWORD, DB_HOST);
	$db = dbFacile::bindConnection($cn);
}

Conectar();
?>