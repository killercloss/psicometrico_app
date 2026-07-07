<?php
session_start();
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
require_admin();
csrf_check();

$programas = $pdo->query('SELECT * FROM programas WHERE activo=1 ORDER BY tipo DESC, nombre ASC')->fetchAll();

$aspirantesSt = $pdo->query("SELECT DISTINCT a.id, a.folio_ceneval, a.apellido_paterno, a.apellido_materno, a.nombres, a.maestria FROM resultados r JOIN aspirantes a ON a.id = r.aspirante_id ORDER BY a.folio_ceneval ASC, a.apellido_paterno ASC");
$aspirantesFiltro = $aspirantesSt->fetchAll();

$where = [];
$params = [];

if(!empty($_GET['maestria'])){ $where[] = 'a.maestria = ?'; $params[] = $_GET['maestria']; }
if(!empty($_GET['fecha'])){ $where[] = 'DATE(r.created_at) = ?'; $params[] = $_GET['fecha']; }
if(!empty($_GET['aspirante_id'])){ $where[] = 'a.id = ?'; $params[] = (int)$_GET['aspirante_id']; }

$sql = "SELECT r.id AS resultado_id, r.created_at AS fecha_examen, r.puntaje_total, r.nivel_general, a.id AS aspirante_id, a.folio_ceneval, a.apellido_paterno, a.apellido_materno, a.nombres, a.maestria FROM resultados r JOIN aspirantes a ON a.id = r.aspirante_id";
if($where){ $sql .= ' WHERE '.implode(' AND ', $where); }
$sql .= ' ORDER BY a.folio_ceneval ASC, r.created_at DESC';

$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Respuestas del examen</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include '_nav.php'; ?>
<div class="encabezadoDash"><p class="dashTitle">Panel de Control - Respuestas del examen<br>Departamento de Orientación Psicopedagógica<br>Bienvenido(a)</p></div>
<div class="container">
    <h1>Respuestas del examen</h1>
    <div class="card">
        <form method="get" class="grid">
            <div>
                <label>Programa</label>
                <select name="maestria" id="filtroPrograma">
                    <option value="">Todos</option>
                    <?php foreach($programas as $p): ?>
                        <option value="<?=h($p['nombre'])?>" <?=($_GET['maestria']??'')===$p['nombre']?'selected':''?>><?=h($p['tipo'].' - '.$p['nombre'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Fecha de examen</label>
                <input type="date" name="fecha" value="<?=h($_GET['fecha'] ?? '')?>">
            </div>
            <div>
                <label>Folio / Aspirante</label>
                <select name="aspirante_id" id="filtroAspirante">
                    <option value="">Todos</option>
                    <?php foreach($aspirantesFiltro as $a): ?>
                        <option value="<?=$a['id']?>" data-programa="<?=h($a['maestria'])?>" <?=((string)($_GET['aspirante_id']??''))===(string)$a['id']?'selected':''?>><?=h($a['folio_ceneval'].' - '.$a['apellido_paterno'].' '.$a['apellido_materno'].' '.$a['nombres'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label>&nbsp;</label><button type="submit">Filtrar</button></div>
            <div><label>&nbsp;</label><a class="btn secondary" href="respuestas_examen.php">Limpiar filtros</a></div>
        </form>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <tr><th>Folio</th><th>Nombre</th><th>Programa</th><th>Fecha de examen</th><th>Puntaje total</th><th>Nivel general</th><th>Acciones</th></tr>
            <?php foreach($rows as $r): ?>
                <tr>
                    <td><?=h($r['folio_ceneval'])?></td>
                    <td><?=h($r['apellido_paterno'].' '.$r['apellido_materno'].' '.$r['nombres'])?></td>
                    <td><?=h($r['maestria'])?></td>
                    <td><?=!empty($r['fecha_examen']) ? h(date('d/m/Y H:i', strtotime($r['fecha_examen']))) : '—'?></td>
                    <td><?=$r['puntaje_total']?> / 300</td>
                    <td><span class="badge <?=$r['nivel_general']=='Alto'?'ok':($r['nivel_general']=='Medio'?'mid':'low')?>"><?=h($r['nivel_general'])?></span></td>
                    <td class="actions">
                        <a class="btn secondary" href="respuestas_ver.php?id=<?=$r['resultado_id']?>">Ver en línea</a>
                        <a class="btn secondary" href="respuestas_csv.php?id=<?=$r['resultado_id']?>">CSV</a>
                        <a class="btn" href="imprimir_pdf_completo.php?id=<?=$r['resultado_id']?>" target="_blank">Reporte completo</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($rows)): ?><tr><td colspan="7">No se encontraron resultados con esos filtros.</td></tr><?php endif; ?>
        </table>
    </div>
</div>
<script>
const programa = document.getElementById('filtroPrograma');
const aspirante = document.getElementById('filtroAspirante');
function filtrarAspirantesPorPrograma(){
    const seleccionado = programa.value;
    Array.from(aspirante.options).forEach((opt, idx) => {
        if(idx === 0){ opt.hidden = false; return; }
        const prog = opt.getAttribute('data-programa') || '';
        opt.hidden = seleccionado !== '' && prog !== seleccionado;
    });
    const actual = aspirante.options[aspirante.selectedIndex];
    if(actual && actual.hidden){ aspirante.value = ''; }
}
programa.addEventListener('change', filtrarAspirantesPorPrograma);
filtrarAspirantesPorPrograma();
</script>
</body>
</html>
