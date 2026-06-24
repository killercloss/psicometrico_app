<?php 
	
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();

	if(isset($_GET['del']))
		{ 
			$pdo->prepare('DELETE FROM aspirantes WHERE id=?')->execute([$_GET['del']]); redirect('aspirantes.php'); 
		}

	$rows = $pdo->query('SELECT * FROM aspirantes ORDER BY id DESC')->fetchAll();

?>

<!doctype html>
	<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Aspirantes</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php 
		include '_nav.php';
		?>

		<div class="container">
			<h1>Aspirantes</h1>
			<a class="btn" href="aspirante_form.php">Nuevo aspirante</a>
			<table class="table">
				<tr>
					<th>Folio</th>
					<th>Nombre</th>
					<th>Correo</th>
					<th>Programa</th>
					<th>Autorizado</th>
					<th>Terminado</th>
					<th>Intentos tras terminar</th>
					<th></th>
				</tr>
				
				<?php 
					foreach($rows as $a):
				?>
				
				<tr>
					<td><?=h($a['folio_ceneval'])?></td>
					<td><?=h($a['apellido_paterno'].' '.$a['apellido_materno'].' '.$a['nombres'])?></td>
					<td><?=h($a['correo'])?></td>
					<td><?=h($a['maestria'])?></td>
					<td><?=$a['autorizado']?'Sí':'No'?></td>
					<td><?=$a['terminado']?'Sí':'No'?></td>
					<td><?=$a['intentos_post_finalizacion']?></td>
					<td class="actions">
						<a class="btn secondary" href="aspirante_form.php?id=<?=$a['id']?>">Editar</a>
						<a class="btn danger" onclick="return confirm('¿Eliminar?')" href="?del=<?=$a['id']?>">Eliminar</a>
					</td>
				</tr>
				<?php 
					endforeach;
				?>
			</table>
		</div>
	</body>
</html>