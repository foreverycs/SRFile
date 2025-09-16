<?php
/**
 * 文件存储管理器
 * 优化文件存储结构，按日期分类存储，遵循单一职责原则
 */

class FileStorageManager {
    private static $instance = null;
    private $config = [
        'base_path' => UPLOAD_DIR,
        'date_format' => 'Y/m/d', // 按年/月/日分类
        'max_files_per_dir' => 1000, // 每个目录最大文件数
        'auto_create_dirs' => true,
        'use_hash_naming' => true, // 使用哈希命名避免冲突
        'backup_enabled' => false,
        'backup_path' => null,
        'cleanup_enabled' => true,
        'cleanup_interval' => 86400, // 24小时
        'compression_enabled' => false, // 是否启用压缩
        'compression_level' => 6
    ];
    
    private $stats = [
        'total_files' => 0,
        'total_size' => 0,
        'directories' => [],
        'last_cleanup' => 0
    ];
    
    /**
     * 私有构造函数，实现单例模式
     */
    private function __construct($config = []) {
        $this->config = array_merge($this->config, $config);
        $this->initializeStorage();
    }
    
    /**
     * 获取存储管理器实例
     */
    public static function getInstance($config = []) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    /**
     * 初始化存储
     */
    private function initializeStorage() {
        // 确保基础目录存在
        if (!is_dir($this->config['base_path'])) {
            mkdir($this->config['base_path'], 0755, true);
        }
        
        // 设置备份路径
        if ($this->config['backup_enabled'] && !$this->config['backup_path']) {
            $this->config['backup_path'] = $this->config['base_path'] . '/backup';
        }
        
        // 加载统计信息
        $this->loadStats();
        
        // 检查是否需要清理
        if ($this->config['cleanup_enabled']) {
            $this->checkCleanup();
        }
    }
    
    /**
     * 存储文件
     */
    public function storeFile($sourceFile, $originalName = null, $metadata = []) {
        if (!file_exists($sourceFile)) {
            throw new Exception("源文件不存在: {$sourceFile}");
        }
        
        // 生成存储路径
        $storagePath = $this->generateStoragePath($originalName ?? basename($sourceFile));
        
        // 确保目录存在
        $directory = dirname($storagePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // 复制文件
        if (!copy($sourceFile, $storagePath)) {
            throw new Exception("文件存储失败: {$sourceFile} -> {$storagePath}");
        }
        
        // 如果启用压缩，压缩文件
        if ($this->config['compression_enabled'] && $this->shouldCompress($storagePath)) {
            $storagePath = $this->compressFile($storagePath);
        }
        
        // 保存元数据
        if (!empty($metadata)) {
            $this->saveMetadata($storagePath, $metadata);
        }
        
        // 更新统计信息
        $this->updateStats($storagePath);
        
        return $storagePath;
    }
    
    /**
     * 移动文件
     */
    public function moveFile($sourceFile, $originalName = null, $metadata = []) {
        if (!file_exists($sourceFile)) {
            throw new Exception("源文件不存在: {$sourceFile}");
        }
        
        // 生成存储路径
        $storagePath = $this->generateStoragePath($originalName ?? basename($sourceFile));
        
        // 确保目录存在
        $directory = dirname($storagePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // 移动文件
        if (!rename($sourceFile, $storagePath)) {
            throw new Exception("文件移动失败: {$sourceFile} -> {$storagePath}");
        }
        
        // 如果启用压缩，压缩文件
        if ($this->config['compression_enabled'] && $this->shouldCompress($storagePath)) {
            $storagePath = $this->compressFile($storagePath);
        }
        
        // 保存元数据
        if (!empty($metadata)) {
            $this->saveMetadata($storagePath, $metadata);
        }
        
        // 更新统计信息
        $this->updateStats($storagePath);
        
        return $storagePath;
    }
    
    /**
     * 删除文件
     */
    public function deleteFile($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        // 如果是备份，先删除备份
        if ($this->config['backup_enabled']) {
            $this->deleteBackup($filePath);
        }
        
        // 删除元数据文件
        $metadataFile = $this->getMetadataPath($filePath);
        if (file_exists($metadataFile)) {
            unlink($metadataFile);
        }
        
        // 删除文件
        $success = unlink($filePath);
        
        if ($success) {
            // 更新统计信息
            $this->decrementStats($filePath);
        }
        
        return $success;
    }
    
    /**
     * 获取文件信息
     */
    public function getFileInfo($filePath) {
        if (!file_exists($filePath)) {
            return null;
        }
        
        $info = [
            'path' => $filePath,
            'size' => filesize($filePath),
            'modified' => filemtime($filePath),
            'mime_type' => mime_content_type($filePath),
            'is_compressed' => $this->isCompressed($filePath),
            'metadata' => $this->loadMetadata($filePath)
        ];
        
        return $info;
    }
    
    /**
     * 生成存储路径
     */
    private function generateStoragePath($originalName) {
        // 生成日期路径
        $datePath = date($this->config['date_format']);
        
        // 如果使用哈希命名
        if ($this->config['use_hash_naming']) {
            $hash = md5($originalName . time() . rand(0, 999999));
            $fileName = substr($hash, 0, 8) . '_' . $this->sanitizeFileName($originalName);
        } else {
            $fileName = $this->sanitizeFileName($originalName);
        }
        
        // 构建完整路径
        $fullPath = $this->config['base_path'] . '/' . $datePath . '/' . $fileName;
        
        // 检查文件名冲突
        $counter = 1;
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        
        while (file_exists($fullPath)) {
            if ($extension) {
                $fileName = $baseName . '_' . $counter . '.' . $extension;
            } else {
                $fileName = $baseName . '_' . $counter;
            }
            $fullPath = $this->config['base_path'] . '/' . $datePath . '/' . $fileName;
            $counter++;
        }
        
        return $fullPath;
    }
    
    /**
     * 清理文件名
     */
    private function sanitizeFileName($fileName) {
        // 移除危险字符
        $fileName = preg_replace('/[^\w\s\-\.]/u', '', $fileName);
        $fileName = preg_replace('/[\s]+/', '_', $fileName);
        
        // 限制长度
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        
        if (mb_strlen($baseName) > 100) {
            $baseName = mb_substr($baseName, 0, 100);
        }
        
        if ($extension) {
            return $baseName . '.' . $extension;
        }
        
        return $baseName;
    }
    
    /**
     * 检查是否应该压缩
     */
    private function shouldCompress($filePath) {
        $compressibleTypes = [
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'application/json',
            'application/xml',
            'application/javascript'
        ];
        
        $mimeType = mime_content_type($filePath);
        return in_array($mimeType, $compressibleTypes) && filesize($filePath) > 1024;
    }
    
    /**
     * 压缩文件
     */
    private function compressFile($filePath) {
        $compressedPath = $filePath . '.gz';
        
        if (file_exists($compressedPath)) {
            return $compressedPath;
        }
        
        $source = fopen($filePath, 'rb');
        $dest = gzopen($compressedPath, 'wb' . $this->config['compression_level']);
        
        if (!$source || !$dest) {
            throw new Exception("无法打开文件进行压缩");
        }
        
        while (!feof($source)) {
            gzwrite($dest, fread($source, 4096));
        }
        
        fclose($source);
        gzclose($dest);
        
        // 删除原文件
        unlink($filePath);
        
        return $compressedPath;
    }
    
    /**
     * 解压文件
     */
    public function decompressFile($compressedPath) {
        if (!$this->isCompressed($compressedPath)) {
            return $compressedPath;
        }
        
        $originalPath = substr($compressedPath, 0, -3); // 移除.gz扩展名
        
        $source = gzopen($compressedPath, 'rb');
        $dest = fopen($originalPath, 'wb');
        
        if (!$source || !$dest) {
            throw new Exception("无法打开文件进行解压");
        }
        
        while (!gzeof($source)) {
            fwrite($dest, gzread($source, 4096));
        }
        
        gzclose($source);
        fclose($dest);
        
        return $originalPath;
    }
    
    /**
     * 检查文件是否被压缩
     */
    private function isCompressed($filePath) {
        return substr($filePath, -3) === '.gz';
    }
    
    /**
     * 保存元数据
     */
    private function saveMetadata($filePath, $metadata) {
        $metadataFile = $this->getMetadataPath($filePath);
        $metadataJson = json_encode($metadata, JSON_UNESCAPED_UNICODE);
        
        file_put_contents($metadataFile, $metadataJson);
    }
    
    /**
     * 加载元数据
     */
    private function loadMetadata($filePath) {
        $metadataFile = $this->getMetadataPath($filePath);
        
        if (!file_exists($metadataFile)) {
            return [];
        }
        
        $metadataJson = file_get_contents($metadataFile);
        return json_decode($metadataJson, true);
    }
    
    /**
     * 获取元数据文件路径
     */
    private function getMetadataPath($filePath) {
        return $filePath . '.meta';
    }
    
    /**
     * 更新统计信息
     */
    private function updateStats($filePath) {
        $this->stats['total_files']++;
        $this->stats['total_size'] += filesize($filePath);
        
        $directory = dirname($filePath);
        if (!isset($this->stats['directories'][$directory])) {
            $this->stats['directories'][$directory] = 0;
        }
        $this->stats['directories'][$directory]++;
        
        $this->saveStats();
    }
    
    /**
     * 减少统计信息
     */
    private function decrementStats($filePath) {
        $this->stats['total_files']--;
        $this->stats['total_size'] -= filesize($filePath);
        
        $directory = dirname($filePath);
        if (isset($this->stats['directories'][$directory])) {
            $this->stats['directories'][$directory]--;
            if ($this->stats['directories'][$directory] <= 0) {
                unset($this->stats['directories'][$directory]);
            }
        }
        
        $this->saveStats();
    }
    
    /**
     * 保存统计信息
     */
    private function saveStats() {
        $statsFile = $this->config['base_path'] . '/storage_stats.json';
        file_put_contents($statsFile, json_encode($this->stats, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * 加载统计信息
     */
    private function loadStats() {
        $statsFile = $this->config['base_path'] . '/storage_stats.json';
        
        if (file_exists($statsFile)) {
            $stats = json_decode(file_get_contents($statsFile), true);
            if ($stats) {
                $this->stats = array_merge($this->stats, $stats);
            }
        }
    }
    
    /**
     * 检查是否需要清理
     */
    private function checkCleanup() {
        $currentTime = time();
        
        if ($currentTime - $this->stats['last_cleanup'] > $this->config['cleanup_interval']) {
            $this->cleanup();
        }
    }
    
    /**
     * 清理空目录
     */
    public function cleanup() {
        $cleanedDirs = 0;
        
        foreach ($this->stats['directories'] as $directory => $fileCount) {
            if ($fileCount == 0 && is_dir($directory)) {
                if ($this->removeEmptyDirectory($directory)) {
                    $cleanedDirs++;
                }
            }
        }
        
        $this->stats['last_cleanup'] = time();
        $this->saveStats();
        
        return $cleanedDirs;
    }
    
    /**
     * 递归删除空目录
     */
    private function removeEmptyDirectory($directory) {
        if (!is_dir($directory)) {
            return false;
        }
        
        $items = scandir($directory);
        unset($items[0], $items[1]); // 移除 . 和 ..
        
        if (empty($items)) {
            return rmdir($directory);
        }
        
        foreach ($items as $item) {
            $path = $directory . '/' . $item;
            if (is_dir($path)) {
                if ($this->removeEmptyDirectory($path)) {
                    // 递归删除后再次检查
                    $items = scandir($directory);
                    unset($items[0], $items[1]);
                    
                    if (empty($items)) {
                        return rmdir($directory);
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * 创建备份
     */
    public function createBackup($filePath) {
        if (!$this->config['backup_enabled']) {
            return false;
        }
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        // 确保备份目录存在
        if (!is_dir($this->config['backup_path'])) {
            mkdir($this->config['backup_path'], 0755, true);
        }
        
        // 生成备份路径
        $relativePath = substr($filePath, strlen($this->config['base_path']));
        $backupPath = $this->config['backup_path'] . $relativePath;
        
        // 确保备份目录存在
        $backupDir = dirname($backupPath);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // 复制文件
        return copy($filePath, $backupPath);
    }
    
    /**
     * 删除备份
     */
    private function deleteBackup($filePath) {
        if (!$this->config['backup_enabled']) {
            return false;
        }
        
        $relativePath = substr($filePath, strlen($this->config['base_path']));
        $backupPath = $this->config['backup_path'] . $relativePath;
        
        if (file_exists($backupPath)) {
            unlink($backupPath);
        }
        
        // 删除元数据备份
        $metadataBackup = $backupPath . '.meta';
        if (file_exists($metadataBackup)) {
            unlink($metadataBackup);
        }
        
        return true;
    }
    
    /**
     * 获取存储统计信息
     */
    public function getStats() {
        return $this->stats;
    }
    
    /**
     * 获取目录结构
     */
    public function getDirectoryStructure($path = null) {
        $basePath = $path ?? $this->config['base_path'];
        
        if (!is_dir($basePath)) {
            return [];
        }
        
        $structure = [];
        $items = scandir($basePath);
        unset($items[0], $items[1]); // 移除 . 和 ..
        
        foreach ($items as $item) {
            $itemPath = $basePath . '/' . $item;
            
            if (is_dir($itemPath)) {
                $structure[$item] = [
                    'type' => 'directory',
                    'path' => $itemPath,
                    'size' => $this->getDirectorySize($itemPath),
                    'file_count' => $this->countFiles($itemPath),
                    'children' => $this->getDirectoryStructure($itemPath)
                ];
            } else {
                $structure[$item] = [
                    'type' => 'file',
                    'path' => $itemPath,
                    'size' => filesize($itemPath),
                    'modified' => filemtime($itemPath)
                ];
            }
        }
        
        return $structure;
    }
    
    /**
     * 获取目录大小
     */
    private function getDirectorySize($directory) {
        $totalSize = 0;
        
        foreach (glob($directory . '/*') as $item) {
            if (is_dir($item)) {
                $totalSize += $this->getDirectorySize($item);
            } else {
                $totalSize += filesize($item);
            }
        }
        
        return $totalSize;
    }
    
    /**
     * 统计文件数量
     */
    private function countFiles($directory) {
        $count = 0;
        
        foreach (glob($directory . '/*') as $item) {
            if (is_dir($item)) {
                $count += $this->countFiles($item);
            } else {
                $count++;
            }
        }
        
        return $count;
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
     * 析构函数
     */
    public function __destruct() {
        $this->saveStats();
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
 * 文件存储助手类
 * 提供便捷的文件存储操作方法
 */
class FileStorageHelper {
    private $storage;
    
    public function __construct($config = []) {
        $this->storage = FileStorageManager::getInstance($config);
    }
    
    /**
     * 存储上传的文件
     */
    public function storeUploadedFile($uploadedFile, $metadata = []) {
        return $this->storage->storeFile($uploadedFile['tmp_name'], $uploadedFile['name'], $metadata);
    }
    
    /**
     * 格式化文件大小
     */
    public function formatSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * 获取存储统计信息
     */
    public function getStorageStats() {
        $stats = $this->storage->getStats();
        $stats['total_size_formatted'] = $this->formatSize($stats['total_size']);
        return $stats;
    }
    
    /**
     * 获取存储实例
     */
    public function getStorage() {
        return $this->storage;
    }
}

// 全局文件存储助手实例
$fileStorageHelper = new FileStorageHelper();

?>