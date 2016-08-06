<?php
if(isset($_GET[buscar])){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");

	$sql = "SELECT folio_factura FROM compras WHERE folio_factura = '{$_GET['buscar']}' AND id_proveedor = '{$_GET['prov']}' AND status = 0 GROUP BY folio_factura";
	$query = mysql_query($sql) or die (mysql_error());
	$result = mysql_num_rows($query);
	echo $result;
	exit();
}

if(isset($_POST[crear_registros])){
  if(count($_POST[id_producto])>0){
    //**********Eliminar POSTS repetidos con LOTES repetidos   
    $id_productos_indice = array();
    $lotes_indice = array();
    for($x=0;count($_POST[id_producto])>$x;$x++){
      $id_productos_indice[$x] = $_POST[id_producto][$x];
    }
    $id_productos_indice = array_unique($id_productos_indice);
    for($i=0;count($id_productos_indice)>$i;$i++){
      $lotes_indice = array();
      for($ii=0;count($_POST[id_producto])>$ii;$ii++){
        if($id_productos_indice[$i] == $_POST[id_producto][$ii]){
          if(!in_array($_POST[lote][$ii],$lotes_indice)){
            $lotes_indice[$i] = $_POST[lote][$ii];
          } else {
            unset($_POST[id_producto][$ii]);
            unset($_POST[lote][$ii]);
            unset($_POST[cantidad][$ii]);
            unset($_POST[sub_importe][$ii]);
            unset($_POST[importe][$ii]);
            unset($_POST[costo][$ii]);
          }
        }
      }
    }
   }
	   
  if(count($_POST[id_producto])>0){
    //***********Termina filtro
    $s = "INSERT INTO compras (
		folio_factura,
		importe,
		id_proveedor,
		id_almacen,
		fecha_factura,
		fecha_captura,
		dias_credito,
		moneda

		) VALUES (
    '{$_POST[folio_factura]}',
    ".array_sum($_POST[importe]).",
    '{$_POST[id_prove]}',
    '{$_POST[almacen]}',
    '{$_POST[fecha_factura]}',
    NOW(),
    '{$_POST[dias_credito]}',
		'{$_POST[moneda]}')";
		
    mysql_query($s) or die ($s.mysql_error());
    $ID = mysql_insert_id();
		
    foreach($_POST[id_producto] as $k => $v)
		{
      if(strlen($_POST[id_producto][$k])>0 && $_POST[cantidad][$k]>0)
			{
  			//Compras
        $strSQL = "INSERT INTO compras_detalle VALUES (
  			NULL,
  			{$ID},
  			'{$_POST[id_producto][$k]}',
  			'{$_POST[lote][$k]}',
  			'{$_POST[cantidad][$k]}',
  			'{$_POST[costo][$k]}',
  			'{$_POST[iva][$k]}',
  			'{$_POST[sub_importe][$k]}',
  			'{$_POST[importe][$k]}'
  			)";
        mysql_query($strSQL) or die ($strSQL.mysql_error());
  			
/*				
        // Existencias
  			//Buscar Existencia Actual
        $exists_sql = "SELECT
        id_existencia
        FROM existencias
        WHERE id_producto = '{$_POST[id_producto][$k]}'
        AND id_almacen = '{$_POST[almacen]}'
        AND lote = '{$_POST[lote][$k]}'";
        $exists_query = mysql_query($exists_sql) or die (mysql_error());
        if (mysql_num_rows($exists_query)==0){ //No existe. Crear.
          $strSQL = "INSERT INTO existencias VALUES (
  				NULL,
  				'{$_POST[almacen]}',
  				'{$_POST[id_producto][$k]}',
  				'{$_POST[cantidad][$k]}',
  				'{$_POST[lote][$k]}'
  				)";
        }
        else { //Existe. Actualizar.
          $exists = mysql_fetch_assoc($exists_query);
          $exists_id = $exists[id_existencia];
          $strSQL = "UPDATE existencias SET
          cantidad = cantidad+".abs($_POST[cantidad][$k])."
          WHERE id_existencia = {$exists_id}";
        }
  			mysql_query($strSQL) or die (nl2br($strSQL)."<br>".mysql_error());
        
        //Movimientos
    		$strSQL = "INSERT INTO movimientos
    		VALUES(
    		null,
    		'{$_POST[almacen]}',
    		'0',
    		'5',
    		'{$_POST[id_producto][$k]}',
    		'{$_POST[cantidad][$k]}',
    		'{$_POST[lote][$k]}',
    		NOW(),
    		'{$_SESSION[id_usuario]}',
    		'{$_POST[folio_factura]}',
                'CTO1' 
                  )";
    		mysql_query($strSQL) or die (nl2br($strSQL)."<br>".mysql_error());
    		*/
    		//Buscar PMV Actual
				$s = "SELECT pmv FROM vars";
				$q = mysql_query($s);
				$pmv = mysql_fetch_assoc($q);
				$pmv = 1+($pmv[pmv]/100);
				
    		$query_minimo = "SELECT
    		(SUM(compras_detalle.importe)/SUM(cantidad))*{$pmv} AS 'minimo'
    		FROM compras_detalle
    		INNER JOIN compras ON compras.id = compras_detalle.id_compra
    		WHERE id_producto = '{$_POST[id_producto][$k]}'
    		AND status = '0'";
    		$minimo = mysql_query($query_minimo)or die(mysql_error());
    		$row_minimo = mysql_fetch_assoc($minimo);
    		$min = number_format($row_minimo[minimo], 2, '.', '');
    		
    		$pmv_sql = "SELECT id_producto FROM precio_minimo WHERE id_producto = '{$_POST[id_producto][$k]}'";
    		$pmv_query = mysql_query($pmv_sql) or die (mysql_error());
    		if(mysql_num_rows($pmv_query)==0){ //No existe. Crear.
    			$strSQL = "INSERT INTO precio_minimo VALUES ('{$_POST[id_producto][$k]}','{$min}')";
    		}
    		else{ //Existe. Actualizar.
    			$strSQL = "UPDATE precio_minimo SET pmv = '{$min}' WHERE id_producto = '{$_POST[id_producto][$k]}'";
    		}
    		mysql_query($strSQL) or die (nl2br($strSQL)."<br>".mysql_error());
    		unset($min);
    		
    		//Verificar si algún producto ha sido PEDIDO con anterioridad.
    		$pedidoS = "SELECT *,
    		IFNULL(obtenidos,0)'obtenidos'
    		FROM pedidos
    		WHERE proveedor = {$_POST[id_prove]}
    		AND id_producto = {$_POST[id_producto][$k]}
    		AND almacen = '{$_POST[almacen]}'
    		AND status = 0
    		HAVING cantidad <> obtenidos";
    		$pedidoQ = mysql_query($pedidoS) or die ($pedidoS.mysql_error());
    		$comprados = $_POST[cantidad][$k];
    		if(mysql_num_rows($pedidoQ)>0){
    			while($r = mysql_fetch_assoc($pedidoQ)){
    				if($comprados > 0){ //Volver a verificar que por lo menos un artículo fue comprado.
    					$cantidad = $r[cantidad];
    					$obtenidos = $r[obtenidos];
    					$faltantes = $cantidad-$obtenidos;
    					$id_pedido = $r[id];
    					
    					if($faltantes>$comprados){ //Se han comprado más de los que faltan.
    						$nuevo_obtenidos = $obtenidos+$comprados;
    						$sobrantes = 0;
    						$x=1;
    					}
    					if($faltantes<$comprados){ //Aún faltan productos para completar este pedido.
    						$nuevo_obtenidos = $cantidad;
    						$sobrantes = $comprados-$faltantes;
    						$x=2;
    					}
    					if($faltantes==$comprados){ //El pedido ha sido completado satisfactoriamente con esta compra.
    						$nuevo_obtenidos = $comprados;
    						$sobrantes = 0;
    						$x=3;
    					}
    					//Actualizar PEDIDOS
    					$compras = $r[compra].",".$ID."|".($nuevo_obtenidos-$obtenidos);
    					$strSQL = "UPDATE pedidos SET obtenidos = '{$nuevo_obtenidos}', compra = '{$compras}' WHERE id = '{$id_pedido}'";
    					$comprados = $sobrantes;
    					mysql_query($strSQL) or die (nl2br($strSQL)."<br>".mysql_error());
    				}
    			}
    		}
      }
		}
		//Revalidar el total
		$s = "UPDATE compras SET importe = (SELECT SUM(compras_detalle.importe) FROM compras_detalle WHERE compras_detalle.id_compra = {$ID} GROUP BY compras_detalle.id_compra) WHERE id = {$ID}";
		mysql_query($s);
    relocation("?section=compras_detalle&id={$ID}");
  } else {
    $error = "No se hayaron productos para realizar una compra.";
  }
}

//Inicia configuración
titleset("Registro de Compra");
//Fin de configuración
?>
<script language="javascript" type="text/javascript">
var n = 0;
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
  buscar.href = "productos_pick.php?n="+num;
	buscar.onclick = function(){return hs.htmlExpand(this,{objectType:'iframe',headingText:'Buscador de Productos',minWidth:600,height:600,preserveContent:false,cacheAjax:false})};
  celda.appendChild(buscar);
	
  //Control (Oculto) Sirve para determinar si el campo "Búsqueda" ha cambiado despues de "onBlur"
  control = document.createElement("input");
  control.type = "hidden";
  control.name = "control[]";
  control.id = "control"+num;
	control.value = "~~~Campo de Control~~~";
	control.disabled = true;
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
	barras.onkeypress = function(){ barrasEvaluarKey(event, this, num) };
  celda.appendChild(barras);
  
  row.appendChild(celda); //Esta celda comparte "Búsqueda" , "ID_producto" y "Valor_inicial"
  
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
  celda.align = "center";
  lote = document.createElement("input");
  lote.type = "text";
  lote.name = "lote[]";
  lote.id = "lote"+num;
  lote.size = 10;
	lote.readOnly = true;
  celda.appendChild(lote);
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
  cantidad.onblur = function(){numero(this,3); sumar_tr(num); sumar();}
  celda.appendChild(cantidad);
  row.appendChild(celda);
	
  //Costo U.
  celda = document.createElement("td");
  celda.align = "center";
  costo = document.createElement("input");
  costo.type = "text";
  costo.name = "costo[]";
  costo.id = "costo"+num;
  costo.size = 7;
	costo.value= "0.00";
	costo.readOnly = true;
  costo.onblur = function(){numero(this,2); sumar_tr(num); sumar();}
  celda.appendChild(costo);
  row.appendChild(celda);
	
  //IVA
  celda = document.createElement("td");
  celda.align = "center";
  iva = document.createElement("input");
  iva.type = "text";
  iva.name = "iva[]";
  iva.id = "iva"+num;
  iva.size = 5;
	iva.value= "16.00";
	iva.readOnly = true;
  iva.onblur = function(){numero(this,2); sumar_tr(num); sumar(); AgregarFilaValida(this);}
  celda.appendChild(iva);
  row.appendChild(celda);
	
	//Sub-Importe
	celda = document.createElement("td");
  celda.align = "center";
  sub_importe = document.createElement("input");
  sub_importe.type = "text";
  sub_importe.name = "sub_importe[]";
  sub_importe.id = "sub_importe"+num;
  sub_importe.size = 10;
	sub_importe.value= "0.00";
	sub_importe.readOnly = true;
  sub_importe.onblur = function(){numero(this,2); sumar_tr(num); sumar();}
  celda.appendChild(sub_importe);
  row.appendChild(celda);
	
	//Importe
	celda = document.createElement("td");
  celda.align = "center";
  importe = document.createElement("input");
  importe.type = "text";
  importe.name = "importe[]";
  importe.id = "importe"+num;
  importe.size = 10;
	importe.value= "0.00";
	importe.readOnly = true;
  importe.onblur = function(){numero(this,2); sumar_tr(num); sumar();}
  celda.appendChild(importe);
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

//25 Nov > Buscar producto on enter
function barrasEvaluarKey(event, obj, num)
{
	if(event.keyCode == 13)
	{
		buscar_datos(obj, num, "barras");
	}
	console.log(event);
	return false;
}

function resetear(num){
  document.getElementById("control"+num).value = "~~~Campo de Control~~~";
  document.getElementById("id_producto"+num).value = "";
	document.getElementById("barras"+num).value = "";
  document.getElementById("descripcion"+num).length = 0;
  document.getElementById("descripcion"+num).options[0] = new Option("Escriba un código de barras.");
	document.getElementById("cantidad"+num).value = "0";
  document.getElementById("costo"+num).value = "0.00";
	document.getElementById("iva"+num).value = "0.00";
	document.getElementById("importe"+num).value = "0.00";
	
	sumar();
}

function buscar_datos_pop(num){
	var id_producto = document.getElementById("id_producto"+num);
	buscar_datos(document.getElementById("id_producto"+num),num,"id_producto")
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

function buscar_datos(obj,num,tipo){
	var control = document.getElementById("control"+num);
	var id_producto = document.getElementById("id_producto"+num);
  var barras = document.getElementById("barras"+num);
	var descripcion = document.getElementById("descripcion"+num);
	var lote = document.getElementById("lote"+num);
	var cantidad = document.getElementById("cantidad"+num);
  var costo = document.getElementById("costo"+num);
  var iva = document.getElementById("iva"+num);
  var sub_importe = document.getElementById("sub_importe"+num);
  var importe = document.getElementById("importe"+num);
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
        descripciones = ajax_query_id_producto(obj.value,"descripcion").split("~");
        codigo_barras = ajax_query_id_producto(obj.value,"codigo_barras").split("~");
        barras.value = codigo_barras;
        control.value = codigo_barras;
      }
      for(i=0; descripciones.length>i; i++){
				descripcion.options[i] = new Option(descripciones[i],ajax_id_producto);
			}
      lote.readOnly = false;
      cantidad.readOnly = false;
      costo.readOnly = false;
      iva.readOnly = false;
			
			lote.focus();
			
		} else {
			alert("No se pudo encontrar este producto.\n"+obj.value);
			control.value = "~~~Campo de Control~~~";
			id_producto.value = "";
			descripcion.length = 0;
			descripcion.options[0] = new Option("Escriba un código de barras.");
      cantidad.readOnly = true;
      cantidad.value = 0;
      lote.readOnly = true;
      lote.value = "";
      costo.readOnly = true;
      costo.value = "0.00";
      iva.value = "0.00";
      iva.readOnly = true;
      sub_importe.value = "0.00";
		}
	}
	if(obj.value.length == 0){
		control.value = "~~~Campo de Control~~~";
		id_producto.value = "";
		descripcion.length = 0;
		descripcion.options[0] = new Option("Escriba un código de barras.");
    cantidad.readOnly = true;
    cantidad.value = 0;
    lote.readOnly = true;
    lote.value = "";
    costo.readOnly = true;
    costo.value = "0.00";
    iva.readOnly = true;
    iva.value = "0.00";
    sub_importe.readOnly = true;
    sub_importe.value = "0.00";
    importe.readOnly = true;
    importe.value = "0.00";
	}
}

function actualizar_campos(obj,num){
	document.getElementById("id_producto"+num).value = obj.options[obj.selectedIndex].value.split("~")[obj.selectedIndex];
}

function sumar_tr(num){
	var cantidad = parseFloat(document.getElementById("cantidad"+num).value);
  var costo = parseFloat(document.getElementById("costo"+num).value);
  var iva = parseFloat(document.getElementById("iva"+num).value)/100;
  var sub_importe = document.getElementById("sub_importe"+num);
  var importe = document.getElementById("importe"+num);
  
   var sub_importe_v = round(cantidad*costo,2);
   var importe_v =  round(cantidad*costo,2)+ round(cantidad*(costo*iva),2);  
  
  /*var sub_importe_v = cantidad*(costo);
  var importe_v = cantidad*(costo+(costo*(iva/100)));*/
  
  sub_importe.value = sub_importe_v.toFixed(2);
  importe.value = importe_v.toFixed(2);
}

function sumar(){
  var subtotales = document.getElementsByName("sub_importe[]");
  var importes = document.getElementsByName("importe[]");
  var subtotal = 0;
  var total = 0;
  for(var i=0; importes.length>i;i++){
    subtotal += parseFloat(subtotales[i].value);
    total += parseFloat(importes[i].value);
  }
  document.getElementById("sub_total_span").innerHTML = money(subtotal);
  document.getElementById("iva_span").innerHTML = money(total-subtotal);
  document.getElementById("total_span").innerHTML = money(total);
	document.getElementById("total").value = total;
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
  var id_productos = document.getElementsByName("id_producto[]");
  var lotes = document.getElementsByName("lote[]");
  var id_productos_indice = new Array();
  var lotes_indice = new Array();
  for(var x=0;id_productos.length>x;x++){ //Indexar VALORES de id_producto
    if(id_productos[x].value != ""){
      id_productos_indice[x] = id_productos[x].value;
    }
  }
  id_productos_indice = array_unique2(id_productos_indice); //Identificar los valores diferentes
  for(var i=0;id_productos_indice.length>i;i++){ //Por cada valor...
    var lotes_indice = new Array(); //Resetear índice
    for(var ii=0;id_productos.length>ii;ii++){ //Por cada campo ID_PRODUCTO
      if(id_productos_indice[i] == id_productos[ii].value){ //Si el campo corresponde al valor actual
        if(!in_array(lotes[ii].value,lotes_indice)){ //¿Está en el índice de lotes?
          lotes_indice[i] = lotes[i].value; //No está. Indexarlo.
        } else { //Sí está. Reportarlo.
          alert("Existe un producto con el mismo lote repetido.\nVerifique la información.");
          return false;
        }
      }
    }
  }
  return true;
}

function resetear(num){
  document.getElementById("control"+num).value = "~~~Campo de Control~~~";
  document.getElementById("id_producto"+num).value = "";
	document.getElementById("barras"+num).value = "";
  document.getElementById("descripcion"+num).length = 0;
  document.getElementById("descripcion"+num).options[0] = new Option("Escriba un código de barras.");
	document.getElementById("cantidad"+num).value = "0";
  document.getElementById("costo"+num).value = "0.00";
	document.getElementById("iva"+num).value = "0.00";
	document.getElementById("importe"+num).value = "0.00";
	
	sumar();
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
	return false;
}

function checar_folio(){
	var folio = document.getElementById("folio_factura").value;
	var prov = document.getElementById("id_prove");
	var prov_n = prov.options[prov.selectedIndex].value;
	if(folio != ""){
		url = "compras_formulario.php?buscar="+folio+"&prov="+prov_n;
		var r = procesar(url);
		if(r == "1"){alert("Este número de folio ya existe para este proveedor.");
			document.getElementById("folio_factura").focus();
			document.getElementById("folio_factura").select();
			return false;
		} else {
			return true;
		}
	}
}

function verifica_dias(){
	var dias = parseFloat(document.getElementById("dias_credito").value).toFixed(2);
	if(isNaN(dias)){
		alert("Cantidad de días de crédito inválida.");
		document.getElementById("dias_credito").value = 15;
	}
}

function ingresar(){
	var total = document.getElementById("total");
	var dias_credito = document.getElementById("dias_credito");
	var folio_factura = document.getElementById("folio_factura");
	if(total.value <= 0){alert("La compra está en ceros."); return false;}
	if (parseFloat(dias_credito.value) < 0){alert("Define los días de crédito que el proveedor asignó a esta factura."); dias_credito.focus(); return false;}
	if (folio_factura.value == ""){alert("Define el folio de esta compra."); folio_factura.focus(); return false;}
	return checar_folio()
  return items();
}

function confirmar_prove(){
	var prov = document.getElementById("id_prove");
	var prov_n = prov.options[prov.selectedIndex].text;
	prov.style.display = "none";
	document.getElementById("prov_span").innerHTML = prov_n;
	document.getElementById("confirmar").style.display = "none";
	document.getElementById("tr_folio").style.display = "";
	document.getElementById("tr_almacen").style.display = "";
	document.getElementById("tr_fecha").style.display = "";
	document.getElementById("tr_credito").style.display = "";
	document.getElementById("tabla_productos").style.display = "";
	document.getElementById("total_table").style.display = "";
	document.getElementById("registrar").style.display = "";
	document.getElementById("tr_moneda").style.display = "";
}

document.onkeypress = KeyPressed;
function KeyPressed(e)
{
	return ((window.event) ? event.keyCode : e.keyCode) != 13;
}

<?php
if(isset($_GET[pedido]))
{
?>
$(document).ready(function(){
	<?php if($_GET[moneda] == "USD"): ?>
	$("#moneda").find("option:last").attr("selected",true);
	<?php endif; ?>

	pedido_data = procesar("ajax_tools.php?pedido_compra=<?=$_GET[pedido]?>");
	lineas = pedido_data.split("\n");
	$("#id_prove > option").each(function(){
		if($(this).val() == lineas[0])
		{
			$(this).attr("selected",true);
			return false;
		}
	});
	
	confirmar_prove();
	
	$("#almacen > option").each(function(){
		if($(this).val() == lineas[1])
		{
			$(this).attr("selected",true);
			return false;
		}
	});
	
	linlen = lineas.length;
	
	for(x = 4; x < linlen; x++)
	{
		info = lineas[x].split("~");
		if(parseInt(info[0]) > 0)
		{
			table = $("#tabla_productos");
			$("#id_producto"+n).val(info[0]);
			buscar_datos(document.getElementById("id_producto"+n),n,"id_producto");		
			$("#cantidad"+n).val(info[1]);
			$("#costo"+n).val(info[2]);
			$("#iva"+n).val(info[3]);
			sumar_tr(n);
			AgregarFila();
		}
	}
	sumar();
});
<?php
}
?>
 
</script>
<?php
$pp = mysql_query("SELECT nombre FROM proveedores WHERE status = 0") or die (mysql_error());
if(mysql_num_rows($pp)>0){
?>
<form action="" method="post" name="form1" id="form1" onsubmit="return ingresar();">
  <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla" id="formulario">
    <tr>
      <th>Proveedor:</th>
      <td><span id="prov_span"></span>
        <select name="id_prove" id="id_prove">
          <?php
						$sql_proveedor = "SELECT clave, nombre FROM proveedores WHERE status = 0 ORDER BY nombre";
						$query_proveedor = mysql_query($sql_proveedor) or die (mysql_error());
						while($r = mysql_fetch_array($query_proveedor)){
					?>
          <option value="<?=$r[clave]?>">
          <?=$r[nombre]?>
          </option>
          <?php } ?>
        </select>
        <br>
        <input type="button" name="confirmar" id="confirmar" value="Confirmar Proveedor" onclick="confirmar_prove();" /></td>
    </tr>
    <tr id="tr_folio" style="display:none">
      <th>Folio:</th>
      <td><input type="text" name="folio_factura" value="" size="10" id="folio_factura" onBlur="checar_folio();"/></td>
    </tr>
    <tr id="tr_almacen" style="display:none">
      <th>Almac&eacute;n:</th>
      <td><select name="almacen" id="almacen">
          <?php foreach($_SESSION[almacenes] as $k => $v){ echo "<option value=\"{$v[id]}\" ".selected($_GET['almacen'],$v[id]).">{$v[descripcion]}</option>\r\n"; } ?>
        </select></td>
    </tr>
    <tr id="tr_fecha" style="display:none">
      <th>Fecha de Factura:</th>
      <td><input name="fecha_factura" size="10" maxlength="10" readonly="readonly" value="<?=date("Y-m-d")?>"/>
        <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha_factura');" style="margin-bottom:-3px; cursor:pointer" /></td>
    </tr>
    <tr id="tr_credito" style="display:none">
      <th>D&iacute;as de Cr&eacute;dito:</th>
      <td><input name="dias_credito" type="text" id="dias_credito" onBlur="verifica_dias();" value="15" size="3"/></td>
    </tr>
      <tr id="tr_moneda" style="display:none">
        <th>Moneda</th>
        <td><select name="moneda" id="moneda">
          <option value="M.N.">Peso</option>
          <option value="U.S.D.">D&oacute;lar</option>
        </select></td>
      </tr>

  </table><br />

<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="tabla_productos" style="display:none">
     <tr>
     	<th>&nbsp;</th>
      <th>C&oacute;digo de Barras</th>
      <th>Descripci&oacute;n</th>
      <th>Lote</th>
      <th>Cantidad</th>
      <th>Costo U.</th>
      <th>% IVA</th>
      <th>Sub-Importe</th>
      <th>Importe</th>
      <th>Borrar</th>
    </tr>
    <tbody id="tabla"></tbody>
	</table>
  <br>
  <table border="0" align="center" cellpadding="6" cellspacing="0" class="bordear_tabla" id="total_table" style="display:none">
    <tr>
      <th>Sub-Total</th>
      <td style="text-align:right"><b>$ <span id="sub_total_span">0.00</span></b></td>
    </tr>
    <tr>
      <th>IVA</th>
      <td style="text-align:right"><b>$ <span id="iva_span">0.00</span></b></td>
    </tr>
    <tr>
      <th>Total:</th>
      <td style="text-align:right"><input name="total" type="hidden" id="total" value="0.00" /><b>$ <span id="total_span">0.00</span></b></td>
    </tr>
  </table>
  <p style="text-align:center">
    <input type="submit" value="Registrar Compra" id="registrar"  style="display:none" name="crear_registros"/>
  </p>
</form>
<script language="javascript" type="text/javascript">
  <?php
  if(isset($error)){echo "alert(\"{$error}\");";}
  ?>
	AgregarFila();
</script>
<?php } else { ?>
<center><b>No hay proveedores disponibles.</b></center>
<?php } ?>