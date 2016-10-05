<?php
/*
23 Sep
Garantizar que la sesión pertenece al sistema.
Si hay sesión en tornero y por URL se accede a HC o viceversa reiniciar la sesión.
*/
try {
	// p($_SESSION);
  if(isset($_SESSION['entorno']) and substr_count(__FILE__, $_SESSION['entorno']) == 0)
	{
		echo '<meta http-equiv="Refresh" content="0;index.php">';
		exit;
	}
} catch (Exception $e) {}

// Esta función regresa el valor en días de dos fechas dadas
// Si diferencia > 0 entonces Fecha1 > Fecha2   Faltan N días para llegar a la Fecha2
// Si diferencia < 0 entonces Fecha1 < Fecha2   Ya pasaron N días después de la Fecha2
// Si diferencia = 0 entonces Fecha1 = Fecha2
function DiferenciaFechas($fecha1, $fecha2){
	list($fecha1,$hora1) = explode(" ", $fecha1);
	list($anio, $mes, $dia) = explode("-", $fecha1);
	$fecha1 = mktime(0,0,0, $mes, $dia, $anio);
	list($fecha2,$hora2) = explode(" ", $fecha2);
	list($anio, $mes, $dia) = explode("-", $fecha2);
	$fecha2 = mktime(0,0,0, $mes, $dia, $anio);
	$diferencia = ($fecha1-$fecha2) / 86400; // 86400 = 60seg * 60min * 24 hrs = Cantidad de segundos que transcurre en un día.
	return ceil($diferencia);		
}

// Esta función regresa el nombre del mes
function Meses($m){
	$meses = array("", "Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	return $meses[$m];
}

// Esta función regresa el nombre del día de la semana Domingo = 0, Lunes = 1, ..., Sábado = 6
function Dias($d){
	$dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
	return $dias[$d];
}

// Convierte el formato de Fecha de MYSQL a una frase. Ejemplo: 2007-25-05  ==>  25/05/2007
function FormatoFechaFrase($f){
	list($fecha, $hora) = explode(" ", $f);
	list($anio, $mes, $dia) = explode("-", $fecha);
	//$ff = $dia." de ".Meses((int) $mes)." del ".$anio;
	$ff = $dia."/".$mes."/".$anio;
	return $ff;
}

// Convierte el formato de Fecha de MYSQL a una frase. Ejemplo: 2007-25-05  ==>  05 de Mayo del 2007
function FormatoFechaFrase2($f){
	list($fecha, $hora) = explode(" ", $f);
	list($anio, $mes, $dia) = explode("-", $fecha);
	$ff = $dia." de ".Meses((int) $mes)." del ".$anio;
	return $ff;
}

// Devuelve la cadena PM o AM, dependiendo de la hora que sea
function FormatoHora($f){
	list($fecha, $hora) = explode(" ", $f);
	list($hora, $min, $seg) = explode(":", $hora);
	if($hora >=12) {
		$texto = "pm";
		if($hora>12) { $hora -= 12; }
	} else {
		$texto = "am";
	}
	$hh = $hora.":".$min.$texto;
	return $hh;
}

// Convierte el formato de Fecha-Hora de MYSQL a una frase. Ejemplo: 2007-25-05 12:30:25  ==>  05 de Mayo del 2007 a las 12:30:25
function FormatoFechaHoraFrase($f){
	list($fecha, $hora) = explode(" ", $f);
	list($anio, $mes, $dia) = explode("-", $fecha);
	$ff = $dia." de ".Meses((int) $mes)." del ".$anio." a las ".FormatoHora($hora);
	return $ff;
}

// Convierte el formato de Fecha-Hora de MYSQL a una frase. Ejemplo: 2007-25-05 12:30:25  ==>  05 de Mayo del 2007 <br> 12:30:25
function FormatoFechaHoraFrase2($f){
	list($fecha, $hora) = explode(" ", $f);
	list($anio, $mes, $dia) = explode("-", $fecha);
	$ff = $dia." de ".Meses((int) $mes)." del ".$anio."<br>".FormatoHora($hora);
	return $ff;
}

// Regresa una frase de la fecha actual. Ejemplo:  06-23-06-2007  ==>  23 de Junio del 2007
function FechaActual(){
	list($NombreDia, $dia, $mes, $anio) = explode("-",date("w-d-n-Y"));
	$fecha = $dia." de ".Meses($mes)." del ".$anio;
	return $fecha;
}

// Regresa una frase de la fecha actual. Ejemplo:  06-23-06-2007  ==>  23/Junio/2007
function FormatoFecha($f){
	list($fecha, $hora) = explode(" ", $f);
	list($anio, $mes, $dia) = explode("-", $fecha);
	$ff = $dia."/".Meses((int)$mes)."/".$anio;
	return $ff;
}

// Regresa una frase de la fecha actual. Ejemplo:  06-23-06-2007 10:20:00 ==>  23/Junio/2007 \n 10:20
function FormatoFecha2($f){
	list($fecha, $hora) = explode(" ", $f);
	list($anio, $mes, $dia) = explode("-", $fecha);
	list($hh, $mm, $ss) = explode(":", $hora);
	$ff = $dia."/".Meses((int)$mes)."/".$anio."<br>".$hh.":".$mm;
	return $ff;
}

// Regresa una frase de la fecha actual. Ejemplo:  23-06-2007  ==>  23/06/2007
function FormatoFecha3($f){
	list($fecha, $hora) = explode(" ", $f);
	list($anio, $mes, $dia) = explode("-", $fecha);
	$ff = $dia."/".$mes."/".$anio;
	return $ff;
}

function FechaNoticia($f){
	list($fecha, $hora) = explode(" ", $f);
	list($anio, $mes, $dia) = explode("-", $fecha);
	list($hh, $mm, $ss) = explode(":", $hora);
	$ff = $dia." de ".Meses((int) $mes);
	return $ff;
}

function FileExtension($archivo){
	$d = explode(".",$archivo);
	$ultimo = count($d);
	return strtolower($d[$ultimo-1]);
}

function mon($m)
{
	return '<img src="imagenes/'.str_replace(".","",strtolower($m)).'.png" class="tipo_moneda" />';
}

// Esta función convierte caracteres especiales a su código en HTML
function HTML($cadena){
	$acentos = array("á","é","í","ó","ú","ñ","ü","Á","É","Í","Ó","Ú","Ñ","Ü","¿","\n","À","È","Ì","Ò","Ù","à","è","ì","ò","ù");
	$acentosHTML = array("&aacute;","&eacute;","&iacute;","&oacute;","&uacute;","&ntilde;","&uuml;","&Aacute;","&Eacute;","&Iacute;","&Oacute;","&Uacute;","&Ntilde;","&Uuml;","&iquest;","<br>","&Agrave;","&Egrave;","&Igrave;","&Ograve;","&Ugrave;","&agrave;","&egrave;","&igrave;","&ograve;","&ugrave;");

	for($i=0;$i<count($acentos);$i++){
		$cadena = str_replace($acentos[$i],$acentosHTML[$i],$cadena);
	}
	return $cadena;
}

function AscDescImg($TipoOrden){
	if($TipoOrden == "ASC"){
		echo "<img src='imagenes/flecha_arriba.gif' title='Cambiar a orden descendente'>";
	}
	
	if($TipoOrden == "DESC"){
		echo "<img src='imagenes/flecha_abajo.gif' title='Cambiar a orden ascendente'>";
	}
}
	
// RICH FUNCTIONS
function SQLOrden($campo){
	if (isset($_GET['campo'])){
		if($_GET['campo']==$campo & $_GET['order']=="DESC"){
		echo "ASC";
		}
		else
		{
		echo "DESC";
		}
	}
	else{
		$TipoOrden="DESC";
		echo "DESC";
	}
}

function DirFlecha($campo){
	if (isset($_GET['campo'])){
		if ($_GET['campo']==$campo){
      if ($_GET['order']=="DESC"){
        $TipoOrden="DESC";
        AscDescImg($TipoOrden);
			}
      else{
        $TipoOrden="ASC";
        AscDescImg($TipoOrden);
      }
    }
  } 
}

function redon($numero, $decimales = 2) { 
  $factor = pow(10, $decimales); 
  $result = floor($numero * $factor) / $factor; 
  return($result); 
} 


function money($num){
	return number_format($num,2);
}

function fecha1($x){
	$x = explode("-",$x);
	$x = "{$x['2']}-{$x['1']}-{$x['0']}";
	return $x;
}

function print_pre( $array ){
  if ( @is_array ( $array ) )
  {
    print "<pre>";
    print_r ( $array );
		print "</pre>";
  }
}

function p($s)
{
	if(!is_string($s))
	{
		echo "<pre>";
		print_r($s);
		echo "</pre>";
	}
	else
	{
		echo nl2br($s);
	}
}

function existencias($post){
  for($x=0; $x<count($post['id_producto']); $x++){
    if(isset($post['entregados'][$x])){
      $post['cantidad'][$x] = $post['entregados'][$x];
    }
    if($post['id_producto'][$x]>0 && strlen($post['id_producto'][$x])>0 && $post['cantidad'][$x]>0){
      $exS = "SELECT cantidad FROM existencias WHERE id_producto = '{$post['id_producto'][$x]}' AND lote = '{$post['lote'][$x]}' AND id_almacen = '{$post['id_almacen']}'";
      //echo $exS."<br>";
      $exQ = mysql_query($exS) or die (mysql_error());
      $exR = mysql_fetch_assoc($exQ);
      $nueva_existencia = $exR['cantidad']-$post['cantidad'][$x];
      if($nueva_existencia<0){
      
        $sql_A = "SELECT descripcion FROM almacenes WHERE id_almacen = '{$post['id_almacen']}'";
        $sqlQ_A = mysql_query($sql_A) or die (mysql_error());
        $sqlR_A = mysql_fetch_assoc($sqlQ_A);
        $almacen = $sqlR_A['descripcion'];
        
        $sql_P = "SELECT descripcion FROM productos WHERE id_producto = '{$post['id_producto'][$x]}'";
        $sqlQ_P = mysql_query($sql_P) or die (mysql_error());
        $sqlR_P = mysql_fetch_assoc($sqlQ_P);
        $producto = $sqlR_P['descripcion'];
        
        echo "Ha ocurrido un error relacionado con las existencias de los productos.<br>
              Esto puede ocurrir debido a que las existencias cambiaron durante la creación de este documento.<br>
              Favor de realizarla nuevamente.<br>
              Si el error continua, contacte al soporte técnico e indíquele los productos que está intentANDo registrar<hr>
              </hr>
              <b>Error detectado en
              ID del almacén:</b> {$almacen}<br/>
              <b>ID del producto:</b> {$post['id_producto'][$x]}<br/>
              <b>Descripción del producto:</b> {$producto}<br/>
              <b>Lote:</b> {$post['lote'][$x]}<br/>
              <b>Cantidad solicitada:</b> {$post['cantidad'][$x]}<br/>
              <b>Cantidad disponible:</b> {$exR['cantidad']}<br/>
              <a href='{$_SERVER['PHP_SELF']}'>Crear nuevamente el documento.</a>";
        exit();
      }
      else{
        unset($nueva_existencia);
      }
    }
  }
  unset($x);
}
function query($s,$debug = false)
{
	if($debug)
	{
		echo "<hr /><p style='text-align:left'>Ejecutando:<br /><font color='#3F6A3F'><b>".($s)."</b></font></p>";
	}
	$q = mysql_query($s) or die ("<hr /><p style='text-align:left'><font color='#FF5500'><b>".($s)."</b></font></p>".mysql_error());
	return $q;
}
function selected($actual,$valor){
	if($actual == $valor){
		$selected = "selected=\"selected\"";
		return $selected;
	}
}
function selectedMultiple($actual,$valor_array){
	if(@in_array($actual,$valor_array)){
		$selected = "selected=\"selected\"";
		return $selected;
	}
}
function checked($actual,$valor){
	if($actual == $valor){
		$checked = "checked=\"checked\"";
		return $checked;
	}
}
function formatear(){ //Quitar |, ~, *, "", '', []
	foreach($_POST as $k => $v){
		$_POST[$k] = str_replace("|","",$_POST[$k]);
		$_POST[$k] = str_replace("~","",$_POST[$k]);
		$_POST[$k] = str_replace("*","",$_POST[$k]);
		$_POST[$k] = str_replace("\"","",$_POST[$k]);
		$_POST[$k] = str_replace("\'","",$_POST[$k]);
		$_POST[$k] = str_replace("[","",$_POST[$k]);
		$_POST[$k] = str_replace("]","",$_POST[$k]);
	}
}
function fecha_sql($fecha){
	$fecha = explode("-",$fecha);
	$fecha = $fecha[2]."-".$fecha[1]."-".$fecha[0];
	return $fecha;
}
function relocation($url){
	echo "
	<script language=\"JavaScript\" type=\"text/javascript\">
	window.location=\"{$url}\";
	</script>
	";
}
function relocation2($url){
	echo "
	<script language=\"JavaScript\" type=\"text/javascript\">
	window.location=\"{$url}\";
	</script>
	";
	exit();
}



function titleset($s){
	echo "
	<script language=\"JavaScript\" type=\"text/javascript\">
	document.getElementById(\"header_title\").innerHTML=\"{$s}\";
	</script>
	";
}

function alerttogo($method,$var,$alert){
	if($method == "get" && isset($_GET[$var])){
		$index = $_GET[$var];
	}
	if($method == "post" && isset($_POST[$var])){
		$index = $_POST[$var];
	}
	
	foreach($_GET as $k => $v){
		if($k != $var){
			$params .= "&{$k}={$v}";
		}
	}
	$params = substr($params,1,strlen($params));
	$pagina = "comuni-k.php?".$params;
	
	if(($method == "get" && isset($_GET[$var])) || ($method == "post" && isset($_POST[$var]))){
		echo "
		<html>
		<body>
		<script language=\"JavaScript\" type=\"text/javascript\">
		alert(\"{$alert}\");
		window.location = '{$pagina}';
		</script>
		</body>
		</html>
		";
		if($method == "get"){
			unset($_GET[$var]);
		}
		if($method == "post"){
			unset($_POST[$var]);
		}
		exit();
	}
}

function pop($o,$page){
	if(count($o) > 0){
		if(!isset($_SESSION[$page."_menu"])){
			$_SESSION[$page."_menu"] = 0;
		}
		if($_SESSION[$page."_menu"] == 0){
			$display_hide = "";
			$display_show = "none";
		} else {
			$display_hide = "none";
			$display_show = "";
		}
?>
<table id="pop" border="0" cellpadding="0" cellspacing="0" style="margin-top:-6px; position:absolute;">
	<tr>
		<td background="images/pop_01.png" width="11"></td>
		<td valign="top" bgcolor="#FFFFFF" style="padding-top:5px">
      <div id="pop_0" style="display:<?=$display_hide?>">
      	<p>
        <?php
        	for($i=0; count($o)>$i; $i++){
						echo $o[$i];
						if(count($o)>$i){
							echo "</p><p>";
						}
          }
        ?>
        </p>
      <div style="text-align:center; margin:6px auto 0px auto; width:16px" onclick="pop_switch('<?=$page?>');" class="manita"><img src="imagenes/arrow_u.gif" alt="Ocultar men&uacute; de Opciones" /></div>
      </div>
      <div id="pop_1" style="display:<?=$display_show?>">
      	<div style="margin:-3px" onclick="pop_switch('<?=$page?>');" class="manita"><img src="imagenes/arrow_d.gif" alt="Mostrar menpu de Opciones" /></div>
      </div>
    </td>
		<td background="images/pop_03.png" width="11"></td>
	</tr>
	<tr>
		<td>
			<img src="images/pop_04.png" width="11" height="14" alt=""></td>
		<td background="images/pop_05.png" height="14"></td>
		<td>
			<img src="images/pop_06.png" width="11" height="14" alt=""></td>
	</tr>
</table>
<?php
	}
}

if(isset($_POST['edit_now_data']))
{
	@session_start();
	$r = array( 'error' => false );
	if(strlen($_SESSION[id_usuario])>0)
	{
		include("basedatos.php");
		$s = "UPDATE {$_POST[tabla]} SET {$_POST[campo]} = '{$_POST[nuevo]}' WHERE {$_POST[id_tabla]} = '{$_POST[este_id]}'";
		mysql_query($s) or die ($s."\n".mysql_error());
		
		if($_POST['tabla'] == 'clientes') mysql_query("CALL clientes2Memory()");
		if($_POST['tabla'] == 'productos') mysql_query("CALL productos2Memory()");
		
		$r['msg'] = $s;
	}
	else
	{
		$r = array(
			'error' => true,
			'msg' => "No cuenta con los privilegios suficientes."
		);
		exit();
	}

	echo json_encode($r);
	exit();
}

function editNowJava(){
	if(!isset($GLOBALS[editNow])){//Evitar llamar a la función más de una vez
		$GLOBALS[editNow] = 1;
?>
<script language="javascript" type="text/javascript">
function keyPressHandler(e,id) {
	var key_control  = (window.event) ? event.keyCode : e.keyCode;						
	if(key_control==27) document.getElementById("cb_"+id).onclick();
	if(key_control==13){
		try{ document.getElementById("text_"+id).onblur(); }
		catch(e){}
		document.getElementById("ab_"+id).onclick();
	}
}

function editNow(id){
	var first = document.getElementById(id);
	var second = document.getElementById("input_"+id);
	
	first.style.display="none";
	second.style.display="";
	
	document.getElementById("text_"+id).select();
}
function editNowCancel(id){
	var first = document.getElementById(id);
	var second = document.getElementById("input_"+id);
	
	first.style.display="";
	second.style.display="none";
	
	document.getElementById("text_"+id).value = document.getElementById(id).innerHTML;
}
function editNowDo(tabla,id_tabla,este_id,campo,obj_id,minimo){
	var nuevo_valor = addslashes(trim(document.getElementById("text_"+obj_id).value));
	if(nuevo_valor.length >= minimo)
	{
		$.ajax({
			url: "funciones/funciones.php",
			type: 'post',
			data: {
				edit_now_data: true,
				tabla: tabla,
				id_tabla: id_tabla,
				campo: campo,
				este_id: este_id,
				nuevo: nuevo_valor
			},
			dataType: 'json',
			success: function(r){
				if(!r.error){
					document.getElementById(obj_id).innerHTML = trim(document.getElementById("text_"+obj_id).value);
					editNowCancel(obj_id);
				} else {
					editNowCancel(obj_id);
				}
			},
			error: function(){
				alert("Ha ocurrido un error y este registro no pudo ser procesado.\nIntente de nuevo más tarde.");
			}
		});
		
		//var sql = sql.replace("[nuevo_valor]",addslashes(trim(nuevo_valor)));
		// var url = "funciones/funciones.php?edit_now_data=0&tabla="+tabla+"&id_tabla="+id_tabla+"&campo="+campo+"&este_id="+este_id+"&nuevo="+nuevo_valor;
		// var r = procesar(url);
		
	} else {
		alert("El dato ingresado es demasiado pequeño.\nIntente nuevamente.");
	}
}
</script>
<style>
	.boton{margin-bottom:-4px; cursor:pointer; float:left;}
	.editnow_input{font-size:11px;}
	.editnow_span{cursor:pointer;}
</style>
<?php
	}
}

function editNow($tabla,$id_tabla,$campo,$este_id,$grupo,$original,$tipo,$chars,$width){
	$id = $grupo."_".$este_id;
?>
<span class="editnow_span" id="<?=$id?>" onclick="editNow('<?=$id?>');"><?=htmlentities($original)?></span><span id="input_<?=$id?>" style="display:none;"><input class="editnow_input" style="width:<?=$width?>;" id="text_<?=$id?>" value="<?=htmlentities($original)?>" onkeypress="keyPressHandler(event,'<?=$id?>')" <?php if($tipo >= 0){ ?>onblur="numero(this,<?=$tipo?>)"<?php } ?> /><br /><img id="cb_<?=$id?>" src="imagenes/cancel_editnow.png" class="boton" onClick="editNowCancel('<?=$id?>');"><img  id="ab_<?=$id?>" src="imagenes/accept_editnow.png" class="boton" onClick="editNowDo('<?=$tabla?>','<?=$id_tabla?>','<?=$este_id?>','<?=$campo?>','<?=$id?>','<?=$chars?>');"></span>
<?php
}

function mandar_mail($remitente,$desti,$titulo,$ruta,$ruta2,$mensaje)
{
    require("Classes/mailer/class.phpmailer.php");
		$s = "SELECT * FROM vars";
		$q = query($s);
		$r = mysql_fetch_assoc($q);	
		$error = "";
		if(strlen($r[smtp_usuario]) > 0 && strlen($r[smtp_pass]) > 0 && strlen($r[smtp_puerto]) > 0 && strlen($r[smtp_servidor]) > 0 && count($desti) > 0)
		{
			$desti = array_unique($desti);
			$mail = new PHPMailer (true);
			try {
				$mail -> IsSMTP(); // send via SMTP
				$mail -> Sender = $remitente;
				$mail -> FromName = $r[nombre];
				$mail -> Subject = $titulo;
				$mail -> CharSet	= "iso-8859-1";
				$mail -> Encoding	= "quoted-printable";
				$mail -> AddReplyTo($remitente);
				$mail -> SMTPSecurity = "tls";
				$mail -> Body = $mensaje;
				$mail -> IsHTML (true);
				$mail -> Host = "ssl://".$r[smtp_servidor];
				$mail -> Port = $r[smtp_puerto];
				$mail -> SMTPAuth = ($r[smtp_autenticar] == "0") ? false : true;
				$mail -> Username = $r[smtp_usuario];
				$mail -> Password = $r[smtp_pass];
				$mail -> AddAddress($desti[0]);
				foreach($ruta as $r) if(strlen($r) > 0) $mail -> AddAttachment($r,$r,"base64",$r);
				foreach($ruta2 as $r2) if(strlen($r2) > 0) $mail -> AddAttachment($r2,$r2,"base64",$r2);
					
				
				for($i = 1; $i < count($desti); $i++)
				{
					$mail -> AddBCC($desti[$i]);
				}
				$mail -> Send();
			}
			catch (Exception $e)
			{
				$error = $e-> getMessage();
			}			
			if(strlen($error) > 0)
			{
				return $error;
			}
			else
			{
				return 1;
			}
		}
		else
		{
		  echo "<b>La <a href='comuni-k.php?section=vars' target='_top'>configuraci&oacute;n de env&iacute;o de correo electr&oacute;nico</a> no ha sido configurado correctamente.</b>";
			return false;
		}
}
function nu($n)
{
	return number_format($n,2,".","");
}
function x($s)
{
	$s = str_replace("&","&amp;",$s);
	$s = str_replace("<","&lt;",$s);
	$s = str_replace(">","&gt;",$s);
	$s = str_replace("\"","&quot;",$s);
	$s = str_replace("'","&#39;",$s);
	return $s;
}

function reporte_mensual_data($f1,$f2)
{	
		$con= "SELECT rfc,ruta,serie,dolar FROM vars LIMIT 1";
		$res= mysql_query($con);
		$fila= mysql_fetch_assoc($res);
	  $d= array();
  	$s="SELECT 
		datos_cliente,
		folio,
		anoap,
   	noap,
		DATE_FORMAT(fecha_captura, '%d/%m/%Y %H:%i:%s') fecha_sat,		
		moneda,	
		importe,
		status		
		FROM facturas WHERE  fecha_factura BETWEEN '{$f1}' AND '{$f2}' AND serie='{$fila[serie]}' ORDER BY 0+folio+0 ASC";
    $q= mysql_query($s);
		while($r= mysql_fetch_assoc($q))
		{	
			$d[chris][] = $r;
		}		
			 foreach($d[chris] as $v)
			 {				   
					$pro_s="SELECT  
					f.cantidad,
					f.canti_,
					f.lote,
					f.precio,
					f.descuento,
					f.iva,
					f.unidad_factura,
					f.importe,
					p.codigo_barras,
					IF(
						f.id_producto = 0,
						especial,
						p.descripcion
					) descripcion,
					IFNULL(f.complemento,NULL) 'complemento'
					FROM
					facturas_productos f
					LEFT JOIN productos p ON f.id_producto = p.id_producto
					WHERE folio_factura = '{$v[folio]}' AND serie='{$fila[serie]}'";
	    $pro_q = query($pro_s);	
			$iva=0;		
			while($pro = mysql_fetch_assoc($pro_q))
			{
			  $iva += $pro[cantidad]*($pro[precio]*($pro[iva]/100));
			}		
			$d[chris1][] =$iva;
				
		}						 
			 foreach($d[chris] as $k => $v)
			 {				   
			    $rfcr = explode("|",$v[datos_cliente]);
					if($v[moneda] == "U.S.D."){ $impor =$v[importe]*$fila[dolar]; }else{$impor=$v[importe];}
					if($v[status] == 0){$st = 1;}
					if($v[status] == 1){$st = 0;}
			    $linea .="|".$rfcr[0]."||".$v[folio]."|".$v[anoap].$v[noap]."|".$v[fecha_sat]."|".nu($impor)."|".nu($d[chris1][$k])."|".$st."|I||||\r\n";		
			 }			
			$fn= explode("-",$f1);		
			$nombrearchivo= "1".$fila[rfc].$fn[1].$fn[0];					
    	$conte_repor=$linea;
		//crear un archivo txt para guardar el reporte mensual
		$rut= $fila[ruta]."/".$nombrearchivo.".txt";
		if(file_exists($rut)){unlink($rut);}		
		if ($rp = fopen("{$rut}","w+")) 
		{ 
				fwrite($rp,$conte_repor,strlen($conte_repor)); 
				fclose($rp); 
		}  	
		return $d;
}

function array_resume($a)
{
	foreach($a as $k => $v)
	{
		if(!is_array($v))
		{
			$a[$k] = trim(x($v));
		}
		else
		{
			$a[$k] = array_resume($v);
		}
	}
	return $a;
}

function getFile($nombre_fichero)
{
	$gestor = fopen($nombre_fichero, "r");
	$contenido = fread($gestor, filesize($nombre_fichero));
	fclose($gestor);
	return $contenido;
}

function getCadenaOriginal($path)
{
	exec("xsltproc cadenaoriginal_2_2.xslt {$path} > co2.txt");
	exec("Openssl dgst -SHA1 -sign certificado.key.pem co2.txt | Openssl enc -base64 -A > sello2.txt");
	
	$co = getFile('sello2.txt');
	return $co;
}

function sellarXML($path)
{
	$cadenaOriginal = getCadenaOriginal($path);
	$xmlOriginal = getFile($path);
	
	$xmlFinalTxt = str_replace('__SELLO__', $cadenaOriginal, $xmlOriginal);
	
	$xmlFinal = fopen($path, 'w');
	fwrite($xmlFinal, $xmlFinalTxt);
	fclose($xmlFinal);
}

function factura_data($f,$ser)
{
	$data = array();
	
	//Rich 28/6/12
	//La cadena original será un arreglo que será imploded posteriormente
	$co = array("||2.2", $f );
	$var_s = "SELECT nombre, calle, noe, noi, colonia, localidad, municipio, estado, pais_nombre pais, cp, rfc, logotipo, ncsd, ruta, cedula, dolar FROM vars INNER JOIN paises ON paises.id = vars.pais";
	$data[empresa] = mysql_fetch_assoc(query($var_s));
	if($ser == "")
	{
		$serie= " AND serie IS NULL";
	}
	else
	{
		$serie= " AND serie='{$ser}'";
	}
	 
	$fac_s = "SELECT
						DATE_FORMAT(fecha_captura, '%Y-%m-%d') fecha,
						DATE_FORMAT(fecha_captura, '%H:%i:%s') hora,
						DATE_FORMAT(fecha_captura, '%Y-%m-%dT%H:%i:%s') fecha_sat,
						datos_cliente,
						anoap,
						noap,
						nocertificado,
						leyenda,
						moneda,
						importe,
						NumCtaPago,
						metodoDePago
						FROM
						facturas
						WHERE folio = '{$f}' {$serie}";
	$cliente = mysql_fetch_assoc(query($fac_s));
	$cliente_data = explode("|",$cliente[datos_cliente]);
	
	$co[] = $cliente[fecha_sat];
	$co[] = $cliente[noap];
	$co[] = $cliente[anoap];
	$co[] = "ingreso";
	$co[] = "PAGO EN UNA SOLA EXHIBICION";
	
	$data[factura][fecha] = $cliente[fecha];
	$data[factura][hora] = $cliente[hora];
	$data[factura][fecha_sat] = $cliente[fecha_sat];
	$data[factura][importe] = $cliente[importe];
  $data[factura][leyenda] = $cliente[leyenda];
  $data[factura][moneda] =($cliente[moneda] == 'M.N.') ? 'MXN' : 'USD';
  $data[empresa][dolar] = ($cliente[moneda] == 'M.N.') ? '1' : $data[empresa][dolar];
	$data[empresa][ruta] = $data[empresa][ruta];
	$data[factura][nocertificado] =  $cliente[nocertificado];
	$data[factura][anoaprobacion] =  $cliente[anoap];
	$data[factura][noaprobacion] =  $cliente[noap];	
	
	$data[cliente][rfc] = $cliente_data[0];
	$data[cliente][nombre] = $cliente_data[1];
	$data[cliente][calle] = $cliente_data[2];
	$data[cliente][noe] = $cliente_data[3];
	$data[cliente][noi] = $cliente_data[4];
	$data[cliente][colonia] = $cliente_data[5];
	$data[cliente][localidad] = $cliente_data[6];
	$data[cliente][municipio] = $cliente_data[7];
	$data[cliente][estado] = $cliente_data[8];
	$data[cliente][pais] = $cliente_data[9];
	$data[cliente][cp] = $cliente_data[10];
	$data[cliente][metodoDePago] = $cliente[metodoDePago];
	$data[cliente][NumCtaPago] = $cliente[NumCtaPago];

	$pro_s = "SELECT
						IF(canti_ = 0, cantidad, canti_) cantidad,
						IF(canti_ = 0, precio, (canti_ * precio) / canti_) precio,
						
						f.lote,
						f.descuento,
						f.iva,
						IF(TRIM(f.unidad_factura) = '', 'NO DEFINIDO', f.unidad_factura) unidad,
						f.importe,
						p.codigo_barras,
						
						TRIM(CONCAT(IF(f.id_producto > 0, p.descripcion, especial), '\n', IFNULL(IF(f.complemento = '0', '', f.complemento),''))) descripcion
						
						FROM
						facturas_productos f
						LEFT JOIN productos p ON f.id_producto = p.id_producto
						WHERE folio_factura = '{$f}' {$serie}
						GROUP BY f.id_facturaproducto";
	$pro_q = query($pro_s);
	$productos = array();
	while($pro = mysql_fetch_assoc($pro_q))
	{
		
		$data[factura][subtotal] += $pro[cantidad]*$pro[precio];
		$data[factura][iva] += $pro[cantidad]*($pro[precio]*($pro[iva]/100));
		$data[factura][descuento] += $pro[cantidad]*($pro[precio]*($pro[descuento]/100));
	
		$descripcion = $pro[descripcion];
		$data[productos][] = array(
													'cantidad' => $pro[cantidad],
													'unidad' => $pro[unidad],
													'descripcion' => $pro[descripcion],
													'precio' => nu($pro[precio]),
													'importe' => nu($pro[cantidad]*$pro[precio])
												 );

		$productos[] = $pro[cantidad];
		$productos[] = $pro[unidad];
		$productos[] = $pro[descripcion];
		$productos[] = nu($pro[precio]);
		$productos[] = nu($pro[cantidad]*$pro[precio]);
	}

	$data[factura][tipodecomprobante] = "ingreso";

	//Obtener las letras del importe. Cuando llama desde Advans no encuentra la ruta. Evitamos el error...
	$numLet = "funciones/CNumeroaLetra.php";
	if(file_exists($numLet))
	{
		include($numLet);
		$numalet = new CNumeroaletra;
		$numalet -> setNumero($data[factura][importe]);
		$numalet -> setMayusculas(0);
		$numalet -> setGenero(0);
		
		if($data[factura][moneda] == "MXN")
		{
			$numalet -> setMoneda("PESOS");
			$numalet -> setPrefijo("");
			$numalet -> setSufijo("M.N.");
		}
		else
		{
			$numalet -> setMoneda("DOLARES");
			$numalet -> setPrefijo("");
			$numalet -> setSufijo("U.S.D.");
		}

		$data[factura][letras] = "SON: ".strtoupper($numalet->letra());
	}
	$data[cadenaoriginal] = '__CADENAORIGINAL__';
	$data[sello] = '__SELLO__';
	
	$data = array_resume($data);
	// p($data);
	return $data;
}

function filter_display($page){
	if(!isset($_SESSION[$page."_filter"])){
		$_SESSION[$page."_filter"] = 0;
	}
	if($_SESSION[$page."_filter"] == 0){ //El filtro puede verse
		$_POST[class_filtro] = "filtro_principal";
	} else {
		$_POST[class_filtro] = "filtro_principal_escondido";
	}
?>
<table id="filtro_toogle" border="0" cellpadding="0" cellspacing="0" style="margin-top:-6px; position:absolute; right:0px">
	<tr>
		<td background="images/pop_01.png" width="11"></td>
		<td valign="top" bgcolor="#FFFFFF" style="padding-top:5px">
        <?php
        	for($i=0; count($o)>$i; $i++){
						echo $o[$i];
						if(count($o)>$i){
							echo "</p><p>";
						}
          }
        ?>
        <div style="text-align:center; margin:0px auto 0px auto;" onclick="filter_display('<?=$page?>');">
          <img src="imagenes/filter.png" alt="Mostrar / Ocultar filtros" style="margin:0px -5px -2px 0px; cursor:pointer" />
          <input type="checkbox" id="filter_checkbox" <?=checked($_SESSION[$page."_filter"],0)?> onclick="if(this.checked){this.checked = false} else {this.checked=true}" class="manita" />
      </div>
    <?php if(isset($_POST[printable])){
		$s = "SELECT * FROM vars";
		$q = mysql_query($s);
		$r = mysql_fetch_assoc($q);
		$dir = explode("\n",$r[direccion]);
		for($i=0;count($dir)>$i;$i++){
				$dir_str .= "<br />".$dir[$i];
		}
		$dir_str = preg_replace("[\n|\r|\n\r]", '', substr($dir_str,6,strlen($dir_str)));  ;
		?>
		<script language="javascript" type="text/javascript">
      function Clickheretoprint(){
				var disp_setting = "toolbar=no,location=no,directories=no,menubar=no,"; 
            disp_setting += "scrollbars=yes,width=700, height=600, left=100, top=25"; 
				if(document.getElementById("_lista")){
					win = window.open("printer.php?titulo="+document.getElementById("header_title").innerHTML, 'printer', disp_setting);
					//win.clear();
				}
				else
				{
					alert("No hay nada que imprimir.");
				}
      }
    </script>    
    <div style="margin-top:8px; text-align:center;">
    <img src="imagenes/print-icon.gif" alt="Imprimir el contenido de esta p&aacute;gina"
    style="cursor:pointer"
    onclick="Clickheretoprint();"
    onmouseover="this.src = 'imagenes/print-icon_over.gif'"
    onmouseout="this.src = 'imagenes/print-icon.gif'" />
    </div>
    <?php } ?>
    </td>
		<td background="images/pop_03.png" width="11"></td>
	</tr>
	<tr>
		<td>
			<img src="images/pop_04.png" width="11" height="14" alt=""></td>
		<td background="images/pop_05.png" height="14"></td>
		<td>
			<img src="images/pop_06.png" width="11" height="14" alt=""></td>
	</tr>
</table>
<?php
}

function dbConnect()
{
	global $db;
	return $db;
}

function isCFDI($serie, $folio)
{
	if(substr_count($folio, 'NOTA') > 0)
	{
		return false;
	}
	else
	{
		global $db;
		return ((int)$db->fetchCell("SELECT IFNULL((SELECT id FROM cfdi WHERE serie = '{$serie}' AND folio = '{$folio}'), 0)") > 0);
	}
}

function isCFDIf_($serie, $folio)
{
	if(substr_count($folio, 'NOTA') > 0)
	{
		return false;
	}
	else
	{
		global $db;
		$s = "SELECT IF( (SELECT fecha_factura FROM facturas WHERE folio = '{$folio}' AND serie = '{$serie}' LIMIT 1) >= (SELECT fecha_inicio_cfdi FROM vars), 1,0 ) cfdi";
		$valor = $db->fetchCell($s);
		return ($valor == 1);
	}
}

if(isset($_SESSION[id_usuario]) and !isset($_SESSION['almacen_restaurado'])){
  //Restaurar Almacenes
  $s = "SELECT almacenes FROM usuarios WHERE id_usuario = {$_SESSION[id_usuario]}";
  $q = mysql_query($s) or die (mysql_error());
  $r = mysql_fetch_assoc($q);
  $r = $r[almacenes];

  $almacenes_todos[] = array();
  $x=0;
  $query_a = mysql_query("SELECT * FROM almacenes WHERE status = 0 ORDER BY descripcion");
  while($a = mysql_fetch_array($query_a)){
    $almacenes_todos[$x][id] = $a[id_almacen];
    $almacenes_todos[$x][descripcion] = $a[descripcion];
    $x++;
  }
  if($r == "*"){ //Para Almacenes Definidos
    $almacenes = $almacenes_todos;
  } else {
    $a = explode("-",$r);
    foreach($a as $k => $v){
      foreach($almacenes_todos as $kk => $vv){
        if($almacenes_todos[$kk][id] == $v){
          $almacenes[$k][id] = $vv[id];
          $almacenes[$k][descripcion] = $vv[descripcion];
        }
      }
    }
  }
	
	$_SESSION['almacen_restaurado'] = true;
}

function utf8_deconverter($array)
{
    /*array_walk_recursive($array, function(&$item, $key){
      $item = utf8_decode($item);
    });*/

	foreach($array as $k => $v)
{
	$array[$k] = utf8_decode($v);
}

	//Maldita versión 5.2!!
	//array_walk($array, create_function(addslashes("&$item, $key;"), '$item = utf8_decode($item);'));
 
    return $array;
}

/*$s = "SELECT dolar FROM vars";
$q = mysql_query($s) or die (mysql_error());
$r = mysql_fetch_assoc($q);
define(DOLAR,$r[dolar]);*/

function utf8ize($d) {
    if (is_array($d)) 
        foreach ($d as $k => $v) 
            $d[$k] = utf8ize($v);

     else if(is_object($d))
        foreach ($d as $k => $v) 
            $d->$k = utf8ize($v);

     else 
        return utf8_encode($d);

    return $d;
}

function json_encode_utf8($array)
{
	return json_encode(utf8ize($array));
}

//Regresar colección de unidades
function unidades()
{
	return array(
		'CMS',
		'GRS',
		'JGO',
		'KG',
		'LT',
		'MT',
		'PZA',
		'Pulgada',
		'SERV'
	);
}

/* 3 Oct 2016 - Obtener valor de USD */
function getUSD()
{
	global $db;
	return $db->fetchCell("SELECT dolar FROM vars");
}
?>