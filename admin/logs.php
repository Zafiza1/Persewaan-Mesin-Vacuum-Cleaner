<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rbac.php';
require_once __DIR__ . '/../includes/logging.php';

// Require admin access
require_role([ROLE_ADMIN]);

session_start();

// Handle filters
$filters = [
    'category' => $_GET['category'] ?? '',
    'action' => $_GET['action'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
    'level' => $_GET['level'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'limit' => 50
];

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $filters['limit'];
$filters['offset'] = $offset;

// Get logs
$logs = get_logs($conn, $filters);

// Get statistics
$stats = get_log_statistics($conn, [
    'date_from' => $filters['date_from'],
    'date_to' => $filters['date_to']
]);

// Get total count for pagination
$total_logs = $stats['total'] ?? 0;
$total_pages = ceil($total_logs / $filters['limit']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-history me-2"></i>Audit Trail & Logs</h1>
            <p class="text-muted">Monitor semua aktivitas sistem dan keamanan</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Logs</h5>
                    <h3 class="card-text"><?php echo number_format($stats['total'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Security Events</h5>
                    <h3 class="card-text">
                        <?php 
                        $security_count = 0;
                        foreach ($stats['by_category'] ?? [] as $cat) {
                            if ($cat['category'] === 'security') $security_count = $cat['count'];
                        }
                        echo number_format($security_count);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">User Activities</h5>
                    <h3 class="card-text">
                        <?php 
                        $user_count = 0;
                        foreach ($stats['by_category'] ?? [] as $cat) {
                            if ($cat['category'] === 'user') $user_count = $cat['count'];
                        }
                        echo number_format($user_count);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Admin Activities</h5>
                    <h3 class="card-text">
                        <?php 
                        $admin_count = 0;
                        foreach ($stats['by_category'] ?? [] as $cat) {
                            if ($cat['category'] === 'admin') $admin_count = $cat['count'];
                        }
                        echo number_format($admin_count);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-filter me-2"></i>Filter Logs</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        <option value="security" <?php echo $filters['category'] === 'security' ? 'selected' : ''; ?>>Security</option>
                        <option value="user" <?php echo $filters['category'] === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo $filters['category'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="rental" <?php echo $filters['category'] === 'rental' ? 'selected' : ''; ?>>Rental</option>
                        <option value="product" <?php echo $filters['category'] === 'product' ? 'selected' : ''; ?>>Product</option>
                        <option value="system" <?php echo $filters['category'] === 'system' ? 'selected' : ''; ?>>System</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Action</label>
                    <input type="text" name="action" class="form-control" value="<?php echo htmlspecialchars($filters['action']); ?>" placeholder="Cari action...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">User ID</label>
                    <input type="number" name="user_id" class="form-control" value="<?php echo htmlspecialchars($filters['user_id']); ?>" placeholder="User ID">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                    <a href="logs.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Reset
                    </a>
                    <a href="logs_export.php" class="btn btn-success">
                        <i class="fas fa-download me-2"></i>Export CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-list me-2"></i>Log Entries</h5>
            <span class="badge bg-secondary"><?php echo number_format($total_logs); ?> entries</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Level</th>
                            <th>Kategori</th>
                            <th>Action</th>
                            <th>Pesan</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id']; ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($log['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        $level_classes = [
                                            0 => 'danger',
                                            1 => 'danger', 
                                            2 => 'danger',
                                            3 => 'warning',
                                            4 => 'warning',
                                            5 => 'info',
                                            6 => 'primary',
                                            7 => 'secondary'
                                        ];
                                        $level_names = [
                                            0 => 'EMERGENCY',
                                            1 => 'ALERT',
                                            2 => 'CRITICAL', 
                                            3 => 'ERROR',
                                            4 => 'WARNING',
                                            5 => 'NOTICE',
                                            6 => 'INFO',
                                            7 => 'DEBUG'
                                        ];
                                        $class = $level_classes[$log['level']] ?? 'secondary';
                                        $name = $level_names[$log['level']] ?? 'UNKNOWN';
                                        ?>
                                        <span class="badge bg-<?php echo $class; ?>"><?php echo $name; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo strtoupper($log['category']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($log['message'] ?? '', 0, 50)); ?></td>
                                    <td>
                                        <?php if ($log['user_id']): ?>
                                            <div>
                                                <small>ID: <?php echo $log['user_id']; ?></small><br>
                                                <small><?php echo htmlspecialchars($log['user_role'] ?? ''); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></code></td>
                                    <td>
                                        <?php if (!empty($log['details'])): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $log['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <!-- Modal -->
                                            <div class="modal fade" id="detailModal<?php echo $log['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Log Details #<?php echo $log['id']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <pre><?php echo json_encode($log['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada log yang ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($filters, function($k) { return $k !== 'limit' && $k !== 'offset'; }, ARRAY_FILTER_USE_KEY)); ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($filters, function($k) { return $k !== 'limit' && $k !== 'offset'; }, ARRAY_FILTER_USE_KEY)); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($filters, function($k) { return $k !== 'limit' && $k !== 'offset'; }, ARRAY_FILTER_USE_KEY)); ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
