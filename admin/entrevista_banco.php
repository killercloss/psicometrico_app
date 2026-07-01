<?php
session_start();
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
require_admin();
csrf_check();

$msg = '';

if(isset($_GET['del'])){
    $pdo->prepare('DELETE FROM entrevista_banco_preguntas WHERE id=?')->execute([(int)$_GET['del']]);
    redirect('entrevista_banco.php');
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = $_POST['id'] ?? null;
    $dimension_id = (int)($_POST['dimension_id'] ?? 0);
    $pregunta = trim($_POST['pregunta'] ?? '');
    $orden = (int)($_POST['orden'] ?? 0);
    $activa = isset($_POST['activa']) ? 1 : 0;

    if($dimension_id > 0 && $pregunta !== ''){
        if($id){
            $pdo->prepare('UPDATE entrevista_banco_preguntas SET dimension_id=?, pregunta=?, orden=?, activa=? WHERE id=?')
                ->execute([$dimension_id, $pregunta, $orden, $activa, (int)$id]);
        }else{
            $pdo->prepare('INSERT INTO entrevista_banco_preguntas (dimension_id, pregunta, orden, activa) VALUES (?,?,?,?)')
                ->execute([$dimension_id, $pregunta, $orden, $activa]);
        }
        redirect('entrevista_banco.php');
    }else{
        $msg = 'Selecciona una dimensión y escribe la pregunta.';
    }
}

$edit = null;
if(isset($_GET['edit'])){
    $st = $pdo->prepare('SELECT * FROM entrevista_banco_preguntas WHERE id=?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
}

$dimensiones = $pdo->query('SELECT id, nombre, orden FROM dimensiones ORDER BY orden')->fetchAll();

$rows = $pdo->query('
    SELECT ebp.*, d.nombre AS dimension_nombre, d.orden AS dimension_orden
    FROM entrevista_banco_preguntas ebp
    JOIN dimensiones d ON d.id = ebp.dimension_id
    ORDER BY d.orden ASC, ebp.orden ASC, ebp.id ASC
')->fetchAll();
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Preguntas de entrevista</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include '_nav.php'; ?>

    <div class="container">
        <h1>Preguntas de entrevista</h1>

        <?php if($msg): ?>
            <div class="alert error"><?=h($msg)?></div>
        <?php endif; ?>

        <div class="card">
            <h2><?= $edit ? 'Editar pregunta' : 'Nueva pregunta' ?></h2>
            <form method="post">
                <?=csrf_field()?>
                <?php if($edit): ?>
                    <input type="hidden" name="id" value="<?=$edit['id']?>">
                <?php endif; ?>

                <label>Dimensión</label>
                <select name="dimension_id" required>
                    <option value="">Seleccione dimensión</option>
                    <?php foreach($dimensiones as $d): ?>
                        <option value="<?=$d['id']?>" <?=($edit && (int)$edit['dimension_id']===(int)$d['id'])?'selected':''?>>
                            <?=h($d['orden'].'. '.$d['nombre'])?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Pregunta</label>
                <textarea name="pregunta" rows="4" required><?=h($edit['pregunta'] ?? '')?></textarea>

                <label>Orden</label>
                <input type="number" name="orden" value="<?=h($edit['orden'] ?? 0)?>">

                <label>
                    <input type="checkbox" name="activa" <?=(!$edit || $edit['activa'])?'checked':''?>> Activa
                </label>

                <button>Guardar</button>
                <?php if($edit): ?>
                    <a class="btn secondary" href="entrevista_banco.php">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h2>Banco actual</h2>
            <div class="table-wrapper">
                <table class="table">
                    <tr>
                        <th>Dimensión</th>
                        <th>Orden</th>
                        <th>Pregunta</th>
                        <th>Activa</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach($rows as $r): ?>
                        <tr>
                            <td><?=h($r['dimension_orden'].'. '.$r['dimension_nombre'])?></td>
                            <td><?=h($r['orden'])?></td>
                            <td><?=h($r['pregunta'])?></td>
                            <td><?=$r['activa']?'Sí':'No'?></td>
                            <td class="actions">
                                <a class="btn secondary" href="entrevista_banco.php?edit=<?=$r['id']?>">Editar</a>
                                <a class="btn danger" onclick="return confirm('¿Eliminar pregunta?')" href="entrevista_banco.php?del=<?=$r['id']?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($rows)): ?>
                        <tr><td colspan="5">No hay preguntas registradas.</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
