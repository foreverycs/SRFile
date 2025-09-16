<?php
/**
 * 系统性能监控器
 * 提供全面的系统性能监控和统计功能，遵循单一职责原则
 */

require_once 'DatabaseManager.php';
require_once 'CacheManager.php';
require_once 'FileStorageManager.php';

class PerformanceMonitor {
    private static $instance = null;
    private $config = [
        'enabled' => true,
        'log_slow_queries' => true,
        'slow_query_threshold' => 1000, // 毫秒
        'log_memory_usage' => true,
        'memory_threshold' => 64 * 1024 * 1024, // 64MB
        'log_errors' => true,
        'log_access' => true,
        'performance_log_file' => DATA_DIR . 'performance.log',
        'stats_retention_days' => 30,
        'alert_thresholds' => [
            'cpu_usage' => 80,
            'memory_usage' => 80,
            'disk_usage' => 80,
            'response_time' => 2000
        ]
    ];
    
    private $metrics = [
        'start_time' => 0,
        'memory_start' => 0,
        'queries' => [],
        'errors' => [],
        'access_log' => [],
        'performance_data' => []
    ];
    
    private $db;
    private $cache;
    private $storage;
    
    /**
     * 私有构造函数，实现单例模式
     */
    private function __construct($config = []) {
        $this->config = array_merge($this->config, $config);
        $this->initialize();
    }
    
    /**
     * 获取性能监控器实例
     */
    public static function getInstance($config = []) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    /**
     * 初始化监控器
     */
    private function initialize() {
        $this->metrics['start_time'] = microtime(true);
        $this->metrics['memory_start'] = memory_get_usage();
        
        try {
            $this->db = DatabaseManager::getInstance();
            $this->cache = CacheManager::getInstance();
            $this->storage = FileStorageManager::getInstance();
        } catch (Exception $e) {
            // 依赖服务不可用，继续运行但记录错误
            $this->logError('初始化失败', $e->getMessage());
        }
        
        // 注册关闭函数
        register_shutdown_function([$this, 'onShutdown']);
    }
    
    /**
     * 开始性能监控
     */
    public function startMonitoring() {
        if (!$this->config['enabled']) {
            return;
        }
        
        $this->metrics['start_time'] = microtime(true);
        $this->metrics['memory_start'] = memory_get_usage();
        
        // 清空本次监控数据
        $this->metrics['queries'] = [];
        $this->metrics['errors'] = [];
        $this->metrics['performance_data'] = [];
    }
    
    /**
     * 记录查询性能
     */
    public function recordQuery($sql, $executionTime, $params = []) {
        if (!$this->config['enabled']) {
            return;
        }
        
        $queryData = [
            'sql' => $sql,
            'execution_time' => $executionTime,
            'params' => $params,
            'timestamp' => microtime(true),
            'backtrace' => $this->getBacktrace()
        ];
        
        $this->metrics['queries'][] = $queryData;
        
        // 记录慢查询
        if ($this->config['log_slow_queries'] && $executionTime > $this->config['slow_query_threshold']) {
            $this->logSlowQuery($queryData);
        }
    }
    
    /**
     * 记录错误
     */
    public function recordError($message, $context = []) {
        if (!$this->config['enabled']) {
            return;
        }
        
        $errorData = [
            'message' => $message,
            'context' => $context,
            'timestamp' => microtime(true),
            'memory_usage' => memory_get_usage(),
            'backtrace' => $this->getBacktrace()
        ];
        
        $this->metrics['errors'][] = $errorData;
        
        if ($this->config['log_errors']) {
            $this->logError($message, $context);
        }
    }
    
    /**
     * 记录访问日志
     */
    public function recordAccess($action, $details = []) {
        if (!$this->config['enabled'] || !$this->config['log_access']) {
            return;
        }
        
        $accessData = [
            'action' => $action,
            'details' => $details,
            'timestamp' => microtime(true),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? ''
        ];
        
        $this->metrics['access_log'][] = $accessData;
    }
    
    /**
     * 记录性能数据
     */
    public function recordPerformance($key, $value, $metadata = []) {
        if (!$this->config['enabled']) {
            return;
        }
        
        $performanceData = [
            'key' => $key,
            'value' => $value,
            'metadata' => $metadata,
            'timestamp' => microtime(true)
        ];
        
        $this->metrics['performance_data'][] = $performanceData;
    }
    
    /**
     * 获取系统性能指标
     */
    public function getSystemMetrics() {
        $metrics = [
            'timestamp' => time(),
            'uptime' => time() - $this->metrics['start_time'],
            'memory_usage' => memory_get_usage(),
            'memory_peak' => memory_get_peak_usage(),
            'memory_limit' => ini_get('memory_limit'),
            'cpu_usage' => $this->getCpuUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'load_average' => $this->getLoadAverage(),
            'process_count' => $this->getProcessCount(),
            'network_connections' => $this->getNetworkConnections()
        ];
        
        // 计算内存使用百分比
        $memoryLimit = $this->parseMemoryLimit($metrics['memory_limit']);
        if ($memoryLimit > 0) {
            $metrics['memory_usage_percent'] = ($metrics['memory_usage'] / $memoryLimit) * 100;
        }
        
        return $metrics;
    }
    
    /**
     * 获取数据库性能指标
     */
    public function getDatabaseMetrics() {
        if (!$this->db) {
            return null;
        }
        
        $queryStats = $this->db->getQueryStats();
        $cacheStats = $this->db->getCacheStats();
        
        return [
            'total_queries' => $queryStats['total_queries'],
            'slow_queries' => $queryStats['slow_queries'],
            'avg_query_time' => $queryStats['avg_execution_time'],
            'cache_hits' => $cacheStats['cache_hits'],
            'cache_misses' => $cacheStats['cache_misses'],
            'cache_hit_rate' => $cacheStats['hit_rate'],
            'database_size' => $this->db->getDatabaseSize(),
            'connection_status' => 'connected'
        ];
    }
    
    /**
     * 获取缓存性能指标
     */
    public function getCacheMetrics() {
        if (!$this->cache) {
            return null;
        }
        
        $cacheStats = $this->cache->getStats();
        
        return [
            'enabled' => $cacheStats['config']['enabled'],
            'total_items' => $cacheStats['cache_size'],
            'memory_usage' => $cacheStats['memory_usage'],
            'hit_rate' => $cacheStats['hit_rate'],
            'apc_enabled' => $cacheStats['apc_enabled'],
            'redis_enabled' => $cacheStats['redis_enabled']
        ];
    }
    
    /**
     * 获取文件存储性能指标
     */
    public function getStorageMetrics() {
        if (!$this->storage) {
            return null;
        }
        
        $storageStats = $this->storage->getStats();
        
        return [
            'total_files' => $storageStats['total_files'],
            'total_size' => $storageStats['total_size'],
            'directory_count' => count($storageStats['directories']),
            'last_cleanup' => $storageStats['last_cleanup']
        ];
    }
    
    /**
     * 获取完整的性能报告
     */
    public function getPerformanceReport() {
        $report = [
            'timestamp' => time(),
            'system' => $this->getSystemMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'storage' => $this->getStorageMetrics(),
            'current_session' => $this->getCurrentSessionMetrics(),
            'alerts' => $this->checkAlerts()
        ];
        
        return $report;
    }
    
    /**
     * 获取当前会话性能指标
     */
    public function getCurrentSessionMetrics() {
        $currentTime = microtime(true);
        $executionTime = ($currentTime - $this->metrics['start_time']) * 1000;
        $memoryUsage = memory_get_usage() - $this->metrics['memory_start'];
        
        return [
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage,
            'query_count' => count($this->metrics['queries']),
            'error_count' => count($this->metrics['errors']),
            'access_count' => count($this->metrics['access_log']),
            'slow_query_count' => count(array_filter($this->metrics['queries'], function($q) {
                return $q['execution_time'] > $this->config['slow_query_threshold'];
            }))
        ];
    }
    
    /**
     * 检查性能警报
     */
    public function checkAlerts() {
        $alerts = [];
        $metrics = $this->getSystemMetrics();
        
        // 检查CPU使用率
        if (isset($metrics['cpu_usage']) && $metrics['cpu_usage'] > $this->config['alert_thresholds']['cpu_usage']) {
            $alerts[] = [
                'type' => 'cpu_usage',
                'level' => 'warning',
                'message' => "CPU使用率过高: {$metrics['cpu_usage']}%",
                'value' => $metrics['cpu_usage'],
                'threshold' => $this->config['alert_thresholds']['cpu_usage']
            ];
        }
        
        // 检查内存使用率
        if (isset($metrics['memory_usage_percent']) && $metrics['memory_usage_percent'] > $this->config['alert_thresholds']['memory_usage']) {
            $alerts[] = [
                'type' => 'memory_usage',
                'level' => 'warning',
                'message' => "内存使用率过高: {$metrics['memory_usage_percent']}%",
                'value' => $metrics['memory_usage_percent'],
                'threshold' => $this->config['alert_thresholds']['memory_usage']
            ];
        }
        
        // 检查磁盘使用率
        if (isset($metrics['disk_usage']) && $metrics['disk_usage'] > $this->config['alert_thresholds']['disk_usage']) {
            $alerts[] = [
                'type' => 'disk_usage',
                'level' => 'warning',
                'message' => "磁盘使用率过高: {$metrics['disk_usage']}%",
                'value' => $metrics['disk_usage'],
                'threshold' => $this->config['alert_thresholds']['disk_usage']
            ];
        }
        
        // 检查响应时间
        $sessionMetrics = $this->getCurrentSessionMetrics();
        if ($sessionMetrics['execution_time'] > $this->config['alert_thresholds']['response_time']) {
            $alerts[] = [
                'type' => 'response_time',
                'level' => 'warning',
                'message' => "响应时间过长: {$sessionMetrics['execution_time']}ms",
                'value' => $sessionMetrics['execution_time'],
                'threshold' => $this->config['alert_thresholds']['response_time']
            ];
        }
        
        return $alerts;
    }
    
    /**
     * 关闭时处理
     */
    public function onShutdown() {
        if (!$this->config['enabled']) {
            return;
        }
        
        $this->recordPerformance('shutdown', $this->getCurrentSessionMetrics());
        $this->savePerformanceData();
    }
    
    /**
     * 保存性能数据
     */
    private function savePerformanceData() {
        $performanceData = [
            'timestamp' => time(),
            'session_metrics' => $this->getCurrentSessionMetrics(),
            'system_metrics' => $this->getSystemMetrics(),
            'queries' => $this->metrics['queries'],
            'errors' => $this->metrics['errors'],
            'alerts' => $this->checkAlerts()
        ];
        
        // 保存到日志文件
        if ($this->config['performance_log_file']) {
            $this->logPerformance($performanceData);
        }
        
        // 如果数据库可用，保存到数据库
        if ($this->db && $this->db->tableExists('performance_logs')) {
            $this->savePerformanceToDatabase($performanceData);
        }
    }
    
    /**
     * 获取CPU使用率
     */
    private function getCpuUsage() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] * 100; // 转换为百分比
        }
        
        // 备用方法
        if (PHP_OS_FAMILY === 'Linux') {
            $load = @file_get_contents('/proc/loadavg');
            if ($load !== false) {
                $load = explode(' ', $load);
                return floatval($load[0]) * 100;
            }
        }
        
        return 0;
    }
    
    /**
     * 获取磁盘使用率
     */
    private function getDiskUsage() {
        $totalSpace = disk_total_space(__DIR__);
        $freeSpace = disk_free_space(__DIR__);
        
        if ($totalSpace > 0) {
            return round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
        }
        
        return 0;
    }
    
    /**
     * 获取系统负载
     */
    private function getLoadAverage() {
        if (function_exists('sys_getloadavg')) {
            return sys_getloadavg();
        }
        return [0, 0, 0];
    }
    
    /**
     * 获取进程数量
     */
    private function getProcessCount() {
        if (PHP_OS_FAMILY === 'Linux') {
            $count = shell_exec('ps aux | wc -l');
            return intval($count) - 1; // 减去标题行
        }
        return 0;
    }
    
    /**
     * 获取网络连接数
     */
    private function getNetworkConnections() {
        if (PHP_OS_FAMILY === 'Linux') {
            $count = shell_exec('netstat -an | grep ESTABLISHED | wc -l');
            return intval($count);
        }
        return 0;
    }
    
    /**
     * 解析内存限制
     */
    private function parseMemoryLimit($limit) {
        $limit = strtoupper($limit);
        $units = [
            'K' => 1024,
            'M' => 1024 * 1024,
            'G' => 1024 * 1024 * 1024
        ];
        
        if (preg_match('/^(\d+)([KMG]?)$/', $limit, $matches)) {
            $value = intval($matches[1]);
            $unit = $matches[2];
            
            if (isset($units[$unit])) {
                return $value * $units[$unit];
            }
        }
        
        return 0;
    }
    
    /**
     * 获取调用栈
     */
    private function getBacktrace() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        return array_slice($backtrace, 0, 5); // 只返回前5层
    }
    
    /**
     * 记录慢查询
     */
    private function logSlowQuery($queryData) {
        $message = sprintf(
            "慢查询检测: %.2fms - %s",
            $queryData['execution_time'],
            $queryData['sql']
        );
        
        $this->logPerformance([
            'type' => 'slow_query',
            'message' => $message,
            'data' => $queryData
        ]);
    }
    
    /**
     * 记录错误
     */
    private function logError($message, $context = []) {
        $logData = [
            'type' => 'error',
            'message' => $message,
            'context' => $context,
            'timestamp' => time()
        ];
        
        $this->logPerformance($logData);
    }
    
    /**
     * 记录性能数据
     */
    private function logPerformance($data) {
        $logFile = $this->config['performance_log_file'];
        
        if (!$logFile) {
            return;
        }
        
        $logEntry = json_encode($data) . PHP_EOL;
        
        // 确保日志目录存在
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // 写入日志
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // 清理旧日志
        $this->cleanupOldLogs();
    }
    
    /**
     * 保存性能数据到数据库
     */
    private function savePerformanceToDatabase($data) {
        try {
            $this->db->insert('performance_logs', [
                'timestamp' => $data['timestamp'],
                'execution_time' => $data['session_metrics']['execution_time'],
                'memory_usage' => $data['session_metrics']['memory_usage'],
                'query_count' => $data['session_metrics']['query_count'],
                'error_count' => $data['session_metrics']['error_count'],
                'cpu_usage' => $data['system_metrics']['cpu_usage'],
                'memory_usage_percent' => $data['system_metrics']['memory_usage_percent'],
                'disk_usage' => $data['system_metrics']['disk_usage'],
                'raw_data' => json_encode($data)
            ]);
        } catch (Exception $e) {
            // 数据库保存失败，忽略错误
        }
    }
    
    /**
     * 清理旧日志
     */
    private function cleanupOldLogs() {
        $logFile = $this->config['performance_log_file'];
        
        if (!$logFile || !file_exists($logFile)) {
            return;
        }
        
        $cutoffTime = time() - ($this->config['stats_retention_days'] * 24 * 3600);
        
        // 读取日志文件
        $lines = file($logFile);
        $newLines = [];
        
        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data && isset($data['timestamp']) && $data['timestamp'] > $cutoffTime) {
                $newLines[] = $line;
            }
        }
        
        // 写回文件
        file_put_contents($logFile, implode('', $newLines));
    }
    
    /**
     * 获取配置
     */
    public function getConfig($key = null) {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? null;
    }
    
    /**
     * 设置配置
     */
    public function setConfig($key, $value) {
        $this->config[$key] = $value;
    }
    
    /**
     * 获取性能指标
     */
    public function getMetrics() {
        return $this->metrics;
    }
    
    /**
     * 防止克隆
     */
    private function __clone() {}
    
    /**
     * 防止反序列化
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * 性能监控助手类
 * 提供便捷的性能监控操作方法
 */
class PerformanceHelper {
    private $monitor;
    
    public function __construct($config = []) {
        $this->monitor = PerformanceMonitor::getInstance($config);
    }
    
    /**
     * 开始性能监控
     */
    public function start() {
        return $this->monitor->startMonitoring();
    }
    
    /**
     * 记录操作性能
     */
    public function recordOperation($operation, $callback) {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        try {
            $result = $callback();
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage();
            
            $this->monitor->recordPerformance($operation, [
                'execution_time' => ($endTime - $startTime) * 1000,
                'memory_usage' => $endMemory - $startMemory,
                'success' => true
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            $endTime = microtime(true);
            $endMemory = memory_get_usage();
            
            $this->monitor->recordPerformance($operation, [
                'execution_time' => ($endTime - $startTime) * 1000,
                'memory_usage' => $endMemory - $startMemory,
                'success' => false,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 获取性能报告
     */
    public function getReport() {
        return $this->monitor->getPerformanceReport();
    }
    
    /**
     * 检查警报
     */
    public function checkAlerts() {
        return $this->monitor->checkAlerts();
    }
    
    /**
     * 获取监控实例
     */
    public function getMonitor() {
        return $this->monitor;
    }
}

// 全局性能监控助手实例
$performanceHelper = new PerformanceHelper();

?>