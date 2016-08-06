<?php
define("FPDF_FONTPATH","font/");
require("fpdf/fpdf.php");
require("funciones/basedatos.php");
require("funciones/funciones.php");
Conectar();

$pdf=new FPDF('P','pt','Letter');
$pdf->AddPage();
$pdf->SetFont('courier','',9);
$pdf->SetXY(0,0);
$pdf->SetMargins(0,0,0);
$pdf->SetDrawColor(150,150,150);

class PDF extends FPDF{
	//Cabecera de página
	function Header(){
		
			$s = "SELECT * FROM vars";
			$q = mysql_query($s) or die (mysql_error());
			$r = mysql_fetch_assoc($q);
	    //Logo
	    $this->Image("logo/{$r[logotipo]}",10,10,0,20);
			$this->SetY(10);
	    //Arial bold 15
	    $this->SetFont("Arial","B",15);
	    //Título
	    $this->Cell(0,10,$r[empresa],0,1,"C");
      $this->SetFont("Arial","B",8);
	    $this->Cell(0,0,$r[direccion],0,1,"C");
			$this->SetY(24);
			$this->Cell(0,0,"RFC: {$r[rfc]}",0,1,"C");
	    $this->Ln(8);
	 }
}

$s = "SELECT
pedidos.id,
pedidos.folio,
pedidos.almacen,
pedidos.id_producto,
pedidos.cantidad,
pedidos.costo,
pedidos.iva,
pedidos.importe,
pedidos.obtenidos,
pedidos.compra,
pedidos.status,
pedidos.comentario,
pedidos.tipo,
DATE_FORMAT(fecha,'%d-%m-%Y')'fecha',
proveedores.nombre 'proveedor',
productos.codigo_barras,
IF(
	 pedidos.id_producto = 0,
	 especial,
	 productos.descripcion
	 ) descripcion,
IFNULL(pedidos.complemento,NULL) 'complemento',
almacenes.descripcion 'almacen_desc'
FROM
pedidos
INNER JOIN proveedores ON pedidos.proveedor = proveedores.clave
INNER JOIN almacenes ON pedidos.almacen = almacenes.id_almacen
LEFT JOIN productos ON productos.id_producto = pedidos.id_producto
WHERE pedidos.folio = '{$_GET['folio']}'";
$q = mysql_query($s) or die (mysql_error());
$q1 = mysql_query($s) or die (mysql_error());

$resultado = mysql_fetch_assoc($q1);
$proveedor = $resultado['proveedor'];
$almacen = $resultado['almacen_desc'];
$fecha = $resultado['fecha'];
$estado = $resultado['cancelado'];
$comentario = $resultado['comentario'];
if( $resultado[tipo] == "p" || $resultado[tipo] == "" ){ $tipo = "PEDIDO"; }else{ $tipo = "COTIZACIÓN"; }

	$pdf = new PDF("P","mm","Letter");
	$pdf->AliasNbPages();
	$pdf->SetAutoPageBreak(true,"20");
	$pdf->AddPage();
	$pdf->SetFont("Times","",8);
  
  $altura = 4;
  $pdf->Ln();
  $pdf->SetFont("Arial","B",9);
  $pdf->Cell(196,6, "{$tipo} DE PRODUCTOS","BT",1,"C");
  
  $pdf->Ln(3);
  $pdf->Cell(12,$altura,"FECHA:",0,0,"L");
  $pdf->SetFont("Arial","",9);
  $pdf->Cell(15,$altura,$resultado['fecha'],0,1,"L");
  
	$pdf->SetFont("Arial","B",9);
  $pdf->Cell(12,$altura,"FOLIO:",0,0,"L");
  $pdf->SetFont("Arial","",9);
  $pdf->Cell(15,$altura,$_GET['folio'],0,1,"L");
  
  $pdf->SetFont("Arial","B",9);
  $pdf->Cell(23,$altura,"PROVEEDOR:",0,0,"L");
  $pdf->SetFont("Arial","",9);
  $pdf->Cell(23,$altura,$proveedor,0,1,"L");
  
  $pdf->SetFont("Arial","B",9);
  $pdf->Cell(19,$altura,"ALMACÉN:",0,0,"L");
  $pdf->SetFont("Arial","",9);
  $pdf->Cell(19,$altura,$almacen,0,1,"L");
  
  $pdf->Ln();
  
  $pdf->SetFont("Arial","B",9);
  $pdf->Cell(124,$altura,"PRODUCTO",1,0,"C");
  $pdf->Cell(18,$altura,"CANT",1,0,"C");
  $pdf->Cell(18,$altura,"PRECIO",1,0,"C");
  $pdf->Cell(18,$altura,"IMPUESTO",1,0,"C");
  $pdf->Cell(20,$altura,"IMPORTE",1,1,"C");
  
  $pdf->SetFont("Arial","",9);
	$pdf->SetY(67);
	while($r = mysql_fetch_assoc($q)){
		$total = $total + $r['importe'];
		
		$sub_total += round($r[cantidad]*$r[precio],2);
		$iva += round($r[cantidad]*$r[precio]*($r[iva]/100),2);
		
		//$pdf->SetX($s[x]); //Arrimar hacia la izquierda
		//$pdf->Cell($w[0],15,$r[cantidad]."     ",0,0,"C"); //Cantidad
		
		$y1 = $pdf->GetY(); //Indexar Y1
		
		$descripcion = $r[descripcion];
		if($r[complemento] != "0")
		{
			$descripcion .= "\n".$r[complemento];
		}				
		
		$pdf->MultiCell(124,3,$descripcion,0); //Descripción
		
		$y2 = $pdf->GetY(); //Indexar Y2
		$yH = $y2 - $y1; // Diferencia
		$pdf->SetXY(134, $pdf->GetY() - $yH); //Reindexar X
		$pdf->Cell(18,$altura,$r['cantidad'],0,0,"R");
		$pdf->Cell(18,$altura,money($r['costo']),0,0,"R");
		$pdf->Cell(18,$altura,$r['iva'],0,0,"R");
		$pdf->Cell(20,$altura,money($r['importe']),0,1,"R");
		
		if($r[complemento] != "0")
		{
			$pdf->SetY($pdf->GetY()+3);
		}
		/*
		$pdf->Cell($w[2],15,number_format($r[precio],2)."  ",0,0,"C"); //Precio
		$pdf->Cell($w[3],15,number_format($r[cantidad]*$r[precio],2)."  ",0,0,"C"); //Importe*/
		$pdf->Cell(200,2,"",0,1,"R"); //Invisible!!  
		
		/*
		$total = $total + $r['importe'];
		$pdf->Cell(124,$altura,substr($r['descripcion']."\n".$r[complemento],0,65),1,0,"L");
		$pdf->Cell(18,$altura,$r['cantidad'],1,0,"C");
		$pdf->Cell(18,$altura,money($r['costo']),1,0,"R");
		$pdf->Cell(18,$altura,$r['iva'],1,0,"R");
		$pdf->Cell(20,$altura,money($r['importe']),1,1,"R");
		*/
	}
  $pdf->SetFont("Arial","B",9);
  $pdf->Cell(160,$altura,"",0,0,"L");
  $pdf->Cell(18,$altura,"TOTAL:",0,0,"R");
  $pdf->Cell(20,$altura,money($total),0,1,"R");
  
  $pdf->Cell(40,$altura,"COMENTARIOS:",0,1,"L");
  $pdf->SetFont("Arial","",9);
  $pdf->MultiCell(158,5,$comentario);
	
	$archivo = "Pedido_".$_GET['folio'].".pdf";
	$pdf->Output();
?>  
