<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Mudanzas GL</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --rojo: #c0392b;
      --rojo-hover: #e74c3c;
      --negro: #0d0d0d;
      --negro-card: #1a1a1a;
      --gris: #2a2a2a;
      --borde: #333;
      --texto: #e8e8e8;
      --texto-dim: #888;
    }
 
    * { margin: 0; padding: 0; box-sizing: border-box; }
 
    body {
      font-family: 'Inter', sans-serif;
      background: var(--negro);
      color: var(--texto);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
 
    /* Fondo decorativo */
    body::before {
      content: 'GL';
      position: absolute;
      font-family: 'Bebas Neue', sans-serif;
      font-size: 40rem;
      color: rgba(192,57,43,0.04);
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
      user-select: none;
    }
 
    /* CARD */
    .card {
      background: var(--negro-card);
      border: 1px solid var(--borde);
      border-top: 3px solid var(--rojo);
      padding: 3rem 2.5rem;
      width: 100%;
      max-width: 400px;
      position: relative;
      z-index: 1;
    }
 
    .logo {
      text-align: center;
      margin-bottom: 2rem;
      padding-bottom: 2rem;
      border-bottom: 1px solid var(--borde);
    }
 
    .logo-icono { font-size: 2.5rem; margin-bottom: 0.5rem; }
 
    .logo-nombre {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 2.2rem;
      letter-spacing: 5px;
      color: var(--texto);
      line-height: 1;
    }
 
    .logo-nombre span { color: var(--rojo); }
 
    .logo-slogan {
      font-size: 0.65rem;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--texto-dim);
      margin-top: 0.3rem;
    }
 
    .card-titulo {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.6rem;
      letter-spacing: 3px;
      margin-bottom: 0.3rem;
    }
 
    .card-sub {
      font-size: 0.75rem;
      color: var(--texto-dim);
      margin-bottom: 1.8rem;
    }
 
    .form-group { margin-bottom: 1.2rem; }
 
    label {
      display: block;
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--texto-dim);
      margin-bottom: 0.5rem;
    }
 
    input[type="text"],
    input[type="password"] {
      width: 100%;
      background: var(--gris);
      border: 1px solid var(--borde);
      color: var(--texto);
      padding: 0.75rem 1rem;
      font-family: 'Inter', sans-serif;
      font-size: 0.9rem;
      outline: none;
      transition: border-color 0.2s;
    }
 
    input:focus { border-color: var(--rojo); }
    input::placeholder { color: #555; }
 
    .btns { margin-top: 2rem; display: flex; flex-direction: column; gap: 0.8rem; }
 
    .btn-primary {
      width: 100%;
      padding: 0.85rem;
      background: var(--rojo);
      color: white;
      border: none;
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.1rem;
      letter-spacing: 3px;
      cursor: pointer;
      transition: background 0.2s;
    }
 
    .btn-primary:hover { background: var(--rojo-hover); }
 
    .btn-secondary {
      width: 100%;
      padding: 0.75rem;
      background: transparent;
      color: var(--texto-dim);
      border: 1px solid var(--borde);
      font-family: 'Inter', sans-serif;
      font-size: 0.8rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      cursor: pointer;
      transition: border-color 0.2s, color 0.2s;
    }
 
    .btn-secondary:hover { border-color: var(--texto-dim); color: var(--texto); }
 
    #mensaje {
      margin-top: 1rem;
      font-size: 0.82rem;
      text-align: center;
      font-weight: 500;
      min-height: 1.2rem;
    }
 
    .card-footer {
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--borde);
      text-align: center;
      font-size: 0.78rem;
      color: var(--texto-dim);
    }
 
    .card-footer a { color: var(--rojo); text-decoration: none; }
    .card-footer a:hover { text-decoration: underline; }
  </style>
</head>
<body>
 
  <div class="card">
    <div class="logo">
      <div class="logo-icono">🚛</div>
      <div class="logo-nombre">MUDANZAS <span>GL</span></div>
      <div class="logo-slogan">Honestidad y Puntualidad</div>
    </div>
 
    <div class="card-titulo">Iniciar sesión</div>
    <div class="card-sub">Ingresa tus credenciales para continuar</div>
 
    <form id="formlogin" action="../app/login_procesar.php" method="POST">
    <div class="form-group">
        <label>Cédula de ciudadanía</label>
        <input type="text" name="cedula" id="usuario" placeholder="Número de documento" required>
    </div>
    <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="contrasena" id="password" placeholder="Tu contraseña" required>
    </div>

    <div class="btns">
        <button type="submit" class="btn-primary">Ingresar</button>
        <button type="reset" class="btn-secondary">Limpiar</button>
    </div>

    <p id="mensaje"></p>
</form>
 
    <div class="card-footer">
      ¿No tienes cuenta? <a href="registrousuariosGL.php">Regístrate aquí</a>
    </div>
  </div>
 
</body>
</html>