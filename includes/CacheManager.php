<?php
/**
 * 缓存管理器
 * 提供多层缓存机制，优化频繁访问的数据，遵循单一职责原则
 */

class CacheManager {
    private static $instance = null;
    private $cache = [];
    private $hitCount = 0;
    private $missCount = 0;
    private $config = [
        'enabled' => true,
        'default_ttl' => 3600, // 默认1小时
        'max_size' => 10000,   // 最大缓存条目数
        'cleanup_threshold' => 0.8, // 清理阈值
        'memory_limit' => 64 * 1024 * 1024, // 64MB内存限制
        'use_apc' => false,   // 是否使用APCu
        'use_redis' => false,  // 是否使用Redis
        'redis_config' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => 0
        ]
    ];
    
    private $redis = null;
    private $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
        'evictions' => 0,
        'memory_usage' => 0
    ];
    
    /**
     * 私有构造函数，实现单例模式
     */
    private function __construct($config = []) {
        $this->config = array_merge($this->config, $config);
        $this->initializeCache();
    }
    
    /**
     * 获取缓存实例
     */
    public static function getInstance($config = []) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    /**
     * 初始化缓存
     */
    private function initializeCache() {
        // 检查APCu是否可用
        $this->config['use_apc'] = function_exists('apcu_fetch') && ini_get('apc.enabled');
        
        // 尝试连接Redis
        if ($this->config['use_redis'] && class_exists('Redis')) {
            try {
                $this->redis = new Redis();
                $this->redis->connect(
                    $this->config['redis_config']['host'],
                    $this->config['redis_config']['port']
                );
                
                if ($this->config['redis_config']['password']) {
                    $this->redis->auth($this->config['redis_config']['password']);
                }
                
                if ($this->config['redis_config']['database']) {
                    $this->redis->select($this->config['redis_config']['database']);
                }
                
                $this->config['use_redis'] = true;
            } catch (Exception $e) {
                $this->config['use_redis'] = false;
                $this->redis = null;
            }
        }
    }
    
    /**
     * 获取缓存值
     */
    public function get($key, $default = null) {
        if (!$this->config['enabled']) {
            return $default;
        }
        
        $value = $this->getFromCache($key);
        
        if ($value !== null) {
            $this->stats['hits']++;
            return $value;
        }
        
        $this->stats['misses']++;
        return $default;
    }
    
    /**
     * 设置缓存值
     */
    public function set($key, $value, $ttl = null) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $ttl = $ttl ?? $this->config['default_ttl'];
        
        // 检查缓存大小
        $this->checkCacheSize();
        
        $success = $this->setToCache($key, $value, $ttl);
        
        if ($success) {
            $this->stats['sets']++;
        }
        
        return $success;
    }
    
    /**
     * 删除缓存值
     */
    public function delete($key) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $success = $this->deleteFromCache($key);
        
        if ($success) {
            $this->stats['deletes']++;
        }
        
        return $success;
    }
    
    /**
     * 清空缓存
     */
    public function clear() {
        if (!$this->config['enabled']) {
            return false;
        }
        
        $this->cache = [];
        $this->stats['evictions'] += count($this->cache);
        
        if ($this->config['use_apc']) {
            apcu_clear_cache();
        }
        
        if ($this->config['use_redis'] && $this->redis) {
            $this->redis->flushDB();
        }
        
        return true;
    }
    
    /**
     * 检查缓存是否存在
     */
    public function has($key) {
        if (!$this->config['enabled']) {
            return false;
        }
        
        return $this->getFromCache($key) !== null;
    }
    
    /**
     * 获取多个缓存值
     */
    public function getMultiple($keys, $default = null) {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }
    
    /**
     * 设置多个缓存值
     */
    public function setMultiple($values, $ttl = null) {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * 删除多个缓存值
     */
    public function deleteMultiple($keys) {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * 获取缓存统计信息
     */
    public function getStats() {
        $this->stats['memory_usage'] = strlen(serialize($this->cache));
        $this->stats['cache_size'] = count($this->cache);
        $this->stats['hit_rate'] = $this->stats['hits'] + $this->stats['misses'] > 0 ? 
            ($this->stats['hits'] / ($this->stats['hits'] + $this->stats['misses'])) * 100 : 0;
        
        return array_merge($this->stats, [
            'config' => $this->config,
            'apc_enabled' => $this->config['use_apc'],
            'redis_enabled' => $this->config['use_redis'],
            'memory_usage_mb' => round($this->stats['memory_usage'] / 1024 / 1024, 2)
        ]);
    }
    
    /**
     * 获取缓存键列表
     */
    public function getKeys() {
        return array_keys($this->cache);
    }
    
    /**
     * 清理过期缓存
     */
    public function cleanup() {
        $cleaned = 0;
        $currentTime = time();
        
        foreach ($this->cache as $key => $item) {
            if (isset($item['expire']) && $item['expire'] < $currentTime) {
                unset($this->cache[$key]);
                $cleaned++;
            }
        }
        
        $this->stats['evictions'] += $cleaned;
        return $cleaned;
    }
    
    /**
     * 获取或设置缓存（如果不存在）
     */
    public function remember($key, $callback, $ttl = null) {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * 递增缓存值
     */
    public function increment($key, $value = 1) {
        $current = $this->get($key, 0);
        $current += $value;
        return $this->set($key, $current);
    }
    
    /**
     * 递减缓存值
     */
    public function decrement($key, $value = 1) {
        $current = $this->get($key, 0);
        $current -= $value;
        return $this->set($key, $current);
    }
    
    /**
     * 从缓存获取值
     */
    private function getFromCache($key) {
        // 优先从APCu获取
        if ($this->config['use_apc']) {
            $value = apcu_fetch($key);
            if ($value !== false) {
                return $this->unserializeValue($value);
            }
        }
        
        // 其次从Redis获取
        if ($this->config['use_redis'] && $this->redis) {
            try {
                $value = $this->redis->get($key);
                if ($value !== false) {
                    return $this->unserializeValue($value);
                }
            } catch (Exception $e) {
                // Redis连接失败，降级到内存缓存
            }
        }
        
        // 最后从内存缓存获取
        if (isset($this->cache[$key])) {
            $item = $this->cache[$key];
            
            // 检查是否过期
            if (isset($item['expire']) && $item['expire'] < time()) {
                unset($this->cache[$key]);
                return null;
            }
            
            return $item['value'];
        }
        
        return null;
    }
    
    /**
     * 设置缓存值
     */
    private function setToCache($key, $value, $ttl) {
        $expire = $ttl > 0 ? time() + $ttl : 0;
        $serializedValue = $this->serializeValue($value);
        
        // 设置到APCu
        if ($this->config['use_apc']) {
            apcu_store($key, $serializedValue, $ttl);
        }
        
        // 设置到Redis
        if ($this->config['use_redis'] && $this->redis) {
            try {
                if ($ttl > 0) {
                    $this->redis->setex($key, $ttl, $serializedValue);
                } else {
                    $this->redis->set($key, $serializedValue);
                }
            } catch (Exception $e) {
                // Redis连接失败，降级到内存缓存
            }
        }
        
        // 设置到内存缓存
        $this->cache[$key] = [
            'value' => $value,
            'expire' => $expire,
            'created' => time()
        ];
        
        return true;
    }
    
    /**
     * 从缓存删除值
     */
    private function deleteFromCache($key) {
        // 从APCu删除
        if ($this->config['use_apc']) {
            apcu_delete($key);
        }
        
        // 从Redis删除
        if ($this->config['use_redis'] && $this->redis) {
            try {
                $this->redis->delete($key);
            } catch (Exception $e) {
                // Redis连接失败，忽略错误
            }
        }
        
        // 从内存缓存删除
        unset($this->cache[$key]);
        
        return true;
    }
    
    /**
     * 检查缓存大小
     */
    private function checkCacheSize() {
        // 检查缓存条目数量
        if (count($this->cache) > $this->config['max_size']) {
            $this->evictLRU();
        }
        
        // 检查内存使用
        $memoryUsage = strlen(serialize($this->cache));
        if ($memoryUsage > $this->config['memory_limit']) {
            $this->evictLRU();
        }
        
        // 检查清理阈值
        if (count($this->cache) > $this->config['max_size'] * $this->config['cleanup_threshold']) {
            $this->cleanup();
        }
    }
    
    /**
     * LRU淘汰策略
     */
    private function evictLRU() {
        // 按访问时间排序，删除最旧的25%
        $items = array_map(function($item, $key) {
            return [
                'key' => $key,
                'accessed' => $item['created'] ?? time()
            ];
        }, $this->cache, array_keys($this->cache));
        
        usort($items, function($a, $b) {
            return $a['accessed'] - $b['accessed'];
        });
        
        $evictCount = max(1, count($items) * 0.25);
        
        for ($i = 0; $i < $evictCount; $i++) {
            unset($this->cache[$items[$i]['key']]);
            $this->stats['evictions']++;
        }
    }
    
    /**
     * 序列化值
     */
    private function serializeValue($value) {
        return serialize($value);
    }
    
    /**
     * 反序列化值
     */
    private function unserializeValue($value) {
        return unserialize($value);
    }
    
    /**
     * 启用/禁用缓存
     */
    public function setEnabled($enabled) {
        $this->config['enabled'] = $enabled;
        if (!$enabled) {
            $this->clear();
        }
    }
    
    /**
     * 设置配置
     */
    public function setConfig($key, $value) {
        $this->config[$key] = $value;
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
     * 析构函数
     */
    public function __destruct() {
        if ($this->redis) {
            $this->redis->close();
        }
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
 * 缓存助手类
 * 提供便捷的缓存操作方法
 */
class CacheHelper {
    private $cache;
    
    public function __construct($config = []) {
        $this->cache = CacheManager::getInstance($config);
    }
    
    /**
     * 缓存文件信息
     */
    public function cacheFileInfo($fileId, $fileInfo, $ttl = 3600) {
        $key = "file_info_{$fileId}";
        return $this->cache->set($key, $fileInfo, $ttl);
    }
    
    /**
     * 获取缓存的文件信息
     */
    public function getCachedFileInfo($fileId) {
        $key = "file_info_{$fileId}";
        return $this->cache->get($key);
    }
    
    /**
     * 缓存取件码信息
     */
    public function cachePickupCode($pickupCode, $fileIds, $ttl = 3600) {
        $key = "pickup_code_{$pickupCode}";
        return $this->cache->set($key, $fileIds, $ttl);
    }
    
    /**
     * 获取缓存的取件码信息
     */
    public function getCachedPickupCode($pickupCode) {
        $key = "pickup_code_{$pickupCode}";
        return $this->cache->get($key);
    }
    
    /**
     * 缓存统计信息
     */
    public function cacheStatistics($stats, $ttl = 300) {
        $key = "system_statistics";
        
        // 确保统计数据的所有值都是整数
        $stats = array_map('intval', $stats);
        
        return $this->cache->set($key, $stats, $ttl);
    }
    
    /**
     * 获取缓存的统计信息
     */
    public function getCachedStatistics() {
        $key = "system_statistics";
        $stats = $this->cache->get($key);
        
        // 如果缓存不存在，返回null
        if ($stats === null) {
            return null;
        }
        
        // 确保返回的统计数据所有值都是整数
        return array_map('intval', $stats);
    }
    
    /**
     * 缓存配置信息
     */
    public function cacheConfig($config, $ttl = 1800) {
        $key = "system_config";
        return $this->cache->set($key, $config, $ttl);
    }
    
    /**
     * 获取缓存的配置信息
     */
    public function getCachedConfig() {
        $key = "system_config";
        return $this->cache->get($key);
    }
    
    /**
     * 清除文件相关缓存
     */
    public function clearFileCache($fileId) {
        $keys = ["file_info_{$fileId}"];
        return $this->cache->deleteMultiple($keys);
    }
    
    /**
     * 清除取件码相关缓存
     */
    public function clearPickupCodeCache($pickupCode) {
        $keys = ["pickup_code_{$pickupCode}"];
        return $this->cache->deleteMultiple($keys);
    }
    
    /**
     * 清除系统缓存
     */
    public function clearSystemCache() {
        $keys = ["system_statistics", "system_config"];
        return $this->cache->deleteMultiple($keys);
    }
    
    /**
     * 获取缓存实例
     */
    public function getCache() {
        return $this->cache;
    }
}

// 全局缓存助手实例
$cacheHelper = new CacheHelper();

?>