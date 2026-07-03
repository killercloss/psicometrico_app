<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	csrf_check();

	if(isset($_GET['del']))
		{ 
			$pdo->prepare('DELETE FROM preguntas WHERE id=?')->execute([$_GET['del']]); 
			redirect('preguntas.php'); 
		}

	$rows = $pdo->query('SELECT p.*,d.nombre dimension FROM preguntas p JOIN dimensiones d ON d.id=p.dimension_id ORDER BY p.numero')->fetchAll(); 
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Preguntas</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php 
			include '_nav.php';
		?>
		<div class= "encabezadoDash">
        <p class="dashTitle"> Panel de Control - Creación y edición de preguntas
        	<br>Departamento de Orientación Psicopedagógica<br>
        Bienvenido(a) </p>
    </div>
		<div class="container">
			<h1>Preguntas</h1>
			<a class="btn" href="pregunta_form.php">Nueva pregunta</a>
			<table class="table">
				<tr>
					<th>No.</th>
					<th>Dimensión</th>
					<th>Texto</th>
					<th>Inversa</th>
					<th>Activa</th>
					<th></th>
				</tr>
				<?php 
					foreach($rows as $p):
				?>
				<tr>
					<td><?=$p['numero']?></td>
					<td><?=h($p['dimension'])?></td>
					<td><?=h($p['texto'])?></td>
					<td><?=$p['inversa']?'Sí':'No'?></td>
					<td><?=$p['activa']?'Sí':'No'?></td>
					<td>
						<a class="btn secondary" href="pregunta_form.php?id=<?=$p['id']?>">Editar</a>
						<a class="btn danger" onclick="return confirm('¿Eliminar?')" href="?del=<?=$p['id']?>">Eliminar</a>
					</td>
				</tr>
				<?php 
					endforeach;
				?>
			</table>
		</div>
	</body>
</html>