<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();

	$id = $_GET['id']??null; 
	$a = [
		'folio_ceneval'=>'',
		'codigo_acceso'=>'',
		'apellido_paterno'=>'',
		'apellido_materno'=>'',
		'nombres'=>'',
		'correo'=>'',
		'maestria'=>'',
		'inicio_examen_at'=>'',
		'autorizado'=>1
	];

	$programas = $pdo->query('SELECT * FROM programas WHERE activo=1 ORDER BY tipo DESC, nombre ASC')->fetchAll();

	if($id)
	{
		$st = $pdo->prepare('SELECT * FROM aspirantes WHERE id=?');
		$st->execute([$id]);
		$a = $st->fetch();
	}

	if($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		$programa = trim($_POST['maestria']);
		$inicio_examen_at = !empty($_POST['inicio_examen_at'])
			? str_replace('T', ' ', $_POST['inicio_examen_at']).':00'
			: null;

		$data = [
			trim($_POST['folio_ceneval']),
			trim($_POST['codigo_acceso']),
			trim($_POST['apellido_paterno']),
			trim($_POST['apellido_materno']),
			trim($_POST['nombres']),
			trim($_POST['correo']),
			$programa,
			$inicio_examen_at,
			isset($_POST['autorizado']) ? 1 : 0
		];

		if($id)
		{
			$pdo->prepare('
				UPDATE aspirantes 
				SET folio_ceneval=?,
					codigo_acceso=?,
					apellido_paterno=?,
					apellido_materno=?,
					nombres=?,
					correo=?,
					maestria=?,
					inicio_examen_at=?,
					autorizado=? 
				WHERE id=?
			')->execute([...$data,$id]);
		}
		else 
		{
			$pdo->prepare('
				INSERT INTO aspirantes 
				(folio_ceneval,codigo_acceso,apellido_paterno,apellido_materno,nombres,correo,maestria,inicio_examen_at,autorizado) 
				VALUES (?,?,?,?,?,?,?,?,?)
			')->execute($data);
		} 

		redirect('aspirantes.php');
	}

	$inicio_value = '';
	if(!empty($a['inicio_examen_at'])) {
		$inicio_value = date('Y-m-d\TH:i', strtotime($a['inicio_examen_at']));
	}
?>

<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>Aspirante</title>
	<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
	<?php include '_nav.php'; ?>

	<div class="container">
		<div class="card">
			<h1><?= $id?'Editar':'Nuevo' ?> aspirante</h1>

			<form method="post">
				<label>Folio CENEVAL</label>
				<input name="folio_ceneval" value="<?=h($a['folio_ceneval'])?>" required>

				<label>Código de acceso</label>
				<input name="codigo_acceso" value="<?=h($a['codigo_acceso'])?>" required>

				<label>Apellido paterno</label>
				<input name="apellido_paterno" value="<?=h($a['apellido_paterno'])?>" required>

				<label>Apellido materno</label>
				<input name="apellido_materno" value="<?=h($a['apellido_materno'])?>">

				<label>Nombre(s)</label>
				<input name="nombres" value="<?=h($a['nombres'])?>" required>

				<label>Correo</label>
				<input name="correo" type="email" value="<?=h($a['correo'])?>">

				<label>Programa al que intenta ingresar</label>
				<select name="maestria" required>
					<option value="">Seleccione una maestría o doctorado</option>
					<?php foreach($programas as $p): ?>
						<option value="<?=h($p['nombre'])?>" <?=$a['maestria']===$p['nombre']?'selected':''?>>
							<?=h($p['tipo'].' - '.$p['nombre'])?>
						</option>
					<?php endforeach; ?>
				</select>

				<label>Fecha y hora de inicio del examen</label>
				<input type="datetime-local" name="inicio_examen_at" value="<?=h($inicio_value)?>">

				<label>
					<input type="checkbox" name="autorizado" <?=$a['autorizado']?'checked':''?>> Autorizado
				</label>

				<button>Guardar</button>
			</form>
		</div>
	</div>
</body>
</html>