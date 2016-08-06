var db = 'comuni-k_' + ((document.URL.indexOf('acero') > -1) ? 'acero' : 'tornero');
var error = 0;
function p(s){ $('._timbrar_').parent().empty().html('<b class="_timbrar_">' + s + '</b>') }
function c(s){ console.log(s) }
function verificarTimbre(folio, serie)
{
	if(error == 0)
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
						var vinculos = '<a href="../advans/get_file.php?tipo=xml&db=' + db + '&folio=' + folio + '&serie=' + serie + '" class="vinculo_cfdi" target="_myxml">Descargar XML</a>'
													+'<a href="../advans/get_file.php?tipo=pdf&db=' + db + '&folio=' + folio + '&serie=' + serie + '" class="vinculo_cfdi" target="_mypdf">Descargar PDF</a>';
						p(vinculos);
					}
					else
					{
						setTimeout(function(){ verificarTimbre(folio, serie) }, 2000);
					}
				}
				else
				{
					p(data.msg);
				}
			},
			error: function(e, v){
				p('Error: ' + e.statusText);
			}
		});
	}
}

$().ready(function(){
	$('._timbrar_').click(function(e){
		e.preventDefault();
		p('Solicitando timbre. Por favor, espere...');
		var t = $(this);
		var folio = t.attr('data-folio');
		var serie = t.attr('data-serie');
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
				if(data.error > 0)
				{
					p(data.msg);
					error++;
				}
				
				//Switch de acciones
				if(data.accion > 0)
				{
					switch(data.accion)
					{
						case 1: //Ya está timbrada. Recargar página
							verificarTimbre(folio, serie);
						break;
					}
				}
			},
			error: function(e, v){
				p('Error: ' + e.statusText);
			}
		});
	});
});