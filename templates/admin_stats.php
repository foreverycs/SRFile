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
            // 获取当前月份的所有天数数据
            $currentMonth = date('m');
            $currentYear = date('Y');
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
            
            // 初始化当前月份的每日上传数据
            $monthlyUploads = array_fill(1, $daysInMonth, 0);
            
            // 填充实际数据
            foreach ($stats['daily_uploads'] as $day => $count) {
                if ($day <= $daysInMonth) {
                    $monthlyUploads[$day] = $count;
                }
            }
            
            $maxUploads = max($monthlyUploads);
            $chartWidth = 5000; // 再次增加图表宽度（放大2倍）
            $chartHeight = 150;
            $sidePadding = 100; // 再次增加侧边距（放大2倍）
            $topBottomPadding = 20;
            $dateLabelPadding = 60; // 日期标签的额外空间
            $monthLabelPadding = 80; // 月份标签的额外空间
            ?>
            <div class="line-chart-container">
                <svg width="100%" height="<?= $chartHeight + $topBottomPadding + $dateLabelPadding + $monthLabelPadding ?>" viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight + $topBottomPadding + $dateLabelPadding + $monthLabelPadding ?>">
                    <!-- 定义渐变 -->
                    <defs>
                        <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#4b6cb7" />
                            <stop offset="100%" stop-color="#182848" />
                        </linearGradient>
                        <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#4b6cb7" stop-opacity="0.6" />
                            <stop offset="100%" stop-color="#4b6cb7" stop-opacity="0.1" />
                        </linearGradient>
                        <filter id="glow">
                            <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                            <feMerge>
                                <feMergeNode in="coloredBlur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    
                    <!-- 网格线 -->
                    <g class="grid-lines">
                        <?php for ($i = 0; $i <= 4; $i++): ?>
                            <line x1="<?= $sidePadding ?>" y1="<?= $topBottomPadding + ($chartHeight / 4) * $i ?>"
                                  x2="<?= $chartWidth - $sidePadding ?>" y2="<?= $topBottomPadding + ($chartHeight / 4) * $i ?>"
                                  stroke="rgba(255, 255, 255, 0.1)" stroke-width="1" />
                        <?php endfor; ?>
                    </g>
                    
                    <!-- 区域填充 -->
                    <path
                        d="
                            M <?= $sidePadding ?>,<?= $topBottomPadding + $chartHeight ?>
                            <?php
                            $points = [];
                            for ($day = 1; $day <= $daysInMonth; $day++):
                                $x = $sidePadding + (($chartWidth - $sidePadding * 2) / ($daysInMonth - 1)) * ($day - 1);
                                $y = $topBottomPadding + $chartHeight - ($maxUploads > 0 ? ($monthlyUploads[$day] / $maxUploads) * $chartHeight : 0);
                                // 增加垂直偏移量，使数据点下移
                                $y += 300;
                                echo "L $x,$y ";
                                $points[] = ['x' => $x, 'y' => $y, 'value' => $monthlyUploads[$day], 'day' => $day];
                            endfor;
                            ?>
                            L <?= $chartWidth - $sidePadding ?>,<?= $topBottomPadding + $chartHeight ?>
                            Z
                        "
                        fill="url(#areaGradient)"
                    />
                    
                    <!-- 折线 -->
                    <polyline
                        fill="none"
                        stroke="url(#lineGradient)"
                        stroke-width="10"
                        filter="url(#glow)"
                        points="
                            <?php
                            foreach ($points as $point):
                                echo "{$point['x']},{$point['y']} ";
                            endforeach;
                            ?>
                        "
                    />
                    
                    <!-- 数据点 -->
                    <?php foreach ($points as $point): ?>
                        <circle
                            cx="<?= $point['x'] ?>"
                            cy="<?= $point['y'] ?>"
                            r="20"
                            fill="#fdbb2d"
                            stroke="white"
                            stroke-width="3"
                            class="data-point"
                            data-value="<?= $point['value'] ?>"
                            data-day="<?= $point['day'] ?>"
                        />
                        <circle
                            cx="<?= $point['x'] ?>"
                            cy="<?= $point['y'] ?>"
                            r="15"
                            fill="transparent"
                            stroke="#fdbb2d"
                            stroke-width="2"
                            stroke-opacity="0.5"
                            class="data-point-pulse"
                        />
                    <?php endforeach; ?>
                    
                    <!-- X轴标签 - 每个数据点下方显示日期 -->
                    <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                        <text
                            x="<?= $sidePadding + (($chartWidth - $sidePadding * 2) / ($daysInMonth - 1)) * ($day - 1) ?>"
                            y="<?= $chartHeight + $topBottomPadding + 400 ?>"
                            text-anchor="middle"
                            font-size="85"
                            font-weight="bold"
                            fill="rgba(255, 255, 255, 0.9)"
                        ><?= $day ?></text>
                    <?php endfor; ?>
                    
                    <!-- 月份标签 - 在日期下方显示 -->
                    <text
                        x="<?= $chartWidth / 2 ?>"
                        y="<?= $chartHeight + $topBottomPadding + $dateLabelPadding + 480 ?>"
                        text-anchor="middle"
                        font-size="120"
                        fill="rgba(255, 255, 255, 0.9)"
                        font-weight="bold"
                    ><?= $currentYear ?>年<?= $currentMonth ?>月</text>
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
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.stats-section h3 i {
    color: #fdbb2d;
}

.stats-row {
    display: flex;
    gap: 15px;
    flex-wrap: nowrap;
}

.stat-item {
    flex: 1;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    min-width: 0;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
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
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: white;
    margin-bottom: 5px;
}

.stat-label {
    color: rgba(255, 255, 255, 0.7);
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
    color: white;
}

.file-type-bar {
    flex: 1;
    height: 20px;
    background: rgba(255, 255, 255, 0.1);
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
    color: white;
}

.daily-upload-chart {
    height: 300px;
    padding: 30px 0;
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
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
    transition: all 0.3s ease;
}

.data-point:hover {
    r: 12;
}

.data-point-pulse {
    animation: pulse 2s infinite;
    opacity: 0;
}

@keyframes pulse {
    0% {
        transform: scale(0.8);
        opacity: 0.8;
    }
    70% {
        transform: scale(1.5);
        opacity: 0;
    }
    100% {
        transform: scale(0.8);
        opacity: 0;
    }
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
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.tooltip-day {
    font-weight: bold;
    margin-bottom: 4px;
}

.tooltip-value {
    color: #fdbb2d;
    font-weight: bold;
}

.config-section {
    margin-bottom: 30px;
    padding: 20px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.config-section h3 {
    margin-bottom: 20px;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
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
    background: rgba(255, 255, 255, 0.05);
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.storage-label {
    font-weight: 500;
    color: rgba(255, 255, 255, 0.7);
}

.storage-value {
    font-family: monospace;
    color: white;
    background: rgba(255, 255, 255, 0.1);
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