<?php
/**
 * 数据库连接和操作类
 * 提供统一的数据库操作接口，遵循单一职责原则
 */

if (!defined('DATABASE_MANAGER_INCLUDED')) {
    define('DATABASE_MANAGER_INCLUDED', true);
    
    require_once 'DatabaseSchema.php';

class DatabaseManager {
    private static $instance = null;
    private $pdo;
    private $cache = [];
    private $cacheEnabled = true;
    private $queryLog = [];
    private $slowQueryThreshold = 1000; // 慢查询阈值（毫秒）
    
    /**
     * 私有构造函数，实现单例模式
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * 获取数据库实例
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 连接数据库
     */
    private function connect() {
        try {
            $this->pdo = new PDO(
                DatabaseConfig::getDSN(), 
                DatabaseConfig::USERNAME, 
                DatabaseConfig::PASSWORD, 
                DatabaseConfig::OPTIONS
            );
            
            // 设置字符集
            $this->pdo->exec("SET NAMES utf8mb4");
            
            // 设置时区
            $this->pdo->exec("SET time_zone = '+00:00'");
            
        } catch (PDOException $e) {
            // 优雅地处理数据库连接失败
            $this->pdo = null;
        }
    }
    
    /**
     * 执行查询
     */
    public function query($sql, $params = []) {
        if ($this->pdo === null) {
            return null;
        }
        
        $startTime = microtime(true);
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $this->logQuery($sql, $params, $executionTime);
            
            // 检查是否为慢查询
            if ($executionTime > $this->slowQueryThreshold) {
                $this->logSlowQuery($sql, $params, $executionTime);
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->logQueryError($sql, $params, $e->getMessage());
            throw new Exception("查询执行失败: " . $e->getMessage());
        }
    }
    
    /**
     * 执行查询并获取所有结果
     */
    public function fetchAll($sql, $params = []) {
        $cacheKey = $this->getCacheKey($sql, $params);
        
        if ($this->cacheEnabled && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $stmt = $this->query($sql, $params);
        if ($stmt === null) {
            return [];
        }
        
        $result = $stmt->fetchAll();
        
        if ($this->cacheEnabled) {
            $this->cache[$cacheKey] = $result;
        }
        
        return $result;
    }
    
    /**
     * 执行查询并获取单行结果
     */
    public function fetchRow($sql, $params = []) {
        $cacheKey = $this->getCacheKey($sql, $params);
        
        if ($this->cacheEnabled && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $stmt = $this->query($sql, $params);
        if ($stmt === null) {
            return null;
        }
        
        $result = $stmt->fetch();
        
        if ($this->cacheEnabled) {
            $this->cache[$cacheKey] = $result;
        }
        
        return $result;
    }
    
    /**
     * 执行查询并获取单个值
     */
    public function fetchOne($sql, $params = []) {
        $cacheKey = $this->getCacheKey($sql, $params);
        
        if ($this->cacheEnabled && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $stmt = $this->query($sql, $params);
        if ($stmt === null) {
            return null;
        }
        
        $result = $stmt->fetchColumn();
        
        if ($this->cacheEnabled) {
            $this->cache[$cacheKey] = $result;
        }
        
        return $result;
    }
    
    /**
     * 执行插入操作
     */
    public function insert($table, $data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->query($sql, array_values($data));
        
        // 清除相关缓存
        $this->clearCache($table);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * 执行更新操作
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            $setParts[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $setParts),
            $where
        );
        
        $params = array_merge($params, $whereParams);
        $stmt = $this->query($sql, $params);
        
        // 清除相关缓存
        $this->clearCache($table);
        
        return $stmt->rowCount();
    }
    
    /**
     * 执行删除操作
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        
        // 清除相关缓存
        $this->clearCache($table);
        
        return $stmt->rowCount();
    }
    
    /**
     * 开始事务
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * 提交事务
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * 回滚事务
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * 执行事务
     */
    public function transaction($callback) {
        try {
            $this->beginTransaction();
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * 获取表结构
     */
    public function getTableSchema($table) {
        $sql = "DESCRIBE {$table}";
        return $this->fetchAll($sql);
    }
    
    /**
     * 检查表是否存在
     */
    public function tableExists($table) {
        if ($this->pdo === null) {
            return false;
        }
        $sql = "SHOW TABLES LIKE ?";
        $result = $this->fetchOne($sql, [$table]);
        return !empty($result);
    }
    
    /**
     * 获取数据库版本
     */
    public function getVersion() {
        return $this->fetchOne("SELECT VERSION()");
    }
    
    /**
     * 获取查询统计信息
     */
    public function getQueryStats() {
        $totalQueries = count($this->queryLog);
        $slowQueries = array_filter($this->queryLog, function($log) {
            return $log['execution_time'] > $this->slowQueryThreshold;
        });
        
        return [
            'total_queries' => $totalQueries,
            'slow_queries' => count($slowQueries),
            'avg_execution_time' => $totalQueries > 0 ? 
                array_sum(array_column($this->queryLog, 'execution_time')) / $totalQueries : 0,
            'cache_hits' => count($this->cache),
            'cache_size' => strlen(serialize($this->cache))
        ];
    }
    
    /**
     * 获取查询日志
     */
    public function getQueryLog() {
        return $this->queryLog;
    }
    
    /**
     * 清除查询日志
     */
    public function clearQueryLog() {
        $this->queryLog = [];
    }
    
    /**
     * 启用/禁用缓存
     */
    public function setCacheEnabled($enabled) {
        $this->cacheEnabled = $enabled;
        if (!$enabled) {
            $this->clearCache();
        }
    }
    
    /**
     * 清除缓存
     */
    public function clearCache($table = null) {
        if ($table) {
            // 清除特定表的缓存
            foreach ($this->cache as $key => $value) {
                if (strpos($key, $table) !== false) {
                    unset($this->cache[$key]);
                }
            }
        } else {
            // 清除所有缓存
            $this->cache = [];
        }
    }
    
    /**
     * 获取缓存统计
     */
    public function getCacheStats() {
        return [
            'enabled' => $this->cacheEnabled,
            'size' => count($this->cache),
            'memory_usage' => strlen(serialize($this->cache))
        ];
    }
    
    /**
     * 优化表
     */
    public function optimizeTable($table) {
        $sql = "OPTIMIZE TABLE {$table}";
        return $this->query($sql);
    }
    
    /**
     * 获取表状态
     */
    public function getTableStatus($table) {
        $sql = "SHOW TABLE STATUS LIKE ?";
        return $this->fetchRow($sql, [$table]);
    }
    
    /**
     * 获取数据库大小
     */
    public function getDatabaseSize() {
        $sql = "SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = ?";
        $size = $this->fetchOne($sql, [DatabaseConfig::DATABASE]);
        return $size ? $size : 0;
    }
    
    /**
     * 记录查询日志
     */
    private function logQuery($sql, $params, $executionTime) {
        $this->queryLog[] = [
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $executionTime,
            'timestamp' => microtime(true)
        ];
        
        // 限制日志大小
        if (count($this->queryLog) > 1000) {
            array_shift($this->queryLog);
        }
    }
    
    /**
     * 记录慢查询
     */
    private function logSlowQuery($sql, $params, $executionTime) {
        $logMessage = sprintf(
            "慢查询检测: %.2fms - %s",
            $executionTime,
            $sql
        );
        
        // 这里可以集成日志系统
        error_log($logMessage);
    }
    
    /**
     * 记录查询错误
     */
    private function logQueryError($sql, $params, $error) {
        $logMessage = sprintf(
            "查询错误: %s - %s",
            $error,
            $sql
        );
        
        // 这里可以集成日志系统
        error_log($logMessage);
    }
    
    /**
     * 生成缓存键
     */
    private function getCacheKey($sql, $params) {
        return md5($sql . serialize($params));
    }
    
    /**
     * 析构函数
     */
    public function __destruct() {
        $this->pdo = null;
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
 * 数据库操作助手类
 * 提供更便捷的数据库操作方法
 */
class DatabaseHelper {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseManager::getInstance();
    }
    
    /**
     * 获取配置值
     */
    public function getConfig($key, $default = null) {
        $sql = "SELECT config_value FROM config WHERE config_key = ?";
        $value = $this->db->fetchOne($sql, [$key]);
        return $value !== false ? $value : $default;
    }
    
    /**
     * 设置配置值
     */
    public function setConfig($key, $value, $description = '') {
        $data = [
            'config_key' => $key,
            'config_value' => $value,
            'description' => $description
        ];
        
        $sql = "INSERT INTO config (config_key, config_value, description) VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE config_value = ?, description = ?";
        
        $params = [$key, $value, $description, $value, $description];
        $this->db->query($sql, $params);
        
        // 清除配置缓存
        $this->db->clearCache('config');
    }
    
    /**
     * 记录管理员操作
     */
    public function logAdminOperation($adminId, $actionType, $actionDetails, $targetId = null) {
        $data = [
            'admin_id' => $adminId,
            'action_type' => $actionType,
            'action_details' => $actionDetails,
            'target_id' => $targetId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        return $this->db->insert('admin_logs', $data);
    }
    
    /**
     * 记录安全日志
     */
    public function logSecurityEvent($logType, $severity, $message, $additionalData = null) {
        $data = [
            'log_type' => $logType,
            'severity' => $severity,
            'message' => $message,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'session_id' => session_id(),
            'additional_data' => $additionalData ? json_encode($additionalData) : null
        ];
        
        return $this->db->insert('security_logs', $data);
    }
}

// 全局数据库助手实例
$dbHelper = new DatabaseHelper();

}
?>