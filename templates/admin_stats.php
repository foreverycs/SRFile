<div class="admin-stats">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $stats['total_files'] ?></div>
            <div class="stat-label">总文件数</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['pending_files'] ?></div>
            <div class="stat-label">待审核</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['approved_files'] ?></div>
            <div class="stat-label">已通过</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['blocked_files'] ?></div>
            <div class="stat-label">已封禁</div>
        </div>
    </div>
    
    <div class="chart-container">
        <h3>文件类型分布</h3>
        <div class="file-type-stats">
            <?php foreach ($stats['file_types'] as $type => $count): ?>
                <div class="file-type-item">
                    <div class="file-type-label"><?= $type ?></div>
                    <div class="file-type-bar">
                        <div class="file-type-progress" style="width: <?= ($count / $stats['total_files']) * 100 ?>%"></div>
                    </div>
                    <div class="file-type-count"><?= $count ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="chart-container">
        <h3>每日上传趋势</h3>
        <div class="daily-upload-chart">
            <?php 
            $maxUploads = max($stats['daily_uploads']);
            $currentDay = (int)date('j');
            ?>
            <div class="chart-bars">
                <?php for ($day = 1; $day <= min($currentDay, 31); $day++): ?>
                    <div class="chart-bar-container">
                        <div class="chart-bar" style="height: <?= $maxUploads > 0 ? ($stats['daily_uploads'][$day] / $maxUploads) * 100 : 0 ?>%"></div>
                        <div class="chart-label"><?= $day ?></div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    
    <div class="system-info">
        <h3>系统信息</h3>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">PHP版本</div>
                <div class="info-value"><?= phpversion() ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">服务器时间</div>
                <div class="info-value"><?= date('Y-m-d H:i:s') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">上传目录</div>
                <div class="info-value"><?= UPLOAD_DIR ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">数据目录</div>
                <div class="info-value"><?= DATA_DIR ?></div>
            </div>
        </div>
    </div>
</div>

<style>
.file-type-stats {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.file-type-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.file-type-label {
    min-width: 80px;
    font-weight: 500;
}

.file-type-bar {
    flex: 1;
    height: 20px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.file-type-progress {
    height: 100%;
    background: linear-gradient(90deg, #4b6cb7, #182848);
    transition: width 0.3s ease;
}

.file-type-count {
    min-width: 40px;
    text-align: right;
    font-weight: 500;
}

.daily-upload-chart {
    height: 200px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding: 20px 0;
}

.chart-bars {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    width: 100%;
    height: 150px;
}

.chart-bar-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    flex: 1;
}

.chart-bar {
    width: 100%;
    background: linear-gradient(180deg, #4b6cb7, #182848);
    border-radius: 4px 4px 0 0;
    min-height: 2px;
    transition: height 0.3s ease;
}

.chart-label {
    margin-top: 5px;
    font-size: 0.8rem;
    color: #666;
}

.system-info {
    margin-top: 30px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.info-label {
    font-weight: 500;
    color: #666;
    margin-bottom: 5px;
}

.info-value {
    color: #333;
    font-family: monospace;
}

@media (max-width: 768px) {
    .file-type-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .file-type-bar {
        width: 100%;
    }
    
    .chart-bars {
        overflow-x: auto;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>