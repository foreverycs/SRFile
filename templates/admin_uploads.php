<?php
// 获取上传记录
$uploadRecords = [];
if (isset($_SESSION['uploaded_files']) && !empty($_SESSION['uploaded_files'])) {
    $uploadRecords = array_reverse($_SESSION['uploaded_files']);
}
?>

<div class="upload-records">
    <div class="section-header">
        <h3>上传记录</h3>
        <?php if (!empty($uploadRecords)): ?>
            <form method="post" action="?action=admin&menu=records&type=uploads" onsubmit="return confirm('确定要清理所有上传记录吗？此操作不可撤销。');">
                <input type="hidden" name="clear_uploads" value="1">
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> 清理记录
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php if (empty($uploadRecords)): ?>
        <div class="no-records">
            <i class="fas fa-upload"></i>
            <p>暂无上传记录</p>
        </div>
    <?php else: ?>
        <?php foreach ($uploadRecords as $record): ?>
            <div class="record-item">
                <div class="record-info">
                    <div class="record-time"><?= date('Y-m-d H:i:s', $record['upload_time']) ?></div>
                    <div class="record-details">
                        <div class="record-name"><?= htmlspecialchars($record['name']) ?></div>
                        <div class="record-code">取件码: <?= htmlspecialchars($record['pickup_code']) ?></div>
                        <div class="record-status status-<?= $record['status'] === 0 ? 'pending' : ($record['status'] === 1 ? 'approved' : 'blocked') ?>">
                            <?= $record['status'] === 0 ? '待审核' : ($record['status'] === 1 ? '已通过' : '已封禁') ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>