<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	csrf_check();

	$id = $_GET['id']??null; 
	$d = ['nombre'=>'','descripcion'=>'','tiempo_minutos'=>5,'orden'=>1]; 

	if($id)
		{
			$st = $pdo->prepare('SELECT * FROM dimensiones WHERE id=?');
			$st->execute([$id]);
			$d = $st->fetch();
		}

	if($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			$data=[trim($_POST['nombre']),trim($_POST['descripcion']),(int)$_POST['tiempo_minutos'],(int)$_POST['orden']]; 

			if($id)
				{
					$pdo->prepare('UPDATE dimensiones SET nombre=?,descripcion=?,tiempo_minutos=?,orden=? WHERE id=?')->execute([...$data,$id]);
				} 
			else 
				{
					$pdo->prepare('INSERT INTO dimensiones (nombre,descripcion,tiempo_minutos,orden) VALUES (?,?,?,?)')->execute($data);
				} 
			redirect('dimensiones.php');
		}
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Dimensión</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php 
			include '_nav.php';
		?>
		<div class="container">
			<div class="card">
				<h1>Dimensión</h1>
				<form method="post">
					<?=csrf_field()?>
					<label>Nombre</label>
					<input name="nombre" value="<?=h($d['nombre'])?>" required>
					<label>Descripción</label>
					<textarea name="descripcion"><?=h($d['descripcion'])?></textarea>
					<label>Tiempo en minutos</label>
					<input type="number" name="tiempo_minutos" value="<?=h($d['tiempo_minutos'])?>">
					<label>Orden</label>
					<input type="number" name="orden" value="<?=h($d['orden'])?>">
					<button>Guardar</button>
				</form>
			</div>
		</div>
	</body>
</html>