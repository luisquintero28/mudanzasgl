<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../public/loginGL.php");
    exit;
}

require '../app/conexion.php';

// Filtro por colaborador (opcional)
$filtro_colaborador = (int)($_GET['colaborador'] ?? 0);

// Traer colaboradores para el filtro
$colaboradores = $conexion->query("SELECT id, nombre FROM colaboradores ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Query principal
$sql = "SELECT cal.*, 
               u.nombre AS cliente_nombre,
               col.nombre AS colaborador_nombre,
               col.id AS colaborador_id,
               m.origen, m.destino, m.fecha_servicio
        FROM calificaciones cal
        INNER JOIN usuarios u ON cal.usuario_id = u.id
        INNER JOIN mudanzas m ON cal.mudanza_id = m.id
        INNER JOIN colaboradores col ON m.colaborador_id = col.id";

if ($filtro_colaborador > 0) {
    $sql .= " WHERE col.id = :col_id";
}

$sql .= " ORDER BY cal.fecha DESC";

$stmt = $conexion->prepare($sql);
if ($filtro_colaborador > 0) {
    $stmt->execute([':col_id' => $filtro_colaborador]);
} else {
    $stmt->execute();
}
$calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats generales
$stmt = $conexion->query("SELECT COUNT(*) as total, AVG(estrellas) as promedio FROM calificaciones");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$total_calif = (int)$stats['total'];
$promedio_general = $total_calif > 0 ? round($stats['promedio'], 1) : 0;

// Ranking de colaboradores
$sqlRanking = "SELECT col.id, col.nombre, 
                      COUNT(cal.id) as total_calif,
                      AVG(cal.estrellas) as promedio
               FROM colaboradores col
               INNER JOIN mudanzas m ON m.colaborador_id = col.id
               INNER JOIN calificaciones cal ON cal.mudanza_id = m.id
               GROUP BY col.id, col.nombre
               HAVING COUNT(cal.id) > 0
               ORDER BY promedio DESC, total_calif DESC
               LIMIT 5";
$ranking = $conexion->query($sqlRanking)->fetchAll(PDO::FETCH_ASSOC);

// Colaborador mejor calificado
$mejor_colaborador = !empty($ranking) ? $ranking[0] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Calificaciones — Mudanzas GL</title>
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

    /* STATS */
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
    .stat-valor.dorado { color:var(--dorado); }
    .stat-estrellas { font-size:1rem; color:var(--dorado); letter-spacing:0.2rem; margin-top:0.3rem; }

    /* FILTRO */
    .filtro {
      background:var(--negro-card); border:1px solid var(--borde);
      padding:1.5rem; margin-bottom:2rem; display:flex; align-items:center; gap:1rem;
      flex-wrap:wrap;
    }
    .filtro label {
      font-size:0.7rem; letter-spacing:2px; text-transform:uppercase;
      color:var(--texto-dim);
    }
    .filtro select {
      background:var(--gris); border:1px solid var(--borde); color:var(--texto);
      padding:0.5rem 1rem; font-family:'Inter',sans-serif; font-size:0.85rem; cursor:pointer;
    }
    .filtro button {
      background:var(--rojo); border:none; color:white; padding:0.5rem 1.2rem;
      font-size:0.72rem; letter-spacing:2px; text-transform:uppercase;
      cursor:pointer; font-weight:500;
    }
    .filtro button:hover { background:#e74c3c; }
    .filtro .btn-limpiar {
      background:transparent; border:1px solid var(--borde); color:var(--texto-dim);
    }
    .filtro .btn-limpiar:hover { border-color:var(--texto-dim); color:var(--texto); background:transparent; }

    /* TABLA */
    table { width:100%; border-collapse:collapse; background:var(--negro-card); border:1px solid var(--borde); margin-bottom:3rem; }
    th {
      background:var(--gris); color:var(--texto-dim); font-size:0.7rem;
      letter-spacing:2px; text-transform:uppercase; padding:1rem; text-align:left;
      border-bottom:1px solid var(--borde);
    }
    td { padding:1rem; border-bottom:1px solid var(--borde); font-size:0.85rem; }
    tr:hover td { background:#1f1f1f; }

    .estrellas {
      color:var(--dorado); font-size:1rem; letter-spacing:0.2rem;
    }
    .comentario {
      font-style:italic; color:var(--texto-dim); font-size:0.82rem;
      max-width:400px;
    }

    /* RANKING */
    .seccion-titulo {
      font-family:'Bebas Neue',sans-serif; font-size:1.2rem;
      letter-spacing:3px; margin-bottom:1rem; color:var(--texto);
      display:flex; align-items:center; gap:1rem;
    }
    .seccion-titulo::after {
      content:''; flex:1; height:1px; background:var(--borde);
    }

    .ranking-grid {
      display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));
      gap:1rem;
    }
    .ranking-card {
      background:var(--negro-card); border:1px solid var(--borde);
      border-left:3px solid var(--dorado); padding:1.5rem;
      display:flex; align-items:center; gap:1rem;
    }
    .ranking-pos {
      font-family:'Bebas Neue',sans-serif; font-size:2.5rem;
      color:var(--dorado); line-height:1; letter-spacing:2px;
    }
    .ranking-info { flex:1; }
    .ranking-nombre { font-weight:600; font-size:0.95rem; margin-bottom:0.3rem; }
    .ranking-estrellas { color:var(--dorado); font-size:0.9rem; letter-spacing:0.15rem; }
    .ranking-total { font-size:0.72rem; color:var(--texto-dim); margin-top:0.2rem; }
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
    <a href="panel.php">Mudanzas</a>
    <a href="comisiones.php">Comisiones</a>
    <a href="calificaciones.php" class="active">Calificaciones</a>
  </nav>

  <main>
    <h1>Calificaciones</h1>

    <!-- STATS -->
    <div class="stats">
      <div class="stat-card">
        <div class="stat-label">Total calificaciones</div>
        <div class="stat-valor"><?= $total_calif ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Promedio general</div>
        <div class="stat-valor dorado"><?= number_format($promedio_general, 1) ?></div>
        <div class="stat-estrellas">
          <?php
            $est = round($promedio_general);
            echo str_repeat('★', $est) . str_repeat('☆', 5 - $est);
          ?>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Mejor colaborador</div>
        <div class="stat-valor" style="font-size:1.3rem;">
          <?= $mejor_colaborador ? htmlspecialchars($mejor_colaborador['nombre']) : '—' ?>
        </div>
        <?php if ($mejor_colaborador): ?>
          <div class="stat-estrellas">⭐ <?= number_format($mejor_colaborador['promedio'], 1) ?></div>
        <?php endif; ?>
      </div>
      <div class="stat-card">
        <div class="stat-label">Colaboradores calificados</div>
        <div class="stat-valor"><?= count($ranking) ?></div>
      </div>
    </div>

    <!-- FILTRO -->
    <form class="filtro" method="GET">
      <label>Filtrar por colaborador:</label>
      <select name="colaborador">
        <option value="0">Todos</option>
        <?php foreach ($colaboradores as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $filtro_colaborador == $c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Filtrar</button>
      <?php if ($filtro_colaborador > 0): ?>
        <a href="calificaciones.php" class="btn-limpiar" style="text-decoration:none; padding:0.5rem 1.2rem; display:inline-block;">Limpiar</a>
      <?php endif; ?>
    </form>

    <!-- TABLA -->
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Colaborador</th>
          <th>Cliente</th>
          <th>Mudanza</th>
          <th>Estrellas</th>
          <th>Comentario</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($calificaciones as $cal): ?>
        <tr>
          <td>#<?= $cal['id'] ?></td>
          <td><strong><?= htmlspecialchars($cal['colaborador_nombre']) ?></strong></td>
          <td><?= htmlspecialchars($cal['cliente_nombre']) ?></td>
          <td>
            #<?= $cal['mudanza_id'] ?><br/>
            <small style="color:#888"><?= htmlspecialchars($cal['origen']) ?> → <?= htmlspecialchars($cal['destino']) ?></small>
          </td>
          <td>
            <span class="estrellas">
              <?= str_repeat('★', (int)$cal['estrellas']) . str_repeat('☆', 5 - (int)$cal['estrellas']) ?>
            </span>
          </td>
          <td>
            <?php if (!empty($cal['comentario'])): ?>
              <div class="comentario">"<?= htmlspecialchars($cal['comentario']) ?>"</div>
            <?php else: ?>
              <span style="color:#555;">— sin comentario —</span>
            <?php endif; ?>
          </td>
          <td><?= date('d/m/Y', strtotime($cal['fecha'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($calificaciones)): ?>
          <tr><td colspan="7" style="text-align:center; color:#888; padding:2rem;">No hay calificaciones todavía</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- RANKING -->
    <div class="seccion-titulo">Ranking de colaboradores</div>
    <?php if (empty($ranking)): ?>
      <div style="background:var(--negro-card); border:1px solid var(--borde); padding:2rem; text-align:center; color:#888;">
        Aún no hay datos suficientes para el ranking
      </div>
    <?php else: ?>
      <div class="ranking-grid">
        <?php foreach ($ranking as $i => $r): ?>
          <div class="ranking-card">
            <div class="ranking-pos">#<?= $i + 1 ?></div>
            <div class="ranking-info">
              <div class="ranking-nombre"><?= htmlspecialchars($r['nombre']) ?></div>
              <div class="ranking-estrellas">
                <?php
                  $est = round($r['promedio']);
                  echo str_repeat('★', $est) . str_repeat('☆', 5 - $est);
                ?>
                <?= number_format($r['promedio'], 1) ?>
              </div>
              <div class="ranking-total"><?= $r['total_calif'] ?> calificación<?= $r['total_calif'] > 1 ? 'es' : '' ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
  
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
  </script>
  
</body>

</html>