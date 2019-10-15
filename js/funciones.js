function getUserIP(onNewIP) { //  onNewIp - your listener function for new IPs
    //compatibility for firefox and chrome
    var myPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
    var pc = new myPeerConnection({
        iceServers: []
    }),
    noop = function() {},
    localIPs = {},
    ipRegex = /([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/g,
    key;

    function iterateIP(ip) {
        if (!localIPs[ip]) onNewIP(ip);
        localIPs[ip] = true;
    }

     //create a bogus data channel
    pc.createDataChannel("");

    // create offer and set local description
    pc.createOffer().then(function(sdp) {
        sdp.sdp.split('\n').forEach(function(line) {
            if (line.indexOf('candidate') < 0) return;
            line.match(ipRegex).forEach(iterateIP);
        });

        pc.setLocalDescription(sdp, noop, noop);
    }).catch(function(reason) {
        // An error occurred, so handle the failure to connect
    });

    //listen for candidate events
    pc.onicecandidate = function(ice) {
        if (!ice || !ice.candidate || !ice.candidate.candidate || !ice.candidate.candidate.match(ipRegex)) return;
        ice.candidate.candidate.match(ipRegex).forEach(iterateIP);
    };
}

// Usage

getUserIP(function(ip){
    IPCliente=ip;
});

function ocultar_mostrar(){
	if(window.getComputedStyle(document.getElementById("seccion_comentarios")).display == "block"){
		document.getElementById("seccion_comentarios").style.display="none";
		document.getElementById("boton_mostrar").innerText="Mostrar panel de comentarios";
	}
	else{
		document.getElementById("seccion_comentarios").style.display="block";
		document.getElementById("boton_mostrar").innerText="Ocultar panel de comentarios";
	}
	return false;
}

function enviar_mensaje(){

	var name=nombreTwig;
	var email=emailTwig;
	var comentario=document.getElementById("textcomentario").value;

	var div=document.getElementById("comentarios_anteriores");
  var evento=numeroEvento;

  var comentarios = document.getElementById("comentarios_anteriores");
  var par1 = document.createElement("p");
  var par2 = document.createElement("p");
  var br = document.createElement("br");
  var hr = document.createElement("hr");

  var node1 = document.createTextNode(comentario);
  par1.appendChild(node1);
  var node2 = document.createTextNode(get_hora() + name);
  par2.appendChild(node2);

  comentarios.appendChild(hr);
  comentarios.appendChild(par1);
  comentarios.appendChild(par2);

  var IP = IPCliente;
  var urlString="Evento="+evento+"&IP="+IP+"&Nombre="+name+"&Correo="+email+"&Texto="+comentario;
  var urlDestino = "http://"+window.location.hostname+"/index.php";


$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
				});

	window.alert("Comentario añadido.");
	comentario.value="";
return false;
}

/* lo nuevo de scroll */
$(document).ready(function() {
    $('#key').on('keyup', function() {
        var key = $(this).val();
        var dataString = 'key='+key;
        var urlDestino = "http://"+window.location.hostname+"/index.php";
	$.ajax({
            type: "POST",
            url: urlDestino,
            data: dataString,
            success: function(data) {
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(1000).html(data);
                //Al hacer click en algua de las sugerencias
                $('.suggest-element').on('click', function(){
                        //Obtenemos la id unica de la sugerencia pulsada
                        var id = $(this).attr('id');
                        //Editamos el valor del input con data de la sugerencia pulsada
                        $('#key').val($('#'+id).attr('data'));
                        //Hacemos desaparecer el resto de sugerencias
                        $('#suggestions').fadeOut(1000);
                        //alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));

                        var urlDestino = "http://"+window.location.hostname+"/index.php";
                        var dataString = 'busqueda_evento='+$('#'+id).attr('data');

                        $.ajax({url: urlDestino,
                      				type: "POST",
                      				cache: false,
                      				data: dataString,
                      				succes: function ( ){return true;}
                      	});




                      return true;
                });
            }
        });
    });
});



function aplicar_filtro(){
	var prohibidas;
	prohibidas=JSON.parse(sessionStorage.getItem("prohibidas"));
	if (prohibidas==null){
		prohibidas=JSON.parse(prohibidasTwig);
		sessionStorage.setItem("prohibidas",prohibidasTwig);
	}

	var comentario=document.forms["formulario_comentarios"]["comentario"];

	var indice_prohibidas=0;
	while(indice_prohibidas<prohibidas.length){
		if(comentario.value.indexOf(prohibidas[indice_prohibidas])!=-1){
			var inicio = comentario.value.indexOf(prohibidas[indice_prohibidas]);
			total='*';
			for(i=1;i<prohibidas[indice_prohibidas].length;i++){
				total += '*';
			}
			comentario.value = comentario.value.replace(new RegExp(prohibidas[indice_prohibidas], 'ig'), total);
		}
		indice_prohibidas++;
	}

	return false;
}


function get_hora(){
  var todo = "(" ;
  var d = new Date();
  todo += d.getFullYear() + "-" +  (d.getMonth()+1) + "-" + d.getDate() + ") - " + d.getHours() + ":" + d.getMinutes() + " - " ;
  return todo;
}

/* Galeria */

var indiceImagen=0;

function cambioImagen(i){
	mostrarImagen(indiceImagen+i);
}

function mostrarImagen(n){
	console.log(n);
	var imagenes=document.getElementsByClassName("foto");

	if (n>=imagenes.length){
		n=0;
	}
	else if (n<0){
		n=imagenes.length-1;
	}
	var i;
	for (i=0;i<imagenes.length;i++){
		imagenes[i].style.display= "none";
	}

	console.log(imagenes);
	indiceImagen=n;
	imagenes[indiceImagen].style.display= "inline-block";
}


/* formularios */

function ValidarEmailyPasswordV2(email,password){
	var arroba_position;

	if (email==""){
		window.alert("Por favor introduce un email válido.");
		return false;
	}
	if (email.indexOf("@",0)<0){
		arroba_position=email.value.indexOf("@",0);
		window.alert("Por favor introduce un email válido.");
		return false;
	}
	if (email.indexOf(".",arroba_position)<0){
		window.alert("Por favor introduce un email válido.");
		return false;
	}
	if (password==""){
		window.alert("Por favor introduce una contraseña");
		return false;
	}
	return true;
}

function ValidarEmailyPassword(email,password){
	var arroba_position;

	if (email.value==""){
		window.alert("Por favor introduce tu email.");
		email.focus();
		return false;
	}
	if (email.value.indexOf("@",0)<0){
		arroba_position=email.value.indexOf("@",0);
		window.alert("Por favor introduce un email válido.");
		email.focus();
		return false;
	}
	if (email.value.indexOf(".",arroba_position)<0){
		window.alert("Por favor introduce un email válido.");
		email.focus();
		return false;
	}
	if (password.value==""){
		window.alert("Por favor introduce una contraseña");
		password.focus();
		return false;
	}
	return true;
}

function registrarse(){
	var emailsRegistrados=JSON.parse(emailsRegistradosTwig);
	var usersRegistrados=JSON.parse(usersRegistradosTwig);

	var name=document.forms["formulario_registro"]["nombre"];
	var email=document.forms["formulario_registro"]["email"];
	var password=document.forms["formulario_registro"]["password"];



	if (name.value==""){
		window.alert("Por favor introduce tu nombre.");
		name.focus();
		return false;
	}

	if (!ValidarEmailyPassword(email,password)) return false;

	var indice=0;
	while(indice<emailsRegistrados.length){
		if(name.value==usersRegistrados[indice]){
			window.alert("Lo sentimos, ese nombre de usuario ya está en uso.");
			name.focus();
			return false;
		}
		if(email.value==emailsRegistrados[indice]){
			window.alert("Lo sentimos, ese email ya está en uso.");
			email.focus();
			return false;
		}
		indice++;
	}

	return true;
}

function identificarse(){
	var emailsRegistrados=JSON.parse(emailsRegistradosTwig);
	var passRegistradas=JSON.parse(passRegistradasTwig);

	var email=document.forms["formulario_identificacion"]["email"];
	var password=document.forms["formulario_identificacion"]["password"];

	if (!ValidarEmailyPassword(email,password)) return false;

	var indice=0;
	while(indice<emailsRegistrados.length){
		if(email.value==emailsRegistrados[indice]){
			if(password.value==passRegistradas[indice]){

				return true;
			}

			else{
				window.alert("Lo sentimos, la contraseña no es correcta.");
				password.focus();
				return false;
			}
		}
		indice++;
	}
	window.alert("Lo sentimos, ese email no está registrado");
	email.focus();
	return false;
}

function modificarDatosPersonales(){
	var emailsRegistrados=JSON.parse(emailsRegistradosTwig);
	var usersRegistrados=JSON.parse(usersRegistradosTwig);
	var nombre=nombreTwig;
	var correo=correoTwig;
	var password=passwordTwig;
	var nombreform=document.forms["formulario_cambio_datos"]["nuevo-nombre"].value;
	var correoform=document.forms["formulario_cambio_datos"]["nuevo-email"].value;
	var passwordanteriorform=document.forms["formulario_cambio_datos"]["password"].value;
	var passwordform=document.forms["formulario_cambio_datos"]["nuevo-password"].value;
	var nuevonombre;
	var nuevapassword;
	var nuevocorreo;
	if (passwordanteriorform != password){
		window.alert("Introdujo una contraseña incorrecta.");
		return false;
	}

	if (nombreform!="")
		nuevonombre=nombreform;
	else
		nuevonombre=nombre;

	if (passwordform!="")
		nuevapassword=passwordform;
	else
		nuevapassword=password;

	if (correoform!="")
		nuevocorreo=correoform;
	else
		nuevocorreo=correo;

	if (!ValidarEmailyPasswordV2(nuevocorreo,nuevapassword)) return false;

	var indice=0;
	while(indice<emailsRegistrados.length){
		if (nuevonombre!=nombre){
			if(nuevonombre==usersRegistrados[indice]){
				window.alert("Lo sentimos, ese nombre de usuario ya está en uso.");
				return false;
			}
		}
		if (nuevocorreo!=correo){
			if(nuevocorreo==emailsRegistrados[indice]){
				window.alert("Lo sentimos, ese email ya está en uso.");
				return false;
			}
		}
		indice++;
	}

	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="Modificar="+true+"&nombre="+nuevonombre+"&email="+nuevocorreo+"&password="+nuevapassword+"&nombreanterior="+nombre;
	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});

	return true;
}

function editarComentario(idComentario){
	var texto_comentario=document.getElementById("texto-editable-"+idComentario);
	var texto_original = texto_comentario.innerText;
	texto_comentario.innerHTML="<textarea id='nuevo-texto-"+idComentario+"'>"+texto_original+"</textarea>"+
										"<button class='button' onclick="+"modificarComentario("+idComentario +")>Modificar</button>";
	document.getElementById("boton-editar-comentario-"+idComentario).style.display="none";
	return false;
}

function modificarComentario(idComentario){
	var texto_comentario=document.getElementById("nuevo-texto-"+idComentario);
	var nuevo_texto = texto_comentario.value+" (editado)";

	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="Modificar="+true+"&id_comentario="+idComentario+"&texto="+nuevo_texto;
	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});

	return true;
}

function eliminarComentario(idComentario){
	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="Eliminar="+true+"&id_comentario="+idComentario;
	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});
	document.getElementById(idComentario).style.display="none";

	return true;
}

function editarEvento(numeroEvento){
	var titulo_evento=document.getElementById("titulo-editable-"+numeroEvento);
	var contenido_evento=document.getElementById("contenido-editable-"+numeroEvento);
	var boton=document.getElementById("boton-editar-evento-"+numeroEvento);
	var titulo_original=titulo_evento.innerText;
	var contenido_original=contenido_evento.innerText;
	titulo_evento.innerHTML="<textarea id='nuevo-titulo-"+numeroEvento+"'>"+titulo_original+"</textarea>";
	contenido_evento.innerHTML="<textarea id='nuevo-contenido-"+numeroEvento+"'>"+contenido_original+"</textarea>";
	boton.innerHTML="<button class='button' onclick='return modificarEvento("+numeroEvento+")'> Modificar Evento</button>";
	return false;
}


function modificarEvento(numeroEvento){
	var nuevo_titulo=document.getElementById("nuevo-titulo-"+numeroEvento).value;
	var nuevo_texto =document.getElementById("nuevo-contenido-"+numeroEvento).value;
	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="Modificar="+true+"&evento="+numeroEvento+"&titulo="+nuevo_titulo+"&texto="+nuevo_texto;
	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});

	return true;
}

function eliminarEvento(numeroEvento){
	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="Eliminar="+true+"&evento="+numeroEvento;
	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});
	document.getElementById(numeroEvento).style.display="none"

	return true;
}

function eliminarImagen(numeroEvento, path){
	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="Eliminar="+true+"&evento="+numeroEvento+"&imagen="+path;
	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});
	document.getElementById(numeroEvento+'-'+path).style.display="none";

	return true;
}


function addImagen(numeroEvento){

	var imagen=document.forms["formulario_add_imagen-"+numeroEvento]["nueva-imagen"].value;


	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="Add="+true+"&evento="+numeroEvento+"&imagen="+imagen;
	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});


}

function addEvento(){
	var titulo=document.forms["formulario_add_evento"]["nuevo-titulo"].value;
	var organizadores=document.forms["formulario_add_evento"]["nuevo-organizadores"].value;
	var texto=document.forms["formulario_add_evento"]["nuevo-texto"].value;
	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="Add="+true+"&titulo="+titulo+"&organizadores="+organizadores+"&texto="+texto;
	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});

	return true;
}
function modificarUsuario(nombreUsuario){
	var nuevo_rol = document.getElementById("nuevo-rol-"+nombreUsuario).value;
	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="Modificar="+true+"&user="+nombreUsuario+"&rol="+nuevo_rol;


	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});

	return true;
}



function publicar(evento,publicado){

  var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="publicado="+publicado+"&evento="+evento;


	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});

	return true;

}


function buscar_Evento(){
	var key = document.getElementById("key").value;
	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="busqueda_evento="+key;


	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});

	return true;
}


function buscar_Comentario(){
	var key = document.getElementById("key").value;
	var urlDestino = "http://"+window.location.hostname+"/index.php";
	var urlString="busqueda_comentario="+key;


	$.ajax({url: urlDestino,
				type: "POST",
				cache: false,
				data: urlString,
				succes: function (){window.alert("AJAX CORRECTO");}
	});

	return true;
}
