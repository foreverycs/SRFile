<?php
// 获取安全日志
$securityLogs = [];
$securityLogFile = DATA_DIR . 'security.log';
if (file_exists($securityLogFile)) {
    $lines = file($securityLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach (array_reverse($lines) as $line) {
        $log = json_decode($line, true);
        if ($log) {
            $securityLogs[] = $log;
        }
    }
}

// 只显示最近50条
$securityLogs = array_slice($securityLogs, 0, 50);
?>

<div class="security-logs">
    <h3>安全日志</h3>
    
    <?php if (empty($securityLogs)): ?>
        <div class="no-records">
            <i class="fas fa-shield-alt"></i>
            <p>暂无安全日志</p>
        </div>
    <?php else: ?>
        <?php foreach ($securityLogs as $log): ?>
            <div class="record-item security-event <?= in_array($log['event'], ['failed_admin_login', 'file_upload_rejected']) ? '' : 'info' ?>">
                <div class="record-info">
                    <div class="event-details">
                        <div class="event-type"><?= htmlspecialchars($log['event']) ?></div>
                        <div class="event-time"><?= date('Y-m-d H:i:s', strtotime($log['timestamp'])) ?></div>
                    </div>
                    
                    <?php if (isset($log['details']['message'])): ?>
                        <div class="event-info"><?= htmlspecialchars($log['details']['message']) ?></div>
                    <?php endif; ?>
                    
                    <div class="event-meta">
                        IP: <?= htmlspecialchars($log['ip']) ?>
                        <?php if (isset($log['details']['filename'])): ?>
                            | 文件: <?= htmlspecialchars($log['details']['filename']) ?>
                        <?php endif; ?>
                        <?php if (isset($log['details']['username'])): ?>
                            | 用户: <?= htmlspecialchars($log['details']['username']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>