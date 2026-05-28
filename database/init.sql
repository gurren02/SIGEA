CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    phone VARCHAR(40) NULL,
    institutional_id VARCHAR(60) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS teacher_students (
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (teacher_id, student_id),
    CONSTRAINT fk_teacher_students_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_teacher_students_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS question_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    unit TINYINT NOT NULL,
    text TEXT NOT NULL,
    type ENUM('multiple_choice', 'true_false') NOT NULL,
    score DECIMAL(6,2) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_bank_unit CHECK (unit BETWEEN 1 AND 5),
    CONSTRAINT fk_bank_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bank_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS question_bank_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_question_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_bank_options_question FOREIGN KEY (bank_question_id) REFERENCES question_bank(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    unit TINYINT NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_exams_unit CHECK (unit BETWEEN 1 AND 5),
    CONSTRAINT fk_exams_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_exams_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    unit TINYINT NOT NULL,
    text TEXT NOT NULL,
    type ENUM('multiple_choice', 'true_false') NOT NULL,
    score DECIMAL(6,2) NOT NULL DEFAULT 1,
    CONSTRAINT chk_questions_unit CHECK (unit BETWEEN 1 AND 5),
    CONSTRAINT fk_questions_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS exam_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    score DECIMAL(7,2) NOT NULL DEFAULT 0,
    total_score DECIMAL(7,2) NOT NULL DEFAULT 0,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    validated_at TIMESTAMP NULL,
    validated_by INT NULL,
    UNIQUE KEY uq_attempt_exam_student (exam_id, student_id),
    CONSTRAINT fk_attempt_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    CONSTRAINT fk_attempt_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_attempt_validator FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS exam_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    option_id INT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    points DECIMAL(6,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_answers_attempt FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    CONSTRAINT fk_answers_option FOREIGN KEY (option_id) REFERENCES question_options(id) ON DELETE SET NULL
);

INSERT IGNORE INTO subjects (id, name) VALUES
    (1, 'CALCULO DIFERENCIAL'),
    (2, 'CALCULO INTEGRAL'),
    (3, 'CALCULO VECTORIAL'),
    (4, 'FUND PROGRAMACION'),
    (5, 'ESTRUCTURA DE DATOS'),
    (6, 'DESARR FRONTEND'),
    (7, 'INTELIGENCIA ARTIF'),
    (8, 'QUIMICA'),
    (9, 'ADM BD'),
    (10, 'PROG. WEB'),
    (11, 'DES BACK END'),
    (12, 'ING SOFTWARE');

INSERT IGNORE INTO users (id, name, email, password, role, phone, institutional_id) VALUES
    (1, 'Administrador SIGEA 1', 'admin1@sigea.test', '$2y$10$XGKxNWQG6vGkQgi8wKC29.W14dgww1chPUsNtVz4GDNprxNLlLcJu', 'admin', '555-0101', 'ADM-001'),
    (2, 'Administrador SIGEA 2', 'admin2@sigea.test', '$2y$10$XGKxNWQG6vGkQgi8wKC29.W14dgww1chPUsNtVz4GDNprxNLlLcJu', 'admin', '555-0102', 'ADM-002'),
    (3, 'Administrador SIGEA 3', 'admin3@sigea.test', '$2y$10$XGKxNWQG6vGkQgi8wKC29.W14dgww1chPUsNtVz4GDNprxNLlLcJu', 'admin', '555-0103', 'ADM-003'),
    (4, 'Docente SIGEA 1', 'docente1@sigea.test', '$2y$10$XGKxNWQG6vGkQgi8wKC29.W14dgww1chPUsNtVz4GDNprxNLlLcJu', 'teacher', '555-0201', 'DOC-001'),
    (5, 'Docente SIGEA 2', 'docente2@sigea.test', '$2y$10$XGKxNWQG6vGkQgi8wKC29.W14dgww1chPUsNtVz4GDNprxNLlLcJu', 'teacher', '555-0202', 'DOC-002'),
    (6, 'Docente SIGEA 3', 'docente3@sigea.test', '$2y$10$XGKxNWQG6vGkQgi8wKC29.W14dgww1chPUsNtVz4GDNprxNLlLcJu', 'teacher', '555-0203', 'DOC-003'),
    (7, 'Estudiante SIGEA 1', 'estudiante1@sigea.test', '$2y$10$XGKxNWQG6vGkQgi8wKC29.W14dgww1chPUsNtVz4GDNprxNLlLcJu', 'student', '555-0301', 'EST-001'),
    (8, 'Estudiante SIGEA 2', 'estudiante2@sigea.test', '$2y$10$XGKxNWQG6vGkQgi8wKC29.W14dgww1chPUsNtVz4GDNprxNLlLcJu', 'student', '555-0302', 'EST-002'),
    (9, 'Estudiante SIGEA 3', 'estudiante3@sigea.test', '$2y$10$XGKxNWQG6vGkQgi8wKC29.W14dgww1chPUsNtVz4GDNprxNLlLcJu', 'student', '555-0303', 'EST-003');

INSERT IGNORE INTO teacher_students (teacher_id, student_id) VALUES
    (4, 7),
    (5, 8),
    (6, 9);

DELIMITER //

DROP PROCEDURE IF EXISTS seed_question_bank //
CREATE PROCEDURE seed_question_bank()
BEGIN
    DECLARE v_teacher INT DEFAULT 4;
    DECLARE v_subject INT DEFAULT 1;
    DECLARE v_unit INT DEFAULT 1;
    DECLARE v_question INT DEFAULT 1;
    DECLARE v_question_id INT;
    DECLARE v_subject_name VARCHAR(120);
    DECLARE v_focus VARCHAR(160);
    DECLARE v_concept1 VARCHAR(160);
    DECLARE v_concept2 VARCHAR(160);
    DECLARE v_concept3 VARCHAR(160);
    DECLARE v_practice VARCHAR(160);
    DECLARE v_tool VARCHAR(160);
    DECLARE v_error VARCHAR(160);
    DECLARE v_result VARCHAR(160);
    DECLARE v_text TEXT;
    DECLARE v_type VARCHAR(30);
    DECLARE v_correct VARCHAR(200);
    DECLARE v_wrong1 VARCHAR(200);
    DECLARE v_wrong2 VARCHAR(200);
    DECLARE v_wrong3 VARCHAR(200);
    DECLARE v_true_correct TINYINT DEFAULT 1;

    CREATE TEMPORARY TABLE seed_unit_templates (
        subject_id INT NOT NULL,
        unit TINYINT NOT NULL,
        focus VARCHAR(160) NOT NULL,
        concept1 VARCHAR(160) NOT NULL,
        concept2 VARCHAR(160) NOT NULL,
        concept3 VARCHAR(160) NOT NULL,
        practice VARCHAR(160) NOT NULL,
        tool VARCHAR(160) NOT NULL,
        common_error VARCHAR(160) NOT NULL,
        expected_result VARCHAR(160) NOT NULL,
        PRIMARY KEY (subject_id, unit)
    );

    INSERT INTO seed_unit_templates VALUES
        (1, 1, 'limites y continuidad', 'limite lateral', 'continuidad', 'indeterminacion', 'evaluar acercamientos por izquierda y derecha', 'tabla de valores', 'sustituir sin analizar el dominio', 'describir el comportamiento local'),
        (1, 2, 'derivada como razon de cambio', 'derivada', 'recta tangente', 'tasa instantanea', 'calcular pendientes locales', 'regla de potencia', 'confundir promedio con instantaneo', 'obtener un modelo local'),
        (1, 3, 'reglas de derivacion', 'regla de la cadena', 'regla del producto', 'regla del cociente', 'simplificar antes de derivar', 'algebra simbolica', 'aplicar una regla sin revisar la funcion', 'derivar funciones compuestas'),
        (1, 4, 'aplicaciones de la derivada', 'punto critico', 'maximo local', 'minimo local', 'analizar signos de la derivada', 'tabla de variacion', 'clasificar sin prueba de signos', 'resolver problemas de optimizacion'),
        (1, 5, 'analisis de graficas', 'concavidad', 'punto de inflexion', 'crecimiento', 'usar la segunda derivada', 'grafica de la funcion', 'confundir pendiente con concavidad', 'interpretar la forma de una curva'),
        (2, 1, 'antiderivadas basicas', 'integral indefinida', 'constante de integracion', 'familia de funciones', 'reconocer patrones de derivacion', 'tabla de integrales', 'omitir la constante de integracion', 'recuperar una funcion original'),
        (2, 2, 'integral definida', 'area bajo la curva', 'suma de Riemann', 'teorema fundamental', 'particionar el intervalo', 'notacion sigma', 'usar limites incorrectos', 'calcular acumulacion neta'),
        (2, 3, 'metodos de integracion', 'sustitucion', 'integracion por partes', 'fracciones parciales', 'elegir el metodo segun la estructura', 'cambio de variable', 'elegir una variable que no simplifica', 'resolver integrales no inmediatas'),
        (2, 4, 'aplicaciones de la integral', 'volumen', 'trabajo', 'valor promedio', 'plantear el diferencial correcto', 'metodo de discos', 'integrar una magnitud sin unidades', 'modelar acumulaciones fisicas'),
        (2, 5, 'integrales impropias', 'convergencia', 'divergencia', 'limite infinito', 'evaluar mediante limites', 'criterio de comparacion', 'sustituir infinito como numero', 'decidir si una integral existe'),
        (3, 1, 'vectores en el espacio', 'vector', 'magnitud', 'direccion', 'descomponer en componentes', 'sistema coordenado', 'sumar magnitudes sin direccion', 'representar cantidades espaciales'),
        (3, 2, 'producto punto y proyecciones', 'producto punto', 'angulo entre vectores', 'proyeccion', 'normalizar vectores', 'formula del coseno', 'olvidar dividir por las magnitudes', 'medir alineacion entre vectores'),
        (3, 3, 'producto cruz y planos', 'producto cruz', 'vector normal', 'plano', 'usar determinantes', 'matriz de componentes', 'cambiar el orden sin considerar signo', 'obtener normales y areas'),
        (3, 4, 'funciones vectoriales', 'curva parametrica', 'velocidad', 'aceleracion', 'derivar componente a componente', 'parametro tiempo', 'mezclar parametros distintos', 'describir movimiento en el espacio'),
        (3, 5, 'campos vectoriales', 'gradiente', 'divergencia', 'rotacional', 'analizar variacion espacial', 'operador nabla', 'confundir campo escalar con vectorial', 'interpretar flujo y rotacion'),
        (4, 1, 'algoritmos y pseudocodigo', 'algoritmo', 'entrada', 'salida', 'ordenar pasos de solucion', 'diagrama de flujo', 'empezar a codificar sin plan', 'resolver problemas de forma sistematica'),
        (4, 2, 'variables y tipos de datos', 'variable', 'tipo de dato', 'operador', 'declarar datos adecuados', 'tabla de tipos', 'usar texto como numero sin conversion', 'representar informacion correctamente'),
        (4, 3, 'estructuras de control', 'condicional', 'ciclo', 'contador', 'probar casos limite', 'traza de escritorio', 'crear ciclos sin condicion de salida', 'controlar el flujo del programa'),
        (4, 4, 'funciones y modularidad', 'funcion', 'parametro', 'valor de retorno', 'dividir el problema en partes', 'firma de funcion', 'repetir codigo innecesariamente', 'reutilizar soluciones'),
        (4, 5, 'arreglos basicos', 'arreglo', 'indice', 'recorrido', 'validar limites del arreglo', 'ciclo for', 'acceder fuera del rango', 'procesar colecciones de datos'),
        (5, 1, 'analisis de complejidad', 'orden de crecimiento', 'tiempo de ejecucion', 'memoria', 'comparar algoritmos por costo', 'notacion O grande', 'medir solo con un caso pequeno', 'elegir estructuras eficientes'),
        (5, 2, 'listas enlazadas', 'nodo', 'referencia', 'lista enlazada', 'actualizar enlaces con cuidado', 'puntero siguiente', 'perder la referencia inicial', 'insertar y eliminar elementos'),
        (5, 3, 'pilas y colas', 'pila', 'cola', 'prioridad', 'respetar reglas de acceso', 'operaciones push y pop', 'extraer por el extremo incorrecto', 'modelar turnos y retrocesos'),
        (5, 4, 'arboles', 'arbol binario', 'raiz', 'recorrido', 'aplicar recursion', 'recorrido inorden', 'ignorar subarboles vacios', 'organizar datos jerarquicos'),
        (5, 5, 'grafos y hashing', 'grafo', 'tabla hash', 'colision', 'seleccionar estructura segun busqueda', 'funcion hash', 'no manejar colisiones', 'consultar datos rapidamente'),
        (6, 1, 'html semantico', 'etiqueta semantica', 'estructura del documento', 'accesibilidad', 'usar encabezados en orden', 'validador html', 'usar div para todo', 'crear interfaces comprensibles'),
        (6, 2, 'css y diseno responsivo', 'modelo de caja', 'flexbox', 'media query', 'definir jerarquias visuales', 'inspector del navegador', 'fijar anchos que rompen en movil', 'adaptar pantallas a dispositivos'),
        (6, 3, 'javascript en el navegador', 'evento', 'dom', 'estado', 'separar datos de presentacion', 'addEventListener', 'modificar el dom sin validar', 'crear interacciones'),
        (6, 4, 'componentes frontend', 'componente', 'propiedad', 'estado local', 'reutilizar bloques de interfaz', 'sistema de componentes', 'mezclar responsabilidades', 'mantener interfaces escalables'),
        (6, 5, 'consumo de apis', 'peticion http', 'json', 'renderizado asincrono', 'manejar estados de carga', 'fetch', 'ignorar errores de red', 'mostrar datos externos'),
        (7, 1, 'fundamentos de ia', 'agente inteligente', 'modelo', 'dato', 'definir el problema', 'conjunto de entrenamiento', 'confundir regla fija con aprendizaje', 'automatizar decisiones'),
        (7, 2, 'busqueda y heuristicas', 'heuristica', 'espacio de estados', 'costo', 'evaluar caminos posibles', 'algoritmo A estrella', 'usar heuristicas no admisibles', 'encontrar soluciones eficientes'),
        (7, 3, 'aprendizaje supervisado', 'clasificacion', 'regresion', 'etiqueta', 'separar entrenamiento y prueba', 'matriz de confusion', 'evaluar con los datos de entrenamiento', 'predecir a partir de ejemplos'),
        (7, 4, 'redes neuronales', 'neurona artificial', 'peso', 'funcion de activacion', 'ajustar parametros', 'descenso de gradiente', 'usar demasiadas capas sin datos', 'aprender patrones complejos'),
        (7, 5, 'etica y evaluacion de ia', 'sesgo', 'explicabilidad', 'privacidad', 'validar impacto del modelo', 'metricas de evaluacion', 'ignorar datos desbalanceados', 'usar ia de forma responsable'),
        (8, 1, 'estructura atomica', 'proton', 'electron', 'numero atomico', 'comparar particulas subatomicas', 'tabla periodica', 'confundir masa con carga', 'identificar elementos'),
        (8, 2, 'enlaces quimicos', 'enlace ionico', 'enlace covalente', 'electronegatividad', 'analizar transferencia de electrones', 'estructura de Lewis', 'dibujar enlaces sin valencia', 'predecir propiedades de sustancias'),
        (8, 3, 'estequiometria', 'mol', 'masa molar', 'reactivo limitante', 'balancear ecuaciones', 'tabla molar', 'usar gramos como moles directamente', 'calcular cantidades de reaccion'),
        (8, 4, 'soluciones', 'concentracion', 'molaridad', 'dilucion', 'usar unidades consistentes', 'formula M uno V uno', 'mezclar volumenes sin convertir', 'preparar mezclas correctas'),
        (8, 5, 'acidos y bases', 'ph', 'neutralizacion', 'indicador', 'comparar acidez y basicidad', 'escala de ph', 'asumir que ph alto es acido', 'interpretar reacciones acido base'),
        (9, 1, 'modelo relacional', 'tabla', 'registro', 'clave primaria', 'normalizar entidades', 'diagrama entidad relacion', 'guardar listas en una sola celda', 'organizar datos consistentes'),
        (9, 2, 'consultas sql', 'select', 'where', 'join', 'filtrar antes de agrupar', 'consulta sql', 'olvidar condicion de union', 'obtener informacion precisa'),
        (9, 3, 'normalizacion', 'dependencia funcional', 'forma normal', 'redundancia', 'separar datos repetidos', 'modelo logico', 'duplicar informacion sensible', 'reducir inconsistencias'),
        (9, 4, 'transacciones', 'atomicidad', 'rollback', 'commit', 'proteger operaciones criticas', 'transaccion', 'actualizar tablas relacionadas por separado', 'mantener integridad'),
        (9, 5, 'seguridad y rendimiento', 'indice', 'privilegio', 'respaldo', 'analizar planes de consulta', 'explain', 'dar permisos excesivos', 'mejorar consultas y seguridad'),
        (10, 1, 'arquitectura web', 'cliente', 'servidor', 'protocolo http', 'separar responsabilidades', 'navegador web', 'guardar secretos en el cliente', 'comprender flujo de peticiones'),
        (10, 2, 'formularios y validacion', 'formulario', 'validacion', 'metodo post', 'validar en cliente y servidor', 'atributos html', 'confiar solo en javascript', 'recibir datos confiables'),
        (10, 3, 'sesiones y autenticacion', 'sesion', 'cookie', 'hash de contrasena', 'proteger credenciales', 'session id', 'guardar contrasenas en texto plano', 'controlar acceso de usuarios'),
        (10, 4, 'apis web', 'endpoint', 'json', 'codigo de estado', 'documentar rutas', 'cliente rest', 'responder siempre codigo 200', 'integrar sistemas'),
        (10, 5, 'despliegue web', 'hosting', 'dominio', 'variable de entorno', 'separar configuracion de codigo', 'servidor web', 'subir claves al repositorio', 'publicar aplicaciones'),
        (11, 1, 'fundamentos backend', 'ruta', 'controlador', 'modelo', 'separar capas', 'patron mvc', 'mezclar sql con vista', 'mantener codigo organizado'),
        (11, 2, 'persistencia de datos', 'repositorio', 'consulta preparada', 'conexion', 'usar parametros', 'pdo', 'concatenar datos de usuario en sql', 'guardar datos de forma segura'),
        (11, 3, 'servicios y reglas de negocio', 'servicio', 'validacion', 'caso de uso', 'centralizar reglas', 'clase de dominio', 'duplicar reglas en varias vistas', 'mantener consistencia'),
        (11, 4, 'seguridad backend', 'autorizacion', 'csrf', 'sanitizacion', 'validar permisos', 'token seguro', 'confiar en datos del navegador', 'proteger recursos'),
        (11, 5, 'pruebas y mantenimiento', 'prueba automatica', 'log', 'manejo de errores', 'cubrir casos criticos', 'suite de pruebas', 'ocultar errores sin registrarlos', 'operar sistemas confiables'),
        (12, 1, 'proceso de software', 'requerimiento', 'alcance', 'actor', 'levantar necesidades', 'historia de usuario', 'programar sin entender el problema', 'definir soluciones utiles'),
        (12, 2, 'analisis y diseno', 'caso de uso', 'diagrama', 'arquitectura', 'modelar antes de construir', 'uml', 'disenar sin restricciones reales', 'comunicar decisiones tecnicas'),
        (12, 3, 'gestion de proyectos', 'iteracion', 'riesgo', 'prioridad', 'planificar entregas pequenas', 'tablero kanban', 'ignorar dependencias', 'controlar avance del proyecto'),
        (12, 4, 'calidad de software', 'mantenibilidad', 'refactorizacion', 'deuda tecnica', 'revisar codigo', 'control de versiones', 'cambiar codigo sin pruebas', 'mejorar calidad interna'),
        (12, 5, 'despliegue y operacion', 'version', 'monitoreo', 'incidencia', 'automatizar entregas', 'pipeline ci cd', 'desplegar manualmente sin registro', 'mantener software en produccion');

    WHILE v_teacher <= 6 DO
        SET v_subject = 1;
        WHILE v_subject <= 12 DO
            SELECT name INTO v_subject_name FROM subjects WHERE id = v_subject;
            SET v_unit = 1;
            WHILE v_unit <= 5 DO
                SELECT focus, concept1, concept2, concept3, practice, tool, common_error, expected_result
                INTO v_focus, v_concept1, v_concept2, v_concept3, v_practice, v_tool, v_error, v_result
                FROM seed_unit_templates
                WHERE subject_id = v_subject AND unit = v_unit;

                SET v_question = 1;
                WHILE v_question <= 20 DO
                    IF v_question MOD 5 = 0 THEN
                        SET v_type = 'true_false';
                        SET v_true_correct = IF(v_question IN (5, 15), 1, 0);
                        SET v_text = CASE v_question
                            WHEN 5 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' afirma que ', v_concept1, ' ayuda a ', v_result)
                            WHEN 10 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' afirma que ', v_error, ' es una practica recomendada')
                            WHEN 15 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' afirma que ', v_tool, ' se relaciona con ', v_practice)
                            ELSE CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' afirma que se debe ignorar ', v_concept2, ' para lograr ', v_result)
                        END;
                        INSERT INTO question_bank (teacher_id, subject_id, unit, text, type, score)
                        VALUES (v_teacher, v_subject, v_unit, v_text, v_type, 1);
                        SET v_question_id = LAST_INSERT_ID();
                        INSERT INTO question_bank_options (bank_question_id, option_text, is_correct) VALUES
                            (v_question_id, 'Verdadero', v_true_correct),
                            (v_question_id, 'Falso', IF(v_true_correct = 1, 0, 1));
                    ELSE
                        SET v_type = 'multiple_choice';
                        SET v_text = CASE v_question
                            WHEN 1 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que concepto se relaciona mejor con ', v_focus)
                            WHEN 2 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que practica fortalece ', v_focus)
                            WHEN 3 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que herramienta o tecnica apoya ', v_focus)
                            WHEN 4 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que resultado se busca al aplicar ', v_concept1)
                            WHEN 6 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que elemento complementa a ', v_concept1)
                            WHEN 7 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que error debe evitarse durante ', v_focus)
                            WHEN 8 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que concepto permite explicar ', v_result)
                            WHEN 9 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que accion conviene antes de resolver un ejercicio')
                            WHEN 11 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' cual opcion representa una evidencia de dominio')
                            WHEN 12 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que relacion es correcta dentro del tema')
                            WHEN 13 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que recurso se usa para comprobar el procedimiento')
                            WHEN 14 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que decision mejora la calidad del resultado')
                            WHEN 16 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que concepto aparece como base del aprendizaje')
                            WHEN 17 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que opcion describe mejor el proposito de ', v_practice)
                            WHEN 18 THEN CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que alternativa ayuda a detectar errores')
                            ELSE CONCAT('Unidad ', v_unit, ' de ', v_subject_name, ' que respuesta resume el objetivo principal')
                        END;
                        SET v_correct = CASE v_question
                            WHEN 1 THEN v_concept1
                            WHEN 2 THEN v_practice
                            WHEN 3 THEN v_tool
                            WHEN 4 THEN v_result
                            WHEN 6 THEN v_concept2
                            WHEN 7 THEN v_error
                            WHEN 8 THEN v_concept3
                            WHEN 9 THEN v_practice
                            WHEN 11 THEN v_result
                            WHEN 12 THEN CONCAT(v_concept1, ' se relaciona con ', v_concept2)
                            WHEN 13 THEN v_tool
                            WHEN 14 THEN v_practice
                            WHEN 16 THEN v_concept1
                            WHEN 17 THEN v_result
                            WHEN 18 THEN v_tool
                            ELSE v_result
                        END;
                        SET v_wrong1 = CASE v_question
                            WHEN 7 THEN 'ignorar las instrucciones'
                            WHEN 12 THEN CONCAT(v_error, ' resuelve siempre el problema')
                            ELSE v_error
                        END;
                        SET v_wrong2 = CASE v_question
                            WHEN 1 THEN v_concept2
                            WHEN 2 THEN v_concept3
                            WHEN 3 THEN v_concept2
                            ELSE 'copiar datos sin analizarlos'
                        END;
                        SET v_wrong3 = CASE v_question
                            WHEN 4 THEN 'obtener un resultado sin justificar'
                            WHEN 8 THEN v_concept2
                            ELSE 'elegir una opcion al azar'
                        END;
                        INSERT INTO question_bank (teacher_id, subject_id, unit, text, type, score)
                        VALUES (v_teacher, v_subject, v_unit, v_text, v_type, 1);
                        SET v_question_id = LAST_INSERT_ID();
                        INSERT INTO question_bank_options (bank_question_id, option_text, is_correct) VALUES
                            (v_question_id, v_correct, 1),
                            (v_question_id, v_wrong1, 0),
                            (v_question_id, v_wrong2, 0),
                            (v_question_id, v_wrong3, 0);
                    END IF;
                    SET v_question = v_question + 1;
                END WHILE;
                SET v_unit = v_unit + 1;
            END WHILE;
            SET v_subject = v_subject + 1;
        END WHILE;
        SET v_teacher = v_teacher + 1;
    END WHILE;
END //

DELIMITER ;

CALL seed_question_bank();
DROP PROCEDURE IF EXISTS seed_question_bank;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(160) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_resets_email (email),
    KEY idx_resets_token (token)
);

