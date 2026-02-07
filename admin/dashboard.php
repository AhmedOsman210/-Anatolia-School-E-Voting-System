<?php
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit;
}

// Fetch stats
$student_count = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$vote_count = $pdo->query("SELECT COUNT(*) FROM students WHERE has_voted = 1")->fetchColumn();
$turnout_percent = $student_count > 0 ? round(($vote_count / $student_count) * 100) : 0;

$election_status = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Anatolia School E-Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            padding-left: 280px;
        }

        @media (max-width: 991px) {
            body {
                padding-left: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="d-flex align-items-center gap-3 mb-5 px-3">
            <div class="bg-primary p-2 rounded">
                <i class="bi bi-mortarboard-fill text-white fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0">ANATOLIA SCHOOL</h6>
                <small class="text-muted">Admin Dashboard</small>
            </div>
        </div>

        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link active">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="candidates.php" class="nav-link">
                <i class="bi bi-people-fill"></i> Candidates
            </a>
            <a href="voters.php" class="nav-link">
                <i class="bi bi-person-check-fill"></i> Voter Access
            </a>
            <a href="results.php" class="nav-link">
                <i class="bi bi-bar-chart-fill"></i> Election Results
            </a>
            <a href="settings.php" class="nav-link">
                <i class="bi bi-gear-fill"></i> Settings
            </a>
        </nav>

        <div class="mt-auto pt-5">
            <div class="d-flex align-items-center gap-3 px-3 py-3 border-top border-secondary border-opacity-25 mt-5">
                <div class="bg-secondary bg-opacity-25 p-2 rounded-circle">
                    <i class="bi bi-person-fill text-muted"></i>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <h6 class="fw-bold mb-0 text-truncate">Administrator</h6>
                    <a href="../logout.php" class="small text-muted text-decoration-none">Log out</a>
                </div>
                <i class="bi bi-box-arrow-right text-muted"></i>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-5">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold">Admin Dashboard Overview</h2>
                <p class="text-white mb-0">Manage elections and monitor real-time results.</p>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <span class="text-white small">Election Status:</span>
                <span
                    class="badge rounded-pill bg-success bg-opacity-10 text-white border border-success border-opacity-25 px-3 py-2">
                    <i class="bi bi-circle-fill me-2" style="font-size: 8px;"></i>
                    <?php echo strtoupper($election_status); ?>
                </span>
                <div class="ms-3 border-start ps-4 d-flex align-items-center gap-3">
                    <span class="small fw-bold text-white">VOTING PORTAL</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="portalSwitch" <?php echo $election_status == 'active' ? 'checked' : ''; ?>>
                        <label class="form-check-label small fw-bold text-white" for="portalSwitch">
                            <?php echo $election_status == 'active' ? 'OPEN' : 'CLOSED'; ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-4">
                    <div class="bg-primary bg-opacity-10 p-4 rounded-circle">
                        <i class="bi bi-people-fill text-primary fs-3"></i>
                    </div>
                    <div>
                        <small class="text-white d-block mb-1">Total Registered Voters</small>
                        <h2 class="fw-bold mb-0">
                            <?php echo number_format($student_count); ?>
                        </h2>
                        <small class="text-white fw-bold">+12 from yesterday</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <small class="text-white d-block mb-1">Current Voter Turnout</small>
                            <h2 class="fw-bold mb-0">
                                <?php echo number_format($vote_count); ?>
                            </h2>
                            <small class="text-white">Targeting 90% turnout</small>
                        </div>
                        <div class="position-relative" style="width: 70px; height: 70px;">
                            <svg class="w-100 h-100" viewBox="0 0 36 36">
                                <path fill="none" stroke="#334155" stroke-width="3"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path fill="none" stroke="#3b82f6" stroke-width="3"
                                    stroke-dasharray="<?php echo $turnout_percent; ?>, 100"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <text x="18" y="20.35" class="fw-bold" font-size="8" text-anchor="middle" fill="white">
                                    <?php echo $turnout_percent; ?>%
                                </text>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <small class="text-white d-block mb-1">Time Remaining</small>
                            <h2 class="fw-bold mb-0">08 <span class="fs-6 text-white">h</span> 42 <span
                                    class="fs-6 text-white">m</span></h2>
                        </div>
                        <i class="bi bi-stopwatch text-white fs-4"></i>
                    </div>
                    <div class="progress bg-secondary bg-opacity-10" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 65%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i> Live Activity
                            Feed</h5>
                        <a href="#" class="text-white text-decoration-none small fw-bold">Export Logs</a>
                    </div>

                    <div class="activity-list">
                        <div class="activity-item d-flex gap-3">
                            <div class="bg-success bg-opacity-10 p-2 rounded h-fit">
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Student #2024-042 just cast their vote.</h6>
                                <small class="text-white">Just now • Voter Booth 4</small>
                            </div>
                        </div>
                        <div class="activity-item d-flex gap-3">
                            <div class="bg-primary bg-opacity-10 p-2 rounded h-fit">
                                <i class="bi bi-person-plus-fill text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">New candidate profile updated: <strong>Dimitri Papadopoulos</strong>
                                </h6>
                                <small class="text-white">14 minutes ago • Admin: sys_admin</small>
                            </div>
                        </div>
                        <div class="activity-item d-flex gap-3">
                            <div class="bg-warning bg-opacity-10 p-2 rounded h-fit">
                                <i class="bi bi-clock-fill text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Voting window scheduled: Phase II - Senior Classes</h6>
                                <small class="text-white">1 hour ago • Auto-scheduler</small>
                            </div>
                        </div>
                        <div class="activity-item d-flex gap-3">
                            <div class="bg-info bg-opacity-10 p-2 rounded h-fit">
                                <i class="bi bi-shield-lock-fill text-info"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">System backup completed successfully.</h6>
                                <small class="text-white">3 hours ago • Cloud Server 01</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="stat-card mb-4 border-0 shadow-lg"
                    style="background: linear-gradient(135deg, #1e293b, #0f172a);">
                    <h5 class="fw-bold mb-3">Chairman Election 2024</h5>
                    <p class="small text-white">Current phase: Active Voting. System monitoring all nodes for integrity.
                    </p>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-white">System Load</small>
                        <small class="text-white fw-bold">12%</small>
                    </div>
                    <div class="progress bg-white bg-opacity-10" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 12%;"></div>
                    </div>
                </div>

                <div class="stat-card">
                    <h5 class="fw-bold mb-4">Quick Shortcuts</h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <button
                                class="btn btn-outline-secondary w-100 py-4 d-flex flex-column align-items-center gap-2">
                                <i class="bi bi-plus-circle-fill text-primary fs-4"></i>
                                <span class="small fw-bold">New Voter</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button
                                class="btn btn-outline-secondary w-100 py-4 d-flex flex-column align-items-center gap-2">
                                <i class="bi bi-printer-fill fs-4 text-primary"></i>
                                <span class="small fw-bold">Ballot Print</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button
                                class="btn btn-outline-secondary w-100 py-4 d-flex flex-column align-items-center gap-2">
                                <i class="bi bi-file-earmark-bar-graph-fill fs-4 text-primary"></i>
                                <span class="small fw-bold">PDF Report</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button
                                class="btn btn-outline-secondary w-100 py-4 d-flex flex-column align-items-center gap-2">
                                <i class="bi bi-envelope-at-fill fs-4 text-primary"></i>
                                <span class="small fw-bold">Blast Email</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>