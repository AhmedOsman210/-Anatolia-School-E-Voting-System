<?php
require_once 'includes/db.php';

try {
    echo "Updating database schema...\n";

    // 1. Add status to candidates
    try {
        $pdo->query("SELECT status FROM candidates LIMIT 1");
    } catch (PDOException $e) {
        echo "Adding 'status' column to candidates...\n";
        $pdo->exec("ALTER TABLE candidates ADD COLUMN status VARCHAR(20) DEFAULT 'published' AFTER mission");
    }

    // 2. Add class and is_locked to students
    try {
        $pdo->query("SELECT class FROM students LIMIT 1");
    } catch (PDOException $e) {
        echo "Adding 'class' column to students...\n";
        $pdo->exec("ALTER TABLE students ADD COLUMN class VARCHAR(20) AFTER full_name");
    }

    try {
        $pdo->query("SELECT is_locked FROM students LIMIT 1");
    } catch (PDOException $e) {
        echo "Adding 'is_locked' column to students...\n";
        $pdo->exec("ALTER TABLE students ADD COLUMN is_locked TINYINT(1) DEFAULT 0 AFTER has_voted");
    }

    // 3. Create votes table
    echo "Creating 'votes' table if not exists...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        candidate_id INT NOT NULL,
        student_grade VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Ensure settings exist
    echo "Ensuring timeline settings exist...\n";
    $settings = ['start_time', 'end_time'];
    foreach ($settings as $key) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM election_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO election_settings (setting_key, setting_value) VALUES (?, '')");
            $stmt->execute([$key]);
            echo "Added setting: $key\n";
        }
    }

    echo "\nDatabase update completed successfully!";

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>