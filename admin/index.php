<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	
	$tot = $pdo->query('SELECT COUNT(*) FROM aspirantes')->fetchColumn(); 
	$fin = $pdo->query('SELECT COUNT(*) FROM aspirantes WHERE terminado=1')->fetchColumn(); 
	$auth = $pdo->query('SELECT COUNT(*) FROM aspirantes WHERE autorizado=1')->fetchColumn(); 
	$r = $pdo->query('SELECT COUNT(*) FROM resultados')->fetchColumn();
	$maes = $pdo->query('SELECT maestria, COUNT(*) total, SUM(terminado=1) terminados FROM aspirantes GROUP BY maestria ORDER BY maestria')->fetchAll();
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Dashboard</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php 
			include '_nav.php';
		?>
		<div class="container">
			<h1>Dashboard</h1>
			<div class="grid">
				<div class="stat">Aspirantes
					<br>
					<b><?=$tot?></b>
				</div>
				<div class="stat">Autorizados
					<br>
					<b><?=$auth?></b>
				</div>
				<div class="stat">Finalizados
					<br>
					<b><?=$fin?></b>
				</div>
				<div class="stat">Resultados
					<br>
					<b><?=$r?></b>
				</div>
			</div>
			<div class="card">
				<h2>Avance por programa</h2>
				<table class="table">
					<tr>
						<th>Programa</th>
						<th>Aspirantes</th>
						<th>Finalizados</th>
					</tr>
					<?php 
						foreach($maes as $m):
					?>
					<tr>
						<td><?=h($m['maestria'])?></td>
						<td><?=$m['total']?></td>
						<td><?=$m['terminados']?></td>
					</tr>
					<?php 
						endforeach;
					?>
				</table>
			</div>
		</div>
	</body>
</html>