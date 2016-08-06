<?php
if(isset($_POST[id_producto1])){
	$lote1 = explode(":::",$_POST[lote1]);
	$_POST[lote1] = $lote1[0];
	$s = "SELECT cantidad-{$_POST[merma]} c FROM existencias WHERE id_almacen = {$_POST[almacen]} AND id_producto = {$_POST[id_producto1]} AND lote = '{$_POST[lote1]}'";
	$q = mysql_query($s) or die ($s.mysql_error());
	$r = mysql_fetch_assoc($q);
	if($r[c] >= 0){ 	//Probar errores
		$s = "UPDATE existencias SET cantidad=cantidad-{$_POST[merma]} WHERE id_almacen = {$_POST[almacen]} AND id_producto = {$_POST[id_producto1]} AND lote = '{$_POST[lote1]}'";
		$q = mysql_query($s) or die (mysql_error());
		
		$s = "INSERT INTO mermas VALUES (
					NULL,NOW(),{$_SESSION[id_usuario]},{$_POST[almacen]},{$_POST[id_producto1]},'{$_POST[lote1]}',{$_POST[merma]},{$_POST[merma]},0
					)";
		$q = mysql_query($s) or die (mysql_error());
		relocation("?section=mermas_detalle&id=".mysql_insert_id());
	}
}

//Inicia configuración
titleset("Registro de Mermas");
//Fin de configuración

//Nombre del almacén de origen
$s = "SELECT descripcion FROM almacenes WHERE id_almacen='{$_GET[a]}'";
$q = mysql_query($s) or die (mysql_error());
$r = mysql_fetch_assoc($q);
$almacen = $r[descripcion];
?>
<script language="JavaScript" type="text/javascript">
function resetear(num){
  document.getElementById("control"+num).value = "~~~Campo de Control~~~";
  document.getElementById("id_producto"+num).value = "";
  document.getElementById("descripcion"+num).length = 0;
  document.getElementById("descripcion"+num).options[0] = new Option("Escriba un código de barras.");
	if(num == 1){
		document.getElementById("lote"+num).length = 0;
		document.getElementById("lote"+num).options[0] = new Option("Sin lote");
		document.getElementById("disponible"+num).value = "0";
	}
}

function ajax_query_barras(data,field){ //Petición desde el campo de código de barras
	var url = "ajax_tools.php?ajax_producto="+data+"&field="+field+"&ident=codigo_barras";
	var r = procesar(url);
	return r;
}

function ajax_query_id_producto(data,field){ //Petición desde HighSlide
	var url = "ajax_tools.php?ajax_producto="+data+"&field="+field+"&ident=id_producto";
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
  var barras = document.getElementById("barras"+num);
	if(obj.value.length > 0 && control.value != obj.value){
    if(tipo == "barras"){ //Petición desde el campo de código de barras
      var ajax_id_producto = ajax_query_barras(obj.value,"id_producto");
    } else { //Petición desde HighSlide
      var ajax_id_producto = ajax_query_id_producto(obj.value,"id_producto");
    }
		if(ajax_id_producto != "NULO"){
			id_producto.value = ajax_id_producto.split("~")[0];
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
			if(num == 1){
      	loteDetect(num);
			}
 		} else {
			alert("No se pudo encontrar este producto.\n"+obj.value);
      resetear(num);
		}
	}
  if(obj.value.length == 0) {
    resetear(num);
	}
}

function loteDetect(id){
  var id_producto = document.getElementById("id_producto"+id);
  var lote = document.getElementById("lote"+id);
  var disponible = document.getElementById("disponible"+id);
	var almacen = <?=$_GET[a]?>;
  var url = "ajax_tools.php?lotes="+id_producto.value+"&ad1="+almacen;
  var rr = procesar(url);
  if(rr != "NULO"){ //Tiene existencias en este almacén
    var r = rr.split("|||");
    for(i=0; r.length>i; i++){
      lote.options[i] = new Option(r[i].split(":::")[0],r[i]);
    }
    disponible.value = r[0].split(":::")[1];
  } else { //Este producto no tiene existencias en este almacén
		lote.length = 0;
		lote.options[0] = new Option("Sin Existencias",0);
    disponible.value = 0;
  }
}

function actualizar_campos(obj,num){
	document.getElementById("id_producto"+num).value = obj.options[obj.selectedIndex].value.split("~")[obj.selectedIndex];
	if(num == 1){
		loteDetect(num);
	}
}

function actualizar_campos_lote(obj,num){
  var disponible = document.getElementById("disponible"+num);
  var existencia = obj.options[obj.selectedIndex].value.split(":::")[1];
  disponible.value = existencia;
}

function items(){
	var disponible = parseFloat(document.getElementById("disponible1").value);
	var merma = parseFloat(document.getElementById("merma").value);
	if(merma == 0){
		alert("Determine la cantidad de merma.");
		document.getElementById("merma").focus();
		return false;
	} else if(disponible < merma){
		alert("No hay existencia suficiente.\nDisponible: "+disponible+"\nSolicitado: "+merma);
		document.getElementById("merma").focus();
		return false;
	} else {
		return true;
	}
}
</script>

<form action="" method="post" name="form1" id="form1" onsubmit="return items();">
  <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla">
    <tr>
      <td id="aviso" style="text-align:center"><b>Almac&eacute;n:</b>
        <?=$almacen?></td>
    </tr>
    <tr>
      <th style="text-align:center">Elegir producto con mermas</th>
    </tr>
    <tr bgcolor="#CCCCCC">
      <td>
      <span id="paso1">
      <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="tabla_productos1">
          <tr>
            <th>&nbsp;</th>
            <th>C&oacute;digo de Barras</th>
            <th>Descripci&oacute;n</th>
            <th>Lote</th>
            <th>Disponible</th>
            <th>Merma</th>
          </tr>
          <tr bgcolor="#FFFFFF">
            <td><a
              href="productos_pick.php?n=1"
              onclick="return hs.htmlExpand(this,{objectType:'iframe',headingText:'Buscador de Productos',minWidth:600,height:600,preserveContent:false,cacheAjax:false})"
              > <img src="imagenes/search.png" /> </a>
              <input name="control1" type="hidden" id="control1" value="" />
              <input name="id_producto1" type="hidden" id="id_producto1" value="" /></td>
            <td><input type="text" name="barras" id="barras1" onblur="buscar_datos(this,1,'barras');" /></td>
            <td><select type="text" name="descripcion" id="descripcion1" onchange="actualizar_campos(this,1);">
                <option>Escriba un código de barras.</option>
              </select></td>
            <td><select id="lote1" name="lote1" onchange="actualizar_campos_lote(this,1);">
                <option>Sin lote</option>
              </select></td>
            <td><input name="disponible1" id="disponible1" value="0" size="5" readonly="readonly"  /></td>
            <td><input name="merma" id="merma" value="0" size="5" onblur="numero(this,3)" /></td>
          </tr>
        </table>
        <center>
          <br />
          <input type="hidden" name="almacen" id="almacen" value="<?=$_GET[a]?>" />
          <input type="submit" value="Registrar Merma"  />
        </center>
        </span>
        <span id="paso1_data">
        </span>
        </td>
    </tr>
  </table>
</form>
