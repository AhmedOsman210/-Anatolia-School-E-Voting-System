<?php
// Function to get current election status based on settings and time
function get_election_status($pdo)
{
    $settings_raw = $pdo->query("SELECT * FROM election_settings")->fetchAll();
    $settings = [];
    foreach ($settings_raw as $s) {
        $settings[$s['setting_key']] = $s['setting_value'];
    }

    $now = new DateTime();
    $status = $settings['election_status'];

    // If it's set to active, but we have times, check if we are within the window
    if ($status == 'active' || $status == 'pending') {
        if (!empty($settings['start_time']) && !empty($settings['end_time'])) {
            $start = new DateTime($settings['start_time']);
            $end = new DateTime($settings['end_time']);

            if ($now < $start) {
                return 'pending';
            } elseif ($now >= $start && $now <= $end) {
                return 'active';
            } else {
                return 'closed';
            }
        }
    }

    return $status;
}

// Function to get time remaining as string
function get_time_remaining_str($pdo)
{
    $end_time = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'end_time'")->fetchColumn();
    if (empty($end_time))
        return "00:00:00";

    $end = new DateTime($end_time);
    $now = new DateTime();

    if ($now > $end)
        return "00:00:00";

    $diff = $now->diff($end);
    return $diff->format('%H:%I:%S');
}
?>