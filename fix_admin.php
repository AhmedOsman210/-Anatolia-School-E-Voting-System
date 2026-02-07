<?php
require_once 'includes/db.php';

$password = password_hash('admin123', PASSWORD_DEFAULT);
try {
    $pdo->query("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?) ON DUPLICATE KEY UPDATE password = ?");
    $stmt->execute([$password, $password]);

    echo "Admin password updated to: admin123<br>";
    echo "You can now login at admin_login.php using:<br>Username: admin<br>Password: admin123";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>