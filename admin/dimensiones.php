<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	csrf_check();

	if(isset($_GET['del']))
		{ 
			$pdo->prepare('DELETE FROM dimensiones WHERE id=?')->execute([$_GET['del']]); redirect('dimensiones.php'); 
		}

	$rows = $pdo->query('SELECT * FROM dimensiones ORDER BY orden')->fetchAll(); 
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Dimensiones</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php 
			include '_nav.php';
		?>
		<div class="container">
			<h1>Dimensiones</h1>
			<a class="btn" href="dimension_form.php">Nueva dimensión</a>
			<table class="table">
				<tr>
					<th>Orden</th>
					<th>Nombre</th>
					<th>Tiempo</th>
					<th>Descripción</th>
					<th></th>
				</tr>
				<?php 
					foreach($rows as $d):
				?>
				<tr>
					<td><?=$d['orden']?></td>
					<td><?=h($d['nombre'])?></td>
					<td><?=$d['tiempo_minutos']?> min</td>
					<td><?=h($d['descripcion'])?></td>
					<td>
						<a class="btn secondary" href="dimension_form.php?id=<?=$d['id']?>">Editar</a>
						<a class="btn danger" onclick="return confirm('¿Eliminar dimensión y sus preguntas?')" href="?del=<?=$d['id']?>">Eliminar</a>
					</td>
				</tr>
				<?php 
					endforeach;
				?>
			</table>
		</div>
	</body>
</html>