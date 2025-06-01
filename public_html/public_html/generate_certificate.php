<?php
require 'vendor/tecnickcom/tcpdf/tcpdf.php'; // Zorg dat je het juiste pad naar tcpdf.php hebt

function generateCertificate($userName, $projectName, $amount, $certificatePath) {
    // Zorg dat de 'certificates' map bestaat en schrijfbaar is
    $certificatesDir = dirname($certificatePath);
    if (!is_dir($certificatesDir)) {
        mkdir($certificatesDir, 0777, true);
    } elseif (!is_writable($certificatesDir)) {
        chmod($certificatesDir, 0777);
    }

    // Gebruik een absoluut pad voor het certificaatbestand
    $absoluteCertificatePath = realpath($certificatesDir) . DIRECTORY_SEPARATOR . basename($certificatePath);

    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Certificate of Contribution', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 10, "This is to certify that $userName has contributed €$amount to the project '$projectName'.", 0, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, 'Thank you for your support!', 0, 1, 'C');
    $pdf->Output($absoluteCertificatePath, 'F');
}
?>
