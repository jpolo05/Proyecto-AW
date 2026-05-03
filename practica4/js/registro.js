//Comprueba si el correo termina en @gmail.com
function correoValido(correo){
    return correo.toLowerCase().endsWith('@gmail.com');
}

$("#email").change(function(){ //Cuando cambia el campo email
    const campo = $("#email"); //Referencia jquery al campo
    campo[0].setCustomValidity(""); //Limpia validaciones previas
    $("#correoOk").hide(); //Oculta marca de correcto
    $("#correoMal").hide(); //Oculta marca de error
    
    const esCorreoValido = campo[0].checkValidity(); //Usa validacion HTML5 del input email
    if (esCorreoValido && correoValido(campo.val())) { //Si es email valido y acaba en @gmail.com
        $("#correoOk").show(); //Muestra marca correcta
        $("#correoMal").hide(); //Oculta marca incorrecta
        campo[0].setCustomValidity(""); //Deja el campo como valido
    } 
    else { //Si el correo no es valido
        $("#correoOk").hide(); //Oculta marca correcta
        $("#correoMal").show(); //Muestra marca incorrecta
        campo[0].setCustomValidity(
            "El correo debe ser valido y acabar por @gmail.com"); //Mensaje de error del navegador
    }
});
