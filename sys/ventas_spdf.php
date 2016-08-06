<?php
if(!isset($_GET[enviar]))
{
	include("funciones/basedatos.php");
	require("funciones/funciones.php");
}
require("fpdf/fpdf.php");
Conectar();

$pdf=new FPDF('P','pt','Letter');
$pdf->AddPage();
$pdf->SetFont('Arial','',9);
$pdf->SetXY(0,0);
$pdf->SetMargins(0,0,0);
$pdf->SetTopMargin(25);

$d = $f;
if(!isset($_GET[enviar]))
{
	$d = factura_data($_GET[folio],$_GET[serie]);
}
else
{
	$d[cadenaoriginal] = utf8_decode(getFile('co2.txt'));
	$d[sello] = getFile('sello2.txt');
}

// ==== ENCABEZADO
//Logo de la empresa
$pdf->SetDrawColor(214,214,214);
$pdf->Image("logo/".$d[empresa][logotipo],106,4,0,60);
//Llenamos los cuadros con un color en especifico
$pdf->SetFillColor(214,214,214);
//Nombre de la empresa
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(36,65);
$pdf->MultiCell(360,12,$d[empresa][nombre],0,"L");   
//RFC del cliente
$pdf->SetFont('Arial','',8);
$pdf->Ln(5);
$pdf->SetX(36);
$pdf->Cell(300,12,"RFC: ".$d[empresa][rfc],0,0,"L"); 
//Datos del cliente
$pdf->SetFont('Arial','',7);
$pdf->Ln(10);
$pdf->SetX(36);

$pdf->MultiCell(590,7,strtoupper("CALLE {$d[empresa][calle]} No. {$d[empresa][noe]}\nCOLONIA {$d[empresa][colonia]}\nC.P. {$d[empresa][cp]}  {$d[empresa][localidad]}, {$d[empresa][municipio]}, {$d[empresa][estado]}, {$d[empresa][pais]}"),0,"L"); 

$pdf->SetX(36);
$pdf->MultiCell(590,7,"REGIMEN FISCAL: REGIMEN GENERAL DE LEY PERSONAS MORALES.",0,"L");
//Fecha de la factura
$pdf->SetFont('Arial','',8); 
$pdf->Ln(5);
$pdf->SetX(36);
$pdf->Cell(300,12,FormatoFechaFrase2($d[factura][fecha])." ".$d[factura][hora],0,0,"L"); 

if(strlen($d[empresa][cedula])>0){
//Cedula de la factura
$pdf->Image("cedula/".$d[empresa][cedula],320,8,0,120);
}

//Encabezado de la factura
$pdf->SetFont('Arial','B',8);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY(410,10);
$pdf->Cell(160,12,"Serie y No. Factura",0,1,"C",1); 

//valor del folio de la factura
$pdf->SetFont('Arial','B',13);
$pdf->SetTextColor(255,0,0);
$pdf->SetXY(410,22);
$s_f = explode("-",$_GET[folio]);
$pdf->Cell(160,26,"   ".$_GET[folio],1,1,"C");
//No de certificado en cabezado 
$pdf->SetFont('Arial','B',8);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY(410,56);
$pdf->Cell(160,12,"No. de serie del certificado de sello digital",0,1,"C",1); 
//valor del No del certiifcado $d[factura][nocertificado]
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(410,68);
$pdf->Cell(160,20,$d[factura][nocertificado],1,1,"C");

//Encabezado del año y no de aprobación  
$pdf->SetFont('Arial','B',8);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY(410,95);
$pdf->Cell(160,12,"Año y No de aprobación de folios",0,1,"C",1);
//valor el año y no de aprobación  
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',11);
$pdf->SetXY(410,107);
$pdf->Cell(160,20,"   ".$d[factura][anoaprobacion]."    ".$d[factura][noaprobacion],1,1,"L");
  
//Texto de Encabezado de datos  del cliente
$pdf->SetFont('Arial','B',11);
$pdf->SetXY(36,140);
$pdf->Cell(534,12,"DATOS DEL CLIENTE:",0,1,"L");  
//Cuadro donde van los datos del cliente
$pdf->SetXY(36,152);
$pdf->SetDrawColor(214,214,214);
$pdf->Cell(534,60,NULL,1,1,"L");
//Datos del cliente
$pdf->SetFont('Arial','',9);
$pdf->SetXY(36,155);
$pdf->Cell(534,12,$d[cliente][nombre],0,1,"L");
$pdf->SetX(36);
$pdf->Cell(534,12,"RFC: ".$d[cliente][rfc],0,1,"L");
$pdf->SetX(36);
//Se valida que exista tanto el numero exterior como el interior
if(strlen($d[cliente][noe]) > 0){$noeC= " No. ".$d[cliente][noe];}
//Switch para número interior
$noiC = (strlen(trim($d[cliente][noi])) > 0) ? " Interior {$d[cliente][noi]}" : null;
if(strlen($d[cliente][colonia]) > 0){$coloniaC= " Col./Fracc. ".$d[cliente][colonia];}
$pdf->MultiCell(534,12,"CALLE {$d[cliente][calle]}{$noeC}{$noiC}{$coloniaC} C.P. {$d[cliente][cp]}\n{$d[cliente][localidad]}, {$d[cliente][municipio]}, {$d[cliente][estado]}, {$d[cliente][pais]}",0,1,"L");
$pdf->Ln(15);
//Fin de los encabezados

$pdf->SetTextColor(0,0,0);
$pdf->SetDrawColor(204,204,204);
$pdf->SetFillColor(214,214,214);
$pdf->SetFont('Arial','B',9);
$pdf->SetX(36); //Arrimar hacia la izquierda
$pdf->Cell(50,15,"CANTIDAD",1,0,"C",1); //Cantidad

$y1 = $pdf->GetY();
$pdf->Cell(365,15,"CONCEPTO",1,0,"C",1);
$y2 = $pdf->GetY();
$yH = $y2 - $y1;
$pdf->SetXY(36 + 365 +50, $pdf->GetY() - $yH);
				
$pdf->Cell(60,15,"P.U.",1,0,"C",1);
$pdf->Cell(60,15,"IMPORTE",1,0,"L",1);
$pdf->Cell(.001,$yH,"",0,1,"R");

$pdf->SetXY(36+20,88+20);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',9);
// === FINALIZA CABECERA DE PRODUCTOS
$pdf->SetXY(36,233); //Arrimar hacia la izquierda
foreach($d[productos] as $p)
{				
				
	$pdf->SetX(36); //Arrimar hacia la izquierda
	//ajuste para el tornero 	
	if($p[canti_] != 0)
	{
		$pdf->Cell(60,15,$p[canti_]." {$p[unidad]}",1,0,"R"); //Cantidad	
	}
	else{
		$pdf->Cell(60,15,$p[cantidad]." {$p[unidad]}",1,0,"R"); //Cantidad	
	}
	
	$y1 = $pdf->GetY(); //Indexar Y1	
	
	$descripcion = $p[descripcion];	
	if($p[complemento] != "0")
	{
		$descripcion .= "\n".$p[complemento];
	}
	
	$pdf->MultiCell(355,15,html_entity_decode($descripcion, ENT_QUOTES),1); //Descripción
	$y2 = $pdf->GetY(); //Indexar Y2
	$yH = $y2 - $y1; // Diferencia
	$pdf->SetXY(62 + 369 + 20, $pdf->GetY() - $yH); //Reindexar X		
	
	if($p[canti_] != 0)
	{					
  	  $pdf->Cell(60,15,number_format((($p[cantidad]*$p[precio])/$p[canti_]),2)." ",1,0,"R"); //Precio		
	}
	else
	{
	  	$pdf->Cell(60,15,number_format($p[precio],2)."  ",1,0,"R"); //Precio
	}
	
	$pdf->Cell(60,15,number_format($p[cantidad]*$p[precio],2)."  ",1,0,"R"); //Importe
	$pdf->Cell(.001,$yH,"",0,1,"R"); //Invisible!!        
}

$pdf->SetX(36);
$pdf->Cell(415,15,"",0,0,"C",0);
$pdf->Cell(60,15,"SUB-TOTAL",1,0,"C",0);
$pdf->Cell(60,15,money($d[factura][subtotal])."  ",1,1,"R",0); //subtotal
if(money($d[factura][descuento])  != "0.00")
{
	$pdf->SetX(36);
	$pdf->Cell(415,15,"",0,0,"C",0);
	$pdf->Cell(60,15,"DESCUENTO",1,0,"C",0);
	$pdf->Cell(60,15,money($d[factura][descuento])."  ",1,1,"R",0); //descuento
}

$pdf->SetX(36);
$pdf->Cell(415,15,"",0,0,"L",0);
$pdf->Cell(60,15,"I.V.A. 16%",1,0,"C",0);
$pdf->Cell(60,15,money($d[factura][iva])."  ",1,1,"R",0); //iva
$pdf->SetX(36);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(415,15,"IMPORTE CON LETRA",0,0,"L",0);
$pdf->SetFont('Arial','',9);
$pdf->Cell(60,15,"TOTAL",1,0,"C",0);
$pdf->Cell(60,15,money($d[factura][importe])."  ",1,1,"R",0); //Total
//importe con letras	
$pdf->SetX(36);	
$pdf->MultiCell(400,15,$d[factura][letras],0,"L",0);
$pdf->Ln(5);
$pdf->SetX(36);	
$pdf->Cell(415,12,"Forma de pago: Pago en una sola exhibición",0,1,"L",0);
$pdf->SetX(36);	
$pdf->Cell(415,12,"Método de pago: {$d[cliente][metodoDePago]}",0,1,"L",0);
if($d[cliente][metodoDePago] != "EFECTIVO")
{
	$pdf->SetX(36);	
	$pdf->Cell(415,12,"No. cta. de pago: {$d[cliente][NumCtaPago]}",0,1,"L",0);
}
$pdf->Ln(5);

$pdf->SetDrawColor(214,214,214);
if(strlen($d[factura][leyenda]) > 0)
{
	$pdf->SetX(36);
	$pdf->SetFont('Arial','',9);	
	$pdf->MultiCell(415,12,"OBSERVACIONES: ".$d[factura][leyenda],0,"L");
	$pdf->Ln(5);
}

//Para la impresión sin sello digital

if(!isset($_GET[sinSello]))
{
	$pdf->SetX(36);	
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(415,15,"CADENA ORIGINAL",0,1,"L",0);
	$pdf->SetDrawColor(214,214,214);
	$pdf->SetX(36);
	$pdf->SetFont('Arial','',9);	
	$pdf->MultiCell(534,12,$d[cadenaoriginal],1,"L");
	$pdf->Ln(5);
	$pdf->SetX(36);	
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(415,15,"SELLO DIGITAL DEL EMISOR",0,1,"L",0);
	$pdf->SetDrawColor(214,214,214);
	$pdf->SetX(36);	
	$y3 = $pdf->GetY();
	$pdf->SetFont('Arial','',9);	
	$pdf->MultiCell(534,12,$d[sello],1,"L");
	$pdf->SetFont('Arial','B',9);
	$pdf->Ln(5);
	$pdf->SetX(36);	
	$pdf->Cell(360,15,"ESTE DOCUMENTO ES UNA REPRESENTACIÓN IMPRESA DE UN CFD",0,1,"L",0);
}

// @unlink('co2.txt');
// @unlink('sello2.txt');

if(isset($_GET[infor]))
{
	unlink($smtp_file_xml);
	$pdf->Output($smtp_file,'D');
}
else
{
	$pdf->Output($smtp_file,'F');
}
?>