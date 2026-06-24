<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();

	$id = $_GET['id']??0; 
	$st = $pdo->prepare('SELECT r.*, a.* FROM resultados r JOIN aspirantes a ON a.id=r.aspirante_id WHERE r.id=?');
	$st->execute([$id]);
	$r=$st->fetch(); 

	if(!$r) 
	{
		die('No encontrado');
	}
	
	$ds = $pdo->prepare('SELECT rd.*, d.nombre FROM resultados_dimension rd JOIN dimensiones d ON d.id = rd.dimension_id WHERE rd.resultado_id=? ORDER BY d.orden');
	$ds->execute([$id]);
	$dims=$ds->fetchAll();

	
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Reporte</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php 
			include '_nav.php';
		?>
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
					<b>Programa:</b> 
					<?=h($r['maestria'])?>
				</p>
				<div class="grid">
					<div class="stat">Puntaje total
						<br>
						<b>
							<?=$r['puntaje_total']?> / 300
						</b>
					</div>
					<div class="stat">Nivel general
						<br>
						<b>
							<?=h($r['nivel_general'])?>
						</b>
					</div>
					<div class="stat">Tiempo total
						<br>
						<b>
							<?=format_seconds($r['tiempo_total_segundos'])?>
						</b>
					</div>
					<div class="stat">Intentos post finalización
						<br>
						<b>
							<?=$r['intentos_post_finalizacion']?>
						</b>
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
						<th>Fundamentación</th>
					</tr>
					
					<?php 
						foreach($dims as $d):
					?>
					<tr>
						<td>
							<?=h($d['nombre'])?>
						</td>
						<td>
							<?=$d['puntaje']?> / 60
						</td>
						<td>
							<?=h($d['nivel'])?>
						</td>
						<td>
							<?=format_seconds($d['tiempo_segundos'])?>
						</td>
						<td>
							<?=h($d['interpretacion'])?>
						</td>
						<td>
							<?=h($d['fundamentacion'])?>
						</td>
					</tr>
					<?php 
					endforeach;
					?>
				</table>
			</div>
			<div class="card">
				<h2>Carta de resultados</h2>
				<div class="report">
					<?=h($r['carta_resultados'])?>
				</div>
			</div>
			
			<button>Descargar como PDF</button>
			<button onclick="location.href='imprimir_pdf.php';">Descargar como PDF</button>
			<button>Descargar como PDF</button>
			<a href="exportar_csv.php" class="btn">
    			Descargar como PDF
			</a>
		</div>
	</body>
</html>