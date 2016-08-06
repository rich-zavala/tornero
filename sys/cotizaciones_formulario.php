<?php
$com_text = "Use este campo para escribir un complemento de la descripción del producto.";
if($_POST[crear_registros] == "Registrar"){
	
	if(isset($_POST[editar]))
	{
		mysql_query("DELETE FROM cotizaciones WHERE folio = '{$_POST[editar]}'") or die (mysql_error());
		mysql_query("DELETE FROM cotizaciones_productos WHERE folio_cotizacion = '{$_POST[editar]}'") or die (mysql_error());
	}
	
  //Limpiar información
  $data = array();
  for($i=0;count($_POST[id_producto])>$i;$i++){
		if(strlen($_POST[descripcion_especial][$i])==0){ //Es producto
			if(strlen($_POST[id_producto][$i])>0 && $_POST[control] != "~~~Campo de Control~~~"){ //Tiene cantidad y no es nulo
				$data[id_producto][] = $_POST[id_producto][$i];
				$data[cantidad][] = $_POST[cantidad][$i];
				$data[descuento][] = $_POST[descuento][$i];
				$data[importe][] = $_POST[importe][$i];
				$data[iva][] = $_POST[iva][$i];
				$data[precio][] = $_POST[precio][$i];
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
		} else {
			$data[descripcion_especial][] = $_POST[descripcion_especial][$i];
			$data[id_producto][] = "0";
			$data[cantidad][] = $_POST[cantidad][$i];
			$data[descuento][] = $_POST[descuento][$i];
			$data[importe][] = $_POST[importe][$i];
			$data[iva][] = $_POST[iva][$i];
			$data[precio][] = $_POST[precio][$i];
			
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
	$data[moneda] = $_POST[moneda];
  $data[cliente] = $_POST[cliente];
  $data[cliente_data] = $_POST[cliente_data];
	$data[fecha] = $_POST[fecha];
	$data[folio] = $_POST[folio];
  $data[total_input] = $_POST[total_input];
  unset($_POST);
  $_POST = $data;

  //Indexar esta venta
  $s = "INSERT INTO cotizaciones
    VALUES (
    '{$_POST[folio]}',
    '{$_SESSION[id_usuario]}',
    '{$_POST[fecha]}',
    '{$_POST[cliente]}',
    '{$_POST[cliente_data]}',
    '{$_POST[total_input]}',
		'{$_POST[moneda]}',
    0)";
  mysql_query($s) or die (nl2br($s)."<br>".mysql_error());

  //Registrar productos de esta receta
  foreach($_POST[importe] as $k => $v){
		if(strlen($_POST[descripcion_especial][$k])>1){
			$especial = mysql_real_escape_string($_POST[descripcion_especial][$k]);
		} else {
			$especial = 0;
		}

		if(strlen($_POST[complemento][$k])>1){
			$complemento = mysql_real_escape_string($_POST[complemento][$k]);
		} else {
			$complemento = '';
		}

    $s = "INSERT INTO cotizaciones_productos
		(
			folio_cotizacion,
			id_producto,
			cantidad,
			precio,
			iva,
			importe,
			especial,
			complemento
		)
    VALUES(
			'{$_POST[folio]}',
			'{$_POST[id_producto][$k]}',
			'{$_POST[cantidad][$k]}',
			'{$_POST[precio][$k]}',
			'{$_POST[iva][$k]}',
			'{$_POST[importe][$k]}',
			'{$especial}',
			'{$complemento}'
    )";	
    mysql_query($s) or die("facturas_productos: ".mysql_error());
  }
	
	//Corregir error de centavitos de diferencia
	$s = "UPDATE cotizaciones SET importe = ( SELECT SUM(ROUND(cantidad*precio,2)) + SUM(ROUND(cantidad*(precio*(iva/100)),2)) total FROM cotizaciones_productos WHERE folio_cotizacion = folio) WHERE folio = '{$_POST[folio]}'";
	mysql_query($s) or die($s." - ".mysql_error());
	$s = "UPDATE cotizaciones_productos SET importe = ROUND(cantidad*precio,2) + ROUND(cantidad*(precio*(iva/100)),2) WHERE folio_cotizacion = '{$_POST[folio]}'";
	mysql_query($s) or die($s." - ".mysql_error());
	
	relocation("?section=cotizaciones_detalle&folio={$_POST[folio]}");
}

if(isset($_GET[editar]))
{
	$s = "SELECT * FROM cotizaciones WHERE folio = '{$_GET[editar]}'";
	$q = mysql_query($s) or die (mysql_error());
	$editor = mysql_fetch_assoc($q);
}

$sf = "SELECT folio FROM cotizaciones ORDER BY folio+1 DESC LIMIT 1";
$qf = mysql_query($sf);
$rf = mysql_fetch_assoc($qf);
$proximo_folio = number_format(($rf[folio]+1),0,"","");

//Inicia configuración
titleset("Registro de Cotizaci&oacute;n");
//Fin de configuración
?>
<script language="javascript" type="text/javascript">
var n = 0;
var com_text = "<?=$com_text?>";
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
	buscar.href = "productos_pick.php?n="+num;
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
	precio.onblur = function(){numero(this,2); sumar_tr(num); sumar();}
	celda.appendChild(precio);
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
	descuento.onblur = function(){numero(this,2); sumar_tr(num); sumar();}
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
	iva.onblur = function(){numero(this,2); sumar_tr(num); sumar(); AgregarFilaValida(this);}
	celda.appendChild(iva);
	row.appendChild(celda);
	
	//Importe
	celda = document.createElement("td");
	celda.align = "center";
	importe = document.createElement("input");
	importe.type = "text";
	importe.name = "importe[]";
	importe.id = "importe"+num;
	importe.size = 9;
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
	eliminar.id = "eliminar"+num;
	celda.appendChild(eliminar);
	row.appendChild(celda);

	tbody.appendChild(row);

	$("[name='iva[]']").attr("disabled",true);
	$("#barras"+n).focus().select();
	evitar();
	$("[name='iva[]']").attr("disabled",false);
}


function cambiar_especial(obj,n){
	resetear(n);
	if(obj.checked){ // Cambiar a Especial
		document.getElementById("cantidad"+n).onblur = null;
		document.getElementById("precio"+n).onblur = null;
	
		$("#id_producto"+n).val("");
		$("#id_producto"+n).val("");
		$("#complemento"+n).attr("disabled",false);
		$("#buscar"+n).css("display","none");
		$("#barras"+n).val("").css("display","none");
		//$("#lote"+n).find('option').remove().end().append('<option>'+Math.floor(Math.random()*11)+'</option>').css("display","none");
		$("#disponible"+n).val("0.000").css("display","none");
		$("#descripcion"+n).css("display","none").find('option').remove().end().css("display","none");
		$("#descripcion_especial"+n).css("display","");
		$("#cantidad"+n).blur(function(){numero(this,3); sumar_tr(n); sumar();}).attr("readonly","");
		$("#precio"+n).blur(function(){numero(this,2); sumar_tr(n); sumar();});
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
      
      //Determinar IVA
      document.getElementById("iva"+num).value = ajax_query_id_producto(id_producto.value,"iva");
      numero(document.getElementById("iva"+num),2);
			$("#cantidad"+num).focus();
			precio_quiz(num);
		} else {
			alert("No se pudo encontrar este producto.\n"+obj.value);
			$("#barras"+num).val("");
      resetear(num);
		}
	}
  if(obj.value.length == 0) {
    resetear(num);
	}
}

function resetear(num){
  document.getElementById("control"+num).value = "~~~Campo de Control~~~";
  document.getElementById("id_producto"+num).value = "";
  document.getElementById("descripcion"+num).length = 0;
  document.getElementById("descripcion"+num).options[0] = new Option("Escriba un código de barras.");
  document.getElementById("iva"+num).value = "0.00";
  document.getElementById("precio"+num).value = "0.00";
	$("#complemento"+num).val(com_text);
  desactivar(num);
  sumar_tr(num);
	sumar();
}

function activar(num){
  document.getElementById("cantidad"+num).readOnly = false;
  document.getElementById("precio"+num).readOnly = false;
  document.getElementById("descuento"+num).readOnly = false;
  document.getElementById("iva"+num).readOnly = false;
	$("#complemento"+n).attr("disabled",false);
	sumar();
}

function desactivar(num){
  document.getElementById("cantidad"+num).readOnly = true;
  document.getElementById("cantidad"+num).value = "0.000";
  document.getElementById("precio"+num).readOnly = false;
  document.getElementById("descuento"+num).readOnly = true;
  document.getElementById("descuento"+num).value = "0.00";
  document.getElementById("iva"+num).readOnly = true;
	$("#complemento"+n).attr("disabled",true).val(com_text);
	sumar();
}

function precio_quiz(num){
	var id_producto = document.getElementById("id_producto"+num).value;
	var lista = "_normal_";
	var url = "ajax_tools.php?precio&id_producto="+id_producto+"&lista="+lista;
	var r = procesar(url);
	document.getElementById("precio"+num).value = r;
	monedas();
}

function actualizar_campos(obj,num){
	document.getElementById("id_producto"+num).value = obj.options[obj.selectedIndex].value.split("~")[obj.selectedIndex];
  var iva = ajax_query_id_producto(document.getElementById("id_producto"+num).value,"iva");
  document.getElementById("iva"+num).value = iva;
  numero(document.getElementById("iva"+num),2);
  precio_quiz(num);
}

function redon(valor){
	valor=valor*100;
	valor=Math.floor(valor);
	valor=valor/100;		
	return money(valor);
}
function redon2(valor){
	valor=valor*100;
	valor=Math.floor(valor);
	valor=valor/100;		
	return parseFloat(valor);
}

function sumar_tr(num){
	var _cantidad_ = parseFloat(document.getElementById("cantidad"+num).value);
  var _precio_ = parseFloat(document.getElementById("precio"+num).value);
  var _iva_ = parseFloat(document.getElementById("iva"+num).value)/100;
  
	importe_v = round(_cantidad_*_precio_,2) + round(_cantidad_*(_precio_*_iva_),2);
	
	var importe = document.getElementById("importe"+num);  
  importe.value = importe_v.toFixed(2);
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
		subtotal += round(_cantidad_*(_precio_),2);
		iva += round(round(_cantidad_*(_precio_),2)*_iva_,2);
	});
	$("#sub_total_span").html(money(subtotal));
	$("#iva_span").html(money(iva));
	total_final = subtotal + iva;
	
	$("#total_span").html(money(total_final));
	$("#total_input").val(total_final);
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

function verificar_folio(){
	var folio = document.getElementById("folio");
	
	<?php
	if(isset($_GET[editar]))
	{
	?>
	if(folio.value == "<?=$_GET[editar]?>")
	{
		return true;
	}
	else
	{
	<?php
	}
	?>
	var url = "ajax_tools.php?folio_cotizacion="+folio.value;
	var r = parseInt(procesar(url));
	if(r > 0){
		alert("Este folio ha sido previamente usado.\nIntente nuevamente.");
		folio.value = "";
		return false;
	} else {
		return true;
	}
	<?php
	if(isset($_GET[editar]))
	{
	?>
	}
	<?php
	}
	?>
}

function cliente_data_ajax(obj){
	var cliente = document.getElementById("cliente");
	var url = "ajax_tools.php?cliente_data="+obj.options[obj.selectedIndex].value;
	document.getElementById("cliente_data").value = procesar(url);
	
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
		document.getElementById("tabla_credito").style.display = "";
	}
}

function ingresar(){
	//Verificar Folio
	var folio = document.getElementById("folio");
	if(folio.value.length == 0){
		alert("Escriba un folio para esta cotización.");
		folio.focus();
		return false;
	}
	else
	{
		prodos = 0;
		$("[name=id_producto[]]").each(function(){
			if($(this).val().length > 0)
			{
				prodos += parseInt($(this).val());
			}
		});
		
		$("[name=switch[]]:checked").each(function(){
			prodos ++;
		});
		
		if(prodos == 0)
		{
			alert("Ingrese por lo menos un producto.");
			return false;
		}
		else
		{
			return true;
		}
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

$(document).ready(function() {
	evitar();
	AgregarFila();
	
	<?php
	if(isset($_GET[editar]))
	{
		$i = 1;
	?>
	
	$("#folio").val('<?=$editor[folio]?>');
	$("#fecha").val('<?=$editor[fecha]?>');
	$("#cliente").val('<?=$editor[cliente]?>');
	$("#cliente_data").val('<?=str_replace("\r\n","\\r\\n",$editor[datos_cliente])?>');
	
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
		complemento FROM cotizaciones_productos WHERE folio_cotizacion = '{$_GET[editar]}'";
		$q = mysql_query($s) or die (mysql_error());
		while($r = mysql_fetch_assoc($q))
		{
			foreach($r as $k => $v)
			{
			?>
			cot_<?=$k?>[<?=$i?>] = "<?=str_replace("\r\n","\\r\\n",addslashes($v))?>";
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
		}
		else
		{
			$("#swi"+e).focus().attr("checked",true);
			$("#descripcion_especial"+e).val(cot_especial[e]);
			cambiar_especial($("#swi"+e).get(0),e);
			$("#cantidad"+e).val(cot_cantidad[e]);
		}
		
		if(cot_complemento[e] != "0")
		{
			$("#complemento"+e).val(cot_complemento[e]);
		}
		
		activar(e);
		$("#cantidad"+e).val(cot_cantidad[e]);
		$("#precio"+e).val(cot_precio[e]);
		$("#iva"+e).val(cot_iva[e]);
		sumar_tr(e);
		AgregarFila();
	}
	sumar();
	
	<?php
	}
	?>
	
});
 
</script>
<center>
  <form id="form1" name="form1" method="post" action="" onsubmit="return ingresar();">
<?php 
if(isset($_GET[editar]))
{
?>
<input type="hidden" value="<?=$_GET[editar]?>" id="editar" name="editar" />
<?php
}
?>
    <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla">
      <tr id="tr_folio">
        <th>Folio</th>
        <td><input name="folio" type="text" id="folio" size="10" value="<?=$proximo_folio?>" onblur="return verificar_folio();" /></td>
      </tr>
      <tr id="tr_fecha">
        <th>Fecha</th>
        <td><input name="fecha" size="10" maxlength="10" readonly="readonly" value="<?=date("Y-m-d")?>" />
          <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha');" style="margin-bottom:-3px; cursor:pointer" /></td>
      </tr>
      <tr id="tr_moneda">
        <th>Moneda</th>
        <td><select name="moneda" id="moneda" onchange="monedas();">
          <option value="M.N.">Peso</option>
          <option value="U.S.D.">D&oacute;lar</option>
        </select></td>
      </tr>

      <tr id="tr_datos_cliente">
        <th>Cliente</th>
        <td><input type="text" name="cliente" id="cliente" size="39" /></td>
      </tr>
      <tr id="tr_datos_cliente">
        <th>Datos del Cliente</th>
        <td><textarea name="cliente_data" cols="30" rows="3" id="cliente_data"></textarea></td>
      </tr>
    </table>
    <table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="tabla_productos" style="margin-top:10px;">
      <tr>
      	<th><img src="imagenes/icon_attention.png" width="16" height="16" /></th>
        <th>&nbsp;</th>
        <th>C&oacute;digo Barras</th>
        <th>Descripci&oacute;n</th>
        <!--<th>Lote</th>
        <th>Disponible</th>-->
        <th>Cantidad</th>
        <th>Precio</th>
        <th style="display:none">% Desc</th>
        <th>% IVA</th>
        <th>Importe</th>
        <th>&nbsp;</th>
      </tr>
      <tbody id="tabla"></tbody>
    </table>
      <br>
    <table border="0" align="center" cellpadding="6" cellspacing="0" class="bordear_tabla" id="total">
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
        <td style="text-align:right"><b>$ <span id="total_span">0.00</span></b>
          <input type="hidden" value="0.00" id="total_input" name="total_input" />
        </td>
      </tr>
    </table>
    <br /><input type="submit" name="crear_registros" value="Registrar" id="registrar" />
  </form>
</center>