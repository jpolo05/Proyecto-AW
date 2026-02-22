<?php
$contenidoPrincipal = <<<EOS
<div class="login-container">
    <h2>Acceso a Bistro FDI</h2>
    <form action="procesarRegister.php" method="POST">
        <label>Usuario:</label>
        <input type="text" name="username" required>

        <label>Nombre:</label>
        <input type="text" name="nombre" required>
        <label>Apellidos:</label>
        <input type="text" name="apellidos" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>    
        <label>Repite la contraseña:</label>
        <input type="password" name="password_confirm" required>

        <label>Rol</label>
        <select name="rol" required>
            <option value="cliente">Cliente</option>
            <option value="gerente">Gerente</option>
            <option value="cocinero">Cocinero</option>
            <option value="camarero">Camarero</option>
        </select>

        <label>Imagen</label>
        <select name="imagen" id="imagen">
            <option value="default.jpg">Imagen por defecto</option>
            <option value="avatar1.jpg">Avatar 1</option>
            <option value="avatar2.jpg">Avatar 2</option>
            <option value="avatar3.jpg">Avatar 3</option>
            <option value="user.jpg"><input type="file" accept="image/*"></option>
        </select>
        
        <button type="submit">Create cuenta</button>
    </form>
    <p>¿Ya tiene una cuenta? <a href="index.php?pagina=login">Inicia sesion aqui</a></p>
</div>
EOS;