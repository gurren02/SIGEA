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

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="resultado-sigea.pdf"');
echo SimplePdf::outputResult($user, $result);
