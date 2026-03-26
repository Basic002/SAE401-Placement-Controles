<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['login'])) {
    http_response_code(403);
    echo 'Acces refuse.';
    exit();
}

require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../libs/fpdf186/fpdf.php';

class EnseignantsPdf extends FPDF
{
    public function header(): void
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('Liste des enseignants'), 0, 1, 'C');
        $this->Ln(2);
    }

    public function footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$stmt = $pdo->query(
    'SELECT nom_ens, prenom_ens, login
       FROM enseignant
      ORDER BY nom_ens ASC, prenom_ens ASC'
);
$enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new EnseignantsPdf();
$pdf->SetTitle(utf8_decode('Export enseignants'));
$pdf->SetAuthor('SAE401');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 11);

// Header table
$pdf->SetFillColor(128, 128, 128);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(60, 8, 'Nom', 1, 0, 'C', true);
$pdf->Cell(60, 8, utf8_decode('Prénom'), 1, 0, 'C', true);
$pdf->Cell(65, 8, 'Login', 1, 1, 'C', true);

// Data rows
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);
$fill = false;
foreach ($enseignants as $ens) {
    $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
    $pdf->Cell(60, 7, utf8_decode((string) $ens['nom_ens']), 1, 0, 'L', true);
    $pdf->Cell(60, 7, utf8_decode((string) $ens['prenom_ens']), 1, 0, 'L', true);
    $pdf->Cell(65, 7, (string) $ens['login'], 1, 1, 'L', true);
    $fill = !$fill;
}

$pdf->Output('I', 'enseignants.pdf');
exit();
