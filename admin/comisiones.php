<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../public/loginGL.php");
    exit;
}

require '../app/conexion.php';

$sql = "SELECT c.*, 
               col.nombre AS colaborador_nombre,
               m.origen, m.destino, m.fecha_servicio, m.usuario_id,
               u.nombre AS cliente_nombre
        FROM comisiones c
        LEFT JOIN colaboradores col ON c.colaborador_id = col.id
        LEFT JOIN mudanzas m ON c.mudanza_id = m.id
        LEFT JOIN usuarios u ON m.usuario_id = u.id
        ORDER BY c.fecha DESC";
$comisiones = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$totales = [
    'facturado'   => 0,
    'comision'    => 0,
    'colaborador' => 0,
    'pendiente'   => 0,
    'pagado'      => 0,
];

foreach ($comisiones as $c) {
    $totales['facturado']   += (float)$c['valor_servicio'];
    $totales['comision']    += (float)$c['valor_comision'];
    $totales['colaborador'] += (float)$c['valor_colaborador'];
    if ($c['estado'] === 'pendiente') {
        $totales['pendiente'] += (float)$c['valor_colaborador'];
    } else {
        $totales['pagado'] += (float)$c['valor_colaborador'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Comisiones — Mudanzas GL</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --rojo: #c0392b; --negro: #0d0d0d; --negro-card: #1a1a1a;
      --gris: #2a2a2a; --borde: #333; --texto: #e8e8e8; --texto-dim: #888;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:var(--negro); color:var(--texto); min-height:100vh; }
    header {
      background:var(--negro-card); border-bottom:2px solid var(--rojo);
      padding:1.2rem 3rem; display:flex; justify-content:space-between; align-items:center;
    }
    .marca { font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:4px; }
    .marca span { color:var(--rojo); }
    .btn-salir {
      background:transparent; border:1px solid var(--borde); color:var(--texto-dim);
      padding:0.5rem 1.2rem; font-size:0.72rem; letter-spacing:2px; text-transform:uppercase;
      cursor:pointer; text-decoration:none;
    }
    .btn-salir:hover { border-color:var(--rojo); color:var(--rojo); }

    nav.admin-nav {
      background:var(--negro-card); border-bottom:1px solid var(--borde);
      padding:0 3rem; display:flex; gap:0;
    }
    nav.admin-nav a {
      text-decoration:none; color:var(--texto-dim); font-size:0.75rem;
      font-weight:500; letter-spacing:2px; text-transform:uppercase;
      padding:1rem 1.5rem; border-bottom:2px solid transparent;
      transition:color 0.2s, border-color 0.2s;
    }
    nav.admin-nav a:hover { color:var(--texto); }
    nav.admin-nav a.active { color:var(--rojo); border-bottom-color:var(--rojo); }

    main { padding:3rem; }
    h1 { font-family:'Bebas Neue',sans-serif; font-size:2rem; letter-spacing:3px; margin-bottom:2rem; }

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
      letter-spacing:1px; color:var(--texto);
    }
    .stat-valor.rojo    { color:var(--rojo); }
    .stat-valor.verde   { color:#27ae60; }
    .stat-valor.amarillo{ color:#f39c12; }

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
    .estado-pendiente { background:#f39c1233; color:#f39c12; border:1px solid #f39c12; }
    .estado-pagada    { background:#27ae6033; color:#27ae60; border:1px solid #27ae60; }
    .valor { color:var(--rojo); font-weight:600; }
    .valor-verde { color:#27ae60; font-weight:600; }
    button.btn-accion {
      background:var(--rojo); border:none; color:white; font-weight:500;
      padding:0.5rem 1rem; letter-spacing:1px; text-transform:uppercase; font-size:0.72rem;
      cursor:pointer;
    }
    button.btn-accion:hover { background:#e74c3c; }
    button.btn-accion.verde { background:#27ae60; }
    button.btn-accion.verde:hover { background:#2ecc71; }
  </style>
</head>
<body>
    <header>
    <div class="marca">PANEL <span>ADMIN</span></div>
    <div style="display:flex; align-items:center; gap:1rem;">
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

  <nav class="admin-nav">
    <a href="panel.php" class="<?= basename($_SERVER['PHP_SELF']) === 'panel.php' ? 'active' : '' ?>">Mudanzas</a>
    <a href="comisiones.php" class="<?= basename($_SERVER['PHP_SELF']) === 'comisiones.php' ? 'active' : '' ?>">Comisiones</a>
    <a href="calificaciones.php" class="<?= basename($_SERVER['PHP_SELF']) === 'calificaciones.php' ? 'active' : '' ?>">Calificaciones</a>
  </nav>

  <main>
    <h1>Comisiones</h1>

    <div class="stats">
      <div class="stat-card">
        <div class="stat-label">Total facturado</div>
        <div class="stat-valor">$<?= number_format($totales['facturado'], 0, ',', '.') ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Comisión Mudanzas GL</div>
        <div class="stat-valor rojo">$<?= number_format($totales['comision'], 0, ',', '.') ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">A pagar a colaboradores</div>
        <div class="stat-valor amarillo">$<?= number_format($totales['pendiente'], 0, ',', '.') ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Ya pagado</div>
        <div class="stat-valor verde">$<?= number_format($totales['pagado'], 0, ',', '.') ?></div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Colaborador</th>
          <th>Mudanza</th>
          <th>Valor servicio</th>
          <th>Comisión GL</th>
          <th>Pago colaborador</th>
          <th>Estado</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($comisiones as $c): ?>
        <tr>
          <td>#<?= $c['id'] ?></td>
          <td><strong><?= htmlspecialchars($c['colaborador_nombre'] ?? '—') ?></strong></td>
          <td>
            Mudanza #<?= $c['mudanza_id'] ?><br/>
            <small style="color:#888">
              <?= htmlspecialchars($c['origen'] ?? '') ?> → <?= htmlspecialchars($c['destino'] ?? '') ?>
            </small>
          </td>
          <td>$<?= number_format($c['valor_servicio'], 0, ',', '.') ?></td>
          <td class="valor">$<?= number_format($c['valor_comision'], 0, ',', '.') ?></td>
          <td class="valor-verde">$<?= number_format($c['valor_colaborador'], 0, ',', '.') ?></td>
          <td><span class="estado estado-<?= $c['estado'] ?>"><?= $c['estado'] ?></span></td>
          <td>
            <?php if ($c['estado'] === 'pendiente'): ?>
              <button class="btn-accion verde" onclick="marcarPagada(<?= $c['id'] ?>)">Marcar pagada</button>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($comisiones)): ?>
          <tr><td colspan="8" style="text-align:center; color:#888; padding:2rem;">No hay comisiones todavía</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>

  <script>
    async function marcarPagada(comisionId) {
      if (!confirm('¿Confirmas que ya le pagaste al colaborador?')) return;

      const res = await fetch('marcar_comision_pagada.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ comision_id: comisionId })
      });
      const data = await res.json();
      if (data.ok) location.reload();
      else alert('Error: ' + data.mensaje);
    }

    
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
  
  </script>
</body>
</html>