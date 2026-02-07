<?php
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit;
}

// Handle Lock/Unlock
if (isset($_GET['toggle_lock'])) {
    $id = $_GET['toggle_lock'];
    $stmt = $pdo->prepare("UPDATE students SET is_locked = NOT is_locked WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: voters.php');
    exit;
}

// Handle Save (Add/Edit) Student
// Handle Save (Add/Edit) Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_student'])) {
    $id = $_POST['id'] ?? '';
    $sid = $_POST['student_id'];
    $name = $_POST['full_name'];
    $class = $_POST['class'];
    $email = $_POST['email'];
    $raw_password = $_POST['password'] ?? '';

    if ($id) {
        // Update
        if (!empty($raw_password)) {
            $pass = password_hash($raw_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE students SET student_id=?, full_name=?, class=?, email=?, password=? WHERE id=?");
            $stmt->execute([$sid, $name, $class, $email, $pass, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE students SET student_id=?, full_name=?, class=?, email=? WHERE id=?");
            $stmt->execute([$sid, $name, $class, $email, $id]);
        }
    } else {
        // Insert
        $pass_to_hash = !empty($raw_password) ? $raw_password : 'student123';
        $pass = password_hash($pass_to_hash, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO students (student_id, full_name, class, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$sid, $name, $class, $email, $pass]);
    }
    header('Location: voters.php?success=1');
    exit;
}

// Handle Bulk Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_upload'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        // Skip header
        fgetcsv($file);

        $count = 0;
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO students (student_id, full_name, class, email, password) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), class = VALUES(class), password = VALUES(password)");

            while (($row = fgetcsv($file)) !== false) {
                // Expected format: ID, Name, Class, Email, Password
                if (count($row) >= 5) {
                    $raw_pass = $row[4]; // 5th column is password
                    $hashed_pass = password_hash($raw_pass, PASSWORD_DEFAULT);
                    $stmt->execute([$row[0], $row[1], $row[2], $row[3], $hashed_pass]);
                    $count++;
                }
            }
            $pdo->commit();
            $message = "Successfully imported $count students with unique passwords.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error importing file: " . $e->getMessage();
        }
        fclose($file);
    }
}

$students = $pdo->query("SELECT * FROM students ORDER BY student_id ASC")->fetchAll();
$total_count = count($students);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Access Management - Anatolia School E-Voting</title>
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

        .badge-active {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-locked {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .badge-voted {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .badge-notvoted {
            background-color: rgba(107, 114, 128, 0.1);
            color: #9ca3af;
            border: 1px solid rgba(107, 114, 128, 0.2);
        }

        .btn-lock {
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.3);
            background-color: rgba(245, 158, 11, 0.05);
        }

        .btn-lock:hover {
            background-color: #f59e0b;
            color: #000;
        }

        .btn-unlock {
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.3);
            background-color: rgba(16, 185, 129, 0.05);
        }

        .btn-unlock:hover {
            background-color: #10b981;
            color: #fff;
        }

        .search-input {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: white;
            padding-left: 2.5rem;
        }

        .search-wrapper {
            position: relative;
            width: 450px;
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
            <a href="candidates.php" class="nav-link">
                <i class="bi bi-people-fill"></i> Candidates
            </a>
            <a href="voters.php" class="nav-link active">
                <i class="bi bi-person-check-fill"></i> Voter Access
            </a>
            <a href="results.php" class="nav-link">
                <i class="bi bi-bar-chart-fill"></i> Election Results
            </a>
            <a href="settings.php" class="nav-link">
                <i class="bi bi-gear-fill"></i> Settings
            </a>
        </nav>
    </div>

    <div class="p-5">
        <div class="d-flex justify-content-between align-items-start mb-5">
            <div>
                <h1 class="fw-bold text-white mb-2">Voter Access Management</h1>
                <p class="text-white">Manage student enrollment, account status, and track voting participation.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary px-4 py-2 d-flex align-items-center gap-2"
                    style="background-color: #1e293b;" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                    <i class="bi bi-cloud-arrow-up-fill"></i> Bulk Upload Students
                </button>
                <button class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2" data-bs-toggle="modal"
                    data-bs-target="#addStudentModal" onclick="resetModal()">
                    <i class="bi bi-person-plus-fill"></i> Manually Add Student
                </button>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-success fs-5"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger fs-5"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" class="form-control search-input"
                    placeholder="Search by Student ID, Name or Class...">
            </div>
            <div class="d-flex gap-2 p-1 rounded-3" style="background-color: #1e293b;">
                <button class="btn btn-primary btn-sm px-3 active">All Students</button>
                <button class="btn btn-link btn-sm text-muted text-decoration-none px-3">Active</button>
                <button class="btn btn-link btn-sm text-muted text-decoration-none px-3">Locked</button>
                <button class="btn btn-outline-secondary btn-sm border-0"><i class="bi bi-filter"></i></button>
            </div>
        </div>

        <div class="data-table-card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Class</th>
                        <th>Account Status</th>
                        <th>Voting Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No students found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><span class="text-muted">#<?php echo htmlspecialchars($s['student_id']); ?></span></td>
                                <td><span class="fw-bold text-white"><?php echo htmlspecialchars($s['full_name']); ?></span>
                                </td>
                                <td><span class="text-muted"><?php echo htmlspecialchars($s['class'] ?? 'N/A'); ?></span></td>
                                <td>
                                    <span
                                        class="badge rounded-pill <?php echo !$s['is_locked'] ? 'badge-active' : 'badge-locked'; ?>">
                                        ● <?php echo !$s['is_locked'] ? 'Active' : 'Locked'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge rounded-pill <?php echo $s['has_voted'] ? 'badge-voted' : 'badge-notvoted'; ?>">
                                        <?php echo $s['has_voted'] ? 'Voted' : 'Not Voted'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="?toggle_lock=<?php echo $s['id']; ?>"
                                        class="btn <?php echo !$s['is_locked'] ? 'btn-lock' : 'btn-unlock'; ?> btn-sm px-3 me-2">
                                        <i
                                            class="bi <?php echo !$s['is_locked'] ? 'bi-lock-fill' : 'bi-unlock-fill'; ?> me-1"></i>
                                        <?php echo !$s['is_locked'] ? 'Lock' : 'Unlock'; ?>
                                    </a>
                                    <button class="btn btn-link text-muted p-0"
                                        onclick='editStudent(<?php echo json_encode($s); ?>)' data-bs-toggle="modal"
                                        data-bs-target="#addStudentModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="p-3 border-top border-secondary d-flex justify-content-between align-items-center">
                <small class="text-muted">Displaying <?php echo count($students); ?> of <?php echo $total_count; ?>
                    students</small>
                <!-- Pagination omitted for brevity as in original -->
            </div>
        </div>
    </div>

    <!-- Add/Edit Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white" id="studentModalTitle">Manually Add Student</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="studentForm">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-white">Student ID</label>
                            <input type="text" name="student_id" id="edit_student_id" class="form-control"
                                placeholder="e.g. 2024-0012" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white">Full Name</label>
                            <input type="text" name="full_name" id="edit_full_name" class="form-control"
                                placeholder="Enter student's full name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white">Class</label>
                            <input type="text" name="class" id="edit_class" class="form-control"
                                placeholder="e.g. Grade 12-A" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control"
                                placeholder="student@anatolia.edu.gr" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white">Password</label>
                            <input type="text" name="password" id="edit_password" class="form-control"
                                placeholder="Set student password (leave blank on edit to keep current)">
                            <div class="form-text text-white small" id="passwordNote">Default:
                                <strong>student123</strong> if left blank on new student.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_student" class="btn btn-primary px-4">Save Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkUploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white">Bulk Upload Students</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-white">Upload CSV File</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                            <div class="form-text text-white">CSV Format: Student ID, Full Name, Class, Email,
                                <strong>Password</strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="bulk_upload" class="btn btn-primary px-4">Upload & Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editStudent(data) {
            document.getElementById('studentModalTitle').innerText = 'Edit Student';
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_student_id').value = data.student_id;
            document.getElementById('edit_full_name').value = data.full_name;
            document.getElementById('edit_class').value = data.class;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_password').value = '';
            document.getElementById('passwordNote').style.display = 'none';
        }

        function resetModal() {
            document.getElementById('studentModalTitle').innerText = 'Manually Add Student';
            document.getElementById('studentForm').reset();
            document.getElementById('edit_id').value = '';
            document.getElementById('edit_password').value = '';
            document.getElementById('passwordNote').style.display = 'block';
        }
    </script>
</body>

</html>