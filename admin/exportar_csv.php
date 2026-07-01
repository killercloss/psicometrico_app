<?php 
	session_start(); 
	require_once __DIR__.'/../includes/db.php'; 
	require_once __DIR__.'/../includes/functions.php'; 
	require_admin();
	csrf_check();

	header('Content-Type: text/xslx; charset=utf-8'); 
	header('Content-Disposition: attachment; filename=resultados_psicometricos.csv');

	$out = fopen('php://output','w'); 
	fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

	fputcsv($out,['folio','apellido_paterno','apellido_materno','nombres','correo','maestria','puntaje_total','nivel_general','interpretacion_general','tiempo_total','intentos_post_finalizacion','dimension','puntaje_dimension','nivel_dimension','tiempo_dimension','interpretacion_dimension']);

	$sql = 'SELECT a.folio_ceneval,a.apellido_paterno,a.apellido_materno,a.nombres,a.correo,a.maestria,a.intentos_post_finalizacion,r.puntaje_total,r.nivel_general,r.interpretacion_general,r.tiempo_total_segundos,d.nombre dimension,rd.puntaje,rd.nivel,rd.tiempo_segundos,rd.interpretacion FROM resultados r JOIN aspirantes a ON a.id=r.aspirante_id JOIN resultados_dimension rd ON rd.resultado_id=r.id JOIN dimensiones d ON d.id=rd.dimension_id ORDER BY a.maestria,a.apellido_paterno,d.orden';
	foreach($pdo->query($sql) as $row)
		{
			fputcsv($out, [$row['folio_ceneval'], $row['apellido_paterno'], $row['apellido_materno'], $row['nombres'], $row['correo'], $row['maestria'], $row['puntaje_total'], $row['nivel_general'], $row['interpretacion_general'], format_seconds($row['tiempo_total_segundos']), $row['intentos_post_finalizacion'], $row['dimension'], $row['puntaje'], $row['nivel'], format_seconds($row['tiempo_segundos']), $row['interpretacion']]);
		}
	exit;