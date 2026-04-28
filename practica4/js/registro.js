function correoValido(correo){
    return correo.toLowerCase().endsWith('@gmail.com');
}

$("#email").change(function(){
    const campo = $("#email"); // referencia jquery al campo
    campo[0].setCustomValidity(""); // limpia validaciones previas
    // validación html5, porque el campo es <input type="email" ...>
    $("#correoOk").hide();
    $("#correoMal").hide();
    
    const esCorreoValido = campo[0].checkValidity();
    if (esCorreoValido && correoValido(campo.val())) {
        // el correo es válido y acaba por @gmail.com

        $("#correoOk").show();
        $("#correoMal").hide();
        // <-- aquí pongo la marca apropiada, y quito (si la hay) la otra
        // y lo marco como válido
        campo[0].setCustomValidity("");
    } 
    else {
        // correo inválido: ponemos una marca e indicamos al usuario que no es válido
        
        $("#correoOk").hide();
        $("#correoMal").show();
        // <-- aquí pongo la marca apropiada, y quito (si la hay) la otra
        // y pongo un mensaje como no-válido
        campo[0].setCustomValidity(
            "El correo debe ser válido y acabar por @gmail.com");
    }
});
