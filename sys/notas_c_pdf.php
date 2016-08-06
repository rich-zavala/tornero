<?php
require("fpdf/fpdf.php");
include("funciones/basedatos.php");
require("funciones/funciones.php");
//require("funciones/CNumeroaLetra.php");
Conectar();

function iva($t)
{
	$r = 0;
	$iv_ini = $t - ($t*.16);
	$d = 0;
	for($d = 0; $r <= $t; $d += 0.01)
	{
		$r = ($iv_ini + $d) + (($iv_ini + $d) * .16);
	}
	return $iv_ini + ($d - .01);
}

function sector($sector){
	$s = "SELECT * FROM vectores WHERE sector = '{$sector}' AND tipo = 'nota_credito'";
	$q = mysql_query($s);
	return mysql_fetch_assoc($q);
}

function celda($x){
	$s = "SELECT porcentaje FROM vectores_productos WHERE columna = '{$x}' AND tipo = 'credito'";
	$q = mysql_query($s);
	$r = mysql_fetch_assoc($q);
	return $r[porcentaje];
}

$s = "SELECT
clientes.*,
ingresos.id id,
ingresos.importe,
ingresos.banco,
ingresos.tipo,
ingresos.referencia,
ingresos.fecha,
notas_de_credito.status,
notas_de_credito.reviso,
notas_de_credito.autorizo,
notas_de_credito.id id_nota
FROM
notas_de_credito
INNER JOIN ingresos ON notas_de_credito.id_mov = ingresos.id
LEFT JOIN clientes ON clientes.clave = notas_de_credito.persona
WHERE
folio =  '{$_GET['folio']}'
AND notas_de_credito.tipo = 'cliente'";
$q = mysql_query($s) or die (mysql_error());
$r = mysql_fetch_assoc($q);
$importe = $r[importe];
$subtotal = iva($r[importe]);
$iva = $subtotal * .16;
if(strlen($r[noexterior]) > 0){$ne= " No. ".$r[noexterior];}
if(strlen($r[nointerior]) > 0){$ni= " Int. ".$r[nointerior];}
$direccion="CALLE ".$r[calle].$ne.$ni." COLONIA ".$r[colonia]." CP. ".$r[cp];
$sf = "SELECT * FROM notas_de_credito_detalle WHERE nota = '{$r[id_nota]}' LIMIT 1";
//echo $sf;
$qf = mysql_query($sf) or die (mysql_error());
$f= mysql_fetch_assoc($qf);
$pdf=new FPDF('P','pt','Letter');
$pdf->AddPage();
$pdf->SetFont('Arial','',9);
$pdf->SetXY(0,0);
$pdf->SetMargins(0,0,0);

$secs = array("Datos_del_Cliente" => array("{$r[nombre]}\n{$r[RFC]}\n{$direccion}", "L"),
              "Tabla_de_Facturas" => array("","L"),
              "Reviso" => array($r[reviso], "L"),
              "Autorizo" => array($r[autorizo], "L"),
              "Sub-Total" => array(money(1000),"R"),
              "Total" => array(money(1000),"R"),
              "Fecha" => array(FormatoFechaFrase($r[fecha]),"R")
              );
foreach($secs as $k => $v){
  $s = sector(str_replace("_"," ",$k));
  
    switch($k){
      case "Sub-Total": $v[0] = money($subtotal); break;	
      case "Total": $v[0] = money($importe); break;
    }
    $pdf->SetXY($s[x],$s[y]);
    $pdf->MultiCell($s[ancho],13,$v[0],0,$v[1]);
}
$pdf->SetXY(30,210);
$pdf->MultiCell(20,15,"1  ",0,"C");

$pdf->SetXY(120,210);
$pdf->MultiCell(305,15,$f[descripcion],0,"L");

$pdf->SetXY(430,210);
$pdf->MultiCell(70,15,money($subtotal),0,"L");

$pdf->SetXY(460,348);
$pdf->MultiCell(127,15,money($iva),0,"R");

$pdf->SetXY(445,165);
$pdf->MultiCell(130,15,$f[folio],0,"R");

$pdf->Output();
?>