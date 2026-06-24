<?php
    session_start(); 
    require_once __DIR__.'/includes/db.php'; 
    require_once __DIR__.'/includes/functions.php'; 
    require_candidate();

    $aspirante_id = $_SESSION['aspirante_id']; 
    $int = current_attempt($pdo,$aspirante_id); 
    if(!$int) 
        {
            redirect('index.php');
        }

    if($int['estado']==='finalizado') 
        {
            redirect('resultado.php');
        }

    $pdo->beginTransaction();
    // Asegura respuestas 0 para preguntas no enviadas de dimensiones ya abiertas o vencidas.
    $dims = $pdo->query('SELECT * FROM dimensiones ORDER BY orden')->fetchAll();
    foreach($dims as $d)
    {
        $td = $pdo->prepare('SELECT * FROM tiempos_dimension WHERE intento_id=? AND dimension_id=?'); 
        $td->execute([$int['id'],$d['id']]); 
        $time = $td->fetch();

        if(!$time) 
            {
                continue;
            }

        if(!$time['fin_at']) 
            {
                $pdo->prepare('UPDATE tiempos_dimension SET fin_at=NOW(), agotado=1 WHERE intento_id=? AND dimension_id=?')->execute([$int['id'],$d['id']]);
            }
        $qs = $pdo->prepare('SELECT * FROM preguntas WHERE dimension_id=? AND activa=1'); 
        $qs->execute([$d['id']]);
        foreach($qs->fetchAll() as $q)
        {
            $pdo->prepare('INSERT IGNORE INTO respuestas (intento_id,aspirante_id,pregunta_id,valor_original,valor_puntuado) VALUES (?,?,?,?,?)')->execute([$int['id'],$aspirante_id,$q['id'],0,0]);
        }
    }
    // Calcular resultados
    $st=$pdo->prepare('SELECT SUM(valor_puntuado) FROM respuestas WHERE intento_id=?'); 
    $st->execute([$int['id']]); 
    $total=(int)$st->fetchColumn();

    $nivel=level_general($total); 
    $gen=general_interpretation($nivel);

    $tt=$pdo->prepare('SELECT SUM(TIMESTAMPDIFF(SECOND,inicio_at,COALESCE(fin_at,NOW()))) FROM tiempos_dimension WHERE intento_id=?'); 
    $tt->execute([$int['id']]); 
    $total_time=(int)$tt->fetchColumn();

    $carta="Resultado general: $nivel\nPuntaje total: $total / 300\nInterpretación: $gen\n\nResultados por dimensión:\n";
    $pdo->prepare('INSERT INTO resultados (intento_id,aspirante_id,puntaje_total,nivel_general,interpretacion_general,tiempo_total_segundos,carta_resultados) VALUES (?,?,?,?,?,?,?)')->execute([$int['id'],$aspirante_id,$total,$nivel,$gen,$total_time,'']);
    $resultado_id = $pdo->lastInsertId();
    foreach($dims as $d)
    {
        $s=$pdo->prepare('SELECT SUM(r.valor_puntuado) FROM respuestas r JOIN preguntas p ON p.id=r.pregunta_id WHERE r.intento_id=? AND p.dimension_id=?'); 
        $s->execute([$int['id'],$d['id']]); 
        $score=(int)$s->fetchColumn();
        $lev=level_dimension($score);
        $ii=$pdo->prepare('SELECT * FROM interpretaciones_dimension WHERE dimension_id=? AND nivel=? LIMIT 1'); 
        $ii->execute([$d['id'],$lev]); 
        $interp=$ii->fetch();
        $ts=$pdo->prepare('SELECT TIMESTAMPDIFF(SECOND,inicio_at,COALESCE(fin_at,NOW())) FROM tiempos_dimension WHERE intento_id=? AND dimension_id=?'); 
        $ts->execute([$int['id'],$d['id']]); 
        $secs=(int)$ts->fetchColumn();
        $pdo->prepare('INSERT INTO resultados_dimension (resultado_id,dimension_id,puntaje,nivel,interpretacion,fundamentacion,tiempo_segundos) VALUES (?,?,?,?,?,?,?)')->execute([$resultado_id,$d['id'],$score,$lev,$interp['interpretacion']??'', $interp['fundamentacion']??'', $secs]);
        $carta.="\n".$d['nombre'].": $score / 60 ($lev)\n".($interp['interpretacion']??'')."\n";
    }
    $pdo->prepare('UPDATE resultados SET carta_resultados=? WHERE id=?')->execute([$carta,$resultado_id]);
    $pdo->prepare('UPDATE intentos SET estado="finalizado", finalizado_at=NOW() WHERE id=?')->execute([$int['id']]);
    $pdo->prepare('UPDATE aspirantes SET terminado=1 WHERE id=?')->execute([$aspirante_id]);
    $pdo->commit();
    redirect('resultado.php');
?>
