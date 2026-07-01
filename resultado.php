<?php 
	session_start(); 
	require_once __DIR__.'/includes/db.php'; 
	require_once __DIR__.'/includes/functions.php'; 
	require_candidate();

	$aspirante_id = $_SESSION['aspirante_id'];

	$st = $pdo->prepare('SELECT r.* FROM resultados r WHERE aspirante_id=? ORDER BY id DESC LIMIT 1'); $st->execute([$_SESSION['aspirante_id']]); $r=$st->fetch();

	$st = $pdo->prepare('SELECT * FROM aspirantes WHERE id = ?');
    $st->execute([$aspirante_id]);
    $datos = $st->fetch(); 

    if(!$datos) 
    {
        die('No encontrado');
    }
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
				<div class= "encabezado">
                    <img style="width: 20%;" src="resources/uanl.png">
                    <p class="bienvenida">
                        Departamento de Orientación Psicopedagógica<br>
                        <?=h($datos['maestria'])?><br>
                        Fin de la prueba
                    </p>
                    <img style="width: 20%;" src="resources/5 FCFM.png">
                </div>
				<h1>Prueba finalizada</h1>
				<p>Gracias por completar la evaluación <?=h($datos['nombres'])?>. Sus respuestas han sido registradas.</p>
				
				<a class="btn" href="logout.php">Salir</a>
			</div>
		</div>
	</body>
</html>