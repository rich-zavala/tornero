	var __resultado__;
	
	// Removes leading whitespaces
	function LTrim( value ) {
		var re = /\s*((\S+\s*)*)/;
		return value.replace(re, "$1");
		
	}
	// Removes ending whitespaces
	function RTrim( value ) {	
		var re = /((\s*\S+)*)\s*/;
		return value.replace(re, "$1");
	}
	
	// Removes leading and ending whitespaces
	function Trim( value ) {
		return LTrim(RTrim(value));
	}

	function AJAX() {
		// will store the reference to the XMLHttpRequest object 
	var xmlhttp;
	// if running Internet Explorer
	if(window.ActiveXObject){
		try{
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
    catch (e){
			xmlhttp = false;
		}
	}
	// if running Mozilla or other browsers
	else{
		try{
			xmlhttp = new XMLHttpRequest();
		}
    catch (e){
			xmlhttp = false;
		}
	}
	// return the created object or display an error message
		if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
			alert("Tienes una version muy antigua de tu navegador y no soporta comunicacion asincrona. Por favor, actualiza tu navegador.");
		}
		return xmlhttp;
	}
	
/*	function procesar(url){
		xmlhttp = new AJAX();
		if(xmlhttp){
			xmlhttp.open("GET", url, false);
			xmlhttp.onreadystatechange = handleHttpResponse;
			xmlhttp.send(null);
		}
		return __resultado__;
	}*/
  
function procesar(url){
  if (window.XMLHttpRequest){              
    AJAX=new XMLHttpRequest();              
  }
  else {                                  
    AJAX=new ActiveXObject("Microsoft.XMLHTTP");
  }
  if(AJAX){
    AJAX.open("GET", url, false);                             
    AJAX.send(null);
    return AJAX.responseText;                                         
  }
  else {
    return false;
  }                                             
}
	
	function handleHttpResponse(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
		   __resultado__ = xmlhttp.responseText;
		}
	}
		
	function cargar(url, destino, mensaje, metodo){
		// Verficamos si es GET o POST
		if(typeof(metodo) == "undefined" || Trim(metodo) == ""){ metodo = "GET"; }
		// Convertimos a mayusculas para mayor comodidad
		metodo = metodo.toUpperCase(metodo);
		// Si es metodo POST entonces dividimos la url:    url/variables
		if(metodo == "POST"){ var parametros = url.split("?"); }
		// Si hay parametros entonces se enviarán, de lo contrario, no se envia nada
		if(typeof(parametros) == "undefined" || typeof(parametros[1]) == "undefined"){ parametros = null; } else { parametros = parametros[1]; }
		// Validamos si se escribe el mensaje por defecto o alguno proporcionado por el mensaje
		if(typeof(mensaje) == "undefined" || Trim(mensaje) == "") { mensaje = "Cargando p&aacute;gina. Espere por favor..."; }
		// ID del DIV por defecto donde se mostrará la informacion procesada
		if(typeof(destino) == "undefined") { destino = "__contenido"; }
		// Obtenemos la referencia al objeto
		objeto = document.getElementById(destino);
		// Si es DIV o SPAN entonces nos procedemos a ejecutar
		if(objeto.tagName == "DIV" || objeto.tagName == "SPAN"){
			// Creamos una instancia del objeto XmlHttpRequest
			xmlhttp = new AJAX();
			// Si se creo la instacia del objeto entonces proceguimos
			if(xmlhttp){
				// Abrimos la URL de manera asincrona (el TRUE indica que será comunicacion asíncrona)
				xmlhttp.open(metodo, url, false);
				// Si es metodo POST entonces enviamos las cabeceras
				if(metodo == "POST"){
					xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					xmlhttp.setRequestHeader("Content-length", parametros.length);
					xmlhttp.setRequestHeader("Connection", "close");
				}
				// Enviamos la cabecera para que no se guarde en cache
				xmlhttp.setRequestHeader( "If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT" );
				// Desplegamos al usuario que se esta procesando la página
				objeto.INNERHTML = "<p class='centrar'><b>" + mensaje + "</b><br><img src='imagenes/loading.gif' /></p>";
				// Por cada cambio que haga el objeto XmlHttpRequest realizamos una validacion
				xmlhttp.onreadystatechange=function() {
					/* Activense en caso de que quiera mostrar mensajes para cada cambio de estado */
					/*
					if(xmlhttp.readyState == 1){ document.getElementById("aqui1").INNERHTML = "Cargando..."; }
					if(xmlhttp.readyState == 2){ document.getElementById("aqui2").INNERHTML = "Cargado..."; }
					if(xmlhttp.readyState == 3){ document.getElementById("aqui3").INNERHTML = "Interactuando..."; }
					*/
					// Si todo esta listo
					if(xmlhttp.readyState == 4) {
						// y no hubo error
						if(xmlhttp.status == 200){
							// Ejecutamos el código javascript que tenga la página
							if(ParceJS(xmlhttp.responseText)){ 
								// Regresamos los resultados al objeto.
								objeto.INNERHTML = xmlhttp.responseText;
								DespuesDeCargarProductos();
							}
						} else {
							// Todo listo, pero hubo un error
							objeto.INNERHTML = StatusMsg(xmlhttp.status);
						}
					}
				}
			} else {
				// Si entramos aqui significa que no se creo la instancia del objeto.
				return false;	
			}
		} else{
			objeto.src = url;
		}
		// Envimos parametros o NULL (dependiendo del metodo de envio)
		xmlhttp.send(parametros);
		// Regresamos un FALSE para que no se active el evento que llamo a la funcion
		return false;
	}
	
	function ParceJS( ObjResponse ){
		/**************************************
		funcion creada por Victor Manuel Agudelo : vicmany2k@hotmail.com 
		funcion encargada de recorrer el texto devuelto por el responseText de AJAX, e identificar codigo JavaScript
		y habilitarlos para modo ejecucion desde la pagina llamada
		
		Nota: cualquier mejora sobre el codigo hacermela saber 
		*************************************/     
	
		 if ( ObjResponse == "" )  {
			  alert("No se han enviado parametros a parcear");
			  return false;
		   }
		  //variable que almacena el texto del codigo javascript
		  var TextJs = "";
		  //almacena la cadena de texto a recorrer para encontrar el archivo incluido en lso js's
		  var TextSrc = "";
		  //arreglo que almacena cada uno de los archivos incluidos llamados por src
		  var FileJsSrc = new Array();
		  var counter=0;
		  //guarda las porciones siguientes de codigo de HTML que se van generando por cada recorrido del parceador
		  var TextNextHtml;
		  var PosJSTagStart;
		  var PosJSTagEnd;
		  //guarda la posicion de la primera ocurrencia del parametro src
		  var SrcPosIni;
		  //guarda la posicion de ocurrencia de las comillas
		  var SrcPosComilla;
		  while (ObjResponse.indexOf("<script") > 0) {
				/*encuentra la primera ocurrencia del tag <script*/
				PosJSTagStart = ObjResponse.indexOf("<script");
				/*corta el texto resultante desde la primera ocurrencia hasta el final del texto */
				TextNextHtml = ObjResponse.substring( PosJSTagStart,ObjResponse.length);					   
				/*encuentra la primera ocurrencia de finalizacion del tag >, donde cierra la palabra javascript*/
				PosJSTagEnd = TextNextHtml.indexOf(">");	
				//captura el texto entre el tag <script>
				TextSrc = TextNextHtml.substring(0,PosJSTagEnd);
				//verficica si tiene le texto src de llamado a un archivo js
				if ( TextSrc.indexOf("src") > 0) {
					//posicion del src
					 SrcPosIni = TextSrc.indexOf( "src" );
					 //almacena el texto desde la primera aparicion del src hasta el final
					 TextSrc = TextSrc.substring(SrcPosIni, PosJSTagEnd);
					 //lee la posicion de la primer comilla
					 SrcPosComilla = TextSrc.indexOf( '"' );
					 //arma el texto, desde la primer comilla hasta el final,se le suma 1, para pasar la comilla inicial
					 TextSrc = TextSrc.substring(SrcPosComilla + 1,PosJSTagEnd);				
					 //posicion de la comilla final
					 SrcPosComilla = TextSrc.indexOf('"');
					 //lee el archivo
					 SrcFileJs = TextSrc.substring(0, SrcPosComilla);
					 FileJsSrc[counter] = SrcFileJs;
					 counter++;
							  
				} 					   
				//TextNextHtml, nuevo porcion de texto HTML empezando desde el tag script
				TextNextHtml = TextNextHtml.substring(PosJSTagEnd + 1,ObjResponse.length);
				//encuentra el final del script
				objJSTagEndSc = TextNextHtml.indexOf("script>");
	
				/*recorre desde la primera ocurrencia del tag > hasta el final del script < /script>*/
				//se le resta 2 al objJSTagEndSc, para restarle el < /
				objJSText = TextNextHtml.substring(0, objJSTagEndSc - 2);
	
				ObjResponse = TextNextHtml;
				TextJs = TextJs + "\n" + objJSText;
						  
		 }					   
		// Agrego los scripts dentro del encabezado
		EvalScript = document.createElement("script");
		EvalScript.text = TextJs;
		document.getElementsByTagName('head')[0].appendChild(EvalScript);
		// Agrego los scripts incluidos dentro del encabezado
		for (i = 0; i <  FileJsSrc.length ;i++ ){
			EvalScript = document.createElement("script");
			EvalScript.src = FileJsSrc[i];
			document.getElementsByTagName('head')[0].appendChild(EvalScript);
		}
		return true;
	}
	
	function FormAJAX(f, destino, mensaje){
		// No envia archivos, solo datos
		var parametros = "";
		for (i=0; i<f.elements.length; i++) {
			objeto = f.elements[i];
			parametros += objeto.name+"="+objeto.value+"&";
		}
		url = f.action+"?"+parametros;
		cargar(url, destino, mensaje, f.method);
	}
	
	function StatusMsg(id){
		switch(id){
			case 100 :  msg = "Continue";  break;
			case 101 :  msg = "Switching protocols";  break;
			case 200 :  msg = "OK";  break;
			case 201 :  msg = "Created";  break;
			case 202 :  msg = "Accepted";  break;
			case 203 :  msg = "Non-Authoritative Information";  break;
			case 204 :  msg = "No Content";  break;
			case 205 :  msg = "Reset Content";  break;
			case 206 :  msg = "Partial Content";  break;
			case 300 :  msg = "Multiple Choices";  break;
			case 301 :  msg = "Moved Permanently";  break;
			case 302 :  msg = "Found";  break;
			case 303 :  msg = "See Other";  break;
			case 304 :  msg = "Not Modified";  break;
			case 305 :  msg = "Use Proxy";  break;
			case 307 :  msg = "Temporary Redirect";  break;
			case 400 :  msg = "Bad Request";  break;
			case 401 :  msg = "Unauthorized";  break;
			case 402 :  msg = "Payment Required";  break;
			case 403 :  msg = "Forbidden";  break;
			case 404 :  msg = "Not Found";  break;
			case 405 :  msg = "Method Not Allowed";  break;
			case 406 :  msg = "Not Acceptable";  break;
			case 407 :  msg = "Proxy Authentication Required";  break;
			case 408 :  msg = "Request Timeout";  break;
			case 409 :  msg = "Conflict";  break;
			case 410 :  msg = "Gone";  break;
			case 411 :  msg = "Length Required";  break;
			case 412 :  msg = "Precondition Failed";  break;
			case 413 :  msg = "Request Entity Too Large";  break;
			case 414 :  msg = "Request-URI Too Long";  break;
			case 415 :  msg = "Unsupported Media Type";  break;
			case 416 :  msg = "Requested Range Not Suitable";  break;
			case 417 :  msg = "Expectation Failed";  break;
			case 500 :  msg = "Internal Server Error";  break;
			case 501 :  msg = "Not Implemented";  break;
			case 502 :  msg = "Bad Gateway";  break;
			case 503 :  msg = "Service Unavailable";  break;
			case 504 :  msg = "Gateway Timeout";  break;
			case 505 :  msg = "HTTP Version Not Supported";  break;
		}
		return msg;
	}
