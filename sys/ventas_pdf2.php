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

if($tipo == "f")
{ // Es factura
	$pdf=new FPDF('P','pt',"Letter");
}
else
{
	$pdf=new FPDF('P','pt',array(611,804));
}
$pdf->AddPage();
$pdf->SetFont('courier','',9);
$pdf->SetMargins(0,0,0);
$pdf->SetDrawColor(150,150,150);

//$pdf->Cell(611,734,"",1,1,"R");
//$pdf->Output();
//exit;

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
usuarios.nombre vendedor
FROM
facturas
LEFT JOIN usuarios ON usuarios.id_usuario = facturas.id_facturista
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

$vendedor = explode(" ",$vendedor);
foreach($vendedor as $v)
{
	$vendedor_v .= $v[0];
}

$pdf->SetXY(20,20);
$pdf->MultiCell(200,15,strtoupper($vendedor_v),0);
$pdf->SetXY(0,0);

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

if($tipo == "f"){ // Es factura

  $cliente_data = explode("|",$r[datos_cliente]);
	if(strlen($cliente_data[3]) > 0 ){ $cliente_data[3] = " No. ".$cliente_data[3];}
	if(strlen($cliente_data[4]) > 0 ){ $cliente_data[4] = " Int. ".$cliente_data[4];}
	if(strlen($cliente_data[5]) > 0 ){ $cliente_data[5] = "Col./Fracc.: ".$cliente_data[5];}
	$datos .= "CALLE: ".$cliente_data[2].$cliente_data[3].$cliente_data[4]."\n";	
	$datos .= $cliente_data[5]." CP: ".$cliente_data[10]."\n";
	$datos .= $cliente_data[6].", ".$cliente_data[7].", ".$cliente_data[8].", ".$cliente_data[9];

	$dc = split("RFC: ",$r[datos_cliente]);
	$datos_cliente = $dc[0];
	$rfc = $dc[1];
	$secs = array(
								"Nombre_del_Cliente" => array($cliente_data[1], "L"),
								"RFC" => array($cliente_data[0], "L"),
								"Datos_del_Cliente" => array($datos, "L"),
								"Tabla_de_Productos" => array("", "L"),
								"Leyenda" => array($r[leyenda], "L"),
								"Total_en_Letras" => array(0,"L"),
								"Sub-Total" => array(0,"R"),
								//"Descuento" => array(0,"R"),
								"Impuesto" => array(0,"R"),
								"Total" => array(0,"R"),
								"Fecha" => array(FormatoFecha($r[fecha_factura]),"R")
								);
  foreach($secs as $k => $v){
  	$s = sector(str_replace("_"," ",$k),$tipo);
  	if($k == "Tabla_de_Productos"){	
  		$a = $s[ancho]-16;
  		$w = array($a*(celda("cantidad",$tipo)/100),
  							 $a*(celda("descripcion",$tipo)/100),
  							 $a*(celda("descuento",$tipo)/100),
  							 $a*(celda("impuesto",$tipo)/100),
  							 $a*(celda("precio",$tipo)/100),
  							 $a*(celda("importe",$tipo)/100)
  							);
  		$x = $s[x];
  		$y = $s[y];
			
  		$pdf->SetXY($s[x],$s[y]);
  		while($r = mysql_fetch_assoc($qq)){
  			/*$sub_total += $r[cantidad]*$r[precio];
  			$descuento += $r[cantidad]*($r[precio]*($r[descuento]/100));
  			$iva += (($r[cantidad]*$r[precio])-($r[cantidad]*($r[precio]*($r[descuento]/100))))*($r[iva]/100);
				$iva = redon($iva,2);*/
				$sub_total += $r[cantidad]*$r[precio];
		  	$iva += $r[cantidad]*($r[precio]*($r[iva]/100));
  			$pdf->SetX($s[x]); //Arrimar hacia la izquierda
			
			//agregado by chris 05-11-2010
				if($r[canti_] != 0)
				{
					$pdf->Cell($w[0],15,$r[canti_],0,0,"C"); //Cantidad
				}
				else{
  			$pdf->Cell($w[0],15,$r[cantidad],0,0,"C"); //Cantidad
				}
  			//termina
  			$y1 = $pdf->GetY(); //Indexar Y1
				
				$descripcion = $r[descripcion];
				if($r[complemento] != "0")
				{
					$descripcion .= "\n".$r[complemento];
				}
				
  			$pdf->MultiCell(($w[1]),15,$descripcion,0); //Descripción
  			$y2 = $pdf->GetY(); //Indexar Y2
  			$yH = $y2 - $y1; // Diferencia
  			$pdf->SetXY($x + $w[1] + $w[0], $pdf->GetY() - $yH); //Reindexar X
 
  			//$pdf->Cell($w[2],15,number_format($r[descuento],2)."%",0,0,"R"); //Descuento
				//$pdf->Cell($w[2],15,"",1,0,"R"); //Descuento CARNITAS
  			//$pdf->Cell($w[3],15,number_format($r[iva],2)."%",1,0,"R"); //Impuesto
					//agregado by chris 05-11-2010	
  				 if($r[canti_] != 0)
				{
         $pdf->Cell($w[2],15,number_format((($r[cantidad]*$r[precio])/$r[canti_]),2)."  ",0,0,"C"); //Precio
				}else
				{
					 $pdf->Cell($w[2],15,number_format($r[precio],2)."  ",0,0,"C"); //Precio
				}
				//termina
  			$pdf->Cell($w[5],15,number_format($r[cantidad]*$r[precio],2),0,0,"C"); //Importe
        $pdf->Cell(.001,$yH,"",0,1,"R"); //Invisible!!
  		}
  	}
		
		
		else {
  		switch($k){
  			case "Sub-Total": $v[0] = number_format($sub_total,2); break;
  			case "Impuesto": $v[0] = number_format($iva,2); break;
  			case "Descuento": $v[0] = number_format($descuento,2); break;
  			case "Total": $v[0] = number_format($importe,2); break;
  			case "Total_en_Letras":
  			
  			$numalet= new CNumeroaletra;
  			$numalet->setNumero($importe);
  			$numalet->setMayusculas(0);
  			$numalet->setGenero(0);
				if($moneda == "M.N.")
				{
  				$numalet->setMoneda("PESOS");
				}
				else
				{
					$numalet->setMoneda("DOLARES");
				}
  			$numalet->setPrefijo("");
  			$numalet->setSufijo($moneda);
  			$v[0] = "SON: ".strtoupper($numalet->letra());
  		}
  		$pdf->SetXY($s[x],$s[y]);
  		$pdf->MultiCell($s[ancho],13,$v[0],0,$v[1]);
  	}
  }
} else if($tipo == "n"){ //Es nota
	$logo_s = "SELECT * FROM vars";
	$logo_q = mysql_query($logo_s);
	$logo_r = mysql_fetch_assoc($logo_q);

  // ==== ENCABEZADO PARA TORNERO
	
	/*trtoupper("CALLE {$d[empresa][calle]} No. {$d[empresa][noe]}\nCOLONIA {$d[empresa][colonia]}\nC.P. {$d[empresa][cp]}  {$d[empresa][localidad]}, {$d[empresa][municipio]}, {$d[empresa][estado]}, {$d[empresa][pais]}*/
	
  $pdf->Image("logo/".$logo_r[logotipo],36,30,0,80);
  $pdf->SetFillColor(64,64,64);
  $pdf->SetFont('Arial','',12);
  $pdf->SetXY(150,50);
  $pdf->Cell(280,13,$logo_r[nombre],0,0,"C");  
  
  $pdf->SetFont('Arial','',10);
  $pdf->SetXY(150,68);
  $pdf->Cell(280,12,"TELÉFONO (01 999) 9 46 20 56 / 57",0,0,"C");  
  
  $pdf->SetXY(150,84);
  $pdf->Cell(280,12,"CALLE ".$logo_r[calle]." No. ".$logo_r[noe]." COLONIA ".$logo_r[colonia],0,0,"C");  
  
  $pdf->SetXY(150,100);
  $pdf->Cell(280,12,"MÉRIDA, MÉRIDA, YUCATÁN, MÉXICO C.P.".$logo_r[cp],0,0,"C");  

  $pdf->SetFont('Arial','',11);
  $pdf->SetTextColor(255,255,255);
  $pdf->SetXY(440,34);
  $pdf->Cell(130,14,"NOTA DE VENTA",0,1,"C",1);  
  
  $pdf->SetFont('Arial','B',13);
  $pdf->SetTextColor(255,0,0);
  $pdf->SetXY(440,48);
  $pdf->Cell(130,26,str_pad(str_replace("NOTA ","",$_GET[folio]),6,"0",STR_PAD_LEFT),1,1,"C");  
  $pdf->SetFont('Arial','',11);
  $pdf->SetTextColor(255,255,255);
  $pdf->SetXY(440,86);
  $pdf->Cell(130,14,"FECHA",0,1,"C",1);  
  
  $pdf->SetTextColor(0,0,0);
  
  $pdf->SetFont('Arial','',11);
  $pdf->SetXY(440,100);
  $pdf->Cell(130,20,FormatoFecha($r[fecha_factura]),1,1,"C");  
  
  /*$pdf->SetFont('Arial','B',11);
  $pdf->SetXY(36,136);
  $pdf->Cell(534,12,"DATOS DEL CLIENTE",0,1,"L");  
  $pdf->SetXY(36,132);
  $pdf->Cell(534,66,NULL,1,1,"L");	
	
	$pdf->SetFont('Arial','',9);
  $pdf->SetXY(36,150);
  $pdf->Cell(534,12,$cliente_data[1],0,1,"L");
	$pdf->SetXY(36,162);
	$pdf->Cell(534,12,$cliente_data[0],0,1,"L");
	$pdf->SetXY(36,174);
	$pdf->Cell(534,12,$datos,0,1,"L");*/

  $pdf->SetXY(36,644);
  $pdf->Cell(370,12,"IMPORTE EN LETRAS",0,1,"L");
  $pdf->SetXY(36,640);
  $pdf->Cell(370,50,NULL,1,1,"L");
  $numalet= new CNumeroaletra;
  $numalet->setNumero($importe);
  $numalet->setMayusculas(0);
  $numalet->setGenero(0);
  $numalet->setMoneda("PESOS");
  $numalet->setPrefijo("");
  $numalet->setSufijo("M.N.");
  $pdf->SetXY(36,658);
  $pdf->SetFont('Arial','',11);
  $pdf->MultiCell(370,12,"SON: ".strtoupper($numalet->letra()),0,1,"L");
  $pdf->SetFont('Arial','B',11);
  
  $pdf->SetXY(414,640);
  $pdf->Cell(155,50,NULL,1,1,"L");
  
  $pdf->SetXY(36,698);
  $pdf->Cell(534,48,"  RECIBIDO: ",1,1,"L"); 
  $pdf->Line(110,728,560,728);
  $pdf->SetFont('Arial','',11);
  $pdf->SetXY(250,731);
  $pdf->Cell(200,12,"NOMBRE                                                     FIRMA",0,1,"L"); 
  // === FIN DE ENCABEZADO
  
	$secs = array("Tabla_de_Productos" => array("Esta es la Tabla de Productos", "L")
								//"Total" => array(0,"R"),
								//"Fecha" => array($r[fecha_factura])
								);

  foreach($secs as $k => $v){
    $s = sector(str_replace("_"," ",$k),$tipo);
    if($k == "Tabla_de_Productos"){
      $a = $s[ancho];
      $w = array($a*(celda("cantidad",$tipo)/100),
                 $a*(celda("descripcion",$tipo)/100),
                 $a*(celda("precio",$tipo)/100),
                 $a*(celda("importe",$tipo)/100)
                );
      $x = $s[x];
      $y = $s[y];

      $pdf->SetXY($s[x],$s[y]);
      
      // === CABECERA DE PRODUCTOS
      $pdf->SetTextColor(255,255,255);
      $pdf->SetFont('Arial','B',9);
      $pdf->SetX($s[x]); //Arrimar hacia la izquierda
      $pdf->Cell($w[0],20,"CANTIDAD",0,0,"C",1); //Cantidad
      
      $y1 = $pdf->GetY();
      $pdf->Cell($w[1],20,"CONCEPTO",0,0,"C",1);
      $y2 = $pdf->GetY();
      $yH = $y2 - $y1;
      $pdf->SetXY($x + $w[1] + $w[0], $pdf->GetY() - $yH);
              
      $pdf->Cell($w[2],20,"P.U.",0,0,"C",1);
      $pdf->Cell($w[3],20,"IMPORTE",0,0,"C",1);
      $pdf->Cell(.001,$yH,"",0,1,"R");
      
      $pdf->SetXY($s[x]+$w[0],$s[y]+20);
      $pdf->SetTextColor(0,0,0);
      $pdf->SetFont('Arial','',9);
      // === FINALIZA CABECERA DE PRODUCTOS
      while($r = mysql_fetch_assoc($qq)){
		    $sub_total += round($r[cantidad]*$r[precio],2);
		  	$iva += round($r[cantidad]*$r[precio]*($r[iva]/100),2);
        // $sub_total += $r[cantidad]*$r[precio];
       // $descuento += $r[cantidad]*($r[precio]*($r[descuento]/100));
	   
        //$iva += (($r[cantidad]*$r[precio])-($r[cantidad]*($r[precio]*($r[descuento]/100))))*($r[iva]/100);

        $pdf->SetX($s[x]); //Arrimar hacia la izquierda
        $pdf->Cell($w[0],15,$r[cantidad]."     ",0,0,"C"); //Cantidad
        
        $y1 = $pdf->GetY(); //Indexar Y1
				
				$descripcion = $r[descripcion];
				if($r[complemento] != "0")
				{
					$descripcion .= "\n".$r[complemento];
				}
				
        $pdf->MultiCell($w[1],15,$descripcion,0); //Descripción
        $y2 = $pdf->GetY(); //Indexar Y2
        $yH = $y2 - $y1; // Diferencia
        $pdf->SetXY($x + $w[1] + $w[0], $pdf->GetY() - $yH); //Reindexar X
                
        $pdf->Cell($w[2],15,number_format($r[precio],2)."  ",0,0,"C"); //Precio
        $pdf->Cell($w[3],15,number_format($r[cantidad]*$r[precio],2)."  ",0,0,"C"); //Importe
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
    
    //CARNE: IMPORTES
    $pdf->SetFont('Arial','',11);
    $pdf->SetXY(420,644);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(72,12,"SUBTOTAL  ",0,0,"R");
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(76,12,number_format($sub_total,2),0,1,"L");
    $pdf->SetXY(420,660);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(72,12,"I.V.A.  ",0,0,"R");
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(76,12,number_format($iva,2),0,1,"L");
    $pdf->SetXY(420,676);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(72,12,"TOTAL  ",0,0,"R");
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(76,12,number_format($importe,2),0,1,"L");
    //FIN CARNE: IMPORTES
    
  }
}
$pdf->Output();
?>