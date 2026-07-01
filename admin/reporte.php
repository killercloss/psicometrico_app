<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();

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
				<h2>Comentarios / Observaciones de entrevista</h2>

				<form method="post">
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
				Descargar como PDF
			</a>

			<a class="btn secondary" href="exportar_excel.php?id=<?=$r['id']?>">
				Descargar Excel
			</a>
		</div>
	</body>
</html>