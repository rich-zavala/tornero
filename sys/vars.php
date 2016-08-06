<?php
$arr = array(
					"clientes" => array(
						"titulos" => array("Nombres", "Tel&eacute;fonos","Celular","Calle","No. Exterior","No. Interior","Colonia","Localidad","Municipio", "Estado", "C&oacute;digo Postal", "RFC", "Cr&eacute;dito", "Grupo", "D&iacute;as Cr&eacute;dito","E-mail")
						),
					"productos" => array(
						"titulos" => array("Descripci&oacute;n", "C&oacute;digo de Barras", "Precio P&uacute;blico", "IVA")
						),
					"proveedores" => array(
						"titulos" => array("Nombres", "Tel&eacute;fonos", "Direcci&oacute;n", "Estado / Regi&oacute;n", "Ciudad / Poblaci&oacute;n", "C&oacute;digo Postal", "RFC", "Grupo")
					)
			 );

if(isset($_POST[registrar])){
	$o = array("nombre","calle","noe","colonia","municipio","estado","cp","rfc");
	foreach($o as $k => $v){
		if(strlen($_POST[$v]) == 0 ){
			$faltantes[] = $v;
		}
	}
	if(count($faltantes)==0){
		$nombre_archivo = $_FILES['logo']['name'];
    $tipo_archivo = $_FILES['logo']['type'];
    $tamano_archivo = $_FILES['logo']['size'];
		$ext = substr($nombre_archivo, strrpos($nombre_archivo, '.') + 1);
		if (($ext == "jpg") && ($tipo_archivo == "image/jpeg"))
		{
			move_uploaded_file($_FILES['logo']['tmp_name'], "logo/".$nombre_archivo);
			$r = mysql_fetch_assoc(query("SELECT logotipo FROM vars"));
			unlink("logo/".$r[logotipo]);
						
			$img_update = "logotipo='{$nombre_archivo}',";
		}		
		
		//esto era por que no sabia como era el sello digital
		$nombre_archivo2 = $_FILES['cedula']['name'];
    $tipo_archivo2 = $_FILES['cedula']['type'];
    $tamano_archivo2 = $_FILES['cedula']['size'];
		$ext2 = substr($nombre_archivo2, strrpos($nombre_archivo2, '.') + 1);
		if (($ext2 == "jpg") && ($tipo_archivo2 == "image/jpeg"))
		{
			move_uploaded_file($_FILES['cedula']['tmp_name'], "cedula/".$nombre_archivo2);
			$r2 = mysql_fetch_assoc(query("SELECT cedula FROM vars"));
			unlink("cedula/".$r2[cedula]);						
			$cedula_update = "cedula='{$nombre_archivo2}',";
		}	

		$pass = (isset($_POST[smtp_pass]) && strlen($_POST[smtp_pass]) > 0)?"smtp_pass='{$_POST[smtp_pass]}',":"";
		$set = "UPDATE vars SET
					nombre='{$_POST[nombre]}',
					calle='{$_POST[calle]}',
					noe='{$_POST[noe]}',
					noi='{$_POST[noi]}',
					colonia='{$_POST[colonia]}',
					localidad='{$_POST[localidad]}',
					municipio='{$_POST[municipio]}',
					estado='{$_POST[estado]}',
					pais='{$_POST[pais]}',
					cp='{$_POST[cp]}',
					rfc='{$_POST[rfc]}',
					smtp_usuario='{$_POST[smtp_usuario]}',
					smtp_remitente='{$_POST[smtp_remitente]}',
					{$pass}
					smtp_puerto='".intval($_POST[smtp_puerto])."',
					smtp_servidor='{$_POST[smtp_servidor]}',
					smtp_autenticar='".intval($_POST[smtp_autenticacion])."',
					mail_activo = 0,
					ncsd='{$_POST[ncsd]}',
					anoa='{$_POST[anoa]}',
					noa='{$_POST[noa]}',						
					{$img_update}
					ruta='{$_POST[ruta]}',
					serie='{$_POST[serie]}',
					folioi='{$_POST[folioi]}',
					foliof='{$_POST[foliof]}',
					dolar ={$_POST[dolar]},
					{$cedula_update}
					pmv = '{$_POST[pmv]}'";				
		     query($set);
		
		if($_POST[mail_activo] == 1)
		{
    	require("Classes/mailer/class.phpmailer.php");
			  ini_set('display_errors', true); 
				if($pass == "")
				{
				  	$_POST[smtp_pass] = $_POST[ts];
				}
		    error_reporting(E_ALL);
				$mail = new PHPMailer ();
				$mail -> Sender = $_POST[smtp_remitente];
				$mail -> FromName = "Tornero prueba de configuración de smtp.";
				$mail -> AddAddress("casadeltornero@gmail.com");
				$mail -> Subject = "Tornero prueba de configuración de smtp.";
				$mail -> Body = "Es un mensaje de prueba.";
				$mail -> IsHTML (true);
				$mail -> IsSMTP();
				$mail -> Host = 'ssl://'.$_POST[smtp_servidor];
				$mail -> Port = intval($_POST[smtp_puerto]);				
				$mail -> SMTPAuth = ($_POST[smtp_autenticacion] == "0") ? false : true;
				$mail -> Username = $_POST[smtp_usuario];
				$mail -> Password = $_POST[smtp_pass];			
			//	$mail -> Send();
				if ($mail->Send())
				{
					$set = "UPDATE vars SET	mail_activo = 1";				
				 	query($set);					
				}
				else
				{
					$config= 1;	
				}
		}
    //Resetear la tabla de PMV
    $pmv = 1+($_POST[pmv]/100);
    $s = "SELECT * FROM precio_minimo";
    $q = query($s);
    while($r = mysql_fetch_assoc($q)){
      $query_minimo = "SELECT
      (SUM(compras_detalle.importe)/SUM(cantidad))*{$pmv} AS 'minimo'
      FROM compras_detalle
      INNER JOIN compras ON compras.id = compras_detalle.id_compra
      WHERE id_producto = '{$r[id_producto]}'
      AND status = '0'";
      $minimo = query($query_minimo);
      $row_minimo = mysql_fetch_assoc($minimo);
      $min = floatval($row_minimo[minimo]);
      $strSQL = "UPDATE precio_minimo SET pmv = '{$min}' WHERE id_producto = '{$r[id_producto]}'";
      query($strSQL);    
    }
	 if(isset($config)){relocation2("?section=vars&ok&config");}else{relocation2("?section=vars&ok");}
		
	}
}
titleset("P&aacute;gina de configuraci&oacute;n de Comuni-K &reg;");
//Parámetros para el ícono "REGRESAR" del menú superior
foreach($_GET as $k => $v){
	$params .= "&{$k}={$v}";
}
$params = substr($params,1,strlen($params));
$_SESSION[start] = "comuni-k.php?".$params;
//Fin de parámetros

$s = "SELECT * FROM vars";
$q = query($s);
?>
<script language="javascript" type="text/javascript">
function mostrar(){
	var op = $("#tablas").val();
	$("#tabla").html(op);
	var value = $("#tablas").val();
	$("#div_clientes,#div_productos,#div_proveedores").hide();
	$("#div_"+value).toggle();
	$("#vinculo_descarga").attr("href","xls/"+value+".xls");
}

function cambiar_pass()
{	
	$("#smtp_pass,#pass").attr("disabled",false).toggle();	
}

$(document).ready(function(){
	<?php if(isset($_GET[config])){?>
	cambiar_pass();
	<?php }?>
	$(".slide_toggle").hide();
	$(".toggler").click(function(){
		var id = $(this).attr("alt");
		
		if($("#"+id).is(':visible'))
		{
			$("#"+id).fadeOut();
			$(this).attr("src","imagenes/arrow_d.gif");
		}
		else
		{
			$("#"+id).fadeIn();
			$(this).attr("src","imagenes/arrow_u.gif");
		}
	});
	<?php
	if(count($faltantes)>0)
	{
		echo "var faltantes = Array();";
		foreach($faltantes as $k => $f)
		{
			echo "faltantes[{$k}] = '{$f}';\n";
		}
	?>
	$("#cliente_data").trigger("click");
	for(var i in faltantes)
	{
		$("[name='"+faltantes[i]+"']").addClass("error_field");
	}
	<?php
	}
	
	if(isset($_GET[error]))
	{
	?>
	$("#excel_data").trigger("click");
	<?php } ?>
});
</script>
<form action="comuni-k.php?section=excel_update" method="post" enctype="multipart/form-data">
  <input type="hidden" name="MAX_FILE_SIZE" value="2000000"> 
  <table width="420" border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla">
    <tr>
      <th colspan="2" style="text-align:center;">
      	<img src="imagenes/excel.png" style="margin-bottom:-2px;" />&nbsp;&nbsp; Insertar datos desde Archivo de Excel
      	<img src="imagenes/arrow_d.gif" style="float:right; margin-left:-16px" class="manita toggler" alt="excel" id="excel_data" /></th>
    </tr>
    <?php if($_GET[error] == 1){?>
    <caption class="aviso">
    	Archivo de excel no identificado
    </caption>
    <?php } elseif($_GET[error] == 2) { ?>
    <caption class="aviso">
    	El tama&ntilde;o del archivo es muy grande, m&aacute;ximo 2 Megas
    </caption>
    <?php }?>
    <tbody class="slide_toggle" id="excel">
    <tr>
      <th><span style="text-align:right;"><img src="imagenes/vars.png" style="margin-bottom:-4px; " />&nbsp;Elegir archivo:</span></th>
      <td><input name="userfile" type="file"></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px;" />&nbsp;Elegir tabla:</th>
      <td><select name="tablas" id="tablas" onchange="mostrar()">
          <option value="clientes">Clientes</option>
          <option value="productos">Productos</option>
          <option value="proveedores">Proveedores</option>
        </select></td>
    </tr>
    <tr>
      <th colspan="2" style="text-align:center;">Orden de la columnas para la tabla de <span id="tabla">Clientes</span> </th>
    </tr>
    <tr>
      <td colspan="2"><table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="216" style="border-bottom:none;"><?php
			$i = 0;
      foreach($arr as $tabla => $datos){
				if($i > 0){
					$dis = "style=\"display:none\"";
				}
				$i++;
				echo "<div border='none'; id='div_{$tabla}' {$dis}><ol style=\"margin:0px; padding:0px; margin-left:30px;\">\n";
				foreach($datos[titulos] as $titulo){
					echo "<li>".$titulo."</li>\n";
				}
				echo "</ol></div>\n";
	  	}
		  ?></td>
            <td width="194" style="text-align:center; vertical-align:middle; border-bottom:none;" ><center>
                <a href="xls/clientes.xls" id="vinculo_descarga" > <img src="imagenes/descargar.png" style="margin-bottom:2px;"/> <br />
                <b>Descargar Ejemplo</b> </a>
              </center></td>
          </tr>
        </table></td>
    </tr>
    <tr>
      <td colspan="2"><center>
          <input type="submit" value="Cargar Archivo">
        </center></td>
    </tr>
    </tbody>
  </table>
</form>
<hr width="450" />
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" width="400">
  <tr>
    <th colspan="4"><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Configuraci&oacute;n de Hojas de Impresi&oacute;n
    <img src="imagenes/arrow_d.gif" style="float:right; margin-left:-16px" class="manita toggler" alt="impresiones" />
    </th>
  </tr>
   <tbody class="slide_toggle" id="impresiones">
  <tr>
    <td width="25%" style="text-align:center"><a href="?section=vectors_factura"><img src="imagenes/vars.jpg" width="64" height="64" /></a></td>
   <!-- <td width="25%" style="text-align:center"><a href="?section=vectors_nota"><img src="imagenes/vars.jpg" width="64" height="64" /></a></td>
    <td width="25%" style="text-align:center"><a href="?section=vectors_credito"><img src="imagenes/vars.jpg" width="64" height="64" /></a></td>-->
    <td width="25%" style="text-align:center"><a href="?section=vectors_contrarecibo"><img src="imagenes/vars.jpg" width="64" height="64" /></a></td>
  </tr>
  <tr>
    <td style="text-align:center; white-space:nowrap"><b><a href="?section=vectors_factura"><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Factura</a></b></td>
    <!--<td style="text-align:center; white-space:nowrap"><b><a href="?section=vectors_nota"><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Nota de Venta</a></b></td>
    <td style="text-align:center; white-space:nowrap"><b><a href="?section=vectors_credito"><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Nota de C&eacute;dito</a></b></td>-->
    <td style="text-align:center; white-space:nowrap"><b><a href="?section=vectors_contrarecibo"><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Contrarecibo</a></b></td>
  </tr>
  </tbody>
</table>
<hr width="450" />
<?php
$r = mysql_fetch_assoc($q);
?>
<form action="" method="post" enctype="multipart/form-data" style="text-align:center">
 <input type="hidden" name="ts" id="ts" value="<?=$r[smtp_pass]?>">
  <table width="300" border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla">
    <tr>
      <th colspan="2" style="text-align:center">
      	<img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Datos de la Empresa
        <img src="imagenes/arrow_d.gif" style="float:right; margin-left:-16px" class="manita toggler" alt="cliente" id="cliente_data" />
      </th>
    </tr>
   
    <?php
    if(count($faltantes)>0){
    ?>
     <caption class="aviso">
     <b>Algunos campos no se llenaron correctamente</b>
     </caption>
    <?php } ?>
    <tbody class="slide_toggle" id="cliente">
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Nombre</th>
      <td align="left"><input name="nombre" type="text" id="nombre" value="<?=$r[nombre]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Calle</th>
      <td align="left"><input name="calle" type="text" id="calle" value="<?=$r[calle]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;No. Exterior</th>
      <td align="left"><input name="noe" type="text" id="noe" value="<?=$r[noe]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;No. Interior</th>
      <td align="left"><input name="noi" type="text" id="noi" value="<?=$r[noi]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Colonia</th>
      <td align="left"><input name="colonia" type="text" id="colonia" value="<?=$r[colonia]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Localidad</th>
      <td align="left"><input name="localidad" type="text" id="localidad" value="<?=$r[localidad]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Municipio</th>
      <td align="left"><input name="municipio" type="text" id="municipio" value="<?=$r[municipio]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Estado</th>
      <td align="left"><input name="estado" type="text" id="estado" value="<?=$r[estado]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Pa&iacute;s</th>
      <td align="left"><select name="pais" id="pais" style="width:170px;">
          <?php
        $s = "SELECT * FROM paises";
				$q = query($s);
				while($n = mysql_fetch_assoc($q))
				{
					if(intval($data[pais]) < 1) $data[pais] = 146;
				?>
          <option value="<?=$n['id']?>" <?=selected($n[id],$data[pais])?>>
          <?=HTML($n['pais_nombre'])?>
          </option>
          <?php } ?>
        </select></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;C&oacute;digo Postal</th>
      <td align="left"><input name="cp" type="text" id="cp" size="32" value="<?=$r[cp]?>" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;RFC</th>
      <td align="left"><input name="rfc" type="text" id="rfc" size="32" value="<?=$r[rfc]?>" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Logotipo<br />
        <span style="font-size:9px">(S&oacute;lamente JPG)</span></th>
      <td align="left"><input name="logo" type="file" id="logo" size="10" />
        <?php if(strlen($r[logotipo])>0) {?>
        <br />
        <span style="font-weight:bold; font-size:12px"><a href="logo/<?=$r[logotipo]?>" onclick="return hs.expand(this)"; >Actual <img src="imagenes/search.png" style="margin-bottom:-2px" /></a></span>
        <?php } ?></td>
    </tr>
    </tbody>
  </table>
  <hr width="450" />
  
  
  <table width="300" border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla">
    <tr>
      <th colspan="2" style="text-align:center">
      	<img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Datos de la Facturaci&oacute;n Electr&oacute;nica
        <img src="imagenes/arrow_d.gif" style="float:right; margin-left:-16px" class="manita toggler" alt="facturacion" />
      </th>
    </tr>
   <tbody class="slide_toggle" id="facturacion">
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;No de serie del C.S.D</th>
      <td align="left"><input name="ncsd" type="text" id="ncsd" value="<?=$r[ncsd]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;A&ntilde;o de aprobaci&oacute;n</th>
      <td align="left"><input name="anoa" type="text" id="anoa" value="<?=$r[anoa]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;No de aprobaci&oacute;n</th>
      <td align="left"><input name="noa" type="text" id="noa" value="<?=$r[noa]?>" size="32" /></td>
    </tr>   
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Serie de la factura</th>
      <td align="left"><input name="serie" type="text" id="serie" value="<?=$r[serie]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Folio inicial</th>
      <td align="left"><input name="folioi" type="text" id="folioi" value="<?=$r[folioi]?>" size="32" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Folio final</th>
      <td align="left"><input name="foliof" type="text" id="foliof" value="<?=$r[foliof]?>" size="32" /></td>
    </tr>
     <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Ruta<br />
        <span style="font-size:9px">(Donde se guardan los archivos)</span></th>
      <td align="left"><input name="ruta" type="text" id="ruta" value="<?=$r[ruta]?>" size="32" />
        </td>
    </tr>
     <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;C&eacute;dula<br />
        <span style="font-size:9px">(S&oacute;lamente JPG)</span></th>
      <td align="left"><input name="cedula" type="file" id="cedula" size="10" />
        <?php if(strlen($r[cedula])>0) {?>
        <br />
        <span style="font-weight:bold; font-size:12px"><a href="cedula/<?=$r[cedula]?>" onclick="return hs.expand(this)"; >Actual <img src="imagenes/search.png" style="margin-bottom:-2px" /></a></span>
        <?php } ?></td>
    </tr>
    </tbody>
  </table>
  <hr width="450" />  
  
  
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla" width="280">
  <?php
	 if(isset($_GET[config]))
	 {?>
		 <caption class="aviso">Hubo en error al intentar conectarse. <br/>Verifique su configuraci&oacute;n y vuelva intentarlo.</caption>
	  <?php 
	 }
	?>
  <tr>
    <th colspan="2" style="text-align:center">
    <img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Servidor de Correo Electr&oacute;nico<img src="imagenes/arrow_d.gif" style="float:right; margin-left:-16px" class="manita toggler" alt="smtp_server" />
    </th>
  </tr>
   <tbody class="slide_toggle2" id="smtp_server">
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Servidor</th>
      <td align="left"><input name="smtp_servidor" type="text" id="smtp_servidor" value="<?=$r[smtp_servidor]?>" size="26" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Remitente</th>
      <td align="left"><input name="smtp_remitente" type="text" id="smtp_remitente" value="<?=$r[smtp_remitente]?>" size="26" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Puerto</th>
      <td align="left"><input name="smtp_puerto" type="text" id="smtp_puerto" value="<?=$r[smtp_puerto]?>" size="26" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Usuario</th>
      <td align="left"><input name="smtp_usuario" type="text" id="smtp_usuario" value="<?=$r[smtp_usuario]?>" size="26" /></td>
    </tr>
    <tr>
      <th><img src="imagenes/vars.png" style="margin-bottom:-4px" />&nbsp;Contrase&ntilde;a</th>
      <td align="left">
      	<a id="pass" style="font-size:11px;" href="javascript:cambiar_pass();">Click para cambiar contrase&ntilde;a</a>
      	<input name="smtp_pass" type="password" id="smtp_pass" size="26" style="display:none" disabled="disabled" />
      </td>
    </tr>
    <tr>
      <th nowrap="nowrap"><img src="imagenes/vars.png" style="margin-bottom:-4px" /> Autenticaci&oacute;n</th>
      <td align="left"><input type="checkbox" name="smtp_autenticacion" id="smtp_autenticacion" value="1" <?=($r[smtp_autenticar] == 1)?"checked":""?> /></td>
    </tr>
    <tr>
      <th nowrap="nowrap"><img src="imagenes/vars.png" style="margin-bottom:-4px" /> Activado</th>
      <td align="left"><input type="checkbox" name="mail_activo" id="mail_activo" value="1" <?=($r[mail_activo] == 1)?"checked":""?> /></td>
    </tr>
  </tbody>
</table>
<hr width="450" />
  
  <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla">
    <tr>
      <th>Precio M&iacute;nimo de Venta</th>
      <td><input name="pmv" type="text" id="pmv" value="<?=number_format($r[pmv], 2, '.', '')?>" size="10"  onblur="numero(this,2);" style="text-align:right"/>
        %</td>
    </tr>  
     <tr>
      <th>Valor actual del D&oacute;lar</th>
      <td><input name="dolar" type="text" id="dolar" value="<?=number_format($r[dolar], 2, '.', '')?>" size="10"  onblur="numero(this,2);" style="text-align:right"/>
        </td>
    </tr>   
  </table>
  <br />
  <input name="registrar" type="submit" value="Registrar" />
</form>