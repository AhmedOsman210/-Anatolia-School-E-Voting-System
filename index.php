<?php
require_once 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($student_id) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? OR email = ?");
        $stmt->execute([$student_id, $student_id]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password'])) {
            if ($student['is_locked']) {
                $error = 'Your account has been locked. Please contact administration.';
            } else {
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['role'] = 'student';
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Login - Anatolia School E-Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-image-side">
            <div class="mb-4">
                <div class="bg-primary d-inline-flex p-3 rounded-3 mb-4">
                    <i class="bi bi-person-check-fill text-white fs-3"></i>
                </div>
                <h1 class="display-4 fw-bold mb-3">Empowering<br>Student Voices</h1>
                <p class="fs-5 text-muted max-width-400">
                    Join your fellow students in shaping the future of Anatolia School. Your vote is secure,
                    confidential, and vital for our community.
                </p>
            </div>
            <div class="mt-5 pt-5 border-top border-secondary">
                <small class="text-muted">© 2024 ANATOLIA SCHOOL SYSTEMS</small>
            </div>
        </div>
        <div class="auth-form-side">
            <div class="w-100">
                <div class="text-center mb-5">
                    <h2 class="fw-bold mb-2">Voter Login</h2>
                    <p class="text-muted">Enter your credentials to access the voting booth</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label">Student ID or School Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" name="student_id" class="form-control border-start-0 border-secondary"
                                placeholder="e.g. AS-2024-XXXXX" required>
                        </div>
                    </div>

                    <div class="mb-5">
                        <div class="d-flex justify-content-between">
                            <label class="form-label">Password</label>
                            <a href="#" class="text-decoration-none small text-primary">Forgot password?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password" name="password" class="form-control border-start-0 border-secondary"
                                placeholder="Enter your secure password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 mb-5">Log In to Vote</button>

                    <div class="divider d-flex align-items-center my-4">
                        <div class="flex-grow-1 bg-secondary opacity-25" style="height: 1px;"></div>
                        <span class="mx-3 text-muted small">Authorized Personnel</span>
                        <div class="flex-grow-1 bg-secondary opacity-25" style="height: 1px;"></div>
                    </div>

                    <div class="text-center">
                        <a href="admin_login.php" class="btn btn-outline-secondary w-100 py-2">
                            <i class="bi bi-shield-lock me-2"></i> Administrator Login
                        </a>
                    </div>
                </form>

                <div class="mt-5 p-4 border border-secondary rounded-3 bg-dark-subtle">
                    <div class="d-flex gap-3">
                        <i class="bi bi-info-circle text-primary fs-5"></i>
                        <p class="small text-muted mb-0">
                            Need help logging in? Contact the student services office or visit the IT helpdesk in the
                            library during school hours.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>