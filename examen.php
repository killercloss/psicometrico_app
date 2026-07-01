<?php
    session_start(); 
    require_once __DIR__.'/includes/db.php'; 
    require_once __DIR__.'/includes/functions.php'; 
    require_candidate();

    $aspirante_id=$_SESSION['aspirante_id'];
    $int=current_attempt($pdo,$aspirante_id); 
    if(!$int) 
    {
        redirect('index.php');
    }

    if($int['estado']==='finalizado')
        {
            redirect('resultado.php');
        }

    if($int['estado']==='preparacion') 
        {
            $pdo->prepare('UPDATE intentos SET iniciado_at=NOW(), estado="en_progreso" WHERE id=?')->execute([$int['id']]);
        }

    $int = current_attempt($pdo,$aspirante_id);
    $dim_id = (int)$int['dimension_actual'];
    $dim = $pdo->prepare('SELECT * FROM dimensiones WHERE id=?'); 
    $dim->execute([$dim_id]); 
    $dimension=$dim->fetch();
    $opciones = array("Totalmente en desacuerdo", "En desacuerdo", "Neutral", "De acuerdo", "Totalmente de acuerdo");
    $contador = 0;
    
    if(!$dimension)
        {
            redirect('finalizar.php');
        }
    
    $td = $pdo->prepare('SELECT * FROM tiempos_dimension WHERE intento_id=? AND dimension_id=?'); 
    $td->execute([$int['id'],$dim_id]); 
    $time=$td->fetch();

    if(!$time)
    {
        $lim=((int)$dimension['tiempo_minutos'])*60;
        $pdo->prepare('INSERT INTO tiempos_dimension (intento_id,dimension_id,inicio_at,limite_segundos) VALUES (?,?,NOW(),?)')->execute([$int['id'],$dim_id,$lim]);
        $td->execute([$int['id'],$dim_id]); 
        $time = $td->fetch();
    }
    
    /*$start=strtotime($time['inicio_at']); 
    $limit=(int)$time['limite_segundos']; 
    $elapsed=time()-$start; 
    $remaining=max(0,$limit-$elapsed);*/

    $td = $pdo->prepare("
        SELECT *,
               TIMESTAMPDIFF(SECOND, inicio_at, NOW()) AS elapsed
        FROM tiempos_dimension
        WHERE intento_id=? AND dimension_id=?
    ");
    $td->execute([$int['id'], $dim_id]);
    $time = $td->fetch();

    $limit = (int)$time['limite_segundos'];
    $elapsed = (int)$time['elapsed'];
    $remaining = max(0, $limit - $elapsed);

    if($_SERVER['REQUEST_METHOD']==='POST')
    {
        $td = $pdo->prepare('SELECT * FROM tiempos_dimension WHERE intento_id=? AND dimension_id=?'); 
        $td->execute([$int['id'],$dim_id]); 
        $time=$td->fetch();
        //$remaining=max(0,(int)$time['limite_segundos']-(time()-strtotime($time['inicio_at'])));
        $td = $pdo->prepare("
            SELECT *,
                   TIMESTAMPDIFF(SECOND, inicio_at, NOW()) AS elapsed
            FROM tiempos_dimension
            WHERE intento_id=? AND dimension_id=?
        ");
        $td->execute([$int['id'], $dim_id]);
        $time = $td->fetch();

        $remaining = max(0, (int)$time['limite_segundos'] - (int)$time['elapsed']);
        
        $questions=$pdo->prepare('SELECT * FROM preguntas WHERE dimension_id=? AND activa=1 ORDER BY numero'); 
        $questions->execute([$dim_id]); 
        $qs=$questions->fetchAll();
        $pdo->beginTransaction();

        foreach($qs as $q)
        {
            $raw = ($remaining>0 && isset($_POST['q_'.$q['id']])) ? (int)$_POST['q_'.$q['id']] : 0;
            $score = $raw>0 ? score_answer($raw,(int)$q['inversa']) : 0;
            $pdo->prepare('INSERT INTO respuestas (intento_id,aspirante_id,pregunta_id,valor_original,valor_puntuado)   VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE valor_original=VALUES(valor_original), valor_puntuado=VALUES(valor_puntuado)')->execute([$int['id'],$aspirante_id,$q['id'],$raw,$score]);
        }

        $agotado = $remaining<=0?1:0;
        $pdo->prepare('UPDATE tiempos_dimension SET fin_at=NOW(), agotado=? WHERE intento_id=? AND dimension_id=?')->execute([$agotado,$int['id'],$dim_id]);
        $next = $dim_id+1;
        if($next>5)
            { 
                $pdo->prepare('UPDATE intentos SET dimension_actual=?, estado="en_progreso" WHERE id=?')->execute([$next,$int['id']]); 
                $pdo->commit(); 
                redirect('finalizar.php'); 
            }
        else 
            { 
                $pdo->prepare('UPDATE intentos SET dimension_actual=? WHERE id=?')->execute([$next,$int['id']]); 
                $pdo->commit(); 
                redirect('examen.php'); 
            }
    }

    $questions = $pdo->prepare('SELECT * FROM preguntas WHERE dimension_id=? AND activa=1 ORDER BY numero'); 
    $questions->execute([$dim_id]); 
    $qs = $questions->fetchAll();

    $st = $pdo->prepare('SELECT * FROM aspirantes WHERE id = ?');
    $st->execute([$aspirante_id]);
    $datos = $st->fetch(); 

    if(!$datos) 
    {
        die('No encontrado');
    }
?>

<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Examen</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <div class="alert warn">Tiempo restante: 
                    <span class="timer" id="timer"><?=format_seconds($remaining)?></span>
                </div>
        <div class="container">

            <div class="card">

                <div class= "encabezado">
                    <img style="width: 20%;" src="resources/uanl.png">
                    <p class="bienvenida">
                        Departamento de Orientación Psicopedagógica<br>
                        <?=h($datos['maestria'])?><br>
                        Test Psicométrico Posgrado - <?=h($datos['nombres'])?>
                    </p>
                    <img style="width: 20%;" src="resources/5 FCFM.png">
                </div>
                <h1>Dimensión <?=h($dim_id)?>: <?=h($dimension['nombre'])?></h1>
                <p class="muted"><?=h($dimension['descripcion'])?></p>
                
                <form method="post" id="examForm"><?php foreach($qs as $q):?>
                    <?=csrf_field()?>
                    <div class="question">
                        <b><?=h($q['numero'])?>. <?=h($q['texto'])?></b>
                        <div class="likert"><?php for($i=1;$i<=5;$i++):?>
                        <label class="opcion">
                            <input class="input" type="radio" name="q_<?=$q['id']?>" value="<?=$i?>" required> 
                            <span class="numero"> <?=$i?> </span>
                            <div class="texto"><?php echo $opciones[$contador]; $contador++;?></div>
                        </label>

                        <?php 
                            endfor;
                            $contador = 0;
                        ?>
                    </div>
                    
                    </div>
                <?php 
                    endforeach;
                ?>
                <button>Enviar dimensión y continuar</button>
            </form>
            </div>
        </div>
        <script>
            let remaining=<?=$remaining?>; 
            const timer=document.getElementById('timer'); 
            const form=document.getElementById('examForm');
    
            function fmt(t)
            {
                t = Math.max(0,t); 
                return String(Math.floor(t/60)).padStart(2,'0')+':'+String(t%60).padStart(2,'0')
            }
    
            const iv = setInterval(()=>
                {
                    remaining--; 
                    timer.textContent=fmt(remaining); 
                    if(remaining<=0)
                        {
                           clearInterval(iv); 
                           document.querySelectorAll('input[required]').forEach(i=>i.required=false); 
                            form.submit();
                     }
                },1000);
            history.pushState(null,null,location.href); 
            window.onpopstate=function()
            {
                history.go(1)
            };
        </script>
    </body>
</html>
