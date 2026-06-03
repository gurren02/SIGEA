<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('admin');

if (is_post()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'assign_subjects') {
        User::assignSubjects((int) $_POST['teacher_id'], $_POST['subjects'] ?? []);
        flash('success', 'Materias asignadas correctamente.');
    } else {
        User::assignStudents((int) $_POST['teacher_id'], $_POST['students'] ?? []);
        flash('success', 'Asignacion de estudiantes actualizada.');
    }
    redirect('/admin/teachers.php');
}

$teachers = User::teachersWithStudentCounts();
$students  = User::allByRole('student');
$allSubjects = Exam::subjects();

$assigned  = [];
$assignedSubjects = [];
foreach ($teachers as $teacher) {
    $assigned[$teacher['id']] = User::assignedStudentIds((int) $teacher['id']);
    $assignedSubjects[$teacher['id']] = User::assignedSubjectIds((int) $teacher['id']);
}
$pageTitle = 'Docentes';
render('header', compact('pageTitle'));
?>

<section class="panel">
    <div class="section-head">
        <div>
            <h2>Lista de docentes</h2>
            <p class="muted">Asigna estudiantes a cada docente desde aqui.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Docente</th>
                    <th>Correo</th>
                    <th>Alumnos asignados</th>
                    <th>Accion</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="table-avatar table-avatar--green"><?= strtoupper(substr($teacher['name'], 0, 1)) ?></span>
                            <?= e($teacher['name']) ?>
                        </div>
                    </td>
                    <td><?= e($teacher['email']) ?></td>
                    <td>
                        <span class="pill">
                            <span class="material-symbols-rounded" style="font-size:14px;">group</span>
                            <?= (int) $teacher['students_count'] ?> alumnos
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:8px;">
                            <button class="button button-warning" data-modal-open="assign-<?= (int) $teacher['id'] ?>">
                                <span class="material-symbols-rounded">group_add</span>
                                Alumnos
                            </button>
                            <button class="button button-primary" data-modal-open="assign-subjects-<?= (int) $teacher['id'] ?>">
                                <span class="material-symbols-rounded">menu_book</span>
                                Materias
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- ── Modales: Asignar alumnos ─────────────────────────────── -->
<?php foreach ($teachers as $teacher): ?>
<div class="modal-backdrop" id="assign-<?= (int) $teacher['id'] ?>">
    <form class="modal" method="post">
        <input type="hidden" name="action" value="assign_students">
        <input type="hidden" name="teacher_id" value="<?= (int) $teacher['id'] ?>">

        <!-- Header -->
        <div class="modal-header">
            <div class="modal-header-icon modal-header-icon--amber">
                <span class="material-symbols-rounded">group_add</span>
            </div>
            <div class="modal-header-text">
                <h2>Asignar alumnos</h2>
                <p>Docente: <strong><?= e($teacher['name']) ?></strong></p>
            </div>
            <button class="modal-close" type="button" data-modal-close aria-label="Cerrar">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="modal-body">
            <?php if ($students): ?>
            <p class="modal-section-label">
                <span class="material-symbols-rounded">checklist</span>
                Selecciona los estudiantes
            </p>
            <div class="student-pick-list">
                <?php foreach ($students as $student):
                    $checked = in_array((int) $student['id'], $assigned[$teacher['id']], true);
                    $initials = strtoupper(substr($student['name'], 0, 1));
                ?>
                <label class="student-pick-card <?= $checked ? 'is-checked' : '' ?>">
                    <input type="checkbox"
                           name="students[]"
                           value="<?= (int) $student['id'] ?>"
                           <?= $checked ? 'checked' : '' ?>>
                    <span class="student-pick-avatar"><?= $initials ?></span>
                    <span class="student-pick-info">
                        <strong><?= e($student['name']) ?></strong>
                        <small><?= e($student['email']) ?></small>
                    </span>
                    <span class="material-symbols-rounded student-pick-check">check_circle</span>
                </label>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="modal-empty">
                <span class="material-symbols-rounded">person_off</span>
                <p>No hay estudiantes registrados aun.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button class="button button-ghost" type="button" data-modal-close>Cancelar</button>
            <button class="button button-primary" type="submit">
                <span class="material-symbols-rounded">save</span>
                Guardar asignacion
            </button>
        </div>

    </form>
</div>

<div class="modal-backdrop" id="assign-subjects-<?= (int) $teacher['id'] ?>">
    <form class="modal" method="post">
        <input type="hidden" name="action" value="assign_subjects">
        <input type="hidden" name="teacher_id" value="<?= (int) $teacher['id'] ?>">

        <!-- Header -->
        <div class="modal-header">
            <div class="modal-header-icon modal-header-icon--blue">
                <span class="material-symbols-rounded">menu_book</span>
            </div>
            <div class="modal-header-text">
                <h2>Asignar materias</h2>
                <p>Docente: <strong><?= e($teacher['name']) ?></strong></p>
            </div>
            <button class="modal-close" type="button" data-modal-close aria-label="Cerrar">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="modal-body">
            <?php if ($allSubjects): ?>
            <p class="modal-section-label">
                <span class="material-symbols-rounded">checklist</span>
                Selecciona las materias
            </p>
            <div class="student-pick-list">
                <?php foreach ($allSubjects as $subj):
                    $checked = in_array((int) $subj['id'], $assignedSubjects[$teacher['id']], true);
                ?>
                <label class="student-pick-card <?= $checked ? 'is-checked' : '' ?>">
                    <input type="checkbox"
                           name="subjects[]"
                           value="<?= (int) $subj['id'] ?>"
                           <?= $checked ? 'checked' : '' ?>>
                    <span class="student-pick-avatar" style="background:rgba(37,99,235,.1);color:var(--blue-vivid);"><span class="material-symbols-rounded" style="font-size:16px;">book</span></span>
                    <span class="student-pick-info">
                        <strong><?= e($subj['name']) ?></strong>
                    </span>
                    <span class="material-symbols-rounded student-pick-check">check_circle</span>
                </label>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="modal-empty">
                <span class="material-symbols-rounded">book</span>
                <p>No hay materias registradas en el sistema.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button class="button button-ghost" type="button" data-modal-close>Cancelar</button>
            <button class="button button-primary" type="submit">
                <span class="material-symbols-rounded">save</span>
                Guardar asignacion
            </button>
        </div>

    </form>
</div>
<?php endforeach; ?>

<?php render('footer'); ?>
