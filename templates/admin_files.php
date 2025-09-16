<div class="admin-files">
    <div class="filter-tabs">
        <a href="?action=admin&menu=files&filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">
            全部文件 (<?= $totalFiles ?>)
        </a>
        <a href="?action=admin&menu=files&filter=pending" class="filter-tab <?= $filter === 'pending' ? 'active' : '' ?>">
            待审核
        </a>
        <a href="?action=admin&menu=files&filter=approved" class="filter-tab <?= $filter === 'approved' ? 'active' : '' ?>">
            已通过
        </a>
        <a href="?action=admin&menu=files&filter=blocked" class="filter-tab <?= $filter === 'blocked' ? 'active' : '' ?>">
            已封禁
        </a>
    </div>
    
    <?php if (empty($files)): ?>
        <div class="no-files">
            <i class="fas fa-inbox"></i>
            <p>暂无文件</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>文件名</th>
                        <th>类型</th>
                        <th>大小</th>
                        <th>上传时间</th>
                        <th>状态</th>
                        <th>取件码</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                        <tr>
                            <td>
                                <div class="file-info">
                                    <div class="file-name"><?= htmlspecialchars($file->name) ?></div>
                                    <div class="file-meta">
                                        ID: <?= htmlspecialchars($file->id) ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= getFileType($file->type) ?></td>
                            <td><?= formatSize($file->size) ?></td>
                            <td><?= date('Y-m-d H:i', $file->upload_time) ?></td>
                            <td>
                                <span class="status-badge status-<?= $file->status === 0 ? 'pending' : ($file->status === 1 ? 'approved' : 'blocked') ?>">
                                    <?= $file->status === 0 ? '待审核' : ($file->status === 1 ? '已通过' : '已封禁') ?>
                                </span>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($file->pickup_code) ?></code>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($file->status === 0): ?>
                                        <a href="?action=approve&id=<?= $file->id ?>&filter=<?= $filter ?>" class="btn btn-success" title="通过">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="?action=block&id=<?= $file->id ?>&filter=<?= $filter ?>" class="btn btn-danger" title="封禁">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php elseif ($file->status === 1): ?>
                                        <a href="?action=block&id=<?= $file->id ?>&filter=<?= $filter ?>" class="btn btn-danger" title="封禁">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=approve&id=<?= $file->id ?>&filter=<?= $filter ?>" class="btn btn-success" title="通过">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?action=delete&id=<?= $file->id ?>&filter=<?= $filter ?>" class="btn btn-danger" title="删除" onclick="return confirm('确定要删除这个文件吗？')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="?action=admin_preview&id=<?= $file->id ?>" class="btn" title="预览">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?action=admin&menu=files&filter=<?= $filter ?>&page=<?= $i ?>" class="<?= $i === $currentPage ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.no-files {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-files i {
    font-size: 3rem;
    margin-bottom: 20px;
    color: #ccc;
}

.file-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.file-name {
    font-weight: 500;
    color: #333;
}

.file-meta {
    font-size: 0.8rem;
    color: #666;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.action-buttons .btn {
    padding: 6px 8px;
    font-size: 0.8rem;
}

.table-responsive {
    overflow-x: auto;
}

@media (max-width: 768px) {
    .admin-table {
        font-size: 0.9rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 2px;
    }
    
    .filter-tabs {
        flex-wrap: wrap;
    }
}
</style>