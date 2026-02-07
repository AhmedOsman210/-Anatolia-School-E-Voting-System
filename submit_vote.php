<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['student_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$candidate_id = $_POST['candidate_id'] ?? null;

// Validation
if (!$candidate_id) {
    die("Error: No candidate selected.");
}

// Check if election is open
$status = get_election_status($pdo);
if ($status !== 'active') {
    die("Error: Election is not currently active.");
}

// Check if already voted
$stmt = $pdo->prepare("SELECT has_voted FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if ($student['has_voted']) {
    die("Error: You have already voted.");
}

// Record Vote
try {
    $pdo->beginTransaction();

    // 1. Insert vote record
    // We need student's class/grade for analytics, fetch it
    $stmt = $pdo->prepare("SELECT class FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student_info = $stmt->fetch();
    $student_grade = $student_info['class'];

    $stmt = $pdo->prepare("INSERT INTO votes (student_id, candidate_id, student_grade) VALUES (?, ?, ?)");
    $stmt->execute([$student_id, $candidate_id, $student_grade]);

    // 2. Increment candidate vote count
    $stmt = $pdo->prepare("UPDATE candidates SET vote_count = vote_count + 1 WHERE id = ?");
    $stmt->execute([$candidate_id]);

    // 3. Mark student as voted
    $stmt = $pdo->prepare("UPDATE students SET has_voted = 1 WHERE id = ?");
    $stmt->execute([$student_id]);

    $pdo->commit();

    // Redirect to results or dashboard with success
    header('Location: dashboard.php?vote_success=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error processing vote: " . $e->getMessage());
}
?>