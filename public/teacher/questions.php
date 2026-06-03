<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('teacher');

if (is_post()) {
    try {
        Exam::addBankQuestion($user['id'], $_POST);
        flash('success', 'Pregunta agregada al banco.');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }
    redirect('/teacher/questions.php');
}

$subjects  = User::subjectsByTeacher($user['id']);
$bank      = Exam::bankByTeacher($user['id']);
$pageTitle = 'Banco de preguntas';
render('header', compact('pageTitle'));
?>

<?php if (!$subjects): ?>
    <div class="alert alert-danger" style="margin-bottom:20px;">
        No tienes materias asignadas por el administrador. Ponte en contacto con el administrador para poder registrar preguntas.
    </div>
<?php endif; ?>

<section class="panel">
    <div class="section-head">
        <div>
            <h2>Preguntas</h2>
            <p class="muted">Cada pregunta queda clasificada por materia y unidad.</p>
        </div>
        <div class="actions">
            <button class="button button-primary" data-modal-open="multiple-question">
                <span class="material-symbols-rounded">add_circle</span>
                Opcion multiple
            </button>
            <button class="button button-warning" data-modal-open="true-false-question">
                <span class="material-symbols-rounded">toggle_on</span>
                Verdadero / Falso
            </button>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Pregunta</th>
                    <th>Materia</th>
                    <th>Unidad</th>
                    <th>Tipo</th>
                    <th>Puntos</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bank as $question): ?>
                <tr>
                    <td><?= e($question['text']) ?></td>
                    <td><?= e($question['subject_name']) ?></td>
                    <td>UNIDAD <?= (int) $question['unit'] ?></td>
                    <td>
                        <?php if ($question['type'] === 'true_false'): ?>
                            <span class="pill pill--amber">
                                <span class="material-symbols-rounded" style="font-size:13px;">toggle_on</span>
                                Verdadero/Falso
                            </span>
                        <?php else: ?>
                            <span class="pill">
                                <span class="material-symbols-rounded" style="font-size:13px;">checklist</span>
                                Opcion multiple
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($question['score']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$bank): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--gray);padding:28px;">Aun no hay preguntas registradas.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- ── Modal: Opcion multiple ───────────────────────────────── -->
<div class="modal-backdrop" id="multiple-question">
    <form class="modal" method="post">
        <input type="hidden" name="type" value="multiple_choice">

        <!-- Header -->
        <div class="modal-header">
            <div class="modal-header-icon modal-header-icon--blue">
                <span class="material-symbols-rounded">checklist</span>
            </div>
            <div class="modal-header-text">
                <h2>Pregunta de opcion multiple</h2>
                <p>Define la pregunta, sus opciones y la respuesta correcta.</p>
            </div>
            <button class="modal-close" type="button" data-modal-close aria-label="Cerrar">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="modal-body">

            <!-- Meta: materia, unidad, puntos -->
            <p class="modal-section-label">
                <span class="material-symbols-rounded">tune</span>
                Clasificacion
            </p>
            <div class="form-row">
                <label>Materia
                    <select name="subject_id" required>
                        <option value="">Seleccionar materia</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= (int) $subject['id'] ?>"><?= e($subject['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Unidad
                    <select name="unit" required>
                        <option value="">Seleccionar unidad</option>
                        <?php foreach (Exam::units() as $unit): ?>
                            <option value="<?= $unit ?>">UNIDAD <?= $unit ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Puntos
                    <input type="number" min="1" step="0.5" name="score" value="1" required>
                </label>
            </div>

            <!-- Texto de la pregunta -->
            <p class="modal-section-label" style="margin-top:18px;">
                <span class="material-symbols-rounded">help</span>
                Enunciado
            </p>
            <label>Texto de la pregunta
                <textarea name="text" rows="3" placeholder="Escribe aqui el enunciado de la pregunta..." required></textarea>
            </label>

            <!-- Opciones -->
            <p class="modal-section-label" style="margin-top:18px;">
                <span class="material-symbols-rounded">format_list_bulleted</span>
                Opciones de respuesta
            </p>
            <div class="option-editor">
                <?php foreach (['A','B','C','D'] as $i => $letter): ?>
                <div class="option-editor-row">
                    <span class="option-letter"><?= $letter ?></span>
                    <input name="options[]"
                           placeholder="Opcion <?= $letter ?>"
                           <?= $i < 2 ? 'required' : '' ?>>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Respuesta(s) correcta(s) -->
            <p class="modal-section-label" style="margin-top:18px;">
                <span class="material-symbols-rounded">check_circle</span>
                Respuesta(s) correcta(s) (puedes seleccionar más de una)
            </p>
            <div class="correct-pick">
                <?php foreach (['A','B','C','D'] as $i => $letter): ?>
                <label class="correct-pick-item">
                    <input type="checkbox" name="correct[]" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>>
                    <span class="option-letter option-letter--sm"><?= $letter ?></span>
                    Opcion <?= $letter ?>
                </label>
                <?php endforeach; ?>
            </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button class="button button-ghost" type="button" data-modal-close>Cancelar</button>
            <button class="button button-primary" type="submit">
                <span class="material-symbols-rounded">save</span>
                Guardar pregunta
            </button>
        </div>

    </form>
</div>

<!-- ── Modal: Verdadero / Falso ─────────────────────────────── -->
<div class="modal-backdrop" id="true-false-question">
    <form class="modal" method="post">
        <input type="hidden" name="type" value="true_false">

        <!-- Header -->
        <div class="modal-header">
            <div class="modal-header-icon modal-header-icon--amber">
                <span class="material-symbols-rounded">toggle_on</span>
            </div>
            <div class="modal-header-text">
                <h2>Pregunta Verdadero / Falso</h2>
                <p>Define el enunciado y selecciona la respuesta correcta.</p>
            </div>
            <button class="modal-close" type="button" data-modal-close aria-label="Cerrar">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="modal-body">

            <!-- Meta -->
            <p class="modal-section-label">
                <span class="material-symbols-rounded">tune</span>
                Clasificacion
            </p>
            <div class="form-row">
                <label>Materia
                    <select name="subject_id" required>
                        <option value="">Seleccionar materia</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= (int) $subject['id'] ?>"><?= e($subject['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Unidad
                    <select name="unit" required>
                        <option value="">Seleccionar unidad</option>
                        <?php foreach (Exam::units() as $unit): ?>
                            <option value="<?= $unit ?>">UNIDAD <?= $unit ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Puntos
                    <input type="number" min="1" step="0.5" name="score" value="1" required>
                </label>
            </div>

            <!-- Enunciado -->
            <p class="modal-section-label" style="margin-top:18px;">
                <span class="material-symbols-rounded">help</span>
                Enunciado
            </p>
            <label>Texto de la pregunta
                <textarea name="text" rows="3" placeholder="Escribe aqui la afirmacion a evaluar..." required></textarea>
            </label>

            <!-- Respuesta -->
            <p class="modal-section-label" style="margin-top:18px;">
                <span class="material-symbols-rounded">check_circle</span>
                Respuesta correcta
            </p>
            <div class="tf-pick">
                <label class="tf-pick-card tf-pick-card--true">
                    <input type="radio" name="correct" value="0" checked>
                    <span class="material-symbols-rounded">check_circle</span>
                    <span>Verdadero</span>
                </label>
                <label class="tf-pick-card tf-pick-card--false">
                    <input type="radio" name="correct" value="1">
                    <span class="material-symbols-rounded">cancel</span>
                    <span>Falso</span>
                </label>
            </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button class="button button-ghost" type="button" data-modal-close>Cancelar</button>
            <button class="button button-primary" type="submit">
                <span class="material-symbols-rounded">save</span>
                Guardar pregunta
            </button>
        </div>

    </form>
</div>

<?php render('footer'); ?>
