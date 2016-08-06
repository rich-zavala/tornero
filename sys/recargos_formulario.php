<?php
if (!isset($_SESSION) or !isset($_SESSION['nombre']) or !isset($_SESSION['id_tipousuario']))
  header("Location: index.php");

if(!Administrador()) exit;

if(isset($_POST['id']))
{
	$o = array("etiqueta", "porcentaje");
	foreach($o as $oo)
		if(strlen($_POST[$oo]) == 0)
			switch ($oo)
			{
				case "etiqueta":
					$error[] = "El campo <b>Etiqueta</b> no es correcto.";
					break;
				case "porcentaje":
					$error[] = "El campo <b>Porcentaje</b> no es correcto.";
					break;
			}
	
	if(!is_numeric($_POST['porcentaje']))
		$error[] = "El campo <b>Porcentaje</b> debe ser numérico.";
	
	if(count($error) == 0)
	{
		$_POST['porcentaje'] = number_format((float)$_POST['porcentaje'], 2, '.', '');
		$_POST['usuario'] = $_SESSION['id_usuario'];
		$d = $_POST;
		if($_POST['id'] == 0)
		{
			unset($_POST['id']);
			$_POST['id'] = $db->insert('recargos', utf8_deconverter($d));
		}
		else
		{
			if(!isset($d['activo'])) $d['activo'] = 0;
			$db->update('recargos', $d, array( 'id' => $d['id'] ));
		}
		
		relocation("?section=recargos_formulario&modificar={$_POST['id']}&ok");
	}
}

if(!isset($_GET['modificar']))
	$_GET['modificar'] = 0;

//Dependiendo de esta configuracin, el sistema detectar si es REGISTRO  MODIFICACIN	de los datos de un usuario
$tipo_formulario = isset($_GET['registrar']) ? "Registrar" : "Modificar";

//Poner datos en el formulario
$data[] = array();
if(@count($error) > 0) //Hubo errore. Restaurar los datos.
	$data = array_merge($data, $_POST);
else if(isset($_GET['modificar'])) //Obtener datos originales de la BDD
{
	$s = "SELECT etiqueta, porcentaje, activo FROM recargos WHERE id = '{$_GET['modificar']}' LIMIT 1";
	$data = $db->fetchRow($s);
}

titleset("Registro y Edici&oacute;n de Recargos");
?>
<center><a href="?section=recargos_formulario&registrar"><img src="imagenes/add.png" style="margin-bottom:-3px"> <b>Agregar otro registro</b></a></center>
<br>
<form action="" method="post" name="recargos">
	<table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla" id="formulario" width="400">
		<caption id="aviso">Los campos marcados con * son obligatorios.</caption>
		<?php if(@count($error) > 0){ ?>
		<tr>
			<td colspan="2" id="formulario_error">
				<div style="text-align:left">
				El sistema ha arrojado los siguientes errores:
				<?php
				foreach ($error as $e)
					echo "<li style=\"margin-left:20px; padding:0px;\">{$e}</li>";
				?>
				</div>
			</td>
		</tr>
		<?php
		}
		?>
		<tr>
			<th nowrap="nowrap" width="100"><label for="etiqueta">Etiqueta*</label></th>
			<td><input id="etiqueta" name="etiqueta" type="text" class="w100" value="<?=$data['etiqueta']?>" placeholder="Nombre de este recargo" autofocus required/></td>
		</tr>
		<tr>
			<th nowrap="nowrap"><label for="porcentaje">Porcentaje (%)*</label></th>
			<td><input id="porcentaje" name="porcentaje" type="number" class="w100" value="<?=$data['porcentaje']?>" max="100" min="-100" step="any" placeholder="Positivo = Incremento, Negativo = Descuento" required/></td>
		</tr>
		<tr>
			<th nowrap="nowrap"><label for="activo">Activo *</label></th>
			<td><input id="activo" name="activo" type="checkbox" size="30" value="1" <?=($data['activo'] == 1 ? 'checked' : null)?>/></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center"><button type="submit"><?=$tipo_formulario?></button></td>
		</tr>
	</table>
	<input type="hidden" name="id" value="<?=$_GET['modificar'] ?>" />
</form>