<?php
if (!isset($_GET[ok])) {
    titleset("Registro y Edicin de Usuarios");
    if (isset($_POST[mysql_intervention])) {
        //Verificar datos obligatorios
        if ($_POST[mysql_intervention] == "Registrar") {
            $o = array("username", "contrasena1", "contrasena2", "nombre");
        }
        if ($_POST[mysql_intervention] == "Modificar") {
            $o = array("nombre");
        }
        $error = array();
        foreach ($o as $oo) {
            if (strlen($_POST[$oo]) == 0) {
                switch ($oo) {
                    case "username":
                        $error[] = "El campo <b>Usuario</b> no es correcto.";
                    break;
                    case "contrasena1":
                        $error[] = "El campo <b>Contrasea</b> no es correcto.";
                    break;
                    case "contrasena2":
                        $error[] = "El campo <b>Confirmacin de Contrasea</b> no es correcto.";
                    break;
                    case "nombre":
                        $error[] = "El campo <b>Nombre</b> no es correcto.";
                    break;
                }
            }
        }
        if ($_POST[mysql_intervention] == "Registrar") {
            //Verificar que la contrasea haya sido confirmada correctamente
            if ($_POST[contrasena1] != $_POST[contrasena2]) {
                $error[] = "La contrasea no pudo ser confirmada";
            }
            //Verificar que el usuario no exista en la Base de Datos
            $s = "SELECT username FROM usuarios WHERE username = '{$_POST[username]}'";
            $r = mysql_query($s) or die(mysql_error());
            if (mysql_num_rows($r) > 0) {
                $error[] = "El nombre de usuario no est disponible";
            }
        }
        $almacenes = "";
        //Almacenes del usuario
        $s = "SELECT almacenes FROM tipos_usuarios WHERE id_tipousuario = {$_POST[id_tipousuario]}";
        $q = mysql_query($s) or die(mysql_error());
        $r = mysql_fetch_assoc($q);
        if ($r[almacenes] == 0) {
            $almacenes = "*";
        } else {
            if (count($_POST['id_almacen']) > 0) {
                foreach ($_POST['id_almacen'] as $a) {
                    $almacenes.= $a . "-";
                }
                $almacenes = substr($almacenes, 0, strlen($almacenes) - 1);
            } else {
                $error[] = "No seleccion ningn almacn.";
            }
        }
        if (count($error) == 0) { //Sin errores
            //Ingreso de datos
            if ($_POST[mysql_intervention] == "Registrar") { //Registrar nuevo usuario
                $s = "INSERT INTO usuarios VALUES(
				NULL,
				'{$_POST[username]}',
				MD5('{$_POST[contrasena1]}'),
				'{$_POST[nombre]}',
				'{$_POST[correo]}',
				'{$_POST[id_tipousuario]}',
				'{$almacenes}',
				'{$_POST[id_entidad]}',
				NOW(),
				0
				)";
                mysql_query($s) or die(mysql_error());
                $id = mysql_insert_id();
            }
            if ($_POST[mysql_intervention] == "Modificar") { //Modificar datos de usuario
                $s = "UPDATE usuarios SET
				nombre = '{$_POST[nombre]}',
				correo = '{$_POST[correo]}',
				id_tipousuario = '{$_POST[id_tipousuario]}',
				almacenes = '{$almacenes}',
				id_entidad = '{$_POST[id_entidad]}'
				WHERE id_usuario = '{$_POST[modificar]}'";
                mysql_query($s) or die(mysql_error());
                $id = $_POST[modificar];
            }
            relocation("?section=usuarios_formulario&modificar={$id}&ok");
        }
    }
    //Dependiendo de esta configuracin, el sistema detectar si es REGISTRO  MODIFICACIN	de los datos de un usuario
    if (isset($_GET[registrar])) {
        $tipo_formulario = "Registrar";
    }
    if (isset($_GET[modificar])) {
        $tipo_formulario = "Modificar";
    }
    //Poner datos en el formulario
    $data[] = array();
    if (@count($error) > 0) { //Hubo errore. Restaurar los datos.
        $data = array_merge($data, $_POST);
    } else if (isset($_GET[modificar])) { //Obtener datos originales de la BDD
        $s = "SELECT * FROM usuarios WHERE id_usuario = '{$_GET[modificar]}'";
        $q = mysql_query($s) or die(mysql_error());
        $r = mysql_fetch_assoc($q);
        $data = array_merge($data, $r);
        if ($data[almacenes] != "*") { //Crear arreglo de almacenes
            $data[almacenes] = explode("-", $data[almacenes]);
        }
    }
}
?>
<script type="text/javascript" language="JavaScript">
function almacenes(){
	id_tipousuario = document.getElementById("id_tipousuario");
	id_almacen = document.getElementById("id_almacen");
	if(id_tipousuario.options[id_tipousuario.selectedIndex].title==0){
		id_almacen.disabled = true;
	} else {
		id_almacen.disabled = false;
	}
}
</script>
<form action="" method="post" name="usuarios">
<table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla" id="formulario">
  <caption id="aviso">
  Los campos marcados con * son obligatorios.
  </caption>
  <?php
if (@count($error) > 0) {
?>
  <tr>
  <td colspan="2" id="formulario_error">
  <div style="text-align:left">
  El sistema ha arrojado los siguientes errores:
  <?php
    foreach ($error as $e) {
        echo "<li style=\"margin-left:20px; padding:0px;\">{$e}</li>";
    }
?>
  </div>
  </td>
  </tr>
  <?php
}
if (!isset($_GET[modificar])) {
?>
  <tr>
    <th>Usuario*</th>
    <td><input name="username" type="text" id="username" size="30" value="<?=$data[username] ?>"/></td>
  </tr>
  <tr>
    <th nowrap="nowrap">Contrase&ntilde;a*</th>
    <td><input name="contrasena1" type="password" id="contrasena_n" size="30" /></td>
  </tr>
  <tr>
    <th nowrap="nowrap">Confirme su  Contrase&ntilde;a*</th>
    <td><input name="contrasena2" type="password" id="contrasena_r" size="30" /></td>
  </tr>
  <?php
} else {
?>
  <tr>
    <th>Usuario</th>
    <td><?=$data[username] ?></td>
  </tr>
  <?php
}
?>
  <tr>
    <th>Nombre Completo*</th>
    <td><input name="nombre" type="text" id="nombre" size="30"  value="<?=$data[nombre] ?>"/></td>
  </tr>
  <tr>
    <th>Correo</th>
    <td><input name="correo" type="text" id="correo" size="30"  value="<?=$data[correo] ?>" /></td>
  </tr>
  <tr>
    <th>Entidad</th>
    <td><select name="id_entidad" id="id_entidad">
        <?php
$strSQL = "SELECT * FROM entidades ORDER BY nombre";
$reg = mysql_query($strSQL) or die(mysql_error());
while ($n = mysql_fetch_assoc($reg)) { ?>
        <option value="<?=$n['id_entidad'] ?>" <?=selected($n['id_entidad'], $data[id_entidad]) ?>>
        <?=$n['nombre'] ?>
        </option>
        <?php
} ?>
      	</select>
    </td>
  </tr>
  <?php if (Administrador() || CCC()) { ?>
  <tr>
    <th>Tipo de usuario</th>
    <td>
    <select name="id_tipousuario" id="id_tipousuario" onChange="almacenes();">
    <?php
    $strSQL = "SELECT * FROM tipos_usuarios ORDER BY descripcion";
    $reg = mysql_query($strSQL) or die(mysql_error());
    while ($n2 = mysql_fetch_assoc($reg)) {
        if ($n2['descripcion'] <> 'Indeterminado') { ?>
    <option value="<?=$n2['id_tipousuario'] ?>" title="<?=$n2['almacenes'] ?>" <?=selected($n2['id_tipousuario'], $data[id_tipousuario]) ?>><?=$n2['descripcion'] ?></option>
    <?php
        }
    }
?>
    </select>
    </td>
  </tr>
  <tr>
    <th>Almacenes</th>
    <td><select name="id_almacen[]" size="10" multiple="multiple" id="id_almacen"><?php
    $strSQL = "SELECT * FROM almacenes WHERE status = 0 ORDER BY descripcion";
    $reg = mysql_query($strSQL) or die(mysql_error());
    while ($n3 = mysql_fetch_assoc($reg)) {
?><option value="<?=$n3['id_almacen'] ?>" <?=selectedMultiple($n3[id_almacen], $data[almacenes]) ?>><?=$n3['descripcion'] ?></option>
        <?php
    } ?>
      </select></td>
  </tr>
  <?php
} ?>
  <tr>
    <td colspan="2" style="text-align:center"><input type="submit" value="<?=$tipo_formulario
?>" name="mysql_intervention"/></td>
  </tr>
</table>
<input type="hidden" name="modificar" value="<?=$_GET[modificar] ?>" />
</form>
<script type="text/javascript" language="JavaScript">
almacenes();
</script>