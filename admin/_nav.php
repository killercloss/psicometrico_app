<?php
	$pagina = basename($_SERVER['PHP_SELF']);
?>

<div class="topnav">
	<a href="https://www.uanl.mx"><img style="width: 100%;" alt="Visitar UANL" title="Visitar UANL" src="../resources/uanl.png"></a>
	<a class="<?= $pagina=='index.php' ? 'active' : '' ?>" href="index.php" title="Página principal">Dashboard</a>
	<a class="<?= $pagina=='aspirantes.php' ? 'active' : '' ?>" href="aspirantes.php" title="Agregar, editar o eliminar aspirantes">Aspirantes</a>
	<a class="<?= $pagina=='programas.php' ? 'active' : '' ?>" href="programas.php" title="Lista de maestrías y doctorados">Programas</a>
	<a class="<?= $pagina=='dimensiones.php' ? 'active' : '' ?>" href="dimensiones.php" title="Dimensiones de psicométrico">Dimensiones</a>
	<a class="<?= $pagina=='preguntas.php' ? 'active' : '' ?>" href="preguntas.php" title="Lista de preguntas para psicométrico">Preguntas de test</a>
	<a class="<?= $pagina=='entrevista_banco.php' ? 'active' : '' ?>" href="entrevista_banco.php">Preguntas de entrevista</a>
	<a class="<?= $pagina=='reportes.php' ? 'active' : '' ?>" href="reportes.php" title="Generar reportes">Reportes</a>
	<a class="<?= $pagina=='respuestas_examen.php' ? 'active' : '' ?>" href="respuestas_examen.php" title="Ver reporte de respuestas">Respuestas por aspirante</a>
	<a href="exportar_csv.php" title="Exportar pantalla en CSV">CSV</a>
	<a href="logout.php" title="Cerrar sesión">Salir</a>
	<a href="https://www.fcfm.uanl.mx"><img style="width: 100%;" alt="Visitar FCFM" title="Visitar FCFM" src="../resources/5 FCFM.png"></a>
</div>