var db = 'comuni-k_' + ((document.URL.indexOf('acero') > -1) ? 'acero' : 'tornero');
$('#wait').hide();

function label(o, msg)
{
	o.html(msg);
}

function verificarCancelacion(folio, serie, t)
{
	$.ajax({
		url: '../advans/verificar_cancelacion.php',
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
					label(t, 'Cancelada')
				}
				else
				{
					setTimeout(function(){ verificarCancelacion(folio, serie, t) }, 1000);
				}
			}
			else
			{
				label(t, '<a href="javascript:alert(\'Error: ' + data.msg + '\')">Error</a>');
			}
		},
		error: function(e, v){
			label(t, '<a href="javascript:alert(\'Error: ' + e.statusText + '\')">Error</a>');
		}
	});
}

function cancelar_cfdi(folio, serie, t)
{
	var obj = t;
	if(confirm('Confirme la cancelación de este CFDI') > 0)
	{
		var t = $(t).parents('TD');
		label(t, 'Cancelando...');
		
		//Generar el XML en la carpeta del conector
		$.ajax({
			url: '../advans/cancelar.php',
			type: 'post',
			dataType: 'json',
			data: {
				db: db,
				folio: folio,
				serie: serie
			},
			success: function(data){
				if(data.error > 0) label(t, '<a href="javascript:alert(\'' + data.msg + '\')">Error</a>');
				
				//Switch de acciones
				if(data.accion > 0)
				{
					switch(data.accion)
					{
						case 1: //Ya está timbrada. Recargar página
							verificarCancelacion(folio, serie, t);
						break;
					}
				}
			},
			error: function(e, v){
				label(t, '<a href="javascript:alert(\'Error: ' + e.statusText + '\')">Error</a>');
			}
		});
	}
	else
	{
		$(obj).val(0);
	}
}