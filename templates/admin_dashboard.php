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
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-tabs {
    display: flex;
    gap: 10px;
}

.nav-tab {
    padding: 10px 20px;
    text-decoration: none;
    color: rgba(255, 255, 255, 0.7);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-tab:hover {
    background: rgba(255, 255, 255, 0.1);
}

.nav-tab.active {
    background: linear-gradient(90deg, #4b6cb7, #182848);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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
    color: rgba(255, 255, 255, 0.7);
    border-radius: 6px;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.filter-tab:hover {
    background: rgba(255, 255, 255, 0.1);
}

.filter-tab.active {
    background: linear-gradient(90deg, #28a745, #1e7e34);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.admin-table th {
    background: rgba(255, 255, 255, 0.1);
    font-weight: 600;
    color: white;
}

.admin-table tr:hover {
    background: rgba(255, 255, 255, 0.05);
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-pending {
    background: rgba(255, 193, 7, 0.2);
    color: #fff3cd;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-approved {
    background: rgba(40, 167, 69, 0.2);
    color: #d4edda;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.status-blocked {
    background: rgba(220, 53, 69, 0.2);
    color: #f8d7da;
    border: 1px solid rgba(220, 53, 69, 0.3);
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
    color: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    transition: all 0.3s ease;
}

.pagination a:hover {
    background: rgba(255, 255, 255, 0.1);
}

.pagination a.active {
    background: linear-gradient(90deg, #4b6cb7, #182848);
    color: white;
    border-color: #4b6cb7;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: rgba(0, 0, 0, 0.3);
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    text-align: center;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #fdbb2d;
    margin-bottom: 10px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.stat-label {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

.chart-container {
    background: rgba(0, 0, 0, 0.3);
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    margin-bottom: 20px;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>