<?php
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $status = $_POST['election_status'];
    $title = $_POST['election_title'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $updates = [
        'election_status' => $status,
        'election_title' => $title,
        'start_time' => $start_time,
        'end_time' => $end_time
    ];

    foreach ($updates as $key => $val) {
        $stmt = $pdo->prepare("UPDATE election_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$val, $key]);
    }

    $message = 'Settings updated successfully!';
}

$settings_raw = $pdo->query("SELECT * FROM election_settings")->fetchAll();
$settings = [];
foreach ($settings_raw as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Settings - Anatolia School E-Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <div class="sidebar">
        <!-- Sidebar content (Same as others) -->
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
            <a href="dashboard.php" class="nav-link">
                <i class="bi bi-grid-fill"></i> Overview
            </a>
            <a href="candidates.php" class="nav-link">
                <i class="bi bi-people-fill"></i> Candidate Management
            </a>
            <a href="voters.php" class="nav-link">
                <i class="bi bi-person-check-fill"></i> Voter Access
            </a>
            <a href="settings.php" class="nav-link active">
                <i class="bi bi-gear-fill"></i> Election Settings
            </a>
        </nav>
    </div>

    <div class="p-5">
        <h2 class="fw-bold mb-5">Election Settings</h2>

        <?php if ($message): ?>
            <div class="alert alert-info">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="stat-card" style="max-width: 600px;">
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">Election Title</label>
                    <input type="text" name="election_title" class="form-control"
                        value="<?php echo htmlspecialchars($settings['election_title']); ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Election Status</label>
                    <select name="election_status" class="form-select bg-dark text-white border-secondary">
                        <option value="pending" <?php echo $settings['election_status'] == 'pending' ? 'selected' : ''; ?>>Manual: Pending (Not started)</option>
                        <option value="active" <?php echo $settings['election_status'] == 'active' ? 'selected' : ''; ?>>Manual: Active (Voting open)</option>
                        <option value="closed" <?php echo $settings['election_status'] == 'closed' ? 'selected' : ''; ?>>Manual: Closed (Voting ended)</option>
                    </select>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-control" value="<?php echo htmlspecialchars($settings['start_time']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Time</label>
                        <input type="datetime-local" name="end_time" class="form-control" value="<?php echo htmlspecialchars($settings['end_time']); ?>">
                    </div>
                    <div class="form-text text-muted mt-2">
                        If Start/End times are set, they will determine the status and override manual selection.
                    </div>
                </div>

                <hr class="border-secondary my-4">

                <button type="submit" name="update_settings" class="btn btn-primary px-5 py-2">Save Changes</button>
            </form>
        </div>
    </div>
</body>

</html>