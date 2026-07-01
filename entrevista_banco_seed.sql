-- Tabla sugerida
CREATE TABLE IF NOT EXISTS entrevista_banco_preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dimension_id INT NOT NULL,
    pregunta TEXT NOT NULL,
    activa TINYINT DEFAULT 1,
    orden INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS entrevista_preguntas_aplicadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aspirante_id INT NOT NULL,
    resultado_id INT NOT NULL,
    pregunta_banco_id INT NULL,
    dimension_id INT NULL,
    pregunta_texto TEXT NOT NULL,
    respuesta TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

TRUNCATE TABLE entrevista_banco_preguntas;

INSERT INTO entrevista_banco_preguntas (dimension_id, pregunta, activa, orden) VALUES
(1, 'Cuéntame sobre una situación en la que tuviste que elegir entre obtener un beneficio personal o actuar de acuerdo con tus principios. ¿Qué decisión tomaste y por qué?', 1, 1),
(1, '¿Cómo decides qué es correcto o incorrecto cuando las normas no son completamente claras?', 1, 2),
(1, '¿Qué opinas sobre el uso de herramientas de inteligencia artificial para realizar actividades académicas o laborales? ¿Dónde consideras que deberían existir límites?', 1, 3),
(1, 'Describe una ocasión en la que cometiste un error importante. ¿Cómo manejaste la situación?', 1, 4),
(1, 'Si observaras a un compañero utilizar inteligencia artificial de manera poco ética para obtener ventajas, ¿cómo reaccionarías?', 1, 5),
(2, 'Háblame de una meta importante que hayas tenido que mantener durante varios meses. ¿Cómo lograste sostener tu esfuerzo?', 1, 1),
(2, '¿Qué haces cuando pierdes la motivación en un proyecto que todavía no has terminado?', 1, 2),
(2, 'Describe una situación en la que no pudiste cumplir con una responsabilidad. ¿Qué ocurrió y qué aprendiste de esa experiencia?', 1, 3),
(2, '¿Cómo organizas tus actividades cuando tienes varias tareas importantes al mismo tiempo?', 1, 4),
(2, '¿Qué estrategias utilizas para asegurar que cumples con tus compromisos académicos o laborales?', 1, 5),
(3, 'Cuéntame sobre una situación reciente que te haya generado mucho enojo o frustración. ¿Cómo reaccionaste?', 1, 1),
(3, '¿Qué haces normalmente cuando alguien critica tu trabajo o tus ideas?', 1, 2),
(3, 'Describe una ocasión en la que tuviste un conflicto importante con otra persona. ¿Cómo lo resolviste?', 1, 3),
(3, '¿Cómo manejas el estrés cuando enfrentas presión académica o laboral?', 1, 4),
(3, 'Cuando sientes emociones intensas, ¿qué haces para recuperar la calma y tomar decisiones adecuadas?', 1, 5),
(4, 'Cuéntame sobre una decisión difícil que hayas tenido que tomar recientemente. ¿Qué factores consideraste antes de decidir?', 1, 1),
(4, 'Cuando una decisión puede beneficiar a algunas personas pero perjudicar a otras, ¿cómo determinas cuál es la mejor opción?', 1, 2),
(4, '¿Qué papel juegan los valores personales en tus decisiones importantes?', 1, 3),
(4, 'Describe una situación en la que cambiaste de opinión después de analizar mejor las consecuencias de una decisión.', 1, 4),
(4, '¿Cómo evalúas los riesgos antes de implementar una idea o propuesta nueva?', 1, 5),
(5, '¿Qué significa para ti actuar con transparencia dentro de un equipo de trabajo?', 1, 1),
(5, 'Describe una situación en la que cometiste un error que podía afectar a otras personas. ¿Cómo manejaste esa situación?', 1, 2),
(5, '¿Qué haces cuando consideras que una regla o procedimiento es injusto o innecesario?', 1, 3),
(5, '¿Crees que existen situaciones en las que ocultar información puede estar justificado? ¿Por qué?', 1, 4),
(5, 'Cuando tus intereses personales entran en conflicto con las necesidades de un grupo, ¿cómo manejas esa situación?', 1, 5);
