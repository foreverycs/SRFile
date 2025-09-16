<?php
// 管理员操作日志管理类
class AdminOperationLogger {
    private static $logFile = 'data/admin_operations.log';
    
    /**
     * 记录管理员操作
     */
    public static function log($action, $details = [], $adminId = null) {
        $logEntry = [
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
            'admin_id' => $adminId ?? 'admin',
            'action' => $action,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // 确保日志目录存在
        $logDir = dirname(self::$logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        // 写入日志
        $logLine = json_encode($logEntry) . PHP_EOL;
        file_put_contents(self::$logFile, $logLine, FILE_APPEND);
    }
    
    /**
     * 获取管理员操作记录
     */
    public static function getOperations($limit = 100, $offset = 0) {
        $operations = [];
        
        if (!file_exists(self::$logFile)) {
            return $operations;
        }
        
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
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
     * 获取操作统计
     */
    public static function getStats() {
        $stats = [
            'total_operations' => 0,
            'today_operations' => 0,
            'actions' => [],
            'last_operation' => null
        ];
        
        if (!file_exists(self::$logFile)) {
            return $stats;
        }
        
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $today = date('Y-m-d');
        
        foreach ($lines as $line) {
            $operation = json_decode($line, true);
            if (!$operation) continue;
            
            $stats['total_operations']++;
            
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
        
        return $stats;
    }
    
    /**
     * 清空操作日志
     */
    public static function clear() {
        if (file_exists(self::$logFile)) {
            file_put_contents(self::$logFile, '');
        }
    }
    
    /**
     * 获取操作类型的中文名称
     */
    public static function getActionName($action) {
        $actionNames = [
            'login' => '管理员登录',
            'logout' => '管理员退出',
            'approve_file' => '审核通过文件',
            'block_file' => '封禁文件',
            'delete_file' => '删除文件',
            'view_file' => '预览文件',
            'download_file' => '下载文件',
            'config_update' => '更新系统配置',
            'admin_operation' => '管理员操作'
        ];
        
        return $actionNames[$action] ?? $action;
    }
}

// 便捷函数
function logAdminOperation($action, $details = [], $adminId = null) {
    AdminOperationLogger::log($action, $details, $adminId);
}

function getAdminOperations($limit = 100, $offset = 0) {
    return AdminOperationLogger::getOperations($limit, $offset);
}

function getAdminOperationStats() {
    return AdminOperationLogger::getStats();
}
?>