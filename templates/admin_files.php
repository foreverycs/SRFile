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
    color: white;
}

.file-meta {
    font-size: 0.8rem;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.action-buttons .btn {
    padding: 6px 8px;
    font-size: 0.8rem;
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    touch-action: manipulation;
    min-width: 32px;
    min-height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.table-responsive {
    overflow-x: auto;
}

@media (max-width: 768px) {
    .admin-table {
        font-size: 0.9rem;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 8px 5px;
    }
    
    .admin-table th:nth-child(2),
    .admin-table td:nth-child(2),
    .admin-table th:nth-child(3),
    .admin-table td:nth-child(3) {
        display: none; /* 隐藏类型和大小列 */
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 2px;
    }
    
    .action-buttons .btn {
        width: 100%;
        padding: 8px;
        font-size: 0.8rem;
        min-height: 36px;
    }
    
    .filter-tabs {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .filter-tab {
        padding: 8px 12px;
        font-size: 0.85rem;
    }
    
    .file-name {
        max-width: 120px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .pagination {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .pagination a {
        min-width: 32px;
        min-height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .admin-table {
        font-size: 0.85rem;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 6px 4px;
    }
    
    .admin-table th:nth-child(4),
    .admin-table td:nth-child(4) {
        display: none; /* 隐藏上传时间列 */
    }
    
    .file-name {
        max-width: 80px;
        font-size: 0.85rem;
    }
    
    .file-meta {
        font-size: 0.75rem;
    }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 2px 6px;
    }
    
    .action-buttons .btn {
        padding: 6px;
        font-size: 0.75rem;
        min-height: 32px;
    }
    
    .filter-tab {
        padding: 6px 10px;
        font-size: 0.8rem;
    }
}

/* 平板设备适配 */
@media (min-width: 601px) and (max-width: 768px) {
    .admin-table th:nth-child(2),
    .admin-table td:nth-child(2) {
        display: table-cell; /* 显示类型列 */
    }
    
    .file-name {
        max-width: 150px;
    }
}

/* 大屏手机适配 */
@media (min-width: 769px) and (max-width: 992px) {
    .admin-table {
        font-size: 0.95rem;
    }
    
    .file-name {
        max-width: 200px;
    }
}

/* 横屏模式适配 */
@media (max-width: 768px) and (orientation: landscape) {
    .admin-table {
        font-size: 0.8rem;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 5px 3px;
    }
    
    .admin-table th:nth-child(3),
    .admin-table td:nth-child(3) {
        display: table-cell; /* 显示大小列 */
    }
    
    .action-buttons {
        flex-direction: row;
        gap: 3px;
    }
    
    .action-buttons .btn {
        width: auto;
        padding: 4px 6px;
        min-height: 28px;
    }
}
</style>