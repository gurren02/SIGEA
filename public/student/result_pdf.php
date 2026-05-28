<?php
require_once __DIR__ . '/../../app/Core/bootstrap.php';
$user = require_role('student');
$attemptId = (int) ($_GET['id'] ?? 0);
$result = null;
foreach (Exam::resultsForStudent($user['id']) as $row) {
    if ((int) $row['id'] === $attemptId) {
        $result = $row;
        break;
    }
}

if (!$result) {
    redirect('/student/results.php');
}

$lines = [
    'Estudiante: ' . $user['name'],
    'Examen: ' . $result['title'],
    'Materia: ' . $result['subject_name'],
    'Unidad: UNIDAD ' . $result['unit'],
    'Docente: ' . $result['teacher_name'],
    'Calificacion: ' . $result['score'] . ' / ' . $result['total_score'],
    'Validacion: ' . ($result['validated_at'] ? 'Validado' : 'Pendiente'),
    'Fecha de envio: ' . $result['submitted_at'],
];

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="resultado-sigea.pdf"');
echo SimplePdf::output('Resultado SIGEA', $lines);
