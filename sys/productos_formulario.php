<?php
if(!isset($_GET['ok']))
{
	if(isset($_POST['codigo_barras']))
	{
		//Verificar datos obligatorios
		$o = array("codigo_barras","descripcion");
		$error = array();
		foreach($o as $oo)
			if(strlen($_POST[$oo])==0)
				switch($oo)
				{
					case "codigo_barras": $error[] = "Faltó llenar el campo 'Código de Barras'.";
					break;
					
					case "descripcion": $error[] = "Faltó llenar el campo 'Descripción'.";
					break;
				}
		
		if(count($error) == 0)
		{
			$d = array(
				'descripcion'			=> $_POST['descripcion'],
				'codigo_barras'		=> $_POST['codigo_barras'],
				'precio_publico'	=> $_POST['precio_publico'],
				'unidad'					=> $_POST['unidad'],
				'iva'							=> $_POST['iva']
			);
			
			if($_POST['id_producto'] == 0)
			{
				$_POST['id_producto'] = $db->insert('productos_solido', utf8_deconverter($d));
				foreach($_POST['cliente'] as $ck => $cliente)
				{
					if($_POST['activo'][$ck] == 1)
					{
						$d = array(
							'id_producto' => $$_POST['id_producto'],
							'cliente'			=> $cliente,
							'precio'			=> $_POST['precio'][$ck]
						);
						$db->insert('precios', utf8_deconverter($d));
					}
				}
			}
			else
			{
				$db->update('productos_solido', $d, array( 'id_producto' => $_POST['id_producto'] ));
				foreach($_POST['cliente'] as $ck => $cliente)
				{
					if($_POST['activo'][$ck] != 1)
					{
						$db->delete('precios', "id_producto = {$_POST['id_producto']} AND cliente = '{$cliente}'");
					}
					else
					{
						$d = array(
							'id_producto' => $_POST['id_producto'],
							'cliente'			=> $cliente,
							'precio'			=> $_POST['precio'][$ck]
						);
						$s = "SELECT id FROM precios WHERE id_producto = '{$_POST['id_producto']}' AND cliente = '{$cliente}'";
						$precio_existe = $db->fetchRow($s)['id'];
						if($precio_existe > 0)
							$db->update('precios', utf8_deconverter($d), array( 'id_producto' => $precio_existe, 'cliente' => $cliente ));
						else
							$db->insert('precios', utf8_deconverter($d));
					}
				}
			}
			
			foreach($_POST['max'] as $k => $v)
			{
				$max_min_data = array(
					'almacen'			=> $k,
					'min'					=> $_POST['min'][$k],
					'max'					=> $v,
					'status'			=> $estado,
					'id_producto'	=> $_POST['id_producto']
				);
				
				//Verificar si tiene maxmin
				$s = "SELECT id FROM minmax_productos WHERE id_producto = {$_POST['id_producto']} AND almacen = {$k}";
				$idMaxMin = $db->fetchRow($s)['id'];
				if(strlen($idMaxMin) > 0)
					$db->update('minmax_productos', utf8_deconverter($max_min_data), array( 'id' => $idMaxMin ));
				else
					$db->insert('minmax_productos', utf8_deconverter($max_min_data));
			}
			
			mysql_query("CALL productos2Memory()");
			relocation("?section=productos_formulario&modificar={$_POST['id_producto']}&ok");
		}
	}
	
	$data[] = array();
	if(@count($error)>0) //Hubo errore. Restaurar los datos.
	{
		$data = array_merge($data,$_POST);
	}
	else if(isset($_GET['modificar'])) //Obtener datos originales de la BDD
	{
		$s = "SELECT * FROM productos WHERE id_producto = '{$_GET['modificar']}'";
		$q = mysql_query($s) or die (mysql_error());
		$r = mysql_fetch_assoc($q);
		$data = array_merge($data,$r);
	}
	
	//Inicia configuración
	titleset("Registro y Edici&oacute;n de Productos");
	//Fin de configuración
}

//12 agosto 2016 > Catálogo de unidades de medida
$unidades = array(
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

//Preestablecer la unidad
if(!isset($data['unidad']))
	$data['unidad'] = 'PZA';
?>
<script language="javascript" type="text/javascript">
function cambiar(cliente)
{
  check = document.getElementById("check"+cliente);
  row = document.getElementById("row"+cliente);
  precio = document.getElementById("precio"+cliente);
  td = document.getElementById("td"+cliente);
  activo = document.getElementById("activo"+cliente);
  
  if(check.checked)
	{
    row.style.color = "";
    precio.readOnly = false;
    td.style.fontWeight = "bold";
    activo.value = "1";
  }
  else
	{
		<?php if(isset($_GET[modificar])){ //Quitar / Poner el confirm cuando sea Registro / Modificación respectivamente ?>
    if(confirm("¿Seguro que desea eliminar éste producto\nde ésta lista de precios?")){
      row.style.color = "#888888";
      precio.readOnly = true;
      td.style.fontWeight = "";
      activo.value = "0";
    }
    else{
      check.checked = true;
    }
		<?php } else { ?>
		row.style.color = "#888888";
		precio.readOnly = true;
		td.style.fontWeight = "";
		activo.value = "0";
		<?php } ?>
  }
}

function CalcularCostoTotal()
{
	var precio = document.getElementById("precio_publico").value;
	var iva = document.getElementById("iva").value;
	var costo = parseFloat(precio) + parseFloat(precio) * parseFloat(iva/100);
	if(isNaN(costo))
		costo = 0;
	document.getElementById("costoTotal").innerHTML = parseFloat(costo).toFixed(2);
}
</script>
<?php if(isset($_GET[modificar])){ ?>
<center><a href="?section=productos_formulario&registrar"><img src="imagenes/add.png" style="margin-bottom:-3px" /> <b>Agregar Otro Producto</b></a></center><br />
<?php } ?>
<form action="" method="post" name="productos">
  <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla" id="formulario" width="400">
    <caption id="aviso">Los campos marcados con * son obligatorios.</caption>
    <?php if(@count($error)>0){ ?>
    <tr>
      <td colspan="2" id="formulario_error">
				<div style="text-align:left"> El sistema ha arrojado los siguientes errores:
        <?php
				foreach($error as $e) echo "<li style=\"margin-left:20px; padding:0px;\">".htmlentities($e)."</li>";
				?>
        </div>
			</td>
    </tr>
    <?php } ?>
    <tr>
      <th><label for="codigo_barras">C&oacute;digo de Baras*</label></th>
      <td>
				<input name="codigo_barras" type="text" id="codigo_barras" class="w100" value="<?=htmlentities($data['codigo_barras'])?>" required />
			</td>
    </tr>
    <tr>
      <th><label for="descripcion">Descripci&oacute;n*</label></th>
      <td><input name="descripcion" type="text" id="descripcion" class="w100" value="<?=htmlentities($data['descripcion'])?>" required /></td>
    </tr>
    <tr>
      <th><label for="unidad">Unidad*</label></th>
      <td>
				<select name="unidad" id="unidad" required>
					<?php
					foreach($unidades as $unidad)
					{
						$selected = selected($unidad, $data['unidad']);
					?>
					<option <?=$selected?>><?=$unidad?></option>
					<?php
					}
					?>
				</select>
			</td>
    </tr>
    <tr>
      <th><label for="precio_publico">Precio al p&uacute;blico</label></th>
      <td>
				<b>$
					<input name="precio_publico" type="text" id="precio_publico" size="8" value="<?=number_format($data['precio_publico'],3,'.','')?>" style="text-align:right;" onBlur="numero(this,3); CalcularCostoTotal();" required />
					+ IVA
					<input name="iva" type="text" id="iva"  size="5" maxlength="5" value="<?=number_format($data['iva'],2,'.','')?>" style="text-align:right;" onBlur="numero(this,2); CalcularCostoTotal();" required />
					% = $ <span id="costoTotal" style="font-weight:bold">
					<?=money($data['precio_publico']+($data['precio_publico']*($data['iva']/100)))?>
					</span>
				</b>
			</td>
    </tr>
	</table>
	
	<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista margin-top10">
		<tr>
			<th colspan="3" >Listas de Precios</th>
		</tr>
		<?php
		$query_tipocliente = "SELECT cliente FROM precios GROUP BY cliente ORDER BY cliente DESC";
		$tipocliente = mysql_query($query_tipocliente)or die(mysql_error());
		if(mysql_num_rows($tipocliente) > 0)
		{
		?>
		<tr>
			<th>&nbsp;</th>
			<th>Cliente</th>
			<th>Precio ($)</th>
		</tr>
			<?php
			while($row_tipocliente = mysql_fetch_assoc($tipocliente))
			{
				if(isset($_GET['modificar']))
				{
					$query_cliente = "SELECT precio, cliente, id FROM precios WHERE cliente='{$row_tipocliente['cliente']}' AND id_producto = '{$_GET['modificar']}'";
					$cliente = mysql_query($query_cliente)or die(mysql_error());
					$row_cliente = mysql_fetch_assoc($cliente);
				}
				if($row_cliente['precio'] <> NULL)
				{
					$checked = "checked=\"checked\"";
					$value = 1;
					$readonly = "";
					$precio = money($row_cliente['precio']);
					$color = "#000";
					$font = "bold";
				}
				else
				{
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
		<?php
			}
		}
		else
		{
		?>
		<tr>
			<td colspan="3" style="text-align:center" id="aviso">No hay listas de precios actualmente.</td>
		</tr>
		<?php } ?>
	</table>
	
	<table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla margin-top10" id="formulario" width="400">
		<tr>
			<th colspan="4" class="text-center">Inventarios m&aacute;ximos y m&iacute;nimos</th>
		</tr>
		<tr>
			<th class="text-center">Almac&eacute;n</th>
			<th class="text-center">M&iacute;nimo</th>
			<th class="text-center">M&aacute;ximo</th>
			<th class="text-center">Activo</th>
		</tr>
		<?php
		if(isset($_GET['modificar']))
		{
			$minmax_array = array();
			$sa = "SELECT *	FROM minmax_productos WHERE id_producto = {$_GET['modificar']}";    
			$qa = mysql_query($sa) or die (mysql_error());
			while($ra = mysql_fetch_assoc($qa))
				$minmax_array[] = array_merge($ra);
		}
		
		foreach($_SESSION['almacenes'] as $k => $v)
		{
			$dato_min = 0;
			$dato_max = 0;
			if(isset($_GET['modificar']))
			{	
				foreach($minmax_array as $arra_key => $array_valores)
				{
					if($v['id'] == $array_valores['almacen'])
					{
						$dato_min = $array_valores['min'];
						$dato_max = $array_valores['max'];
						$status = $array_valores['status'];
						break;
					}
				}
			}
			else
				$status = 1;
		?>
		<tr>
			<td><b><?=$v['descripcion']?></b></td>
			<td><input name="min[<?=$v['id']?>]" type="text"  onBlur="numero(this)" class="minmax_input" value="<?=$dato_min?>" /></td>
			<td><input name="max[<?=$v['id']?>]" type="text" onBlur="numero(this)" class="minmax_input" value="<?=$dato_max?>" /></td>
			<td style="text-align:center"><input type="checkbox" name="activo[<?=$v['id']?>]" <?=checked($status,0)?> /></td>
		</tr>
		<?php
		}
		?>
	</table>
	
	<center class="margin-top10"><button type="submit">Registrar</button></center>
  <input type="hidden" name="id_producto" value="<?=$_GET['modificar']?>" />
</form>
