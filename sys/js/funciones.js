function submitenter(e,myfield,myform){
	var keycode;
	if(window.event){
		keycode = window.event.keyCode;
	} else if(e){
		keycode = e.which;
	} else{
		return true;
	}
	if(keycode == 13){
	 document.getElementsByName(myform)[0].submit();
	 return false;
	} else {
		return true;
	}
}

function pop_switch(page){
	var pop_0 = document.getElementById("pop_0");
	var pop_1 = document.getElementById("pop_1");
	
	if(pop_0.style.display==""){
		pop_0.style.display="none";
		pop_1.style.display="";
		url = "ajax_tools.php?pop="+page+"&val=1";
	} else {
		pop_0.style.display="";
		pop_1.style.display="none";
		url = "ajax_tools.php?pop="+page+"&val=0";
	}
	procesar(url);
}

function numero(obj,decimal){
	var decimal = parseInt(decimal);
	var num = parseFloat(obj.value);
	if(isNaN(num) || num<0){
		num = 0.00;
	}
	if(decimal == 0){
		num = parseInt(num);
	} else {
		num = num.toFixed(decimal);
	}
	obj.value = num;
}

function addLoadEvent(func) { 
 var oldonload = window.onload; 
 if (typeof window.onload != 'function') { 
   window.onload = func; 
 } else { 
   window.onload = function() { 
     if (oldonload) { 
       oldonload(); 
     } 
      func(); 
    } 
  } 
}

function money(num) {
num = num.toString().replace(/\$|\,/g,'');
if(isNaN(num))
num = "0";
sign = (num == (num = Math.abs(num)));
num = Math.floor(num*100+0.50000000001);
cents = num%100;
num = Math.floor(num/100).toString();
if(cents<10)
cents = "0" + cents;
for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
num = num.substring(0,num.length-(4*i+3))+','+
num.substring(num.length-(4*i+3));
return (((sign)?'':'-') + num + '.' + cents);
}

function filter_display(page){
	var checkbox = document.getElementById("filter_checkbox");
	var filter = document.getElementById("filtro");
	if(checkbox.checked){
		checkbox.checked = false;
		filter.setAttribute("class","filtro_principal_escondido");
		procesar("ajax_tools.php?filtro_listado="+page+"&val=1");
	} else {
		checkbox.checked = true;
		filter.setAttribute("class","filtro_principal");
		procesar("ajax_tools.php?filtro_listado="+page+"&val=0");
	}
}

function go(){
	document.getElementsByTagName("form")[0].submit();
}

function bak(cell){
	document.getElementById(cell).style.backgroundColor = "#218BF5";
	document.getElementById("link"+cell).style.color = "#FFFFFF";
}
function bak2(cell){
	document.getElementById(cell).style.backgroundColor = "";
	document.getElementById("link"+cell).style.color = "";
}
function goto(url){
	window.location = url;
}