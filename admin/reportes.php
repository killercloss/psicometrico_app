<?php 

	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	csrf_check();

	$programas = $pdo->query('SELECT * FROM programas WHERE activo=1 ORDER BY tipo DESC, nombre ASC')->fetchAll();
	$where=[];$params=[]; 
	if (!empty($_GET['maestria']))
		{
			$where[] = 'a.maestria = ?';
			$params[] = $_GET['maestria'];
		} 
	if (!empty($_GET['folio']))
		{
			$where[]='a.folio_ceneval LIKE ?';
			$params[]='%'.$_GET['folio'].'%';
		}
	$sql = 'SELECT r.*, a.folio_ceneval, a.apellido_paterno, a.apellido_materno, a.nombres, a.correo, a.maestria, a.intentos_post_finalizacion FROM resultados r JOIN aspirantes a ON a.id = r.aspirante_id'; 
	if ($where)$sql.=' WHERE '.implode(' AND ',$where); $sql.=' ORDER BY r.created_at DESC';
	$st=$pdo->prepare($sql);$st->execute($params);$rows=$st->fetchAll(); 
	
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Reportes</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<?php 
			include '_nav.php';
		?>
		<div class= "encabezadoDash">
        <p class="dashTitle"> Panel de Control - Generación de reportes
        	<br>Departamento de Orientación Psicopedagógica<br>
        Bienvenido(a) </p>
    </div>
		<div class="container">
			<h1>Reportes</h1>
			<div class="card">
				<form method="get" class="grid">
					<div>
						<label>Folio</label>
						<input name="folio" value="<?=h($_GET['folio']??'')?>">
					</div>
					<div>
						<label>Programa</label>
						<select name="maestria">
							<option value="">Todos</option>
								<?php 
									foreach($programas as $p):
								?>
							<option value="<?=h($p['nombre'])?>" <?=($_GET['maestria']??'')===$p['nombre']?'selected':''?>><?=h($p['tipo'] . ' - ' . $p['nombre'])?></option>
								<?php 
									endforeach;
								?>
						</select>
					</div>
					<div>
						<label>&nbsp;</label>
						<button>Filtrar</button>
					</div>
					<div>
						<label>&nbsp;</label>
						<input type="button" onclick="location.href='exportar_csv.php';" value="Exportar todo como CSV" />
					</div>
				</form>
			</div>

			<table class="table">
				<tr>
					<th>Folio</th>
					<th>Aspirante</th>
					<th>Programa</th>
					<th>Total</th>
					<th>Nivel</th>
					<th>Tiempo</th>
					<th>Intentos post</th>
					<th></th>
				</tr>
					<?php 
						foreach($rows as $r):
					?>
				<tr>
					<td><?=h($r['folio_ceneval'])?></td>
					<td><?=h($r['apellido_paterno'].' '.$r['apellido_materno'].' '.$r['nombres'])?></td>
					<td><?=h($r['maestria'])?></td>
					<td><?=$r['puntaje_total']?></td>
					<td>
						<span class="badge <?=$r['nivel_general']=='Alto'?'ok':($r['nivel_general']=='Medio'?'mid':'low')?>"><?=h($r['nivel_general'])?></span>
					</td>
					<td><?=format_seconds($r['tiempo_total_segundos'])?></td>
					<td><?=$r['intentos_post_finalizacion']?></td>
					<td>
						<a class="btn secondary" href="reporte.php?id=<?=$r['id']?>">Ver reporte</a>
					</td>
				</tr>
				<?php 
					endforeach;
				?>
			</table>
		</div>
	</body>
</html>