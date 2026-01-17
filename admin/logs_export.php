<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/rbac.php';
require_once __DIR__ . '/../includes/logging.php';

// Require admin access
require_role([ROLE_ADMIN]);

session_start();

// Handle filters from GET parameters
$filters = [
    'category' => $_GET['category'] ?? '',
    'action' => $_GET['action'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
    'level' => $_GET['level'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

// Generate CSV content
$csv_content = export_logs_csv($conn, $filters);

// Set headers for CSV download
$filename = 'akasa_teknik_logs_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output CSV
echo $csv_content;
exit();
?>
