<?php 
include("funciones/basedatos.php");
include("funciones/funciones.php");
$f = factura_data($_GET[folio],$_GET[serie]);
ob_start();

echo '<?xml version="1.0" encoding="utf-8"?>';
//se valida que la compra tenga descuento
if(nu($f[factura][descuento]) != "0.00"){$descuento = 'descuento="'.nu($f[factura][descuento]).'"';}
//Se validan los campos del emisor
if($f[empresa][calle] != ""){$calleE = 'calle="'.$f[empresa][calle].'"';}
if($f[empresa][noe] != ""){$noexteriorE = 'noExterior="'.$f[empresa][noe].'"';}
if($f[empresa][noi] != ""){$nointeriorE = 'noInterior="'.$f[empresa][noi].'"';}
if($f[empresa][colonia] != ""){$coloniaE = 'colonia="'.$f[empresa][colonia].'"';}
if($f[empresa][localidad] != ""){$localidadE = 'localidad="'.$f[empresa][localidad].'"';}
if($f[empresa][municipio] != ""){$municipioE = 'municipio="'.$f[empresa][municipio].'"';}
if($f[empresa][estado] != ""){$estadoE = 'estado="'.$f[empresa][estado].'"';}
if($f[empresa][pais] != ""){$paisE = 'pais="'.strtoupper($f[empresa][pais]).'"';}
if($f[empresa][cp] != ""){$codigoPostalE = 'codigoPostal="'.$f[empresa][cp].'"';}	
//se validan los campos del cliente
if($f[cliente][calle] != ""){$calleC = 'calle="'.$f[cliente][calle].'"';}
if($f[cliente][noe] != ""){$noexteriorC = 'noExterior="'.$f[cliente][noe].'"';}
if($f[cliente][noi] != ""){$nointeriorC = 'noInterior="'.$f[cliente][noi].'"';}
if($f[cliente][colonia] != ""){$coloniaC = 'colonia="'.$f[cliente][colonia].'"';}
if($f[cliente][localidad] != ""){$localidadC = 'localidad="'.$f[cliente][localidad].'"';}
if($f[cliente][municipio] != ""){$municipioC = 'municipio="'.$f[cliente][municipio].'"';}
if($f[cliente][estado] != ""){$estadoC = 'estado="'.$f[cliente][estado].'"';}
if($f[cliente][pais] != ""){$paisC = 'pais="'.$f[cliente][pais].'"';}
if($f[cliente][cp] != ""){$codigoPostalC = 'codigoPostal="'.$f[cliente][cp].'"';}
?>
<Comprobante
	xmlns="http://www.sat.gob.mx/cfd/2"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sat.gob.mx/cfd/2 http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv2.xsd"
	LugarExpedicion="YUCATAN, MERIDA"
	version="2.2"
	metodoDePago="<?=$f[cliente][metodoDePago]?>"
	NumCtaPago="<?=$f[cliente][NumCtaPago]?>"
	folio="<?=$_GET[folio]?>"
	fecha="<?=$f[factura][fecha_sat]?>"
	noAprobacion="<?=$f[factura][noaprobacion]?>"
	anoAprobacion="<?=$f[factura][anoaprobacion]?>"
	formaDePago="PAGO EN UNA SOLA EXHIBICION"
	noCertificado="<?=$f[factura][nocertificado]?>"
	subTotal="<?=nu($f[factura][subtotal])?>"
	total="<?=nu($f[factura][importe])?>"
	tipoDeComprobante="ingreso"
	sello="<?=$f[sello]?>"
	Moneda="<?=$f[factura][moneda]?>"
	TipoCambio="<?=$f[empresa][dolar]?>"
	certificado="MIIEjDCCA3SgAwIBAgIUMDAwMDEwMDAwMDAyMDE0MzI1MDEwDQYJKoZIhvcNAQEFBQAwggGVMTgwNgYDVQQDDC9BLkMuIGRlbCBTZXJ2aWNpbyBkZSBBZG1pbmlzdHJhY2nDs24gVHJpYnV0YXJpYTEvMC0GA1UECgwmU2VydmljaW8gZGUgQWRtaW5pc3RyYWNpw7NuIFRyaWJ1dGFyaWExODA2BgNVBAsML0FkbWluaXN0cmFjacOzbiBkZSBTZWd1cmlkYWQgZGUgbGEgSW5mb3JtYWNpw7NuMSEwHwYJKoZIhvcNAQkBFhJhc2lzbmV0QHNhdC5nb2IubXgxJjAkBgNVBAkMHUF2LiBIaWRhbGdvIDc3LCBDb2wuIEd1ZXJyZXJvMQ4wDAYDVQQRDAUwNjMwMDELMAkGA1UEBhMCTVgxGTAXBgNVBAgMEERpc3RyaXRvIEZlZGVyYWwxFDASBgNVBAcMC0N1YXVodMOpbW9jMRUwEwYDVQQtEwxTQVQ5NzA3MDFOTjMxPjA8BgkqhkiG9w0BCQIML1Jlc3BvbnNhYmxlOiBDZWNpbGlhIEd1aWxsZXJtaW5hIEdhcmPDrWEgR3VlcnJhMB4XDTEyMDYyNjIzMTk1OFoXDTE2MDYyNjIzMTk1OFowgc0xJTAjBgNVBAMTHExBIENBU0EgREVMIFRPUk5FUk8gU0EgREUgQ1YxJTAjBgNVBCkTHExBIENBU0EgREVMIFRPUk5FUk8gU0EgREUgQ1YxJTAjBgNVBAoTHExBIENBU0EgREVMIFRPUk5FUk8gU0EgREUgQ1YxJTAjBgNVBC0THENUTzAyMDcwODRNMiAvIEhFQUw4MDA2MjhHTDgxHjAcBgNVBAUTFSAvIEhFQUw4MDA2MjhNWU5SVkwwMTEPMA0GA1UECxMGTUVSSURBMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDGp4gXPssFZFhANei9weixsqyfgrZMvl6BjOi9A0ZvkA+XIcukChsRKwvRXljvsY1J16uL5uDFKn1c6XfLDHFdBcsO95WtOezitEMVQmSEyrZh0eKhfvxxu8NtBjSlxDT/yvCwLsVND/4F30OQ9WGUG8qlO1RCJ6/fEugI0Owm8wIDAQABox0wGzAMBgNVHRMBAf8EAjAAMAsGA1UdDwQEAwIGwDANBgkqhkiG9w0BAQUFAAOCAQEAlOZ3fj3BhMJCXihe7I3jfr9STQoV6Z9IChZjQTxhbn6f4KpjP6mablP+g/4pyMx8kWiJ08HG4x/SyYDNcbLVbe0I1ocTNtZdKm4Q44E30w6x81GLZ5iyOhiIed8k9gDDVdBdjxY0Mut3LxNMJyO+Le+s7cyup0xcgp+mc6+v6ssIP8k0QBF/szYCHvHhxszMLPpuPDz+QCgnj/aC+6ZACDT16P1zpb+/KpQWOxLdRIn9jQaOsmk5NFMuIWOPGJ45U8w7GYUQLzM5NF0zgPMXQ1Jke/cYHYWxBJPulnJhilSMnVFWaK5wBDyQEnWmuuQSFDueqQXTnRPAGw+EZw9AWQ=="
	<?=$descuento?>
	>
  
	<Emisor rfc="<?=$f[empresa][rfc]?>" nombre="<?=$f[empresa][nombre]?>">  
    <DomicilioFiscal <?=$calleE?> <?=$noexteriorE?> <?=$nointeriorE?> <?=$coloniaE?> <?=$localidadE?> <?=$municipioE?> <?=$estadoE?> <?=$paisE?> <?=$codigoPostalE?> />
		<RegimenFiscal Regimen="REGIMEN GENERAL DE LEY PERSONAS MORALES" />
  </Emisor>
  <Receptor rfc="<?=$f[cliente][rfc]?>" nombre="<?=$f[cliente][nombre]?>">   
     <Domicilio <?=$calleC?> <?=$noexteriorC?> <?=$nointeriorC?> <?=$coloniaC?> <?=$localidadC?> <?=$municipioC?> <?=$estadoC?> <?=$paisC?> <?=$codigoPostalC?> />    
  </Receptor>
  <Conceptos>
	<?php
	foreach($f[productos] as $p)
	{	
	?>
		<Concepto cantidad="<?=$p[cantidad]?>" unidad="<?=$p[unidad]?>" descripcion="<?=$p[descripcion]?>" valorUnitario="<?=$p[precio]?>" importe="<?=nu($p[cantidad]*$p[precio])?>" />
	<?php
	}
	?>
	</Conceptos>
   <Impuestos totalImpuestosTrasladados="<?=nu($f[factura][iva])?>">
    <Traslados>
      <Traslado impuesto="IVA" tasa="16.00" importe="<?=nu($f[factura][iva])?>" />
    </Traslados>
  </Impuestos>
</Comprobante>
<?php
if(file_exists($smtp_file_xml)){unlink($smtp_file_xml);}
$xml = ob_get_clean();
$xml = utf8_encode($xml);
file_put_contents($smtp_file_xml, $xml);

sellarXML($smtp_file_xml);
?>