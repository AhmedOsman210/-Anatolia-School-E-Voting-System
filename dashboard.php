<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit;
}

$election_status = get_election_status($pdo);
$time_remaining = get_time_remaining_str($pdo);
$time_parts = explode(':', $time_remaining);

// If election is closed, redirect to results
if ($election_status == 'closed') {
    header('Location: results.php');
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch Student Data
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student_data = $stmt->fetch();

// Fetch Candidates
$candidates = $pdo->query("SELECT * FROM candidates WHERE status = 'published' ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Selection - Anatolia School E-Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
        }

        .navbar {
            background-color: #0f172a;
        }

        .nav-link {
            color: #94a3b8;
        }

        .nav-link.active {
            background-color: transparent;
            border-bottom: 2px solid #3b82f6;
            color: #3b82f6;
            border-radius: 0;
        }

        .sidebar-light {
            width: 200px;
            border-right: 1px solid #e2e8f0;
            height: calc(100vh - 72px);
            position: fixed;
            left: 0;
            top: 72px;
            padding: 2rem 1.5rem;
            background: white;
        }

        .main-content {
            margin-left: 200px;
            padding: 2rem 4rem;
            margin-top: 72px;
        }

        .candidate-tag {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .vote-footer {
            position: fixed;
            bottom: 2rem;
            left: 240px;
            right: 40px;
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            border: 1px solid #e2e8f0;
        }

        .candidate-card {
            cursor: pointer;
            transition: all 0.2s ease;
            height: 100%;
            border-radius: 1rem;
            overflow: hidden;
            background: white;
        }

        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .candidate-card.selected {
            border: 2px solid #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .check-overlay {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: rgba(59, 130, 246, 0.1);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .candidate-card.selected .check-overlay {
            display: flex;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg border-bottom border-secondary border-opacity-25 fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2 text-white fw-bold" href="#">
                <div class="bg-primary p-1 rounded">
                    <i class="bi bi-mortarboard-fill text-white"></i>
                </div>
                Anatolia School E-Voting
            </a>
            <div class="collapse navbar-collapse justify-content-center">
                <ul class="navbar-nav gap-4">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Candidates</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Election Info</a></li>
                    <li class="nav-item"><a class="nav-link" href="results.php">Results</a></li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-bell text-muted fs-5"></i>
                <div class="d-flex align-items-center gap-2 text-end">
                    <div>
                        <div class="fw-bold text-white small">
                            <?php echo htmlspecialchars($student_data['full_name']); ?>
                        </div>
                        <div class="text-muted" style="font-size: 10px;">ID:
                            <?php echo htmlspecialchars($student_data['student_id']); ?>
                        </div>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student_data['full_name']); ?>&background=random"
                        class="rounded-circle" width="35" alt="">
                </div>
            </div>
        </div>
    </nav>

    <div class="sidebar-light">
        <h6 class="text-uppercase text-muted fw-bold mb-4" style="font-size: 11px;">Election Guide</h6>
        <div class="nav flex-column gap-2">
            <a href="#" class="nav-link text-primary bg-primary bg-opacity-10 rounded px-3 py-2 small fw-bold"><i
                    class="bi bi-info-circle me-2"></i> Instructions</a>
            <a href="#" class="nav-link text-muted px-3 py-2 small"><i class="bi bi-shield-check me-2"></i> Security
                Policy</a>
            <a href="#" class="nav-link text-muted px-3 py-2 small"><i class="bi bi-question-circle me-2"></i> Need
                Help?</a>
        </div>

        <div class="mt-5 p-3 rounded-3" style="background-color: #fff9eb; border: 1px solid #ffeeba;">
            <h6 class="fw-bold text-warning-emphasis mb-2" style="font-size: 12px;">NOTICE</h6>
            <p class="mb-0 text-warning-emphasis" style="font-size: 11px;">You can only vote once. Please review
                candidates carefully before submitting your choice.</p>
        </div>
    </div>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-start mb-5">
            <div>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 mb-2">●
                    Live Election</span>
                <h1 class="fw-bold">Candidate Selection</h1>
                <p class="text-muted">Browse through the nominated candidates for the 2024/25 Student Council Chairman
                    position.</p>
            </div>
            <div class="text-center bg-white p-3 rounded-3 border border-light-subtle shadow-sm">
                <div class="text-uppercase text-muted fw-bold mb-2" style="font-size: 10px;">Voting Ends In</div>
                <div class="d-flex gap-3 align-items-center">
                    <div><span class="h4 fw-bold">04</span><br><small class="text-muted"
                            style="font-size: 10px;">Hours</small></div>
                    <div class="h4 text-muted">:</div>
                    <div><span class="h4 fw-bold">32</span><br><small class="text-muted"
                            style="font-size: 10px;">Minutes</small></div>
                    <div class="h4 text-muted">:</div>
                    <div><span class="h4 fw-bold text-primary">15</span><br><small class="text-muted"
                            style="font-size: 10px;">Seconds</small></div>
                </div>
            </div>
        </div>

        <form action="submit_vote.php" method="POST" id="votingForm">
            <input type="hidden" name="candidate_id" id="selectedCandidate">
            <div class="row g-4 mb-5 pb-5">
                <?php if (empty($candidates)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-people text-muted display-1"></i>
                        <p class="mt-3 text-muted">No candidates have been added yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="candidate-card border"
                                onclick="selectCandidate(this, <?php echo $candidate['id']; ?>, '<?php echo addslashes($candidate['full_name']); ?>')">
                                <div class="position-relative">
                                    <div class="check-overlay">
                                        <i class="bi bi-check-circle-fill text-primary display-4"></i>
                                    </div>
                                    <img src="<?php echo $candidate['photo'] ? 'uploads/' . $candidate['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($candidate['full_name']); ?>"
                                        class="candidate-img" style="height: 250px; object-fit: cover; width: 100%;" alt="">
                                    <span class="candidate-tag">No.
                                        <?php echo sprintf('%02d', $candidate['id']); ?>
                                    </span>
                                </div>
                                <div class="candidate-info p-3">
                                    <h5 class="fw-bold mb-1">
                                        <?php echo htmlspecialchars($candidate['full_name']); ?>
                                    </h5>
                                    <p class="text-muted small mb-3">Class
                                        <?php echo htmlspecialchars($candidate['class']); ?> • Science Stream
                                    </p>
                                    <button type="button" class="btn btn-sm btn-outline-primary w-100 fw-bold">Select
                                        Candidate</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($student_data['has_voted']): ?>
                <div class="alert alert-info py-4 text-center">
                    <i class="bi bi-check-circle-fill me-2 fs-4"></i> You have already cast your vote. Results will be
                    available
                    once the election ends.
                </div>
            <?php else: ?>
                <div class="vote-footer">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-secondary bg-opacity-10 p-2 rounded-circle">
                            <i class="bi bi-check2 text-muted fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" id="selectionText">No candidate selected</h6>
                            <p class="text-muted small mb-0">Select a candidate above to enable the voting button.</p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary px-5 py-3 fw-bold disabled" id="voteBtn"
                        onclick="confirmVote()">
                        VOTE NOW <i class="bi bi-lock-fill ms-2"></i>
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </main>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Confirm Your Vote</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="text-muted mb-4">You are about to cast your vote for:</p>
                    <h3 class="fw-bold text-primary mb-2" id="confirmCandidateName">Candidate Name</h3>
                    <p class="small text-danger mt-3 mb-0"><i class="bi bi-exclamation-triangle-fill me-1"></i> This
                        action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4"
                        onclick="document.getElementById('votingForm').submit()">Confirm Vote</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectCandidate(card, id, name) {
            // Remove active class from all
            document.querySelectorAll('.candidate-card').forEach(c => c.classList.remove('selected'));

            // Add to clicked
            card.classList.add('selected');

            // Update Footer
            document.getElementById('selectedCandidate').value = id;
            document.getElementById('selectionText').innerHTML = 'Selected: <span class="text-primary">' + name + '</span>';

            // Enable Button
            const btn = document.getElementById('voteBtn');
            btn.classList.remove('btn-secondary', 'disabled');
            btn.classList.add('btn-primary');
            btn.innerHTML = 'VOTE NOW <i class="bi bi-check-circle-fill ms-2"></i>';

            // Store name for modal
            document.getElementById('confirmCandidateName').innerText = name;
        }

        function confirmVote() {
            if (document.getElementById('selectedCandidate').value) {
                new bootstrap.Modal(document.getElementById('confirmModal')).show();
            }
        }
    </script>
</body>

</html>