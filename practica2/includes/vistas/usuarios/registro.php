<?php

require_once __DIR__.'/../../config.php';

$tituloPagina = 'Registro';

$contenidoPrincipal = <<<EOS
<div class="login-container">
    <h2>Acceso a Bistro FDI</h2>
    <form action="procesarRegistro.php" method="POST">
        <fieldset>
            <legend>Datos Usuario</legend>
            <div>
                <label for="username">Usuario:</label>
                <input id="username" type="text" name="username" required>
            </div>
            <div>
                <label for="nombre">Nombre:</label>
                <input id="nombre" type="text" name="nombre" required>
            </div>
            <div>
                <label for="apellidos">Apellidos:</label>
                <input id="apellidos" type="text" name="apellidos" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" required />
            </div>
            <div>
                <label for="password">Contraseña:</label>
                <input id="password" type="password" name="password" required />
            </div>
            <div>
                <label for="password_confirm">Introduzca la contraseña de nuevo:</label>
                <input id="password_confirm" type="password" name="password_confirm" required />
            </div>
            <div>
                <label>Rol: Cliente</label>
            </div>
            <div>
                <label for="imagen">Imagen:</label>
                <select name="imagen" id="imagen" >
                    <option value="default.jpg">Imagen por defecto</option>
                    <option value="avatar1.jpg">Avatar 1</option>
                    <option value="avatar2.jpg">Avatar 2</option>
                    <option value="avatar3.jpg">Avatar 3</option>
                    <option value="propia">Imagen Propia</option>
                </select>
            </div>
            <div id="subir_archivo">
                <label for="imgUser">Sube tu foto:</label>
                <input type="file" name="imgUser" id="imgUser" accept="image/*">
            </div>
            <div>
                <button type="submit">Create cuenta</button>
            </div>
        </fieldset>
    </form>
    <p>¿Ya tiene una cuenta? <a href="login.php">Inicia sesion aqui</a></p>
</div>
EOS;

require __DIR__.'/../plantillas/plantilla.php';