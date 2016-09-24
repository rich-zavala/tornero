<?php
if(!isset($_GET[ok])){
	if(isset($_POST[mysql_intervention])){
		//Verificar datos obligatorios
		$o = array("nombre","RFC","calle","noexterior","colonia","localidad","municipio","estado","cp","credito","dias_credito");
		$error = array();
		foreach($o as $oo){
			if(strlen($_POST[$oo])==0){
				switch($oo){
					case "nombre": $error[] = "Falt� llenar el campo 'Nombre'.";
					break;
					
					case "RFC": $error[] = "Falt� llenar el campo 'RFC'.";
					break;
					
					case "calle": $error[] = "Falt� llenar el campo 'Calle'.";
					break;
					
					case "noexterior": $error[] = "Falt� llenar el campo 'No. Exterior'.";
					break;
					
					case "colonia": $error[] = "Falt� llenar el campo 'Colonia'.";
					break;
					
					case "localidad": $error[] = "Falt� llenar el campo 'Localidad'.";
					break;
					
					case "municipio": $error[] = "Falt� llenar el campo 'Municipio'.";
					break;
					
					case "estado": $error[] = "Falt� llenar el campo 'Estado'.";
					break;
										
					case "cp": $error[] = "Falt� llenar el campo 'C�digo Postal'.";
					break;
	
					case "credito": $error[] = "Falt� llenar el campo 'Cr�dito'.";
					break;
	
					case "dias_credito": $error[] = "Falt� llenar el campo 'D�as de Cr�dito'.";
					break;
				}
			}
		}
		
		if(count($error)==0)
		{
			/* 19 de julio */
			/*if(strlen(trim($_POST[NumCtaPago])) == 0)
			{
				$_POST[NumCtaPago] = "NO DEFINIDO";
			}*/
			/* 19 de julio */

			if($_POST[mysql_intervention] == "Registrar"){
				$strSQL = "INSERT INTO clientes
							(
							nombre,
							telefonos,
							telefono2,
							calle,
							noexterior,
							nointerior,
							colonia,
							localidad,
							municipio,
							estado,
							pais,
							cp,
							RFC,
							vendedor,
							credito,
							grupo,
							observaciones,
							dias_credito,
							email,
							NumCtaPago
							)
							VALUES(
							'{$_POST['nombre']}',
							'{$_POST['telefonos']}',
							'{$_POST['telefono2']}',
							'{$_POST['calle']}',
							'{$_POST['noexterior']}',
							'{$_POST['nointerior']}',
							'{$_POST['colonia']}',
							'{$_POST['localidad']}',
							'{$_POST['municipio']}',
							'{$_POST['estado']}',							
							'{$_POST['pais']}',
							'{$_POST['cp']}',
							'{$_POST['RFC']}',
							'{$_POST['vendedor']}',
							'{$_POST['credito']}',
							'{$_POST['grupo']}',
							'{$_POST['observaciones']}',
							'{$_POST['dias_credito']}',
							'{$_POST['email']}',
							'{$_POST['NumCtaPago']}'
							)";
				mysql_query($strSQL) or die (mysql_error());
				$id = mysql_insert_id();
			}
			
			if($_POST[mysql_intervention] == "Modificar") {
				$strSQL = "UPDATE clientes
						SET nombre = '{$_POST['nombre']}',
						telefonos = '{$_POST['telefonos']}',
						telefono2 ='{$_POST['telefono2']}',
						calle = '{$_POST['calle']}',
						noexterior='{$_POST['noexterior']}',
						nointerior='{$_POST['nointerior']}',
						colonia= '{$_POST['colonia']}',
						localidad='{$_POST['localidad']}',
						municipio = '{$_POST['municipio']}',
						estado = '{$_POST['estado']}',					
						pais = '{$_POST['pais']}',
						cp = '{$_POST['cp']}',
						RFC = '{$_POST['RFC']}',
						vendedor = '{$_POST['vendedor']}',
						grupo = '{$_POST['grupo']}',
						credito = '{$_POST['credito']}',
						observaciones =  '{$_POST['observaciones']}',
						dias_credito =  '{$_POST['dias_credito']}',
						email = '{$_POST['email']}',
						NumCtaPago = '{$_POST['NumCtaPago']}'
						WHERE clave = {$_POST['modificar']}";
				mysql_query($strSQL) or die (mysql_error());
				$id = $_POST[modificar];
			}
			
			$db->execute("CALL clienteCredito({$id})");
			$db->execute("CALL clientes2Memory()");
			
			relocation("?section=clientes_formulario&modificar={$id}&ok");
		}
	}
	
	//Dependiendo de esta configuraci�n, el sistema detectar� si es REGISTRO � MODIFICACI�N	de los datos de un usuario
	if(isset($_GET[registrar])){
		$tipo_formulario = "Registrar";
	}
	if(isset($_GET[modificar])){
		$tipo_formulario = "Modificar";
	}
	
	//Poner datos en el formulario
	if(!isset($_POST[pais])){
		$data[pais] = 146; //Poner a M�xico como pa�s predeterminado
	}
	$data[] = array();
	if(@count($error)>0){ //Hubo errore. Restaurar los datos.
		$data = array_merge($data,$_POST);
	}else if(isset($_GET[modificar])) { //Obtener datos originales de la BDD
		$s = "SELECT * FROM clientes WHERE clave = '{$_GET[modificar]}'";
		$q = mysql_query($s) or die (mysql_error());
		$r = mysql_fetch_assoc($q);
		$data = array_merge($data,$r);
		
		if(count($data) > 0){
			foreach($data as $k => $v)
			{
				if(!is_array($v))
				{
					$data[$k] = htmlentities($v);
				}
			}
		}
	}
	
	//Inicia configuraci�n
	titleset("Registro y Edici&oacute;n de Clientes");
	//Fin de configuraci�n
}
?>
<?php if(isset($_GET[modificar])){ ?>
<center>
  <a href="?section=clientes_formulario&registrar"><img src="imagenes/add.png" style="margin-bottom:-3px" /> <b>Agregar Otro Cliente</b></a>
</center><br />
<?php } ?>
<form action="" method="post" name="clientes">
<table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla" id="formulario">
  <caption class="aviso">
  Los campos marcados con * son obligatorios.
  </caption>
  <?php
	if(@count($error)>0){
	?>
  <tr>
  <td colspan="4" id="formulario_error">
  <div style="text-align:left">
  El sistema ha arrojado los siguientes errores:
  <?php
	foreach($error as $e){
		echo "<li style=\"margin-left:20px; padding:0px;\">".htmlentities($e)."</li>";
	}
	?></div>
  </td>
  </tr>
  <?php
	}
	?>
	<tr>
      <th>Nombre*</th>
      <td><input name="nombre" type="text" id="nombre" size="30" maxlength="100" value="<?=$data[nombre]?>"/></td>
       <th>RFC*</th>
      <td><input name="RFC" type="text" id="RFC" size="30" value="<?=$data[RFC]?>"/></td>		
    </tr>
    	<th>Calle *</th>
      <td><input name="calle" type="text" id="calle" size="30"  value="<?=$data[calle]?>" /></td>
     <th>No. Exterior*</th>
      <td><input name="noexterior" type="text" id="noexterior" size="30" maxlength="100" value="<?=$data[noexterior]?>"/></td>
      
			
    </tr>
      <th>No. Interior</th>
      <td><input name="nointerior" type="text" id="nointerior" size="30"  value="<?=$data[nointerior]?>" /></td>
     <th>Colonia*</th>
      <td><input name="colonia" type="text" id="colonia" size="30" maxlength="100" value="<?=$data[colonia]?>"/></td>
      
			
    </tr>
    <tr>
    <th>Localidad*</th>
      <td><input name="localidad" type="text" id="localidad" size="30"  value="<?=$data[localidad]?>" /></td>
      <th>Municipio*</th>
      <td><input name="municipio" type="text" id="municipio" size="30" value="<?=$data[municipio]?>"/></td> 
    </tr>
    <tr>
      <th>Estado*</th>
      <td><input name="estado" type="text" id="estado" size="30" value="<?=$data[estado]?>"/></td>

      <th>Pa&iacute;s</th>
      <td><select name="pais" id="pais" style="width:170px;">
        <?php
        $s = "SELECT * FROM paises";
				$q = query($s);
				while($n = mysql_fetch_assoc($q)){
					if(intval($data[pais]) < 1) $data[pais] = 146;
				?>
        <option value="<?=$n['id']?>" <?=selected($n['id'],$data[pais])?>>
          <?=HTML($n['pais_nombre'])?>
          </option>
        <?php } ?>
      </select></td>
    </tr>
    <tr>
    <th>C&oacute;digo Postal*</th>
      <td><input name="cp" type="text" id="cp" size="30" value="<?=$data[cp]?>" onblur="numero(this,0)" /></td>
      <th>Tel&eacute;fonos</th>
      <td><input name="telefonos" type="text" id="telefonos" size="30" value="<?=$data[telefonos]?>"/></td>      
    </tr>
    <tr>
     <th>Celular</th>
      <td><input name="telefono2" type="text" id="telefono2" size="30" value="<?=$data[telefono2]?>"/></td>
         <th>E-mail</th>
    <td><input name="email" type="text" id="email" size="30"  value="<?=$data[email]?>" /></td>
      
    </tr>   
    <tr>
      <th>Grupo</th>
      <td><input name="grupo" type="text" id="grupo" size="30" value="<?=$data[grupo]?>"/></td>
      <th>Vendedor</th>
        <td><select name="vendedor" id="vendedor" style="width:170px;">
          <?php $strSQL = "SELECT * FROM usuarios WHERE status != 2 AND id_tipousuario = '1' ORDER BY nombre asc";
									$reg = mysql_query($strSQL) or die (mysql_error());
									while($n = mysql_fetch_assoc($reg)){ ?>
          <option value="<?=$n['id_usuario']?>" <?=selected($n['id_usuario'],$data[vendedor])?>><?=$n['nombre']?></option>
          <?php } ?>
        </select></td>      
    </tr>
    <tr>
      <th>Cr&eacute;dito <b>($)*</b></th>
      <td><input name="credito" type="text" id="credito" size="30" value="<?=$data[credito]?>" onBlur="numero(this,2);" /></td>
      <th>D&iacute;as de Cr&eacute;dito*</th>
      <td><input name="dias_credito" type="text" id="dias_credito" size="10" value="<?=$data[dias_credito]?>" onblur="numero(this,0);"/></td>
    </tr>
    <tr>
      <th>Observaciones</th>
      <td colspan="3"><textarea name="observaciones" cols="25" rows="3" style="height:36px; width:98%; overflow:auto;"><?=$data[observaciones]?></textarea></td>
    </tr>
    <tr>
      <th>No. de cuenta</th>
      <td colspan="3"><input name="NumCtaPago" type="text" id="NumCtaPago" size="30" value="<?=$data[NumCtaPago]?>" /></td>
    </tr>
  </table>
  <br />
  <center>
  <input type="submit" value="<?=$tipo_formulario?>" name="mysql_intervention"/>
  </center>
  <input type="hidden" name="modificar" value="<?=$_GET[modificar]?>" />
</form>