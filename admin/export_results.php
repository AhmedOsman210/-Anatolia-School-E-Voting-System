<?php
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit;
}

// Set headers to force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="election_results_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, ['Candidate Name', 'Class', 'Total Votes', 'Percentage']);

// Get total votes for percentage calc
$total_ballots = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn() ?: 0;

// Fetch results
$candidates = $pdo->query("SELECT * FROM candidates ORDER BY vote_count DESC")->fetchAll();

foreach ($candidates as $c) {
    $percentage = $total_ballots > 0 ? round(($c['vote_count'] / $total_ballots) * 100, 2) . '%' : '0%';
    fputcsv($output, [
        $c['full_name'],
        $c['class'],
        $c['vote_count'],
        $percentage
    ]);
}

fclose($output);
exit;
?>