<?php
    session_start();

    require_once __DIR__.'/../includes/db.php';
    require_once __DIR__.'/../includes/functions.php';

    require_admin();

    $id = $_GET['id'] ?? null;
    $tipo = $_GET['tipo'] ?? '';

    if(!$id || !in_array($tipo, ['examen', 'entrevista'])){
        die('Solicitud inválida.');
    }

    $st = $pdo->prepare('SELECT * FROM aspirantes WHERE id=? LIMIT 1');
    $st->execute([$id]);
    $a = $st->fetch();

    if(!$a){
        die('Aspirante no encontrado.');
    }

    if($tipo === 'examen'){

        /*if(!empty($a['examen_correo_enviado_at'])){
            echo "<script>alert('Correo de examen ya fue preparado/enviado. Revisar tabla.'); window.location='aspirantes.php';</script>";
            exit;
        }*/

        $pdo->prepare('UPDATE aspirantes SET examen_correo_enviado_at = NOW() WHERE id=?')
            ->execute([$id]);

        $url = mailto_examen($a);
?>
        <!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <script>
                window.location.href = <?=json_encode($url, JSON_UNESCAPED_UNICODE)?>;
                setTimeout(function(){
                    window.location.href = 'aspirantes.php';
                }, 1000);
            </script>
        </head>
        <body>
            Abriendo cliente de correo...
        </body>
        </html>
        <?php
        exit;
    }

    if($tipo === 'entrevista'){

        /*if(!empty($a['entrevista_correo_enviado_at'])){
            echo "<script>alert('Correo de entrevista ya fue preparado/enviado. Revisar tabla.'); window.location='aspirantes.php';</script>";
            exit;
        }*/

        $pdo->prepare('UPDATE aspirantes SET entrevista_correo_enviado_at = NOW() WHERE id=?')
            ->execute([$id]);

        $url = mailto_entrevista($a);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body>

<script>
    const mailto = <?=json_encode(
        $url,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    )?>;

    window.location.href = mailto;

    setTimeout(function () {
        window.location.href = 'aspirantes.php';
    }, 1500);
</script>

</body>
</html>

<?php
exit;
}
?>