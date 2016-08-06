var db = 'comuni-k_' + ((document.URL.indexOf('acero') > -1) ? 'acero' : 'tornero');
$('#wait').hide();

function isOver()
{
	if($('.checkFactura:checked').size() == 0)
	{		
		$('#timbrar').show();
		$('#wait').hide();
		$('.checkFactura').attr('disabled', false);
	}
}

function error(o, msg)
{
	var td = o.parents('TD');
	var a = $('<a href="#"><img src="imagenes/cancel_editnow.png" /></a>');
	a.click(function(){ alert(msg); return false; });
	td.html(a);
	isOver();
}

function verificarTimbre(folio, serie, t)
{
	$.ajax({
		url: '../advans/verificar_timbre.php',
		type: 'post',
		dataType: 'json',
		data: {
			db: db,
			folio: folio,
			serie: serie
		},
		success: function(data){
			if(data.error == 0)
			{
				if(data.resultado > 0)
				{
					var td = t.parents('TD');
					var a = $('<a href="comuni-k.php?section=ventas_detalle&folio=' + folio + '&serie=' + serie + '" target="_' + folio + '_"><img src="imagenes/accept_editnow.png" /></a>');
					td.html(a);
					isOver();
				}
				else
				{
					setTimeout(function(){ verificarTimbre(folio, serie, t) }, 1000);
				}
			}
			else
			{
				error(t, data.msg);
			}
		},
		error: function(e, v){
			error(t, 'Error: ' + e.statusText);
		}
	});
}

$().ready(function(){
	$('#timbrar').click(function(){
		var cajitas = $('.checkFactura:checked');
		if(cajitas.size() > 0)
		{
			$('.checkFactura').attr('disabled', true);
			$('#timbrar').hide();
			$('#wait').show();
			cajitas.each(function(){
				var t = $(this);
				t.parents('TR').removeClass('tr_list_0').removeClass('tr_list_1').addClass('tr_list_over');
				var folio = t.attr('data-folio');
				var serie = t.attr('data-serie');
				
				//Generar el XML en la carpeta del conector
				$.ajax({
					url: '../advans/timbrar.php',
					type: 'post',
					dataType: 'json',
					data: {
						db: db,
						folio: folio,
						serie: serie
					},
					success: function(data){
						if(data.error > 0) error(t, data.msg);
						
						//Switch de acciones
						if(data.accion > 0)
						{
							switch(data.accion)
							{
								case 1: //Ya está timbrada. Recargar página
									verificarTimbre(folio, serie, t);
								break;
							}
						}
					},
					error: function(e, v){
						error(t, 'Ha ocurrido un error al intentar timbrar esta factura.\rRevise el histórico de Advans.');
					}
				});
			});
		}
		else
		{
			alert('Seleccione las facturas que desee timbrar.');
		}
	});
});