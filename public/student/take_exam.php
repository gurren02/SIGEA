<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('student');
$examId = (int) ($_GET['id'] ?? 0);
$exam = Exam::findFull($examId);
$teacher = User::teacherForStudent($user['id']);

if (!$exam || !$teacher || (int) $exam['teacher_id'] !== (int) $teacher['id'] || Exam::attemptFor($examId, $user['id'])) {
    flash('error', 'El examen no esta disponible.');
    redirect('/student/exams.php');
}

if (is_post()) {
    try {
        Exam::submit($examId, $user['id'], $_POST['answers'] ?? []);
        flash('success', 'Examen enviado y calificado automaticamente.');
        redirect('/student/results.php');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        redirect('/student/exams.php');
    }
}

$pageTitle = 'Responder examen';
render('header', compact('pageTitle'));
?>
<form class="panel exam-sheet" method="post">
    <h2><?= e($exam['title']) ?></h2>
    <p class="muted"><?= e($exam['subject_name']) ?> | UNIDAD <?= (int) $exam['unit'] ?> | <?= e($exam['teacher_name']) ?></p>
    <?php foreach ($exam['questions'] as $index => $question): ?>
        <fieldset class="question-block">
            <legend><?= ($index + 1) ?>. <?= e($question['text']) ?></legend>
            <p class="muted">UNIDAD <?= (int) $question['unit'] ?> | Puntos: <?= e($question['score']) ?></p>
            <?php foreach ($question['options'] as $option): ?>
                <label class="option">
                    <input type="radio" name="answers[<?= (int) $question['id'] ?>]" value="<?= (int) $option['id'] ?>" required>
                    <?= e($option['option_text']) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
    <?php endforeach; ?>
    <button class="button button-primary" type="submit">Enviar respuestas</button>
</form>
<?php render('footer'); ?>
