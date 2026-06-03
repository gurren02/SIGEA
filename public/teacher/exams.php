<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('teacher');

if (is_post()) {
    try {
        Exam::createFromBank($user['id'], $_POST);
        flash('success', 'Examen generado y publicado con las preguntas seleccionadas.');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }
    redirect('/teacher/exams.php');
}

$exams = Exam::byTeacher($user['id']);
$subjects = User::subjectsByTeacher($user['id']);
$students = User::studentsByTeacher($user['id']);
$bank = Exam::bankByTeacher($user['id']);
$pageTitle = 'Generar examenes';
render('header', compact('pageTitle'));
?>
<?php if (!$subjects): ?>
    <div class="alert alert-danger" style="margin-bottom:20px;">
        No tienes materias asignadas por el administrador. Ponte en contacto con el administrador para poder generar examenes.
    </div>
<?php endif; ?>
<form class="panel exam-builder" method="post" id="exam-wizard">
    <div class="section-head">
        <div>
            <h2>Generacion por fases</h2>
            <p class="muted">Completa cada paso en orden: datos, preguntas del banco y parametros finales.</p>
        </div>
        <a class="button button-ghost" href="/teacher/questions.php">Ir al banco</a>
    </div>

    <ol class="wizard-steps">
        <li class="is-active" data-step-indicator="1">Datos</li>
        <li data-step-indicator="2">Preguntas</li>
        <li data-step-indicator="3">Alumnos</li>
        <li data-step-indicator="4">Parametros</li>
    </ol>

    <section class="phase-block wizard-step is-active" data-step="1">
        <span class="phase-badge">Paso 1</span>
        <h3>Datos del examen</h3>
        <div class="form-row">
            <label>Titulo
                <input name="title" required>
            </label>
            <label>Materia
                <select name="subject_id" id="exam-subject" required>
                    <option value="">Seleccionar materia</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= (int) $subject['id'] ?>"><?= e($subject['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Unidad
                <select name="unit" id="exam-unit" required>
                    <option value="">Seleccionar unidad</option>
                    <?php foreach (Exam::units() as $unit): ?>
                        <option value="<?= $unit ?>">UNIDAD <?= $unit ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <label>Descripcion
            <textarea name="description" rows="3"></textarea>
        </label>
        <div class="wizard-actions">
            <button class="button button-primary" type="button" data-wizard-next>Siguiente</button>
        </div>
    </section>

    <section class="phase-block wizard-step" data-step="2">
        <span class="phase-badge">Paso 2</span>
        <div class="section-head">
            <div>
                <h3>Preguntas del banco</h3>
                <p class="muted">Solo se muestran preguntas de la materia y unidad seleccionadas en el paso anterior.</p>
            </div>
            <strong class="selected-count"><span id="selected-question-count">0</span> seleccionadas</strong>
        </div>

        <div class="question-picker">
            <?php foreach ($bank as $question): ?>
                <label class="question-pick" data-subject="<?= (int) $question['subject_id'] ?>" data-unit="<?= (int) $question['unit'] ?>">
                    <input type="checkbox" name="bank_questions[]" value="<?= (int) $question['id'] ?>">
                    <span>
                        <strong><?= e($question['text']) ?></strong>
                        <small><?= e($question['subject_name']) ?> | UNIDAD <?= (int) $question['unit'] ?> | <?= $question['type'] === 'true_false' ? 'Verdadero/Falso' : 'Opcion multiple' ?></small>
                    </span>
                </label>
            <?php endforeach; ?>
            <?php if (!$bank): ?>
                <p class="muted">Aun no tienes preguntas en el banco. Crea preguntas antes de generar examenes.</p>
            <?php endif; ?>
        </div>
        <p class="muted" id="empty-question-filter">Selecciona materia y unidad en el paso 1 para ver las preguntas disponibles.</p>
        <div class="wizard-actions">
            <button class="button button-ghost" type="button" data-wizard-prev>Anterior</button>
            <button class="button button-primary" type="button" data-wizard-next>Siguiente</button>
        </div>
    </section>

    <section class="phase-block wizard-step" data-step="3">
        <span class="phase-badge">Paso 3</span>
        <div class="section-head">
            <div>
                <h3>Destinatarios del examen</h3>
                <p class="muted">Selecciona los alumnos que tendrán asignado este examen.</p>
            </div>
        </div>

        <div class="student-pick-list">
            <?php foreach ($students as $student): ?>
                <label class="student-pick-card is-checked">
                    <input type="checkbox" name="students[]" value="<?= (int) $student['id'] ?>" checked>
                    <span class="student-pick-avatar"><?= strtoupper(substr($student['name'], 0, 1)) ?></span>
                    <span class="student-pick-info">
                        <strong><?= e($student['name']) ?></strong>
                        <small><?= e($student['email']) ?></small>
                    </span>
                    <span class="material-symbols-rounded student-pick-check">check_circle</span>
                </label>
            <?php endforeach; ?>
            <?php if (!$students): ?>
                <div class="modal-empty">
                    <span class="material-symbols-rounded">person_off</span>
                    <p>No tienes alumnos asignados. Pide al administrador que te asigne estudiantes.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="wizard-actions">
            <button class="button button-ghost" type="button" data-wizard-prev>Anterior</button>
            <button class="button button-primary" type="button" data-wizard-next>Siguiente</button>
        </div>
    </section>

    <section class="phase-block wizard-step" data-step="4">
        <span class="phase-badge">Paso 4</span>
        <h3>Parametros para generar</h3>
        <div class="form-row">
            <label>Numero de preguntas
                <select name="count" id="exam-count" required>
                    <option value="1">1</option>
                </select>
            </label>
            <label>Como se tomaran
                <select name="selection_mode">
                    <option value="manual">Usar las preguntas seleccionadas</option>
                    <option value="random">Tomarlas aleatoriamente de las seleccionadas</option>
                </select>
            </label>
            <label class="inline-check publish-check">
                <input type="checkbox" name="is_published" checked>
                Publicar al guardar
            </label>
        </div>
        <p class="muted">El numero de preguntas no puede ser mayor que las seleccionadas en el paso anterior.</p>
        <div class="wizard-actions">
            <button class="button button-ghost" type="button" data-wizard-prev>Anterior</button>
            <button class="button button-primary" type="submit">Generar examen</button>
        </div>
    </section>
</form>

<section class="content-grid two">
    <aside class="panel">
        <h2>Alumnos asignados</h2>
        <div class="check-list compact">
            <?php foreach ($students as $student): ?>
                <p><?= e($student['name']) ?><span><?= e($student['email']) ?></span></p>
            <?php endforeach; ?>
            <?php if (!$students): ?><p class="muted">El administrador aun no te ha asignado estudiantes.</p><?php endif; ?>
        </div>
    </aside>
    <section class="panel">
        <h2>Banco disponible</h2>
        <div class="metric-grid compact-metrics">
            <?php foreach (Exam::units() as $unit): ?>
                <article class="metric"><span>Unidad <?= $unit ?></span><strong><?= count(array_filter($bank, fn ($q) => (int) $q['unit'] === $unit)) ?></strong></article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

<section class="panel">
    <h2>Examenes creados</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Titulo</th><th>Materia</th><th>Unidad</th><th>Preguntas</th><th>Publicado</th></tr></thead>
            <tbody>
            <?php foreach ($exams as $exam): ?>
                <tr>
                    <td><?= e($exam['title']) ?></td>
                    <td><?= e($exam['subject_name']) ?></td>
                    <td>UNIDAD <?= (int) $exam['unit'] ?></td>
                    <td><?= (int) $exam['questions_count'] ?></td>
                    <td><?= $exam['is_published'] ? 'Si' : 'No' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$exams): ?><tr><td colspan="5">Aun no has creado examenes.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render('footer'); ?>
