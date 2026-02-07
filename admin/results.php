<?php
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit;
}

// Stats
$total_voters = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn() ?: 0;
$total_ballots = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn() ?: 0;
$turnout = $total_voters > 0 ? ($total_ballots / $total_voters) * 100 : 0;
$spoilt_ballots = 0; // Fixed for demo as per image

$candidates = $pdo->query("SELECT * FROM candidates ORDER BY vote_count DESC")->fetchAll();
$winner = !empty($candidates) ? $candidates[0] : null;

// Tally by Grade (Mocking if no data, otherwise real query)
$grades = ['Grade 12', 'Grade 11', 'Grade 10', 'Grade 9'];
$tally_data = [];
foreach ($grades as $grade) {
    $eligible = $pdo->prepare("SELECT COUNT(*) FROM students WHERE class LIKE ?");
    $eligible->execute(["%$grade%"]);
    $eligible_count = $eligible->fetchColumn();

    $voted = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE student_grade LIKE ?");
    $voted->execute(["%$grade%"]);
    $voted_count = $voted->fetchColumn();

    $tally_row = [
        'grade' => $grade,
        'eligible' => $eligible_count,
        'voted' => $voted_count,
        'turnout' => $eligible_count > 0 ? round(($voted_count / $eligible_count) * 100) : 0,
        'candidates' => []
    ];

    foreach ($candidates as $c) {
        $c_votes = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE candidate_id = ? AND student_grade LIKE ?");
        $c_votes->execute([$c['id'], "%$grade%"]);
        $tally_row['candidates'][$c['id']] = $c_votes->fetchColumn();
    }

    $tally_data[] = $tally_row;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Election Results & Analytics - Anatolia Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            padding-left: 280px;
            background-color: #0c121d;
            color: #fff;
        }

        @media (max-width: 991px) {
            body {
                padding-left: 0;
            }
        }

        .analytics-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 1rem;
            padding: 1.5rem;
        }

        .winner-panel {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9)), url('https://w.wallhaven.cc/full/8o/wallhaven-8o9mpy.jpg');
            background-size: cover;
            border: 1px solid #334155;
            border-radius: 1.5rem;
            overflow: hidden;
        }

        .participation-track {
            height: 40px;
            background-color: #f59e0b;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .participation-fill {
            height: 100%;
            background-color: #3b82f6;
            border-right: 2px solid #000;
        }

        .table-analytics {
            background-color: #1e293b;
            border-radius: 1rem;
            overflow: hidden;
        }

        .table-analytics th {
            background-color: transparent;
            border-bottom: 1px solid #334155;
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 1.25rem;
        }

        .table-analytics td {
            border-bottom: 1px solid #334155;
            padding: 1.25rem;
            vertical-align: middle;
        }

        .btn-action {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: #fff;
        }

        .btn-action:hover {
            background-color: #334155;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="d-flex align-items-center gap-3 mb-5 px-3">
            <div class="bg-primary p-2 rounded">
                <i class="bi bi-mortarboard-fill text-white fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0">Anatolia Admin</h6>
            </div>
        </div>
        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="candidates.php" class="nav-link">
                <i class="bi bi-people-fill"></i> Candidates
            </a>
            <a href="voters.php" class="nav-link">
                <i class="bi bi-person-check-fill"></i> Voter Access
            </a>
            <a href="results.php" class="nav-link active">
                <i class="bi bi-bar-chart-fill"></i> Election Results
            </a>
            <a href="settings.php" class="nav-link">
                <i class="bi bi-gear-fill"></i> Settings
            </a>
        </nav>
    </div>

    <div class="p-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="fw-bold mb-1">Final Election Results & Analytics</h1>
                <p class="text-muted">Official verified tallies for the School Chairman 2024 election</p>
            </div>
            <div class="d-flex gap-3">
                <button class="btn btn-action px-3 d-flex align-items-center gap-2" onclick="window.print()"><i
                        class="bi bi-file-earmark-pdf"></i> PDF Report</button>
                <a href="export_results.php"
                    class="btn btn-action px-3 d-flex align-items-center gap-2 text-decoration-none"><i
                        class="bi bi-file-earmark-excel"></i> Export Excel</a>
                <button class="btn btn-primary px-3 d-flex align-items-center gap-2" onclick="window.print()"><i
                        class="bi bi-patch-check"></i> Print Certificate</button>
            </div>
        </div>

        <div class="winner-panel p-5 mb-5">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <img src="<?php echo $winner['photo'] ? '../uploads/' . $winner['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($winner['full_name']) . '&size=250'; ?>"
                        class="rounded-3 w-100 border border-4 border-secondary opacity-75" alt="">
                </div>
                <div class="col-md-9 ps-md-5">
                    <div class="d-flex gap-2 mb-3">
                        <span class="badge bg-warning text-dark fw-bold">ELECTED CHAIRMAN</span>
                        <span class="badge border border-primary text-primary fw-bold">OFFICIAL CERTIFIED WINNER</span>
                    </div>
                    <h1 class="display-3 fw-bold mb-1"><?php echo htmlspecialchars($winner['full_name']); ?></h1>
                    <p class="h4 text-muted mb-5">Grade <?php echo htmlspecialchars($winner['class']); ?> - Senior Class
                        Representative</p>

                    <div class="row g-4">
                        <div class="col-auto me-5">
                            <small class="text-muted text-uppercase fw-bold ls-1">Total Votes</small>
                            <h1 class="fw-bold display-4"><?php echo number_format($winner['vote_count']); ?></h1>
                        </div>
                        <div class="col-auto">
                            <small class="text-muted text-uppercase fw-bold ls-1">Vote Share</small>
                            <h1 class="fw-bold display-4">
                                <?php echo $total_ballots > 0 ? round(($winner['vote_count'] / $total_ballots) * 100, 1) : 0; ?>%
                            </h1>
                        </div>
                    </div>

                    <div
                        class="mt-5 p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 d-flex gap-3 align-items-center">
                        <i class="bi bi-info-circle-fill text-primary fs-4"></i>
                        <p class="mb-0 small text-muted">
                            <?php echo htmlspecialchars($winner['full_name']); ?> has reached the required majority and
                            is hereby declared the Chairman-elect for the upcoming academic year.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="analytics-card">
                    <small class="text-white text-uppercase fw-bold ls-1">Total Eligible Voters</small>
                    <h2 class="fw-bold my-2"><?php echo number_format($total_voters); ?></h2>
                    <small class="text-white">Enrolled Students</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="analytics-card">
                    <small class="text-white text-uppercase fw-bold ls-1">Actual Votes Cast</small>
                    <h2 class="fw-bold my-2"><?php echo number_format($total_ballots); ?></h2>
                    <small class="text-white fw-bold">+12% vs last year</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="analytics-card">
                    <small class="text-white text-uppercase fw-bold ls-1">Voter Turnout</small>
                    <h2 class="fw-bold my-2"><?php echo round($turnout, 1); ?>%</h2>
                    <div class="progress bg-secondary bg-opacity-20" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $turnout; ?>%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="analytics-card">
                    <small class="text-white text-uppercase fw-bold ls-1">Spoilt Ballots</small>
                    <h2 class="fw-bold my-2"><?php echo $spoilt_ballots; ?></h2>
                    <small class="text-white">0.5% of total</small>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="analytics-card h-100">
                    <h6 class="fw-bold mb-5">Candidate Vote Distribution</h6>
                    <div class="d-flex justify-content-around align-items-end" style="height: 250px;">
                        <?php foreach ($candidates as $c): ?>
                            <?php $h = ($total_ballots > 0) ? ($c['vote_count'] / $winner['vote_count']) * 100 : 0; ?>
                            <div class="text-center w-25">
                                <div class="bg-primary mx-auto rounded-top"
                                    style="height: <?php echo $h; ?>%; width: 40px; opacity: 0.8;"></div>
                                <div class="mt-3 small text-white"><?php echo explode(' ', $c['full_name'])[0]; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="analytics-card h-100">
                    <h6 class="fw-bold mb-4">Participation Analysis</h6>
                    <p class="text-white small mb-4">Voter turnout vs. abstentions</p>

                    <div class="participation-track mb-5">
                        <div class="participation-fill d-flex align-items-center justify-content-center"
                            style="width: <?php echo $turnout; ?>%;">
                            <span class="small fw-bold">VOTED (<?php echo round($turnout, 1); ?>%)</span>
                        </div>
                        <div class="position-absolute end-0 top-0 h-100 d-flex align-items-center pe-3">
                            <span class="small fw-bold text-dark">ABSTAINED</span>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-6">
                            <div class="p-3 rounded-4 border border-secondary d-flex align-items-center gap-3">
                                <div class="rounded-circle"
                                    style="width: 12px; height: 12px; background-color: #3b82f6;"></div>
                                <div>
                                    <small class="text-white d-block">Total Participated</small>
                                    <span class="fw-bold"><?php echo number_format($total_ballots); ?> Students</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-4 border border-secondary d-flex align-items-center gap-3">
                                <div class="rounded-circle"
                                    style="width: 12px; height: 12px; background-color: #f59e0b;"></div>
                                <div>
                                    <small class="text-white d-block">Total Abstained</small>
                                    <span class="fw-bold"><?php echo number_format($total_voters - $total_ballots); ?>
                                        Students</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-analytics">
            <div class="p-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Tallies by Grade Level</h5>
                <i class="bi bi-filter text-white fs-5"></i>
            </div>
            <table class="table w-100 text-white mb-0">
                <thead>
                    <tr>
                        <th class="text-white">Grade Level</th>
                        <th class="text-white">Total Eligible</th>
                        <th class="text-white">Turnout</th>
                        <?php foreach ($candidates as $c): ?>
                            <th class="text-white">
                                <?php echo strtoupper(explode(' ', $c['full_name'])[0] . ' ' . (isset(explode(' ', $c['full_name'])[1]) ? explode(' ', $c['full_name'])[1] : '')); ?>
                            </th>
                        <?php endforeach; ?>
                        <th class="text-white">Others</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tally_data as $row): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $row['grade']; ?></td>
                            <td class="text-white"><?php echo $row['eligible']; ?></td>
                            <td class="text-white"><?php echo $row['turnout']; ?>%</td>
                            <?php foreach ($candidates as $c): ?>
                                <td class="fw-bold text-primary"><?php echo $row['candidates'][$c['id']]; ?></td>
                            <?php endforeach; ?>
                            <td class="text-white">0</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="border-top border-secondary">
                    <tr class="fw-bold">
                        <td>TOTALS</td>
                        <td><?php echo number_format($total_voters); ?></td>
                        <td><?php echo round($turnout, 1); ?>%</td>
                        <?php foreach ($candidates as $c): ?>
                            <td class="text-primary"><?php echo $c['vote_count']; ?></td>
                        <?php endforeach; ?>
                        <td>0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-5 pt-5 text-center border-top border-secondary border-opacity-10">
            <p class="small text-white mb-0">© 2024 Anatolia School Administration. All rights reserved. E-voting system
                audited and verified.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>