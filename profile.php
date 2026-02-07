<?php
require_once 'includes/db.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$id]);
$candidate = $stmt->fetch();

if (!$candidate) {
    header('Location: dashboard.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$student = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$student->execute([$student_id]);
$student_data = $student->fetch();

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cast_vote'])) {
    if ($student_data['has_voted']) {
        $message = 'You have already voted!';
    } else {
        $pdo->beginTransaction();
        try {
            // Record the secret vote for analytics (Anonymous but grade-tracked)
            $stmt = $pdo->prepare("INSERT INTO votes (candidate_id, student_grade) VALUES (?, ?)");
            $stmt->execute([$id, $student_data['class'] ?? 'Ungraded']);

            // Increment candidate vote count
            $stmt = $pdo->prepare("UPDATE candidates SET vote_count = vote_count + 1 WHERE id = ?");
            $stmt->execute([$id]);

            // Mark student as voted
            $stmt = $pdo->prepare("UPDATE students SET has_voted = 1 WHERE id = ?");
            $stmt->execute([$student_id]);

            $pdo->commit();
            header('Location: vote_confirmed.php?candidate=' . urlencode($candidate['full_name']));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = 'Error casting vote.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($candidate['full_name']); ?> - Anatolia School E-Voting
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-header {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2.5rem;
            margin-bottom: 2.5rem;
        }

        .candidate-large-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 1rem;
        }

        .info-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            height: 100%;
        }

        .vote-section {
            border: 2px dashed #334155;
            border-radius: 1.5rem;
            padding: 4rem 2rem;
            margin-top: 4rem;
            text-align: center;
        }
    </style>
</head>

<body class="bg-dark text-white">
    <nav class="navbar navbar-expand-lg border-bottom border-secondary border-opacity-25 py-3">
        <div class="container px-4">
            <a class="navbar-brand d-flex align-items-center gap-2 text-white fw-bold" href="dashboard.php">
                <div class="bg-primary p-1 rounded">
                    <i class="bi bi-mortarboard-fill text-white"></i>
                </div>
                Anatolia School E-Voting
            </a>
            <div class="collapse navbar-collapse justify-content-center">
                <ul class="navbar-nav gap-4">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Candidates</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">My Vote</a></li>
                    <li class="nav-item"><a class="nav-link" href="results.php">Results</a></li>
                </ul>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student_data['full_name']); ?>&background=random"
                class="rounded-circle" width="35" alt="">
        </div>
    </nav>

    <div class="container py-5" style="max-width: 900px;">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php"
                        class="text-muted text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php"
                        class="text-muted text-decoration-none">Candidates</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">
                    <?php echo htmlspecialchars($candidate['full_name']); ?>
                </li>
            </ol>
        </nav>

        <div class="profile-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-4">
                <img src="<?php echo $candidate['photo'] ? 'uploads/' . $candidate['photo'] : 'https://images.unsplash.com/photo-1544717297-fa15739a5447?w=200&h=200&fit=crop'; ?>"
                    class="candidate-large-img" alt="">
                <div>
                    <h1 class="fw-bold mb-1">
                        <?php echo htmlspecialchars($candidate['full_name']); ?>
                    </h1>
                    <p class="text-primary fw-bold mb-1">Class of 2024</p>
                    <p class="text-muted mb-0">Candidate for Chairman</p>
                </div>
            </div>
            <button class="btn btn-outline-secondary px-4"><i class="bi bi-share me-2"></i> Share</button>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="info-card">
                    <h5 class="fw-bold mb-4">Who I Am</h5>
                    <p class="text-muted leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($candidate['bio'])); ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-card">
                    <h5 class="fw-bold mb-4">Mission & Vision</h5>
                    <p class="text-muted leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($candidate['mission'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if ($student_data['has_voted']): ?>
            <div class="vote-section">
                <div class="bg-success bg-opacity-10 d-inline-flex p-3 rounded-circle mb-4">
                    <i class="bi bi-check-circle-fill text-success fs-2"></i>
                </div>
                <h3 class="fw-bold mb-3">Vote Recorded</h3>
                <p class="text-muted">You have already cast your vote in this election.</p>
                <a href="dashboard.php" class="text-primary text-decoration-none fw-bold mt-4 d-block">Go Back to Candidates
                    List</a>
            </div>
        <?php else: ?>
            <div class="vote-section">
                <div class="bg-primary bg-opacity-10 d-inline-flex p-3 rounded-circle mb-4">
                    <i class="bi bi-box-arrow-in-down text-primary fs-2"></i>
                </div>
                <h3 class="fw-bold mb-3">Ready to cast your vote?</h3>
                <p class="text-muted mb-5">Please confirm your selection. Your vote is a crucial part of our democratic
                    process at Anatolia School.</p>

                <form method="POST">
                    <button type="submit" name="cast_vote"
                        class="btn btn-primary px-5 py-3 fw-bold rounded-pill shadow-lg mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i> Cast My Vote for
                        <?php echo htmlspecialchars($candidate['full_name']); ?>
                    </button>
                </form>

                <p class="text-danger small fw-bold mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> This action is irreversible. You cannot change your
                    vote later.
                </p>

                <a href="dashboard.php" class="text-muted text-decoration-none fw-bold">Go Back to Candidates List</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="mt-5 py-5 border-top border-secondary border-opacity-10 text-center">
        <p class="small text-muted mb-3">© 2024 Anatolia School Student Council E-Voting System</p>
        <div class="d-flex justify-content-center gap-4 small">
            <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
            <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
            <a href="#" class="text-muted text-decoration-none">Help Center</a>
        </div>
    </footer>
</body>

</html>