<?php
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_candidate'])) {
    $id = $_POST['candidate_id'] ?? '';
    $name = $_POST['full_name'];
    $class = $_POST['class'];
    $bio = $_POST['bio'];
    $mission = $_POST['mission'];
    $status = $_POST['status'] ?? 'published';

    $photo_name = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_name = time() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/' . $photo_name);
    }

    if ($id) {
        // Update existing
        $sql = "UPDATE candidates SET full_name=?, class=?, bio=?, mission=?, status=?";
        $params = [$name, $class, $bio, $mission, $status];
        if ($photo_name) {
            $sql .= ", photo=?";
            $params[] = $photo_name;
        }
        $sql .= " WHERE id=?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        // Insert new
        $sql = "INSERT INTO candidates (full_name, class, bio, mission, status";
        $params = [$name, $class, $bio, $mission, $status];
        if ($photo_name) {
            $sql .= ", photo) VALUES (?, ?, ?, ?, ?, ?)";
            $params[] = $photo_name;
        } else {
            $sql .= ") VALUES (?, ?, ?, ?, ?)";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    echo "<script>window.location.href='candidates.php?success=1';</script>";
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: candidates.php');
    exit;
}

$candidates = $pdo->query("SELECT * FROM candidates ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Management - Anatolia School E-Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            padding-left: 280px;
            background-color: #0f172a;
        }

        @media (max-width: 991px) {
            body {
                padding-left: 0;
            }
        }

        .avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .badge-published {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-draft {
            background-color: rgba(107, 114, 128, 0.1);
            color: #9ca3af;
            border: 1px solid rgba(107, 114, 128, 0.2);
        }

        .search-input {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: white;
            padding-left: 2.5rem;
        }

        .search-wrapper {
            position: relative;
            width: 400px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
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
            <a href="candidates.php" class="nav-link active">
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
            <div class="px-3">
                <img src="https://ui-avatars.com/api/?name=Admin&background=random" class="rounded-circle" width="40"
                    alt="">
            </div>
        </div>
    </div>

    <div class="p-5">
        <div class="d-flex justify-content-between align-items-start mb-5">
            <div>
                <h1 class="fw-bold text-white mb-2">Candidate Management</h1>
                <p class="text-white">Manage student candidates for the 2024 Chairman election.</p>
            </div>
            <button class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2" data-bs-toggle="modal"
                data-bs-target="#addCandidateModal" onclick="resetModal()">
                <i class="bi bi-person-plus-fill"></i> Add New Candidate
            </button>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex gap-2">
                <div class="search-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="form-control search-input" placeholder="Search by name or class">
                </div>
                <!-- ... filters ... -->
            </div>
        </div>

        <div class="data-table-card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Class / Grade</th>
                        <th>Status</th>
                        <th>Votes (Live)</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($candidates)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No candidates found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($candidates as $c): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?php echo $c['photo'] ? '../uploads/' . $c['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($c['full_name']); ?>"
                                            class="avatar-sm" alt="">
                                        <div>
                                            <div class="fw-bold text-white"><?php echo htmlspecialchars($c['full_name']); ?>
                                            </div>
                                            <div class="text-muted small">Candidate #<?php echo sprintf('%02d', $c['id']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="text-muted">Grade <?php echo htmlspecialchars($c['class']); ?></span></td>
                                <td>
                                    <span
                                        class="badge rounded-pill <?php echo $c['status'] == 'published' ? 'badge-published' : 'badge-draft'; ?>">
                                        <?php echo ucfirst($c['status'] ?? 'published'); ?>
                                    </span>
                                </td>
                                <td><span class="text-primary fw-bold"><?php echo $c['vote_count']; ?></span></td>
                                <td class="text-end">
                                    <button class="btn btn-link text-muted p-0 me-3"
                                        onclick='editCandidate(<?php echo json_encode($c); ?>)' data-bs-toggle="modal"
                                        data-bs-target="#addCandidateModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?delete=<?php echo $c['id']; ?>" class="btn btn-link text-muted p-0"
                                        onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark border-secondary p-2">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="fw-bold text-white mb-1" id="modalTitle">Add Candidate</h4>
                        <p class="text-muted small mb-0">Fill in the details for the Anatolia School Chairman election
                            candidate.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="candidateForm">
                    <input type="hidden" name="candidate_id" id="edit_candidate_id">
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label small text-uppercase fw-bold text-primary"><i
                                    class="bi bi-person-circle me-2"></i> Candidate Photo</label>
                            <div class="p-4 rounded-3 border-secondary border-dashed text-center"
                                style="border: 2px dashed #334155; background: rgba(30, 41, 59, 0.5);">
                                <!-- ... photo upload ... -->
                                <div class="d-flex align-items-center gap-4 text-start">
                                    <div class="bg-secondary rounded-3 d-flex align-items-center justify-content-center"
                                        style="width: 100px; height: 100px;">
                                        <i class="bi bi-image text-muted fs-1"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-white mb-1">Upload Profile Picture</h6>
                                        <p class="text-muted small mb-3">JPG, PNG or WEBP. Max size 2MB.</p>
                                        <div class="d-flex gap-2">
                                            <label class="btn btn-primary btn-sm px-3">
                                                Browse Files <input type="file" name="photo" hidden
                                                    onchange="this.parentElement.previousElementSibling.innerText=this.files[0].name">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top border-secondary pt-4 mb-4">
                            <h6 class="fw-bold text-white mb-3">Personal Information</h6>
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label text-muted small">Full Name</label>
                                    <input type="text" name="full_name" id="edit_full_name" class="form-control"
                                        placeholder="Enter candidate's full name" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label text-muted small">Class</label>
                                    <select name="class" id="edit_class"
                                        class="form-select bg-dark text-white border-secondary">
                                        <option value="" selected disabled>Select candidate class</option>
                                        <option value="10-A">Grade 10-A</option>
                                        <option value="11-C">Grade 11-C</option>
                                        <option value="12-B">Grade 12-B</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="border-top border-secondary pt-4 mb-4">
                            <h6 class="fw-bold text-white mb-3">Candidate Statements</h6>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Bio / Who I Am</label>
                                <textarea name="bio" id="edit_bio" class="form-control" rows="3"
                                    placeholder="Brief introduction about the candidate..." required></textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label text-muted small">Mission / Vision Statement</label>
                                <textarea name="mission" id="edit_mission" class="form-control" rows="3"
                                    placeholder="What does the candidate plan to achieve?" required></textarea>
                            </div>
                            <div class="mt-3">
                                <label class="form-label text-muted small">Status</label>
                                <select name="status" id="edit_status"
                                    class="form-select bg-dark text-white border-secondary">
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-link text-muted text-decoration-none"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_candidate"
                            class="btn btn-primary px-4 d-flex align-items-center gap-2">
                            <i class="bi bi-save-fill"></i> Save Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCandidate(data) {
            document.getElementById('modalTitle').innerText = 'Edit Candidate';
            document.getElementById('edit_candidate_id').value = data.id;
            document.getElementById('edit_full_name').value = data.full_name;
            document.getElementById('edit_class').value = data.class;
            document.getElementById('edit_bio').value = data.bio;
            document.getElementById('edit_mission').value = data.mission;
            document.getElementById('edit_status').value = data.status || 'published';
        }

        function resetModal() {
            document.getElementById('modalTitle').innerText = 'Add Candidate';
            document.getElementById('candidateForm').reset();
            document.getElementById('edit_candidate_id').value = '';
            document.getElementById('edit_status').value = 'published';
        }
    </script>
</body>

</html>