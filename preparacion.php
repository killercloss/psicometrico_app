<?php
    session_start(); 
    require_once __DIR__.'/includes/db.php'; 
    require_once __DIR__.'/includes/functions.php'; 
    require_candidate();

    $aspirante_id=$_SESSION['aspirante_id']; 
    $int=current_attempt($pdo,$aspirante_id);
    if(!$int) 
        {
            redirect('index.php');
        }

    if($_SERVER['REQUEST_METHOD']==='POST'){
    $pdo->prepare('UPDATE intentos SET iniciado_at=COALESCE(iniciado_at,NOW()), estado="en_progreso" WHERE id=?')->execute([$int['id']]);
    redirect('examen.php');
    }
?>
<!doctype html><html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Preparación</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <div class="container">
            <div class="card">
                <div class= "encabezado">
                    <img style="width: 20%;" src="resources/uanl.png">
                    <img style="width: 20%;" src="resources/5 FCFM.png">
                </div>
                <h1>Antes de comenzar</h1>
                <div class="alert prepare">
                    <b>La prueba comenzará automáticamente en <span id="prep">02:00</span> minutos.
                    </b>
                    <br>Haga clic en comenzar para iniciarla de inmediato.
                </div>
                <p>Instrucciones:</p>
                <ul>
                    <li>La prueba tiene 5 secciones y 60 preguntas.</li>
                    <li>Cada sección tiene un límite de 5 minutos.</li>
                    <li>No podrá regresar a secciones anteriores.</li>
                    <li>Si se agota el tiempo, las preguntas no enviadas de la sección activa se registrarán con 0 puntos.</li>
                    <li>Evite cerrar, recargar o cambiar de dispositivo durante la aplicación.</li>
                </ul>
                <form method="post" id="startForm">
                    <button>Comenzar</button>
                </form>
            </div>
        </div>
        <script>
            let t=120; 
            const el=document.getElementById('prep'); 
            const f=document.getElementById('startForm');
            
            setInterval(()=>{t--; 
                if(t<=0)
                    {
                        f.submit();
                        return;
                    } 
                let m=String(Math.floor(t/60)).padStart(2,'0'), s=String(t%60).padStart(2,'0'); 
                el.textContent=m+':'+s;},1000);
        </script>
    </body>
    </html>
