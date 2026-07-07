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

function csv_likert_texto($v){
    switch((int)$v){
        case 1: return 'Totalmente en desacuerdo';
        case 2: return 'En desacuerdo';
        case 3: return 'Neutral';
        case 4: return 'De acuerdo';
        case 5: return 'Totalmente de acuerdo';
        default: return 'Sin respuesta';
    }
}

$filename = 'respuestas_'.$r['folio_ceneval'].'.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Pragma: no-cache');
header('Expires: 0');
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');
fputcsv($out, ['Folio', $r['folio_ceneval']]);
fputcsv($out, ['Aspirante', $r['apellido_paterno'].' '.$r['apellido_materno'].' '.$r['nombres']]);
fputcsv($out, ['Programa', $r['maestria']]);
fputcsv($out, ['Fecha de examen', !empty($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '']);
fputcsv($out, ['Puntaje total', $r['puntaje_total'].' / 300']);
fputcsv($out, ['Nivel general', $r['nivel_general']]);
fputcsv($out, []);
fputcsv($out, ['No.', 'Dimensión', 'Pregunta', 'Respuesta original', 'Valor original', 'Reactivo inverso', 'Puntaje aplicado']);
foreach($respuestas as $resp){
    fputcsv($out, [$resp['numero'], $resp['dimension_nombre'], $resp['texto'], csv_likert_texto($resp['valor_original']), $resp['valor_original'], $resp['inversa'] ? 'Sí' : 'No', $resp['valor_puntuado']]);
}
fclose($out);
exit;
