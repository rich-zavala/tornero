<?php
require("fpdf/fpdf.php");
include("funciones/basedatos.php");
require("funciones/funciones.php");

if(!isset($_SESSION[id_usuario]))
{
	header("location: index.php");
}
Conectar();
function sector($sector,$tipo){
  if($tipo == "f"){
    $t = "factura";
  } else {
    $t = "nota";
  }
	$s = "SELECT * FROM vectores WHERE sector = '{$sector}' AND tipo = '{$t}'";
  //echo $s."<br>";
	$q = mysql_query($s);
	return mysql_fetch_assoc($q);
}

function celda($x,$tipo){
  if($tipo == "f"){
    $t = "factura";
  } else {
    $t = "nota";
  }
	$s = "SELECT porcentaje FROM vectores_productos WHERE columna = '{$x}' AND tipo = '{$t}'";
	$q = mysql_query($s);
	$r = mysql_fetch_assoc($q);
	return $r[porcentaje];
}
$pdf=new FPDF('P','pt',array(319,456));
$pdf->AddPage();
$pdf->SetFont('courier','',9);
$pdf->SetMargins(0,0,0);
$pdf->SetDrawColor(150,150,150);

$s = "SELECT
facturas.folio,
moneda,
facturas.leyenda,
facturas.fecha_factura,
facturas.licitacion,
facturas.datos_cliente,
facturas.importe,
facturas.tipo,
facturas.status,
clientes.nombre cliente,
usuarios.nombre vendedor
FROM
facturas
LEFT JOIN usuarios ON usuarios.id_usuario = facturas.id_facturista
LEFT JOIN clientes ON clientes.clave = facturas.id_cliente
WHERE folio = '{$_GET[folio]}'";

$q = mysql_query($s);
$r = mysql_fetch_assoc($q);
$importe = $r[importe];
$tipo = $r[tipo];
$moneda =$r[moneda];

$cliente_direccion = $r[cliente_direccion];
$cliente_nombre = $r[nombre];
$cliente_rfc = $r[cliente_rfc];

$vendedor = $r[vendedor];

$sq = "SELECT
facturas_productos.cantidad,
facturas_productos.canti_,
facturas_productos.lote,
facturas_productos.precio,
facturas_productos.descuento,
facturas_productos.iva,
facturas_productos.importe,
productos.codigo_barras,
IF(
	 facturas_productos.id_producto = 0,
	 especial,
	 productos.descripcion
	 ) descripcion,
IFNULL(facturas_productos.complemento,NULL) 'complemento'
FROM
facturas_productos
LEFT JOIN productos ON facturas_productos.id_producto = productos.id_producto
WHERE folio_factura = '{$_GET[folio]}'
GROUP BY facturas_productos.id_facturaproducto";
$qq = mysql_query($sq) or die (mysql_error());
require("funciones/CNumeroaLetra.php");
$valores = explode(" ",$_GET[folio]);	
//establece el rectangulo para la orden de carga
	//$pdf->Rect(20,20,298,435,"");
	$pdf->SetXY(90,25);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(165,13,"NOTA DE VENTA",0,0,"C"); 
    $pdf->SetFont('courier','B',14);
	$pdf->Cell(50,13,"Nº ".$valores[1],0,1,"R"); 
	$a1 = 14;
	$pdf->SetXY(20,60);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(55,$a1,"FECHA:",0,0,"L");
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(240,$a1,FormatoFecha($r[fecha_factura]),0,1,"L");
	$pdf->SetX(20);
	//rfc cliente
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(55,$a1,"CLIENTE:",0,0,"L");
	//rfc cliente dinamico
	$pdf->SetFont('Arial','B',8);
	$pdf->MultiCell(240,$a1,$r[cliente],0);
	$pdf->SetX(20);
	//domicilio cliente
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(55,$a1,"VENDEDOR:",0,0,"L");
	//domicilio cliente dinamico
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(240,$a1,$r[vendedor],0,1,"L");	
	$pdf->SetFont('Arial','',11);    
	
	$pdf->SetXY(110,375);
    $pdf->Cell(200,20,"  RECIBIDO DE CONFORMIDAD: ",0,0,"C"); 
	$pdf->Line(120,420,300,420);
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(30,375);
    $pdf->MultiCell(90,12,"NO ES VALIDO PARA EFECTOS FISCALES",0); 
	
	$secs = array("Tabla_de_Productos" => array("Esta es la Tabla de Productos", "L")
								//"Total" => array(0,"R"),
								//"Fecha" => array($r[fecha_factura])
								);

  foreach($secs as $k => $v){
    $s = sector(str_replace("_"," ",$k),$tipo);
    if($k == "Tabla_de_Productos"){
      $a = 350;
      $w = array($a*(celda("cantidad",$tipo)/100),
                 $a*(celda("descripcion",$tipo)/100),
                 $a*(celda("precio",$tipo)/100),
                 $a*(celda("importe",$tipo)/100)
                );
      $x = $s[x];
      $y = $s[y];
      $pdf->SetXY(20,120);     
      // === CABECERA DE PRODUCTOS
      //$pdf->SetTextColor(255,255,255);
	  $pdf->SetFillColor(255,255,255);
      $pdf->SetFont('Arial','B',9);
      $pdf->SetX(21); //Arrimar hacia la izquierda
      $pdf->Cell($w[0],20,"CANT.",0,0,"C",1); //Cantidad
      $y1 = $pdf->GetY();
	  if(!isset($_GET[p])){$pdf->Cell($w[1]+10,20,"CONCEPTO",0,0,"C",1);}else{$pdf->Cell($w[1]+50,20,"CONCEPTO",0,0,"C",1);}
      $y2 = $pdf->GetY();
      $yH = $y2 - $y1;
      $pdf->SetXY($x + $w[1] + $w[0], $pdf->GetY() - $yH);
       if(!isset($_GET[p])){
      //$pdf->Cell($w[2]-10,20,"P.U.",1,0,"C",1);
      $pdf->Cell($w[3]-10,20,"IMPORTE",0,0,"C",1);
	   }
      $pdf->Cell(.001,$yH,"",0,1,"R");
      
      $pdf->SetXY($s[x]+$w[0],$s[y]+20-80);
      $pdf->SetTextColor(0,0,0);
      $pdf->SetFont('Arial','',9);
	  
      // === FINALIZA CABECERA DE PRODUCTOS
      while($r = mysql_fetch_assoc($qq)){
		    $sub_total += round($r[canti_]*$r[precio],2);
		  	$iva += round($r[canti_]*$r[precio]*($r[iva]/100),2);
        $pdf->SetX(21); //Arrimar hacia la izquierda
		
				$cantidad = $r[canti_];
				$pdf->Cell(50,15,$cantidad."     ",0,0,"C"); //Cantidad	
		// if($r[canti_] != 0)
		// {
		// }
		// else{
			// $pdf->Cell(50,15,$r[cantidad]."     ",0,0,"C"); //Cantidad	
		// }       
        
        $y1 = $pdf->GetY(); //Indexar Y1
				
				$descripcion = $r[descripcion];
				if($r[complemento] != "0")
				{
					$descripcion .= "\n".$r[complemento];
				}
				
         if(!isset($_GET[p])){$pdf->MultiCell($w[1]-36,15,$descripcion,0);}else{$pdf->MultiCell($w[1]+50,15,$descripcion,0);} //Descripción
        $y2 = $pdf->GetY(); //Indexar Y2
        $yH = $y2 - $y1; // Diferencia
        $pdf->SetXY($x + $w[1] + $w[0], $pdf->GetY() - $yH); //Reindexar X
             
			  if(!isset($_GET[p])){   
        
		/*if($p[canti_] != 0)
		{	 
		  $pdf->Cell($w[2]-10,15,number_format((($r[cantidad]*$r[precio])/$r[canti_]),2)."  ",0,0,"C"); //Precio		
		}
		else
		{			
			$pdf->Cell($w[2]-10,15,number_format($r[precio],2)."  ",0,0,"C"); //Precio
		}	*/
		// p($r);
					// $pdf->Cell($w[3]-10,15,number_format(($r[cantidad]*$r[precio]) + (($r[cantidad] * $r[precio])*($r[iva]/100)),2)."  ",0,0,"C"); //Importe
					$pdf->Cell($w[3]-10,15,number_format($r[importe], 2)."  ",0,0,"C"); //Importe
					// $pdf->Cell($w[3]-10,15,number_format(($r[cantidad]*$r[precio]) + (($r[cantidad] * $r[precio])*($r[iva]/100)),2)."  ",0,0,"C"); //Importe
			  }
        $pdf->Cell(.001,$yH,"",0,1,"R"); //Invisible!!        
        //FIN CARNE
      }
    } else {
      switch($k){
        case "Total": $v[0] = money($importe); break;
      }
      $pdf->SetXY($s[x],$s[y]);
      $pdf->MultiCell($s[ancho],13,$v[0],0,$v[1]);
    }
  }
  if(!isset($_GET[p])){
	/*$pdf->SetXY(200,335);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(72,12,"SUBTOTAL  ",0,0,"R");
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(76,12,number_format($sub_total,2),0,1,"L");
    $pdf->SetXY(200,346);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(72,12,"I.V.A.  ",0,0,"R");
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(76,12,number_format($iva,2),0,1,"L");*/
    $pdf->SetXY(200,360);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(72,12,"TOTAL  ",0,0,"R");
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(76,12,number_format($importe,2),0,1,"L");
  }
	

$pdf->Output();
?>