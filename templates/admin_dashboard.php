<?php
// 管理员面板导航
?>
<div class="admin-nav">
    <div class="nav-tabs">
        <a href="?action=admin&menu=files" class="nav-tab <?= $menu === 'files' ? 'active' : '' ?>">
            <i class="fas fa-file"></i> 文件管理
        </a>
        <a href="?action=admin&menu=stats" class="nav-tab <?= $menu === 'stats' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> 统计信息
        </a>
        <a href="?action=admin&menu=config" class="nav-tab <?= $menu === 'config' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> 系统配置
        </a>
        <a href="?action=admin&menu=records" class="nav-tab <?= $menu === 'records' ? 'active' : '' ?>">
            <i class="fas fa-history"></i> 操作记录
        </a>
    </div>
    
    <div class="nav-actions">
        <a href="?action=logout" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> 退出登录
        </a>
    </div>
</div>

<?php
// 显示操作结果
if (isset($_GET['admin_action']) && isset($_GET['status'])):
    $status = $_GET['status'];
    $message = $_GET['message'] ?? '';
?>
    <div class="notification <?= $status ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php
// 根据菜单显示不同内容
switch ($menu):
    case 'files':
        include 'admin_files.php';
        break;
    case 'stats':
        include 'admin_stats.php';
        break;
    case 'config':
        include 'admin_config.php';
        break;
    case 'records':
        include 'admin_records.php';
        break;
        default:
        include 'admin_files.php';
endswitch;
?>

<style>
.admin-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.nav-tabs {
    display: flex;
    gap: 10px;
}

.nav-tab {
    padding: 10px 20px;
    text-decoration: none;
    color: #666;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-tab:hover {
    background: #f8f9fa;
}

.nav-tab.active {
    background: #4b6cb7;
    color: white;
}

.nav-actions {
    display: flex;
    gap: 10px;
}

.filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.filter-tab {
    padding: 8px 16px;
    text-decoration: none;
    color: #666;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.filter-tab:hover {
    background: #f8f9fa;
}

.filter-tab.active {
    background: #28a745;
    color: white;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.admin-table th,
.admin-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.admin-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.admin-table tr:hover {
    background: #f8f9fa;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-blocked {
    background: #f8d7da;
    color: #721c24;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
}

.pagination a {
    padding: 8px 12px;
    text-decoration: none;
    color: #666;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.pagination a:hover {
    background: #f8f9fa;
}

.pagination a.active {
    background: #4b6cb7;
    color: white;
    border-color: #4b6cb7;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #4b6cb7;
    margin-bottom: 10px;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
}

.chart-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}
</style>