<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro Colaboradores — Mudanzas GL</title>
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
    }
    header {
      background: var(--negro-card);
      border-bottom: 2px solid var(--rojo);
      padding: 1rem 2.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .header-marca { display: flex; align-items: center; gap: 0.8rem; }
    .header-icono { font-size: 1.8rem; }
    .header-nombre {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.8rem;
      letter-spacing: 3px;
      color: var(--texto);
      line-height: 1;
    }
    .header-nombre span { color: var(--rojo); }
    .header-tag {
      font-size: 0.7rem;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--texto-dim);
      border: 1px solid var(--borde);
      padding: 0.3rem 0.8rem;
    }
    nav {
      background: var(--negro-card);
      border-bottom: 1px solid var(--borde);
      padding: 0 2.5rem;
      display: flex;
    }
    nav a {
      text-decoration: none;
      color: var(--texto-dim);
      font-size: 0.78rem;
      font-weight: 500;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 1rem 1.5rem;
      border-bottom: 2px solid transparent;
      transition: color 0.2s, border-color 0.2s;
    }
    nav a:hover { color: var(--texto); }
    nav a.active { color: var(--rojo); border-bottom-color: var(--rojo); }
    .main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3rem 1.5rem;
      position: relative;
    }
    .main::before {
      content: 'GL';
      position: absolute;
      font-family: 'Bebas Neue', sans-serif;
      font-size: 28rem;
      color: rgba(192,57,43,0.04);
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
      user-select: none;
    }
    .card {
      background: var(--negro-card);
      border: 1px solid var(--borde);
      border-top: 3px solid var(--rojo);
      padding: 2.5rem;
      width: 100%;
      max-width: 500px;
      position: relative;
      z-index: 1;
    }
    .card-titulo {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 2rem;
      letter-spacing: 3px;
      color: var(--texto);
      margin-bottom: 0.3rem;
    }
    .card-sub {
      font-size: 0.75rem;
      color: var(--texto-dim);
      letter-spacing: 1px;
      margin-bottom: 2rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid var(--borde);
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
    input, select {
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
    input:focus, select:focus { border-color: var(--rojo); }
    input::placeholder { color: #555; }
    select option { background: var(--gris); }
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
    }
    .btn-secondary:hover { border-color: var(--texto-dim); color: var(--texto); }
    footer {
      background: var(--negro-card);
      border-top: 1px solid var(--borde);
      padding: 1rem 2.5rem;
      display: flex;
      justify-content: space-between;
      font-size: 0.72rem;
      color: var(--texto-dim);
    }
    footer span { color: var(--rojo); }
  </style>
</head>
<body>
 
  <header>
    <div class="header-marca">
      <div class="header-icono">🚛</div>
      <div>
        <div class="header-nombre">MUDANZAS <span>GL</span></div>
        <div style="font-size:0.65rem;letter-spacing:2px;color:#666;text-transform:uppercase">Honestidad y Puntualidad</div>
      </div>
    </div>
    <div class="header-tag">Registro de colaboradores</div>
  </header>
  
 <nav>
    <a href="registrousuariosGL.php">Cliente</a>
    <a href="registroColaborador.php" class="active">Colaborador</a>
    <a href="loginGL.php">Iniciar sesión</a>
</nav>
 
  <div class="main">
    <div class="card">
      <div class="card-titulo">Ser Colaborador</div>
      <div class="card-sub">Regístrate para recibir servicios de Mudanzas GL</div>

      <form action="../app/colaborador_procesar.php" method="POST">
        <div class="form-group">
          <label>Nombre completo</label>
          <input type="text" name="nombre" placeholder="Ej: Carlos Pérez" required>
        </div>
        <div class="form-group">
          <label>Cédula de ciudadanía</label>
          <input type="number" name="cedula" placeholder="Número de documento" required>
        </div>
        <div class="form-group">
          <label>Teléfono</label>
          <input type="tel" name="telefono" placeholder="Ej: 3113670240" required>
        </div>
        <div class="form-group">
          <label>Correo electrónico</label>
          <input type="email" name="correo" placeholder="Ej: carlos@mail.com" required>
        </div>
        <div class="form-group">
          <label>Tipo de vehículo</label>
          <select name="tipo_vehiculo" required>
            <option value="">Seleccionar</option>
            <option value="camioneta">Camioneta (hasta 1 ton)</option>
            <option value="camion_mediano">Camión mediano (hasta 5 ton)</option>
            <option value="camion_grande">Camión grande (hasta 15 ton)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Placa del vehículo</label>
          <input type="text" name="placa_vehiculo" placeholder="Ej: ABC123" required>
        </div>
               <div class="form-group">
          <label>Capacidad (toneladas)</label>
          <input type="number" step="0.1" name="capacidad_toneladas" placeholder="Ej: 5.0" required>
        </div>

        <div class="form-group">
          <label>Contraseña</label>
          <input type="password" name="contrasena" placeholder="Mínimo 6 caracteres" required>
        </div>

        <div class="btns">
      
          <button type="submit" class="btn-primary">Registrarme</button>
          <button type="reset" class="btn-secondary">Limpiar campos</button>
        </div>
      </form>
    </div>
  </div>
 
  <footer>
    <span>MUDANZAS GL</span>
    <span>© 2026 — Todos los derechos reservados</span>
  </footer>
 
</body>
</html>