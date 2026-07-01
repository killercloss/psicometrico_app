<?php
    session_start(); 
    require_once __DIR__.'/includes/db.php'; 
    require_once __DIR__.'/includes/functions.php';
    $msg='';
    
    if($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        csrf_check();
        $folio = trim($_POST['folio']??''); 
        $codigo = trim($_POST['codigo']??'');
        $st = $pdo->prepare('SELECT * FROM aspirantes WHERE folio_ceneval=? LIMIT 1');
        $st->execute([$folio]); 
        $a = $st->fetch();
        if(!$a)
            { 
                $msg='Folio o código incorrecto.'; 
            }
        elseif(!password_verify($codigo, $a['codigo_acceso']))
            {
                $msg = 'Folio o código incorrecto.';
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
        elseif(!empty($a['inicio_examen_at']))
            {
                $st = $pdo->prepare('SELECT NOW() >= ? AS disponible');
                $st->execute([$a['inicio_examen_at']]);
                $disponible = (int)$st->fetchColumn();

                if(!$disponible)
                {
                    $msg = 'La prueba estará disponible a partir de: '.date('d/m/Y H:i', strtotime($a['inicio_examen_at'])).' horas.';
                }
                else
                {
                    $_SESSION['aspirante_id']=$a['id'];
                    password_verify($codigo, $a['codigo_acceso']);
                    $int=current_attempt($pdo,$a['id']);

                    if(!$int)
                    { 
                        $pdo->prepare('INSERT INTO intentos (aspirante_id, estado) VALUES (?, "preparacion")')->execute([$a['id']]); 
                    }

                    redirect('preparacion.php');
                }
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
                <div class= "encabezado">
                    <img style="width: 20%;" src="resources/uanl.png">
                    <p class="bienvenida"> Departamento de Orientación Psicopedagógica<br><br>
                    Bienvenido(a) </p>
                    <img style="width: 20%;" src="resources/5 FCFM.png">
                </div>
                <h1><?=APP_NAME?></h1>
                <p>Acceso para aspirantes.</p>
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
                    <?=csrf_field()?>
                    <label>Folio CENEVAL</label>
                    <input name="folio" required>
                    <label>Código de acceso</label>
                    <input type="password" name="codigo" required>
                    <button>Ingresar</button>
                </form>
            </div>
        </div>
        
    </body>
</html>