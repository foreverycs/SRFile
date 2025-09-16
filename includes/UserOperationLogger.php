<?php
/**
 * 用户操作记录管理类
 * 记录管理员对上传记录和下载记录的操作
 */
class UserOperationLogger {
    private static $uploadLogFile = 'data/upload_operations.log';
    private static $downloadLogFile = 'data/download_operations.log';
    
    /**
     * 记录上传记录操作
     */
    public static function logUploadOperation($action, $fileId, $details = [], $adminId = null) {
        $logEntry = [
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
            'admin_id' => $adminId ?? 'admin',
            'action' => $action,
            'file_id' => $fileId,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // 确保日志目录存在
        $logDir = dirname(self::$uploadLogFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        // 写入日志
        $logLine = json_encode($logEntry) . PHP_EOL;
        file_put_contents(self::$uploadLogFile, $logLine, FILE_APPEND);
    }
    
    /**
     * 记录下载记录操作
     */
    public static function logDownloadOperation($action, $downloadId, $details = [], $adminId = null) {
        $logEntry = [
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
            'admin_id' => $adminId ?? 'admin',
            'action' => $action,
            'download_id' => $downloadId,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // 确保日志目录存在
        $logDir = dirname(self::$downloadLogFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        // 写入日志
        $logLine = json_encode($logEntry) . PHP_EOL;
        file_put_contents(self::$downloadLogFile, $logLine, FILE_APPEND);
    }
    
    /**
     * 获取上传记录操作
     */
    public static function getUploadOperations($limit = 100, $offset = 0) {
        $operations = [];
        
        if (!file_exists(self::$uploadLogFile)) {
            return $operations;
        }
        
        $lines = file(self::$uploadLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // 反转数组，最新的在前面
        $lines = array_reverse($lines);
        
        foreach ($lines as $line) {
            $operation = json_decode($line, true);
            if ($operation) {
                $operations[] = $operation;
            }
        }
        
        // 分页
        return array_slice($operations, $offset, $limit);
    }
    
    /**
     * 获取下载记录操作
     */
    public static function getDownloadOperations($limit = 100, $offset = 0) {
        $operations = [];
        
        if (!file_exists(self::$downloadLogFile)) {
            return $operations;
        }
        
        $lines = file(self::$downloadLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // 反转数组，最新的在前面
        $lines = array_reverse($lines);
        
        foreach ($lines as $line) {
            $operation = json_decode($line, true);
            if ($operation) {
                $operations[] = $operation;
            }
        }
        
        // 分页
        return array_slice($operations, $offset, $limit);
    }
    
    /**
     * 获取所有操作记录
     */
    public static function getAllOperations($limit = 100, $offset = 0) {
        $uploadOps = self::getUploadOperations($limit * 2, $offset);
        $downloadOps = self::getDownloadOperations($limit * 2, $offset);
        
        // 合并并按时间排序
        $allOps = array_merge($uploadOps, $downloadOps);
        usort($allOps, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        // 分页
        return array_slice($allOps, $offset, $limit);
    }
    
    /**
     * 获取操作统计
     */
    public static function getStats() {
        $stats = [
            'total_operations' => 0,
            'today_operations' => 0,
            'upload_operations' => 0,
            'download_operations' => 0,
            'actions' => [],
            'last_operation' => null
        ];
        
        // 统计上传记录操作
        if (file_exists(self::$uploadLogFile)) {
            $lines = file(self::$uploadLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $today = date('Y-m-d');
            
            foreach ($lines as $line) {
                $operation = json_decode($line, true);
                if (!$operation) continue;
                
                $stats['total_operations']++;
                $stats['upload_operations']++;
                
                // 统计今日操作
                if (strpos($operation['datetime'], $today) === 0) {
                    $stats['today_operations']++;
                }
                
                // 统计操作类型
                $action = $operation['action'];
                if (!isset($stats['actions'][$action])) {
                    $stats['actions'][$action] = 0;
                }
                $stats['actions'][$action]++;
                
                // 记录最后操作时间
                if (!$stats['last_operation'] || $operation['timestamp'] > $stats['last_operation']['timestamp']) {
                    $stats['last_operation'] = $operation;
                }
            }
        }
        
        // 统计下载记录操作
        if (file_exists(self::$downloadLogFile)) {
            $lines = file(self::$downloadLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $today = date('Y-m-d');
            
            foreach ($lines as $line) {
                $operation = json_decode($line, true);
                if (!$operation) continue;
                
                $stats['total_operations']++;
                $stats['download_operations']++;
                
                // 统计今日操作
                if (strpos($operation['datetime'], $today) === 0) {
                    $stats['today_operations']++;
                }
                
                // 统计操作类型
                $action = $operation['action'];
                if (!isset($stats['actions'][$action])) {
                    $stats['actions'][$action] = 0;
                }
                $stats['actions'][$action]++;
                
                // 记录最后操作时间
                if (!$stats['last_operation'] || $operation['timestamp'] > $stats['last_operation']['timestamp']) {
                    $stats['last_operation'] = $operation;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * 清空操作日志
     */
    public static function clear() {
        if (file_exists(self::$uploadLogFile)) {
            file_put_contents(self::$uploadLogFile, '');
        }
        if (file_exists(self::$downloadLogFile)) {
            file_put_contents(self::$downloadLogFile, '');
        }
    }
    
    /**
     * 获取操作类型的中文名称
     */
    public static function getActionName($action) {
        $actionNames = [
            'approve_upload' => '审核通过上传',
            'reject_upload' => '拒绝上传',
            'delete_upload' => '删除上传记录',
            'view_upload' => '查看上传记录',
            'delete_download' => '删除下载记录',
            'view_download' => '查看下载记录',
            'block_upload' => '封禁上传记录'
        ];
        
        return $actionNames[$action] ?? $action;
    }
    
    /**
     * 获取操作类型名称
     */
    public static function getOperationTypeName($type) {
        $typeNames = [
            'upload' => '上传记录',
            'download' => '下载记录'
        ];
        
        return $typeNames[$type] ?? $type;
    }
}

// 便捷函数
function logUserUploadOperation($action, $fileId, $details = [], $adminId = null) {
    UserOperationLogger::logUploadOperation($action, $fileId, $details, $adminId);
}

function logUserDownloadOperation($action, $downloadId, $details = [], $adminId = null) {
    UserOperationLogger::logDownloadOperation($action, $downloadId, $details, $adminId);
}

function getUserOperations($limit = 100, $offset = 0) {
    return UserOperationLogger::getAllOperations($limit, $offset);
}

function getUserOperationStats() {
    return UserOperationLogger::getStats();
}

function clearUserOperations() {
    return UserOperationLogger::clear();
}

?>