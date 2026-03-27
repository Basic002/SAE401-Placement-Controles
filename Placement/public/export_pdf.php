<?php
declare(strict_types=1);

if (ob_get_level() === 0) {
    ob_start();
}
session_start();

ini_set('display_errors', '0');
ini_set('zlib.output_compression', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);

if (!isset($_SESSION['login'])) {
    http_response_code(403);
    echo 'Acces refuse.';
    exit();
}

require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../libs/fpdf186/fpdf.php';

$idDevoir = (int) ($_GET['idDevoir'] ?? 0);
$idSalle  = (int) ($_GET['idSalle'] ?? 0);
$idPromo  = (int) ($_GET['idPromo'] ?? 0);
$varD     = (string) ($_GET['varD'] ?? '');

if ($idDevoir <= 0) {
    http_response_code(400);
    echo 'Parametre idDevoir invalide.';
    exit();
}

function latin1(string $text): string
{
    return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
}

function shortPlanName(string $nom, string $prenom): string
{
    $nom = trim($nom);
    $prenom = trim($prenom);

    $nomPart = mb_strtoupper(mb_substr($nom, 0, 10, 'UTF-8'), 'UTF-8');
    $prenomInitial = $prenom !== '' ? mb_strtoupper(mb_substr($prenom, 0, 1, 'UTF-8'), 'UTF-8') . '.' : '';

    return trim($nomPart . ' ' . $prenomInitial);
}

function fetchDevoirContext(PDO $pdo, int $idDevoir): array
{
    $stmt = $pdo->prepare(
        "SELECT d.id_devoir, d.nom_devoir, d.date_devoir, d.heure_devoir, d.duree_devoir, m.nom_mat
         FROM devoir d
         LEFT JOIN matiere m ON m.id_mat = d.id_mat
         WHERE d.id_devoir = :id_devoir"
    );
    $stmt->execute(['id_devoir' => $idDevoir]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function fetchListeSalleRows(PDO $pdo, int $idDevoir, int $idSalle): array
{
    $stmt = $pdo->prepare(
        "SELECT e.nom_etudiant, e.prenom_etudiant, d.nom_dpt, p.nom_promo, g.nom_groupe, pl.place_x, pl.place_y
         FROM placement pl
         JOIN etudiant e ON e.id_etudiant = pl.id_etudiant
         JOIN groupe g ON g.id_groupe = e.id_groupe
         JOIN promotion p ON p.id_promo = g.id_promo
         JOIN departement d ON d.id_dpt = p.id_dpt
         WHERE pl.id_devoir = :id_devoir
           AND pl.id_salle = :id_salle
         ORDER BY e.nom_etudiant, e.prenom_etudiant"
    );
    $stmt->execute([
        'id_devoir' => $idDevoir,
        'id_salle'  => $idSalle,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchPlanSalle(PDO $pdo, int $idSalle): array
{
    $stmt = $pdo->prepare(
        "SELECT s.nom_salle, p.donnee
         FROM salle s
         JOIN plan p ON p.id_plan = s.id_plan
         WHERE s.id_salle = :id_salle"
    );
    $stmt->execute(['id_salle' => $idSalle]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function parsePlan(string $donnee): array
{
    $rows = [];
    foreach (explode('-', $donnee) as $line) {
        if ($line !== '') {
            $rows[] = str_split($line);
        }
    }
    return $rows;
}

function fetchPlacementMap(PDO $pdo, int $idDevoir, int $idSalle): array
{
    $stmt = $pdo->prepare(
        "SELECT pl.place_x, pl.place_y, e.nom_etudiant, e.prenom_etudiant
         FROM placement pl
         JOIN etudiant e ON e.id_etudiant = pl.id_etudiant
         WHERE pl.id_devoir = :id_devoir
           AND pl.id_salle = :id_salle"
    );
    $stmt->execute([
        'id_devoir' => $idDevoir,
        'id_salle'  => $idSalle,
    ]);

    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = ((int) $row['place_x']) . ',' . ((int) $row['place_y']);
        $map[$key] = shortPlanName((string) $row['nom_etudiant'], (string) $row['prenom_etudiant']);
    }
    return $map;
}

function placeLabel(int $x, int $y, array $planRows): string
{
    $rang = 0;
    for ($i = count($planRows) - 1; $i >= 0; $i--) {
        if (($planRows[$i][0] ?? '0') !== '0') {
            $rang++;
            if ($i === $x) {
                $col = 0;
                for ($j = 0; $j < count($planRows[$i]); $j++) {
                    if (($planRows[$i][$j] ?? '0') !== '0') {
                        $col++;
                        if ($j === $y) {
                            return $rang . '-' . $col;
                        }
                    }
                }
            }
        }
    }
    return '';
}

function flushPdf(FPDF $pdf, string $filename): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    $pdf->Output('D', $filename);
    exit();
}

$ctx = fetchDevoirContext($pdo, $idDevoir);
$date = (string) ($ctx['date_devoir'] ?? '');
$heure = substr((string) ($ctx['heure_devoir'] ?? '00:00:00'), 0, 5);
$duree = substr((string) ($ctx['duree_devoir'] ?? '00:00:00'), 0, 5);

if ($varD === '1' || $varD === '2') {
    if ($idSalle <= 0) {
        http_response_code(400);
        echo 'Parametre idSalle invalide.';
        exit();
    }

    $salle = fetchPlanSalle($pdo, $idSalle);
    $planRows = parsePlan((string) ($salle['donnee'] ?? ''));
    $rows = fetchListeSalleRows($pdo, $idDevoir, $idSalle);

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);

    $title = $varD === '1' ? 'Liste' : latin1("Feuille d'emargement");
    $pdf->Cell(0, 8, $title . ' - ' . latin1((string) ($salle['nom_salle'] ?? '')), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, latin1($date . ' - ' . $heure . ' - Duree: ' . $duree), 0, 1, 'C');
    $pdf->Ln(2);

    $pdf->SetFont('Arial', 'B', 9);
    if ($varD === '1') {
        $pdf->Cell(42, 7, 'Nom', 1, 0, 'C');
        $pdf->Cell(38, 7, latin1('Prenom'), 1, 0, 'C');
        $pdf->Cell(18, 7, 'Place', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Promotion', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Groupe', 1, 1, 'C');
    } else {
        $pdf->Cell(35, 7, 'Signature', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Nom', 1, 0, 'C');
        $pdf->Cell(35, 7, latin1('Prenom'), 1, 0, 'C');
        $pdf->Cell(18, 7, 'Place', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Promotion', 1, 0, 'C');
        $pdf->Cell(22, 7, 'Groupe', 1, 1, 'C');
    }

    $pdf->SetFont('Arial', '', 8);
    foreach ($rows as $r) {
        $place = placeLabel((int) $r['place_x'], (int) $r['place_y'], $planRows);
        $promo = (string) $r['nom_dpt'] . ' ' . (string) $r['nom_promo'];
        if ($varD === '1') {
            $pdf->Cell(42, 6, latin1((string) $r['nom_etudiant']), 1);
            $pdf->Cell(38, 6, latin1((string) $r['prenom_etudiant']), 1);
            $pdf->Cell(18, 6, $place, 1, 0, 'C');
            $pdf->Cell(50, 6, latin1($promo), 1);
            $pdf->Cell(40, 6, latin1((string) $r['nom_groupe']), 1, 1);
        } else {
            $pdf->Cell(35, 8, '', 1);
            $pdf->Cell(40, 8, latin1((string) $r['nom_etudiant']), 1);
            $pdf->Cell(35, 8, latin1((string) $r['prenom_etudiant']), 1);
            $pdf->Cell(18, 8, $place, 1, 0, 'C');
            $pdf->Cell(40, 8, latin1($promo), 1);
            $pdf->Cell(22, 8, latin1((string) $r['nom_groupe']), 1, 1);
        }
    }

    $name = $varD === '1' ? "liste_salle_{$idSalle}.pdf" : "emargement_salle_{$idSalle}.pdf";
    flushPdf($pdf, $name);
}

if ($varD === '4') {
    if ($idSalle <= 0) {
        http_response_code(400);
        echo 'Parametre idSalle invalide.';
        exit();
    }

    $salle = fetchPlanSalle($pdo, $idSalle);
    $rows = parsePlan((string) ($salle['donnee'] ?? ''));
    $map = fetchPlacementMap($pdo, $idDevoir, $idSalle);

    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'Plan - ' . latin1((string) ($salle['nom_salle'] ?? "Salle {$idSalle}")), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 5, latin1($date . ' - ' . $heure . ' - Duree: ' . $duree), 0, 1, 'C');
    $pdf->Ln(2);

    $colCount = max(1, count($rows[0] ?? []));
    $usableW = 277.0; // A4 landscape width minus margins
    $cellW = max(9.0, floor(($usableW / $colCount) * 10) / 10);
    $cellH = 8.0;
    $pdf->SetFont('Arial', '', 5.5);

    foreach ($rows as $x => $line) {
        foreach ($line as $y => $cell) {
            $k = $x . ',' . $y;
            $text = '';
            if (isset($map[$k])) {
                $text = latin1((string) $map[$k]);
            } elseif ((int) $cell === 3) {
                $text = '-';
            }
            $pdf->Cell($cellW, $cellH, $text, 1, 0, 'C');
        }
        $pdf->Ln();
    }

    flushPdf($pdf, "plan_salle_{$idSalle}.pdf");
}

if ($varD === '3') {
    if ($idPromo <= 0) {
        http_response_code(400);
        echo 'Parametre idPromo invalide.';
        exit();
    }

    $stmt = $pdo->prepare(
        "SELECT e.nom_etudiant, e.prenom_etudiant, g.nom_groupe, s.nom_salle, pl.place_x, pl.place_y
         FROM placement pl
         JOIN etudiant e ON e.id_etudiant = pl.id_etudiant
         JOIN groupe g ON g.id_groupe = e.id_groupe
         JOIN promotion p ON p.id_promo = g.id_promo
         JOIN salle s ON s.id_salle = pl.id_salle
         WHERE pl.id_devoir = :id_devoir AND p.id_promo = :id_promo
         ORDER BY s.nom_salle, e.nom_etudiant"
    );
    $stmt->execute(['id_devoir' => $idDevoir, 'id_promo' => $idPromo]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'Liste promo', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(45, 7, 'Nom', 1, 0, 'C');
    $pdf->Cell(40, 7, latin1('Prenom'), 1, 0, 'C');
    $pdf->Cell(35, 7, 'Salle', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Place', 1, 0, 'C');
    $pdf->Cell(45, 7, 'Groupe', 1, 1, 'C');
    $pdf->SetFont('Arial', '', 8);
    foreach ($rows as $r) {
        $pdf->Cell(45, 6, latin1((string) $r['nom_etudiant']), 1);
        $pdf->Cell(40, 6, latin1((string) $r['prenom_etudiant']), 1);
        $pdf->Cell(35, 6, latin1((string) $r['nom_salle']), 1);
        $pdf->Cell(25, 6, (string) $r['place_x'] . '-' . (string) $r['place_y'], 1, 0, 'C');
        $pdf->Cell(45, 6, latin1((string) $r['nom_groupe']), 1, 1);
    }
    flushPdf($pdf, "liste_promo_{$idPromo}.pdf");
}

http_response_code(400);
echo 'Parametre varD invalide.';
exit();

