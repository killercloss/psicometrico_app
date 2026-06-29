<?php 
	session_start(); 
	require_once __DIR__.'/includes/db.php'; 
	require_once __DIR__.'/includes/functions.php'; 
	require_candidate();

	$st = $pdo->prepare('SELECT r.* FROM resultados r WHERE aspirante_id=? ORDER BY id DESC LIMIT 1'); $st->execute([$_SESSION['aspirante_id']]); $r=$st->fetch();
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Resultado</title>
		<link rel="stylesheet" href="assets/style.css">
	</head>
	<body>
		<div class="container">
			<div class="card">
				<h1>Prueba finalizada</h1>
				<p>Gracias por completar la evaluación. Sus respuestas han sido registradas.</p>
				
				<a class="btn" href="logout.php">Salir</a>
			</div>
		</div>
	</body>
</html>