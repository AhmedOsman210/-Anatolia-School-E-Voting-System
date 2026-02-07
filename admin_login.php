<?php
require_once 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['role'] = 'admin';
        header('Location: admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Anatolia School E-Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: radial-gradient(circle at center, #1a253a 0%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
    </style>
</head>

<body>
    <div class="text-center w-100" style="max-width: 450px;">
        <div class="mb-4">
            <div class="bg-primary d-inline-flex p-3 rounded-circle mb-3 shadow-lg">
                <i class="bi bi-mortarboard-fill text-white fs-3"></i>
            </div>
            <h5 class="fw-bold">Anatolia School</h5>
        </div>

        <div class="auth-card mx-auto text-start">
            <div class="text-center mb-4">
                <small class="text-primary text-uppercase fw-bold ls-1"><i class="bi bi-shield-lock me-1"></i> Secure
                    E-Voting Portal</small>
                <h2 class="fw-bold mt-2">Admin Login</h2>
                <p class="text-muted">Sign in to manage the School Chairman election.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control py-2" placeholder="Enter admin username"
                        required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control py-2" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-lg">
                    Access Admin Panel <i class="bi bi-arrow-right ms-2"></i>
                </button>

                <div class="divider d-flex align-items-center my-4">
                    <div class="flex-grow-1 bg-secondary opacity-25" style="height: 1px;"></div>
                    <span class="mx-3 text-muted small">OR LOGIN WITH SSO</span>
                    <div class="flex-grow-1 bg-secondary opacity-25" style="height: 1px;"></div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <button type="button"
                            class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                            <img src="https://www.google.com/favicon.ico" width="16" alt=""> Google
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button"
                            class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-microsoft text-info"></i> Office 365
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="mt-5 text-muted small">
            <a href="#" class="text-muted text-decoration-none mx-2">Privacy Policy</a> •
            <a href="#" class="text-muted text-decoration-none mx-2">Election Rules</a> •
            <a href="#" class="text-muted text-decoration-none mx-2">Help Center</a>
        </div>

        <div class="mt-4">
            <span
                class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                <i class="bi bi-circle-fill me-2 fs-xs" style="font-size: 8px;"></i> System Status: Live
            </span>
        </div>
    </div>
</body>

</html>