<?php
require_once 'includes/db.php';

$election_status = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'")->fetchColumn();

// Stats
$total_voters = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_ballots = $pdo->query("SELECT COUNT(*) FROM students WHERE has_voted = 1")->fetchColumn();
$turnout = $total_voters > 0 ? ($total_ballots / $total_voters) * 100 : 0;
$valid_ballots = $total_ballots; // For simplicity in this demo

$candidates = $pdo->query("SELECT * FROM candidates ORDER BY vote_count DESC")->fetchAll();
$winner = !empty($candidates) ? $candidates[0] : null;
$second_place = count($candidates) > 1 ? $candidates[1] : null;
$margin = ($winner && $second_place) ? ($winner['vote_count'] - $second_place['vote_count']) : 0;

if ($election_status != 'closed' && !isset($_SESSION['admin_id'])) {
    // Show progress only if closed OR if logged in as admin
    $show_results = false;
} else {
    $show_results = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - Anatolia School E-Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #0f172a;
        }

        .bg-blue-600 {
            background-color: #2563eb;
        }

        .text-blue-400 {
            color: #60a5fa;
        }
    </style>
</head>

<body class="text-white">
    <nav class="navbar navbar-expand-lg border-bottom border-secondary border-opacity-10 py-3">
        <div class="container px-4">
            <a class="navbar-brand d-flex align-items-center gap-2 text-white fw-bold" href="dashboard.php">
                <div class="bg-primary p-1 rounded">
                    <i class="bi bi-mortarboard-fill text-white"></i>
                </div>
                Anatolia School E-Voting
            </a>
            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav gap-4 align-items-center">
                    <li class="nav-item"><a class="nav-link text-muted" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-muted" href="#">Candidates</a></li>
                    <li class="nav-item"><a class="nav-link text-primary active fw-bold" href="#">Results</a></li>
                    <li class="nav-item"><a class="nav-link text-muted" href="#">Archive</a></li>
                    <li class="nav-item ms-3">
                        <button class="btn btn-dark btn-sm rounded-circle p-2"><i class="bi bi-bell"></i></button>
                    </li>
                    <li class="nav-item ms-2">
                        <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle"
                            width="35" alt="">
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5" style="max-width: 1100px;">
        <?php if (!$show_results): ?>
            <div class="text-center py-5">
                <div class="bg-warning bg-opacity-10 d-inline-flex p-4 rounded-circle mb-4">
                    <i class="bi bi-hourglass-split text-warning display-4"></i>
                </div>
                <h2 class="fw-bold">Results are not available yet</h2>
                <p class="text-muted">Voting is still in progress. Check back once the election has officially closed.</p>
                <a href="dashboard.php" class="btn btn-primary px-5 py-3 rounded-pill mt-4">Return to Dashboard</a>
            </div>
        <?php else: ?>
            <div class="results-banner mb-5">
                <span
                    class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 px-3 py-2 mb-3">OFFICIAL
                    DECLARATION</span>
                <h1 class="display-5 fw-bold mb-2">2024 Election Results Finalized</h1>
                <p class="text-white text-opacity-75 mb-0">Thank you for participating in the Anatolia School Student
                    Government elections.</p>
            </div>

            <?php if ($winner): ?>
                <div class="winner-card mb-5">
                    <div class="winner-img-container">
                        <img src="<?php echo $winner['photo'] ? 'uploads/' . $winner['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($winner['full_name']) . '&size=120'; ?>"
                            class="rounded-circle border border-4 border-primary" width="120" height="120">
                        <div class="winner-badge">
                            <i class="bi bi-award-fill"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1">Chairman Elect: <?php echo htmlspecialchars($winner['full_name']); ?></h2>
                    <h5 class="text-primary fw-bold mb-3">Congratulations to the new Student Chairman!</h5>
                    <p class="text-muted small mb-4">Anatolia School Student Government • 2024-2025 Term</p>

                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2">
                            <i class="bi bi-download"></i> Download Official Results
                        </button>
                        <a href="profile.php?id=<?php echo $winner['id']; ?>" class="btn btn-outline-secondary px-4 py-2">View
                            Full Profile</a>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-lg-8">
                        <h5 class="fw-bold mb-4">Vote Distribution</h5>
                        <div class="stat-card p-5">
                            <div class="d-flex justify-content-between align-items-end mb-4">
                                <div>
                                    <small class="text-muted text-uppercase fw-bold ls-1">Candidate Performance</small>
                                    <h2 class="fw-bold mb-0"><?php echo number_format($total_ballots); ?> Total Ballots</h2>
                                </div>
                                <span
                                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">100%
                                    COUNTED</span>
                            </div>

                            <div class="d-flex justify-content-around align-items-end pt-5" style="height: 350px;">
                                <?php foreach ($candidates as $c): ?>
                                    <?php $h = ($total_ballots > 0) ? ($c['vote_count'] / $winner['vote_count']) * 100 : 0; ?>
                                    <div class="chart-item">
                                        <div class="bar" style="height: <?php echo $h; ?>%;">
                                            <div class="bar-fill" style="height: 100%;"></div>
                                        </div>
                                        <div class="text-center">
                                            <div class="fw-bold small"><?php echo explode(' ', $c['full_name'])[0]; ?>.</div>
                                            <div class="text-muted" style="font-size: 10px;"><?php echo $c['vote_count']; ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex flex-column gap-4">
                            <div class="stat-card">
                                <small class="text-muted d-block mb-1">Voter Turnout</small>
                                <h1 class="fw-bold mb-2"><?php echo round($turnout, 1); ?>%</h1>
                                <div class="progress bg-secondary bg-opacity-10 mb-2" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: <?php echo $turnout; ?>%;"></div>
                                </div>
                                <small class="text-muted"><?php echo number_format($total_ballots); ?> of
                                    <?php echo number_format($total_voters); ?> eligible students</small>
                            </div>
                            <div class="stat-card">
                                <small class="text-muted d-block mb-1">Valid Ballots</small>
                                <h1 class="fw-bold mb-2"><?php echo number_format($valid_ballots); ?></h1>
                                <small class="text-muted">0 Spoiled/Blank ballots excluded</small>
                            </div>
                            <div class="stat-card bg-primary bg-opacity-10 border-primary border-opacity-25">
                                <small class="text-primary-emphasis d-block mb-1">Close Margin</small>
                                <h1 class="fw-bold mb-2 text-primary"><?php echo number_format($margin); ?> Votes</h1>
                                <small class="text-primary-emphasis opacity-75">Difference between 1st and 2nd</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="d-flex justify-content-between align-items-center pt-5 border-top border-secondary border-opacity-10">
                    <div class="text-muted small d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-primary"></i> Results verified by the Anatolia School Election
                        Committee on <?php echo date('F j, Y'); ?>
                    </div>
                    <div class="d-flex gap-4 small">
                        <a href="#" class="text-muted text-decoration-none">Audit Log</a>
                        <a href="#" class="text-muted text-decoration-none">Electoral Bylaws</a>
                        <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No candidates participated in this election.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>

</html>