<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('teacher');

if (is_post()) {
    Exam::validateAttempt((int) $_POST['attempt_id'], $user['id']);
    flash('success', 'Resultado validado.');
    redirect('/teacher/results.php');
}

$results = Exam::resultsForTeacher($user['id']);
$stats = Exam::statsByUnitForTeacher($user['id']);
$pageTitle = 'Validar resultados';
render('header', compact('pageTitle'));
?>
<section class="panel">
    <h2>Resultados de estudiantes</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Alumno</th><th>Examen</th><th>Materia</th><th>Unidad</th><th>Calificacion</th><th>Estado</th><th>Accion</th></tr></thead>
            <tbody>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?= e($result['student_name']) ?></td>
                    <td><?= e($result['title']) ?></td>
                    <td><?= e($result['subject_name']) ?></td>
                    <td>UNIDAD <?= (int) $result['unit'] ?></td>
                    <td><?= e($result['score']) ?>/<?= e($result['total_score']) ?></td>
                    <td><?= $result['validated_at'] ? 'Validado' : 'Pendiente' ?></td>
                    <td>
                        <?php if (!$result['validated_at']): ?>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="attempt_id" value="<?= (int) $result['id'] ?>">
                                <button class="button button-primary" type="submit">Validar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$results): ?><tr><td colspan="7">Aun no hay resultados.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<section class="panel">
    <h2>Estadisticas por unidad</h2>
    <div class="stats-bars">
        <?php foreach ($stats as $row): ?>
            <div>
                <span>UNIDAD <?= (int) $row['unit'] ?> (<?= e($row['accuracy']) ?>%)</span>
                <meter min="0" max="100" value="<?= e($row['accuracy']) ?>"></meter>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php render('footer'); ?>
