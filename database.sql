DROP DATABASE IF EXISTS psicometrico_db;
CREATE DATABASE psicometrico_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE psicometrico_db;



CREATE TABLE programas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(220) NOT NULL UNIQUE,
 tipo ENUM('Maestría','Doctorado') NOT NULL DEFAULT 'Maestría',
 activo TINYINT(1) DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE aspirantes (
 id INT AUTO_INCREMENT PRIMARY KEY,
 folio_ceneval VARCHAR(50) NOT NULL UNIQUE,
 codigo_acceso VARCHAR(255) NOT NULL,
 apellido_paterno VARCHAR(100) NOT NULL,
 apellido_materno VARCHAR(100) DEFAULT '',
 nombres VARCHAR(150) NOT NULL,
 correo VARCHAR(180) DEFAULT '',
 maestria VARCHAR(180) NOT NULL,
 autorizado TINYINT(1) DEFAULT 1,
 terminado TINYINT(1) DEFAULT 0,
 intentos_post_finalizacion INT DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE dimensiones (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(180) NOT NULL,
 descripcion TEXT,
 tiempo_minutos INT DEFAULT 5,
 orden INT NOT NULL
);
CREATE TABLE preguntas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 dimension_id INT NOT NULL,
 numero INT NOT NULL UNIQUE,
 texto TEXT NOT NULL,
 inversa TINYINT(1) DEFAULT 0,
 activa TINYINT(1) DEFAULT 1,
 FOREIGN KEY (dimension_id) REFERENCES dimensiones(id) ON DELETE CASCADE
);
CREATE TABLE interpretaciones_dimension (
 id INT AUTO_INCREMENT PRIMARY KEY,
 dimension_id INT NOT NULL,
 nivel ENUM('Alto','Medio','Bajo') NOT NULL,
 min_puntaje INT NOT NULL,
 max_puntaje INT NOT NULL,
 interpretacion TEXT,
 fundamentacion TEXT,
 FOREIGN KEY (dimension_id) REFERENCES dimensiones(id) ON DELETE CASCADE
);
CREATE TABLE intentos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 aspirante_id INT NOT NULL,
 iniciado_at DATETIME DEFAULT NULL,
 finalizado_at DATETIME DEFAULT NULL,
 dimension_actual INT DEFAULT 1,
 estado ENUM('preparacion','en_progreso','finalizado') DEFAULT 'preparacion',
 FOREIGN KEY (aspirante_id) REFERENCES aspirantes(id) ON DELETE CASCADE
);
CREATE TABLE tiempos_dimension (
 id INT AUTO_INCREMENT PRIMARY KEY,
 intento_id INT NOT NULL,
 dimension_id INT NOT NULL,
 inicio_at DATETIME DEFAULT NULL,
 fin_at DATETIME DEFAULT NULL,
 limite_segundos INT DEFAULT 300,
 agotado TINYINT(1) DEFAULT 0,
 UNIQUE KEY uniq_intento_dimension (intento_id, dimension_id),
 FOREIGN KEY (intento_id) REFERENCES intentos(id) ON DELETE CASCADE,
 FOREIGN KEY (dimension_id) REFERENCES dimensiones(id) ON DELETE CASCADE
);
CREATE TABLE respuestas (
 id INT AUTO_INCREMENT PRIMARY KEY,
 intento_id INT NOT NULL,
 aspirante_id INT NOT NULL,
 pregunta_id INT NOT NULL,
 valor_original INT NOT NULL DEFAULT 0,
 valor_puntuado INT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uniq_respuesta (intento_id, pregunta_id),
 FOREIGN KEY (intento_id) REFERENCES intentos(id) ON DELETE CASCADE,
 FOREIGN KEY (aspirante_id) REFERENCES aspirantes(id) ON DELETE CASCADE,
 FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE CASCADE
);
CREATE TABLE resultados (
 id INT AUTO_INCREMENT PRIMARY KEY,
 intento_id INT NOT NULL UNIQUE,
 aspirante_id INT NOT NULL,
 puntaje_total INT DEFAULT 0,
 nivel_general VARCHAR(50),
 interpretacion_general TEXT,
 tiempo_total_segundos INT DEFAULT 0,
 carta_resultados MEDIUMTEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (intento_id) REFERENCES intentos(id) ON DELETE CASCADE,
 FOREIGN KEY (aspirante_id) REFERENCES aspirantes(id) ON DELETE CASCADE
);
CREATE TABLE resultados_dimension (
 id INT AUTO_INCREMENT PRIMARY KEY,
 resultado_id INT NOT NULL,
 dimension_id INT NOT NULL,
 puntaje INT DEFAULT 0,
 nivel VARCHAR(50),
 interpretacion TEXT,
 fundamentacion TEXT,
 tiempo_segundos INT DEFAULT 0,
 FOREIGN KEY (resultado_id) REFERENCES resultados(id) ON DELETE CASCADE,
 FOREIGN KEY (dimension_id) REFERENCES dimensiones(id) ON DELETE CASCADE
);


INSERT INTO programas (nombre,tipo,activo) VALUES
('Maestría en Astrofísica Planetaria y Tecnologías Afines','Maestría',1),
('Maestría en Administración con Inteligencia Artificial Aplicada','Maestría',1),
('Maestría en Ciencias con orientación en Matemáticas','Maestría',1),
('Maestría en Ciencia de Datos','Maestría',1),
('Maestría en Ingeniería en Seguridad de la Información','Maestría',1),
('Maestría en Ingeniería Física Industrial','Maestría',1),
('Maestría en Seguridad de la Información y Ciberseguridad','Maestría',1),
('Doctorado en Ciencias con orientación en Matemáticas','Doctorado',1),
('Doctorado en Ingeniería Física','Doctorado',1);

INSERT INTO aspirantes (folio_ceneval,codigo_acceso,apellido_paterno,apellido_materno,nombres,correo,maestria,autorizado) VALUES ('1234','ABCD-1234','García','López','Juan Prueba','aspirante@example.com','Maestría en Ciencia de Datos',1);

INSERT INTO dimensiones (id,nombre,descripcion,tiempo_minutos,orden) VALUES
(1,'Ética e integridad','Honestidad, uso responsable de IA, integridad.',5,1),
(2,'Compromiso y responsabilidad','Cumplimiento, constancia, responsabilidad académica.',5,2),
(3,'Agresividad y control emocional','Impulsividad, tolerancia a la frustración.',5,3),
(4,'Toma de decisiones y juicio ético','Juicio crítico y consecuencias.',5,4),
(5,'Manipulación y conductas de riesgo','Tendencia a engañar o aprovecharse.',5,5);

INSERT INTO preguntas (numero,dimension_id,texto,inversa) VALUES
(1,1,'Considero importante actuar con honestidad incluso cuando nadie supervisa mi trabajo.',0),
(2,1,'Considero que las reglas morales y los códigos de conducta son flexibles dependiendo de la conveniencia del momento.',1),
(3,1,'Me resulta fácil justificar pequeñas faltas a la honestidad si estas me ayudan a conseguir un objetivo personal importante.',1),
(4,1,'Presentar como propio un trabajo realizado por IA no representa un problema importante.',1),
(5,1,'Me siento responsable de las consecuencias éticas de mis decisiones.',0),
(6,1,'Me considero una persona con principios morales firmes y estrictos que guían cada una de mis acciones cotidianas.',0),
(7,1,'La transparencia es indispensable en el manejo de información.',0),
(8,1,'Considero que las normas y regulaciones son obstáculos que limitan el desarrollo del talento individual.',1),
(9,1,'Pienso que exagerar mis capacidades o virtudes personales es una estrategia necesaria para destacar socialmente.',1),
(10,1,'Alterar datos para mejorar resultados puede ser justificable.',1),
(11,1,'Me siento profundamente culpable cuando utilizo algún atajo poco ético para sobresalir.',0),
(12,1,'La honestidad profesional es más importante que obtener reconocimiento rápido.',0),
(13,2,'Suelo terminar las actividades que comienzo.',0),
(14,2,'Frecuentemente dejo tareas importantes para el último momento.',1),
(15,2,'Me organizo adecuadamente para cumplir objetivos.',0),
(16,2,'Cuando un proyecto se vuelve difícil, pierdo motivación rápidamente.',1),
(17,2,'Cumplo acuerdos incluso cuando no existe supervisión.',0),
(18,2,'Me considero una persona constante en mis responsabilidades.',0),
(19,2,'Abandono actividades cuando dejan de interesarme.',1),
(20,2,'Administro adecuadamente mi tiempo.',0),
(21,2,'Tiendo a postergar por mucho tiempo aquellas actividades de mi vida diaria que me resultan rutinarias.',1),
(22,2,'Me cuesta mantener disciplina en proyectos largos.',1),
(23,2,'Busco mantener altos estándares de calidad en mi trabajo.',0),
(24,2,'Evito responsabilidades cuando implican demasiado esfuerzo.',1),
(25,3,'Pierdo la paciencia con extrema facilidad cuando las personas que me rodean no comprenden las cosas a mi mismo ritmo.',1),
(26,3,'Mantengo la calma ante situaciones de presión.',0),
(27,3,'Cuando me enojo, reacciono impulsivamente.',1),
(28,3,'Escucho opiniones diferentes aunque no coincidan con las mías.',0),
(29,3,'Me cuesta controlar mi frustración.',1),
(30,3,'Suelo responder de manera agresiva cuando siento que tengo razón.',1),
(31,3,'Puedo manejar desacuerdos de forma profesional.',0),
(32,3,'Tiendo a responder de manera cortante o confrontativa cuando alguien cuestiona mis opiniones o juicios personales.',1),
(33,3,'Antes de reaccionar, intento analizar la situación.',0),
(34,3,'En discusiones importantes elevo fácilmente el tono de voz.',1),
(35,3,'Considero importante mantener respeto incluso en desacuerdos.',0),
(36,3,'Bajo presión puedo actuar sin pensar en las consecuencias.',1),
(37,4,'Analizo las consecuencias de mis decisiones antes de actuar.',0),
(38,4,'Los resultados son más importantes que la forma en que se obtienen.',1),
(39,4,'Evito de manera activa involucrarme en proyectos que me obliguen a salir de mi zona de confort o a adquirir nuevos conocimientos.',1),
(40,4,'Si una acción incorrecta no es descubierta, el daño no es realmente importante.',1),
(41,4,'Pienso en las consecuencias a largo plazo de mis decisiones.',0),
(42,4,'Busco habitualmente la forma de delegar o evadir mis responsabilidades personales cuando los resultados no son los esperados.',1),
(43,4,'Antes de tomar decisiones importantes reviso posibles riesgos.',0),
(44,4,'Romper reglas puede ser válido si ayuda a alcanzar mejores resultados.',1),
(45,4,'Me identifico como alguien que invierte únicamente el mínimo esfuerzo indispensable para cumplir con sus obligaciones.',0),
(46,4,'Creo que engañar o callar ciertos detalles es aceptable siempre y cuando nadie resulte perjudicado de manera evidente.',1),
(47,4,'Considero importante actuar con responsabilidad digital.',0),
(48,4,'Tomo decisiones impulsivas cuando estoy bajo presión.',1),
(49,5,'A veces es necesario ocultar información para obtener ventajas.',1),
(50,5,'Considero importante actuar con transparencia frente a otros.',0),
(51,5,'Puedo convencer fácilmente a otros de actuar en mi beneficio.',1),
(52,5,'Si una regla me parece innecesaria, no veo problema en ignorarla.',1),
(53,5,'He ocultado errores para evitar consecuencias.',1),
(54,5,'Aprovechar oportunidades personales es más importante que seguir normas estrictas.',1),
(55,5,'Considero importante asumir responsabilidad por mis errores.',0),
(56,5,'Manipular información puede ser útil para evitar conflictos.',1),
(57,5,'Prefiero resolver problemas de forma honesta.',0),
(58,5,'En ocasiones las reglas limitan demasiado el éxito profesional.',1),
(59,5,'Admito mis errores aunque esto pueda perjudicarme.',0),
(60,5,'Algunas personas merecen ser engañadas si eso trae beneficios personales.',1);

INSERT INTO interpretaciones_dimension (dimension_id,nivel,min_puntaje,max_puntaje,interpretacion,fundamentacion) VALUES
(1,'Alto',45,60,'Posee una fuerte orientación hacia la ética, honestidad, responsabilidad social y profesional. Puede actuar de manera congruente con normas y valores internos, incluso sin observación externa. Puede asociarse con mayor conciencia sobre normas éticas de la tecnología y uso responsable.','Kohlberg (1984): niveles superiores de razonamiento moral basados en principios éticos internalizados.'),
(1,'Medio',28,44,'Refleja una orientación ética adecuada, aunque puede verse comprometida por influencias externas, presión social o beneficios personales. Reconoce normas éticas, pero no siempre las mantiene presentes.','Rest (1986): factores situacionales influyen significativamente en la toma de decisiones morales.'),
(1,'Bajo',12,27,'Puede justificar conductas poco éticas, dar mayor importancia a beneficios personales o minimizar consecuencias que afectan a terceros.','Kohlberg (1984): niveles inferiores de razonamiento moral centrados en intereses personales inmediatos.'),
(2,'Alto',45,60,'Indica altos niveles de disciplina, organización, resiliencia y cumplimiento de metas u objetivos. Presenta habilidad adecuada de planificación y solución de problemas.','McCrae y Costa (1999): responsabilidad se asocia con desempeño académico, laboral y cumplimiento de metas.'),
(2,'Medio',28,44,'Refleja niveles adecuados de responsabilidad; generalmente cumple obligaciones y metas, aunque puede presentar dificultades de motivación, organización o constancia bajo presión.','McCrae y Costa (1999): niveles intermedios de responsabilidad muestran comportamientos adaptativos variables por contexto.'),
(2,'Bajo',12,27,'Presenta dificultades importantes en organización y cumplimiento de compromisos; puede tender a procrastinar, abandonar tareas o planear poco.','John, Naumann y Soto (2008); McCrae y Costa (1999): baja responsabilidad se asocia con menor autodisciplina y orientación al logro.'),
(3,'Alto',45,60,'Tiene óptima regulación emocional, buen control de impulsos y capacidad para manejar conflictos y resolver problemas.','Gross (1998): la regulación emocional es fundamental para funcionamiento adaptativo y relaciones saludables.'),
(3,'Medio',28,44,'Posee capacidad moderada para regular emociones y controlar reacciones impulsivas; puede manejar conflictos, estrés o frustración, aunque bajo alta presión puede presentar dificultades temporales.','Gross (1998); Salovey y Mayer (1990): la regulación emocional depende de procesos y demandas contextuales.'),
(3,'Bajo',12,27,'Indica rasgos de impulsividad, dificultad para regular emociones negativas, irritabilidad y respuestas negativas ante estrés o frustración.','Gross (1998): baja regulación emocional se relaciona con conflicto interpersonal y conductas impulsivas.'),
(4,'Alto',45,60,'Refleja capacidad para analizar consecuencias, evaluar riesgos y considerar el impacto de decisiones sobre otras personas. Favorece decisiones en contextos complejos.','Rest (1986): la toma de decisiones éticas implica análisis, razonamiento moral y evaluación de consecuencias.'),
(4,'Medio',28,44,'Posee capacidad adecuada para analizar situaciones y consecuencias antes de decidir, aunque su juicio ético puede verse influido por presión social, contexto o beneficios personales.','Rest (1986); Kohlberg (1984): el juicio ético puede operar en niveles convencionales dependientes de normas y aprobación social.'),
(4,'Bajo',12,27,'Puede presentar dificultades para evaluar consecuencias éticas o actuar impulsivamente. Puede no considerar el impacto de sus acciones sobre otras personas o entorno.','Kohlberg (1984): niveles bajos se caracterizan por decisiones basadas en intereses personales o recompensas inmediatas.'),
(5,'Alto',45,60,'Refleja honestidad interpersonal, transparencia y disposición para asumir responsabilidad. Puede mantener relaciones basadas en confianza y respeto.','Bandura (1999): conductas prosociales y responsabilidad interpersonal favorecen cooperación y funcionamiento saludable.'),
(5,'Medio',28,44,'Reconoce la importancia de honestidad, transparencia y responsabilidad; en algunas circunstancias podría justificar conductas orientadas al beneficio personal o minimizar reglas.','Bandura (1999): la desconexión moral permite justificar comportamientos inapropiados bajo beneficios percibidos o presión contextual.'),
(5,'Bajo',12,27,'Puede presentar tendencia a justificar engaños, ocultamiento de información o conductas orientadas al beneficio personal, aun cuando afecten a otras personas o al entorno laboral.','Bandura (1999): mecanismos de desconexión moral justifican comportamientos normalmente considerados inapropiados.');

ALTER TABLE aspirantes
ADD inicio_examen_at DATETIME NULL AFTER maestria;

ALTER TABLE aspirantes
ADD fecha_nacimiento DATE NULL AFTER correo,
ADD entrevista_at DATETIME NULL AFTER inicio_examen_at,
ADD observaciones TEXT NULL;

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
