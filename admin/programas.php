<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	csrf_check();

	if(isset($_GET['del']))
		{ 
			$pdo->prepare('DELETE FROM programas WHERE id=?')->execute([$_GET['del']]); 
			redirect('programas.php'); 
		}

	$rows = $pdo->query('SELECT * FROM programas ORDER BY tipo DESC, nombre ASC')->fetchAll();
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Programas</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php 
			include '_nav.php';
		?>
		<div class= "encabezadoDash">
        <p class="dashTitle"> Panel de Control - Vista de Oferta Educativa
        	<br>Departamento de Orientación Psicopedagógica<br>
        Bienvenido(a) </p>
    </div>
		<div class="container">
			<h1>Maestrías y doctorados</h1>
			<a class="btn" href="programa_form.php">Nuevo programa</a>
			<table class="table">
				<tr>
					<th>Tipo</th>
					<th>Nombre</th>
					<th>Activo</th>
					<th></th>
				</tr>
				<?php 
					foreach($rows as $p):
				?>
				<tr>
					<td><?=h($p['tipo'])?></td>
					<td><?=h($p['nombre'])?></td>
					<td><?=$p['activo']?'Sí':'No'?></td>
					<td class="actions">
						<a class="btn secondary" href="programa_form.php?id=<?=$p['id']?>">Editar</a>
						<a class="btn danger" onclick="return confirm('¿Eliminar este programa?')" href="?del=<?=$p['id']?>">Eliminar</a>
					</td>
				</tr>
				<?php 
					endforeach;
				?>
			</table>
		</div>
	</body>
</html>