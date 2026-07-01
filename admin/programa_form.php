<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	csrf_check();

	$id = $_GET['id']??null; 
	$p=['nombre'=>'','tipo'=>'Maestría','activo'=>1];

	if($id)
		{ 
			$st = $pdo->prepare('SELECT * FROM programas WHERE id=?');
			$st->execute([$id]);
			$p = $st->fetch();
		}

	if($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			$data = [trim($_POST['nombre']),$_POST['tipo'],isset($_POST['activo'])?1:0];
			
			if($id)
				{
					$pdo->prepare('UPDATE programas SET nombre=?,tipo=?,activo=? WHERE id=?')->execute([...$data,$id]);
				}
			else 
				{
					$pdo->prepare('INSERT INTO programas (nombre,tipo,activo) VALUES (?,?,?)')->execute($data);
				} 
			redirect('programas.php');
		}
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Programa</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php 
			include '_nav.php';
		?>
		<div class="container">
			<div class="card">
				<h1><?= $id?'Editar':'Nuevo' ?> programa</h1>
				<form method="post">
					<?=csrf_field()?>
					<label>Tipo</label>
					<select name="tipo" required>
						<option value="Maestría" <?=$p['tipo']==='Maestría'?'selected':''?>>Maestría</option>
						<option value="Doctorado" <?=$p['tipo']==='Doctorado'?'selected':''?>>Doctorado</option>
					</select>
					<label>Nombre del programa</label>
					<input name="nombre" value="<?=h($p['nombre'])?>" required>
					<label>
						<input type="checkbox" name="activo" <?=$p['activo']?'checked':''?>> Activo
					</label>
					<button>Guardar</button>
				</form>
			</div>
		</div>
	</body>
</html>