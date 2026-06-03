<?php
require_once __DIR__ . '/bootstrap.php';

try {
    $db = Database::get();
    
    // 1. Create table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS exam_students (
            exam_id INT NOT NULL,
            student_id INT NOT NULL,
            PRIMARY KEY (exam_id, student_id),
            CONSTRAINT fk_exam_students_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
            CONSTRAINT fk_exam_students_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "Table 'exam_students' created or already exists.\n";
    
    // 2. Backfill existing exams
    $inserted = $db->exec("
        INSERT IGNORE INTO exam_students (exam_id, student_id)
        SELECT e.id, ts.student_id
        FROM exams e
        INNER JOIN teacher_students ts ON ts.teacher_id = e.teacher_id
    ");
    echo "Backfilled $inserted records into 'exam_students' for existing exams.\n";
    
} catch (Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
