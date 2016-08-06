function c(s){ try{console.log(s);}catch(e){} }
var _ajaxBussy_ = false;
$.ajaxSetup({
	method: 'post',
	dataType: 'json',
	complete: function(){ _ajaxBussy_ = false; }
});

//Campo de fecha
function setDatePicker()
{
	try
	{
		$('.date').datepicker({
			format: 'yyyy-mm-dd',
			language: 'es',
			autoclose: true
		});
	} catch(e){ c('No JS > datepicker'); }
}

//Alerta de error
var _errorMsg_ = 'Ha ocurrido un error. Intente de nuevo más tarde.';
function alertError(s)
{
	if(typeof s == 'undefined') s = _errorMsg_;
	alert(s);
}

function setPops(){
	$("[data-toggle=popover]").popover();
	$("[data-toggle=tooltip]").tooltip();
}

//Block y deBlock
function block(){ $.blockUI({ message: '&nbsp;' }); }
function unBlock(){
	$.unblockUI();
	$('.blockUI').remove();
}