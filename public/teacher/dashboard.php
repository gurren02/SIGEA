<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('teacher');
$students = User::studentsByTeacher($user['id']);
$exams = Exam::byTeacher($user['id']);
$stats = Exam::statsByUnitForTeacher($user['id']);
$pageTitle = 'Panel docente';
render('header', compact('pageTitle'));
?>
<section class="metric-grid">
    <article class="metric"><span>Alumnos asignados</span><strong><?= count($students) ?></strong></article>
    <article class="metric"><span>Examenes creados</span><strong><?= count($exams) ?></strong></article>
    <article class="metric"><span>Unidades evaluadas</span><strong><?= count($stats) ?></strong></article>
</section>
<section class="panel">
    <h2>Desempeno por unidad</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Unidad</th><th>Respuestas</th><th>Precision</th></tr></thead>
            <tbody>
            <?php foreach ($stats as $row): ?>
                <tr><td>UNIDAD <?= (int) $row['unit'] ?></td><td><?= (int) $row['answers_count'] ?></td><td><?= e($row['accuracy']) ?>%</td></tr>
            <?php endforeach; ?>
            <?php if (!$stats): ?><tr><td colspan="3">Aun no hay resultados.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render('footer'); ?>
