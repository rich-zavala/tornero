<?php
$com_text = "Use este campo para escribir un complemento de la descripción del producto.";
if($_POST[crear_registros] == "Registrar Venta")
{
	if($_POST[tipo_venta]==0){ //Es nota de venta
		$_POST[tipo_venta]="n";
	} else { //Es factura
		$_POST[tipo_venta]="f";
	}
	
	if($_POST[tipo_venta]=="n"){ //Es nota de venta
		$_POST[cliente] = 0;
		$_POST[cliente_data] = "";
		$_POST[pago] = "contado";
		$_POST[leyenda] = "";
		
		//Crear nuevo folio de nota
		mysql_query("INSERT INTO notas_consecutivo () VALUES ()");
		$_n = mysql_insert_id();
		$_POST[folio] = "NOTA ".$_n;
	}
	
  //Limpiar información
  $data = array();
  for($i=0;count($_POST[id_producto])>$i;$i++){
		if(strlen($_POST[descripcion_especial][$i])==0){ //Es producto
			if(strlen($_POST[id_producto][$i])>0 && $_POST[cantidad][$i]>0 && $_POST[control] != "~~~Campo de Control~~~"){ //Tiene cantidad y no es nulo
				if($_POST[disponible][$i]>=$_POST[cantidad][$i]){ //Verificar cantidad disponible
					$lote_this = explode(":::",$_POST[lote][$i]);
					if(strlen($lote_this[0])==NULL){ //Tomar en cuenta los lotes sin caracteres
						$lote_this[0] = "";
					}
					$data[id_producto][] = $_POST[id_producto][$i];
					$data[cantidad][] = $_POST[cantidad][$i];
					$data[unidades][] = $_POST[unidades][$i];
					$data[cantidad_esp][] = $_POST[cantidad_esp][$i];
					$data[descuento][] = $_POST[descuento][$i];
					$data[importe][] = $_POST[importe][$i];
					$data[iva][] = $_POST[iva][$i];
					$data[precio][] = $_POST[precio][$i];
					$data[lote][] = $lote_this[0];
					$data[descripcion_especial][] = 0;
					
					if($_POST[complemento][$i] != $com_text && $_POST[complemento][$i] != "")
					{
						$data[complemento][] = $_POST[complemento][$i];
					}
					else
					{
						$data[complemento][] = "";
					}
					
				}
			}
		}
		else
		{
			$data[descripcion_especial][] = $_POST[descripcion_especial][$i];
			$data[id_producto][] = "0";
			$data[cantidad][] = $_POST[cantidad][$i];
			$data[unidades][] = $_POST[unidades][$i];
			$data[descuento][] = $_POST[descuento][$i];
			$data[importe][] = $_POST[importe][$i];
			$data[iva][] = $_POST[iva][$i];
			$data[precio][] = $_POST[precio][$i];
			$data[lote][] = "No aplica";
			
			if($_POST[complemento][$i] != $com_text && $_POST[complemento][$i] != "")
			{
				$data[complemento][] = $_POST[complemento][$i];
			}
			else
			{
				$data[complemento][] = "";
			}
		}		
  }
	
  $data[cliente] = $_POST[cliente];
	$data[moneda] = $_POST[moneda];
  $data[cliente_datos] = $_POST[cliente_datos];
	$data[fecha] = $_POST[fecha];
	$data[folio] = $_POST[folio];
	$data[id_almacen] = $_POST[id_almacen];
	$data[leyenda] = $_POST[leyenda];
	$data[pago] = $_POST[pago];
	$data[precios] = $_POST[precios];
  $data[tipo_venta] = $_POST[tipo_venta];
  $data[total_input] = $_POST[total_input];
  $data[metodoDePago] = $_POST[metodoDePago];
	
	//cambio aplicado para la FE (Facturación electrónica)
	$sql_fe ="SELECT ncsd, anoa, noa, serie FROM vars LIMIT 1";
	$query_fe = mysql_query($sql_fe);
	$r_fe = mysql_fetch_assoc($query_fe);
	
	if(strlen(trim($_POST[NumCtaPago])) == 0)
	{
		$s = "SELECT NumCtaPago FROM clientes WHERE clave = '{$_POST[cliente]}'";
		$q = mysql_query($s) or die (mysql_error());
		$r = mysql_fetch_assoc($q);
		$data[NumCtaPago] = $r[NumCtaPago];
	}
	else
	{
		$data[NumCtaPago] = $_POST[NumCtaPago];
	}
	
  unset($_POST);
  $_POST = $data;
  // existencias($_POST);
	
  //Indexar esta venta
  $s = "INSERT INTO facturas
		(folio, leyenda, id_facturista, id_almacen, fecha_factura,
		licitacion, id_cliente, datos_cliente, tipo, moneda, anoap, noap, nocertificado, serie,
		metodoDePago, NumCtaPago)
    VALUES (
    '{$_POST[folio]}',
    '{$_POST[leyenda]}',
    '{$_SESSION[id_usuario]}',
    '{$_POST[id_almacen]}',
    '{$_POST[fecha]}',
    #NOW(),
    '{$_POST[precios]}',
    '{$_POST[cliente]}',
    '{$_POST[cliente_datos]}',
    #'{$_POST[total_input]}',
    '{$_POST[tipo_venta]}',
		'{$_POST[moneda]}',
		'{$r_fe[anoa]}',
		'{$r_fe[noa]}',
		'{$r_fe[ncsd]}',
		'{$r_fe[serie]}',
		'{$_POST[metodoDePago]}',
		'{$_POST[NumCtaPago]}')";
	//echo $s; exit;
  mysql_query($s) or die (nl2br($s)."<br>".mysql_error());

  //Registrar productos de esta factura
  foreach($_POST[importe] as $k => $v)
	{
		if(strlen($_POST[descripcion_especial][$k])>1){
			$especial = mysql_real_escape_string($_POST[descripcion_especial][$k]);
		} else {
			$especial = 0;
		}

		if(strlen($_POST[complemento][$k])>1){
			$complemento = mysql_real_escape_string($_POST[complemento][$k]);
		} else {
			$complemento = 0;
		}
		if(!isset($_POST[cantidad_esp][$k])){	
			$_POST[cantidad_esp][$k] = 0;
		}
		
		
		#$importe = $_POST[cantidad][$k]*($_POST[precio][$k]+($_POST[precio][$k]*($_POST[iva][$k]/100)));
    $s = "INSERT INTO facturas_productos 
		(folio_factura, id_producto, cantidad, lote, precio, iva, especial, complemento,
		canti_, unidad, serie, almacen, usuario)
    VALUES(
    #null, 
    '{$_POST[folio]}',
    '{$_POST[id_producto][$k]}',
    '{$_POST[cantidad][$k]}',
    '{$_POST[lote][$k]}',
    '{$_POST[precio][$k]}',
    #'{$_POST[descuento][$k]}',
    '{$_POST[iva][$k]}',
    #'$importe',
		'{$especial}',
		'{$complemento}',
  	'{$_POST[cantidad_esp][$k]}',
		'{$_POST[unidades][$k]}',
		'{$r_fe[serie]}',
    '{$_POST[id_almacen]}',
		'{$_SESSION[id_usuario]}'
		 )";
    mysql_query($s) or die("facturas_productos: ".mysql_error());
    // echo nl2br($s);
	}
		
	//13 noviembre 2015 > La factura de contado se paga con la función
	if($_POST[pago] == "contado")
	{
		$s = "CALL facturaPagoContado('{$_POST[folio]}', '{$r_fe[serie]}')";
		$q = mysql_query($s) or die (mysql_error());
	}
	
	//Corregir error de centavitos de diferencia
	
	/*$s = "UPDATE facturas SET importe = {$importe_real} WHERE folio = '{$_POST[folio]}' AND serie='{$r_fe[serie]}'";
	mysql_query($s) or die($s." - ".mysql_error());
	$s = "UPDATE facturas_productos SET importe = ROUND(cantidad*precio,2) + ROUND(cantidad*(precio*(iva/100)),2) WHERE folio_factura = '{$_POST[folio]}' AND serie='{$r_fe[serie]}'";
	mysql_query($s) or die($s." - ".mysql_error());*/
	// exit;
  relocation("?section=ventas_detalle&folio={$_POST[folio]}&serie={$r_fe[serie]}");
}

//Re-ajuste de optimización por Rich del presente 14 nov 2015
$sql_cliente = "SELECT * FROM clientes ORDER BY nombre ASC";
$query_cliente = mysql_query($sql_cliente) or die (mysql_error());

$sql_fes="SELECT serie, folioi, foliof FROM vars LIMIT 1";
$query_fes = mysql_query($sql_fes);
$r_fes = mysql_fetch_assoc($query_fes);		


$sf = "SELECT folio FROM facturas ORDER BY folio+1 DESC LIMIT 1";
$qf = mysql_query($sf);
$rf = mysql_fetch_assoc($qf);
if($rf[folio] >= $r_fes[foliof])
{
	$proximo_folio ="";
	$seagoto=1;
}
else
{
	if($rf[folio] == "")
	{
		 $proximo_folio=$r_fes[folioi];
	}
	else{		
		$proximo_folio =number_format(($rf[folio]+1),0,"","");
	}
}

//Inicia configuración
titleset("Registro de Venta");
//Fin de configuración
?>
<script language="javascript" type="text/javascript">
var n = 0;
var com_text = "<?=$com_text?>";
function AgregarFila(){
	var	almacen = document.getElementById("id_almacen").options[document.getElementById("id_almacen").selectedIndex].value
	
  n++;
  var num = n;
	if(n%2 == 0)
	{
		var color = "tr_list_0";
	}
	else
	{
		var color = "tr_list_1";
	}
	
  var tbody = document.getElementById("tabla_productos").getElementsByTagName("tbody")[0];
  var row = document.createElement("tr");
	row.id = "tr_"+num;
	$(row).addClass(color).mouseover(function(){$(row).attr('class','tr_list_over')}).mouseout(function(){$(row).attr('class',color)});
	  
  //Switch
  celda = document.createElement("td");
	celda.align = "center";
  swi = document.createElement("input");
	swi.type = "checkbox";
	swi.name = "switch[]";
	swi.value = 1;
	swi.id = "swi"+num;
	swi.onclick = function(){cambiar_especial(this,num);}
  celda.appendChild(swi);
	row.appendChild(celda);
	
  //Búsqueda
  celda = document.createElement("td");
  buscar = document.createElement("a");
  buscar.innerHTML = "<img src='imagenes/search.png' />";
  buscar.id = "buscar"+num;
  buscar.href = "productos_pick.php?n="+num+"&almacen="+almacen+"&venta";
	buscar.onclick = function(){return hs.htmlExpand(this,{objectType:'iframe',headingText:'Buscador de Productos',minWidth:600,height:600,preserveContent:false,cacheAjax:false})};
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
	
	 //unidades
  celda = document.createElement("td");
  unidades = document.createElement("input");
  unidades.type = "text";
  unidades.name = "unidades[]";
  unidades.id = "unidades"+num;
  unidades.size = 10; 
  celda.appendChild(unidades);
  row.appendChild(celda);
	
  
  //Descripcion
  celda = document.createElement("td");
  descripcion = document.createElement("select");
  descripcion.name = "descripcion[]";
  descripcion.id = "descripcion"+num;
  descripcion.onchange = function(){actualizar_campos(this,num);}
	descripcion.options[0] = new Option("Escriba un código de barras.");
	celda.appendChild(descripcion);
		
  descripcion_especial = document.createElement("input");
	descripcion_especial.type = "text";
	descripcion_especial.style.display = "none";
  descripcion_especial.name = "descripcion_especial[]";
  descripcion_especial.id = "descripcion_especial"+num;
	descripcion_especial.style.width = "200px";
	celda.appendChild(descripcion_especial);

	br_complemento = document.createElement("br");
	celda.appendChild(br_complemento);
	
  complemento = document.createElement("textarea");
	$(complemento).css("width","200px").css("height","39px").attr("id","complemento"+num).attr("name","complemento[]").attr("disabled",true).val(com_text).focus(function(){if($(this).val() == com_text){$(this).val("");}});
	celda.appendChild(complemento);

  row.appendChild(celda);
	
  //Lote
  celda = document.createElement("td");
  lote = document.createElement("select");
  lote.name = "lote[]";
  lote.id = "lote"+num;
  lote.onchange = function(){actualizar_campos_lote(this,num);}
	lote.options[0] = new Option("Sin lote");
	celda.appendChild(lote);
  row.appendChild(celda);
	
  //Disponible
  celda = document.createElement("td");
  celda.align = "center";
  disponible = document.createElement("input");
  disponible.type = "text";
  disponible.name = "disponible[]";
  disponible.id = "disponible"+num;
  disponible.size = 8;
	disponible.value= "0.000";
	disponible.readOnly = true;
  celda.appendChild(disponible);
  row.appendChild(celda);

  //Cantidad
  celda = document.createElement("td");
  celda.align = "center";
  cantidad = document.createElement("input");
  cantidad.type = "text";
  cantidad.name = "cantidad[]";
  cantidad.id = "cantidad"+num;
  cantidad.size = 8;
	cantidad.value= "0.000";
	cantidad.readOnly = true;
  cantidad.onblur = function(){numero(this,3); CompararExistencias(this,num); sumar();}
  celda.appendChild(cantidad);	
	//empieza BY chris 11-21-2010
	br_complemento = document.createElement("br");
	celda.appendChild(br_complemento);	
	 //Cantidad especial
  cantidadesp = document.createElement("input");
  cantidadesp.type = "text";
  cantidadesp.name = "cantidad_esp[]";
  cantidadesp.id = "cantidadesp"+num;
  cantidadesp.size = 8;
	cantidadesp.value= "0.000";
	cantidadesp.readOnly = true;
  cantidadesp.onblur = function(){numero(this,3); CompararExistencias(this,num);}
  celda.appendChild(cantidadesp);
	//termina 
	 row.appendChild(celda);
  
	//Precio
  celda = document.createElement("td");
  celda.align = "center";
  precio = document.createElement("input");
  precio.type = "text";
  precio.name = "precio[]";
  precio.id = "precio"+num;
  precio.size = 8;
	precio.value= "0.00";
	precio.readOnly = false;
	precio.title = "MN";
  precio.onblur = function(){numero(this,2); f_pmv(this,num); sumar();}
  celda.appendChild(precio);
	
  //PMV
  celda.align = "center";
  pmv = document.createElement("input");
  pmv.type = "hidden";
  pmv.name = "pmv[]";
  pmv.id = "pmv"+num;
	pmv.value= 0;
  celda.appendChild(pmv);
  row.appendChild(celda);
	
	//Descuento
  celda = document.createElement("td");
  celda.align = "center";
	celda.style.display = "none";
  descuento = document.createElement("input");
  descuento.type = "text";
  descuento.name = "descuento[]";
  descuento.id = "descuento"+num;
  descuento.size = 8;
	descuento.value= "0.00";
	descuento.readOnly = true;
  descuento.onblur = function(){numero(this,2); sumar();}
  celda.appendChild(descuento);
  row.appendChild(celda);
	
	//IVA
  celda = document.createElement("td");
  celda.align = "center";
  iva = document.createElement("input");
  iva.type = "text";
  iva.name = "iva[]";
  iva.id = "iva"+num;
  iva.size = 8;
	iva.value= "0.00";
	iva.readOnly = true;
  iva.onblur = function(){numero(this,2); sumar(); AgregarFilaValida(this);}
  celda.appendChild(iva);
  row.appendChild(celda);
	
  //Eliminar Fila
	celda = document.createElement("td"); 
	celda.align = "center";
	eliminar = document.createElement("img"); 
	eliminar.src = "imagenes/deleteX.png";
	eliminar.setAttribute("class", "manita");
	eliminar.onclick = function(){borrar(this);}
	eliminar.id = "eliminar"+num;
  celda.appendChild(eliminar);
  row.appendChild(celda);

	tbody.appendChild(row);
	//if(num>1){
		//$(barras).focus();
		//setTimeout(function(){$(barras).focus()},100);
		evitar();
	//}
}


function cambiar_especial(obj,n){
	resetear(n);
	if(obj.checked){ // Cambiar a Especial
		document.getElementById("cantidad"+n).onblur = null;
		document.getElementById("precio"+n).onblur = null;
	
		$("#id_producto"+n).val("");
		//$("#id_producto"+n).val("");
		$("#complemento"+n).attr("disabled",false);
		$("#buscar"+n).css("display","none");
		$("#barras"+n).val("").css("display","none");
		//$("#unidades"+n).val("").css("display","none");
		$("#lote"+n).find('option').remove().end().append('<option>'+Math.floor(Math.random()*11)+'</option>').css("display","none");
		$("#disponible"+n).val("0.000").css("display","none");
		$("#descripcion"+n).css("display","none").find('option').remove().end().css("display","none");
		$("#descripcion_especial"+n).css("display","");
		$("#cantidad"+n).blur(function(){numero(this,3); sumar();}).attr("readonly","");
		//agregado by chris el 11-21-2010
	// 	$("#cantidadesp"+n).blur(function(){numero(this,3);}).attr("readonly","");
		$("#cantidadesp"+n).css("display","none");
		//termina
		$("#precio"+n).blur(function(){numero(this,2); sumar();});
		$("#iva"+n).attr("readonly","");
	} else {
		$("#tr_"+n).remove();
		AgregarFila();
		sumar();
	}
}

function evitar()
{
	$("#form1").find("input").bind("keypress",function(e){
		if(e.keyCode == 13) {
			return false;
		}
	});
}

function AgregarFilaValida(obj){
	var tbody = document.getElementById("tabla_productos").getElementsByTagName("tbody")[0];
	var filas = tbody.rows.length-1; // Le restamos 2 porque no tomamos en cuenta los encabezados y comienza desde 0
	var id = obj.parentNode.parentNode.id;
	var last_tr = document.getElementById("tabla_productos").getElementsByTagName("tbody")[0].rows[filas].id
	for(var i=0;filas>i++;){ //Pasar por cada fila
		var fila = document.getElementById("tabla_productos").getElementsByTagName("tbody")[0].rows[i].id;
		if(id == last_tr && fila == last_tr){
			AgregarFila();
		}
	}
}

function borrar(obj) {
	var tbody = document.getElementById("tabla_productos").getElementsByTagName("tbody")[0];
	if(tbody.rows.length > 2){
		while (obj.tagName != 'TR') 
			obj = obj.parentNode;
		for (i=1; ele=tbody.getElementsByTagName('tr')[i]; i++)
			if (ele==obj) num=i;
		tbody.deleteRow(num);
		for (i=1; ele=tbody.getElementsByTagName('tr')[i]; i++)
			ele.id = "tr_"+i;
	}
	sumar();
	return false;
}

function CompararExistencias(obj,num){
	var disponible = document.getElementById("disponible"+num).value;
	var cantidad = document.getElementById("cantidad"+num).value;
	if (parseFloat(cantidad) > parseFloat(disponible)){
		alert("Esta cantidad no está disponible en el almacén seleccionado.\nSe aplicará la cantidad máxima actual.");
		document.getElementById("cantidad"+num).value = disponible;
		document.getElementById("cantidad"+num).focus();
		document.getElementById("cantidad"+num).select();
		return false;
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
			activar(num);
      loteDetect(num);
      
      //Determinar IVA
      document.getElementById("iva"+num).value = ajax_query_id_producto(id_producto.value,"iva");
      numero(document.getElementById("iva"+num),2);
      
      //Determinar PMV
      var url = "ajax_tools.php?pmv="+id_producto.value;
      document.getElementById("pmv"+num).value = procesar(url);
			
			$("#disponible"+num).focus();
		} else {
			alert("No se pudo encontrar este producto.\n"+obj.value);
			$("#barras"+num).val("");
      resetear(num);
		}
	}
  if(obj.value.length == 0) {
    resetear(num);
	}
  //f_pmv(document.getElementById("precio"+num),num);
}

function resetear(num){
  document.getElementById("control"+num).value = "~~~Campo de Control~~~";
  document.getElementById("id_producto"+num).value = "";
	document.getElementById("unidades"+num).length = "";
  document.getElementById("descripcion"+num).length = 0;  
  document.getElementById("descripcion"+num).options[0] = new Option("Escriba un código de barras.");
  document.getElementById("lote"+num).length = 0;
  document.getElementById("lote"+num).options[0] = new Option("Sin lote");
  document.getElementById("iva"+num).value = "0.00";
  document.getElementById("precio"+num).value = "0.00";
  document.getElementById("pmv"+num).value = "0.00";
	$("#disponible"+num).val("0.00");
	$("#complemento"+num).val(com_text);
  desactivar(num);
	sumar();
}

function activar(num){
  document.getElementById("cantidad"+num).readOnly = false;
	//empieza BY chris 11-21-2010
	document.getElementById("cantidadesp"+num).readOnly = false;
	//termina
  document.getElementById("precio"+num).readOnly = false;
  document.getElementById("descuento"+num).readOnly = false;
  document.getElementById("iva"+num).readOnly = false;
	$("#complemento"+n).attr("disabled",false);
	//alert(8);
	sumar();
}

function desactivar(num){
  document.getElementById("cantidad"+num).readOnly = true;
  document.getElementById("cantidad"+num).value = "0";
	//empieza BY chris 11-21-2010
	document.getElementById("cantidadesp"+num).readOnly = true;
  document.getElementById("cantidadesp"+num).value = "0";
	//termina
  document.getElementById("precio"+num).readOnly = false;
  document.getElementById("descuento"+num).readOnly = true;
  document.getElementById("descuento"+num).value = "0.00";
  document.getElementById("iva"+num).readOnly = true;
	$("#complemento"+n).attr("disabled",true).val(com_text);
	//alert(7);
	sumar();
}

function precio_quiz(num){
	var lote = 	document.getElementById("lote"+num);
	var lote_value = lote.options[lote.selectedIndex].value;
	if(lote_value != "Sin lote")
	{
		var id_producto = document.getElementById("id_producto"+num).value;
		var precios = document.getElementById("precios"); //Selector de Lista de Precios
		var lista = precios.options[precios.selectedIndex].value;
		
		var url = "ajax_tools.php?precio&id_producto="+id_producto+"&lista="+lista;
		var r = procesar(url);
		if(r.length == 0 && lista != "_normal_")
		{
			var url = "ajax_tools.php?precio&id_producto="+id_producto+"&lista=_normal_";
			var r = procesar(url);
			
			var descripcion = document.getElementById("descripcion"+num);
			var descripcion_text = descripcion.options[descripcion.selectedIndex].text;
			
			alert("El producto \""+descripcion_text+"\" no está definido en la lista de precios \""+lista+"\".\nSe aplicará su precio público: $ "+money(parseFloat(r)))
		}
		document.getElementById("precio"+num).value = r;
	}
	else
	{
		document.getElementById("precio"+num).value = "0.00";
		document.getElementById("iva"+num).value = "0.00";
	}
}

function actualizar_campos(obj,num){
	document.getElementById("id_producto"+num).value = obj.options[obj.selectedIndex].value.split("~")[obj.selectedIndex];

  //Determinar IVA
  var iva = ajax_query_id_producto(document.getElementById("id_producto"+num).value,"iva");
  document.getElementById("iva"+num).value = iva;
  numero(document.getElementById("iva"+num),2);
	loteDetect(num);
  precio_quiz(num);
  
  //Determinar PMV
  var url = "ajax_tools.php?pmv="+id_producto.value;
  document.getElementById("pmv"+num).value = procesar(url);
  f_pmv(document.getElementById("precio"+num),num);
}

function loteDetect(id){
  var id_producto = document.getElementById("id_producto"+id);
  var lote = document.getElementById("lote"+id);
  var disponible = document.getElementById("disponible"+id);
	var almacen = document.getElementById("id_almacen");
  var url = "ajax_tools.php?lotes="+id_producto.value+"&ad1="+almacen.options[almacen.selectedIndex].value;
  var rr = procesar(url);
  if(rr != "NULO"){ //Tiene existencias en este almacén
		document.getElementById("lote"+id).length = 0;
    var r = rr.split("|||");
    for(i=0; r.length>i; i++){
      lote.options[i] = new Option(r[i].split(":::")[0],r[i]);
    }
    disponible.value = r[0].split(":::")[1];
    activar(id);
		precio_quiz(id);
  } else { //Este producto no tiene existencias en este almacén
		lote.length = 0;
		lote.options[0] = new Option("Sin Existencias",0);
		//alert("Desacivar: loteDetect 1");
    desactivar(id);
  }
  if(parseFloat(document.getElementById("disponible"+id).value) == 0){
		//alert("Desacivar: loteDetect 1");
    desactivar(id);
  }
	monedas();
}

function actualizar_campos_lote(obj,num){
  var disponible = document.getElementById("disponible"+num);
  var existencia = obj.options[obj.selectedIndex].value.split(":::")[1];
  desactivar(num);
  activar(num);
  disponible.value = existencia;
  if(parseFloat(existencia)==0){
    desactivar(num);
  }
}


function redon(valor){
		valor=valor*100;
		valor=Math.floor(valor);
		valor=valor/100;		
		return money(valor);
		}
function sumar(){
	cantidades = $("input[name='cantidad[]']");
	subtotal = 0;
	descuento = 0;
	iva = 0;
	$(cantidades).each(function(i,v){
		x = $(this).attr("id").replace("cantidad","");
	  var _cantidad_ = parseFloat($("#cantidad"+x).val());
		var _precio_ = parseFloat($("#precio"+x).val());		
		var _iva_ = round(parseFloat($("#iva"+x).val())/100,2);
		subtotal += _cantidad_*(_precio_);
		iva += (_cantidad_*_precio_)*_iva_;
	
	});
	
	$("#sub_total_span").html(money(round(subtotal,2)));
	$("#iva_span").html(money(round(iva,2)));
	total_final = subtotal + iva;	
	$("#total_span").html(money(total_final));
	$("#total_input").val(total_final);
	
	//Actualizar campos de Crédito
	var sumatoria = parseFloat($("#credito_disponible").val())-total_final;
  $("#sumatoria_span").html(money(sumatoria));
	$("#sumatoria").val(sumatoria);
}

function array_unique2(array){
    var tem_arr = new Array();
    for(i=0;i<array.length;i++){
        if(!in_array(array[i],tem_arr)){
            tem_arr[i]=array[i];
        }
    }
    return tem_arr.join(',').split(',');
}

function items(){
	return true;
}

function f_pmv(obj,id){
}

function verificar_folio(){
	var folio = document.getElementById("folio");
	var tipo_venta = document.getElementById("tipo_venta");
	
	var f_b=$("#folio").val(); 
	var s_f=$("#serie_f").val(); 	
	var url = "ajax_tools.php?folio_venta="+f_b+"&serie="+s_f+"&tipo="+tipo_venta.value;
	var r = procesar(url);
	if(r > 0){
		alert("Este folio ha sido previamente usado.\nIntente nuevamente.");
		var url = "ajax_tools.php?obtener_folio_fallido="+s_f;
	  var r = procesar(url);
	  $("#folio").val(r); 		
		folio.focus();
		folio.select();
		return false;
	} else {
		return true;
	}
}

function confirmar_tipo_venta(i){
	document.getElementById("tipo_nota").style.display = "none";
	if(document.getElementById("tipo_factura")){ document.getElementById("tipo_factura").style.display = "none"; }
	document.getElementById("tipo_venta").value = i;
	if(i == 0){ //Nota de venta
		document.getElementById("span_tipo_venta").innerHTML = "Nota de Venta";
		//Establecer folio
		var url = "ajax_tools.php?ultima_nota";
		var r = procesar(url);
		document.getElementById("folio").value = r;
		document.getElementById("folio").readOnly = true;
		document.getElementById("tabla_credito").style.display = "none";		
		document.getElementById("tr_cliente_list").style.display = "none";		
		document.getElementById("cliente_span").innerHTML = "Cliente Parcial";
		document.getElementById("tr_cliente").style.display = "none";
	} else { //Es Factura
		<?php if($_GET[moneda] == "USD"): ?>
		$("#moneda").find("option:last").attr("selected",true);
		<?php endif; ?>
	
		document.getElementById("span_tipo_venta").innerHTML = "Factura";
		document.getElementById("tr_cliente").style.display = "";
		document.getElementById("tr_pago").style.display = "";
		document.getElementById("tr_moneda").style.display = "";
		document.getElementById("tr_metodo").style.display = "";
		document.getElementById("tr_leyenda").style.display = "";
		document.getElementById("tr_cliente").style.display = "";
		document.getElementById("tr_cliente_list").style.display = "";
		document.getElementById("tr_datos_cliente").style.display = "";
		document.getElementById("tr_folio").style.display = "";
		document.getElementById("folio").focus();
		
		var cliente = document.getElementById("cliente");
		var url = "ajax_tools.php?cliente_data2="+cliente.options[cliente.selectedIndex].value;
		data = array("","","","","","","","","","");
		v = procesar(url);		
		data = v.split("|");
		dataX(data);		
		//document.getElementById("cliente_data").value = procesar(url);
		document.getElementById("credito").value = cliente.options[cliente.selectedIndex].title.split("~")[0];
		document.getElementById("credito_span").innerHTML = money(cliente.options[cliente.selectedIndex].title.split("~")[0]);
		document.getElementById("credito_disponible").value = cliente.options[cliente.selectedIndex].title.split("~")[1];
		document.getElementById("credito_disponible_span").innerHTML = money(cliente.options[cliente.selectedIndex].title.split("~")[1]);
		document.getElementById("sumatoria_span").innerHTML = money(cliente.options[cliente.selectedIndex].title.split("~")[1]);
	}
	document.getElementById("tr_fecha").style.display = "";
	document.getElementById("tr_almacen").style.display = "";
	document.getElementById("tr_precios").style.display = "";
	document.getElementById("tr_folio").style.display = "";
	document.getElementById("folio").style.display = "";
	document.getElementById("info_confirm").style.display = "";
	
	credito_show(document.getElementById("pago"));
}

function dataX(data)
{
  $("#cliente_datos").val(data[0]+"|"+data[1]+"|"+data[2]+"|"+data[3]+"|"+data[4]+"|"+data[5]+"|"+data[6]+"|"+data[7]+"|"+data[8]+"|"+data[9]+"|"+data[10]);	
	
	if(data[4].length > 0)
	{
	  	data[4] = " Int. "+data[4];
	}
	if(data[3].length > 0)
	{
	  	data[3] = " No. "+data[3];
	}	
	$("#cliente_data").val("==Número de cuenta ["+data[11]+"]==\nCALLE: "+data[2]+data[3]+ data[4]+"\nCOLONIA: "+data[5]+"\nC.P.: "+data[10]+"\n"+data[6]+", "+data[7]+", "+data[8]+", "+data[9]+"\nRFC: "+data[0]);
}

function cliente_data_ajax(obj){	
	var cliente = document.getElementById("cliente");
	var url = "ajax_tools.php?cliente_data2="+obj.options[obj.selectedIndex].value;
	data = array("","","","","","","","","","");
	r = procesar(url);		
	data = r.split("|");
	dataX(data);
	//este cambio se hace por la factura electronica para simplificar el manejo de los datos
	//document.getElementById("cliente_data").value = procesar(url);		
	document.getElementById("credito").value = obj.options[obj.selectedIndex].title.split("~")[0];
	document.getElementById("credito_span").innerHTML = money(obj.options[obj.selectedIndex].title.split("~")[0]);
	document.getElementById("credito_disponible").value = obj.options[obj.selectedIndex].title.split("~")[1];
	document.getElementById("credito_disponible_span").innerHTML = money(obj.options[obj.selectedIndex].title.split("~")[1]);
	document.getElementById("sumatoria_span").innerHTML = money(cliente.options[cliente.selectedIndex].title.split("~")[1]);
	sumar();
}

function credito_show(obj){
	if(obj.selectedIndex == 0){ //Es pago de Contado
		document.getElementById("tabla_credito").style.display = "none";
	} else { //Es a Crédito		
	  var tip =$("#tipo_venta").val();
		if(tip != 0)
		{
		  document.getElementById("tabla_credito").style.display = "";
		}
	}
}

function confirmar_informacion(){
	var almacen = document.getElementById("id_almacen");
	document.getElementById("almacen_span").innerHTML = almacen.options[almacen.selectedIndex].text;
	document.getElementById("almacen_fields").style.display = "none";
	var almacen = document.getElementById("precios");
	document.getElementById("precios_span").innerHTML = almacen.options[almacen.selectedIndex].text;
	document.getElementById("precios_fields").style.display = "none";
	document.getElementById("info_confirm").style.display = "none";
	document.getElementById("tabla_productos").style.display = "";
	document.getElementById("total").style.display = "";
	document.getElementById("registrar").style.display = "";
	AgregarFila();
	
	<?php
	if(isset($_GET[cotizacion]))
	{
		$i = 1;
	?>
	cot_id_cotizacionproducto = array();
	cot_folio_cotizacion = array();
	cot_id_producto = array();
	cot_cantidad = array();
	cot_precio = array();
	cot_iva = array();
	cot_importe = array();
	cot_especial = array();
	cot_cantidad = array();
	cot_complemento = array();
	cot_moneda = array();
	<?php
		$s = "SELECT
		id_cotizacionproducto,
		folio_cotizacion,
		id_producto,
		cantidad,
		precio,
		iva,
		importe,
		especial,
		complemento,

		IF(complemento = 0,complemento,'')complemento  FROM cotizaciones_productos WHERE folio_cotizacion = '{$_GET[cotizacion]}'";
		$q = mysql_query($s) or die (mysql_error());
		while($r = mysql_fetch_assoc($q))
		{
			foreach($r as $k => $v)
			{		
			 if($k == "complemento")		
			 {
				 if($v == "0") 
				 {
					 $v =""; 
				 }
			 }
			?>
			cot_<?=$k?>[<?=$i?>] = "<?=addslashes(str_replace("\r\n","\\r\\n",$v))?>";
			<?php
			}
			$i++;
		}
	?>
	for(e = 1; e <= <?=($i-1)?>; e++)
	{
	
		if(parseInt(cot_id_producto[e]) > 0)
		{
			activar(e);		
			$("#id_producto"+e).val(cot_id_producto[e]);
			buscar_datos(document.getElementById("id_producto"+e),e,"id_producto");
			$("#cantidad"+e).val(cot_cantidad[e]);
			CompararExistencias($($("#cantidad"+e).get(0)),e);
		}
		else
		{
			$("#swi"+e).attr("checked",true);
			$("#descripcion_especial"+e).val(cot_especial[e]);
			cambiar_especial($("#swi"+e).get(0),e);	
			$("#cantidad"+e).val(cot_cantidad[e]);
		}
		$("#complemento"+e).val(cot_complemento[e]).attr("disabled",false);
		$("#precio"+e).val(cot_precio[e]);
		$("#iva"+e).val(cot_iva[e]);
		AgregarFila();
	}
	sumar();
	
	<?php
	}
	?>
}

function ingresar()
{
	//Verificar Folio
	var folio = document.getElementById("folio");
	if(folio.value.length == 0)
	{
		alert("Escriba un folio para esta venta.");
		folio.focus();
		return false;
	}
	
	if(verificar_folio())
	{
		//Verificar que la venta tenga valor
		var cantidad = 0;
		$("input[name='cantidad[]']").each(function(i,v){
			cantidad += parseFloat($(this).val());
		});
		if(cantidad == 0)
		{
			alert("La venta está en ceros.");
			return false;
		}
		
		//Verificar Crédito del cliente
		if(items())
		{
			var tipo = document.getElementById("tipo_venta");
			var pago = document.getElementById("pago");
			if(tipo !== null)
			{
				if(tipo.value == 1 && pago.selectedIndex == 1)
				{ //Es factura a crédito
					var sumatoria = document.getElementById("sumatoria");
					if(parseFloat(sumatoria.value) < 0)
					{
						alert("El crédito de este cliente ha sido superado.");
						return false;
					}
				} 
				else 
				{
					return true;
				}
			}
		} 
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

function monedas()
{
	if($("#moneda").val() == "U.S.D.")
	{
		$("#span_moneda").html("USD");
		$("[name='precio[]']").each(function(){
			if($(this).attr("title") != "USD" && parseFloat($(this).val()) > 0)
			{
				$(this).val(parseFloat($(this).val()) / <?=$_SESSION[dolar]?>).attr("title","USD");
			}
		});
	}
	else
	{
		$("#span_moneda").html("MN");
		var dolar = parseFloat($("#moneda").find("option:last").val());
		$("[name='precio[]']").each(function(){
			if($(this).attr("title") != "MN" && parseFloat($(this).val()) > 0)
			{
				$(this).val(parseFloat($(this).val()) * <?=$_SESSION[dolar]?>).attr("title","MN");
			}
		});
	}
	$("[name='precio[]']").each(function(){
		$(this).val(parseFloat($(this).val()).toFixed(2));
	});
	sumar();
}

$(document).ready(function(){
	evitar();
	
	var hecho = 0;
	var m = $('#morosos').hide();
	/*$('#pago').change(function(){
		var t = $(this);
		if(t.val() == 'contado' && hecho == 0)
		{
			hecho++;
			var cargando = $('<span>Cargando clientes...</span>').css({ fontSize: '10px', float: 'right', marginTop: '5px', fontStyle: 'italic' });
			$('#cliente').attr('disabled', true);
			t.parent().append(cargando);
			$.getJSON('ajax_tools.php', { morosos: true }, function(data){
				$(data).each(function(i, r){
					var o = $("<option value='"+ r.clave +"' title='"+ r.credito +"~"+ r.disponible +"'>"+ r.nombre +"</option>");
					m.append(o);
				});
				m.show();
				cargando.fadeOut();
			$('#cliente').attr('disabled', false);
			});
		}
	});*/
});

</script>
<center>
  <form id="form1" name="form1" method="post" action="" onsubmit="return ingresar();">
    <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla">
      <tr>
        <th>Tipo de Documento</th>
        <td colspan="3">
        	<input name="Nota" type="button" value="Nota" onclick="confirmar_tipo_venta(0);" id="tipo_nota" />
        	&nbsp;
          <?php if(mysql_num_rows($query_cliente)>0){ ?>
          <input name="Factura" type="button" value="Factura" onclick="confirmar_tipo_venta(1);" id="tipo_factura" />
          <?php } else {?>
          <script language="javascript" type="text/javascript">window.onload = function(){confirmar_tipo_venta(0);}</script>
          <?php } ?>
          <input name="tipo_venta" id="tipo_venta" type="hidden" value="" />
          <span id="span_tipo_venta"></span>
        </td>
      </tr>
      <tr id="tr_folio" style="display:none">
        <th>Folio</th>
        <?php if(isset($seagoto)){
					 $valor= "<span style='color:#F00; font-size:9px;'>Los folios se agotaron. Solicite otros.</span>";
					  $sololectura="readonly='readonly'";
					}
					else
					{
					 $valor="";	
					}
					?>          
        <td><input name="folio" type="text" <?=$sololectura?> id="folio" size="10" value="<?=$proximo_folio?>" onblur="return verificar_folio();" /> <?=$valor?>      
        <input name="serie_f" type="hidden" value="<?=$r_fes[serie]?>" id="serie_f" size="10" />
        </td>
        <th id="tr_fecha" style="display:none">Fecha</th>
        <td><input name="fecha" size="10" maxlength="10" readonly="readonly" value="<?=date("Y-m-d")?>" />
          <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha');" style="margin-bottom:-3px; cursor:pointer" /></td>
      </tr>
      <tr id="tr_almacen" style="display:none">
        <th>Almac&eacute;n</th>
        <td>
        <span id="almacen_fields">
        <select name="id_almacen" id="id_almacen">
            <?php
						foreach($_SESSION[almacenes] as $k => $v){
							echo "<option value=\"{$v[id]}\" ".selected($_GET[almacen],$v[id]).">{$v[descripcion]}</option>\r\n";
						}
						?>
          </select>
        </span>
          <span id="almacen_span">
          </span>
        </td>
        <th id="tr_precios" style="display:none">Precios</th>
        <td>
					<span id="precios_fields">
						<select name="precios" id="precios">
							<optgroup label="Swedish Cars">
								<option value="_normal_">Normal</option>
								<?php
												$strSQL = "SELECT cliente AS 'licitacion' FROM precios GROUP BY cliente";
												$cli = mysql_query($strSQL);
												while($s = mysql_fetch_array($cli, MYSQL_ASSOC)){ ?>
								<option value="<?=$s[licitacion]?>">
								<?=$s[licitacion]?>
								</option>
								<? } ?>
						</select>
					</span>
					<span id="precios_span"></span>
				</td>
      </tr>
      <tr id="tr_pago" style="display:none">
        <th>Pago</th>
        <td><select name="pago" id="pago" onchange="credito_show(this);">
            <option value="contado">Contado</option>
            <option value="credito" selected="selected">Cr&eacute;dito</option>
          </select></td>
        <th id="tr_moneda" style="display:none">Moneda</th>
        <td><select name="moneda" id="moneda" onchange="monedas();">
          <option value="M.N.">Peso</option>
          <option value="U.S.D.">D&oacute;lar</option>
        </select></td>
      </tr>
      <tr id="tr_metodo" style="display:none">
        <th>M&eacute;todo de pago</th>
        <td>
					<select name="metodoDePago">
						<option>EFECTIVO</option>
						<option>TARJETA DE CREDITO</option>
						<option>TARJETA DE DEBITO</option>
						<option>CHEQUE NOMINATIVO</option>
						<option>TRANSFERENCIA BANCARIA</option>
						<option>NO IDENTIFICADO</option>
					</select>
				</td>
        <th>No. de cuenta</th>
        <td><input name="NumCtaPago" id="NumCtaPago" placeholder="Definido por el cliente" /></td>
      </tr>
      <tr id="tr_leyenda" style="display:none">
        <th>Leyenda</th>
        <td colspan="3"><textarea name="leyenda" cols="60" rows="4" id="leyenda"></textarea></td>
      </tr>
      <tr id="tr_cliente" style="display:none">
        <th colspan="4"> <center>
            <span id="cliente_span">Cliente</span> <a href="#" onclick="return hs.htmlExpand(this, { headingText: 'Información de Lista de Clientes' })"> <img src="imagenes/icon_attention.png" style="margin-bottom:-5px" /> </a>
            <div class="highslide-maincontent">En esta lista de clientes s&oacute;lamente se muestran aquellos que est&eacute;n activos y  no tengan facturas vencidas.</div>
          </center>
        </th>
      </tr>
      <tr id="tr_cliente_list" style="display:none">
        <td colspan="4" style="text-align:center">
					<select name="cliente" id="cliente" onchange="cliente_data_ajax(this);">
						<optgroup label="Con credito disponible">
							<?php
							function dateDiff($start, $end) {$start_ts = strtotime($start);$end_ts = strtotime($end);$diff = $end_ts - $start_ts;return round($diff / 86400);}
							if(mysql_num_rows($query_cliente)>0)
							{
								while($r = mysql_fetch_array($query_cliente))
								{
									echo "<option value='{$r[clave]}' title='{$r[credito]}~{$r[disponible]}'>{$r[nombre]}</option>";
								}
							}
							?>
						</optgroup>
						<optgroup label="Para pago de contado" id="morosos"></optgroup>
					</select>
				</td>
      </tr>
      <tr id="tr_datos_cliente" style="display:none">
        <th>Datos del Cliente</th>
        <td colspan="3"><textarea name="cliente_data" cols="60" rows="6" readonly="readonly" id="cliente_data"></textarea><input type="hidden" name="cliente_datos" value="" id="cliente_datos" /></td>
      </tr>
		</table>
    <br />
    <input type="button" name="info_confirm" id="info_confirm" value="Confirmar Almac&eacute;n y Lista de Precios" onclick="confirmar_informacion();" style="display:none; margin-bottom:14px;" />    
    
    <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla lista" id="tabla_credito" style="display:none; margin-bottom:10px;">
      <caption id="aviso">
      Crédito
      </caption>
      <tr>
        <th>Total</th>
        <th>Disponible</th>
        <th>Con esta Venta</th>
      </tr>
      <tr>
        <td style="text-align:center"><input type="hidden" value="0" name="credito" id="credito"/>
          $ <span id="credito_span">0.00</span></td>
        <td style="text-align:center"><input type="hidden" value="0" name="credito_disponible" id="credito_disponible" />
          $ <span id="credito_disponible_span">0.00</span></td>
        <td style="text-align:center"><input type="hidden" name="sumatoria" id="sumatoria" />
          $ <span id="sumatoria_span">0.00</span></td>
      </tr>
    </table>
    <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="tabla_productos" style="display:none">
      <tr>
      	<th><img src="imagenes/icon_attention.png" width="16" height="16" /></th>
        <th>&nbsp;</th>
        <th>C&oacute;digo Barras</th>
        <th>Unidad</th>
        <th>Descripci&oacute;n</th>
        <th>Lote</th>
        <th>Disponible</th>
        <th>Cantidad</th>
        <th>Precio <span id="span_moneda"></span></th>
        <th style="display:none;">% Desc</th>
        <th>% IVA</th>
        <!--<th>Importe</th>-->
        <th>&nbsp;</th>
      </tr>
      <tbody id="tabla"></tbody>
    </table>
      <br>
    <table border="0" align="center" cellpadding="6" cellspacing="0" class="bordear_tabla" id="total" style="display:none">
      <tr>
        <th>Sub-Total</th>
        <td style="text-align:right"><b>$ <span id="sub_total_span">0.00</span></b></td>
      </tr>
      <tr style="display:none;">
        <th>Descuento</th>
        <td style="text-align:right"><b>$ <span id="descuento_span">0.00</span></b></td>
      </tr>
      <tr>
        <th>IVA</th>
        <td style="text-align:right"><b>$ <span id="iva_span">0.00</span></b></td>
      </tr>
      <tr>
        <th>Total:</th>
        <td style="text-align:right">

          <b>$ <span id="total_span">0.00</span></b>
          <input type="hidden" value="0.00" id="total_input" name="total_input" />
        </td>
      </tr>
    </table>
    <br /><input type="submit" name="crear_registros" value="Registrar Venta" id="registrar" style="display:none"/>
  </form>
</center>