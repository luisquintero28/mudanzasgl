<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: loginGL.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal — Mudanzas GL</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
      padding: 1.2rem 3rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
 
    .header-marca { display: flex; align-items: center; gap: 1rem; }
    .header-icono { font-size: 2rem; }
    .header-nombre {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 2rem;
      letter-spacing: 4px;
      line-height: 1;
    }
    .header-nombre span { color: var(--rojo); }
    .header-slogan {
      font-size: 0.65rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--texto-dim);
    }
    .header-right { display: flex; align-items: center; gap: 1rem; }
    .btn-salir {
      background: transparent;
      border: 1px solid var(--borde);
      color: var(--texto-dim);
      padding: 0.5rem 1.2rem;
      font-family: 'Inter', sans-serif;
      font-size: 0.72rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      cursor: pointer;
      transition: border-color 0.2s, color 0.2s;
      text-decoration: none;
    }
    .btn-salir:hover { border-color: var(--rojo); color: var(--rojo); }
 
    nav {
      background: var(--negro-card);
      border-bottom: 1px solid var(--borde);
      padding: 0 3rem;
      display: flex;
      gap: 0;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    nav a {
      text-decoration: none;
      color: var(--texto-dim);
      font-size: 0.75rem;
      font-weight: 500;
      letter-spacing: 2px;
      text-transform: uppercase;
      padding: 1rem 1.5rem;
      border-bottom: 2px solid transparent;
      transition: color 0.2s, border-color 0.2s;
    }
    nav a:hover { color: var(--texto); }
    nav a.active { color: var(--rojo); border-bottom-color: var(--rojo); }
 
    .hero {
      background: var(--negro-card);
      background-image: 
        linear-gradient(rgba(13, 13, 13, 0.85), rgba(13, 13, 13, 0.85)),
        url('imagenes/Pereira-Eje-Cafetero.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      border-bottom: 1px solid var(--borde);
      padding: 6rem 3rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 2rem;
      position: relative;
      overflow: hidden;
      min-height: 500px;
    }
 
    .hero::before {
      content: 'GL';
      position: absolute;
      right: -2rem;
      top: 50%;
      transform: translateY(-50%);
      font-family: 'Bebas Neue', sans-serif;
      font-size: 18rem;
      color: rgba(192,57,43,0.06);
      pointer-events: none;
      user-select: none;
      line-height: 1;
    }
 
    .hero-content { max-width: 600px; position: relative; z-index: 1; }
    .hero-tag {
      display: inline-block;
      font-size: 0.65rem;
      letter-spacing: 4px;
      text-transform: uppercase;
      color: var(--rojo);
      border: 1px solid rgba(192,57,43,0.4);
      padding: 0.3rem 0.8rem;
      margin-bottom: 1.2rem;
    }
    .hero-titulo {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(2.5rem, 5vw, 4.5rem);
      letter-spacing: 4px;
      line-height: 1;
      margin-bottom: 1rem;
    }
    .hero-titulo span { color: var(--rojo); }
    .hero-desc {
      font-size: 0.9rem;
      color: var(--texto-dim);
      line-height: 1.7;
      max-width: 480px;
    }
    .hero-stats {
      display: flex;
      gap: 2.5rem;
      margin-top: 2rem;
      padding-top: 2rem;
      border-top: 1px solid var(--borde);
    }
    .stat-num {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 2.2rem;
      color: var(--rojo);
      letter-spacing: 2px;
      line-height: 1;
    }
    .stat-label {
      font-size: 0.65rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--texto-dim);
      margin-top: 0.2rem;
    }
 
    main { padding: 3rem; flex: 1; }
 
    .seccion-titulo {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 0.9rem;
      letter-spacing: 4px;
      color: var(--texto-dim);
      text-transform: uppercase;
      margin-bottom: 1.5rem;
      margin-top: 4rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .seccion-titulo::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--borde);
    }
 
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 1px;
      background: var(--borde);
      margin-bottom: 3rem;
    }
    .card-servicio {
      background: var(--negro-card);
      padding: 2rem;
      transition: background 0.2s;
      cursor: default;
      position: relative;
      overflow: hidden;
    }
    .card-servicio::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 3px; height: 0;
      background: var(--rojo);
      transition: height 0.3s ease;
    }
    .card-servicio:hover::before { height: 100%; }
    .card-servicio:hover { background: #1f1f1f; }
    .servicio-icon { font-size: 1.8rem; margin-bottom: 1rem; }
    .servicio-nombre {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.3rem;
      letter-spacing: 2px;
      margin-bottom: 0.5rem;
      color: var(--texto);
    }
    .servicio-desc {
      font-size: 0.82rem;
      color: var(--texto-dim);
      line-height: 1.6;
    }
 
    .cotizacion {
      background: var(--negro-card);
      border: 1px solid var(--borde);
      border-left: 3px solid var(--rojo);
      padding: 2.5rem;
      margin-bottom: 3rem;
    }
    .cotizacion h3 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.5rem;
      letter-spacing: 3px;
      margin-bottom: 0.5rem;
    }
    .cotizacion p {
      font-size: 0.82rem;
      color: var(--texto-dim);
      margin-bottom: 1.5rem;
    }
    .cot-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1rem;
    }
    .form-group label {
      display: block;
      font-size: 0.68rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--texto-dim);
      margin-bottom: 0.4rem;
    }
    select, input[type="text"], input[type="number"] {
      width: 100%;
      background: var(--gris);
      border: 1px solid var(--borde);
      color: var(--texto);
      padding: 0.7rem 0.9rem;
      font-family: 'Inter', sans-serif;
      font-size: 0.85rem;
      outline: none;
      transition: border-color 0.2s;
    }
    select:focus, input:focus { border-color: var(--rojo); }
    select option { background: var(--gris); }
 
    .btn-cotizar {
      background: var(--rojo);
      color: white;
      border: none;
      padding: 0.8rem 2rem;
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1rem;
      letter-spacing: 3px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn-cotizar:hover { background: var(--rojo-hover); }
 
    #resultado-cotizacion {
      margin-top: 1rem;
      padding: 1rem;
      background: var(--gris);
      border-left: 3px solid var(--rojo);
      font-size: 0.88rem;
      display: none;
    }
 
    .vehiculos-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1px;
      background: var(--borde);
      margin-bottom: 3rem;
    }
    .vehiculo-card {
      background: var(--negro-card);
      padding: 1.8rem;
      text-align: center;
    }
    .vehiculo-icon { font-size: 2.5rem; margin-bottom: 0.8rem; }
    .vehiculo-tipo {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.1rem;
      letter-spacing: 2px;
      color: var(--texto);
    }
    .vehiculo-cap {
      font-size: 0.75rem;
      color: var(--rojo);
      margin-top: 0.3rem;
      letter-spacing: 1px;
    }
    .vehiculo-desc {
      font-size: 0.78rem;
      color: var(--texto-dim);
      margin-top: 0.5rem;
      line-height: 1.5;
    }
 
    .interaccion {
      background: var(--negro-card);
      border: 1px solid var(--borde);
      padding: 2rem;
      margin-bottom: 3rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
    }
    .interaccion h3 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.2rem;
      letter-spacing: 2px;
    }
    .interaccion p {
      font-size: 0.8rem;
      color: var(--texto-dim);
      margin-top: 0.2rem;
    }
    .temas { display: flex; gap: 0.5rem; }
    .tema-btn {
      width: 32px;
      height: 32px;
      border: 2px solid var(--borde);
      cursor: pointer;
      transition: border-color 0.2s, transform 0.2s;
    }
    .tema-btn:hover { border-color: var(--texto); transform: scale(1.1); }
    .tema-btn.activo { border-color: white; }

    .contacto-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1px;
      background: var(--borde);
      margin-bottom: 2rem;
    }
    .contacto-card {
      background: var(--negro-card);
      padding: 2rem;
      text-decoration: none;
      color: var(--texto);
      transition: background 0.2s, transform 0.2s;
      position: relative;
      overflow: hidden;
      display: block;
    }
    .contacto-card:hover {
      background: #1f1f1f;
      transform: translateY(-3px);
    }
    .contacto-icon { font-size: 2rem; margin-bottom: 1rem; }
    .contacto-titulo {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.2rem;
      letter-spacing: 2px;
      margin-bottom: 0.3rem;
    }
    .contacto-dato {
      color: var(--rojo);
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    .contacto-desc {
      font-size: 0.78rem;
      color: var(--texto-dim);
      line-height: 1.5;
    }
    .horario {
      background: var(--negro-card);
      border: 1px solid var(--borde);
      border-left: 3px solid var(--rojo);
      padding: 1.5rem;
      text-align: center;
    }
    .horario h3 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.2rem;
      letter-spacing: 2px;
      margin-bottom: 0.5rem;
    }
    .horario p {
      color: var(--texto-dim);
      font-size: 0.85rem;
    }
 
    footer {
      background: var(--negro-card);
      border-top: 2px solid var(--rojo);
      padding: 1.5rem 3rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
    }
    .footer-marca {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.2rem;
      letter-spacing: 3px;
    }
    .footer-marca span { color: var(--rojo); }
    .footer-copy {
      font-size: 0.72rem;
      color: var(--texto-dim);
    }
 
    .reveal {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    .botones-cotizar {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .btn-limpiar {
      background: transparent;
      color: var(--texto-dim);
      border: 1px solid var(--borde);
      padding: 0.8rem 2rem;
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1rem;
      letter-spacing: 3px;
      cursor: pointer;
      transition: border-color 0.2s, color 0.2s;
    }
    .btn-limpiar:hover {
      border-color: var(--rojo);
      color: var(--rojo);
    }
    .servicios-adicionales {
      margin-top: 1.5rem;
      margin-bottom: 1.5rem;
    }
    .servicios-titulo {
      display: block;
      font-size: 0.68rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--texto-dim);
      margin-bottom: 0.8rem;
      font-weight: 600;
    }
    .servicios-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1rem;
    }
    .servicio-check {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background: var(--gris);
      border: 1px solid var(--borde);
      padding: 0.8rem 1rem;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
      font-size: 0.85rem;
      text-transform: none;
      letter-spacing: 0;
      font-weight: 400;
      margin: 0;
    }
    .servicio-check:hover { border-color: var(--rojo); }
    .servicio-check input[type="checkbox"] {
      width: auto;
      cursor: pointer;
      accent-color: var(--rojo);
    }
    .servicio-check input[type="checkbox"]:checked + span {
      color: var(--rojo);
      font-weight: 600;
    }

    /* MAPA */
 .mapa-container {
    max-width: 100%;
    width: 100%;
    margin-left: 0;
    margin-right: 0;
}
    .mapa-header {
      padding: 1.5rem 2rem;
      border-bottom: 1px solid var(--borde);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }
    .mapa-header h3 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.4rem;
      letter-spacing: 3px;
      margin-bottom: 0.3rem;
    }
    .mapa-header p {
      font-size: 0.82rem;
      color: var(--texto-dim);
    }
    .mapa-header a {
      color: var(--rojo);
      font-size: 0.8rem;
      text-decoration: none;
      letter-spacing: 1px;
      text-transform: uppercase;
      font-weight: 600;
      border: 1px solid var(--rojo);
      padding: 0.5rem 1rem;
      transition: background 0.2s, color 0.2s;
    }
    .mapa-header a:hover {
      background: var(--rojo);
      color: white;
    }
    #mapa-mudanzas {
      width: 100%;
      height: 300px;
      background: var(--gris);
    }

    /* MODAL FOTOS CLIENTE */
    .modal-fotos {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.9);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .modal-fotos.activo { display: flex; }
    .modal-fotos-content {
      background: var(--negro-card);
      border: 1px solid var(--borde);
      border-top: 3px solid var(--rojo);
      padding: 2.5rem;
      max-width: 800px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      position: relative;
    }
    .btn-cerrar-modal {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: transparent;
      border: none;
      color: var(--texto-dim);
      font-size: 1.5rem;
      cursor: pointer;
    }
    .btn-ver-fotos {
      background: transparent;
      border: 1px solid var(--rojo);
      color: var(--rojo);
      padding: 0.4rem 0.8rem;
      font-size: 0.72rem;
      letter-spacing: 1px;
      text-transform: uppercase;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s, color 0.2s;
    }
    .btn-ver-fotos:hover {
      background: var(--rojo);
      color: white;
    }
    .categoria-tabs {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
      border-bottom: 1px solid var(--borde);
      padding-bottom: 0.5rem;
    }
    .cat-tab {
      background: transparent;
      border: none;
      color: var(--texto-dim);
      padding: 0.5rem 1rem;
      font-size: 0.8rem;
      letter-spacing: 1px;
      text-transform: uppercase;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      transition: color 0.2s, border-color 0.2s;
    }
    .cat-tab:hover { color: var(--texto); }
    .cat-tab.activo {
      color: var(--rojo);
      border-bottom-color: var(--rojo);
    }
    .galeria-grid-cliente {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 1rem;
    }
    .foto-item-cliente {
      position: relative;
      aspect-ratio: 1;
      border: 1px solid var(--borde);
      overflow: hidden;
      border-radius: 3px;
    }
    .foto-item-cliente img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      cursor: pointer;
      transition: transform 0.2s;
    }
    .foto-item-cliente img:hover {
      transform: scale(1.05);
    }
    .sin-fotos-cliente {
      text-align: center;
      padding: 2rem;
      color: var(--texto-dim);
      font-size: 0.85rem;
      border: 1px dashed var(--borde);
      border-radius: 3px;
    }

    /* ============================================
   RESPONSIVE - CELULARES
   ============================================ */
@media (max-width: 768px) {

    /* Header más compacto */
    header {
        padding: 1rem 1rem;
        flex-direction: column;
        gap: 0.5rem;
    }

    .header-nombre {
        font-size: 1.4rem;
        letter-spacing: 2px;
    }

    .header-right {
        flex-wrap: wrap;
        justify-content: center;
        font-size: 0.75rem;
    }

    /* Nav scrolleable */
    nav {
        padding: 0 0.5rem;
        overflow-x: auto;
        flex-wrap: nowrap;
    }

    nav a {
        padding: 0.8rem 0.8rem;
        font-size: 0.65rem;
        white-space: nowrap;
    }

    /* Hero ajustado */
    .hero {
        padding: 3rem 1rem;
        min-height: auto;
    }

    .hero-titulo {
        font-size: 2rem;
        letter-spacing: 2px;
    }

    .hero-stats {
        gap: 1rem;
        flex-wrap: wrap;
    }

    .stat-num {
        font-size: 1.5rem;
    }

    /* Main padding reducido */
    main {
        padding: 1.5rem 1rem;
    }

    /* Cotización en 1 columna */
    .cot-grid {
        grid-template-columns: 1fr;
    }

    /* Servicios en 1 columna */
    .grid {
        grid-template-columns: 1fr;
    }

    /* Servicios adicionales en 1 columna */
    .servicios-grid {
        grid-template-columns: 1fr;
    }

    /* Vehículos en 1 columna */
    .vehiculos-grid {
        grid-template-columns: 1fr;
    }

    /* Contacto en 1 columna */
    .contacto-grid {
        grid-template-columns: 1fr;
    }

    /* Mapa más pequeño en celular */
    #mapa-mudanzas {
        height: 250px;
    }

    .mapa-container {
        max-width: 100%;
    }

    .mapa-header {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
    }

    /* Modales más compactos */
    .modal-fotos-content,
    .modal-fotos > div > div {
        padding: 1.5rem;
        max-width: 95%;
    }

    /* Tablas scrolleables */
    #lista-mis-mudanzas {
        overflow-x: auto;
        padding: 1rem !important;
    }

    #lista-mis-mudanzas table {
        min-width: 600px;
        font-size: 0.75rem;
    }

    /* Notificaciones */
    #dropdown-notif {
        width: calc(100vw - 2rem) !important;
        right: -1rem !important;
    }

    /* Botones */
    .btn-cotizar,
    .btn-limpiar {
        width: 100%;
        padding: 0.7rem 1rem;
        font-size: 0.85rem;
    }

    /* Títulos */
    .seccion-titulo {
        font-size: 0.8rem;
        letter-spacing: 2px;
        margin-top: 2rem;
    }
}
  </style>
</head>
<body>
 
  <header>
    <div class="header-marca">
      <div class="header-icono">🚛</div>
      <div>
        <div class="header-nombre">MUDANZAS <span>GL</span></div>
        <div class="header-slogan">Honestidad y Puntualidad</div>
      </div>
    </div>
    <div class="header-right">
      <span style="color:var(--texto-dim); font-size:0.85rem;">
        Hola, <strong style="color:var(--texto);"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong> 👋
      </span>

      <div style="position:relative;">
        <button onclick="toggleNotificaciones()" style="
          background:transparent; border:1px solid var(--borde); color:var(--texto);
          padding:0.5rem 0.8rem; font-size:1.1rem; cursor:pointer;
          position:relative; transition:border-color 0.2s;
        " onmouseover="this.style.borderColor='var(--rojo)'" onmouseout="this.style.borderColor='var(--borde)'">
          🔔
          <span id="badge-notif" style="
            display:none; position:absolute; top:-6px; right:-6px;
            background:#e74c3c; color:white; font-size:0.65rem;
            font-weight:600; padding:2px 6px; border-radius:10px;
            min-width:18px; text-align:center;
          ">0</span>
        </button>

        <div id="dropdown-notif" style="
          display:none; position:absolute; top:calc(100% + 10px); right:0;
          width:360px; max-height:400px; overflow-y:auto;
          background:var(--negro-card); border:1px solid var(--borde);
          border-top:3px solid var(--rojo); z-index:1000;
          box-shadow:0 10px 30px rgba(0,0,0,0.5);
        ">
          <div style="padding:1rem; border-bottom:1px solid var(--borde); display:flex; justify-content:space-between; align-items:center;">
            <strong style="font-family:'Bebas Neue',sans-serif; letter-spacing:2px;">Notificaciones</strong>
            <button onclick="marcarLeidas()" style="background:transparent; border:none; color:var(--rojo); font-size:0.7rem; cursor:pointer; letter-spacing:1px; text-transform:uppercase;">Marcar leídas</button>
          </div>
          <div id="lista-notif" style="padding:0;">
            <div style="padding:1.5rem; text-align:center; color:var(--texto-dim); font-size:0.85rem;">Cargando...</div>
          </div>
        </div>
      </div>

      <a href="../app/logout.php" class="btn-salir">Cerrar sesión</a>
    </div>
  </header>
 
  <nav>
    <a href="#inicio" class="active">Inicio</a>
    <a href="#servicios">Servicios</a>
    <a href="#vehiculos">Vehículos</a>
    <a href="#cotizacion">Cotización</a>
    <a href="#mis-mudanzas">Mis mudanzas</a>
    <a href="#contacto">Contacto</a>
  </nav>
 
  <section class="hero" id="inicio">
    <div class="hero-content">
      <div class="hero-tag">Portal de servicios</div>
      <h1 class="hero-titulo">
        Tu mudanza,<br/>
        nuestra <span>misión</span>
      </h1>
      <p class="hero-desc">
        Servicios locales y nacionales con seguridad, puntualidad y el mejor equipo de transporte de carga.
      </p>
      <div class="hero-stats">
        <div>
          <div class="stat-num">100%</div>
          <div class="stat-label">Seguro</div>
        </div>
        <div>
          <div class="stat-num">24/7</div>
          <div class="stat-label">Disponible</div>
        </div>
        <div>
          <div class="stat-num">+500</div>
          <div class="stat-label">Mudanzas</div>
        </div>
      </div>
    </div>
  </section>

  <main>
 
    <section id="servicios">
      <div class="seccion-titulo reveal">Nuestros servicios</div>
      <div class="grid">
        <div class="card-servicio reveal">
          <div class="servicio-icon">🏠</div>
          <div class="servicio-nombre">Mudanza local</div>
          <p class="servicio-desc">Traslado dentro de la ciudad con personal capacitado y vehículos adecuados para cada tipo de mudanza.</p>
        </div>
        <div class="card-servicio reveal">
          <div class="servicio-icon">🗺️</div>
          <div class="servicio-nombre">Mudanza nacional</div>
          <p class="servicio-desc">Llegamos a cualquier ciudad del país con seguimiento en tiempo real y garantía de entrega segura.</p>
        </div>
        <div class="card-servicio reveal">
          <div class="servicio-icon">📦</div>
          <div class="servicio-nombre">Embalaje</div>
          <p class="servicio-desc">Protegemos tus pertenencias con materiales de calidad. Embalaje profesional incluido en el servicio.</p>
        </div>
        <div class="card-servicio reveal">
          <div class="servicio-icon">🏢</div>
          <div class="servicio-nombre">Mudanza empresarial</div>
          <p class="servicio-desc">Traslados de oficinas y bodegas con planificación y mínima interrupción de tus operaciones.</p>
        </div>
      </div>
    </section>

    <section id="vehiculos">
      <div class="seccion-titulo reveal">Flota de vehículos</div>
      <div class="vehiculos-grid">
        <div class="vehiculo-card reveal">
          <div class="vehiculo-icon">🚐</div>
          <div class="vehiculo-tipo">Camioneta</div>
          <div class="vehiculo-cap">Hasta 1 tonelada</div>
          <p class="vehiculo-desc">Ideal para mudanzas pequeñas y traslados de pocos muebles en la ciudad.</p>
        </div>
        <div class="vehiculo-card reveal">
          <div class="vehiculo-icon">🚛</div>
          <div class="vehiculo-tipo">Camión mediano</div>
          <div class="vehiculo-cap">Hasta 5 toneladas</div>
          <p class="vehiculo-desc">Perfecto para apartamentos completos y mudanzas locales de mediano volumen.</p>
        </div>
        <div class="vehiculo-card reveal">
          <div class="vehiculo-icon">🚚</div>
          <div class="vehiculo-tipo">Camión grande</div>
          <div class="vehiculo-cap">Hasta 15 toneladas</div>
          <p class="vehiculo-desc">Para mudanzas nacionales, casas grandes y traslados empresariales.</p>
        </div>
      </div>
    </section>

    <section id="cotizacion">
      <div class="seccion-titulo reveal">Cotización</div>
      <div class="cotizacion reveal">
        <h3>Calcula tu servicio</h3>
        <p>Completa los datos y obtén un estimado de tu mudanza al instante.</p>
        <div class="cot-grid">
          <div class="form-group">
            <label>Tipo de mudanza</label>
            <select id="tipo-mudanza">
              <option value="">Seleccionar</option>
              <option value="local">Local</option>
              <option value="nacional">Nacional</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tamaño del inmueble</label>
            <select id="tamano">
              <option value="">Seleccionar</option>
              <option value="1">Apartamento pequeño</option>
              <option value="2">Apartamento grande</option>
              <option value="3">Casa</option>
              <option value="4">Oficina</option>
            </select>
          </div>
          <div class="form-group">
            <label>Ciudad origen</label>
            <select id="origen">
              <option value="">Cargando ciudades...</option>
            </select>
          </div>
          <div class="form-group">
            <label>Ciudad destino</label>
            <select id="destino">
              <option value="">Cargando ciudades...</option>
            </select>
          </div>
        </div>

        <div class="servicios-adicionales">
          <label class="servicios-titulo">Servicios adicionales (opcional)</label>
          <div class="servicios-grid">
            <label class="servicio-check">
              <input type="checkbox" name="servicios" value="1" data-nombre="Embalaje" data-precio="50000">
              <span>📦 Embalaje — $50.000</span>
            </label>
            <label class="servicio-check">
              <input type="checkbox" name="servicios" value="2" data-nombre="Seguro de mudanza" data-precio="80000">
              <span>🛡️ Seguro de mudanza — $80.000</span>
            </label>
            <label class="servicio-check">
              <input type="checkbox" name="servicios" value="3" data-nombre="Almacenamiento temporal" data-precio="120000">
              <span>🏠 Almacenamiento temporal — $120.000</span>
            </label>
          </div>
        </div>

        <div class="botones-cotizar">
          <button class="btn-cotizar" onclick="calcularCotizacion()">Calcular cotización</button>
          <button class="btn-limpiar" onclick="limpiarCotizacion()">Nueva cotización</button>
        </div>

        <div id="resultado-cotizacion"></div>

        <div id="boton-agendar" style="display:none; margin-top:1rem;">
          <button class="btn-cotizar" onclick="abrirModalAgendar()" style="background:transparent; border:2px solid var(--rojo); color:var(--rojo);">📅 AGENDAR ESTA MUDANZA</button>
        </div>
      </div>
    </section>

    <section id="mis-mudanzas">
      <div class="seccion-titulo reveal">Mis mudanzas</div>
      <div id="lista-mis-mudanzas" style="background:var(--negro-card); border:1px solid var(--borde); padding:2rem;">
        <p style="color:var(--texto-dim); text-align:center;">Cargando tus mudanzas...</p>
      </div>
    </section>

    <section id="contacto">
      <div class="seccion-titulo reveal">Contáctanos</div>

      <div class="mapa-container reveal">
        <div class="mapa-header">
          <div>
            <h3>Visítanos</h3>
            <p>📍 Pereira, Risaralda — Colombia</p>
          </div>
          <a href="https://maps.app.goo.gl/APCugqVnazyP7kyY9" target="_blank">Abrir en Google Maps →</a>
        </div>
        <div id="mapa-mudanzas"></div>
      </div>
      
      <div class="contacto-grid">
        <a href="https://wa.me/573113670240" target="_blank" class="contacto-card reveal">
          <div class="contacto-icon">📱</div>
          <div class="contacto-titulo">WhatsApp</div>
          <div class="contacto-dato">+57 311 367 0240</div>
        </a>
        
        <a href="https://instagram.com/LUISNARX" target="_blank" class="contacto-card reveal">
          <div class="contacto-icon">📸</div>
          <div class="contacto-titulo">Instagram</div>
          <div class="contacto-dato">@LUISNARX</div>
          <p class="contacto-desc">Mira nuestros trabajos y reseñas</p>
        </a>
        
        <a href="https://www.facebook.com/luis.quintero.961905" target="_blank" class="contacto-card reveal">
          <div class="contacto-icon">👍</div>
          <div class="contacto-titulo">Facebook</div>
          <div class="contacto-dato">Quintero Luis</div>
          <p class="contacto-desc">Síguenos para más información</p>
        </a>
        
        <a href="mailto:luix123494@gmail.com" class="contacto-card reveal">
          <div class="contacto-icon">✉️</div>
          <div class="contacto-titulo">Correo</div>
          <div class="contacto-dato">luix123494@gmail.com</div>
          <p class="contacto-desc">Para cotizaciones formales</p>
        </a>
      </div>

      <div class="horario reveal">
        <h3>Horario de atención</h3>
        <p>Lunes a sábado: 7:00 a.m. — 7:00 p.m.</p>
        <p style="margin-top:0.5rem;color:var(--rojo)">Atención 24/7 para emergencias</p>
      </div>
      
      <div class="interaccion reveal" style="margin-top:2rem">
        <div>
          <h3>Tema del portal</h3>
          <p>Cambia el color de fondo según tu preferencia</p>
        </div>
        <div class="temas">
          <div class="tema-btn activo" style="background:#0d0d0d" onclick="cambiarTema('#0d0d0d', this)" title="Negro"></div>
          <div class="tema-btn" style="background:#0a1628" onclick="cambiarTema('#0a1628', this)" title="Azul oscuro"></div>
          <div class="tema-btn" style="background:#f3f7f3" onclick="cambiarTema('#f3f7f3', this)" title="blanco"></div>
          <div class="tema-btn" style="background:#1a0f0a" onclick="cambiarTema('#1a0f0a', this)" title="Café oscuro"></div>
        </div>
      </div>
    </section>
 
  </main>
 
  <footer>
    <div class="footer-marca">MUDANZAS <span>GL</span></div>
    <div class="footer-copy">© 2026 Mudanzas GL — Todos los derechos reservados</div>
    <div style="font-size:0.7rem;color:#555;letter-spacing:1px">Honestidad y Puntualidad</div>
  </footer>
 
  <script>
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((e, i) => {
        if (e.isIntersecting) setTimeout(() => e.target.classList.add('visible'), i * 100);
      });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
 
    const secciones = document.querySelectorAll('section[id], .hero');
    const navLinks = document.querySelectorAll('nav a');
    window.addEventListener('scroll', () => {
      let actual = '';
      secciones.forEach(s => { if (window.scrollY >= s.offsetTop - 100) actual = s.id; });
      navLinks.forEach(a => {
        a.classList.remove('active');
        if (a.getAttribute('href') === '#' + actual) a.classList.add('active');
      });
    });

    let ultimaCotizacion = null;
    let mudanzaCalificar = null;
    let mudanzaPagar = null;
    let mudanzaActualFotosCliente = null;
    let categoriaActualCliente = 'antes';

    async function cargarCiudades() {
      try {
        const response = await fetch('https://api-colombia.com/api/v1/City');
        const ciudades = await response.json();
        const selectOrigen = document.getElementById('origen');
        const selectDestino = document.getElementById('destino');
        selectOrigen.innerHTML = '<option value="">Seleccionar ciudad</option>';
        selectDestino.innerHTML = '<option value="">Seleccionar ciudad</option>';
        ciudades.forEach(ciudad => {
          const option1 = document.createElement('option');
          option1.value = ciudad.name;
          option1.textContent = ciudad.name;
          selectOrigen.appendChild(option1);
          const option2 = document.createElement('option');
          option2.value = ciudad.name;
          option2.textContent = ciudad.name;
          selectDestino.appendChild(option2);
        });
      } catch (error) {
        console.error('❌ Error al cargar ciudades:', error);
      }
    }

    function limpiarCotizacion() {
      document.getElementById('tipo-mudanza').value = '';
      document.getElementById('tamano').value = '';
      document.getElementById('origen').value = '';
      document.getElementById('destino').value = '';
      const resultado = document.getElementById('resultado-cotizacion');
      resultado.style.display = 'none';
      resultado.innerHTML = '';
      document.getElementById('boton-agendar').style.display = 'none';
    }

    async function calcularCotizacion() {
      const tipo = document.getElementById('tipo-mudanza').value;
      const tamano = document.getElementById('tamano').value;
      const origen = document.getElementById('origen').value;
      const destino = document.getElementById('destino').value;
      const resultado = document.getElementById('resultado-cotizacion');

      if (!tipo || !tamano || !origen || !destino) {
        resultado.style.display = 'block';
        resultado.innerHTML = '⚠️ Por favor completa todos los campos.';
        resultado.style.borderLeftColor = '#e74c3c';
        return;
      }

      const precios = {
        '1': { local: 150000, nacional: 800000 },
        '2': { local: 280000, nacional: 1500000 },
        '3': { local: 450000, nacional: 2500000 },
        '4': { local: 380000, nacional: 2000000 }
      };
      let precioBase = precios[tamano][tipo];
      const serviciosSeleccionados = [];
      const serviciosData = [];
      const checkboxes = document.querySelectorAll('input[name="servicios"]:checked');
      checkboxes.forEach(checkbox => {
        precioBase += parseInt(checkbox.dataset.precio);
        serviciosSeleccionados.push(checkbox.dataset.nombre);
        serviciosData.push({ id: checkbox.value, precio: checkbox.dataset.precio });
      });
      const precioFormateado = precioBase.toLocaleString('es-CO');
      let textoServicios = '';
      if (serviciosSeleccionados.length > 0) {
        textoServicios = `<br/><small style="color:#aaa">+ Servicios: ${serviciosSeleccionados.join(', ')}</small>`;
      }
      ultimaCotizacion = { tipo, tamano, origen, destino, valor: precioBase, servicios: serviciosData };
      document.getElementById('boton-agendar').style.display = 'block';
      resultado.style.display = 'block';
      resultado.style.borderLeftColor = '#c0392b';
      resultado.innerHTML = `
        ✓ <strong>Cotización estimada</strong><br/>
        De <em>${origen}</em> a <em>${destino}</em> — ${tipo === 'nacional' ? 'Nacional' : 'Local'}${textoServicios}<br/>
        <span style="font-size:1.2rem;color:#c0392b;font-weight:600">$ ${precioFormateado} COP</span><br/>
        <small style="color:#888">*Precio estimado. Contáctenos para una cotización exacta.</small>
      `;
      try {
        const response = await fetch('../app/cotizacion_procesar.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ tipo, tamano, origen, destino, valor: precioBase })
        });
        const data = await response.json();
        if (!data.ok) {
          resultado.innerHTML += `<br/><small style="color:#e74c3c">⚠️ No se pudo guardar: ${data.mensaje}</small>`;
        }
      } catch (error) {
        resultado.innerHTML += `<br/><small style="color:#e74c3c">⚠️ Error de conexión al guardar</small>`;
      }
    }

    function cambiarTema(color, btn) {
      document.body.style.backgroundColor = color;
      document.querySelectorAll('.tema-btn').forEach(b => b.classList.remove('activo'));
      btn.classList.add('activo');
    }

    function abrirModalAgendar() {
      if (!ultimaCotizacion) return;
      document.getElementById('modal-agendar').style.display = 'flex';
    }
    function cerrarModalAgendar() {
      document.getElementById('modal-agendar').style.display = 'none';
      document.getElementById('ag-mensaje').innerHTML = '';
    }

    async function enviarAgendamiento() {
      const fecha = document.getElementById('ag-fecha').value;
      const dirOrigen = document.getElementById('ag-dir-origen').value.trim();
      const dirDestino = document.getElementById('ag-dir-destino').value.trim();
      const contacto = document.getElementById('ag-contacto').value.trim();
      const telefono = document.getElementById('ag-telefono').value.trim();
      const mensaje = document.getElementById('ag-mensaje');
      if (!fecha || !dirOrigen || !dirDestino || !contacto || !telefono) {
        mensaje.innerHTML = '<span style="color:#e74c3c">⚠️ Completa todos los campos</span>';
        return;
      }
      mensaje.innerHTML = '<span style="color:#888">Guardando...</span>';
      try {
        const response = await fetch('../app/mudanza_procesar.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({
            tipo: ultimaCotizacion.tipo, tamano: ultimaCotizacion.tamano,
            origen: ultimaCotizacion.origen, destino: ultimaCotizacion.destino,
            valor: ultimaCotizacion.valor, fecha_servicio: fecha,
            direccion_origen: dirOrigen, direccion_destino: dirDestino,
            contacto_nombre: contacto, contacto_telefono: telefono,
            servicios: ultimaCotizacion.servicios
          })
        });
        const data = await response.json();
        if (data.ok) {
          mensaje.innerHTML = `<span style="color:#27ae60">✅ ¡Mudanza agendada! ID: ${data.mudanza_id}</span>`;
          setTimeout(() => {
            cerrarModalAgendar();
            alert('¡Mudanza agendada con éxito! Pronto te contactaremos.');
            cargarMisMudanzas();
          }, 1500);
        } else {
          mensaje.innerHTML = `<span style="color:#e74c3c">⚠️ ${data.mensaje}</span>`;
        }
      } catch (error) {
        mensaje.innerHTML = '<span style="color:#e74c3c">⚠️ Error de conexión</span>';
      }
    }

    async function cargarMisMudanzas() {
      const contenedor = document.getElementById('lista-mis-mudanzas');
      try {
        const res = await fetch('../app/mis_mudanzas.php', { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.ok || !data.mudanzas || data.mudanzas.length === 0) {
          contenedor.innerHTML = '<p style="color:var(--texto-dim); text-align:center;">Aún no tienes mudanzas agendadas</p>';
          return;
        }
        let html = `
          <table style="width:100%; border-collapse:collapse;">
            <thead>
              <tr style="border-bottom:1px solid var(--borde);">
                <th style="text-align:left; padding:0.8rem; font-size:0.7rem; letter-spacing:2px; color:var(--texto-dim); text-transform:uppercase;">ID</th>
                <th style="text-align:left; padding:0.8rem; font-size:0.7rem; letter-spacing:2px; color:var(--texto-dim); text-transform:uppercase;">Ruta</th>
                <th style="text-align:left; padding:0.8rem; font-size:0.7rem; letter-spacing:2px; color:var(--texto-dim); text-transform:uppercase;">Fecha</th>
                <th style="text-align:left; padding:0.8rem; font-size:0.7rem; letter-spacing:2px; color:var(--texto-dim); text-transform:uppercase;">Valor</th>
                <th style="text-align:left; padding:0.8rem; font-size:0.7rem; letter-spacing:2px; color:var(--texto-dim); text-transform:uppercase;">Estado</th>
                <th style="text-align:left; padding:0.8rem; font-size:0.7rem; letter-spacing:2px; color:var(--texto-dim); text-transform:uppercase;">Pago</th>
                <th style="text-align:left; padding:0.8rem; font-size:0.7rem; letter-spacing:2px; color:var(--texto-dim); text-transform:uppercase;">Fotos</th>
                <th style="text-align:left; padding:0.8rem; font-size:0.7rem; letter-spacing:2px; color:var(--texto-dim); text-transform:uppercase;">Calificación</th>
              </tr>
            </thead>
            <tbody>
        `;

        data.mudanzas.forEach(m => {
          const fecha = new Date(m.fecha_servicio).toLocaleDateString('es-CO', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
          const valor = '$' + Number(m.valor).toLocaleString('es-CO');
          let accionCalif = '—';
          if (m.estado === 'completada') {
            if (m.calificacion_estrellas) {
              accionCalif = '<span style="color:#f39c12;">' + '★'.repeat(m.calificacion_estrellas) + '☆'.repeat(5 - m.calificacion_estrellas) + '</span>';
            } else {
              accionCalif = '<button class="btn-cotizar" style="padding:0.4rem 1rem; font-size:0.75rem;" onclick="abrirModalCalificar(' + m.id + ')">⭐ Calificar</button>';
            }
          }
          let accionPago = '—';
          if (!m.pago_estado) {
            accionPago = '<button class="btn-cotizar" style="padding:0.4rem 1rem; font-size:0.75rem; background:#27ae60;" onclick="abrirModalPagar(' + m.id + ', ' + m.valor + ')">💳 Pagar</button>';
          } else if (m.pago_estado === 'pendiente') {
            accionPago = '<span style="color:#f39c12; font-size:0.8rem;">⏳ Pendiente<br/><small style="color:#888;">' + (m.pago_metodo || '') + '</small></span>';
          } else if (m.pago_estado === 'pagado') {
            accionPago = '<span style="color:#27ae60; font-size:0.8rem;">✓ Pagado<br/><small style="color:#888;">' + (m.pago_metodo || '') + '</small></span>';
          } else if (m.pago_estado === 'rechazado') {
            accionPago = '<span style="color:#e74c3c; font-size:0.8rem;">✗ Rechazado</span>';
          } else if (m.pago_estado === 'reembolsado') {
            accionPago = '<span style="color:#888; font-size:0.8rem;">↩ Reembolsado</span>';
          }
          html += `
            <tr style="border-bottom:1px solid var(--borde);">
              <td style="padding:0.8rem;">#${m.id}</td>
              <td style="padding:0.8rem;">${m.origen} → ${m.destino}</td>
              <td style="padding:0.8rem;">${fecha}</td>
              <td style="padding:0.8rem; color:var(--rojo); font-weight:600;">${valor}</td>
              <td style="padding:0.8rem;"><span style="padding:0.2rem 0.6rem; font-size:0.7rem; letter-spacing:1px; text-transform:uppercase; background:${colorEstado(m.estado)}; border-radius:2px;">${m.estado}</span></td>
              <td style="padding:0.8rem;">${accionPago}</td>
              <td style="padding:0.8rem;"><button class="btn-ver-fotos" onclick="abrirFotosCliente(${m.id})">📸 Ver</button></td>
              <td style="padding:0.8rem;">${accionCalif}</td>
            </tr>
          `;
        });
        html += '</tbody></table>';
        contenedor.innerHTML = html;
      } catch (err) {
        contenedor.innerHTML = '<p style="color:#e74c3c; text-align:center;">Error al cargar tus mudanzas</p>';
      }
    }

    function colorEstado(estado) {
      const colores = { 'pendiente':'#c0392b33','programada':'#2980b933','en_proceso':'#f39c1233','completada':'#27ae6033','cancelada':'#7f8c8d33' };
      return colores[estado] || '#33333333';
    }

    function abrirModalCalificar(mudanzaId) {
      mudanzaCalificar = mudanzaId;
      document.getElementById('cal-estrellas').value = 0;
      document.getElementById('cal-comentario').value = '';
      document.getElementById('cal-mensaje').innerHTML = '';
      actualizarEstrellas(0);
      document.getElementById('modal-calificar').style.display = 'flex';
    }
    function cerrarModalCalificar() {
      document.getElementById('modal-calificar').style.display = 'none';
      mudanzaCalificar = null;
    }
    function actualizarEstrellas(cantidad) {
      document.querySelectorAll('#estrellas-input span').forEach(span => {
        const num = parseInt(span.dataset.estrella);
        span.textContent = num <= cantidad ? '★' : '☆';
        span.style.color = num <= cantidad ? '#f39c12' : '#555';
      });
    }
    document.addEventListener('DOMContentLoaded', () => {
      const estrellas = document.querySelectorAll('#estrellas-input span');
      estrellas.forEach(span => {
        span.addEventListener('mouseenter', () => actualizarEstrellas(parseInt(span.dataset.estrella)));
        span.addEventListener('click', () => {
          const n = parseInt(span.dataset.estrella);
          document.getElementById('cal-estrellas').value = n;
          actualizarEstrellas(n);
        });
      });
      const inputEstrellas = document.getElementById('estrellas-input');
      if (inputEstrellas) {
        inputEstrellas.addEventListener('mouseleave', () => {
          actualizarEstrellas(parseInt(document.getElementById('cal-estrellas').value) || 0);
        });
      }
    });
    async function enviarCalificacion() {
      const estrellas = parseInt(document.getElementById('cal-estrellas').value);
      const comentario = document.getElementById('cal-comentario').value.trim();
      const mensaje = document.getElementById('cal-mensaje');
      if (!estrellas || estrellas < 1) {
        mensaje.innerHTML = '<span style="color:#e74c3c;">⚠️ Selecciona al menos 1 estrella</span>';
        return;
      }
      mensaje.innerHTML = '<span style="color:#888;">Enviando...</span>';
      try {
        const res = await fetch('../app/calificacion_procesar.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ mudanza_id: mudanzaCalificar, estrellas, comentario })
        });
        const data = await res.json();
        if (data.ok) {
          mensaje.innerHTML = '<span style="color:#27ae60;">✅ ¡Gracias por tu calificación!</span>';
          setTimeout(() => { cerrarModalCalificar(); cargarMisMudanzas(); }, 1500);
        } else {
          mensaje.innerHTML = `<span style="color:#e74c3c;">⚠️ ${data.mensaje}</span>`;
        }
      } catch (err) {
        mensaje.innerHTML = '<span style="color:#e74c3c;">⚠️ Error de conexión</span>';
      }
    }

    function abrirModalPagar(mudanzaId, monto) {
      mudanzaPagar = { id: mudanzaId, monto: monto };
      document.getElementById('pago-monto').textContent = '$' + Number(monto).toLocaleString('es-CO');
      document.getElementById('pago-referencia').value = '';
      document.getElementById('pago-mensaje').innerHTML = '';
      document.querySelectorAll('input[name="metodo_pago"]').forEach(r => r.checked = false);
      document.getElementById('modal-pagar').style.display = 'flex';
    }
    function cerrarModalPagar() {
      document.getElementById('modal-pagar').style.display = 'none';
      mudanzaPagar = null;
    }
    async function enviarPago() {
      const metodo = document.querySelector('input[name="metodo_pago"]:checked');
      const referencia = document.getElementById('pago-referencia').value.trim();
      const mensaje = document.getElementById('pago-mensaje');
      if (!metodo) {
        mensaje.innerHTML = '<span style="color:#e74c3c;">⚠️ Selecciona un método de pago</span>';
        return;
      }
      mensaje.innerHTML = '<span style="color:#888;">Registrando...</span>';
      try {
        const res = await fetch('../app/pago_procesar.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ mudanza_id: mudanzaPagar.id, monto: mudanzaPagar.monto, metodo_pago: metodo.value, referencia })
        });
        const data = await res.json();
        if (data.ok) {
          mensaje.innerHTML = '<span style="color:#27ae60;">✅ ¡Pago registrado! Espera confirmación</span>';
          setTimeout(() => { cerrarModalPagar(); cargarMisMudanzas(); }, 1500);
        } else {
          mensaje.innerHTML = `<span style="color:#e74c3c;">⚠️ ${data.mensaje}</span>`;
        }
      } catch (err) {
        mensaje.innerHTML = '<span style="color:#e74c3c;">⚠️ Error de conexión</span>';
      }
    }

    // ==========================================
    // NOTIFICACIONES
    // ==========================================
    async function cargarNotificaciones() {
      try {
        const res = await fetch('../app/notificaciones_listar.php', { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.ok) return;
        const badge = document.getElementById('badge-notif');
        if (data.sin_leer > 0) { badge.textContent = data.sin_leer; badge.style.display = 'block'; }
        else { badge.style.display = 'none'; }
        const lista = document.getElementById('lista-notif');
        if (data.notificaciones.length === 0) {
          lista.innerHTML = '<div style="padding:2rem; text-align:center; color:#888; font-size:0.85rem;">No tienes notificaciones</div>';
          return;
        }
        lista.innerHTML = data.notificaciones.map(n => {
          const fecha = new Date(n.fecha);
          const ahora = new Date();
          const diffMin = Math.floor((ahora - fecha) / 60000);
          let cuando = '';
          if (diffMin < 1) cuando = 'Hace un momento';
          else if (diffMin < 60) cuando = `Hace ${diffMin} min`;
          else if (diffMin < 1440) cuando = `Hace ${Math.floor(diffMin/60)} h`;
          else cuando = fecha.toLocaleDateString('es-CO');
          const noLeida = n.leida == 0;
          return `
            <div style="padding:1rem; border-bottom:1px solid var(--borde); ${noLeida ? 'background:#c0392b0d; border-left:3px solid var(--rojo);' : ''}">
              <div style="font-size:0.85rem; color:${noLeida ? 'var(--texto)' : 'var(--texto-dim)'}; margin-bottom:0.3rem;">
                ${noLeida ? '● ' : ''}${n.mensaje}
              </div>
              <div style="font-size:0.7rem; color:#666;">${cuando}</div>
            </div>
          `;
        }).join('');
      } catch (err) {}
    }
    function toggleNotificaciones() {
      const dd = document.getElementById('dropdown-notif');
      const abierto = dd.style.display === 'block';
      dd.style.display = abierto ? 'none' : 'block';
      if (!abierto) cargarNotificaciones();
    }
    async function marcarLeidas() {
      await fetch('../app/notificaciones_marcar.php', { method: 'POST', credentials: 'same-origin' });
      cargarNotificaciones();
    }
    document.addEventListener('click', (e) => {
      const dd = document.getElementById('dropdown-notif');
      if (dd && !e.target.closest('#dropdown-notif') && !e.target.closest('button[onclick="toggleNotificaciones()"]')) {
        dd.style.display = 'none';
      }
    });

    // ==========================================
    // VER FOTOS (CLIENTE)
    // ==========================================
    function abrirFotosCliente(mudanzaId) {
      mudanzaActualFotosCliente = mudanzaId;
      categoriaActualCliente = 'antes';
      document.querySelectorAll('#modal-fotos .cat-tab').forEach(t => t.classList.remove('activo'));
      document.querySelector('#modal-fotos .cat-tab[data-cat="antes"]').classList.add('activo');
      document.getElementById('modal-fotos').classList.add('activo');
      cargarFotosCliente();
    }
    function cerrarFotosCliente() {
      document.getElementById('modal-fotos').classList.remove('activo');
      mudanzaActualFotosCliente = null;
    }
    function cambiarCategoriaCliente(cat, btn) {
      categoriaActualCliente = cat;
      document.querySelectorAll('#modal-fotos .cat-tab').forEach(t => t.classList.remove('activo'));
      btn.classList.add('activo');
      cargarFotosCliente();
    }
    async function cargarFotosCliente() {
      if (!mudanzaActualFotosCliente) return;
      const galeria = document.getElementById('galeria-fotos-cliente');
      const sinFotos = document.getElementById('sin-fotos-cliente');
      galeria.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#888; padding:1rem;">Cargando...</div>';
      sinFotos.style.display = 'none';
      try {
        const res = await fetch('../app/foto_listar.php?mudanza_id=' + mudanzaActualFotosCliente, { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.ok) {
          galeria.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#e74c3c; padding:1rem;">Error al cargar fotos</div>';
          return;
        }
        const fotosCat = data.agrupadas[categoriaActualCliente] || [];
        if (fotosCat.length === 0) {
          galeria.innerHTML = '';
          sinFotos.style.display = 'block';
          return;
        }
        sinFotos.style.display = 'none';
        galeria.innerHTML = fotosCat.map(f => `
          <div class="foto-item-cliente">
            <img src="../uploads/mudanzas/${f.archivo}" alt="Foto" onclick="window.open('../uploads/mudanzas/${f.archivo}', '_blank')">
          </div>
        `).join('');
      } catch (err) {
        galeria.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#e74c3c; padding:1rem;">Error de conexión</div>';
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      cargarCiudades();
      cargarMisMudanzas();
      cargarNotificaciones();
      setInterval(cargarNotificaciones, 30000);
    });
  </script>

  <!-- LEAFLET -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof L === 'undefined') return;
      const lat = 4.796639;
      const lng = -75.746722;
      const map = L.map('mapa-mudanzas', { scrollWheelZoom: false }).setView([lat, lng], 16);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19
      }).addTo(map);
      const iconoRojo = L.divIcon({
        className: 'marcador-personalizado',
        html: '<div style="background:#c0392b; width:30px; height:30px; border-radius:50% 50% 50% 0; transform:rotate(-45deg); border:3px solid white; box-shadow:0 3px 10px rgba(0,0,0,0.5);"></div>',
        iconSize: [30, 30], iconAnchor: [15, 30]
      });
      L.marker([lat, lng], { icon: iconoRojo }).addTo(map).bindPopup(`
        <div style="font-family:'Inter',sans-serif; text-align:center;">
          <strong style="font-size:14px;">🚛 Mudanzas GL</strong><br/>
          <span style="font-size:12px; color:#666;">Honestidad y Puntualidad</span><br/>
          <a href="https://maps.app.goo.gl/APCugqVnazyP7kyY9" target="_blank" style="color:#c0392b; font-size:12px;">Ver en Google Maps</a>
        </div>
      `).openPopup();
    });
  </script>

  <!-- MODAL AGENDAR -->
  <div id="modal-agendar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:9999; align-items:center; justify-content:center; padding:2rem;">
    <div style="background:#1a1a1a; border:1px solid #333; border-top:3px solid #c0392b; padding:2.5rem; max-width:600px; width:100%; max-height:90vh; overflow-y:auto; position:relative;">
      <button onclick="cerrarModalAgendar()" style="position:absolute; top:1rem; right:1rem; background:transparent; border:none; color:#888; font-size:1.5rem; cursor:pointer;">✕</button>
      <h3 style="font-family:'Bebas Neue',sans-serif; font-size:1.8rem; letter-spacing:3px; margin-bottom:0.3rem;">Agendar mudanza</h3>
      <p style="font-size:0.8rem; color:#888; margin-bottom:1.5rem;">Completa los datos para confirmar tu reserva</p>
      <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:#888; margin-bottom:0.4rem;">Fecha y hora del servicio</label>
        <input type="datetime-local" id="ag-fecha" style="width:100%; background:#2a2a2a; border:1px solid #333; color:#e8e8e8; padding:0.7rem; font-size:0.9rem;">
      </div>
      <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:#888; margin-bottom:0.4rem;">Dirección de origen</label>
        <input type="text" id="ag-dir-origen" placeholder="Ej: Cra 10 #20-30, Barrio Centro" style="width:100%; background:#2a2a2a; border:1px solid #333; color:#e8e8e8; padding:0.7rem; font-size:0.9rem;">
      </div>
      <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:#888; margin-bottom:0.4rem;">Dirección de destino</label>
        <input type="text" id="ag-dir-destino" placeholder="Ej: Calle 45 #12-10, Barrio Poblado" style="width:100%; background:#2a2a2a; border:1px solid #333; color:#e8e8e8; padding:0.7rem; font-size:0.9rem;">
      </div>
      <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:#888; margin-bottom:0.4rem;">Nombre de contacto</label>
        <input type="text" id="ag-contacto" placeholder="Ej: Juan Pérez" style="width:100%; background:#2a2a2a; border:1px solid #333; color:#e8e8e8; padding:0.7rem; font-size:0.9rem;">
      </div>
      <div style="margin-bottom:1.5rem;">
        <label style="display:block; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:#888; margin-bottom:0.4rem;">Teléfono de contacto</label>
        <input type="tel" id="ag-telefono" placeholder="Ej: 3113670240" style="width:100%; background:#2a2a2a; border:1px solid #333; color:#e8e8e8; padding:0.7rem; font-size:0.9rem;">
      </div>
      <div id="ag-mensaje" style="margin-bottom:1rem; font-size:0.85rem;"></div>
      <button class="btn-cotizar" onclick="enviarAgendamiento()" style="width:100%; padding:0.9rem;">CONFIRMAR AGENDAMIENTO</button>
    </div>
  </div>

  <!-- MODAL CALIFICAR -->
  <div id="modal-calificar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:9999; align-items:center; justify-content:center; padding:2rem;">
    <div style="background:#1a1a1a; border:1px solid #333; border-top:3px solid #c0392b; padding:2.5rem; max-width:500px; width:100%; position:relative;">
      <button onclick="cerrarModalCalificar()" style="position:absolute; top:1rem; right:1rem; background:transparent; border:none; color:#888; font-size:1.5rem; cursor:pointer;">✕</button>
      <h3 style="font-family:'Bebas Neue',sans-serif; font-size:1.8rem; letter-spacing:3px; margin-bottom:0.3rem;">Calificar servicio</h3>
      <p style="font-size:0.8rem; color:#888; margin-bottom:1.5rem;">Cuéntanos cómo fue tu experiencia</p>
      <div style="text-align:center; margin-bottom:1.5rem;">
        <div id="estrellas-input" style="font-size:2.5rem; letter-spacing:0.5rem; cursor:pointer;">
          <span data-estrella="1" style="color:#555;">☆</span>
          <span data-estrella="2" style="color:#555;">☆</span>
          <span data-estrella="3" style="color:#555;">☆</span>
          <span data-estrella="4" style="color:#555;">☆</span>
          <span data-estrella="5" style="color:#555;">☆</span>
        </div>
        <input type="hidden" id="cal-estrellas" value="0">
      </div>
      <div style="margin-bottom:1.5rem;">
        <label style="display:block; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:#888; margin-bottom:0.4rem;">Comentario (opcional)</label>
        <textarea id="cal-comentario" placeholder="Ej: Excelente servicio, muy puntuales..." style="width:100%; background:#2a2a2a; border:1px solid #333; color:#e8e8e8; padding:0.7rem; font-size:0.9rem; font-family:'Inter',sans-serif; min-height:80px; resize:vertical;"></textarea>
      </div>
      <div id="cal-mensaje" style="margin-bottom:1rem; font-size:0.85rem;"></div>
      <button class="btn-cotizar" onclick="enviarCalificacion()" style="width:100%; padding:0.9rem;">ENVIAR CALIFICACIÓN</button>
    </div>
  </div>

  <!-- MODAL PAGAR -->
  <div id="modal-pagar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:9999; align-items:center; justify-content:center; padding:2rem;">
    <div style="background:#1a1a1a; border:1px solid #333; border-top:3px solid #27ae60; padding:2.5rem; max-width:500px; width:100%; position:relative;">
      <button onclick="cerrarModalPagar()" style="position:absolute; top:1rem; right:1rem; background:transparent; border:none; color:#888; font-size:1.5rem; cursor:pointer;">✕</button>
      <h3 style="font-family:'Bebas Neue',sans-serif; font-size:1.8rem; letter-spacing:3px; margin-bottom:0.3rem;">Pagar mudanza</h3>
      <p style="font-size:0.8rem; color:#888; margin-bottom:1.5rem;">Selecciona tu método de pago preferido</p>
      <div style="background:#2a2a2a; padding:1rem; margin-bottom:1.5rem; border-left:3px solid #27ae60;">
        <div style="font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:#888;">Total a pagar</div>
        <div id="pago-monto" style="font-family:'Bebas Neue',sans-serif; font-size:2rem; color:#27ae60; letter-spacing:1px;">$0</div>
      </div>
      <div style="margin-bottom:1.5rem;">
        <label style="display:block; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:#888; margin-bottom:0.6rem;">Método de pago</label>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
          <label style="display:flex; align-items:center; gap:0.5rem; background:#2a2a2a; border:1px solid #333; padding:0.8rem; cursor:pointer; font-size:0.85rem;">
            <input type="radio" name="metodo_pago" value="efectivo" style="accent-color:#c0392b;">💵 Efectivo
          </label>
          <label style="display:flex; align-items:center; gap:0.5rem; background:#2a2a2a; border:1px solid #333; padding:0.8rem; cursor:pointer; font-size:0.85rem;">
            <input type="radio" name="metodo_pago" value="transferencia" style="accent-color:#c0392b;">🏦 Transferencia
          </label>
          <label style="display:flex; align-items:center; gap:0.5rem; background:#2a2a2a; border:1px solid #333; padding:0.8rem; cursor:pointer; font-size:0.85rem;">
            <input type="radio" name="metodo_pago" value="tarjeta" style="accent-color:#c0392b;">💳 Tarjeta
          </label>
          <label style="display:flex; align-items:center; gap:0.5rem; background:#2a2a2a; border:1px solid #333; padding:0.8rem; cursor:pointer; font-size:0.85rem;">
            <input type="radio" name="metodo_pago" value="nequi" style="accent-color:#c0392b;">📱 Nequi
          </label>
          <label style="display:flex; align-items:center; gap:0.5rem; background:#2a2a2a; border:1px solid #333; padding:0.8rem; cursor:pointer; font-size:0.85rem; grid-column:span 2;">
            <input type="radio" name="metodo_pago" value="daviplata" style="accent-color:#c0392b;">📱 Daviplata
          </label>
        </div>
      </div>
      <div style="margin-bottom:1.5rem;">
        <label style="display:block; font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:#888; margin-bottom:0.4rem;">Referencia (opcional)</label>
        <input type="text" id="pago-referencia" placeholder="Número de transacción" style="width:100%; background:#2a2a2a; border:1px solid #333; color:#e8e8e8; padding:0.7rem; font-size:0.9rem;">
      </div>
      <div id="pago-mensaje" style="margin-bottom:1rem; font-size:0.85rem;"></div>
      <button class="btn-cotizar" onclick="enviarPago()" style="width:100%; padding:0.9rem; background:#27ae60;">CONFIRMAR PAGO</button>
    </div>
  </div>

  <!-- MODAL FOTOS CLIENTE -->
  <div id="modal-fotos" class="modal-fotos">
    <div class="modal-fotos-content">
      <button class="btn-cerrar-modal" onclick="cerrarFotosCliente()">✕</button>
      <h3 style="font-family:'Bebas Neue',sans-serif; font-size:1.8rem; letter-spacing:3px; margin-bottom:0.3rem;">Fotos de la mudanza</h3>
      <p style="font-size:0.8rem; color:#888; margin-bottom:1.5rem;">Aquí puedes ver las fotos de tu mudanza</p>
      <div class="categoria-tabs">
        <button class="cat-tab activo" data-cat="antes" onclick="cambiarCategoriaCliente('antes', this)">📦 Antes</button>
        <button class="cat-tab" data-cat="durante" onclick="cambiarCategoriaCliente('durante', this)">🚚 Durante</button>
        <button class="cat-tab" data-cat="despues" onclick="cambiarCategoriaCliente('despues', this)">🏠 Después</button>
      </div>
      <div id="galeria-fotos-cliente" class="galeria-grid-cliente"></div>
      <div id="sin-fotos-cliente" class="sin-fotos-cliente" style="display:none;">No hay fotos en esta categoría</div>
    </div>
  </div>

</body>
</html>

