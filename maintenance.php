<?php
/**
 * 系统维护脚本
 * 用于清理过期文件、日志和临时文件
 */

// 禁用执行时间限制
set_time_limit(0);

// 包含必要的文件
require_once 'config.php';
require_once 'includes/ErrorHandler.php';
require_once 'includes/SessionManager.php';
require_once 'includes/FilePreview.php';
require_once 'includes/FileShare.php';

class SystemMaintenance {
    
    /**
     * 执行系统维护
     */
    public function run() {
        echo "=== 文件快递柜系统维护 ===\n\n";
        
        $this->cleanupExpiredFiles();
        $this->cleanupLogs();
        $this->cleanupPreviews();
        $this->cleanupShares();
        $this->optimizeDatabase();
        
        echo "\n维护完成！\n";
    }
    
    /**
     * 清理过期文件
     */
    private function cleanupExpiredFiles() {
        echo "清理过期文件...\n";
        
        $cleanedCount = 0;
        $currentTime = time();
        
        foreach (glob(DATA_DIR . '*.json') as $file) {
            if (basename($file) === 'config.json' || strpos(basename($file), 'pickup_') === 0) {
                continue;
            }
            
            $fileInfo = FileInfo::load(basename($file, '.json'));
            if (!$fileInfo) continue;
            
            // 检查是否过期
            if ($currentTime > $fileInfo->expire_time) {
                // 删除文件
                if (file_exists($fileInfo->path)) {
                    unlink($fileInfo->path);
                }
                
                // 删除文件信息
                unlink($file);
                
                // 删除取件码文件
                $pickupFile = DATA_DIR . 'pickup_' . $fileInfo->pickup_code . '.json';
                if (file_exists($pickupFile)) {
                    unlink($pickupFile);
                }
                
                $cleanedCount++;
                echo "  - 已删除过期文件: {$fileInfo->name}\n";
            }
        }
        
        echo "  清理了 $cleanedCount 个过期文件\n\n";
    }
    
    /**
     * 清理日志文件
     */
    private function cleanupLogs() {
        echo "清理日志文件...\n";
        
        $logFiles = [
            DATA_DIR . 'error.log',
            DATA_DIR . 'security_events.log',
            DATA_DIR . 'security_alerts.log',
            DATA_DIR . 'user_actions.log',
            DATA_DIR . 'downloads.log'
        ];
        
        $maxAge = 30 * 24 * 3600; // 30天
        $currentTime = time();
        
        foreach ($logFiles as $logFile) {
            if (!file_exists($logFile)) continue;
            
            $fileAge = $currentTime - filemtime($logFile);
            
            if ($fileAge > $maxAge) {
                // 清理日志内容
                $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $newLines = [];
                
                foreach ($lines as $line) {
                    $logEntry = json_decode($line, true);
                    if ($logEntry && isset($logEntry['timestamp'])) {
                        $logTime = strtotime($logEntry['timestamp']);
                        if ($logTime > ($currentTime - $maxAge)) {
                            $newLines[] = $line;
                        }
                    }
                }
                
                file_put_contents($logFile, implode("\n", $newLines) . "\n");
                echo "  - 已清理日志文件: " . basename($logFile) . "\n";
            }
        }
        
        echo "  日志清理完成\n\n";
    }
    
    /**
     * 清理预览缓存
     */
    private function cleanupPreviews() {
        echo "清理预览缓存...\n";
        
        FilePreview::cleanupPreviewCache(7);
        
        echo "  预览缓存清理完成\n\n";
    }
    
    /**
     * 清理分享链接
     */
    private function cleanupShares() {
        echo "清理分享链接...\n";
        
        FileShare::cleanupExpiredShares();
        
        echo "  分享链接清理完成\n\n";
    }
    
    /**
     * 优化数据库（JSON文件）
     */
    private function optimizeDatabase() {
        echo "优化数据库...\n";
        
        // 重新格式化所有JSON文件
        foreach (glob(DATA_DIR . '*.json') as $file) {
            if (basename($file) === 'config.json') continue;
            
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
            }
        }
        
        echo "  数据库优化完成\n\n";
    }
    
    /**
     * 生成系统报告
     */
    public function generateReport() {
        echo "=== 系统状态报告 ===\n\n";
        
        // 文件统计
        $fileCount = count(glob(DATA_DIR . '*.json')) - 1; // 减去config.json
        echo "文件数量: $fileCount\n";
        
        // 磁盘使用情况
        $uploadDirSize = $this->getDirectorySize(UPLOAD_DIR);
        $dataDirSize = $this->getDirectorySize(DATA_DIR);
        
        echo "上传目录大小: " . $this->formatBytes($uploadDirSize) . "\n";
        echo "数据目录大小: " . $this->formatBytes($dataDirSize) . "\n";
        
        // 系统状态
        $status = ErrorHandler::getSystemStatus();
        echo "PHP版本: " . $status['php_version'] . "\n";
        echo "服务器软件: " . $status['server_software'] . "\n";
        echo "内存限制: " . $status['memory_limit'] . "\n";
        echo "上传限制: " . $status['upload_max_filesize'] . "\n";
        
        // 目录权限
        echo "数据目录可写: " . ($status['data_dir_writable'] ? '是' : '否') . "\n";
        echo "上传目录可写: " . ($status['upload_dir_writable'] ? '是' : '否') . "\n";
        
        echo "\n报告生成完成！\n";
    }
    
    /**
     * 获取目录大小
     */
    private function getDirectorySize($dir) {
        $size = 0;
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->getDirectorySize($each);
        }
        return $size;
    }
    
    /**
     * 格式化字节大小
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// 命令行参数处理
if (php_sapi_name() === 'cli') {
    $maintenance = new SystemMaintenance();
    
    if ($argc > 1) {
        switch ($argv[1]) {
            case 'cleanup':
                $maintenance->run();
                break;
            case 'report':
                $maintenance->generateReport();
                break;
            case 'help':
                echo "使用方法:\n";
                echo "  php maintenance.php cleanup  - 执行系统清理\n";
                echo "  php maintenance.php report   - 生成系统报告\n";
                echo "  php maintenance.php help     - 显示帮助信息\n";
                break;
            default:
                echo "未知命令: {$argv[1]}\n";
                echo "使用 'php maintenance.php help' 查看帮助\n";
        }
    } else {
        echo "使用 'php maintenance.php help' 查看帮助\n";
    }
} else {
    // Web界面访问
    if (isset($_GET['action']) && $_GET['action'] === 'maintenance') {
        // 需要管理员权限
        if (SessionManager::isAdmin()) {
            $maintenance = new SystemMaintenance();
            
            if (isset($_GET['task'])) {
                switch ($_GET['task']) {
                    case 'cleanup':
                        $maintenance->run();
                        echo '<script>alert("系统维护完成！"); window.location.href = "?action=admin";</script>';
                        break;
                    case 'report':
                        $maintenance->generateReport();
                        break;
                }
            } else {
                echo '<h1>系统维护</h1>';
                echo '<p><a href="?action=maintenance&task=cleanup">执行系统清理</a></p>';
                echo '<p><a href="?action=maintenance&task=report">生成系统报告</a></p>';
                echo '<p><a href="?action=admin">返回管理员面板</a></p>';
            }
        } else {
            echo '需要管理员权限！';
        }
    }
}
?>