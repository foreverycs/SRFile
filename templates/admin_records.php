<div class="admin-records">
    <div class="filter-tabs">
        <a href="?action=admin&menu=records&type=uploads" class="filter-tab <?= (!isset($_GET['type']) || $_GET['type'] === 'uploads') ? 'active' : '' ?>">
            上传记录
        </a>
        <a href="?action=admin&menu=records&type=downloads" class="filter-tab <?= (isset($_GET['type']) && $_GET['type'] === 'downloads') ? 'active' : '' ?>">
            下载记录
        </a>
        <a href="?action=admin&menu=records&type=security" class="filter-tab <?= (isset($_GET['type']) && $_GET['type'] === 'security') ? 'active' : '' ?>">
            安全日志
        </a>
    </div>
    
    <?php
    $type = $_GET['type'] ?? 'uploads';
    
    switch ($type):
        case 'uploads':
            include 'admin_uploads.php';
            break;
        case 'downloads':
            include 'admin_downloads.php';
            break;
        case 'security':
            include 'admin_security.php';
            break;
        default:
            // 如果访问了已删除的operations类型，重定向到uploads
            header('Location: ?action=admin&menu=records&type=uploads');
            exit;
    endswitch;
    ?>
</div>

<style>
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
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.filter-tab:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.filter-tab.active {
    background: linear-gradient(90deg, #4b6cb7, #182848);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.record-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    margin-bottom: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.record-item:hover {
    background: rgba(255, 255, 255, 0.1);
}

.record-info {
    flex: 1;
}

.record-time {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 5px;
}

.record-details {
    display: flex;
    align-items: center;
    gap: 15px;
}

.record-name {
    font-weight: 500;
    color: white;
}

.record-code {
    font-family: monospace;
    background: rgba(255, 255, 255, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.9rem;
    color: #fdbb2d;
}

.record-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.security-event {
    border-left: 4px solid #dc3545;
    background: rgba(255, 255, 255, 0.05);
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.security-event.warning {
    border-left-color: #ffc107;
}

.security-event.info {
    border-left-color: #17a2b8;
}

.event-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.event-type {
    font-weight: 500;
    color: white;
}

.event-time {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
}

.event-info {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 5px;
}

.event-meta {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.5);
}

.no-records {
    text-align: center;
    padding: 40px;
    color: rgba(255, 255, 255, 0.7);
}

.no-records i {
    font-size: 2rem;
    margin-bottom: 10px;
    color: rgba(255, 255, 255, 0.3);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.section-header h3 {
    margin: 0;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}


@media (max-width: 768px) {
    .record-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .record-details {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>