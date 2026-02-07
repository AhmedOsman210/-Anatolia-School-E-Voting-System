# Anatolia School E-Voting System 🗳️

A modern, secure, and premium E-voting system designed for **Anatolia School**. This platform provides a seamless voting experience for students and a powerful management interface for administrators.

## 🌟 Key Features

*   **Premium UI/UX**: Modern glassmorphic design with dark mode support.
*   **Real-time Analytics**: Live vote tallies, turnout percentages, and winner detection.
*   **Voter Management**: Effortless student enrollment, account locking, and participation tracking.
*   **Candidate Portal**: Manage candidate profiles, bios, and mission statements with photo uploads.
*   **Regional Support**: Fully configured for **Somalia (Africa/Mogadishu)** timezone.
*   **Security**: Transaction-based voting to prevent duplicates and ensure data integrity.

## 📸 Screenshots

### 1. Admin Dashboard Overview
![Admin Dashboard](screenshots/admin_dashboard.png)
*Comprehensive overview of election status, voter turnout, and live activity.*

### 2. Candidate Management
![Candidate Management](screenshots/candidate_management.png)
*Add and edit student candidates for the election.*

### 3. Voter Access Management
![Voter Access](screenshots/voter_management.png)
*Manage student access and track who has already voted.*

### 4. Election Results & Analytics
![Election Results](screenshots/results_analytics.png)
*Detailed breakdown of votes by grade level and official winner declaration.*

### 5. Final Results Public View
![Public Results](screenshots/public_results.png)
*The final announcement screen for the student body.*

## 🛠️ Tech Stack

*   **Backend**: PHP 8.x
*   **Database**: MySQL
*   **Frontend**: HTML5, CSS3 (Custom Glassmorphism), Bootstrap 5
*   **Icons**: Bootstrap Icons
*   **Timezone Config**: Africa/Mogadishu (Somalia)

## 🚀 Installation & Setup

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/AhmedOsman210/-Anatolia-School-E-Voting-System.git
    ```
2.  **Database Configuration**:
    *   Import `database.sql` into your MySQL server.
    *   Update `includes/db.php` with your database credentials.
3.  **Timezone**:
    *   The system is set to `Africa/Mogadishu`. You can change this in `includes/db.php` if needed.
4.  **Run with XAMPP**:
    *   Place the project in your `htdocs` folder.
    *   Access via `http://localhost/Anatolia-School-Voting/`.

---
© 2024 Anatolia School Administration. Created for educational excellence.
