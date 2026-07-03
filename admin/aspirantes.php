<?php 
	
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	csrf_check();

	/*if(isset($_GET['del']))
	{ 
		$pdo->prepare('DELETE FROM aspirantes WHERE id=?')->execute([$_GET['del']]); 
		redirect('aspirantes.php'); 
	}*/

	if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['del']))
	{
	    $pdo->prepare('DELETE FROM aspirantes WHERE id=?')->execute([(int)$_POST['del']]);
	    redirect('aspirantes.php');
	}

	if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_entrevista']))
	{
		$id = (int)($_POST['aspirante_id'] ?? 0);

		$entrevista_at = !empty($_POST['entrevista_at'])
			? str_replace('T', ' ', $_POST['entrevista_at']).':00'
			: null;

		$pdo->prepare('UPDATE aspirantes SET entrevista_at=? WHERE id=?')
			->execute([$entrevista_at, $id]);

		redirect('aspirantes.php');
	}

	$programas = $pdo->query('SELECT * FROM programas WHERE activo=1 ORDER BY tipo DESC, nombre ASC')->fetchAll();

	$where = [];
	$params = [];

	if (!empty($_GET['folio'])) {
		$where[] = 'folio_ceneval LIKE ?';
		$params[] = '%'.$_GET['folio'].'%';
	}

	if (!empty($_GET['nombre'])) {
		$where[] = "CONCAT(apellido_paterno,' ',apellido_materno,' ',nombres) LIKE ?";
		$params[] = '%'.$_GET['nombre'].'%';
	}

	if (!empty($_GET['correo'])) {
		$where[] = 'correo LIKE ?';
		$params[] = '%'.$_GET['correo'].'%';
	}

	if (!empty($_GET['maestria'])) {
		$where[] = 'maestria = ?';
		$params[] = $_GET['maestria'];
	}

	if (isset($_GET['autorizado']) && $_GET['autorizado'] !== '') {
		$where[] = 'autorizado = ?';
		$params[] = (int)$_GET['autorizado'];
	}

	if (isset($_GET['terminado']) && $_GET['terminado'] !== '') {
		$where[] = 'terminado = ?';
		$params[] = (int)$_GET['terminado'];
	}

	if (isset($_GET['intentos']) && $_GET['intentos'] !== '') {
		$where[] = 'intentos_post_finalizacion = ?';
		$params[] = (int)$_GET['intentos'];
	}

	$allowedSorts = [
		'folio' => 'folio_ceneval',
		'nombre' => 'apellido_paterno, apellido_materno, nombres',
		'correo' => 'correo',
		'edad' => 'fecha_nacimiento',
		'programa' => 'maestria',
		'autorizado' => 'autorizado',
		'terminado' => 'terminado',
		'fecha' => 'inicio_examen_at',
		'hora' => 'inicio_examen_at',
		'entrevista' => 'entrevista_at',
		'intentos' => 'intentos_post_finalizacion'
	];

	$sort = $_GET['sort'] ?? 'folio';
	$dir = strtolower($_GET['dir'] ?? 'asc');

	if (!array_key_exists($sort, $allowedSorts)) {
		$sort = 'folio';
	}

	if (!in_array($dir, ['asc', 'desc'])) {
		$dir = 'asc';
	}

	$sql = 'SELECT * FROM aspirantes';

	if ($where) {
		$sql .= ' WHERE '.implode(' AND ', $where);
	}

	$sql .= ' ORDER BY '.$allowedSorts[$sort].' '.strtoupper($dir);

	$st = $pdo->prepare($sql);
	$st->execute($params);
	$rows = $st->fetchAll();

	function sort_link($label, $field, $currentSort, $currentDir) {
		$params = $_GET;
		unset($params['del']);

		$isCurrent = $currentSort === $field;

		$ascParams = array_merge($params, ['sort' => $field, 'dir' => 'asc']);
		$descParams = array_merge($params, ['sort' => $field, 'dir' => 'desc']);

		$ascUrl = 'aspirantes.php?'.http_build_query($ascParams);
		$descUrl = 'aspirantes.php?'.http_build_query($descParams);

		$html = '<span class="th-sort">';
		$html .= '<span>'.h($label).'</span>';
		$html .= '<span class="sort-arrows">';

		if (!($isCurrent && $currentDir === 'asc')) {
			$html .= '<a title="Orden ascendente" href="'.h($ascUrl).'">▲</a>';
		}

		if (!($isCurrent && $currentDir === 'desc')) {
			$html .= '<a title="Orden descendente" href="'.h($descUrl).'">▼</a>';
		}

		$html .= '</span></span>';

		return $html;
	}
?>

<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>Aspirantes</title>
	<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

	<?php include '_nav.php'; ?>
	<div class= "encabezadoDash">
        <p class="dashTitle"> Panel de Control - Resumen de aspirantes
        	<br>Departamento de Orientación Psicopedagógica<br>
        Bienvenido(a) </p>
    </div>
	<div class="container">
		<h1>Aspirantes</h1>

		<div class="card">

			<form method="get" class="grid">
				<div>
					<label>Folio</label>
					<input name="folio" value="<?=h($_GET['folio']??'')?>">
				</div>

				<div>
					<label>Nombre</label>
					<input name="nombre" value="<?=h($_GET['nombre']??'')?>">
				</div>

				<div>
					<label>Correo</label>
					<input name="correo" value="<?=h($_GET['correo']??'')?>">
				</div>

				<div>
					<label>Programa</label>
					<select name="maestria">
						<option value="">Todos</option>
						<?php foreach($programas as $p): ?>
							<option value="<?=h($p['nombre'])?>" <?=($_GET['maestria']??'')===$p['nombre']?'selected':''?>>
								<?=h($p['tipo'].' - '.$p['nombre'])?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div>
					<label>Autorizado</label>
					<select name="autorizado">
						<option value="">Todos</option>
						<option value="1" <?=($_GET['autorizado']??'')==='1'?'selected':''?>>Sí</option>
						<option value="0" <?=($_GET['autorizado']??'')==='0'?'selected':''?>>No</option>
					</select>
				</div>

				<div>
					<label>Terminado</label>
					<select name="terminado">
						<option value="">Todos</option>
						<option value="1" <?=($_GET['terminado']??'')==='1'?'selected':''?>>Sí</option>
						<option value="0" <?=($_GET['terminado']??'')==='0'?'selected':''?>>No</option>
					</select>
				</div>

				<div>
					<label>Intentos tras terminar</label>
					<input type="number" min="0" name="intentos" value="<?=h($_GET['intentos']??'')?>">
				</div>

				<div>
					<label>&nbsp;</label>
					<button type="submit">Filtrar</button>
				</div>

				<div>
					<label>&nbsp;</label>
					<a class="btn secondary" href="aspirantes.php">Limpiar filtros</a>
				</div>

				<div>
					<label>&nbsp;</label>
					<a class="btn" href="aspirante_form.php">Nuevo aspirante</a>
				</div>
			</form>
		</div>

		<div class="table-wrapper">
			<table class="table">
				<tr>
					<th><?=sort_link('Folio', 'folio', $sort, $dir)?></th>
					<th><?=sort_link('Nombre', 'nombre', $sort, $dir)?></th>
					<th><?=sort_link('Correo', 'correo', $sort, $dir)?></th>
					<th><?=sort_link('Edad', 'edad', $sort, $dir)?></th>
					<th><?=sort_link('Programa', 'programa', $sort, $dir)?></th>
					<th><?=sort_link('Acceso', 'autorizado', $sort, $dir)?></th>
					<th><?=sort_link('Examen acabado', 'terminado', $sort, $dir)?></th>
					<th><?=sort_link('Fecha inicio', 'fecha', $sort, $dir)?></th>
					<th><?=sort_link('Hora inicio', 'hora', $sort, $dir)?></th>
					<th><?=sort_link('Entrevista', 'entrevista', $sort, $dir)?></th>
					<th><?=sort_link('Intentos tras terminar', 'intentos', $sort, $dir)?></th>
					<th>Correo examen</th>
					<th>Correo entrevista</th>
					<th>Acciones</th>
				</tr>
				
				<?php foreach($rows as $a): ?>
					<tr>
						<td><?=h($a['folio_ceneval'])?></td>
						<td><?=h($a['apellido_paterno'].' '.$a['apellido_materno'].' '.$a['nombres'])?></td>
						<td><?=h($a['correo'])?></td>
						<td><?=calcular_edad($a['fecha_nacimiento'] ?? null)?></td>
						<td><?=h($a['maestria'])?></td>
						<td><?=$a['autorizado']?'Sí':'No'?></td>
						<td><?=$a['terminado']?'Sí':'No'?></td>
						<td>
							<?=!empty($a['inicio_examen_at']) ? h(date('d/m/Y', strtotime($a['inicio_examen_at']))) : '—'?>
						</td>
						<td>
							<?=!empty($a['inicio_examen_at']) ? h(date('H:i', strtotime($a['inicio_examen_at']))) : '—'?>
						</td>
						<td>
							<form method="post" class="inline-form">
								<?=csrf_field()?>
								<input type="hidden" name="aspirante_id" value="<?=$a['id']?>">
								<input 
									type="datetime-local" 
									name="entrevista_at"
									value="<?=!empty($a['entrevista_at']) ? h(date('Y-m-d\TH:i', strtotime($a['entrevista_at']))) : ''?>">
								<button type="submit" name="guardar_entrevista" value="1">Guardar</button>
							</form>
						</td>
						<td><?=$a['intentos_post_finalizacion']?></td>
						<td>
						    <?=!empty($a['examen_correo_enviado_at']) ? h(date('d/m/Y H:i', strtotime($a['examen_correo_enviado_at']))) : '—'?>
						</td>

						<td>
						    <?=!empty($a['entrevista_correo_enviado_at']) ? h(date('d/m/Y H:i', strtotime($a['entrevista_correo_enviado_at']))) : '—'?>
						</td>
						<td class="actions">
							<a class="btn secondary" href="aspirante_form.php?id=<?=$a['id']?>">Editar</a>
							<!--<a class="btn danger" onclick="return confirm('¿Eliminar?')" href="aspirantes.php?del=<?=$a['id']?>">Eliminar</a>-->
							<form method="post" style="display:inline" onsubmit="return confirm('¿Eliminar?')">
								<?=csrf_field()?>
							    <input type="hidden" name="del" value="<?=$a['id']?>">
							    <button class="btn danger">Eliminar</button>
							</form>
							<br>
							<?php if(empty($a['examen_correo_enviado_at'])): ?>
							    <a class="btn secondary"
							       href="correo.php?id=<?=$a['id']?>&tipo=examen">
							        ✉ examen
							    </a>
							<?php else: ?>
							    <button class="btn secondary"
							            onclick="alert('Correo de examen ya fue preparado/enviado. Revisar tabla.')">
							        ✉ examen
							    </button>
							<?php endif; ?>

							<?php if(empty($a['entrevista_correo_enviado_at'])): ?>
							    <a class="btn secondary"
							       href="correo.php?id=<?=$a['id']?>&tipo=entrevista">
							        ✉ entrevista
							    </a>
							<?php else: ?>
							    <button class="btn secondary"
							            onclick="alert('Correo de entrevista ya fue preparado/enviado. Revisar tabla.')">
							        ✉ entrevista
							    </button>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>

				<?php if(empty($rows)): ?>
					<tr>
						<td colspan="12">No se encontraron aspirantes con esos filtros.</td>
					</tr>
				<?php endif; ?>
			</table>
		</div>
	</div>

</body>
</html>
