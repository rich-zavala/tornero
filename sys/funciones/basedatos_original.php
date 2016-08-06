<?php
if (!isset($_GET['sinsession'])){
	session_start();
}

define('PATH', 'http://localhost/tornero/');
define('DIR', 'C:/Comuni-K/data/tornero/');

define('DB_NAME', 'comuni-k_tornero');
define('DB_USER', 'coneccion');
define('DB_PASSWORD', 'Vizion2010');
define('DB_HOST', 'localhost');

function Conectar(){	
	$conn = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
	if(!$conn){
		die('<br>No se pudo conectar a la base de datos: ' . mysql_error());
		return 0;
	}
	mysql_SELECT_db(DB_NAME,$conn);
}

Conectar();
?>