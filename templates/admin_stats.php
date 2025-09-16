<div class="admin-stats">
    <div class="stats-section">
        <h3><i class="fas fa-chart-bar"></i> 文件类型统计</h3>
        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-file"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['total_files'] ?></div>
                    <div class="stat-label">总文件数</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['pending_files'] ?></div>
                    <div class="stat-label">待审核</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['approved_files'] ?></div>
                    <div class="stat-label">已通过</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['blocked_files'] ?></div>
                    <div class="stat-label">已封禁</div>
                </div>
            </div>
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
            $chartWidth = 100;
            $chartHeight = 150;
            $padding = 20;
            ?>
            <div class="line-chart-container">
                <svg width="100%" height="<?= $chartHeight + $padding * 2 ?>" viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight + $padding * 2 ?>">
                    <!-- 网格线 -->
                    <g class="grid-lines">
                        <?php for ($i = 0; $i <= 4; $i++): ?>
                            <line x1="<?= $padding ?>" y1="<?= $padding + ($chartHeight / 4) * $i ?>"
                                  x2="<?= $chartWidth - $padding ?>" y2="<?= $padding + ($chartHeight / 4) * $i ?>"
                                  stroke="#e9ecef" stroke-width="1" />
                        <?php endfor; ?>
                    </g>
                    
                    <!-- 折线 -->
                    <polyline
                        fill="none"
                        stroke="#4b6cb7"
                        stroke-width="2"
                        points="
                            <?php
                            $points = [];
                            for ($day = 1; $day <= min($currentDay, 31); $day++):
                                $x = $padding + (($chartWidth - $padding * 2) / (min($currentDay, 31) - 1)) * ($day - 1);
                                $y = $padding + $chartHeight - ($maxUploads > 0 ? ($stats['daily_uploads'][$day] / $maxUploads) * $chartHeight : 0);
                                echo "$x,$y ";
                                $points[] = ['x' => $x, 'y' => $y, 'value' => $stats['daily_uploads'][$day], 'day' => $day];
                            endfor;
                            ?>
                        "
                    />
                    
                    <!-- 数据点 -->
                    <?php foreach ($points as $point): ?>
                        <circle
                            cx="<?= $point['x'] ?>"
                            cy="<?= $point['y'] ?>"
                            r="4"
                            fill="#4b6cb7"
                            stroke="white"
                            stroke-width="2"
                            class="data-point"
                            data-value="<?= $point['value'] ?>"
                            data-day="<?= $point['day'] ?>"
                        />
                    <?php endforeach; ?>
                    
                    <!-- X轴标签 -->
                    <?php for ($day = 1; $day <= min($currentDay, 31); $day += 5): ?>
                        <text
                            x="<?= $padding + (($chartWidth - $padding * 2) / (min($currentDay, 31) - 1)) * ($day - 1) ?>"
                            y="<?= $chartHeight + $padding + 15 ?>"
                            text-anchor="middle"
                            font-size="10"
                            fill="#666"
                        ><?= $day ?></text>
                    <?php endfor; ?>
                </svg>
                
                <!-- 悬停提示 -->
                <div class="tooltip" style="display: none;">
                    <div class="tooltip-day"></div>
                    <div class="tooltip-value"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="config-section">
        <h3>系统信息</h3>
        <div class="storage-info">
            <div class="storage-item">
                <div class="storage-label">PHP版本:</div>
                <div class="storage-value"><?= phpversion() ?></div>
            </div>
            <div class="storage-item">
                <div class="storage-label">服务器时间:</div>
                <div class="storage-value"><?= date('Y-m-d H:i:s') ?></div>
            </div>
            <div class="storage-item">
                <div class="storage-label">上传目录:</div>
                <div class="storage-value"><?= UPLOAD_DIR ?></div>
            </div>
            <div class="storage-item">
                <div class="storage-label">数据目录:</div>
                <div class="storage-value"><?= DATA_DIR ?></div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-section {
    margin-bottom: 30px;
}

.stats-section h3 {
    margin-bottom: 20px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stats-section h3 i {
    color: #4b6cb7;
}

.stats-row {
    display: flex;
    gap: 15px;
    flex-wrap: nowrap;
}

.stat-item {
    flex: 1;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    min-width: 0;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #4b6cb7, #182848);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

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
    padding: 20px 0;
}

.line-chart-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.line-chart-container svg {
    width: 100%;
    height: 100%;
}

.data-point {
    cursor: pointer;
    transition: r 0.2s ease;
}

.data-point:hover {
    r: 6;
}

.tooltip {
    position: absolute;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    pointer-events: none;
    z-index: 10;
    transform: translate(-50%, -100%);
    margin-top: -10px;
}

.tooltip-day {
    font-weight: bold;
    margin-bottom: 4px;
}

.tooltip-value {
    color: #4b6cb7;
    font-weight: bold;
}

.config-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.config-section h3 {
    margin-bottom: 20px;
    color: #333;
}

.storage-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.storage-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: white;
    border-radius: 6px;
}

.storage-label {
    font-weight: 500;
    color: #666;
}

.storage-value {
    font-family: monospace;
    color: #333;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .stats-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .stat-item {
        min-width: 100%;
    }
}

@media (max-width: 1200px) {
    .stats-row {
        gap: 10px;
    }
    
    .stat-item {
        padding: 12px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .stat-value {
        font-size: 20px;
    }
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
    
    .storage-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataPoints = document.querySelectorAll('.data-point');
    const tooltip = document.querySelector('.tooltip');
    const tooltipDay = document.querySelector('.tooltip-day');
    const tooltipValue = document.querySelector('.tooltip-value');
    
    dataPoints.forEach(point => {
        point.addEventListener('mouseenter', function(e) {
            const day = this.getAttribute('data-day');
            const value = this.getAttribute('data-value');
            
            tooltipDay.textContent = `第 ${day} 天`;
            tooltipValue.textContent = `${value} 个文件`;
            
            // 获取数据点的位置
            const rect = this.getBoundingClientRect();
            const containerRect = this.closest('.line-chart-container').getBoundingClientRect();
            
            // 计算提示框的位置
            const left = rect.left - containerRect.left + rect.width / 2;
            const top = rect.top - containerRect.top;
            
            tooltip.style.left = `${left}px`;
            tooltip.style.top = `${top}px`;
            tooltip.style.display = 'block';
        });
        
        point.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });
    });
});
</script>