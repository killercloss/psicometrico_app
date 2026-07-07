<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	csrf_check();

	$id = $_GET['id'] ?? 0; 

	if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_observaciones']))
	{
		$aspirante_id = (int)($_POST['aspirante_id'] ?? 0);
		$observaciones = trim($_POST['observaciones'] ?? '');

		$pdo->prepare('UPDATE aspirantes SET observaciones=? WHERE id=?')
			->execute([$observaciones, $aspirante_id]);

		redirect('reporte.php?id='.$id);
	}

	$st = $pdo->prepare('SELECT r.*, a.* FROM resultados r JOIN aspirantes a ON a.id=r.aspirante_id WHERE r.id=?');
	$st->execute([$id]);
	$r = $st->fetch(); 

	if(!$r) 
	{
		die('No encontrado');
	}
	
	$ds = $pdo->prepare('SELECT rd.*, d.nombre FROM resultados_dimension rd JOIN dimensiones d ON d.id = rd.dimension_id WHERE rd.resultado_id=? ORDER BY d.orden');
	$ds->execute([$id]);
	$dims = $ds->fetchAll();
	
	if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_entrevista_preguntas']))
	{
	    $aspirante_id = (int)$r['aspirante_id'];
	    $resultado_id = (int)$r['id'];

	    $pdo->prepare('DELETE FROM entrevista_preguntas_aplicadas WHERE resultado_id=?')
	        ->execute([$resultado_id]);

	    $preguntas = $_POST['pregunta_banco_id'] ?? [];
	    $respuestas = $_POST['respuesta_entrevista'] ?? [];

	    foreach($preguntas as $idx => $pregunta_banco_id)
	    {
	        $pregunta_banco_id = (int)$pregunta_banco_id;
	        if($pregunta_banco_id <= 0) continue;

	        $stp = $pdo->prepare('SELECT ebp.*, d.nombre AS dimension_nombre FROM entrevista_banco_preguntas ebp JOIN dimensiones d ON d.id=ebp.dimension_id WHERE ebp.id=? LIMIT 1');
	        $stp->execute([$pregunta_banco_id]);
	        $pb = $stp->fetch();

	        if(!$pb) continue;

	        $respuesta = trim($respuestas[$idx] ?? '');

	        $pdo->prepare('INSERT INTO entrevista_preguntas_aplicadas (aspirante_id, resultado_id, pregunta_banco_id, dimension_id, pregunta_texto, respuesta) VALUES (?,?,?,?,?,?)')
	            ->execute([$aspirante_id, $resultado_id, $pregunta_banco_id, $pb['dimension_id'], $pb['pregunta'], $respuesta]);
	    }

	    redirect('reporte.php?id='.$resultado_id);
	}

	$bancoEntrevista = $pdo->query('
	    SELECT ebp.*, d.nombre AS dimension_nombre, d.orden AS dimension_orden
	    FROM entrevista_banco_preguntas ebp
	    JOIN dimensiones d ON d.id = ebp.dimension_id
	    WHERE ebp.activa=1
	    ORDER BY d.orden ASC, ebp.orden ASC, ebp.id ASC
	')->fetchAll();

	$aplicadasSt = $pdo->prepare('SELECT * FROM entrevista_preguntas_aplicadas WHERE resultado_id=? ORDER BY id ASC');
	$aplicadasSt->execute([$id]);
	$preguntasAplicadas = $aplicadasSt->fetchAll();

	$prioridadDims = [];
	foreach($dims as $d){
	    if($d['nivel'] === 'Bajo' || $d['nivel'] === 'Medio'){
	        $prioridadDims[] = $d;
	    }
	}
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Reporte</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php include '_nav.php'; ?>

		<div class="container">
			<div class="card">
				<h1>Reporte individual</h1>
				<p>
					<b>Aspirante:</b> 
					<?=h($r['apellido_paterno'].' '.$r['apellido_materno'].' '.$r['nombres'])?>
					<br>

					<b>Folio:</b> 
					<?=h($r['folio_ceneval'])?>
					<br>

					<b>Correo:</b> 
					<?=h($r['correo'])?>
					<br>

					<b>Fecha de nacimiento:</b>
					<?=!empty($r['fecha_nacimiento']) ? h(date('d/m/Y', strtotime($r['fecha_nacimiento']))) : '—'?>
					<br>

					<b>Edad:</b>
					<?=calcular_edad($r['fecha_nacimiento'] ?? null)?>
					<br>

					<b>Programa:</b> 
					<?=h($r['maestria'])?>
					<br>

					<b>Fecha y hora de entrevista:</b>
					<?=!empty($r['entrevista_at']) ? h(date('d/m/Y H:i', strtotime($r['entrevista_at']))) : '—'?>
				</p>

				<div class="grid">
					<div class="stat">Puntaje total
						<br>
						<b><?=$r['puntaje_total']?> / 300</b>
					</div>

					<div class="stat">Nivel general
						<br>
						<b><?=h($r['nivel_general'])?></b>
					</div>

					<div class="stat">Tiempo total
						<br>
						<b><?=format_seconds($r['tiempo_total_segundos'])?></b>
					</div>

					<div class="stat">Intentos post finalización
						<br>
						<b><?=$r['intentos_post_finalizacion']?></b>
					</div>
				</div>

				<p>
					<b>Interpretación general:</b>
					<?=h($r['interpretacion_general'])?>
				</p>
			</div>

			<div class="card">
				<h2>Resultados por dimensión</h2>
				<table class="table">
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
			</div>

			<div class="card">
			    <h2>Preguntas de entrevista</h2>

			    <?php if(!empty($prioridadDims)): ?>
			        <div class="alert info">
			            <b>Dimensiones sugeridas para profundizar:</b>
			            <?php foreach($prioridadDims as $pd): ?>
			                <span class="badge <?=$pd['nivel']=='Medio'?'mid':'low'?>"><?=h($pd['nombre'])?>: <?=h($pd['nivel'])?></span>
			            <?php endforeach; ?>
			        </div>
			    <?php endif; ?>

			    <form method="post" id="formPreguntasEntrevista">
			    	<?=csrf_field()?>
			        <div id="preguntasEntrevistaWrap">
			            <?php if(!empty($preguntasAplicadas)): ?>
			                <?php foreach($preguntasAplicadas as $idx => $pa): ?>
			                    <div class="entrevista-item">
			                        <label>Pregunta <?=($idx+1)?></label>
			                        <select name="pregunta_banco_id[]" required>
			                            <option value="">-- Selecciona una pregunta --</option>
			                            <?php
			                                $dimActual = null;
			                                foreach($bancoEntrevista as $p):
			                                    if($dimActual !== $p['dimension_id']):
			                                        if($dimActual !== null) echo '</optgroup>';
			                                        $dimActual = $p['dimension_id'];
			                                        echo '<optgroup label="'.h($p['dimension_orden'].'. '.$p['dimension_nombre']).'">';
			                                    endif;
			                            ?>
			                                <option value="<?=$p['id']?>" <?=$pa['pregunta_banco_id']==$p['id']?'selected':''?>><?=h($p['orden'].'. '.$p['pregunta'])?></option>
			                            <?php endforeach; if($dimActual !== null) echo '</optgroup>'; ?>
			                        </select>

			                        <label>Respuesta / notas de entrevista</label>
			                        <textarea name="respuesta_entrevista[]" rows="3"><?=h($pa['respuesta'])?></textarea>
			                        <button type="button" class="btn danger quitar-pregunta">Quitar</button>
			                    </div>
			                <?php endforeach; ?>
			            <?php endif; ?>
			        </div>

			        <button type="button" class="btn secondary" id="agregarPreguntaEntrevista">+ Agregar pregunta</button>
			        <button type="submit" name="guardar_entrevista_preguntas" value="1">Guardar preguntas de entrevista</button>
			    </form>
			</div>

			<script>
			const bancoPreguntasHtml = `
			    <option value="">-- Selecciona una pregunta --</option>
			    <?php
			        $dimActual = null;
			        foreach($bancoEntrevista as $p):
			            if($dimActual !== $p['dimension_id']):
			                if($dimActual !== null) echo '</optgroup>';
			                $dimActual = $p['dimension_id'];
			                echo '<optgroup label="'.h($p['dimension_orden'].'. '.$p['dimension_nombre']).'">';
			            endif;
			    ?>
			        <option value="<?=$p['id']?>"><?=h($p['orden'].'. '.$p['pregunta'])?></option>
			    <?php endforeach; if($dimActual !== null) echo '</optgroup>'; ?>
			`;

			function renumerarPreguntasEntrevista(){
			    document.querySelectorAll('.entrevista-item').forEach((item, index) => {
			        const label = item.querySelector('label');
			        if(label) label.textContent = 'Pregunta ' + (index + 1);
			    });
			}

			function agregarPreguntaEntrevista(){
			    const wrap = document.getElementById('preguntasEntrevistaWrap');
			    const total = wrap.querySelectorAll('.entrevista-item').length;

			    if(total >= 5){
			        alert('Solo se pueden registrar hasta 5 preguntas de entrevista.');
			        return;
			    }

			    const div = document.createElement('div');
			    div.className = 'entrevista-item';
			    div.innerHTML = `
			        <label>Pregunta ${total + 1}</label>
			        <select name="pregunta_banco_id[]" required>${bancoPreguntasHtml}</select>
			        <label>Respuesta / notas de entrevista</label>
			        <textarea name="respuesta_entrevista[]" rows="3"></textarea>
			        <button type="button" class="btn danger quitar-pregunta">Quitar</button>
			    `;
			    wrap.appendChild(div);
			}

			document.getElementById('agregarPreguntaEntrevista').addEventListener('click', agregarPreguntaEntrevista);

			document.addEventListener('click', function(e){
			    if(e.target.classList.contains('quitar-pregunta')){
			        e.target.closest('.entrevista-item').remove();
			        renumerarPreguntasEntrevista();
			    }
			});

			if(document.querySelectorAll('.entrevista-item').length === 0){
			    agregarPreguntaEntrevista();
			}
			</script>
			
			<div class="card">
				<h2>Comentarios / Observaciones de entrevista</h2>

				<form method="post">
					<?=csrf_field()?>
					<input type="hidden" name="aspirante_id" value="<?=$r['aspirante_id']?>">

					<textarea name="observaciones" rows="7" placeholder="Escriba aquí observaciones del entrevistador...">
<?=h($r['observaciones'] ?? '')?></textarea>

					<br><br>

					<button type="submit" name="guardar_observaciones" value="1">
						Guardar observaciones
					</button>
				</form>
			</div>
			
			<a class="btn" href="imprimir_pdf.php?id=<?=$r['id']?>" target="_blank">
				Reporte resumido PDF
			</a>

			<a class="btn secondary" href="exportar_excel.php?id=<?=$r['id']?>">
				Descargar Excel
			</a>

			<a class="btn" href="imprimir_pdf_completo.php?id=<?=$r['resultado_id']?>" target="_blank">Reporte completo PDF</a>
		</div>
	</body>
</html>