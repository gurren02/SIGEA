<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('student');
$teacher = User::teacherForStudent($user['id']);
$exams = Exam::forStudent($user['id']);
$pageTitle = 'Examenes por hacer';
render('header', compact('pageTitle'));
?>
<section class="panel">
    <h2>Docente asignado</h2>
    <p class="muted"><?= $teacher ? e($teacher['name'] . ' - ' . $teacher['email']) : 'Aun no tienes docente asignado.' ?></p>
</section>
<section class="panel">
    <h2>Lista de examenes</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Examen</th><th>Materia</th><th>Unidad</th><th>Docente</th><th>Estado</th><th>Accion</th></tr></thead>
            <tbody>
            <?php foreach ($exams as $exam): ?>
                <tr>
                    <td><?= e($exam['title']) ?></td>
                    <td><?= e($exam['subject_name']) ?></td>
                    <td>UNIDAD <?= (int) $exam['unit'] ?></td>
                    <td><?= e($exam['teacher_name']) ?></td>
                    <td><?= $exam['attempt_id'] ? 'Contestado' : 'Pendiente' ?></td>
                    <td>
                        <?php if (!$exam['attempt_id']): ?>
                            <a class="button button-primary" href="/student/take_exam.php?id=<?= (int) $exam['id'] ?>">Responder</a>
                        <?php else: ?>
                            <span class="pill">Calificado</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$exams): ?><tr><td colspan="6">No hay examenes disponibles.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render('footer'); ?>
