<?php
session_start();

require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
require_once __DIR__.'/../dompdf/autoload.inc.php';

require_admin();

use Dompdf\Dompdf;
use Dompdf\Options;

$id = $_GET['id'] ?? null;

if(!$id){
    die('Reporte no especificado.');
}

$st = $pdo->prepare("
    SELECT 
        r.*, 
        a.folio_ceneval,
        a.apellido_paterno,
        a.apellido_materno,
        a.nombres,
        a.correo,
        a.maestria,
        a.intentos_post_finalizacion
    FROM resultados r
    JOIN aspirantes a ON a.id = r.aspirante_id
    WHERE r.id = ?
    LIMIT 1
");

$st->execute([$id]);
$r = $st->fetch();

if(!$r){
    die('Reporte no encontrado.');
}

$st = $pdo->prepare("
    SELECT 
        rd.*,
        d.nombre
    FROM resultados_dimension rd
    JOIN dimensiones d ON d.id = rd.dimension_id
    WHERE rd.resultado_id = ?
    ORDER BY d.orden ASC
");

$st->execute([$id]);
$dims = $st->fetchAll();

ob_start();
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    body{
        font-family: DejaVu Sans, sans-serif;
        font-size:12px;
        color:#010203;
    }

    .header{
        background:#1F6FB6;
        color:white;
        padding:18px;
        border-bottom:5px solid #FFA600;
        margin-bottom:20px;
    }

    h1{
        margin:0;
        font-size:22px;
    }

    h2{
        color:#1F6FB6;
        margin-top:25px;
        font-size:18px;
    }

    .info{
        margin-bottom:18px;
        line-height:1.6;
    }

    .stats{
        width:100%;
        margin-bottom:20px;
    }

    .stats td{
        width:25%;
        border:1px solid #d7e1ea;
        padding:10px;
        vertical-align:top;
    }

    .stat-title{
        font-size:11px;
        color:#555;
    }

    .stat-value{
        font-size:18px;
        font-weight:bold;
        color:#010203;
    }

    table{
        width:100%;
        border-collapse:collapse;
    }

    th{
        background:#1F6FB6;
        color:white;
        padding:8px;
        text-align:left;
        font-size:11px;
    }

    td{
        border:1px solid #d7e1ea;
        padding:8px;
        vertical-align:top;
        font-size:10.5px;
    }

    .general{
        background:#f5f7fa;
        border-left:5px solid #FFA600;
        padding:12px;
        margin-bottom:15px;
    }

    .footer{
        margin-top:25px;
        font-size:10px;
        color:#666;
        text-align:right;
    }
</style>
</head>
<body>

<div class="header">
    <h1>Reporte individual</h1>
    <div>Sistema de Evaluación Psicométrica</div>
</div>

<div class="info">
    <b>Aspirante:</b>
    <?=h($r['apellido_paterno'].' '.$r['apellido_materno'].' '.$r['nombres'])?><br>

    <b>Folio:</b>
    <?=h($r['folio_ceneval'])?><br>

    <b>Correo:</b>
    <?=h($r['correo'])?><br>

    <b>Programa:</b>
    <?=h($r['maestria'])?>
</div>

<table class="stats">
    <tr>
        <td>
            <div class="stat-title">Puntaje total</div>
            <div class="stat-value"><?=$r['puntaje_total']?> / 300</div>
        </td>
        <td>
            <div class="stat-title">Nivel general</div>
            <div class="stat-value"><?=h($r['nivel_general'])?></div>
        </td>
        <td>
            <div class="stat-title">Tiempo total</div>
            <div class="stat-value"><?=format_seconds($r['tiempo_total_segundos'])?></div>
        </td>
        <td>
            <div class="stat-title">Intentos post finalización</div>
            <div class="stat-value"><?=$r['intentos_post_finalizacion']?></div>
        </td>
    </tr>
</table>

<div class="general">
    <b>Interpretación general:</b>
    <?=h($r['interpretacion_general'])?>
</div>

<h2>Resultados por dimensión</h2>

<table>
    <tr>
        <th>Dimensión</th>
        <th>Puntaje</th>
        <th>Nivel</th>
        <th>Tiempo</th>
        <th>Interpretación</th>
    </tr>

    <?php foreach($dims as $d): ?>
        <tr>
            <td><?=h($d['nombre'])?></td>
            <td><?=$d['puntaje']?> / 60</td>
            <td><?=h($d['nivel'])?></td>
            <td><?=format_seconds($d['tiempo_segundos'])?></td>
            <td><?=h($d['interpretacion'])?></td>
        </tr>
    <?php endforeach; ?>
</table>

<div class="footer">
    Generado el <?=date('d/m/Y H:i')?> hrs.
</div>

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

$nombreArchivo = 'reporte_'.$r['folio_ceneval'].'.pdf';

$dompdf->stream($nombreArchivo, [
    'Attachment' => true
]);
exit;