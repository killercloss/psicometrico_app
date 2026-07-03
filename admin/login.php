<?php 
	session_start(); 
	require_once __DIR__.'/../includes/config.php'; 
	require_once __DIR__.'/../includes/functions.php';

	$err=''; 

	if($_SERVER['REQUEST_METHOD'] === 'POST')
		{ 
			csrf_check();
			if(($_POST['user']??'') === ADMIN_USER && password_verify($_POST['pass'] ?? '', ADMIN_PASS))
				{
					$_SESSION['admin'] = true; 
					header('Location:index.php'); 
					exit;
				} 
				$err='Acceso incorrecto'; 
		} 
?>

<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<title>Admin</title>
		<link rel="stylesheet" href="../assets/style.css">
	</head>
	<body>
		<div class="container">
			<div class="card">
				<div class= "encabezado">
                    <img style="width: 20%;" src="../resources/uanl.png">
                    <p class="bienvenida"> Departamento de Orientación Psicopedagógica<br><br>
                    Bienvenido(a) </p>
                    <img style="width: 20%;" src="../resources/5 FCFM.png">
                </div>
				<h1>Acceso Administrativo</h1>
				<?php 
					if($err):
				?>
				<div class="alert error">
					<?=$err?>
				</div>
				<?php 
					endif;
				?>
				<form method="post">
					<?=csrf_field()?>
					<label>Usuario</label>
					<input class="loginInput" name="user">
					<label>Contraseña</label>
					<input class="loginInput" type="password" name="pass">
					<br>
					<button>Entrar</button>
				</form>
			</div>
		</div>
	</body>
</html>