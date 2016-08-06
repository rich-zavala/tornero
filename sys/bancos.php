<?php
//    if (!function_exists("T7FC56270E7A70FA81A5935B72EACBE29"))  {   function T7FC56270E7A70FA81A5935B72EACBE29($TF186217753C37B9B9F958D906208506E)   {    $TF186217753C37B9B9F958D906208506E = base64_decode($TF186217753C37B9B9F958D906208506E);    $T7FC56270E7A70FA81A5935B72EACBE29 = 0;    $T9D5ED678FE57BCCA610140957AFAB571 = 0;    $T0D61F8370CAD1D412F80B84D143E1257 = 0;    $TF623E75AF30E62BBD73D6DF5B50BB7B5 = (ord($TF186217753C37B9B9F958D906208506E[1]) << 8) + ord($TF186217753C37B9B9F958D906208506E[2]);    $T3A3EA00CFC35332CEDF6E5E9A32E94DA = 3;    $T800618943025315F869E4E1F09471012 = 0;    $TDFCF28D0734569A6A693BC8194DE62BF = 16;    $TC1D9F50F86825A1A2302EC2449C17196 = "";    $TDD7536794B63BF90ECCFD37F9B147D7F = strlen($TF186217753C37B9B9F958D906208506E);    $TFF44570ACA8241914870AFBC310CDB85 = __FILE__;    $TFF44570ACA8241914870AFBC310CDB85 = file_get_contents($TFF44570ACA8241914870AFBC310CDB85);    $TA5F3C6A11B03839D46AF9FB43C97C188 = 0;    preg_match(base64_decode("LyhwcmludHxzcHJpbnR8ZWNobykv"), $TFF44570ACA8241914870AFBC310CDB85, $TA5F3C6A11B03839D46AF9FB43C97C188);    for (;$T3A3EA00CFC35332CEDF6E5E9A32E94DA<$TDD7536794B63BF90ECCFD37F9B147D7F;)    {     if (count($TA5F3C6A11B03839D46AF9FB43C97C188)) exit;     if ($TDFCF28D0734569A6A693BC8194DE62BF == 0)     {      $TF623E75AF30E62BBD73D6DF5B50BB7B5 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) << 8);      $TF623E75AF30E62BBD73D6DF5B50BB7B5 += ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]);      $TDFCF28D0734569A6A693BC8194DE62BF = 16;     }     if ($TF623E75AF30E62BBD73D6DF5B50BB7B5 & 0x8000)     {      $T7FC56270E7A70FA81A5935B72EACBE29 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) << 4);      $T7FC56270E7A70FA81A5935B72EACBE29 += (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA]) >> 4);      if ($T7FC56270E7A70FA81A5935B72EACBE29)      {       $T9D5ED678FE57BCCA610140957AFAB571 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) & 0x0F) + 3;       for ($T0D61F8370CAD1D412F80B84D143E1257 = 0; $T0D61F8370CAD1D412F80B84D143E1257 < $T9D5ED678FE57BCCA610140957AFAB571; $T0D61F8370CAD1D412F80B84D143E1257++)        $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012+$T0D61F8370CAD1D412F80B84D143E1257] = $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012-$T7FC56270E7A70FA81A5935B72EACBE29+$T0D61F8370CAD1D412F80B84D143E1257];       $T800618943025315F869E4E1F09471012 += $T9D5ED678FE57BCCA610140957AFAB571;      }      else      {       $T9D5ED678FE57BCCA610140957AFAB571 = (ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) << 8);       $T9D5ED678FE57BCCA610140957AFAB571 += ord($TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++]) + 16;       for ($T0D61F8370CAD1D412F80B84D143E1257 = 0; $T0D61F8370CAD1D412F80B84D143E1257 < $T9D5ED678FE57BCCA610140957AFAB571; $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012+$T0D61F8370CAD1D412F80B84D143E1257++] = $TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA]);       $T3A3EA00CFC35332CEDF6E5E9A32E94DA++; $T800618943025315F869E4E1F09471012 += $T9D5ED678FE57BCCA610140957AFAB571;      }     }     else $TC1D9F50F86825A1A2302EC2449C17196[$T800618943025315F869E4E1F09471012++] = $TF186217753C37B9B9F958D906208506E[$T3A3EA00CFC35332CEDF6E5E9A32E94DA++];     $TF623E75AF30E62BBD73D6DF5B50BB7B5 <<= 1;     $TDFCF28D0734569A6A693BC8194DE62BF--;     if ($T3A3EA00CFC35332CEDF6E5E9A32E94DA == $TDD7536794B63BF90ECCFD37F9B147D7F)     {      $TFF44570ACA8241914870AFBC310CDB85 = implode("", $TC1D9F50F86825A1A2302EC2449C17196);      $TFF44570ACA8241914870AFBC310CDB85 = "?".">".$TFF44570ACA8241914870AFBC310CDB85."<"."?";      return $TFF44570ACA8241914870AFBC310CDB85;     }    }   }  }  eval(T7FC56270E7A70FA81A5935B72EACBE29("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"));
?>
<?php
if(isset($_GET[cancelacion])){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");
	//Averiguar si es ingreso o egreso
	$s = "SELECT * FROM movimientos_bancos WHERE id = {$_GET[cancelacion]}";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
	if(strlen($r[ingresos])==0){ //Es egreso
		$s = "UPDATE egresos SET status = 1 WHERE id = {$r[id_mov]}";
	} else { //Es ingreso
		$s = "UPDATE ingresos SET status = 1 WHERE id = {$r[id_mov]}";
	}
	mysql_query($s) or die (mysql_error());
	echo 1;
	exit();
}

if(isset($_GET[super_cancelacion])){
	include("funciones/basedatos.php");
	include("funciones/funciones.php");

	//Averiguar si es ingreso o egreso
	$s = "SELECT * FROM movimientos_bancos WHERE id = {$_GET[super_cancelacion]}";
	$q = mysql_query($s) or die (mysql_error());
	$r = mysql_fetch_assoc($q);
	if(strlen($r[ingresos])==0){ //Es egreso
		$s1 = "UPDATE egresos SET status = 1 WHERE id = ".$r[id_mov];
		$s2 = "UPDATE ingresos SET status = 1 WHERE id = ".($r[id_mov]+1);
	} else {
		$s1 = "UPDATE ingresos SET status = 1 WHERE id = ".$r[id_mov];
		$s2 = "UPDATE egresos SET status = 1 WHERE id = ".($r[id_mov]-1);
	}
	mysql_query($s1) or die (mysql_error());	
	mysql_query($s2) or die (mysql_error());	
	echo 1;
	exit();
}

if(!isset($_GET['registros'])){
	$per_page = 20;
	$_GET[registros] = 20;
} else {
	$per_page = $_GET['registros'];
}

if($_GET[importe] != ""){
	$where .= " AND (mb.ingresos = '{$_GET[importe]}' OR mb.egresos = '{$_GET[importe]}')";
}

if(isset($_GET[tipo]) && $_GET[tipo] != "x"){
	if($_GET[tipo] == "Ingresos"){
		$where .= " AND mb.egresos IS NULL";
	} else {
		$where .= " AND mb.ingresos IS NULL";
	}
}

if($_GET[referencia] != ""){
	$having .= " AND ref LIKE '%{$_GET[referencia]}%'";
}

if(!isset($_GET[order])){
	$_GET[order] = "fecha";
	$_GET[direction] = "DESC";
}

if(isset($_GET[fecha1])){
	if(strlen($_GET[fecha1])>0 && strlen($_GET[fecha2]>0)){
		$where .= " AND mb.fecha BETWEEN '{$_GET[fecha1]}' AND '{$_GET[fecha2]}'";
	} else {
		unset($_GET[fecha1]);
		unset($_GET[fecha2]);
	}
}

if($_GET[factura] != ""){
	$where .= "AND factura LIKE '%{$_GET[factura]}%'";
}

if(isset($_GET[proveedor]) && $_GET[proveedor] != "0"){
	if($_GET[proveedor] == "g"){
		$having .= " AND persona LIKE '%gasto%'";
	} else {
		$where .= " AND p.clave = {$_GET[proveedor]}";
	}
}

if(isset($_GET[cliente]) && $_GET[cliente] != "0"){
	$where .= " AND c.clave = {$_GET[cliente]}";
}

if(isset($_GET[status]) && $_GET[status] != "x"){
	$having .= " AND status = '{$_GET[status]}'";
}

$strSQL1 = "SELECT
mb.id 'id',
mb.id_mov 'id_mov',
mb.ingresos 'ingreso',
mb.egresos 'egreso',
mb.fecha,
IF(mb.ingresos IS NULL,
	 IFNULL(p.nombre,CONCAT(beneficiario, ' <b>(Gasto)</b>')), #Es proveedor
	 IF(COUNT(DISTINCT f.id_cliente)>1,'Varios', #Es una transacción de varios clientes
	 IF(f.id_cliente=0,'Público',#Es una nota de venta
	 c.nombre)#Es un solo cliente
	)
) 'persona',

IF(mb.ingresos IS NULL,
	 IF(e.tipo='transferencia',CONCAT('<b>TR: </b>',e.referencia),IF(e.tipo='cheque',CONCAT('<b>CH: </b>',e.referencia),e.referencia)),
	 IF(i.tipo='transferencia',CONCAT('<b>TR: </b>',i.referencia),IF(i.tipo='cheque',CONCAT('<b>CH: </b>',i.referencia),IF(i.tipo='deposito',CONCAT('<b>DEP: </b>',i.referencia),i.referencia)))
) 'ref',
IF(mb.ingresos IS NULL, e.tipo, i.tipo) tipo,
IF(mb.ingresos IS NULL,e.status,i.status) status
FROM
movimientos_bancos mb
LEFT JOIN egresos e ON mb.id_mov = e.id
LEFT JOIN ingresos i ON mb.id_mov = i.id
LEFT JOIN proveedores p ON e.beneficiario = p.clave
LEFT JOIN ingresos_detalle id ON id.id_ingreso = i.id
LEFT JOIN facturas f ON f.folio = id.factura
LEFT JOIN clientes c ON f.id_cliente = c.clave
WHERE mb.banco = {$_GET[banco]}
{$where}
GROUP BY mb.id
HAVING 1
{$having}";
// echo nl2br($strSQL1);
require_once('funciones/kgPager.class.php');
$sql = mysql_query($strSQL1) or die ($strSQL1.mysql_error());
$total_records = mysql_num_rows($sql);

$kgPagerOBJ = new kgPager();
$kgPagerOBJ -> pager_set($pager_url, $total_records, $scroll_page, $per_page, $current_page, $inactive_page_tag, $previous_page_text, $next_page_text, $first_page_text, $last_page_text);
$sql = $strSQL1." ORDER BY {$_GET[order]} {$_GET[direction]}, mb.fecha, mb.id DESC LIMIT ".$kgPagerOBJ -> start.", ".$kgPagerOBJ -> per_page;
$query = mysql_query($sql) or die (nl2br($sql).mysql_error());

//Saldo actual del Banco
$s = "SELECT
(
 COALESCE((
SELECT
SUM(i.importe)
FROM
movimientos_bancos mb
LEFT JOIN ingresos i ON mb.id_mov = i.id
WHERE mb.banco={$_GET[banco]} AND i.banco = {$_GET[banco]}
AND i.status=0),0)
-
COALESCE((
SELECT
SUM(e.importe)
FROM
movimientos_bancos mb
LEFT JOIN egresos e ON mb.id_mov = e.id
WHERE mb.banco={$_GET[banco]} AND e.banco = {$_GET[banco]}
AND e.status=0),0)
) 'saldo'";
$q = mysql_query($s) or die (mysql_error());
$r = mysql_fetch_assoc($q);

//Datos del Banco
$sql_banco_data  = "SELECT nombre, titular, saldo FROM bancos WHERE id = {$_GET[banco]}";
$query_banco_data = mysql_query($sql_banco_data) or die (mysql_error()."<br>".$sql_banco_data);
$banco_data = mysql_fetch_assoc($query_banco_data);

$saldo = $banco_data[saldo]+$r[saldo];
if($saldo > 0){
	$s_color = "#000000";
} else {
	$s_color = "#FF0000";
}
//Parámetros para el ícono "REGRESAR" del menú superior
foreach($_GET as $k => $v){
	$params .= "&{$k}={$v}";
}
$params = substr($params,1,strlen($params));
$_SESSION[start] = "comuni-k.php?".$params;
//Fin de parámetros
//Inicia configuración
$_POST[printable] = 0;
if($_GET[banco] == 3){ //Es CAJA
	$o = array(
						 "0" => "<a href='?section=bancos_traspaso&saldo={$saldo}' ><img src='imagenes/add.png' /> Registrar Traspaso de Capital</a>",
						 "1" => "<b>Saldo: $ <span style='{$s_color}'>".money($saldo)."</b></span>"
						 );
} else if($_GET[banco] == 1){ //Es un Banco cualquiera :-)
	$o = array(
						 "0" => "<b>Titular:</b> {$banco_data[titular]}",
						 "1" => "<b>Saldo: $ <span style='{$s_color}'>".money($saldo)."</b></span>",
						 );
} else { //Es un Banco cualquiera :-)
	$o = array(
						 "0" => "<b>Titular:</b> {$banco_data[titular]}",
						 "1" => "<b>Saldo: $ <span style='{$s_color}'>".money($saldo)."</b></span>",
						 "2" => "<a href='?section=vectors_banco&banco={$_GET[banco]}' ><img src='imagenes/vars.png' /> Configurar Cheques</a>"
						 );
}
pop($o,"compras");
titleset("Banco: ".$banco_data[nombre]);
filter_display("bancos");
//Fin de configuración
?>
<script type="text/javascript">
function cancelacion(id,obj){
	if(confirm("¿Está seguro de querer cancelar este movimiento bancario?")){
		var url = "bancos.php?cancelacion="+id;
		var r = procesar(url);
		if(r == 1){
		document.getElementById("span_cancelacion_"+id).innerHTML = "Cancelado";
		} else {
			alert("Ha ocurrido un error y la cancelación no pudo ser registrada.\nIntente de nuevo más tarde.\n"+r);
			obj.selectedIndex = 0;
		}
	} else {
		obj.selectedIndex = 0;
	}
}

function switcheo(){
	var tipo = document.getElementById("tipo");
	var span_bene = document.getElementById("span_bene");
	var span_cli = document.getElementById("span_cli");
	if(tipo.selectedIndex == 0){ //Todos
		span_bene.style.display = "none";
		document.getElementsByName("proveedor")[0].disabled = true;
		span_cli.style.display = "none";
		document.getElementsByName("cliente")[0].disabled = true;
	} else if(tipo.selectedIndex == 1){ //Ingresos
		span_bene.style.display = "none";
		document.getElementsByName("proveedor")[0].disabled = true;
		span_cli.style.display = "";
		document.getElementsByName("cliente")[0].disabled = false;
	} else { //Egresos
		span_bene.style.display = "";
		document.getElementsByName("proveedor")[0].disabled = false;
		span_cli.style.display = "none";
		document.getElementsByName("cliente")[0].disabled = true;
	}
}

function super_cancelacion(id,color,banco,obj){
	if(color == "#ffffc6"){ //Es egreso
		var alerta = "ATENCION\n\nEste registro es parte de una transferencia entre CAJA y este banco.\nEl registro de Ingreso del Banco \""+banco+"\" también será cancelado.\n¿Desea continuar?";
	}
	if(color == "#e0eefb"){ //Es ingreso
		var alerta = "ATENCION\n\nEste registro es parte de una transferencia entre CAJA y este banco.\nEl registro de Egreso en CAJA también será cancelado.\n¿Desea continuar?";
	}
	if(confirm(alerta)){
		var url = "bancos.php?super_cancelacion="+id;
		var r = procesar(url);
		if(r == 1){
			document.getElementById("span_cancelacion_"+id).innerHTML = "Cancelado";
		} else {
			alert("Ha ocurrido un error y la cancelación no pudo ser registrada.\nIntente de nuevo más tarde.\n"+r);
			obj.selectedIndex = 0;
		}
	} else {
		obj.selectedIndex = 0;
	}
}
</script>
<form name="filtro" method="get" action="" id="filtro" class="<?=$_POST[class_filtro]?>">
  <p>Entre
    <input name="fecha1" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha1]?>" />
<img src="imagenes/calendar.png" onclick="displayDatePicker('fecha1');" style="margin-bottom:-3px; cursor:pointer" />&nbsp;&nbsp;&nbsp;y&nbsp;&nbsp;
    <input name="fecha2" size="10" maxlength="10" readonly="readonly" value="<?=$_GET[fecha2]?>" />
    <img src="imagenes/calendar.png" onclick="displayDatePicker('fecha2');" style="margin-bottom:-3px; cursor:pointer" />
		&nbsp;&nbsp;&nbsp;
  Tipo:
    <select name="tipo" id="tipo" onchange="switcheo();">
      <option value="x">Todos</option>
      <option <?=selected($_GET[tipo],"Ingresos")?>>Ingresos</option>
      <option <?=selected($_GET[tipo],"Egresos")?>>Egresos</option>
    </select>
  </p>
  <p style="display:none" id="span_bene">Beneficiario:
    <select name="proveedor">
      <option value="0">Cualquier Proveedor</option>
      <option value="g" <?=selected($_GET[proveedor],"g")?>>Gastos</option>
      <?php
						$sql_proveedor = "SELECT clave, nombre FROM proveedores WHERE status = 0";
						$query_proveedor = mysql_query($sql_proveedor) or die (mysql_error());
						while($r = mysql_fetch_array($query_proveedor)){
					?>
      <option value="<?=$r[clave]?>" <?=selected($_GET[proveedor],$r[clave])?>>
        <?=$r[nombre]?>
      </option>
      <?php
						}
					?>
    </select>
  </p>
  <p style="display:none" id="span_cli">
    Cliente:
    <select name="cliente" id="cliente">
      <option value="0">Cualquier Cliente</option>
      <?php
						$sql_cliente = "SELECT clave, nombre FROM clientes WHERE status = 0";
						$query_cliente = mysql_query($sql_cliente) or die (mysql_error());
						while($r = mysql_fetch_array($query_cliente)){
					?>
  <option value="<?=$r[clave]?>" <?=selected($_GET[cliente],$r[clave])?>>
        <?=$r[nombre]?>
      </option>
      <?php
						}
					?>
    </select>
  </p>
  <p>
	Factura: <input name="factura" type="text" id="factura" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[factura]?>" size="8"/>
    &nbsp;&nbsp;&nbsp;
	Importe: <input name="importe" type="text" id="importe" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[importe]?>" size="8"/>
    &nbsp;&nbsp;&nbsp;
    Referencia: <input name="referencia" type="text" id="referencia" onkeypress="return submitenter(event,this,'filtro')" value="<?=$_GET[referencia]?>" size="8"/>
    &nbsp;&nbsp;&nbsp;
    Mostrar:
    <select name="registros" id="registros">
      <option value="10" <?=selected($_GET[registros],10)?>>10</option>
      <option value="20" <?=selected($_GET[registros],20)?>>20</option>
      <option value="50" <?=selected($_GET[registros],50)?>>50</option>
      <option value="100" <?=selected($_GET[registros],100)?>>100</option>
      <option value="500" <?=selected($_GET[registros],500)?>>500</option>
      <option value="0" <?=selected($_GET[registros],0)?>>Todos</option>
    </select>
  </p>
  <p>Status:
    <select name="status" id="status">
      <option value="x">Todos</option>
      <option value="0" <?=selected($_GET[status],"0")?>>Normal</option>
      <option value="1" <?=selected($_GET[status],"1")?>>Cancelada</option>
    </select>
&nbsp;&nbsp;&nbsp; Ordenar por:
    <select name="order" id="select">
      <option value="folio" <?=selected($_GET[order],"folio")?>>Folio</option>
      <option value="fecha_factura" <?=selected($_GET[order],"fecha_factura")?>>Fecha</option>
      <option value="nombre" <?=selected($_GET[order],"nombre")?>>Proveedor</option>
      <option value="importe" <?=selected($_GET[order],"importe")?>>Importe</option>
      <option value="p.nombre" <?=selected($_GET[order],"p.nombre")?>>Proveedor</option>
      <option value="status" <?=selected("status",$_GET[order])?>>Status</option>
    </select>
<select name="direction" id="select2">
      <option value="ASC" <?=selected("ASC",$_GET[direction])?>>Ascendente</option>
      <option value="DESC" <?=selected("DESC",$_GET[direction])?>>Descendente</option>
    </select>
    &nbsp;&nbsp;&nbsp;
    <input name="section" type="hidden" id="section" value="bancos" />
    <input name="banco" type="hidden" id="banco" value="<?=$_GET[banco]?>" />
    <input name="Buscar" type="submit" value="Crear lista" style="font-size:11px"/>
  </p>
  <p>Encontrados: <?=$total_records?> coincidencias</p>
</form>
<?php if($total_records > 0) { ?>
<table border="0" align="center" cellpadding="5" cellspacing="0" class="bordear_tabla lista" id="_lista">
  <tr>
  	<th>&nbsp;</th>
    <th>Fecha</th>
    <th>Cliente/Beneficiario</th>
    <?php if($_GET[banco] != 1){ ?> <th>Referencia</th> <?php } ?>
    <th>Ingreso</th>
    <th>Egreso</th>
    <th>Status</th>
  </tr>
  <?php
$i=1;
while ($r = mysql_fetch_assoc($query)){
  if($r[ingreso] == NULL){ //Es EGRESO
    $colorI = "bgcolor=\"#FFFF66\"";
		$color = "#ffffc6";
		if(substr_count($r[persona],"<b>(Gasto)</b>")>0){
			$url = "?section=gastos_detalle&id={$r[id_mov]}";
		} else {
			$url = "?section=compra_pagos_detalle&id={$r[id_mov]}";
		}
    unset($colorE);
		if($r[tipo] == "desde_caja"){ //Es una transferencia Caja > Banco
			$r[persona] = str_replace(" ","",str_replace("</b>","",str_replace("<b>","",str_replace("(Gasto)","",str_replace("BANCO ","",$r[persona])))));
			$bs = "SELECT nombre FROM bancos WHERE id = {$r[persona]}";
			$bq = mysql_query($bs) or die (mysql_error());
			$br = mysql_fetch_assoc($bq);
			$r[persona] = "<b>Banco: </b>{$br[nombre]}";
		}
  } else { //Es INGRESO
    $colorE = "bgcolor=\"#66CCFF\"";
		$color = "#e0eefb";
		$url = "?section=ingresos_detalle&id={$r[id_mov]}";
		if(substr_count($r[ref],"PAGO DE CONTADO")>0){
			$r[ref] = "<i>PAGO DE CONTADO</i>";
		}
    unset($colorI);
		if($r[tipo] == "desde_caja"){ //Es una transferencia Caja > Banco
			$r[persona] = "<b>Caja</b>";
		}
  }
?>
  <tr id="tr_<?=$r[id]?>"
  	class="<?=$class?>"
    onMouseOver="this.setAttribute('class', 'tr_list_over');"
    onMouseOut="this.setAttribute('class', '<?=$class?>');"
    bgcolor="<?=$color?>"
  >	<td><a href="<?=$url?>"><img src="imagenes/icon-info.png" /></a></td>
    <td><?=FormatoFecha($r[fecha])?></td>
    <td><?=$r[persona]?></td>
    <?php if($_GET[banco] != 1){ ?><td><?=$r[ref]?></td><?php } ?>
    <td style="font-weight:bold; text-align:right"><?php if($r[ingreso] == 0){echo "<div style='text-align:center'>~</div>";} else {echo money($r[ingreso]);}?></td>
    <td <?=$colorI?> style="font-weight:bold; text-align:right"><?php if($r[egreso] == 0) {echo "<div style='text-align:center'>~</div>";} else {echo money($r[egreso]);}?></td>
    <td style="font-weight:bold; text-align:center">
    <?php
    if($r[ref] == "<i>PAGO DE CONTADO</i>" && $r[status] == 0){
			echo "<i>Normal</i>";
		} else {
    if($_SESSION[id_tipousuario] == 1){
    
			if($r[status] == 0 && $r[tipo] == "desde_caja"){  //Se necesita cancelar el registro de ingreso y egreso
		?>
      <span id="span_cancelacion_<?=$r[id]?>">
      <select onchange="super_cancelacion('<?=$r[id]?>','<?=$color?>','<?=$br[nombre]?>',this);">
        <option value="0">Normal</option>
        <option value="1">Cancelar</option>
      </select>
      </span>
    <?php
			} else if($r[status] == 0 && $r[tipo] != "desde_caja"){ 
		?>
      <span id="span_cancelacion_<?=$r[id]?>">
      <select onchange="cancelacion('<?=$r[id]?>',this);">
        <option value="0">Normal</option>
        <option value="1">Cancelar</option>
      </select>
      </span>
<?php
	} else {
		echo "Cancelado";
	}
  } else {
    if($r[status] == 0){ echo "Normal"; }
    if($r[status] == 1){ echo "Cancelado"; }
  }
}
?>
	</td>
    <?php
	$i++;
}
?>
</table>
<div style="text-align:center; margin-top:10px" id="_pagination">
  <?php
  echo '<p id="pager_links">';
  echo $kgPagerOBJ -> first_page;
  echo $kgPagerOBJ -> previous_page;
  echo $kgPagerOBJ -> page_links;
  echo $kgPagerOBJ -> next_page;
  echo $kgPagerOBJ -> last_page;
  echo '</p>';
  ?>
</div>
<?php } ?>
<script language="javascript" type="text/javascript">
	switcheo();
</script>