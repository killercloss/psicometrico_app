<?php
session_start();
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
require_once __DIR__.'/../dompdf/autoload.inc.php';
require_admin();
csrf_check();

use Dompdf\Dompdf;
use Dompdf\Options;

$id = $_GET['id'] ?? null;

function imageToBase64Local($ruta){
    if(!file_exists($ruta)){ return ''; }
    $tipo = pathinfo($ruta, PATHINFO_EXTENSION);
    return 'data:image/'.$tipo.';base64,'.base64_encode(file_get_contents($ruta));
}
function pdf_likert_texto($v){
    switch((int)$v){
        case 1: return '1 - Totalmente en desacuerdo';
        case 2: return '2 - En desacuerdo';
        case 3: return '3 - Neutral';
        case 4: return '4 - De acuerdo';
        case 5: return '5 - Totalmente de acuerdo';
        default: return '0 - Sin respuesta';
    }
}

$logoUANL = imageToBase64Local(__DIR__.'/../resources/uanl.png');
$logoFCFM = imageToBase64Local(__DIR__.'/../resources/5 FCFM.png');

if(!$id){ die('Reporte no especificado.'); }

$st = $pdo->prepare("SELECT r.*, a.folio_ceneval, a.apellido_paterno, a.apellido_materno, a.nombres, a.correo, a.maestria, a.intentos_post_finalizacion, a.observaciones, a.fecha_nacimiento FROM resultados r JOIN aspirantes a ON a.id = r.aspirante_id WHERE r.id = ? LIMIT 1");
$st->execute([$id]);
$r = $st->fetch();
if(!$r){ die('Reporte no encontrado.'); }

$st = $pdo->prepare("SELECT rd.*, d.nombre FROM resultados_dimension rd JOIN dimensiones d ON d.id = rd.dimension_id WHERE rd.resultado_id = ? ORDER BY d.orden ASC");
$st->execute([$id]);
$dims = $st->fetchAll();

$st = $pdo->prepare("SELECT ep.*, d.nombre AS dimension_nombre FROM entrevista_preguntas_aplicadas ep LEFT JOIN dimensiones d ON d.id = ep.dimension_id WHERE ep.resultado_id = ? ORDER BY ep.id ASC");
$st->execute([$id]);
$preguntasEntrevista = $st->fetchAll();

$st = $pdo->prepare("SELECT resp.valor_original, resp.valor_puntuado, p.numero, p.texto, p.inversa, d.nombre AS dimension_nombre, d.orden AS dimension_orden FROM respuestas resp JOIN preguntas p ON p.id = resp.pregunta_id JOIN dimensiones d ON d.id = p.dimension_id WHERE resp.aspirante_id = ? ORDER BY d.orden ASC, p.numero ASC");
$st->execute([$r['aspirante_id']]);
$respuestasExamen = $st->fetchAll();

ob_start();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#010203;}
    .header{background:#1F6FB6;color:white;padding:18px;margin-bottom:20px;}
    h1{margin:0;font-size:22px;} h2{color:#1F6FB6;margin-top:22px;font-size:17px;}
    .info{margin-bottom:18px;line-height:1.6;}
    .stats{width:100%;margin-bottom:20px;}.stats td{width:25%;border:1px solid #d7e1ea;padding:10px;vertical-align:top;}
    .stat-title{font-size:10px;color:#555;}.stat-value{font-size:16px;font-weight:bold;color:#010203;}
    table{width:100%;border-collapse:collapse;} th{background:#1F6FB6;color:white;padding:7px;text-align:left;font-size:10px;} td{border:1px solid #d7e1ea;padding:6px;vertical-align:top;font-size:9.5px;}
    .general{background:#f5f7fa;border-left:5px solid #FFA600;padding:10px;margin-bottom:15px;}
    .footer{margin-top:25px;font-size:10px;color:#666;text-align:right;}
    .encabezado{margin-bottom:10px;background:#1F6FB6;border-bottom:5px solid #FFA600;padding:8px 20px;color:white;text-align:center;}
    .encabezado table,.encabezado td{border:0;}.encabezado img{height:70px;width:auto;}.titulo-encabezado{font-size:16px;font-weight:bold;color:white;text-align:center;}
    .page-break{page-break-before:always;}
</style>
</head>
<body>
<div class="encabezado"><table><tr><td style="width:20%;text-align:left;"><?php if($logoUANL): ?><img src="<?=$logoUANL?>"><?php endif; ?></td><td style="width:60%;" class="titulo-encabezado">Reporte completo de evaluación</td><td style="width:20%;text-align:right;"><?php if($logoFCFM): ?><img src="<?=$logoFCFM?>"><?php endif; ?></td></tr></table></div>
<div class="header"><h1>Reporte completo</h1><div>Sistema de Evaluación Psicométrica</div></div>
<div class="info">
    <b>Aspirante:</b> <?=h($r['apellido_paterno'].' '.$r['apellido_materno'].' '.$r['nombres'])?><br>
    <b>Folio:</b> <?=h($r['folio_ceneval'])?><br>
    <b>Correo:</b> <?=h($r['correo'])?><br>
    <b>Programa:</b> <?=h($r['maestria'])?><br>
    <b>Edad:</b> <?=calcular_edad($r['fecha_nacimiento'] ?? null)?><br>
    <b>Fecha de examen:</b> <?=!empty($r['created_at']) ? h(date('d/m/Y H:i', strtotime($r['created_at']))) : '—'?>
</div>
<table class="stats"><tr><td><div class="stat-title">Puntaje total</div><div class="stat-value"><?=$r['puntaje_total']?> / 300</div></td><td><div class="stat-title">Nivel general</div><div class="stat-value"><?=h($r['nivel_general'])?></div></td><td><div class="stat-title">Tiempo total</div><div class="stat-value"><?=format_seconds($r['tiempo_total_segundos'])?></div></td><td><div class="stat-title">Intentos post finalización</div><div class="stat-value"><?=$r['intentos_post_finalizacion']?></div></td></tr></table>
<div class="general"><b>Interpretación general:</b> <?=h($r['interpretacion_general'])?></div>
<h2>Resultados por dimensión</h2>
<table><tr><th>Dimensión</th><th>Puntaje</th><th>Nivel</th><th>Tiempo</th><th>Interpretación</th></tr><?php foreach($dims as $d): ?><tr><td><?=h($d['nombre'])?></td><td><?=$d['puntaje']?> / 60</td><td><?=h($d['nivel'])?></td><td><?=format_seconds($d['tiempo_segundos'])?></td><td><?=h($d['interpretacion'])?></td></tr><?php endforeach; ?></table>
<h2>Preguntas realizadas durante la entrevista</h2>
<?php if(!empty($preguntasEntrevista)): ?><table><tr><th>Dimensión</th><th>Pregunta</th><th>Respuesta / comentario</th></tr><?php foreach($preguntasEntrevista as $p): ?><tr><td><?=h($p['dimension_nombre'] ?? '—')?></td><td><?=h($p['pregunta_texto'])?></td><td><?=!empty($p['respuesta']) ? h($p['respuesta']) : '—'?></td></tr><?php endforeach; ?></table><?php else: ?><div class="general">No se registraron preguntas de entrevista.</div><?php endif; ?>
<h2>Observaciones generales</h2><div class="general"><?=!empty($r['observaciones']) ? nl2br(h($r['observaciones'])) : 'Sin observaciones registradas.'?></div>
<div class="page-break"></div>
<h2>Respuestas completas del examen</h2>
<table><tr><th>No.</th><th>Dimensión</th><th>Pregunta</th><th>Respuesta original</th><th>Inversa</th><th>Puntaje</th></tr><?php foreach($respuestasExamen as $resp): ?><tr><td><?=h($resp['numero'])?></td><td><?=h($resp['dimension_nombre'])?></td><td><?=h($resp['texto'])?></td><td><?=h(pdf_likert_texto($resp['valor_original']))?></td><td><?=$resp['inversa'] ? 'Sí' : 'No'?></td><td><?=h($resp['valor_puntuado'])?></td></tr><?php endforeach; ?><?php if(empty($respuestasExamen)): ?><tr><td colspan="6">No se encontraron respuestas registradas.</td></tr><?php endif; ?></table>
<div class="footer">Generado el <?=date('d/m/Y H:i')?> hrs.</div>
</body>
</html>
<?php
$html = ob_get_clean();
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
$nombreArchivo = 'reporte_completo_'.$r['folio_ceneval'].'.pdf';
$dompdf->stream($nombreArchivo, ['Attachment' => true]);
exit;
