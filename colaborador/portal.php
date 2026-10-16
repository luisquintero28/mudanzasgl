<?php
session_start();

// Solo colaboradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'colaborador') {
    header("Location: ../public/loginGL.php");
    exit;
}

require '../app/conexion.php';

$usuario_id = $_SESSION['usuario_id'];

// Traer el colaborador
$stmt = $conexion->prepare("SELECT c.* FROM colaboradores c 
                            INNER JOIN usuarios u ON c.cedula = u.cedula 
                            WHERE u.id = :uid");
$stmt->execute([':uid' => $usuario_id]);
$colaborador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$colaborador) {
    echo "No se encontró información de colaborador.";
    exit;
}

$colaborador_id = $colaborador['id'];

// Traer sus mudanzas asignadas
$stmt = $conexion->prepare(
    "SELECT m.*, u.nombre AS cliente_nombre, u.cedula AS cliente_cedula
     FROM mudanzas m
     LEFT JOIN usuarios u ON m.usuario_id = u.id
     WHERE m.colaborador_id = :cid
     ORDER BY m.fecha_servicio DESC"
);
$stmt->execute([':cid' => $colaborador_id]);
$mudanzas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traer sus comisiones
$stmt = $conexion->prepare(
    "SELECT * FROM comisiones WHERE colaborador_id = :cid ORDER BY fecha DESC"
);
$stmt->execute([':cid' => $colaborador_id]);
$comisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traer sus calificaciones (con datos del cliente)
$stmt = $conexion->prepare(
    "SELECT c.*, u.nombre AS cliente_nombre, m.origen, m.destino
     FROM calificaciones c
     INNER JOIN mudanzas m ON c.mudanza_id = m.id
     INNER JOIN usuarios u ON c.usuario_id = u.id
     WHERE m.colaborador_id = :cid
     ORDER BY c.fecha DESC"
);
$stmt->execute([':cid' => $colaborador_id]);
$calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular promedio y total
$total_calif = count($calificaciones);
$promedio = 0;
if ($total_calif > 0) {
    $suma = 0;
    foreach ($calificaciones as $cal) {
        $suma += (int)$cal['estrellas'];
    }
    $promedio = round($suma / $total_calif, 1);
}

// Totales
$total_pendiente = 0;
$total_pagado    = 0;
$total_ganado    = 0;

foreach ($comisiones as $c) {
    $total_ganado += (float)$c['valor_colaborador'];
    if ($c['estado'] === 'pendiente') {
        $total_pendiente += (float)$c['valor_colaborador'];
    } else {
        $total_pagado += (float)$c['valor_colaborador'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Portal Colaborador — Mudanzas GL</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --rojo: #c0392b; --negro: #0d0d0d; --negro-card: #1a1a1a;
      --gris: #2a2a2a; --borde: #333; --texto: #e8e8e8; --texto-dim: #888;
      --dorado: #f39c12;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:var(--negro); color:var(--texto); min-height:100vh; }

    header {
      background:var(--negro-card); border-bottom:2px solid var(--rojo);
      padding:1.2rem 3rem; display:flex; justify-content:space-between; align-items:center;
    }
    .marca { font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:4px; }
    .marca span { color:var(--rojo); }
    .info-colab { font-size:0.8rem; color:var(--texto-dim); text-align:right; }
    .info-colab strong { color:var(--texto); font-size:0.95rem; }
    .btn-salir {
      background:transparent; border:1px solid var(--borde); color:var(--texto-dim);
      padding:0.5rem 1.2rem; font-size:0.72rem; letter-spacing:2px; text-transform:uppercase;
      cursor:pointer; text-decoration:none;
    }
    .btn-salir:hover { border-color:var(--rojo); color:var(--rojo); }

    main { padding:3rem; }
    h1 { font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:3px; margin-bottom:0.3rem; }
    .subt { color:var(--texto-dim); font-size:0.85rem; margin-bottom:2.5rem; }

    .promedio-card {
      background:var(--negro-card); border:1px solid var(--borde); border-top:3px solid var(--dorado);
      padding:2rem; margin-bottom:2rem; display:flex; align-items:center; gap:2rem;
    }
    .promedio-num {
      font-family:'Bebas Neue',sans-serif; font-size:4rem; color:var(--dorado);
      line-height:1; letter-spacing:2px;
    }
    .promedio-info { flex:1; }
    .promedio-estrellas { font-size:1.8rem; color:var(--dorado); letter-spacing:0.3rem; margin-bottom:0.3rem; }
    .promedio-label { font-size:0.8rem; color:var(--texto-dim); }

    .stats {
      display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
      gap:1px; background:var(--borde); margin-bottom:2.5rem;
    }
    .stat-card { background:var(--negro-card); padding:1.5rem; }
    .stat-label {
      font-size:0.65rem; letter-spacing:2px; text-transform:uppercase;
      color:var(--texto-dim); margin-bottom:0.5rem;
    }
    .stat-valor {
      font-family:'Bebas Neue',sans-serif; font-size:1.8rem;
      letter-spacing:1px;
    }
    .stat-valor.amarillo { color:#f39c12; }
    .stat-valor.verde    { color:#27ae60; }
    .stat-valor.rojo     { color:var(--rojo); }

    .seccion-titulo {
      font-family:'Bebas Neue',sans-serif; font-size:1.2rem;
      letter-spacing:3px; margin:2.5rem 0 1rem; color:var(--texto);
      display:flex; align-items:center; gap:1rem;
    }
    .seccion-titulo::after {
      content:''; flex:1; height:1px; background:var(--borde);
    }

    table { width:100%; border-collapse:collapse; background:var(--negro-card); border:1px solid var(--borde); }
    th {
      background:var(--gris); color:var(--texto-dim); font-size:0.7rem;
      letter-spacing:2px; text-transform:uppercase; padding:1rem; text-align:left;
      border-bottom:1px solid var(--borde);
    }
    td { padding:1rem; border-bottom:1px solid var(--borde); font-size:0.85rem; }
    tr:hover td { background:#1f1f1f; }

    .estado {
      display:inline-block; padding:0.2rem 0.6rem; font-size:0.7rem;
      letter-spacing:1px; text-transform:uppercase; border-radius:2px;
    }
    .estado-pendiente  { background:#c0392b33; color:#e74c3c; border:1px solid #c0392b; }
    .estado-programada { background:#2980b933; color:#3498db; border:1px solid #2980b9; }
    .estado-en_proceso { background:#f39c1233; color:#f39c12; border:1px solid #f39c12; }
    .estado-completada { background:#27ae6033; color:#27ae60; border:1px solid #27ae60; }
    .estado-cancelada  { background:#7f8c8d33; color:#7f8c8d; border:1px solid #7f8c8d; }
    .estado-pagada     { background:#27ae6033; color:#27ae60; border:1px solid #27ae60; }

    .valor { color:var(--rojo); font-weight:600; }
    .valor-verde { color:#27ae60; font-weight:600; }

    .resenas-grid {
      display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));
      gap:1rem;
    }
    .resena-card {
      background:var(--negro-card); border:1px solid var(--borde);
      border-left:3px solid var(--dorado); padding:1.5rem;
    }
    .resena-header {
      display:flex; justify-content:space-between; align-items:start;
      margin-bottom:0.8rem; gap:1rem;
    }
    .resena-cliente { font-weight:600; font-size:0.9rem; }
    .resena-fecha { font-size:0.72rem; color:var(--texto-dim); }
    .resena-estrellas { font-size:1.1rem; color:var(--dorado); letter-spacing:0.2rem; margin-bottom:0.5rem; }
    .resena-comentario { font-size:0.85rem; color:var(--texto-dim); line-height:1.6; font-style:italic; }

    /* MODAL FOTOS */
    .modal-fotos {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.85);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .modal-fotos.activo {
      display: flex;
    }
    .modal-fotos-content {
      background: var(--negro-card);
      border: 1px solid var(--borde);
      border-top: 3px solid var(--rojo);
      padding: 2.5rem;
      max-width: 700px;
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
    .btn-fotos {
      background: var(--rojo);
      color: white;
      border: none;
      padding: 0.4rem 1rem;
      font-size: 0.72rem;
      letter-spacing: 1px;
      text-transform: uppercase;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn-fotos:hover { background: #e74c3c; }
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
    .upload-area {
      border: 2px dashed var(--borde);
      padding: 2rem;
      text-align: center;
      margin-bottom: 1.5rem;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
    }
    .upload-area:hover {
      border-color: var(--rojo);
      background: #1f1f1f;
    }
    .upload-area input[type="file"] {
      display: none;
    }
    .upload-icon {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
    }
    .upload-txt {
      color: var(--texto-dim);
      font-size: 0.85rem;
    }
    .upload-txt strong {
      color: var(--rojo);
    }
    .galeria-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 0.8rem;
      margin-bottom: 1.5rem;
    }
    .foto-item {
      position: relative;
      aspect-ratio: 1;
      border: 1px solid var(--borde);
      overflow: hidden;
      border-radius: 3px;
    }
    .foto-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      cursor: pointer;
      transition: transform 0.2s;
    }
    .foto-item img:hover {
      transform: scale(1.05);
    }
    .sin-fotos {
      text-align: center;
      padding: 2rem;
      color: var(--texto-dim);
      font-size: 0.85rem;
      border: 1px dashed var(--borde);
      border-radius: 3px;
    }
    .progreso-upload {
      display: none;
      text-align: center;
      padding: 1rem;
      color: var(--rojo);
      font-size: 0.85rem;
    }
    .progreso-upload.activo { display: block; }
  </style>
</head>
<body>
  <header>
    <div class="marca">MUDANZAS <span>GL</span></div>
    <div style="display:flex; align-items:center; gap:1rem;">
      <div class="info-colab">
        <strong><?= htmlspecialchars($colaborador['nombre']) ?></strong><br/>
        <?= htmlspecialchars($colaborador['tipo_vehiculo']) ?> · <?= htmlspecialchars($colaborador['placa_vehiculo']) ?>
      </div>

      <!-- CAMPANITA -->
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

  <main>
    <h1>Mi portal</h1>
    <p class="subt">Aquí puedes ver tus mudanzas asignadas, tus ganancias y tus reseñas</p>

    <!-- PROMEDIO -->
    <?php if ($total_calif > 0): ?>
    <div class="promedio-card">
      <div class="promedio-num"><?= number_format($promedio, 1) ?></div>
      <div class="promedio-info">
        <div class="promedio-estrellas">
          <?php
            $estrellas_full = round($promedio);
            echo str_repeat('★', $estrellas_full);
            echo str_repeat('☆', 5 - $estrellas_full);
          ?>
        </div>
        <div class="promedio-label">Basado en <?= $total_calif ?> calificación<?= $total_calif > 1 ? 'es' : '' ?></div>
      </div>
    </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats">
      <div class="stat-card">
        <div class="stat-label">Mudanzas asignadas</div>
        <div class="stat-valor"><?= count($mudanzas) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total ganado</div>
        <div class="stat-valor">$<?= number_format($total_ganado, 0, ',', '.') ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Pendiente de pago</div>
        <div class="stat-valor amarillo">$<?= number_format($total_pendiente, 0, ',', '.') ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Ya pagado</div>
        <div class="stat-valor verde">$<?= number_format($total_pagado, 0, ',', '.') ?></div>
      </div>
    </div>

    <!-- MUDANZAS -->
    <div class="seccion-titulo">Mis mudanzas asignadas</div>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Ruta</th>
          <th>Fecha servicio</th>
          <th>Valor</th>
          <th>Estado</th>
          <th>Fotos</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($mudanzas as $m): ?>
        <tr>
          <td>#<?= $m['id'] ?></td>
          <td><?= htmlspecialchars($m['cliente_nombre'] ?? '—') ?></td>
          <td>
            <?= htmlspecialchars($m['origen']) ?> → <?= htmlspecialchars($m['destino']) ?><br/>
            <small style="color:#888"><?= htmlspecialchars($m['direccion_origen'] ?? '') ?></small>
          </td>
          <td><?= date('d/m/Y H:i', strtotime($m['fecha_servicio'])) ?></td>
          <td class="valor">$<?= number_format($m['valor'], 0, ',', '.') ?></td>
          <td><span class="estado estado-<?= $m['estado'] ?>"><?= $m['estado'] ?></span></td>
          <td>
            <button class="btn-fotos" onclick="abrirModalFotos(<?= $m['id'] ?>)">📸 Fotos</button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($mudanzas)): ?>
          <tr><td colspan="7" style="text-align:center; color:#888; padding:2rem;">No tienes mudanzas asignadas todavía</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- RESEÑAS -->
    <div class="seccion-titulo">Mis reseñas</div>
    <?php if (empty($calificaciones)): ?>
      <div style="background:var(--negro-card); border:1px solid var(--borde); padding:2rem; text-align:center; color:#888;">
        Aún no tienes reseñas de clientes
      </div>
    <?php else: ?>
      <div class="resenas-grid">
        <?php foreach ($calificaciones as $cal): ?>
          <div class="resena-card">
            <div class="resena-header">
              <div>
                <div class="resena-cliente"><?= htmlspecialchars($cal['cliente_nombre']) ?></div>
                <div class="resena-fecha"><?= date('d/m/Y', strtotime($cal['fecha'])) ?> · Mudanza #<?= $cal['mudanza_id'] ?></div>
              </div>
            </div>
            <div class="resena-estrellas">
              <?= str_repeat('★', (int)$cal['estrellas']) . str_repeat('☆', 5 - (int)$cal['estrellas']) ?>
            </div>
            <?php if (!empty($cal['comentario'])): ?>
              <div class="resena-comentario">"<?= htmlspecialchars($cal['comentario']) ?>"</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- COMISIONES -->
    <div class="seccion-titulo">Mis comisiones</div>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Mudanza</th>
          <th>Valor servicio</th>
          <th>Mi porcentaje</th>
          <th>Mi pago</th>
          <th>Estado</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($comisiones as $c): ?>
        <tr>
          <td>#<?= $c['id'] ?></td>
          <td>Mudanza #<?= $c['mudanza_id'] ?></td>
          <td>$<?= number_format($c['valor_servicio'], 0, ',', '.') ?></td>
          <td><?= number_format($c['porcentaje'], 0) ?>%</td>
          <td class="valor-verde">$<?= number_format($c['valor_colaborador'], 0, ',', '.') ?></td>
          <td><span class="estado estado-<?= $c['estado'] ?>"><?= $c['estado'] ?></span></td>
          <td><?= date('d/m/Y', strtotime($c['fecha'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($comisiones)): ?>
          <tr><td colspan="7" style="text-align:center; color:#888; padding:2rem;">No tienes comisiones todavía</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>

  <!-- MODAL FOTOS -->
  <div id="modal-fotos" class="modal-fotos">
    <div class="modal-fotos-content">
      <button class="btn-cerrar-modal" onclick="cerrarModalFotos()">✕</button>

      <h3 style="font-family:'Bebas Neue',sans-serif; font-size:1.8rem; letter-spacing:3px; margin-bottom:0.3rem;">Fotos de la mudanza</h3>
      <p style="font-size:0.8rem; color:#888; margin-bottom:1.5rem;">Sube las fotos del antes, durante y después</p>

      <div class="categoria-tabs">
        <button class="cat-tab activo" data-cat="antes" onclick="cambiarCategoria('antes', this)">📦 Antes</button>
        <button class="cat-tab" data-cat="durante" onclick="cambiarCategoria('durante', this)">🚚 Durante</button>
        <button class="cat-tab" data-cat="despues" onclick="cambiarCategoria('despues', this)">🏠 Después</button>
      </div>

      <label class="upload-area" for="input-foto">
        <input type="file" id="input-foto" accept="image/*" onchange="subirFoto(this)">
        <div class="upload-icon">📸</div>
        <div class="upload-txt">Haz clic o arrastra una foto aquí<br/><strong>Máximo 5MB · JPG, PNG, WEBP</strong></div>
      </label>

      <div id="progreso-upload" class="progreso-upload">Subiendo foto...</div>

      <div id="galeria-fotos" class="galeria-grid"></div>
      <div id="sin-fotos" class="sin-fotos" style="display:none;">No hay fotos en esta categoría</div>
    </div>
  </div>

  <script>
    async function cargarNotificaciones() {
      try {
        const res = await fetch('../app/notificaciones_listar.php', { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.ok) return;

        const badge = document.getElementById('badge-notif');
        if (data.sin_leer > 0) {
          badge.textContent = data.sin_leer;
          badge.style.display = 'block';
        } else {
          badge.style.display = 'none';
        }

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
      } catch (err) {
        console.error('Error notificaciones:', err);
      }
    }

    function toggleNotificaciones() {
      const dd = document.getElementById('dropdown-notif');
      const abierto = dd.style.display === 'block';
      dd.style.display = abierto ? 'none' : 'block';
      if (!abierto) cargarNotificaciones();
    }

    async function marcarLeidas() {
      await fetch('../app/notificaciones_marcar.php', {
        method: 'POST',
        credentials: 'same-origin'
      });
      cargarNotificaciones();
    }

    document.addEventListener('click', (e) => {
      const dd = document.getElementById('dropdown-notif');
      if (dd && !e.target.closest('#dropdown-notif') && !e.target.closest('button[onclick="toggleNotificaciones()"]')) {
        dd.style.display = 'none';
      }
    });

    document.addEventListener('DOMContentLoaded', () => {
      cargarNotificaciones();
      setInterval(cargarNotificaciones, 30000);
    });

    // ==========================================
    // FOTOS
    // ==========================================
    let mudanzaActualFotos = null;
    let categoriaActual = 'antes';

    function abrirModalFotos(mudanzaId) {
      mudanzaActualFotos = mudanzaId;
      categoriaActual = 'antes';
      document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('activo'));
      document.querySelector('.cat-tab[data-cat="antes"]').classList.add('activo');
      document.getElementById('modal-fotos').classList.add('activo');
      cargarFotos();
    }

    function cerrarModalFotos() {
      document.getElementById('modal-fotos').classList.remove('activo');
      mudanzaActualFotos = null;
    }

    function cambiarCategoria(cat, btn) {
      categoriaActual = cat;
      document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('activo'));
      btn.classList.add('activo');
      cargarFotos();
    }

    async function cargarFotos() {
      if (!mudanzaActualFotos) return;

      const galeria = document.getElementById('galeria-fotos');
      const sinFotos = document.getElementById('sin-fotos');

      galeria.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#888; padding:1rem;">Cargando...</div>';
      sinFotos.style.display = 'none';

      try {
        const res = await fetch('../app/foto_listar.php?mudanza_id=' + mudanzaActualFotos, {
          credentials: 'same-origin'
        });
        const data = await res.json();

        if (!data.ok) {
          galeria.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#e74c3c; padding:1rem;">Error al cargar fotos</div>';
          return;
        }

        const fotosCat = data.agrupadas[categoriaActual] || [];

        if (fotosCat.length === 0) {
          galeria.innerHTML = '';
          sinFotos.style.display = 'block';
          return;
        }

        sinFotos.style.display = 'none';
        galeria.innerHTML = fotosCat.map(f => `
          <div class="foto-item">
            <img src="../uploads/mudanzas/${f.archivo}" alt="Foto" onclick="verFoto('../uploads/mudanzas/${f.archivo}')">
          </div>
        `).join('');

      } catch (err) {
        console.error(err);
        galeria.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#e74c3c; padding:1rem;">Error de conexión</div>';
      }
    }

    function verFoto(url) {
      window.open(url, '_blank');
    }

    async function subirFoto(input) {
      if (!input.files || !input.files[0]) return;

      const archivo = input.files[0];
      const progreso = document.getElementById('progreso-upload');

      if (archivo.size > 5 * 1024 * 1024) {
        alert('La imagen es muy grande. Máximo 5MB');
        input.value = '';
        return;
      }

      const formData = new FormData();
      formData.append('foto', archivo);
      formData.append('mudanza_id', mudanzaActualFotos);
      formData.append('categoria', categoriaActual);

      progreso.classList.add('activo');
      progreso.textContent = 'Subiendo foto...';

      try {
        const res = await fetch('../app/foto_subir.php', {
          method: 'POST',
          credentials: 'same-origin',
          body: formData
        });
        const data = await res.json();

        if (data.ok) {
          progreso.textContent = '✅ Foto subida correctamente';
          setTimeout(() => {
            progreso.classList.remove('activo');
            input.value = '';
            cargarFotos();
          }, 1000);
        } else {
          progreso.classList.remove('activo');
          alert('Error: ' + data.mensaje);
          input.value = '';
        }
      } catch (err) {
        console.error(err);
        progreso.classList.remove('activo');
        alert('Error de conexión');
        input.value = '';
      }
    }
  </script>

</body>
</html>