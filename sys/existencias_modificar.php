<?php
set_time_limit(0);

if(!function_exists("money")){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
}

if(isset($_GET['actualizar_existencia'])){
	$s = "SELECT cantidad FROM existencias WHERE id_existencia = '{$_GET['id_existencia']}'";
	$q = mysql_query($s);
	$r = mysql_fetch_assoc($q);
	if($r['cantidad'] == $_GET['cantidad_vieja']){
		$s1 = "UPDATE existencias SET cantidad = '{$_GET['cantidad_nueva']}' WHERE id_existencia = '{$_GET['id_existencia']}'";
		if(mysql_query($s1)){
			echo "modificado";
		}
		else{
			echo "error";
		}
	}
	else{
		echo $r['cantidad'];
	}
	exit();
}

if(isset($_POST[crear_registros])){
	if(count($_POST[id_producto])>0){
		for($i=0; $i<count($_POST[id_producto]); $i++){
			if(strlen($_POST[id_producto][$i])>0 && $_POST[cantidad][$i]>0 && $_POST[control] != "~~~Campo de Control~~~"){
        $s = "SELECT id_existencia FROM existencias WHERE id_almacen = '{$_POST[id_almacen]}' AND id_producto = '{$_POST[id_producto][$i]}' AND lote = '{$_POST[lote][$i]}'";
        $q = mysql_query($s);
        if(mysql_num_rows($q)>0){
          $str = "UPDATE existencias SET cantidad = cantidad + {$_POST[cantidad][$i]} WHERE id_almacen = '{$_POST[id_almacen]}' AND id_producto = '{$_POST[id_producto][$i]}' AND lote = '{$_POST[lote][$i]}'";
        } else {
          $str = "INSERT INTO existencias VALUES (NULL,'{$_POST['id_almacen']}','{$_POST['id_producto'][$i]}','{$_POST[cantidad][$i]}','{$_POST['lote'][$i]}')";
        }
				//echo $str;
        mysql_query($str) or die (mysql_error());
  		}
    }
	}
}

if(isset($_GET['listar'])){
	$strSQL = "SELECT e. * , p.descripcion, p.codigo_barras 
				FROM existencias e
				INNER JOIN productos p ON p.id_producto = e.id_producto 
				WHERE e.id_almacen = '{$_GET['id_almacen']}'
				ORDER BY p.descripcion, p.codigo_barras";
	//echo nl2br($strSQL);
	$reg = mysql_query($strSQL) or die (mysql_error());
}
titleset("Actualizaci&oacute;n de Existencias");
?>
<script language="javascript" type="text/javascript">
function actualizar_cantidad(id_existencia,vieja_ex,producto,lote){
	var nueva_ex = document.getElementById("cantidad"+id_existencia).value;
	var url = "existencias_modificar.php?actualizar_existencia&id_existencia="+id_existencia+"&cantidad_nueva="+nueva_ex+"&cantidad_vieja="+vieja_ex;
	var r = procesar(url);
	if(r = "modificado"){
		alert("Existencia actualizada con éxito.\nProducto: '"+producto+"'\nLote: '"+lote+"'\nNueva cantidad: "+nueva_ex);
		document.getElementById("cantidad"+id_existencia).style.color="red";
	}
	if(r == "error"){
		alert("Ha ocurrido un error y esta existencia no pudo ser actualizada."+r);
		document.getElementById("cantidad"+id_existencia).value = vieja_existencia;
	}
	if(r != "modificado" && r != "error"){
		alert("Ha ocurrido un cambio en el registro de existencias.\nPudo deberse a una compra, facturación, venta o cualquier otra acción que modifique los registros de existencias.\nEl nuevo valor en la base de datos es:\n"+r);
		document.getElementById("cantidad"+id_existencia).value = r;		
	}
}
var n=0;
var num=n;
function AgregarFila(){
  n++;
  var num = n;
	if(n%2 == 0){
		var color = "tr_list_0";
	} else {
		var color = "tr_list_1";
	}
	
  var tbody = document.getElementById("tabla_productos").getElementsByTagName("tbody")[0];
  var row = document.createElement("tr");
	row.id = "tr_"+num;
  row.setAttribute("class",color);
	row.onmouseover = function(){this.setAttribute('class','tr_list_over');}
	row.onmouseout = function(){this.setAttribute('class',color);}
  
  //Búsqueda
  celda = document.createElement("td");
  buscar = document.createElement("a");
  buscar.innerHTML = "<img src='imagenes/search.png' />";
  buscar.id = "buscar"+num;
  buscar.href = "productos_pick.php?compra=1&n="+num;
	buscar.onclick = function(){return hs.htmlExpand(this,{objectType:'iframe',headingText:'Buscador de Productos',minWidth:700,height:650,preserveContent:true,cacheAjax:true})};
  celda.appendChild(buscar);
	
  //Control (Oculto) Sirve para determinar si el campo "Búsqueda" ha cambiado despues de "onBlur"
  control = document.createElement("input");
  control.type = "hidden";
  control.name = "control[]";
  control.id = "control"+num;
	control.value = "~~~Campo de Control~~~";
  celda.appendChild(control);
	
  //Id_producto (Oculto)
  id_producto = document.createElement("input");
  id_producto.type = "hidden";
  id_producto.name = "id_producto[]";
  id_producto.id = "id_producto"+num;
  celda.appendChild(id_producto);
  
  row.appendChild(celda); //Esta celda comparte "Búsqueda" , "ID_producto" y "Valor_inicial"

  //Barras
  celda = document.createElement("td");
  barras = document.createElement("input");
  barras.type = "text";
  barras.name = "barras[]";
  barras.id = "barras"+num;
  barras.size = 18;
  barras.onblur = function(){buscar_datos(this,num,"barras");}
  celda.appendChild(barras);
  row.appendChild(celda);
  
  //Descripcion
  celda = document.createElement("td");
  descripcion = document.createElement("select");
  descripcion.name = "descripcion[]";
  descripcion.id = "descripcion"+num;
  descripcion.onchange = function(){actualizar_campos(this,num);}
	descripcion.options[0] = new Option("Escriba un código de barras.");
	celda.appendChild(descripcion);
  row.appendChild(celda);
  
  //Lote
  celda = document.createElement("td");
  lote = document.createElement("input");
  lote.name = "lote[]";
  lote.id = "lote"+num;
	lote.size = 10;
  celda.appendChild(lote);
  row.appendChild(celda);
  
  //Cantidad
  celda = document.createElement("td");
  celda.align = "center";
  cantidad = document.createElement("input");
  cantidad.type = "text";
  cantidad.name = "cantidad[]";
  cantidad.id = "cantidad"+num;
  cantidad.size = 5;
	cantidad.value= 0;
  cantidad.onblur = function(){numero(this,3); AgregarFilaValida(this);}
  celda.appendChild(cantidad);
  row.appendChild(celda);

  //Eliminar Fila
	celda = document.createElement("td"); 
	celda.align = "center";
	eliminar = document.createElement("img"); 
	eliminar.src = "imagenes/deleteX.png";
	eliminar.setAttribute("class", "manita");
	eliminar.onclick = function(){borrar(this);}
  celda.appendChild(eliminar);
  row.appendChild(celda);

	tbody.appendChild(row);
	if(num>1){
		barras.focus();
		barras.select();
	}
}

function AgregarFilaValida(obj){
	var tbody = document.getElementById("tabla_productos").getElementsByTagName("tbody")[0];
	var filas = tbody.rows.length-3; // Le restamos 3 porque no tomamos en cuenta los encabezados
	while(obj.tagName != "TR"){
		obj = obj.parentNode;
		if(obj.id == "tr_"+filas){
			AgregarFila();
		}
	}
}

function borrar(obj) {
	var tbody = document.getElementById("tabla_productos").getElementsByTagName("tbody")[0];
	if(tbody.rows.length > 4){
		while (obj.tagName != 'TR') 
			obj = obj.parentNode;
		for (i=1; ele=tbody.getElementsByTagName('tr')[i]; i++)
			if (ele==obj) num=i;
		tbody.deleteRow(num);
		for (i=1; ele=tbody.getElementsByTagName('tr')[i]; i++)
			ele.id = "tr_"+i;
	}
	return false;
}

function ajax_query_barras(data,field){ //Petición desde el campo de código de barras
	var url = "ajax_tools.php?ajax_producto="+data+"&field="+field+"&ident=codigo_barras&compra";
	var r = procesar(url);
	return r;
}

function ajax_query_id_producto(data,field){ //Petición desde HighSlide
	var url = "ajax_tools.php?ajax_producto="+data+"&field="+field+"&ident=id_producto&compra";
	var r = procesar(url);
	return r;
}

function buscar_datos_pop(num){
	var id_producto = document.getElementById("id_producto"+num);
	buscar_datos(document.getElementById("id_producto"+num),num,"id_producto")
}

function buscar_datos(obj,num,tipo){
	var control = document.getElementById("control"+num);
	var id_producto = document.getElementById("id_producto"+num);
	var descripcion = document.getElementById("descripcion"+num);
	var lote = document.getElementById("lote"+num);
	var cantidad = document.getElementById("cantidad"+num);
  var barras = document.getElementById("barras"+num);
	if(obj.value.length > 0 && control.value != obj.value){
    if(tipo == "barras"){ //Petición desde el campo de código de barras
      var ajax_id_producto = ajax_query_barras(obj.value,"id_producto");
    } else { //Petición desde HighSlide
      var ajax_id_producto = ajax_query_id_producto(obj.value,"id_producto");
    }
		if(ajax_id_producto != "NULO"){
			id_producto.value = ajax_id_producto.split("~")[0];
			control.value = obj.value;
			descripcion.length = 0;
      if(tipo == "barras"){ //Petición desde el campo de código de barras
        var descripciones = ajax_query_barras(obj.value,"descripcion").split("~");
        control.value = obj.value;
      } else { //Petición desde HighSlide
        var descripciones = ajax_query_id_producto(obj.value,"descripcion").split("~");
        codigo_barras = ajax_query_id_producto(obj.value,"codigo_barras").split("~");
        barras.value = codigo_barras;
        control.value = codigo_barras;
      }
			for(i=0; descripciones.length>i; i++){
				descripcion.options[i] = new Option(descripciones[i],ajax_id_producto);
			}
			obj.select();
		} else {
			alert("No se pudo encontrar este producto.\n"+obj.value);
			control.value = "~~~Campo de Control~~~";
			id_producto.value = "";
			descripcion.length = 0;
			descripcion.options[0] = new Option("Escriba un código de barras.");
			lote.value = "";
			cantidad = 0;
		}
	}
	if(obj.value.length == 0){
		control.value = "~~~Campo de Control~~~";
		id_producto.value = "";
		descripcion.length = 0;
		descripcion.options[0] = new Option("Escriba un código de barras.");
		lote.value = "";
		cantidad = 0;
	}
}

function actualizar_campos(obj,num){
	document.getElementById("id_producto"+num).value = obj.options[obj.selectedIndex].value.split("~")[obj.selectedIndex];
}

function gotothis(obj){
	window.location = "?section=existencias_modificar&listar&id_almacen="+obj.options[obj.selectedIndex].value;
}

function resetear(num){
	document.getElementById("lote"+num).value = "";
	document.getElementById("cantidad"+num).value = "0";
  document.getElementById("descripcion"+num).length = 0;
  document.getElementById("descripcion"+num).options[0] = new Option("Escriba un código de barras.");
}
</script>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista">
  <tr>
    <th>Almac&eacute;n</th>
  </tr>
  <tr>
    <td><select name="id_almacen2" id="id_almacen2" onchange="gotothis(this);">
      <option value="0">Seleccione un Almacén</option>
      <?php
foreach($_SESSION[almacenes] as $k => $v){
	echo "<option value=\"{$v[id]}\" ".selected($_GET['id_almacen'],$v[id]).">{$v[descripcion]}</option>\r\n";
}
?>
    </select></td>
  </tr>
</table>
<?php if(isset($_GET[listar]) && $_GET['id_almacen'] != "0"){
if(@mysql_num_rows($reg)>0){
?>
<br />
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" style="<?=$display?>">
	<caption id="aviso">
	Para <b>ACTUALIZAR</b> las existencias, haga click en <img src="imagenes/update.png" style="margin-bottom:-3px;"/> a un lado de cantidad.
	</caption>
	<tr>
		<th>C&oacute;digo Barras</th>
		<th>Descripci&oacute;n</th>
		<th>Lote</th>
		<th>Cantidad</th>
	</tr>
<?php if(@mysql_num_rows($reg) > 0){
	$i=0; while($r = mysql_fetch_assoc($reg)){ if($i%2 == 0) $class = "tr_list_0"; else $class = "tr_list_1"; $i++;?>
	<tr id="tr_<?=$r['clave']?>" class="<?=$class?>" onmouseover="this.setAttribute('class', 'tr_list_over');" onmouseout="this.setAttribute('class', '<?=$class?>');">
		<td><?=$r['codigo_barras']?></td>
		<td><b><?=$r['descripcion']?></b></td>
		<td style="text-align:center"><?=$r['lote']?></td>
		<td style="text-align:center">
			<input type="hidden" name="id_existencia[]" value="<?=$r['id_existencia']?>" />
			<input name="mcantidad[]"
			type="text"
			class="disable_1"    
			id="cantidad<?=$r['id_existencia']?>"
			value="<?=$r['cantidad']?>"
			size="8"
			onblur="numero(this,3);"
			style="font-size:10px; text-align:right"/>
			<img src="imagenes/update.png" style="margin-bottom:-3px; cursor:pointer"
      onclick="actualizar_cantidad(<?=$r['id_existencia']?>,<?=$r['cantidad']?>,'<?=htmlentities(addslashes($r['descripcion']))?>','<?=$r['lote']?>');"/>
		</td>
	</tr>
<?php
		}
	}
}
?>
</table>  
<form action="" method="post" name="form1" id="form1" style="margin-top:16px">
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="tabla_productos">
	<tr><th colspan="6">Crear nuevos registros de Existencias</th></tr>
	<tr>
	  <td colspan="6" id="aviso" style="text-align:center"><b>Nota:</b> Este es un sistema de actualizaci&oacute;n masiva.<br />
	    Los datos que coincidan con registros de existencia actuales ser&aacute;n reemplazados.</td></tr>
  <tr>
  	<th>&nbsp;</th>
  	<th>Código de Barras</th>
    <th>Descripción</th>
    <th>Lote</th>
    <th>Cantidad</th>
    <th>&nbsp;</th>
  </tr>
  <tbody>
	</tbody>
<tr>
	<td colspan="6" style="text-align:center" bgcolor="#CCCCCC">
	  <input type="submit" name="crear_registros" value="Agregar Nuevos Productos"/>
  	<input name="id_almacen" type="hidden" id="id_almacen" value="<?=$_GET['id_almacen']; ?>" />
  </td>
</tr>
</table>
</form>
<?php
}
if(isset($_GET[listar]) && $_GET['id_almacen'] != "0"){
?>
<script language="javascript" type="text/javascript">
	AgregarFila();
</script>
<?php
}
?>