<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('teacher');

$attemptId = (int) ($_GET['attempt_id'] ?? ($_POST['attempt_id'] ?? 0));
$db = Database::get();

// Fetch the attempt details
$stmt = $db->prepare("
    SELECT a.*, e.title, e.unit, s.name AS subject_name, student.name AS student_name, student.email AS student_email
    FROM exam_attempts a
    INNER JOIN exams e ON e.id = a.exam_id
    INNER JOIN subjects s ON s.id = e.subject_id
    INNER JOIN users student ON student.id = a.student_id
    WHERE a.id = ? AND e.teacher_id = ?
");
$stmt->execute([$attemptId, $user['id']]);
$attempt = $stmt->fetch();

if (!$attempt) {
    flash('error', 'Resultado no encontrado o no tienes permisos para revisarlo.');
    redirect('/teacher/results.php');
}

// Handle POST actions
if (is_post()) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'toggle_answer') {
        $questionId = (int) ($_POST['question_id'] ?? 0);
        $isCorrect = (int) ($_POST['is_correct'] ?? 0) === 1 ? 1 : 0;
        
        // Find all answer rows for this question in this attempt
        $aStmt = $db->prepare("
            SELECT ans.*, q.score AS question_score 
            FROM exam_answers ans
            INNER JOIN questions q ON q.id = ans.question_id
            WHERE q.id = ? AND ans.attempt_id = ?
            ORDER BY ans.id
        ");
        $aStmt->execute([$questionId, $attemptId]);
        $rows = $aStmt->fetchAll();
        
        if ($rows) {
            $questionScore = (float) $rows[0]['question_score'];
            $upStmt = $db->prepare("UPDATE exam_answers SET is_correct = ?, points = ? WHERE id = ?");
            foreach ($rows as $i => $row) {
                // Only the first row gets the points
                $points = ($isCorrect === 1 && $i === 0) ? $questionScore : 0.00;
                $upStmt->execute([$isCorrect, $points, $row['id']]);
            }
            
            // Update the total score of the attempt
            $db->prepare("UPDATE exam_attempts SET score = (SELECT SUM(points) FROM exam_answers WHERE attempt_id = ?) WHERE id = ?")->execute([$attemptId, $attemptId]);
            flash('success', 'Puntuación y validez de la pregunta actualizadas.');
        }
        redirect('/teacher/validate.php?attempt_id=' . $attemptId);
    }
    
    if ($action === 'validate_attempt') {
        Exam::validateAttempt($attemptId, $user['id']);
        flash('success', 'Resultado del examen validado y finalizado correctamente.');
        redirect('/teacher/results.php');
    }
}

// Fetch answers and their options (grouped by question_id)
$qStmt = $db->prepare("
    SELECT ans.*, q.text AS question_text, q.type AS question_type, q.score AS question_score
    FROM exam_answers ans
    INNER JOIN questions q ON q.id = ans.question_id
    WHERE ans.attempt_id = ?
    ORDER BY ans.id
");
$qStmt->execute([$attemptId]);
$rawAnswers = $qStmt->fetchAll();

$answers = [];
foreach ($rawAnswers as $raw) {
    $qId = (int) $raw['question_id'];
    if (!isset($answers[$qId])) {
        $answers[$qId] = $raw;
        $answers[$qId]['selected_options'] = [];
    }
    if ($raw['option_id'] !== null) {
        $answers[$qId]['selected_options'][] = (int) $raw['option_id'];
    }
}
$answers = array_values($answers);

$oStmt = $db->prepare("SELECT * FROM question_options WHERE question_id = ? ORDER BY id");
foreach ($answers as &$answer) {
    $oStmt->execute([$answer['question_id']]);
    $answer['options'] = $oStmt->fetchAll();
}
unset($answer);

$pageTitle = 'Validar examen';
render('header', compact('pageTitle'));
?>

<div class="content-grid wide-left">
    <div>
        <section class="panel">
            <div class="section-head">
                <div>
                    <h2>Respuestas del Estudiante</h2>
                    <p class="muted">Revisa las respuestas enviadas. Puedes forzar la validación de respuestas individuales.</p>
                </div>
            </div>
            
            <div style="display:flex; flex-direction:column; gap:20px; margin-top:20px;">
                <?php foreach ($answers as $index => $answer): 
                    $isCorrect = (int) $answer['is_correct'] === 1;
                ?>
                    <article class="panel" style="background: rgba(255,255,255,.50); border: 1px solid rgba(26,53,96,.10); margin-bottom: 0;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:12px;">
                            <div>
                                <span class="pill" style="margin-bottom:8px;">Pregunta <?= $index + 1 ?></span>
                                <strong style="display:block; font-size:16px; color:var(--navy);"><?= e($answer['question_text']) ?></strong>
                            </div>
                            <span class="selected-count" style="background:#fff;"><?= number_format($answer['question_score'], 2) ?> pts</span>
                        </div>
                        
                        <div style="margin: 14px 0;">
                            <?php if ($answer['question_type'] === 'true_false'): ?>
                                <?php 
                                    foreach ($answer['options'] as $option):
                                        $isChosen = in_array((int) $option['id'], $answer['selected_options'], true);
                                        $isCorrectOpt = (int) $option['is_correct'] === 1;
                                        
                                        $style = "border: 1px solid rgba(26,53,96,.12); padding: 10px; border-radius: var(--radius-sm); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;";
                                        $icon = "radio_button_unchecked";
                                        $badge = "";
                                        
                                        if ($isChosen) {
                                            $icon = "radio_button_checked";
                                            if ($isCorrectOpt) {
                                                $style = "border: 2px solid var(--green); background: rgba(34,197,94,.08); padding: 10px; border-radius: var(--radius-sm); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: var(--green-deep); font-weight:700;";
                                                $badge = "<span class='role-badge role-badge--student' style='margin-left:auto;'>Correcta (Seleccionada)</span>";
                                            } else {
                                                $style = "border: 2px solid var(--red); background: rgba(239,68,68,.08); padding: 10px; border-radius: var(--radius-sm); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: #991b1b; font-weight:700;";
                                                $badge = "<span class='role-badge role-badge--admin' style='background:rgba(239,68,68,.10); color:#991b1b; border:1px solid rgba(239,68,68,.20); margin-left:auto;'>Incorrecta (Seleccionada)</span>";
                                            }
                                        } else if ($isCorrectOpt) {
                                            $style = "border: 2px dashed var(--green); padding: 10px; border-radius: var(--radius-sm); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: var(--green-deep);";
                                            $badge = "<span class='role-badge role-badge--student' style='margin-left:auto;'>Respuesta Correcta</span>";
                                        }
                                ?>
                                    <div style="<?= $style ?>">
                                        <span class="material-symbols-rounded" style="color:inherit;"><?= $icon ?></span>
                                        <span><?= e($option['option_text']) ?></span>
                                        <?= $badge ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($answer['options'] as $option):
                                    $isChosen = in_array((int) $option['id'], $answer['selected_options'], true);
                                    $isCorrectOpt = (int) $option['is_correct'] === 1;
                                    
                                    $style = "border: 1px solid rgba(26,53,96,.12); padding: 10px; border-radius: var(--radius-sm); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;";
                                    $icon = "check_box_outline_blank";
                                    $badge = "";
                                    
                                    if ($isChosen) {
                                        $icon = "check_box";
                                        if ($isCorrectOpt) {
                                            $style = "border: 2px solid var(--green); background: rgba(34,197,94,.08); padding: 10px; border-radius: var(--radius-sm); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: var(--green-deep); font-weight:700;";
                                            $badge = "<span class='role-badge role-badge--student' style='margin-left:auto;'>Correcta (Seleccionada)</span>";
                                        } else {
                                            $style = "border: 2px solid var(--red); background: rgba(239,68,68,.08); padding: 10px; border-radius: var(--radius-sm); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: #991b1b; font-weight:700;";
                                            $badge = "<span class='role-badge role-badge--admin' style='background:rgba(239,68,68,.10); color:#991b1b; border:1px solid rgba(239,68,68,.20); margin-left:auto;'>Incorrecta (Seleccionada)</span>";
                                        }
                                    } else if ($isCorrectOpt) {
                                        $style = "border: 2px dashed var(--green); padding: 10px; border-radius: var(--radius-sm); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: var(--green-deep);";
                                        $badge = "<span class='role-badge role-badge--student' style='margin-left:auto;'>Respuesta Correcta</span>";
                                    }
                                ?>
                                    <div style="<?= $style ?>">
                                        <span class="material-symbols-rounded" style="color:inherit;"><?= $icon ?></span>
                                        <span><?= e($option['option_text']) ?></span>
                                        <?= $badge ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div style="border-top: 1px solid rgba(26,53,96,.08); padding-top:12px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <?php if ($isCorrect): ?>
                                    <span class="material-symbols-rounded" style="color:var(--green); font-size:18px;">check_circle</span>
                                    <span style="font-size:13px; font-weight:700; color:var(--green-deep);">Considerada correcta (+<?= number_format($answer['points'], 2) ?> pts)</span>
                                <?php else: ?>
                                    <span class="material-symbols-rounded" style="color:var(--red); font-size:18px;">cancel</span>
                                    <span style="font-size:13px; font-weight:700; color:#991b1b;">Considerada incorrecta (0.00 pts)</span>
                                <?php endif; ?>
                            </div>
                            
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="action" value="toggle_answer">
                                <input type="hidden" name="question_id" value="<?= (int) $answer['question_id'] ?>">
                                <input type="hidden" name="is_correct" value="<?= $isCorrect ? 0 : 1 ?>">
                                <?php if ($isCorrect): ?>
                                    <button class="button button-ghost" type="submit" style="min-height:32px; padding:4px 10px; font-size:12px; border-color:rgba(239,68,68,.30); color:var(--red); background:rgba(239,68,68,.05);">
                                        <span class="material-symbols-rounded" style="font-size:16px;">close</span>
                                        Marcar como incorrecta
                                    </button>
                                <?php else: ?>
                                    <button class="button button-ghost" type="submit" style="min-height:32px; padding:4px 10px; font-size:12px; border-color:rgba(34,197,94,.30); color:var(--green-deep); background:rgba(34,197,94,.05);">
                                        <span class="material-symbols-rounded" style="font-size:16px;">check</span>
                                        Marcar como correcta
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    
    <div>
        <section class="panel" style="position:sticky; top:28px;">
            <h3>Detalles del Intento</h3>
            <div style="margin: 16px 0; display:flex; flex-direction:column; gap:10px; font-size:14px;">
                <p style="margin:0;"><strong>Alumno:</strong><br><span class="muted"><?= e($attempt['student_name']) ?></span></p>
                <p style="margin:0;"><strong>Examen:</strong><br><span class="muted"><?= e($attempt['title']) ?> (Unidad <?= (int) $attempt['unit'] ?>)</span></p>
                <p style="margin:0;"><strong>Materia:</strong><br><span class="muted"><?= e($attempt['subject_name']) ?></span></p>
                <p style="margin:0;"><strong>Fecha de envío:</strong><br><span class="muted"><?= date('d/m/Y H:i', strtotime($attempt['submitted_at'])) ?></span></p>
            </div>
            
            <div style="border-top: 1px solid rgba(26,53,96,.10); padding-top:16px; margin-top:16px;">
                <h4 style="margin-bottom:8px;">Calificación Actual</h4>
                <div style="display:flex; align-items:baseline; gap:4px; margin-bottom:8px;">
                    <strong style="font-size:32px; color:var(--navy);"><?= number_format($attempt['score'], 2) ?></strong>
                    <span class="muted">/ <?= number_format($attempt['total_score'], 2) ?> pts</span>
                </div>
                <?php 
                    $pct = $attempt['total_score'] > 0 ? ($attempt['score'] / $attempt['total_score']) * 100 : 0;
                ?>
                <meter min="0" max="100" value="<?= $pct ?>" style="width:100%; height:8px; border-radius:10px; overflow:hidden; margin-bottom:12px; display:block;"></meter>
                
                <div style="margin-top: 16px; display:flex; flex-direction:column; gap:10px;">
                    <?php if ($attempt['validated_at']): ?>
                        <div class="alert alert-success" style="margin:0; font-size:13px; padding:10px;">
                            Validado el <?= date('d/m/Y', strtotime($attempt['validated_at'])) ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger" style="background:rgba(245,158,11,.10); border:1px solid rgba(245,158,11,.25); color:#b45309; margin:0; font-size:13px; padding:10px;">
                            Pendiente de validación
                        </div>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="action" value="validate_attempt">
                            <button class="button button-primary full" type="submit" style="margin-top:6px;">
                                <span class="material-symbols-rounded">check_circle</span>
                                Finalizar Validación
                            </button>
                        </form>
                    <?php endif; ?>
                    <a class="button button-ghost full" href="/teacher/results.php">
                        <span class="material-symbols-rounded">arrow_back</span>
                        Regresar a resultados
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>

<?php render('footer'); ?>
