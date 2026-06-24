<?php
    session_start(); 
    require_once __DIR__.'/includes/db.php'; 
    require_once __DIR__.'/includes/functions.php';
    $msg='';
    
    if($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $folio = trim($_POST['folio']??''); 
        $codigo = trim($_POST['codigo']??'');
        $st = $pdo->prepare('SELECT * FROM aspirantes WHERE folio_ceneval=? AND codigo_acceso = ? LIMIT 1');
        $st->execute([$folio,$codigo]); 
        $a = $st->fetch();
        if(!$a)
            { 
                $msg='Folio o código incorrecto.'; 
            }
        elseif(!$a['autorizado'])
            { 
                $msg='Este aspirante no está autorizado para presentar la prueba.'; 
            }
        elseif($a['terminado'])
            {
                $pdo->prepare('UPDATE aspirantes SET intentos_post_finalizacion=intentos_post_finalizacion+1 WHERE id=?')->execute([$a['id']]);
                $st=$pdo->prepare('SELECT intentos_post_finalizacion FROM aspirantes WHERE id=?'); $st->execute([$a['id']]); $n=$st->fetchColumn();
                $msg='La prueba ya fue finalizada. Intentos de ingreso posteriores a la finalización: '.$n.'.';
            } 
        else 
            {
                $_SESSION['aspirante_id']=$a['id'];
                $int=current_attempt($pdo,$a['id']);
                if(!$int)
                    { 
                        $pdo->prepare('INSERT INTO intentos (aspirante_id, estado) VALUES (?, "preparacion")')->execute([$a['id']]); 
                    }
        redirect('preparacion.php');
            }
    }
?>

<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title><?=APP_NAME?></title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h1><?=APP_NAME?></h1>
                <p>Acceso para aspirantes autorizados.</p>
                <?php 
                    if($msg):
                ?>
                <div class="alert error">
                    <?=h($msg)?>
                </div>
                <?php 
                    endif;
                ?>
                <form method="post">
                    <label>Folio CENEVAL</label>
                    <input name="folio" required>
                    <label>Código de acceso</label>
                    <input name="codigo" required>
                    <button>Ingresar</button>
                </form>
                <p>
                    <a href="admin/login.php">Acceso administrativo</a>
                </p>
            </div>
        </div>
        <?php
            echo "PHP: " . date('Y-m-d H:i:s') . "<br>";

$st = $pdo->query("SELECT NOW() AS mysql_now");
$row = $st->fetch();

echo "MySQL: " . $row['mysql_now'];
exit;
        ?>
    </body>
</html>