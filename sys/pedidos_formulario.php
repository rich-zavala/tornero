<?php
$com_text = "Use este campo para escribir un complemento de la descripción del producto.";
if (isset($_POST[id_producto])) {
	if(isset($_POST[editar]))
	{
		mysql_query("DELETE FROM pedidos WHERE folio = '{$_POST[editar]}'") or die (mysql_error());
	}
	
	//Control de errores
	$index = array();
	foreach($_POST[id_producto] as $k=> $v){
		if(strlen($_POST[descripcion_especial][$k]) == 0 && strlen($v) >= 0)
		{
			if(!in_array($v,$index) && strlen($v) > 0){
				$index[] = $v;
			} else {
				unset($_POST[id_producto][$k]);
				unset($_POST[cantidad][$k]);
			}
		}
	}
	//print_pre($_POST);
	//Fin de control de errorres
	if(count($_POST[id_producto])>0){
		foreach($_POST[id_producto] as $k=> $v){
			//if($v != ""){
				if(strlen($v) == 0)
				{
					$v = 0;
				}
				
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
				
				if($complemento == "Use este campo para escribir un complemento de la descripción del producto.")
				{
					$complemento = "";
				}
				
				$s = "INSERT INTO pedidos VALUES(
							NULL,
							'{$_POST[folio]}',
							'$_POST[fecha]',
							'{$_POST[id_almacen]}',
							'{$_POST[comentario]}',
							'{$v}',
							'{$_POST[cantidad][$k]}',
							'{$_POST[costo][$k]}',
							'{$_POST[iva][$k]}',
							'{$_POST[sub_importe][$k]}',
							'{$_POST[importe][$k]}',
							'{$_POST[proveedor]}',
							0,
							'{$especial}',
							'{$complemento}',
							'{$_POST[tipo_doc]}',
							NULL,
							'$_POST[moneda]',
							0
							)";
				//echo nl2br($s)."<p>";
				mysql_query($s) or die (nl2br($s)." - ".mysql_error());
			//}
		}
		relocation("?section=pedidos_detalle&id={$_POST[folio]}");
	} else {
		$error = "No se hayaron productos para realizar un pedido.";
	}
}

if(isset($_GET[editar]))
{
	$proximo_folio = $_GET[editar];
}
else
{
	$s = "SELECT folio FROM pedidos ORDER BY folio DESC";
	$q = mysql_query($s);
	$r = mysql_fetch_assoc($q);
	$proximo_folio = sprintf("%05d",intval($r[folio])+1);
}

//Inicia configuración
titleset("Registro de Pedidos");
//Fin de configuración

$check = mysql_query("SELECT * FROM proveedores WHERE status = 0"); if(mysql_num_rows($check)>0){ ?>
<script>
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
  row.setAttribute("class",color);
	row.onmouseover = function(){this.setAttribute('class','tr_list_over');}
	row.onmouseout = function(){this.setAttribute('class',color);}
  
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
	$(complemento).css("width","200px").css("height","39px").attr("id","complemento"+num).attr("name","complemento[]").val(com_text).focus(function(){if($(this).val() == com_text){$(this).val("");}});
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
	iva.value= "16";
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

function cambiar_especial(obj,n){
	resetear(n);
	if(obj.checked){ // Cambiar a Especial
		document.getElementById("cantidad"+n).onblur = null;
		document.getElementById("costo"+n).onblur = null;
	
		$("#id_producto"+n).val("");
		$("#id_producto"+n).val("");
		$("#complemento"+n).attr("disabled",false);
		$("#buscar"+n).css("display","none");
		$("#barras"+n).val("").css("display","none");
		//$("#lote"+n).find('option').remove().end().append('<option>'+Math.floor(Math.random()*11)+'</option>').css("display","none");
		//$("#disponible"+n).val("0.000").css("display","none");
		$("#descripcion"+n).css("display","none").find('option').remove().end().css("display","none");
		$("#descripcion_especial"+n).css("display","");
		$("#cantidad"+n).blur(function(){numero(this,3); sumar_tr(n); sumar();}).attr("readonly","");
		$("#costo"+n).blur(function(){numero(this,2); sumar_tr(n); sumar();}).attr("readonly","");
		$("#iva"+n).attr("readonly","");
	} else {
		$("#tr_"+n).remove();
		AgregarFila();
		sumar();
	}
}

function resetear(num){
  document.getElementById("control"+num).value = "~~~Campo de Control~~~";
  document.getElementById("id_producto"+num).value = "";
	document.getElementById("barras"+num).value = "";
  document.getElementById("descripcion"+num).length = 0;
  document.getElementById("descripcion"+num).options[0] = new Option("Escriba un código de barras.");
	document.getElementById("cantidad"+num).value = "0";
  document.getElementById("costo"+num).value = "0.00";
	document.getElementById("iva"+num).value = "16.00";
	document.getElementById("importe"+num).value = "0.00";
	
	sumar();
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
	/*
	var tbody = document.getElementById("tabla_productos").getElementsByTagName("tbody")[0];
	var filas = tbody.rows.length-1; // Le restamos 1 porque no tomamos en cuenta los encabezados
	while(obj.tagName != "TR"){
		obj = obj.parentNode;
		if(obj.id == "tr_"+filas){
			AgregarFila();
		}
	}*/
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
  var barras = document.getElementById("barras"+num);
	var descripcion = document.getElementById("descripcion"+num);
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
			
      cantidad.readOnly = false;
      costo.readOnly = false;
      iva.readOnly = false;
		} else {
			alert("No se pudo encontrar este producto.\n"+obj.value);
			resetear(num);
		}
	}
	if(obj.value.length == 0){
		resetear(num);
	}
}

function actualizar_campos(obj,num){
	var index = obj.selectedIndex;
  var id_producto = document.getElementById("id_producto"+num);
  id_producto.value = obj.options[index].value.split("~")[index];
}

function sumar_tr(num){
	var cantidad = parseFloat(document.getElementById("cantidad"+num).value);
	var costo = parseFloat(document.getElementById("costo"+num).value);
	var iva = parseFloat(document.getElementById("iva"+num).value)/100;
	var sub_importe = document.getElementById("sub_importe"+num);
	var importe = document.getElementById("importe"+num);
	
	var sub_importe_v = round(cantidad*costo,2);
	var importe_v =  round(cantidad*costo,2)+ round(cantidad*(costo*iva),2);  
	
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

function checar_folio(){
	var folio = document.getElementById("folio_factura").value;
	
	if(folio != ""){	
		<?php
		if(isset($_GET[editar]))
		{
		?>
		if(folio == "<?=$_GET[editar]?>")
		{
			return true;
		}
		else
		{
		<?php
		}
		?>		
		url = "ajax_tools.php?pedido="+folio;		
		var r = procesar(url);		
		if(r == "1"){
			alert("Este número de folio ya existe.\nIntente con otro diferente.");		
			//modificación hecha el 09-05-2011 by chris
			if($.browser.mozilla){
				setTimeout(function(){document.getElementById("folio_factura").focus()},10); 
				document.getElementById("folio_factura").select();
			}else{
			document.getElementById("folio_factura").focus();					
			document.getElementById("folio_factura").select();
			}
			//termina modificación
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
}

function ingresar(){
	var total = document.getElementById("total").value;
	var folio_factura = document.getElementById("folio_factura").value;
	
	if(folio_factura == ""){
		alert("Define el folio de esta compra.");
		document.getElementById("folio_factura").focus();
		document.getElementById("folio_factura").select();
		return false;
	}
	//Verificar productos repetidos
	var id_productos = document.getElementsByName("id_producto[]");
	var index = new Array();
	/*for(var i=0;id_productos.length>i;i++){
		if(!in_array(id_productos[i].value,index) && id_productos[i].value.length>0){
			index[i] = id_productos[i].value;
		} else if(in_array(id_productos[i].value,index) && id_productos[i].value.length>0){
			alert("Existe un producto repetido.\nVerifique la información.");
			return false;
		}
	}*/

	if(checar_folio()){
		document.getElementById("form1").submit();
	}
}

$(document).ready(function(){
	<?php
  if(isset($error)){echo "alert(\"{$error}\");";}
  ?>
	AgregarFila();
	<?php
	if(isset($_GET[editar]))
	{
	?>
	
	pedido_data = procesar("ajax_tools.php?pedido_compra=<?=$_GET[editar]?>");
	lineas = pedido_data.split("\n");
	$("#proveedor > option").each(function(){
		if($(this).val() == lineas[0])
		{
			$(this).attr("selected",true);
			return false;
		}
	});
	
	$("#id_almacen > option").each(function(){
		if($(this).val() == lineas[1])
		{
			$(this).attr("selected",true);
			return false;
		}
	});
	
	$("#fecha").val(lineas[2]);
	var comentario = lineas[3].replace(/~/g,"\r\n");
	$("#comentario").val(comentario);

	linlen = lineas.length;
	
	for(x = 4; x < linlen; x++)
	{
		info = lineas[x].split("~");
		table = $("#tabla_productos");
		if(info[0] != "0")
		{
			$("#id_producto"+n).val(info[0]);
			buscar_datos(document.getElementById("id_producto"+n),n,"id_producto");		
		}
		else
		{
			$("#swi"+n).attr("checked",true);
			cambiar_especial($("#swi"+n).get(0),n);
			$("#descripcion_especial"+n).val(info[4]);
		}
		if(info[5].length > 0 && info[5] != "0")
		{
			$("#complemento"+n).val(info[5]);
		}
		$("#cantidad"+n).val(info[1]);
		$("#costo"+n).val(info[2]);
		$("#iva"+n).val(info[3]);
		sumar_tr(n);
		AgregarFila();
	}
	
	sumar();
	
	<?php
	}
	?>
});
</script>
<form action="" method="post" name="form1" id="form1">
	<?php
	if(isset($_GET[editar]))
	{
	?>
  
  <input type="hidden" value="<?=$_GET[editar]?>" name="editar" />
  
	<?php
	}
	?>
  <table border="0" align="center" cellpadding="4" cellspacing="0" class="bordear_tabla" id="formulario">
    <tr>
      <th>Proveedor:</th>
      <td><select name="proveedor" id="proveedor">
          <?php
						$sql_proveedor = "SELECT clave, nombre FROM proveedores WHERE status = 0";
						$query_proveedor = mysql_query($sql_proveedor) or die (mysql_error());
						while($r = mysql_fetch_array($query_proveedor)){
					?>
          <option value="<?=$r[clave]?>">
          <?=$r[nombre]?>
          </option>
          <?php } ?>
        </select></td>
    </tr>
    <tr>
      <th>Folio:</th>
      <td><input type="text" name="folio" value="<?=$proximo_folio?>" size="10" id="folio_factura" onblur="checar_folio();" style='font-size:11px'/></td>
    </tr>
    <!------- MOD 07-09-11 -------->
    <tr>
    	<th>Tipo</th>
    	<td>
        	<select id="tipo_doc" name="tipo_doc">
            	<option value="p">Pedido</option>
                <option value="c">Cotizaci&oacute;n</option>
            </select>
        </td>
    </tr>
    <!------- /MOD 07-09-11 -------->
      <tr>
        <th>Fecha</th>
        <td><input name="fecha" id="fecha" size="10" maxlength="10" readonly="readonly" value="<?=date("Y-m-d")?>" />
          <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha');" style="margin-bottom:-3px; cursor:pointer" /></td>
      </tr>
    <tr>
      <th>Almac&eacute;n:</th>
      <td><select name="id_almacen" id="id_almacen">
          <?php foreach($_SESSION[almacenes] as $k => $v){ echo "<option value=\"{$v[id]}\" ".selected($_GET['almacen'],$v[id]).">{$v[descripcion]}</option>\r\n"; } ?>
        </select></td>
    </tr>
    </tr>
      <tr id="tr_moneda">
        <th>Moneda</th>
        <td><select name="moneda" id="moneda">
          <option value="M.N.">Peso</option>
          <option value="U.S.D.">D&oacute;lar</option>
        </select></td>
      </tr>
    <tr>
      <th>Comentarios:</th>
      <td><textarea name="comentario" id="comentario" cols="45" rows="3" style='font-size:11px'></textarea></td>
    </tr>
  </table>
  <br />
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="tabla_productos">
          <tbody id="tabla">
            <tr>
	            <th><img src="imagenes/icon_attention.png" width="16" height="16" /></th>
              <th>&nbsp;</th>
              <th>C&oacute;digo Barras</th>
              <th>Descripcion</th>
              <th>Cantidad</th>
              <th>Costo U.</th>
              <th>% IVA</th>
              <th>Sub-Importe</th>
              <th>Importe</th>
              <th>&nbsp;</th>
            </tr>
          </tbody>
        </table>
<br /><table border="0" align="center" cellpadding="6" cellspacing="0" class="bordear_tabla" id="formulario">
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
            <td style="text-align:right"><b>$ <span id="total_span">0.00</span></b></td>
          </tr>
        </table>
        <input name="total" type="hidden" id="total" value="0" />
        <p style="text-align:center">
          <input type="button" value="Generar Pedido" onclick="ingresar();"/>
        </p>
</form>
<?php } else { ?>
<center><b>No hay proveedores disponibles.</b></center>
<?php } ?>