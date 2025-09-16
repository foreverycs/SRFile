<?php
// 获取下载记录
$downloadRecords = [];
if (isset($_SESSION['downloaded_files']) && !empty($_SESSION['downloaded_files'])) {
    $downloadRecords = array_reverse($_SESSION['downloaded_files']);
}
?>

<div class="download-records">
    <div class="section-header">
        <h3>下载记录</h3>
        <?php if (!empty($downloadRecords)): ?>
            <form method="post" action="?action=admin&menu=records&type=downloads" onsubmit="return confirm('确定要清理所有下载记录吗？此操作不可撤销。');">
                <input type="hidden" name="clear_downloads" value="1">
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> 清理记录
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php if (empty($downloadRecords)): ?>
        <div class="no-records">
            <i class="fas fa-download"></i>
            <p>暂无下载记录</p>
        </div>
    <?php else: ?>
        <?php foreach ($downloadRecords as $record): ?>
            <div class="record-item">
                <div class="record-info">
                    <div class="record-time"><?= date('Y-m-d H:i:s', $record['download_time']) ?></div>
                    <div class="record-details">
                        <div class="record-name"><?= htmlspecialchars($record['name']) ?></div>
                        <div class="record-code">取件码: <?= htmlspecialchars($record['pickup_code']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>