<?php
if(!isset($_GET[ok])){
	if(isset($_POST[mysql_intervention])){
		//Verificar datos obligatorios
		$o = array("codigo_barras","descripcion");
		$error = array();
		foreach($o as $oo){
			if(strlen($_POST[$oo])==0){
				switch($oo){
					case "codigo_barras": $error[] = "Faltó llenar el campo 'Código de Barras'.";
					break;
					
					case "descripcion": $error[] = "Faltó llenar el campo 'Descripción'.";
					break;
				}
			}
		}
		
		if(count($error)==0){
			if($_POST[mysql_intervention] == "Registrar"){
				$strSQL = "INSERT INTO productos_solido
						VALUES(
						null,
						'{$_POST['descripcion']}', 
						'{$_POST['codigo_barras']}', 
						'{$_POST['precio_publico']}', 
						'{$_POST['iva']}',
						0)";
					mysql_query($strSQL) or die (nl2br($strSQL)."<br>".mysql_error());
					$id = mysql_insert_id();
					for($i=0; $i<count($_POST['cliente']); $i++){
								if($_POST['activo'][$i] == 1){
									$sql = "INSERT INTO precios VALUES (NULL,'{$id}','{$_POST['cliente'][$i]}','{$_POST['precio'][$i]}')";
									mysql_query($sql) or die (mysql_error());
								}
					}
					////-----------Agregar max y min 29/10/2010
					foreach($_POST[max] as $k => $v)
					{						  
						   if(isset($_POST[activo][$k]))
						   {
							 $estado = 0;   
						   }
						   else
						   {
							 $estado =1;
						   }
					   $s="INSERT INTO minmax_productos VALUES(
							null,
							{$id},
							{$k},
							{$_POST[min][$k]},
							{$v},
							{$estado}
							)"; 					   
					     mysql_query($s);   
					}		//termina 29/10/2010 inserccion para min y max
			 }
	
			if($_POST[mysql_intervention] == "Modificar"){
				$strSQL = "UPDATE productos_solido 
						SET
						descripcion = '{$_POST['descripcion']}',
						codigo_barras = '{$_POST['codigo_barras']}',
						precio_publico = '{$_POST['precio_publico']}',
						iva = '{$_POST['iva']}'
						WHERE id_producto = '{$_POST[modificar]}'";
				mysql_query($strSQL) or die (mysql_error());
				//empieza cuando es modificacion min y max 29/10/2010
				  foreach($_POST[max] as $k => $v)
					{						  
						   if(isset($_POST[activo][$k]))
						   {
							 $estado = 0;   
						   }
						   else
						   {
							 $estado =1;
						   }
				       $test ="SELECT * FROM minmax_productos WHERE id_producto ={$_GET[modificar]} AND almacen ={$k}";
					   $test_q=mysql_query($test);
				
						if(mysql_num_rows($test_q)> 0)
						{
							 $sql_= "UPDATE minmax_productos 
									 SET				
									 min = {$_POST[min][$k]},
									 max = {$v},
									 status = {$estado}
									 WHERE id_producto ={$_GET[modificar]} AND almacen = {$k}";	
						}
						else{
							   $sql_="INSERT INTO minmax_productos VALUES(
									null,
									{$_GET[modificar]},
									{$k},
									{$_POST[min][$k]},
									{$v},
									{$estado}
									)"; 		
							}
						 mysql_query($sql_) or die (mysql_error($sql_));
					}
						//termina cuando es modificacion min y max 29/10/2010
				
				for($i=0; $i<count($_POST['cliente']); $i++){
					if($_POST['activo'][$i] != 1){
						$sql = "DELETE FROM precios WHERE id_producto = '{$_POST['modificar']}' AND cliente = '{$_POST['cliente'][$i]}'";
					}
					else{
						$precios_sql = "SELECT id FROM precios WHERE id_producto = '{$_POST['modificar']}' AND cliente = '{$_POST['cliente'][$i]}'";
						$precios_query = mysql_query($precios_sql)or die(mysql_error());
						if(mysql_num_rows($precios_query)>0){
							$sql = "UPDATE precios SET precio = '{$_POST['precio'][$i]}' WHERE id_producto = '{$_POST['modificar']}' AND cliente = '{$_POST['cliente'][$i]}'";
						}
						else{
							$sql = "INSERT INTO precios VALUES (NULL,'{$_POST['modificar']}','{$_POST['cliente'][$i]}','{$_POST['precio'][$i]}')";
						}
					}
					mysql_query($sql) or die (mysql_error());
				}
				$id = $_POST[modificar];
			}
			
			mysql_query("CALL productos2Memory()");
			relocation("?section=productos_formulario&modificar={$id}&ok");
		}
	}
	//Dependiendo de esta configuración, el sistema detectará si es REGISTRO ó MODIFICACIÓN	de los datos de un usuario
	if(isset($_GET[registrar])){
		$tipo_formulario = "Registrar";
	}
	if(isset($_GET[modificar])){
		$tipo_formulario = "Modificar";
	}
	//Poner datos en el formulario
	if(!isset($_POST[pais])){
		$data[pais] = 146; //Poner a México como país predeterminado
	}
	$data[] = array();
	if(@count($error)>0){ //Hubo errore. Restaurar los datos.
		$data = array_merge($data,$_POST);
	}else if(isset($_GET[modificar])) { //Obtener datos originales de la BDD
		$s = "SELECT * FROM productos WHERE id_producto = '{$_GET[modificar]}'";
		$q = mysql_query($s) or die (mysql_error());
		$r = mysql_fetch_assoc($q);
		$data = array_merge($data,$r);
	}
	
	//Inicia configuración
	titleset("Registro y Edici&oacute;n de Productos");
	//Fin de configuración
}
?>
<script language="javascript" type="text/javascript">
function cambiar(cliente){
  check = document.getElementById("check"+cliente);
  row = document.getElementById("row"+cliente);
  precio = document.getElementById("precio"+cliente);
  td = document.getElementById("td"+cliente);
  activo = document.getElementById("activo"+cliente);
  
  if(check.checked){
    row.style.color = "";
    precio.readOnly = false;
    td.style.fontWeight = "bold";
    activo.value = "1";
  }
  else{
		<?php //Quitar / Poner el confirm cuando sea Registro / Modificación respectivamente
			if(isset($_GET[modificar])){
		?>
    if(confirm("¿Seguro que desea eliminar éste producto\nde ésta lista de precios?")){
      row.style.color = "#888888";
      precio.readOnly = true;
      td.style.fontWeight = "";
      activo.value = "0";
    }
    else{
      check.checked = true;
    }
		<?php
			} else {
		?>
		row.style.color = "#888888";
		precio.readOnly = true;
		td.style.fontWeight = "";
		activo.value = "0";
		<?php
			}
		?>
  }
}

function CalcularCostoTotal(){
	var precio = document.getElementById("precio_publico").value;
	var iva = document.getElementById("iva").value;
	var costo = parseFloat(precio) + parseFloat(precio)*parseFloat(iva/100);
	if(isNaN(costo)){ costo = 0; }
	document.getElementById("costoTotal").innerHTML = parseFloat(costo).toFixed(2);
}
</script>
<?php if(isset($_GET[modificar])){ ?>
<center><a href="?section=productos_formulario&registrar"><img src="imagenes/add.png" style="margin-bottom:-3px" /> <b>Agregar Otro Producto</b></a></center><br />
<?php } ?>
<form action="" method="post" name="productos">
  <table border="0" align="center" cellpadding="10" cellspacing="0" class="bordear_tabla" id="formulario">
    <caption id="aviso">
    Los campos marcados con * son obligatorios.
    </caption>
    <?php
	if(@count($error)>0){
	?>
    <tr>
      <td colspan="2" id="formulario_error"><div style="text-align:left"> El sistema ha arrojado los siguientes errores:
          <?php
	foreach($error as $e){
		echo "<li style=\"margin-left:20px; padding:0px;\">".htmlentities($e)."</li>";
	}
	?>
        </div></td>
    </tr>
    <?php
	}
	?>
    <tr>
      <th>C&oacute;digo de Baras*</th>
      <td><input name="codigo_barras" type="text" id="codigo_barras" size="40" value="<?=htmlentities($data[codigo_barras])?>" /></td>
    </tr>
    <tr>
      <th>Descripci&oacute;n*</th>
      <td><input name="descripcion" type="text" id="descripcion" value="<?=htmlentities($data[descripcion])?>" size="40" /></td>
    </tr>
    <!--<tr>
      <th>Precio m&iacute;nimo de venta actual</th>
      <td>$
			<?php
			if(isset($_GET[modificar])){
				// $query_minimo = "SELECT id_producto, pmv FROM precio_minimo WHERE id_producto = '{$_GET['modificar']}'";
				// $minimo = mysql_query($query_minimo)or die(mysql_error());
				// $row_minimo = mysql_fetch_assoc($minimo);
				// echo money($row_minimo['pmv']);
			}
			else{
				echo "0.00";;
			}
			?>
      </td>
    </tr>-->
    <tr>
      <th>Precio al p&uacute;blico</th>
      <td><b>$
        <input name="precio_publico" type="text" id="precio_publico" size="8" value="<?=number_format($data[precio_publico],3,'.','')?>" style="text-align:right;" onBlur="numero(this,3); CalcularCostoTotal();" />
        + IVA
        <input name="iva" type="text" id="iva"  size="5" maxlength="5" value="<?=number_format($data[iva],2,'.','')?>" style="text-align:right;" onBlur="numero(this,2); CalcularCostoTotal();"/>
        % = $ <span id="costoTotal" style="font-weight:bold">
        <?=money($data['precio_publico']+($data['precio_publico']*($data['iva']/100)))?>
        </span></b></td>
    </tr>
    <tr>
      <td colspan="2" style="text-align:center">
        <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
          <tr>
            <th colspan="3" >Listas de Precios</th>
          </tr>
<?php
$query_tipocliente = "SELECT cliente FROM precios GROUP BY cliente ORDER BY cliente DESC";
$tipocliente = mysql_query($query_tipocliente)or die(mysql_error());
if(mysql_num_rows($tipocliente)>0){
?>
          <tr>
            <th>&nbsp;</th>
            <th>Cliente</th>
            <th>Precio ($)</th>
          </tr>
          <?php
while($row_tipocliente = mysql_fetch_assoc($tipocliente)){
	if(isset($_GET['modificar'])){
		$query_cliente = "SELECT precio, cliente, id FROM precios WHERE cliente='{$row_tipocliente['cliente']}' AND id_producto = '{$_GET['modificar']}'";
		$cliente = mysql_query($query_cliente)or die(mysql_error());
		$row_cliente = mysql_fetch_assoc($cliente);
	}
	if($row_cliente['precio'] <> NULL){
		$checked = "checked=\"checked\"";
		$value = 1;
		$readonly = "";
		$precio = money($row_cliente['precio']);
		$color = "#000";
		$font = "bold";
	}
	else{
		$checked = "";
		$value = 0;
		$readonly = "readonly=\"readonly\"";
		$precio = "";
		$color = "#888888";
		$font = "";
	}
?>
          <tr id="row<?=$row_tipocliente['cliente']?>" style="color:<?=$color?>">
            <td bgcolor="#CCCCCC">
            	<input name="check[]" type="checkbox" id="check<?=$row_tipocliente['cliente']?>" onClick="cambiar('<?=$row_tipocliente['cliente']?>')" value="1" <?=$checked?>/>
              <input type="hidden" size="8" name="activo[]" id="activo<?=$row_tipocliente['cliente']?>" value="<?=$value?>"/>
            </td>
            <td bgcolor="#CCCCCC" id="td<?=$row_tipocliente['cliente']?>" style="font-weight:<?=$font?>">
            	<input type="hidden" size="8" name="cliente[]" value="<?=$row_tipocliente['cliente']?>"/>
              <?=$row_tipocliente['cliente']?>
            </td>
            <td align="left" bgcolor="#CCCCCC">
            	<input type="text" size="8" name="precio[]" id="precio<?=$row_tipocliente['cliente']?>" value="<?=number_format((double)$precio,2,'.','')?>" onBlur="numero(this,2);" style="text-align:right" <?=$readonly?>/>
            </td>
          </tr>
          <?php	}	} else {?>
          <tr>
            <td colspan="3" style="text-align:center" id="aviso">No hay listas de precios actualmente.</td></tr>
          <?php } ?>
        </table>
       </td>
    </tr>
    <?php //29/10/2010 empieza?>
    <tr>
      <td colspan="2">
         <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
          <tr>
            <th colspan="4" >Inventarios m&aacute;ximos y m&iacute;nimos</th>
          </tr>
          <tr>
            <th>Almac&eacute;n</th>
            <th>M&iacute;nimo</th>
            <th>M&aacute;ximo</th>
            <th>Activo</th>
</tr>
<?php
if(isset($_GET[modificar]))
{
	$minmax_array = array();
	$sa = "SELECT *	FROM minmax_productos WHERE id_producto = {$_GET[modificar]}";    
	$qa = mysql_query($sa) or die (mysql_error());
	while($ra = mysql_fetch_assoc($qa))
	{
		$minmax_array[] = array_merge($ra);
	}
}
foreach($_SESSION[almacenes] as $k => $v){
	$dato_min = 0;
	$dato_max = 0;
	if(isset($_GET[modificar]))
	{	
		foreach($minmax_array as $arra_key => $array_valores)
		{
			if($v[id] == $array_valores[almacen])
			{
				$dato_min = $array_valores[min];
				$dato_max = $array_valores[max];
				$status = $array_valores[status];
				break;
			}
		}
	}
	else
	{
		$status = 1;
	}
	
?>
<tr>
            <td><b><?=$v[descripcion]?></b></td>
            <td><input name="min[<?=$v[id]?>]" type="text"  onBlur="numero(this)" class="minmax_input" value="<?=$dato_min?>" /></td>
            <td><input name="max[<?=$v[id]?>]" type="text" onBlur="numero(this)" class="minmax_input" value="<?=$dato_max?>" /></td>
            <td style="text-align:center"><input type="checkbox" name="activo[<?=$v[id]?>]" <?=checked($status,0)?> /></td>
          </tr>
          <?php
}
?>

        </table>
      </td>
    </tr>
     <?php //29/10/2010 termina?>
    <tr>
      <td colspan="2" style="text-align:center"><input type="submit" value="<?=$tipo_formulario?>" name="mysql_intervention"/></td>
    </tr>
  </table>
  <input type="hidden" name="modificar" value="<?=$_GET[modificar]?>" />
</form>
