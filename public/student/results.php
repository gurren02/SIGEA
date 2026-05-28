<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('student');
$results = Exam::resultsForStudent($user['id']);
$pageTitle = 'Resultados';
render('header', compact('pageTitle'));
?>
<section class="panel">
    <h2>Mis resultados</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Examen</th><th>Materia</th><th>Unidad</th><th>Docente</th><th>Calificacion</th><th>Validacion</th><th>PDF</th></tr></thead>
            <tbody>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?= e($result['title']) ?></td>
                    <td><?= e($result['subject_name']) ?></td>
                    <td>UNIDAD <?= (int) $result['unit'] ?></td>
                    <td><?= e($result['teacher_name']) ?></td>
                    <td><?= e($result['score']) ?>/<?= e($result['total_score']) ?></td>
                    <td><?= $result['validated_at'] ? 'Validado' : 'Pendiente' ?></td>
                    <td><a class="button button-warning" href="/student/result_pdf.php?id=<?= (int) $result['id'] ?>">Exportar PDF</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$results): ?><tr><td colspan="7">Aun no tienes resultados.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render('footer'); ?>
