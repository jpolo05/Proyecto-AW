<?php

require_once __DIR__.'/../../config.php';

$tituloPagina = 'Eliminar mi usuario';


$contenidoPrincipal = <<<EOS
    <h1>Eliminación de cuenta</h1>

    <p> ¿Estas seguro de que quieres eliminar tu cuenta para siempre? <p>
    <form action="procesarBorrar.php" method="POST">
        <input type="submit" name="borrar" value="Si"></button>
    </form>
    <form action="login.php" method="POST">
        <input type="submit" name="cancelar" value="No"></button>
    </form>
EOS;


require __DIR__.'/../plantillas/plantilla.php';


/*
Otra forma de hacerlo por si acaso no fucniona la que está puesta
 <p> ¿Estas seguro de que quieres eliminar tu cuenta para siempre? <p>
    <a href="procesarBorrar.php">Sí</a>
    <a href="perfil.php">No</a>
*/