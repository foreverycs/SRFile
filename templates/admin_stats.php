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
    
    <!-- 第三行：每日上传趋势 -->
    <div class="chart-section">
        <h3><i class="fas fa-chart-line"></i> 每日上传趋势</h3>
        <div class="daily-upload-chart">
            <?php
            $maxUploads = max($stats['daily_uploads']);
            $currentDay = (int)date('j');
            $chartWidth = min($currentDay, 31) * 30 + 60; // 动态计算画布宽度
            ?>
            <div class="line-chart-container">
                <svg class="line-chart" width="<?= $chartWidth ?>" height="200" viewBox="0 0 <?= $chartWidth ?> 200">
                    <!-- 网格线 -->
                    <defs>
                        <pattern id="grid" width="30" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 30 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                    
                    <!-- Y轴刻度线和标签 -->
                    <?php for ($i = 0; $i <= 5; $i++): ?>
                        <line x1="30" y1="<?= 170 - ($i * 30) ?>" x2="<?= $chartWidth - 30 ?>" y2="<?= 170 - ($i * 30) ?>"
                              stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
                        <text x="25" y="<?= 175 - ($i * 30) ?>" fill="rgba(255,255,255,0.7)" font-size="10" text-anchor="end">
                            <?= $maxUploads > 0 ? round(($maxUploads * $i) / 5) : 0 ?>
                        </text>
                    <?php endfor; ?>
                    
                    <!-- X轴 -->
                    <line x1="30" y1="170" x2="<?= $chartWidth - 30 ?>" y2="170" stroke="rgba(255,255,255,0.3)" stroke-width="2"/>
                    
                    <!-- Y轴 -->
                    <line x1="30" y1="20" x2="30" y2="170" stroke="rgba(255,255,255,0.3)" stroke-width="2"/>
                    
                    <!-- 折线路径 -->
                    <?php
                    $points = [];
                    for ($day = 1; $day <= min($currentDay, 31); $day++):
                        $x = 30 + ($day - 1) * 30;
                        $y = $maxUploads > 0 ? 170 - ($stats['daily_uploads'][$day] / $maxUploads) * 150 : 170;
                        $points[] = "$x,$y";
                    endfor;
                    
                    if (count($points) > 1):
                        $pathData = 'M ' . implode(' L ', $points);
                    ?>
                        <!-- 折线 -->
                        <path d="<?= $pathData ?>" fill="none" stroke="url(#lineGradient)" stroke-width="3" stroke-linejoin="round"/>
                        
                        <!-- 渐变定义 -->
                        <defs>
                            <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#00d4ff;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#00d4ff;stop-opacity:0.3" />
                                <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:0.05" />
                            </linearGradient>
                        </defs>
                        
                        <!-- 面积填充 -->
                        <path d="M <?= implode(' L ', $points) ?> L <?= end($points) ?>,170 L <?= reset($points) ?>,170 Z"
                              fill="url(#areaGradient)" opacity="0.6"/>
                        
                        <!-- 数据点 -->
                        <?php for ($day = 1; $day <= min($currentDay, 31); $day++): ?>
                            <?php
                            $x = 30 + ($day - 1) * 30;
                            $y = $maxUploads > 0 ? 170 - ($stats['daily_uploads'][$day] / $maxUploads) * 150 : 170;
                            $value = $stats['daily_uploads'][$day];
                            ?>
                            <circle cx="<?= $x ?>" cy="<?= $y ?>" r="4" fill="#00d4ff" stroke="white" stroke-width="2">
                                <title>第<?= $day ?>天: <?= $value ?>个文件</title>
                            </circle>
                            
                            <!-- X轴标签 -->
                            <text x="<?= $x ?>" y="185" fill="rgba(255,255,255,0.7)" font-size="10" text-anchor="middle">
                                <?= $day ?>
                            </text>
                        <?php endfor; ?>
                    <?php endif; ?>
                </svg>
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
/* 覆盖admin-stats的网格布局，让每个区块独立占据一行 */
.admin-stats {
    display: block !important;
    margin-bottom: 30px;
}

/* 统计区块样式 */
.stats-section,
.chart-section,
.config-section {
    margin-bottom: 30px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    backdrop-filter: blur(10px);
}

.stats-section h3,
.chart-section h3,
.config-section h3 {
    margin-bottom: 20px;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    gap: 10px;
}

.stats-section h3 i,
.chart-section h3 i,
.config-section h3 i {
    color: rgba(0, 212, 255, 0.8);
}

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
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    touch-action: manipulation;
}

.stat-item:hover, .stat-item:active {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.stat-item:active {
    transform: translateY(0);
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

/* 第三行：每日上传趋势 - 折线图 */
.daily-upload-chart {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px 0;
    overflow-x: auto;
    /* 移动端优化 */
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}

.line-chart-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.line-chart {
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(5px);
    transition: all 0.3s ease;
    /* 移动端优化 */
    -webkit-user-drag: none;
    -khtml-user-drag: none;
    -moz-user-drag: none;
    -o-user-drag: none;
    user-drag: none;
}

.line-chart:hover {
    border-color: rgba(0, 212, 255, 0.3);
    box-shadow: 0 4px 20px rgba(0, 212, 255, 0.2);
}

/* 折线图数据点悬停效果 */
.line-chart circle {
    transition: all 0.3s ease;
    cursor: pointer;
}

.line-chart circle:hover {
    r: 6;
    fill: #8b5cf6;
    stroke-width: 3;
    filter: drop-shadow(0 0 8px rgba(139, 92, 246, 0.6));
}

/* 折线图路径动画 */
.line-chart path[fill="none"] {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
    animation: drawLine 2s ease-in-out forwards;
}

@keyframes drawLine {
    to {
        stroke-dashoffset: 0;
    }
}

/* 面积填充动画 */
.line-chart path[fill^="url"] {
    opacity: 0;
    animation: fadeInArea 1s ease-in-out 0.5s forwards;
}

@keyframes fadeInArea {
    to {
        opacity: 0.6;
    }
}

/* 数据点动画 */
.line-chart circle {
    opacity: 0;
    animation: fadeInPoints 0.5s ease-in-out forwards;
}

@keyframes fadeInPoints {
    to {
        opacity: 1;
    }
}

/* 旧的tooltip样式已移除，新的实现使用SVG原生tooltip */

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
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: text;
    user-select: text;
}

.storage-label {
    font-weight: 500;
    color: rgba(255, 255, 255, 0.7);
    font-size: 16px; /* 防止iOS缩放 */
}

.storage-value {
    font-family: monospace;
    color: white;
    background: rgba(255, 255, 255, 0.1);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 15px;
    word-break: break-all;
    max-width: 60%;
    text-align: right;
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

/* 响应式设计 */
@media (max-width: 1200px) {
    .stats-row {
        gap: 12px;
    }
    
    .file-type-stats {
        gap: 12px;
    }
    
    .stat-item {
        min-width: 180px;
    }
    
    .file-type-item {
        min-width: 160px;
    }
    
    .line-chart {
        min-width: 300px;
    }
}

@media (max-width: 768px) {
    .stats-section,
    .chart-section,
    .config-section {
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .stats-section h3,
    .chart-section h3,
    .config-section h3 {
        font-size: 1rem;
        margin-bottom: 15px;
    }
    
    .stats-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .file-type-stats {
        flex-direction: column;
        gap: 10px;
    }
    
    .stat-item {
        flex-direction: column;
        text-align: center;
        gap: 8px;
        padding: 12px;
    }
    
    .stat-icon {
        font-size: 1.5rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .file-type-item {
        min-width: auto;
        padding: 12px;
    }
    
    .file-type-label {
        font-size: 0.8rem;
    }
    
    .file-type-count {
        font-size: 1rem;
    }
    
    .daily-upload-chart {
        overflow-x: auto;
        justify-content: flex-start;
    }
    
    .storage-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: 15px;
    }
    
    .storage-value {
        align-self: flex-end;
    }
    
    .daily-upload-chart {
        height: 180px;
    }
}

@media (max-width: 480px) {
    .stats-section,
    .chart-section,
    .config-section {
        padding: 12px;
        margin-bottom: 15px;
    }
    
    .stats-row {
        gap: 8px;
    }
    
    .file-type-stats {
        gap: 8px;
    }
    
    .stat-item {
        padding: 10px;
    }
    
    .stat-value {
        font-size: 1.3rem;
    }
    
    .file-type-item {
        padding: 10px;
    }
    
    .line-chart {
        min-width: 250px;
    }
    
    .storage-item {
        padding: 12px;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .storage-value {
        font-size: 0.8rem;
        padding: 4px 8px;
        max-width: 100%;
        text-align: left;
        word-break: break-all;
    }
    
    .daily-upload-chart {
        height: 160px;
    }
}

/* 平板设备适配 */
@media (min-width: 601px) and (max-width: 768px) {
    .stats-row {
        flex-wrap: wrap;
        justify-content: space-between;
    }
    
    .stat-item {
        flex-basis: 48%;
        margin-bottom: 10px;
    }
    
    .file-type-item {
        padding: 15px;
    }
    
    .file-type-label {
        min-width: 100px;
    }
    
    .daily-upload-chart {
        height: 190px;
    }
}

/* 大屏手机适配 */
@media (min-width: 769px) and (max-width: 992px) {
    .stats-row {
        gap: 12px;
    }
    
    .stat-item {
        padding: 14px;
    }
    
    .file-type-item {
        padding: 14px;
    }
    
    .daily-upload-chart {
        height: 195px;
    }
}

/* 横屏模式适配 */
@media (max-width: 768px) and (orientation: landscape) {
    .stats-section,
    .chart-section,
    .config-section {
        padding: 10px;
        margin-bottom: 12px;
    }
    
    .stats-row {
        flex-direction: row;
        gap: 8px;
    }
    
    .stat-item {
        flex-direction: row;
        text-align: left;
        padding: 8px 12px;
    }
    
    .stat-icon {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }
    
    .stat-value {
        font-size: 1.2rem;
    }
    
    .file-type-item {
        flex-direction: row;
        align-items: center;
        padding: 8px 12px;
    }
    
    .file-type-label {
        min-width: 80px;
        font-size: 0.85rem;
    }
    
    .file-type-bar {
        height: 16px;
    }
    
    .daily-upload-chart {
        height: 150px;
        padding: 10px 0;
    }
    
    .storage-item {
        flex-direction: row;
        padding: 8px 12px;
    }
    
    .storage-value {
        max-width: 50%;
        font-size: 0.85rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 新的折线图使用SVG原生tooltip，无需额外的JavaScript交互
    // 保留此脚本以供将来可能的扩展功能
    
    // 为折线图添加加载完成后的动画触发
    const lineChart = document.querySelector('.line-chart');
    if (lineChart) {
        // 触发CSS动画
        lineChart.style.opacity = '1';
    }
    
    // 可以在这里添加其他交互功能，如点击数据点显示详细信息等
    console.log('每日上传趋势折线图已加载完成');
});
</script>