<?php
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($url){ header('Location: '.$url); exit; }
function require_admin(){ if(empty($_SESSION['admin'])) redirect('login.php'); }
function require_candidate(){ if(empty($_SESSION['aspirante_id'])) redirect('index.php'); }
function score_answer($value, $inverse){
    $value = (int)$value;
    if($value < 1 || $value > 5) return 0;
    return $inverse ? (6 - $value) : $value;
}
function level_dimension($score){
    if($score >= 45) return 'Alto';
    if($score >= 28) return 'Medio';
    return 'Bajo';
}
function level_general($score){
    if($score >= 201) return 'Alto';
    if($score >= 141) return 'Medio';
    return 'Bajo';
}
function general_interpretation($level){
    if($level === 'Alto') return 'Rasgos adecuados de ética, responsabilidad y control emocional.';
    if($level === 'Medio') return 'Riesgo moderado. Requiere seguimiento o fortalecimiento.';
    return 'Posibles indicadores de riesgo ético o conductual.';
}
function current_attempt($pdo, $aspirante_id){
    $st=$pdo->prepare('SELECT * FROM intentos WHERE aspirante_id=? ORDER BY id DESC LIMIT 1');
    $st->execute([$aspirante_id]);
    return $st->fetch();
}
function format_seconds($seconds){
    $seconds=max(0,(int)$seconds); 
    $m=floor($seconds/60); 
    $s=$seconds%60;
    return sprintf('%02d:%02d', $m, $s);
}
?>
