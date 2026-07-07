<?php
session_start();
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
require_admin();
csrf_check();

$id = $_GET['id'] ?? null;
if(!$id){ die('Resultado no especificado.'); }

$st = $pdo->prepare("SELECT r.*, a.folio_ceneval, a.apellido_paterno, a.apellido_materno, a.nombres, a.correo, a.maestria FROM resultados r JOIN aspirantes a ON a.id = r.aspirante_id WHERE r.id = ? LIMIT 1");
$st->execute([$id]);
$r = $st->fetch();
if(!$r){ die('Resultado no encontrado.'); }

$st = $pdo->prepare("SELECT resp.valor_original, resp.valor_puntuado, p.numero, p.texto, p.inversa, d.nombre AS dimension_nombre, d.orden AS dimension_orden FROM respuestas resp JOIN preguntas p ON p.id = resp.pregunta_id JOIN dimensiones d ON d.id = p.dimension_id WHERE resp.aspirante_id = ? ORDER BY d.orden ASC, p.numero ASC");
$st->execute([$r['aspirante_id']]);
$respuestas = $st->fetchAll();

function likert_texto($v){
    switch((int)$v){
        case 1: return '1 - Totalmente en desacuerdo';
        case 2: return '2 - En desacuerdo';
        case 3: return '3 - Neutral';
        case 4: return '4 - De acuerdo';
        case 5: return '5 - Totalmente de acuerdo';
        default: return '0 - Sin respuesta';
    }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Respuestas del aspirante</title><link rel="stylesheet" href="../assets/style.css"></head>
<body>
<?php include '_nav.php'; ?>
<div class="container">
    <h1>Respuestas del examen</h1>
    <div class="card">
        <p>
            <b>Aspirante:</b> <?=h($r['apellido_paterno'].' '.$r['apellido_materno'].' '.$r['nombres'])?><br>
            <b>Folio:</b> <?=h($r['folio_ceneval'])?><br>
            <b>Programa:</b> <?=h($r['maestria'])?><br>
            <b>Fecha de examen:</b> <?=!empty($r['created_at']) ? h(date('d/m/Y H:i', strtotime($r['created_at']))) : '—'?><br>
            <b>Puntaje total:</b> <?=$r['puntaje_total']?> / 300<br>
            <b>Nivel general:</b> <?=h($r['nivel_general'])?>
        </p>
        <a class="btn secondary" href="respuestas_examen.php">Volver</a>
        <a class="btn secondary" href="respuestas_csv.php?id=<?=$r['id']?>">Descargar CSV</a>
        <a class="btn" href="imprimir_pdf_completo.php?id=<?=$r['id']?>" target="_blank">Reporte completo PDF</a>
    </div>
    <div class="card">
        <h2>Detalle de respuestas</h2>
        <div class="table-wrapper">
            <table class="table">
                <tr><th>No.</th><th>Dimensión</th><th>Pregunta</th><th>Respuesta original</th><th>Reactivo inverso</th><th>Puntaje aplicado</th></tr>
                <?php foreach($respuestas as $resp): ?>
                    <tr>
                        <td><?=h($resp['numero'])?></td>
                        <td><?=h($resp['dimension_nombre'])?></td>
                        <td><?=h($resp['texto'])?></td>
                        <td><?=h(likert_texto($resp['valor_original']))?></td>
                        <td><?=$resp['inversa'] ? 'Sí' : 'No'?></td>
                        <td><?=h($resp['valor_puntuado'])?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($respuestas)): ?><tr><td colspan="6">No se encontraron respuestas registradas.</td></tr><?php endif; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
