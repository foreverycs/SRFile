<div class="admin-records">
    <div class="records-tabs">
        <a href="?action=admin&menu=records&type=uploads" class="records-tab <?= (!isset($_GET['type']) || $_GET['type'] === 'uploads') ? 'active' : '' ?>">
            上传记录
        </a>
        <a href="?action=admin&menu=records&type=downloads" class="records-tab <?= (isset($_GET['type']) && $_GET['type'] === 'downloads') ? 'active' : '' ?>">
            下载记录
        </a>
                <a href="?action=admin&menu=records&type=security" class="records-tab <?= (isset($_GET['type']) && $_GET['type'] === 'security') ? 'active' : '' ?>">
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
.records-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.records-tab {
    padding: 10px 20px;
    text-decoration: none;
    color: #666;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.records-tab:hover {
    background: #f8f9fa;
}

.records-tab.active {
    background: #4b6cb7;
    color: white;
}

.record-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: white;
    border-radius: 8px;
    margin-bottom: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.record-info {
    flex: 1;
}

.record-time {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 5px;
}

.record-details {
    display: flex;
    align-items: center;
    gap: 15px;
}

.record-name {
    font-weight: 500;
    color: #333;
}

.record-code {
    font-family: monospace;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.9rem;
}

.record-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.security-event {
    border-left: 4px solid #dc3545;
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
    color: #333;
}

.event-time {
    font-size: 0.9rem;
    color: #666;
}

.event-info {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 5px;
}

.event-meta {
    font-size: 0.8rem;
    color: #999;
}

.no-records {
    text-align: center;
    padding: 40px;
    color: #666;
}

.no-records i {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #ccc;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.section-header h3 {
    margin: 0;
    color: #333;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
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