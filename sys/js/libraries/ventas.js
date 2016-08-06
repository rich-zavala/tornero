function eliminar(folio,serie)
{
	if(confirm("Confirme la eliminación de esta factura."))
	{
		procesar("ventas.php?eliminar="+folio+"&series="+serie);
		$("#tr_"+folio).remove();
	}
}

function cancelar_compra(folio,serie,obj){
	var e = obj.value;
	if(e == 1){
		if(confirm("¿Seguro que quiere cancelar la venta?")){
			url = "ventas.php?cancelar="+folio+"&series="+serie;
			var r = procesar(url);
			if(r == '1'){
				document.getElementById('estado_span'+folio).innerHTML = "Cancelada";
			}
			if(r == "error"){
				alert("Esta factura ya ha sido pagada.\nNo se puede cancelar.");
				obj.selectedIndex = 0;
			}
		} else{
			obj.selectedIndex = 0;
		}
	}
}