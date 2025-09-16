<?php
/**
 * 文件分享类
 * 提供批量下载和分享功能
 */
class FileShare {
    /**
     * 创建分享链接
     */
    public static function createShareLink($fileIds, $expireHours = 24) {
        $shareId = uniqid('share_');
        $expireTime = time() + ($expireHours * 3600);
        
        $shareData = [
            'share_id' => $shareId,
            'file_ids' => $fileIds,
            'created_time' => time(),
            'expire_time' => $expireTime,
            'access_count' => 0,
            'max_access' => 100 // 最大访问次数
        ];
        
        $shareFile = DATA_DIR . 'share_' . $shareId . '.json';
        file_put_contents($shareFile, json_encode($shareData, JSON_PRETTY_PRINT));
        
        return [
            'share_id' => $shareId,
            'share_url' => self::getShareUrl($shareId),
            'expire_time' => $expireTime
        ];
    }
    
    /**
     * 获取分享链接
     */
    public static function getShareUrl($shareId) {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . 
               "://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]?action=share&id=$shareId";
    }
    
    /**
     * 验证分享链接
     */
    public static function validateShareLink($shareId) {
        $shareFile = DATA_DIR . 'share_' . $shareId . '.json';
        
        if (!file_exists($shareFile)) {
            return false;
        }
        
        $shareData = json_decode(file_get_contents($shareFile), true);
        
        if (!$shareData) {
            return false;
        }
        
        // 检查是否过期
        if (time() > $shareData['expire_time']) {
            unlink($shareFile);
            return false;
        }
        
        // 检查访问次数
        if ($shareData['access_count'] >= $shareData['max_access']) {
            return false;
        }
        
        return $shareData;
    }
    
    /**
     * 获取分享文件列表
     */
    public static function getShareFiles($shareId) {
        $shareData = self::validateShareLink($shareId);
        
        if (!$shareData) {
            return null;
        }
        
        $files = [];
        foreach ($shareData['file_ids'] as $fileId) {
            $fileInfo = FileInfo::load($fileId);
            if ($fileInfo && file_exists($fileInfo->path)) {
                $files[] = $fileInfo;
            }
        }
        
        // 更新访问次数
        $shareData['access_count']++;
        $shareFile = DATA_DIR . 'share_' . $shareId . '.json';
        file_put_contents($shareFile, json_encode($shareData, JSON_PRETTY_PRINT));
        
        return $files;
    }
    
    /**
     * 创建批量下载
     */
    public static function createBatchDownload($fileIds) {
        $batchId = uniqid('batch_');
        $batchFile = DATA_DIR . 'batch_' . $batchId . '.json';
        
        $batchData = [
            'batch_id' => $batchId,
            'file_ids' => $fileIds,
            'created_time' => time(),
            'expire_time' => time() + 3600, // 1小时过期
            'download_count' => 0
        ];
        
        file_put_contents($batchFile, json_encode($batchData, JSON_PRETTY_PRINT));
        
        return [
            'batch_id' => $batchId,
            'download_url' => self::getBatchDownloadUrl($batchId)
        ];
    }
    
    /**
     * 获取批量下载链接
     */
    public static function getBatchDownloadUrl($batchId) {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . 
               "://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]?action=batch_download&id=$batchId";
    }
    
    /**
     * 验证批量下载
     */
    public static function validateBatchDownload($batchId) {
        $batchFile = DATA_DIR . 'batch_' . $batchId . '.json';
        
        if (!file_exists($batchFile)) {
            return false;
        }
        
        $batchData = json_decode(file_get_contents($batchFile), true);
        
        if (!$batchData) {
            return false;
        }
        
        // 检查是否过期
        if (time() > $batchData['expire_time']) {
            unlink($batchFile);
            return false;
        }
        
        return $batchData;
    }
    
    /**
     * 创建ZIP压缩包
     */
    public static function createZipArchive($fileIds, $zipName = null) {
        if (!$zipName) {
            $zipName = 'files_' . date('Ymd_His') . '.zip';
        }
        
        $zipPath = UPLOAD_DIR . 'temp/' . $zipName;
        
        // 确保临时目录存在
        if (!file_exists(UPLOAD_DIR . 'temp/')) {
            mkdir(UPLOAD_DIR . 'temp/', 0777, true);
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('无法创建ZIP文件');
        }
        
        foreach ($fileIds as $fileId) {
            $fileInfo = FileInfo::load($fileId);
            
            if ($fileInfo && file_exists($fileInfo->path)) {
                $zip->addFile($fileInfo->path, $fileInfo->name);
            }
        }
        
        $zip->close();
        
        return $zipPath;
    }
    
    /**
     * 生成二维码
     */
    public static function generateQRCode($text, $size = 200) {
        // 简化的二维码生成，实际项目中可以使用专门的二维码库
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($text);
        
        return $qrCodeUrl;
    }
    
    /**
     * 发送分享邮件
     */
    public static function sendShareEmail($email, $shareData, $message = '') {
        // 简化的邮件发送功能，实际项目中可以使用PHPMailer等库
        $subject = '文件分享链接';
        $body = "
        <html>
        <head>
            <title>文件分享链接</title>
        </head>
        <body>
            <h2>文件分享</h2>
            <p>您好！</p>
            <p>有人向您分享了文件，请点击以下链接查看：</p>
            <p><a href='{$shareData['share_url']}'>{$shareData['share_url']}</a></p>
            <p>链接有效期至：" . date('Y-m-d H:i:s', $shareData['expire_time']) . "</p>
            " . ($message ? "<p>留言：" . nl2br(htmlspecialchars($message)) . "</p>" : "") . "
            <p>谢谢！</p>
        </body>
        </html>
        ";
        
        // 这里应该集成邮件发送功能
        // mail($email, $subject, $body, "Content-Type: text/html; charset=UTF-8");
        
        return true;
    }
    
    /**
     * 清理过期的分享和批量下载
     */
    public static function cleanupExpiredShares() {
        $cutoffTime = time();
        
        // 清理分享链接
        $shareFiles = glob(DATA_DIR . 'share_*.json');
        foreach ($shareFiles as $file) {
            $shareData = json_decode(file_get_contents($file), true);
            if ($shareData && $shareData['expire_time'] < $cutoffTime) {
                unlink($file);
            }
        }
        
        // 清理批量下载
        $batchFiles = glob(DATA_DIR . 'batch_*.json');
        foreach ($batchFiles as $file) {
            $batchData = json_decode(file_get_contents($file), true);
            if ($batchData && $batchData['expire_time'] < $cutoffTime) {
                unlink($file);
            }
        }
        
        // 清理临时ZIP文件
        $tempFiles = glob(UPLOAD_DIR . 'temp/*.zip');
        foreach ($tempFiles as $file) {
            if (filemtime($file) < $cutoffTime - 3600) { // 1小时前
                unlink($file);
            }
        }
    }
    
    /**
     * 获取分享统计
     */
    public static function getShareStats() {
        $stats = [
            'total_shares' => 0,
            'active_shares' => 0,
            'expired_shares' => 0,
            'total_downloads' => 0,
            'most_accessed' => []
        ];
        
        $shareFiles = glob(DATA_DIR . 'share_*.json');
        $stats['total_shares'] = count($shareFiles);
        
        $currentTime = time();
        $accessCounts = [];
        
        foreach ($shareFiles as $file) {
            $shareData = json_decode(file_get_contents($file), true);
            if ($shareData) {
                if ($shareData['expire_time'] > $currentTime) {
                    $stats['active_shares']++;
                } else {
                    $stats['expired_shares']++;
                }
                
                $stats['total_downloads'] += $shareData['access_count'];
                
                if ($shareData['access_count'] > 0) {
                    $accessCounts[] = [
                        'share_id' => $shareData['share_id'],
                        'access_count' => $shareData['access_count']
                    ];
                }
            }
        }
        
        // 排序获取访问最多的分享
        usort($accessCounts, function($a, $b) {
            return $b['access_count'] - $a['access_count'];
        });
        
        $stats['most_accessed'] = array_slice($accessCounts, 0, 5);
        
        return $stats;
    }
    
    /**
     * 验证文件访问权限
     */
    public static function validateFileAccess($fileInfo, $shareData = null) {
        // 如果是分享链接，检查文件是否在分享列表中
        if ($shareData) {
            return in_array($fileInfo->id, $shareData['file_ids']);
        }
        
        // 检查文件状态
        if ($fileInfo->status == 2) { // 已封禁
            return false;
        }
        
        // 检查是否过期
        if (time() > $fileInfo->expire_time) {
            return false;
        }
        
        // 检查审核状态
        $config = loadConfig();
        if (isset($config['moderation_enabled']) && $config['moderation_enabled']) {
            if ($fileInfo->status == 0) { // 待审核
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 记录下载日志
     */
    public static function logDownload($fileId, $shareId = null) {
        $logFile = DATA_DIR . 'downloads.log';
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'file_id' => $fileId,
            'share_id' => $shareId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $logMessage = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * 获取下载统计
     */
    public static function getDownloadStats($days = 30) {
        $logFile = DATA_DIR . 'downloads.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $cutoffTime = time() - ($days * 24 * 3600);
        $stats = [
            'total_downloads' => 0,
            'daily_downloads' => [],
            'popular_files' => []
        ];
        
        $fileCounts = [];
        $dailyCounts = [];
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $logEntry = json_decode($line, true);
            if ($logEntry && strtotime($logEntry['timestamp']) > $cutoffTime) {
                $stats['total_downloads']++;
                
                // 按文件统计
                $fileId = $logEntry['file_id'];
                if (!isset($fileCounts[$fileId])) {
                    $fileCounts[$fileId] = 0;
                }
                $fileCounts[$fileId]++;
                
                // 按日期统计
                $date = date('Y-m-d', strtotime($logEntry['timestamp']));
                if (!isset($dailyCounts[$date])) {
                    $dailyCounts[$date] = 0;
                }
                $dailyCounts[$date]++;
            }
        }
        
        $stats['daily_downloads'] = $dailyCounts;
        
        // 获取最受欢迎的文件
        arsort($fileCounts);
        $stats['popular_files'] = array_slice($fileCounts, 0, 10, true);
        
        return $stats;
    }
}
?>