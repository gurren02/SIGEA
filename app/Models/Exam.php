<?php

class Exam
{
    public static function units(): array
    {
        return [1, 2, 3, 4, 5];
    }

    public static function subjects(): array
    {
        return Database::get()->query('SELECT * FROM subjects ORDER BY name')->fetchAll();
    }

    public static function addBankQuestion(int $teacherId, array $data): void
    {
        $db = Database::get();
        $subjectId = (int) ($data['subject_id'] ?? 0);
        $unit = (int) ($data['unit'] ?? 0);
        $type = ($data['type'] ?? '') === 'true_false' ? 'true_false' : 'multiple_choice';

        if (!in_array($unit, self::units(), true) || $subjectId <= 0 || trim($data['text'] ?? '') === '') {
            throw new RuntimeException('Selecciona materia, unidad y texto de la pregunta.');
        }

        $options = $type === 'true_false'
            ? ['Verdadero', 'Falso']
            : array_values(array_filter($data['options'] ?? [], fn ($option) => trim((string) $option) !== ''));

        if (count($options) < 2) {
            throw new RuntimeException('Agrega al menos dos opciones para la pregunta.');
        }

        $db->beginTransaction();
        $stmt = $db->prepare(
            'INSERT INTO question_bank (teacher_id, subject_id, unit, text, type, score) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $teacherId,
            $subjectId,
            $unit,
            trim($data['text']),
            $type,
            max(1, (float) ($data['score'] ?? 1)),
        ]);
        $bankQuestionId = (int) $db->lastInsertId();

        $correctIndexes = is_array($data['correct'] ?? null)
            ? array_map('intval', $data['correct'])
            : [(int) ($data['correct'] ?? 0)];

        $oStmt = $db->prepare(
            'INSERT INTO question_bank_options (bank_question_id, option_text, is_correct) VALUES (?, ?, ?)'
        );
        foreach ($options as $index => $optionText) {
            $isCorrect = in_array($index, $correctIndexes, true) ? 1 : 0;
            $oStmt->execute([$bankQuestionId, trim($optionText), $isCorrect]);
        }

        $db->commit();
    }

    public static function bankByTeacher(int $teacherId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT qb.*, s.name AS subject_name
             FROM question_bank qb
             INNER JOIN subjects s ON s.id = qb.subject_id
             WHERE qb.teacher_id = ?
             ORDER BY qb.subject_id, qb.unit, qb.created_at DESC"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public static function bankByTeacherForUnit(int $teacherId, int $subjectId, int $unit): array
    {
        $stmt = Database::get()->prepare(
            "SELECT qb.*, s.name AS subject_name
             FROM question_bank qb
             INNER JOIN subjects s ON s.id = qb.subject_id
             WHERE qb.teacher_id = ? AND qb.subject_id = ? AND qb.unit = ?
             ORDER BY qb.created_at DESC"
        );
        $stmt->execute([$teacherId, $subjectId, $unit]);
        return $stmt->fetchAll();
    }

    public static function createFromBank(int $teacherId, array $data): int
    {
        $db = Database::get();
        $subjectId = (int) ($data['subject_id'] ?? 0);
        $unit = (int) ($data['unit'] ?? 0);
        $count = max(1, (int) ($data['count'] ?? 1));
        $mode = $data['selection_mode'] ?? 'random';
        $selectedIds = array_map('intval', $data['bank_questions'] ?? []);

        if (!in_array($unit, self::units(), true) || $subjectId <= 0 || trim($data['title'] ?? '') === '') {
            throw new RuntimeException('Completa titulo, materia y unidad del examen.');
        }

        $params = [$teacherId, $subjectId, $unit];
        $where = 'teacher_id = ? AND subject_id = ? AND unit = ?';

        if (!$selectedIds) {
            throw new RuntimeException('Selecciona al menos una pregunta del banco para generar el examen.');
        }

        if ($mode === 'manual') {
            $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
            $where .= " AND id IN ($placeholders)";
            $params = array_merge($params, $selectedIds);
            $order = 'ORDER BY FIELD(id, ' . $placeholders . ')';
            $params = array_merge($params, $selectedIds);
        } else {
            if ($selectedIds) {
                $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
                $where .= " AND id IN ($placeholders)";
                $params = array_merge($params, $selectedIds);
            }
            $order = 'ORDER BY RAND()';
        }

        $sql = "SELECT * FROM question_bank WHERE $where $order LIMIT $count";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $bankQuestions = $stmt->fetchAll();

        if (count($bankQuestions) < $count) {
            throw new RuntimeException('No hay suficientes preguntas disponibles para esa seleccion.');
        }

        $db->beginTransaction();
        $examStmt = $db->prepare(
            'INSERT INTO exams (teacher_id, subject_id, unit, title, description, is_published) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $examStmt->execute([
            $teacherId,
            $subjectId,
            $unit,
            trim($data['title']),
            $data['description'] ?? null,
            isset($data['is_published']) ? 1 : 0,
        ]);
        $examId = (int) $db->lastInsertId();

        $studentIds = array_map('intval', $data['students'] ?? []);
        if (!$studentIds) {
            throw new RuntimeException('Selecciona al menos un estudiante para asignar el examen.');
        }

        $esStmt = $db->prepare('INSERT INTO exam_students (exam_id, student_id) VALUES (?, ?)');
        foreach ($studentIds as $studentId) {
            $esStmt->execute([$examId, $studentId]);
        }

        self::copyBankQuestionsToExam($bankQuestions, $examId);
        $db->commit();
        return $examId;
    }

    private static function copyBankQuestionsToExam(array $bankQuestions, int $examId): void
    {
        $db = Database::get();
        $bankOptionStmt = $db->prepare('SELECT * FROM question_bank_options WHERE bank_question_id = ? ORDER BY id');
        $qStmt = $db->prepare('INSERT INTO questions (exam_id, unit, text, type, score) VALUES (?, ?, ?, ?, ?)');
        $oStmt = $db->prepare('INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)');

        foreach ($bankQuestions as $question) {
            $qStmt->execute([$examId, $question['unit'], $question['text'], $question['type'], $question['score']]);
            $questionId = (int) $db->lastInsertId();
            $bankOptionStmt->execute([$question['id']]);
            foreach ($bankOptionStmt->fetchAll() as $option) {
                $oStmt->execute([$questionId, $option['option_text'], $option['is_correct']]);
            }
        }
    }

    public static function byTeacher(int $teacherId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT e.*, s.name AS subject_name, COUNT(q.id) AS questions_count
             FROM exams e
             INNER JOIN subjects s ON s.id = e.subject_id
             LEFT JOIN questions q ON q.exam_id = e.id
             WHERE e.teacher_id = ?
             GROUP BY e.id
             ORDER BY e.created_at DESC"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT e.*, s.name AS subject_name, u.name AS teacher_name, a.id AS attempt_id, a.score, a.total_score
             FROM exams e
             INNER JOIN subjects s ON s.id = e.subject_id
             INNER JOIN users u ON u.id = e.teacher_id
             INNER JOIN exam_students es ON es.exam_id = e.id AND es.student_id = ?
             LEFT JOIN exam_attempts a ON a.exam_id = e.id AND a.student_id = ?
             WHERE e.is_published = 1
             ORDER BY e.created_at DESC"
        );
        $stmt->execute([$studentId, $studentId]);
        return $stmt->fetchAll();
    }

    public static function findFull(int $examId): ?array
    {
        $stmt = Database::get()->prepare(
            "SELECT e.*, s.name AS subject_name, u.name AS teacher_name
             FROM exams e
             INNER JOIN subjects s ON s.id = e.subject_id
             INNER JOIN users u ON u.id = e.teacher_id
             WHERE e.id = ?"
        );
        $stmt->execute([$examId]);
        $exam = $stmt->fetch();
        if (!$exam) {
            return null;
        }

        $qStmt = Database::get()->prepare('SELECT * FROM questions WHERE exam_id = ? ORDER BY id');
        $qStmt->execute([$examId]);
        $questions = $qStmt->fetchAll();
        $oStmt = Database::get()->prepare('SELECT * FROM question_options WHERE question_id = ? ORDER BY id');
        foreach ($questions as &$question) {
            $oStmt->execute([$question['id']]);
            $question['options'] = $oStmt->fetchAll();
        }
        $exam['questions'] = $questions;
        return $exam;
    }

    public static function submit(int $examId, int $studentId, array $answers): int
    {
        if (self::attemptFor($examId, $studentId)) {
            throw new RuntimeException('Este examen ya fue contestado.');
        }

        $exam = self::findFull($examId);
        if (!$exam) {
            throw new RuntimeException('Examen no encontrado.');
        }

        $db = Database::get();
        $db->beginTransaction();
        $total = array_sum(array_map(fn ($question) => (float) $question['score'], $exam['questions']));
        $score = 0;

        $attemptStmt = $db->prepare(
            'INSERT INTO exam_attempts (exam_id, student_id, score, total_score) VALUES (?, ?, 0, ?)'
        );
        $attemptStmt->execute([$examId, $studentId, $total]);
        $attemptId = (int) $db->lastInsertId();

        $answerStmt = $db->prepare(
            'INSERT INTO exam_answers (attempt_id, question_id, option_id, is_correct, points) VALUES (?, ?, ?, ?, ?)'
        );

        foreach ($exam['questions'] as $question) {
            $correctOptionIds = [];
            foreach ($question['options'] as $option) {
                if ((int) $option['is_correct'] === 1) {
                    $correctOptionIds[] = (int) $option['id'];
                }
            }
            
            $selected = isset($answers[$question['id']]) ? $answers[$question['id']] : [];
            if (!is_array($selected)) {
                $selected = ($selected === '' || $selected === null) ? [] : [$selected];
            }
            $selectedIds = array_map('intval', $selected);
            
            sort($correctOptionIds);
            sort($selectedIds);
            $isCorrect = ($correctOptionIds === $selectedIds) ? 1 : 0;
            $points = $isCorrect ? (float) $question['score'] : 0.00;
            $score += $points;
            
            if (empty($selectedIds)) {
                $answerStmt->execute([$attemptId, $question['id'], null, 0, 0.00]);
            } else {
                foreach ($selectedIds as $i => $optId) {
                    $rowPoints = ($i === 0) ? $points : 0.00;
                    $answerStmt->execute([$attemptId, $question['id'], $optId, $isCorrect, $rowPoints]);
                }
            }
        }

        $db->prepare('UPDATE exam_attempts SET score = ? WHERE id = ?')->execute([$score, $attemptId]);
        $db->commit();
        return $attemptId;
    }

    public static function attemptFor(int $examId, int $studentId): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM exam_attempts WHERE exam_id = ? AND student_id = ?');
        $stmt->execute([$examId, $studentId]);
        $attempt = $stmt->fetch();
        return $attempt ?: null;
    }

    public static function resultsForStudent(int $studentId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT a.*, e.title, e.unit, s.name AS subject_name, u.name AS teacher_name
             FROM exam_attempts a
             INNER JOIN exams e ON e.id = a.exam_id
             INNER JOIN subjects s ON s.id = e.subject_id
             INNER JOIN users u ON u.id = e.teacher_id
             WHERE a.student_id = ?
             ORDER BY a.submitted_at DESC"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function resultsForTeacher(int $teacherId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT a.*, e.title, e.unit, s.name AS subject_name, u.name AS student_name
             FROM exam_attempts a
             INNER JOIN exams e ON e.id = a.exam_id
             INNER JOIN subjects s ON s.id = e.subject_id
             INNER JOIN users u ON u.id = a.student_id
             WHERE e.teacher_id = ?
             ORDER BY a.submitted_at DESC"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public static function attemptFullDetail(int $attemptId): ?array
    {
        $db = Database::get();

        // Fetch the attempt with exam + student + subject + teacher info
        $stmt = $db->prepare(
            "SELECT a.*, a.id AS attempt_id,
                    e.title, e.unit, e.description AS exam_description,
                    s.name AS subject_name,
                    u_t.name AS teacher_name,
                    u_s.name AS student_name,
                    u_s.email AS student_email
             FROM exam_attempts a
             INNER JOIN exams e ON e.id = a.exam_id
             INNER JOIN subjects s ON s.id = e.subject_id
             INNER JOIN users u_t ON u_t.id = e.teacher_id
             INNER JOIN users u_s ON u_s.id = a.student_id
             WHERE a.id = ?"
        );
        $stmt->execute([$attemptId]);
        $attempt = $stmt->fetch();
        if (!$attempt) {
            return null;
        }

        // Fetch all questions for this exam
        $qStmt = $db->prepare('SELECT * FROM questions WHERE exam_id = ? ORDER BY id');
        $qStmt->execute([$attempt['exam_id']]);
        $questions = $qStmt->fetchAll();

        // For each question: fetch options and the student's selected answers
        $oStmt = $db->prepare('SELECT * FROM question_options WHERE question_id = ? ORDER BY id');
        $ansStmt = $db->prepare(
            'SELECT option_id, is_correct, points FROM exam_answers WHERE attempt_id = ? AND question_id = ?'
        );

        foreach ($questions as &$question) {
            $oStmt->execute([$question['id']]);
            $question['options'] = $oStmt->fetchAll();

            $ansStmt->execute([$attemptId, $question['id']]);
            $rows = $ansStmt->fetchAll();

            $selectedIds = [];
            $isCorrect = 0;
            $pointsEarned = 0;
            foreach ($rows as $row) {
                if ($row['option_id'] !== null) {
                    $selectedIds[] = (int) $row['option_id'];
                }
                $isCorrect = (int) $row['is_correct'];
                $pointsEarned += (float) $row['points'];
            }

            $question['selected_option_ids'] = $selectedIds;
            $question['is_correct']          = $isCorrect;
            $question['points_earned']       = $pointsEarned;
        }
        unset($question);

        $attempt['questions'] = $questions;
        return $attempt;
    }

    public static function validateAttempt(int $attemptId, int $teacherId): void
    {
        $stmt = Database::get()->prepare(
            "UPDATE exam_attempts a
             INNER JOIN exams e ON e.id = a.exam_id
             SET a.validated_at = NOW(), a.validated_by = ?
             WHERE a.id = ? AND e.teacher_id = ?"
        );
        $stmt->execute([$teacherId, $attemptId, $teacherId]);
    }

    public static function statsByUnitForTeacher(int $teacherId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT q.unit,
                    COUNT(ans.id) AS answers_count,
                    ROUND(AVG(ans.is_correct) * 100, 1) AS accuracy
             FROM exam_answers ans
             INNER JOIN questions q ON q.id = ans.question_id
             INNER JOIN exams e ON e.id = q.exam_id
             WHERE e.teacher_id = ?
             GROUP BY q.unit
             ORDER BY q.unit"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }
}
