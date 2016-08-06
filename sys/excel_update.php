<?php
if($_FILES[userfile][error] > 0){
  relocation("comuni-k.php?section=vars&error=2");
  exit();
}

$arr = array(
					"clientes" => array(
						"titulos" => array("Nombres", "Tel&eacute;fonos","Celular","Calle","No. Exterior","No. Interior","Colonia","Localidad","Municipio", "Estado", "C&oacute;digo Postal", "RFC", "Cr&eacute;dito", "Grupo", "D&iacute;as Cr&eacute;dito","E-mail"),
						"campos" => array("nombre", "telefonos", "telefono2", "calle", "noexterior", "nointerior","colonia", "localidad","municipio", "estado", "cp", "rfc", "credito", "grupo", "dias_credito", "email"),
						"width" => array("150px","70px","70px","50px","40px","40px","120px","70px","70px","70px","40px","70px","50px","50px","30px","120px")
					
					),
					"productos" => array(
						"titulos" => array("Descripci&oacute;n", "C&oacute;digo de Barras", "Precio P&uacute;blico", "IVA"),
						"campos" => array("descripcion", "codigo_barras", "precio_publico", "iva"),
						"width" => array("300px","100px","45px","30px" )
					
					),
					"proveedores" => array(
					"titulos" => array("Nombres", "Tel&eacute;fonos", "Direcci&oacute;n", "Estado / Regi&oacute;n", "Ciudad / Poblaci&oacute;n", "C&oacute;digo Postal", "RFC", "Grupo"),
						"campos" => array("nombre", "telefonos", "direccion", "estado", "ciudad", "cp", "rfc", "grupo"),
						"width" => array("150px","70px","180px","80px","100px","70px","70px","80px")
						
					)
			 );

if(isset($_POST[Enviar])){ //Ijngresar a la base de datos
	if($_POST[tabla] == "productos"){
		$key = "descripcion";
	} else {
		$key = "nombre";
	}
         
	foreach($_POST[$key] as $k => $v){
	
		if($_POST[cb][$k] == 1){//validar que el checbox esta seleccionado
		$string = "INSERT INTO ".$_POST[tabla]. " (";		
		foreach($arr[$_POST[tabla]][campos] as $campo){
			$string .= $campo.",";
		}
		if($_POST[tabla] =="clientes"){$string.="vendedor,";}		
		 if($_POST[tabla] =="clientes" || $_POST[tabla] =="proveedores" ){$string .="observaciones,pais";}
	     if($_POST[tabla] =="productos"){$string = substr($string,0,strlen($string)-1);}		
		$string .= ") VALUES (";
		foreach($arr[$_POST[tabla]][campos] as $campo){
			$string .= "'".mysql_real_escape_string($_POST[$campo][$k])."',";
		}
		if($_POST[tabla] =="clientes"){$string.="0,";};
		 if($_POST[tabla] =="clientes" || $_POST[tabla] =="proveedores" ){$string .="'',146";}
		 if($_POST[tabla] =="productos"){$string = substr($string,0,strlen($string)-1);}		
		$string .= ")";		
		$query = mysql_query($string) or die ($string."<p>".mysql_error());
		}
	}
	
	titleset("Importación de Datos Finalizada");
?>
<p align="center">Los datos seleccionados han sido ingresados al sistema exitosamente.<br />
Para verificar la informaci&oacute;n, haga click <a href="?section=<?=$_POST[tabla]?>">aqui</a> o bien, regrese al <a href="?section=vars">m&oacute;dulo de configuraci&oacute;n</a> para ingresar m&aacute;s datos.</p>
<p align="center">
<a href="?section=<?=$_POST[tabla]?>"><img src="iconos/<?=$_POST[tabla]?>.png" width="64" height="64" /><br /><b><?=ucfirst($_POST[tabla])?></b></a>
</p>
<p align="center">
  <?php
	exit();
}

$nombre = $_FILES['userfile']['name'];
$archivo_temporal = $_FILES['userfile']['tmp_name'];
$tipo_archivo = $_FILES['userfile']['type']; 
$peso= $_FILES['userfile']['size'];

$carpeta = 'archivo/'; 
$id="Libro";
if (file_exists($nombre))
{
	unlink( $carpeta.$nombre);
}	
$file= 	explode(".", $nombre);
if($file[1] != 'xls' && $file[1] != 'xlsx') {
	relocation("comuni-k.php?section=vars&error=1");
	exit();
}          
else {
	$file = $carpeta.$id.".".$file[1];
	move_uploaded_file($archivo_temporal, $carpeta.$nombre);
	rename($carpeta.$nombre, $file); 	
	?>
  <?php
titleset("Inserción de Datos desde Excel");
?>
<script language="javascript" type="text/javascript">
$(document).ready(function() {
      if($("#checkbox").attr('checked') == true)
			{
				$(".chris").each(function(){
				$(this).attr("checked",true);
			});
			}
			});
</script>
</p>
<p align="center"><b>Estos son los datos obtenidos: <span style="color:#933;"><?=ucfirst($_POST[tablas])?></span></b><br />
  Si desea, puede modificarlos o seleccionar 
solanmente aquellos que realmente necesite
 haciendo click en 
 <input name="checkbox" type="checkbox" id="checkbox" checked="checked" />
 correspondiente.<br />
 Una vez confirmada la informaci&oacute;n, presione el bot&oacute;n <i>&quot;Ingresar filas seleccionadas a <?=ucfirst($_POST[tablas])?></i>&quot; para iniciar la descarga.
</p>
<form name="form1" action="" method="post">
    <?php
	
	echo "<table border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" class=\"bordear_tabla lista\" id=\"_lista\"><tr>";
	foreach($arr[$_POST[tablas]][titulos] as $t){
		echo "<th>".$t."</th>";
	}
	echo "<th>&nbsp;</th>";
	echo "</tr>";	
	
	/** PHPExcel_IOFactory */
	require_once 'Classes/phpexcel/PHPExcel/IOFactory.php';

	//echo date('H:i:s') . " Load from Excel2007 file\n";
	$objPHPExcel = PHPExcel_IOFactory::load($file);

	//echo date('H:i:s') . " Write to Excel2007 format\n";

	$objReader = PHPExcel_IOFactory::createReader('Excel2007');
	$objReader->setReadDataOnly(true);

	//$objPHPExcel = $objReader->load("test.xlsx");
	$objWorksheet = $objPHPExcel->getActiveSheet();

	$objWorksheet = $objPHPExcel->getActiveSheet();

	$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
	$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
	
	function validar($fila){
		$val = 0;
		foreach($fila as $v){
			if(strlen($v) > 0){
				$val++;
			}			
		}
		return $val;
	}
	for ($row = 1; $row <= $highestRow; ++$row) {
		$d = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();
		
		$fila = array();
		foreach($arr[$_POST[tablas]][campos] as $k => $v){
			$fila[] = $objWorksheet->getCellByColumnAndRow($k, $row)->getValue();
		}
		
		if(validar($fila) > 0){
			$datos = 0;
			echo "\n<tr>";
			foreach($arr[$_POST[tablas]][campos] as $k => $v){
				$campo = $objWorksheet->getCellByColumnAndRow($k, $row)->getValue();
				if($v == "cp" || $v == "credito" || $v == "dias_credito" || $v == "precio_publico" || $v == "iva"){//validar que estos campos solo escriban datos númericos
					if($v == "credito" || $v == "precio_publico"){
						$n = 2;
					} else {
						$n = 0;
					}
					$blur = "onblur='numero(this,".$n.");'";
				} else {
					$blur = "";
				}
				echo "<td><input value=\"".utf8_decode(trim(htmlspecialchars(strip_tags($campo))))."\"  name=\"".$arr[$_POST[tablas]][campos][$k]."[]\" ".$blur." style=width:".$arr[$_POST[tablas]][width][$k]."; /> </td>";//escribir en las cajas de texto lo que se encuentrae en el excel
				if(strlen($campo) > 0){
					$datos++;
				} else {
				}
			}
			if($datos == count($arr[$_POST[tablas]][campos])){		//Validar si los checkbox estan selecionados o no 			
				$check = "checked='checked'";
			} else {
				$check = '';
			}
			unset($datos);
      echo "<td ".$class."><input name='cb[".($row-1)."]' class='chris' type='checkbox' value='1' ".$check." /></td>";
			echo "</tr>\n";
		}
		unset($fila);
			}
	echo "</table>";	
	//if(file_exists($carpeta.$nombre)){unlink($carpeta.$nombre);}
}
?><br />
<center>
<input name="tabla" type="hidden" value="<?php echo $_POST[tablas];?>" />
<input name="Enviar" id="Enviar" type="submit"   value="Ingresar filas seleccionadas a <?=ucfirst($_POST[tablas])?>" />
</center>

</form>
<?php unlink($file);?>