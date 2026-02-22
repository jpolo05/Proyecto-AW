<?php
$contenidoPrincipal = <<<EOS
<div>
    <h2>Acceso a Bistro FDI</h2>
    <form action="includes/vistas/procesarLogin.php" method="POST">
        <label>Usuario:</label>
        <input type="text" name="username" required>
        
        <label>Contraseña:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Entrar</button>
    </form>
    <p>¿No tienes cuenta? <a href="index.php?pagina=register">Regístrate aquí</a></p>
</div>
EOS;