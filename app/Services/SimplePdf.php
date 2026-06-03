<?php

class SimplePdf
{
    // Keeping output for backwards compatibility or general text output
    public static function output(string $title, array $lines): string
    {
        require_once __DIR__ . '/fpdf/fpdf.php';
        
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', 'B', 16);
        // Header color / design
        $pdf->SetTextColor(26, 53, 96);
        $pdf->Cell(0, 10, self::toIso($title), 0, 1, 'L');
        $pdf->Ln(5);
        $pdf->SetDrawColor(26, 53, 96);
        $pdf->Line(10, 22, 200, 22);
        $pdf->Ln(10);
        
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetTextColor(50, 50, 50);
        foreach ($lines as $line) {
            $pdf->Cell(0, 8, self::toIso($line), 0, 1, 'L');
        }
        
        return $pdf->Output('S');
    }
    
    // Beautifully designed exam result PDF
    public static function outputResult(array $user, array $result): string
    {
        require_once __DIR__ . '/fpdf/fpdf.php';
        
        // Dynamic verification code
        $validationCode = self::generateValidationCode((int)$result['id']);
        
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false); // Disable automatic page breaking to prevent page 2 spill
        $pdf->AddPage();
        $pdf->SetMargins(15, 15, 15);
        
        // 1. Navy top bar accent
        $pdf->SetFillColor(26, 53, 96); // #1A3560
        $pdf->Rect(0, 0, 210, 8, 'F');
        
        // 2. Logo and Header Title
        $logoPath = __DIR__ . '/../../public/logo.png';
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 14, 22);
        }
        
        // Header Text
        $pdf->SetTextColor(26, 53, 96);
        $pdf->SetFont('Helvetica', 'B', 15);
        $pdf->SetXY(42, 14);
        $pdf->Cell(0, 5, self::toIso('SIGEA'), 0, 1, 'L');
        
        $pdf->SetTextColor(80, 90, 105);
        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->SetXY(42, 19);
        $pdf->Cell(0, 4, self::toIso('Sistema de Generación y Evaluación Automática de Exámenes'), 0, 1, 'L');
        
        $pdf->SetTextColor(26, 53, 96);
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetXY(42, 24);
        $pdf->Cell(0, 5, self::toIso('REPORTE DE RESULTADOS DE EVALUACIÓN'), 0, 1, 'L');
        
        // Thin gray line
        $pdf->SetDrawColor(220, 225, 235);
        $pdf->Line(15, 33, 195, 33);
        
        // 3. Information Container (Table)
        $pdf->SetY(38);
        $pdf->SetFillColor(240, 247, 255); // Light Blue background
        $pdf->SetDrawColor(180, 200, 230); // Border color
        $pdf->Rect(15, 38, 180, 48, 'DF');
        
        $pdf->SetTextColor(50, 50, 50);
        $pdf->SetFont('Helvetica', '', 9.5);
        
        // Let's write the values using standard cells
        // Row 1: Student info
        $pdf->SetXY(20, 42);
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->Cell(25, 5, self::toIso('Estudiante:'), 0, 0);
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->Cell(65, 5, self::toIso($user['name']), 0, 0);
        
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->Cell(25, 5, self::toIso('Examen:'), 0, 0);
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->Cell(65, 5, self::toIso($result['title']), 0, 1);
        
        // Row 2: ID and Subject
        $pdf->SetXY(20, 50);
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->Cell(25, 5, self::toIso('Matrícula:'), 0, 0);
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->Cell(65, 5, self::toIso($user['institutional_id'] ?: 'N/A'), 0, 0);
        
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->Cell(25, 5, self::toIso('Materia:'), 0, 0);
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->Cell(65, 5, self::toIso($result['subject_name']), 0, 1);
        
        // Row 3: Teacher and Unit
        $pdf->SetXY(20, 58);
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->Cell(25, 5, self::toIso('Docente:'), 0, 0);
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->Cell(65, 5, self::toIso($result['teacher_name']), 0, 0);
        
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->Cell(25, 5, self::toIso('Unidad:'), 0, 0);
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->Cell(65, 5, self::toIso('UNIDAD ' . $result['unit']), 0, 1);
        
        // Row 4: Date and Validation status
        $pdf->SetXY(20, 66);
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->Cell(25, 5, self::toIso('Fecha Envío:'), 0, 0);
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->Cell(65, 5, self::toIso($result['submitted_at']), 0, 0);
        
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->Cell(25, 5, self::toIso('Validación:'), 0, 0);
        $pdf->SetFont('Helvetica', '', 9.5);
        $status = $result['validated_at'] ? 'VALIDADO (' . $result['validated_at'] . ')' : 'PENDIENTE DE VALIDACIÓN';
        $pdf->Cell(65, 5, self::toIso($status), 0, 1);

        // Row 5: E-mail (Extra)
        $pdf->SetXY(20, 74);
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->Cell(25, 5, self::toIso('Correo:'), 0, 0);
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->Cell(155, 5, self::toIso($user['email']), 0, 1);
        
        // 4. Score Section
        $pdf->SetY(92);
        $pdf->SetFillColor(245, 247, 250);
        $pdf->SetDrawColor(220, 225, 235);
        $pdf->Rect(15, 92, 180, 28, 'DF');
        
        $pdf->SetTextColor(26, 53, 96);
        $pdf->SetXY(20, 96);
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->Cell(80, 5, self::toIso('CALIFICACIÓN OBTENIDA'), 0, 1, 'L');
        
        $pdf->SetTextColor(100, 110, 120);
        $pdf->SetXY(20, 103);
        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->Cell(80, 4, self::toIso('Evaluación automática de respuestas enviadas.'), 0, 1, 'L');
        
        // Score Badge / Number
        $score = (float)$result['score'];
        $totalScore = (float)$result['total_score'];
        $percentage = $totalScore > 0 ? round(($score / $totalScore) * 100, 1) : 0;
        
        $pdf->SetTextColor(46, 117, 89); // Green
        $pdf->SetFont('Helvetica', 'B', 24);
        $pdf->SetXY(110, 95);
        $pdf->Cell(80, 10, $score . ' / ' . $totalScore, 0, 1, 'R');
        
        $pdf->SetTextColor(80, 90, 100);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(110, 107);
        $pdf->Cell(80, 4, self::toIso('Porcentaje de acierto: ' . $percentage . '%'), 0, 1, 'R');
        
        // 5. Verification Section
        $pdf->SetY(126);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(180, 190, 210);
        $pdf->Rect(15, 126, 180, 36, 'DF');
        
        $pdf->SetTextColor(26, 53, 96);
        $pdf->SetXY(20, 129);
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->Cell(170, 4, self::toIso('CÓDIGO ÚNICO DE VERIFICACIÓN DE SEGURIDAD'), 0, 1, 'C');
        
        $pdf->SetTextColor(50, 50, 50);
        $pdf->SetFont('Courier', 'B', 13);
        $pdf->SetXY(20, 134);
        $pdf->Cell(170, 6, $validationCode, 0, 1, 'C');
        
        $pdf->SetTextColor(120, 125, 135);
        $pdf->SetFont('Helvetica', '', 7.5);
        $pdf->SetXY(20, 142);
        $termsText = "Este documento es una constancia digital oficial de evaluación generada por SIGEA. La clave única de arriba garantiza la integridad y veracidad de este reporte. Para validar estos datos o realizar aclaraciones académicas, presente este código de verificación en el panel escolar correspondiente.";
        $pdf->MultiCell(170, 3.5, self::toIso($termsText), 0, 'C');
        
        // Footer signature lines or decorative borders
        $pdf->SetFillColor(26, 53, 96);
        $pdf->Rect(0, 285, 210, 4, 'F');
        
        $pdf->SetTextColor(120, 125, 135);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetXY(15, 278);
        $pdf->Cell(90, 4, self::toIso('SIGEA - Generado el ' . date('d/m/Y H:i:s')), 0, 0, 'L');
        $pdf->Cell(90, 4, self::toIso('Página 1 de 1'), 0, 1, 'R');
        
        return $pdf->Output('S');
    }
    
    // Dynamic and secure code generator
    public static function generateValidationCode(int $attemptId): string
    {
        $salt = "sigea_secure_verification_2026";
        $hash = hash('sha256', $attemptId . $salt);
        $block1 = strtoupper(substr($hash, 0, 4));
        $block2 = strtoupper(substr($hash, 4, 4));
        $block3 = strtoupper(substr($hash, 8, 4));
        return "SIGEA-{$attemptId}-{$block1}-{$block2}-{$block3}";
    }
    
    // Convert UTF-8 text to ISO-8859-1 safely for FPDF standard fonts without php warnings/notices
    private static function toIso(string $text): string
    {
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
            if ($converted !== false) {
                return $converted;
            }
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
        }
        return $text;
    }
}
