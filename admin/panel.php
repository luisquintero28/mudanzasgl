<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../public/loginGL.php");
    exit;
}

require '../app/conexion.php';

$sql = "SELECT m.*, u.nombre AS cliente_nombre, u.cedula AS cliente_cedula,
               c.nombre AS colaborador_nombre
        FROM mudanzas m
        LEFT JOIN usuarios u ON m.usuario_id = u.id
        LEFT JOIN colaboradores c ON m.colaborador_id = c.id
        ORDER BY m.fecha_creacion DESC";
$mudanzas = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$colaboradores = $conexion->query("SELECT id, nombre, tipo_vehiculo, capacidad_toneladas FROM colaboradores")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Admin — Mudanzas GL</title>
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
    select, button {
      background:var(--gris); border:1px solid var(--borde); color:var(--texto);
      padding:0.5rem; font-size:0.8rem; font-family:'Inter',sans-serif; cursor:pointer;
    }
    button.btn-accion {
      background:var(--rojo); border:none; color:white; font-weight:500;
      padding:0.5rem 1rem; letter-spacing:1px; text-transform:uppercase; font-size:0.72rem;
    }
    button.btn-accion:hover { background:#e74c3c; }
    .valor { color:var(--rojo); font-weight:600; }
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
    <a href="pagos.php" class="<?= basename($_SERVER['PHP_SELF']) === 'pagos.php' ? 'active' : '' ?>">Pagos</a>
    <a href="comisiones.php" class="<?= basename($_SERVER['PHP_SELF']) === 'comisiones.php' ? 'active' : '' ?>">Comisiones</a>
    <a href="calificaciones.php" class="<?= basename($_SERVER['PHP_SELF']) === 'calificaciones.php' ? 'active' : '' ?>">Calificaciones</a>
  </nav>

  <main>
    <h1>Mudanzas agendadas (<?= count($mudanzas) ?>)</h1>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Ruta</th>
          <th>Fecha servicio</th>
          <th>Valor</th>
          <th>Estado</th>
          <th>Colaborador</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($mudanzas as $m): ?>
        <tr>
          <td>#<?= $m['id'] ?></td>
          <td>
            <strong><?= htmlspecialchars($m['cliente_nombre'] ?? '—') ?></strong><br/>
            <small style="color:#888">CC <?= htmlspecialchars($m['cliente_cedula'] ?? '') ?></small>
          </td>
          <td>
            <?= htmlspecialchars($m['origen']) ?> → <?= htmlspecialchars($m['destino']) ?><br/>
            <small style="color:#888"><?= htmlspecialchars($m['tipo']) ?> · <?= htmlspecialchars($m['tamano']) ?></small>
          </td>
          <td><?= date('d/m/Y H:i', strtotime($m['fecha_servicio'])) ?></td>
          <td class="valor">$<?= number_format($m['valor'], 0, ',', '.') ?></td>
          <td><span class="estado estado-<?= $m['estado'] ?>"><?= $m['estado'] ?></span></td>
          <td>
            <?php if ($m['colaborador_id']): ?>
              <?= htmlspecialchars($m['colaborador_nombre']) ?>
            <?php else: ?>
              <select id="colab-<?= $m['id'] ?>">
                <option value="">Asignar...</option>
                <?php foreach ($colaboradores as $c): ?>
                  <option value="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['nombre']) ?> (<?= $c['tipo_vehiculo'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!$m['colaborador_id']): ?>
              <button class="btn-accion" onclick="asignar(<?= $m['id'] ?>)">Asignar</button>
            <?php elseif ($m['estado'] === 'programada'): ?>
              <button class="btn-accion" onclick="cambiarEstado(<?= $m['id'] ?>, 'en_proceso')">Iniciar</button>
            <?php elseif ($m['estado'] === 'en_proceso'): ?>
              <button class="btn-accion" onclick="cambiarEstado(<?= $m['id'] ?>, 'completada')">Finalizar</button>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($mudanzas)): ?>
          <tr><td colspan="8" style="text-align:center; color:#888; padding:2rem;">No hay mudanzas todavía</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>

  <script>
    async function asignar(mudanzaId) {
      const sel = document.getElementById('colab-' + mudanzaId);
      const colabId = sel.value;
      if (!colabId) { alert('Selecciona un colaborador'); return; }

      const res = await fetch('asignar_colaborador.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ mudanza_id: mudanzaId, colaborador_id: colabId })
      });
      const data = await res.json();
      if (data.ok) location.reload();
      else alert('Error: ' + data.mensaje);
    }

    async function cambiarEstado(mudanzaId, nuevoEstado) {
      const res = await fetch('cambiar_estado.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ mudanza_id: mudanzaId, estado: nuevoEstado })
      });
      const data = await res.json();
      if (data.ok) location.reload();
      else alert('Error: ' + data.mensaje);
    };
       


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