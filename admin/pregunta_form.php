<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	csrf_check();

	$id = $_GET['id']??null; 
	$p = ['numero'=>'','dimension_id'=>1,'texto'=>'','inversa'=>0,'activa'=>1]; 

	if($id)
		{
			$st = $pdo->prepare('SELECT * FROM preguntas WHERE id=?');
			$st->execute([$id]);
			$p = $st->fetch();
		}

	$dims = $pdo->query('SELECT * FROM dimensiones ORDER BY orden')->fetchAll();
	
	if($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			$data = [(int)$_POST['numero'],(int)$_POST['dimension_id'],trim($_POST['texto']),isset($_POST['inversa'])?1:0,isset($_POST['activa'])?1:0]; 
			if($id)
				{
					$pdo->prepare('UPDATE preguntas SET numero=?,dimension_id=?,texto=?,inversa=?,activa=? WHERE id=?')->execute([...$data,$id]);
				} 
			else 
				{
					$pdo->prepare('INSERT INTO preguntas (numero,dimension_id,texto,inversa,activa) VALUES (?,?,?,?,?)')->execute($data);
				} 
			redirect('preguntas.php');
		}
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Pregunta</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php 
			include '_nav.php';
		?>
		<div class="container">
			<div class="card">
				<h1>Pregunta</h1>
				<form method="post">
					<?=csrf_field()?>
					<label>Número</label>
					<input type="number" name="numero" value="<?=h($p['numero'])?>" required>
					<label>Dimensión</label>
					<select name="dimension_id">
						<?php 
							foreach($dims as $d):
						?>
					<option value="<?=$d['id']?>" <?=$p['dimension_id']==$d['id']?'selected':''?>>
						<?=h($d['nombre'])?>
					</option>
					<?php 
						endforeach;
					?>
					</select>
					<label>Texto</label>
					<textarea name="texto" required><?=h($p['texto'])?></textarea>
					<label>
						<input type="checkbox" name="inversa" <?=$p['inversa']?'checked':''?>> Puntuación inversa
					</label>
					<label>
						<input type="checkbox" name="activa" <?=$p['activa']?'checked':''?>> Activa
					</label>
					<button>Guardar</button>
				</form>
			</div>
		</div>
	</body>
</html>