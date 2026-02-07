<?php
require_once 'includes/db.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$candidate_name = $_GET['candidate'] ?? 'your selected candidate';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote Successfully Cast - Anatolia School E-Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="confirmation-wrapper">
        <div class="container d-flex flex-column align-items-center">
            <div class="success-circle">
                <i class="bi bi-check-lg fs-1 text-dark"></i>
            </div>

            <h1 class="display-4 fw-bold mb-3 text-white">Vote Successfully Cast!</h1>
            <p class="text-muted fs-5 mb-5">Your vote for <strong>
                    <?php echo htmlspecialchars($candidate_name); ?>
                </strong> has been<br>recorded anonymously.</p>

            <div class="confirmation-card mb-5">
                <img src="assets/img/ballot-icon.png" alt="" style="width: 150px; opacity: 0.5; filter: grayscale(1);">
                <div class="mt-4 p-4 rounded-4"
                    style="background: rgba(0, 230, 118, 0.05); border: 1px solid rgba(0, 230, 118, 0.1);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-start">
                            <h6 class="fw-bold mb-1 text-white">Results Notice</h6>
                            <p class="text-muted small mb-0">Results will be available once the election closes<br>at
                                5:00 PM today.</p>
                        </div>
                        <div
                            class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 d-flex align-items-center gap-2">
                            <i class="bi bi-clock-fill" style="font-size: 10px;"></i> UPCOMING
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column align-items-center gap-4">
                <a href="dashboard.php" class="btn btn-success-bright px-5 py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-grid-fill"></i> Back to Dashboard
                </a>
                <a href="#" class="text-muted text-decoration-none small">Download Receipt (PDF)</a>
            </div>

            <div class="mt-5 pt-5 border-top border-secondary border-opacity-10 w-100 text-center">
                <small class="text-muted">Anatolia School Election Commission © 2024. Secured by blockchain-based
                    anonymous voting protocols.</small>
            </div>
        </div>
    </div>
</body>

</html>