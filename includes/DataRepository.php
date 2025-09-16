<?php
/**
 * 增强版数据访问层
 * 支持数据库存储和文件存储的双模式，遵循单一职责原则
 */

require_once 'DatabaseManager.php';

class EnhancedDataRepository {
    private $db;
    private $useDatabase = false;
    private $dbHelper;
    
    public function __construct() {
        $this->db = DatabaseManager::getInstance();
        $this->dbHelper = new DatabaseHelper();
        
        // 检查是否使用数据库
        $this->checkDatabaseMode();
    }
    
    /**
     * 检查是否使用数据库模式
     */
    private function checkDatabaseMode() {
        try {
            // 检查数据库表是否存在
            $this->useDatabase = $this->db->tableExists('files');
        } catch (Exception $e) {
            $this->useDatabase = false;
        }
    }
    
    /**
     * 获取所有文件信息
     */
    public function getAllFiles($filter = 'all', $page = 1, $perPage = 20) {
        if ($this->useDatabase) {
            return $this->getAllFilesFromDB($filter, $page, $perPage);
        } else {
            return $this->getAllFilesFromFile($filter, $page, $perPage);
        }
    }
    
    /**
     * 从数据库获取所有文件信息
     */
    private function getAllFilesFromDB($filter = 'all', $page = 1, $perPage = 20) {
        $where = '';
        $params = [];
        
        // 应用过滤器
        if ($filter !== 'all') {
            $statusMap = [
                'pending' => 0,
                'approved' => 1,
                'blocked' => 2
            ];
            
            if (isset($statusMap[$filter])) {
                $where = 'WHERE status = ?';
                $params[] = $statusMap[$filter];
            }
        }
        
        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM files {$where}";
        $totalFiles = $this->db->fetchOne($countSql, $params);
        $totalPages = max(1, ceil($totalFiles / $perPage));
        
        // 获取分页数据
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM files {$where} ORDER BY upload_time DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $files = $this->db->fetchAll($sql, $params);
        
        // 转换为FileInfo对象
        $fileObjects = [];
        foreach ($files as $file) {
            $fileObjects[] = $this->convertToFileInfo($file);
        }
        
        return [
            'files' => $fileObjects,
            'totalFiles' => $totalFiles,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }
    
    /**
     * 从文件系统获取所有文件信息
     */
    private function getAllFilesFromFile($filter = 'all', $page = 1, $perPage = 20) {
        $files = [];
        
        foreach (glob(DATA_DIR . '*.json') as $file) {
            if (basename($file) === 'config.json' || strpos(basename($file), 'pickup_') === 0) {
                continue;
            }
            
            $fileInfo = FileInfo::load(basename($file, '.json'));
            if (!$fileInfo) continue;
            
            // 应用过滤器
            if ($filter === 'pending' && $fileInfo->status != 0) continue;
            if ($filter === 'approved' && $fileInfo->status != 1) continue;
            if ($filter === 'blocked' && $fileInfo->status != 2) continue;
            
            $files[] = $fileInfo;
        }
        
        // 排序：最新的文件在前
        usort($files, function($a, $b) {
            return $b->upload_time - $a->upload_time;
        });
        
        $totalFiles = count($files);
        $totalPages = max(1, ceil($totalFiles / $perPage));
        
        // 分页
        $files = array_slice($files, ($page - 1) * $perPage, $perPage);
        
        return [
            'files' => $files,
            'totalFiles' => $totalFiles,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }
    
    /**
     * 根据取件码获取文件
     */
    public function getFilesByPickupCode($pickupCode) {
        if ($this->useDatabase) {
            return $this->getFilesByPickupCodeFromDB($pickupCode);
        } else {
            return $this->getFilesByPickupCodeFromFile($pickupCode);
        }
    }
    
    /**
     * 从数据库根据取件码获取文件
     */
    private function getFilesByPickupCodeFromDB($pickupCode) {
        $sql = "SELECT * FROM pickup_codes WHERE pickup_code = ? AND expire_time > ? AND is_used = 0";
        $pickupData = $this->db->fetchRow($sql, [$pickupCode, time()]);
        
        if (!$pickupData) {
            return null;
        }
        
        $fileIds = json_decode($pickupData['file_ids'], true);
        if (!$fileIds) {
            return null;
        }
        
        $files = [];
        foreach ($fileIds as $fileId) {
            $fileInfo = $this->getFileInfo($fileId);
            if ($fileInfo) {
                $files[] = $fileInfo;
            }
        }
        
        return $files;
    }
    
    /**
     * 从文件系统根据取件码获取文件
     */
    private function getFilesByPickupCodeFromFile($pickupCode) {
        $pickupFile = DATA_DIR . 'pickup_' . $pickupCode . '.json';
        
        if (!file_exists($pickupFile)) {
            return null;
        }
        
        $pickupData = json_decode(file_get_contents($pickupFile), true);
        
        // 检查提取码是否过期
        if (time() > $pickupData['expire_time']) {
            return null;
        }
        
        $files = [];
        foreach ($pickupData['file_ids'] as $fileId) {
            $fileInfo = FileInfo::load($fileId);
            if ($fileInfo) {
                $files[] = $fileInfo;
            }
        }
        
        return $files;
    }
    
    /**
     * 获取文件信息
     */
    public function getFileInfo($fileId) {
        if ($this->useDatabase) {
            return $this->getFileInfoFromDB($fileId);
        } else {
            return FileInfo::load($fileId);
        }
    }
    
    /**
     * 从数据库获取文件信息
     */
    private function getFileInfoFromDB($fileId) {
        $sql = "SELECT * FROM files WHERE id = ?";
        $fileData = $this->db->fetchRow($sql, [$fileId]);
        
        if (!$fileData) {
            return null;
        }
        
        return $this->convertToFileInfo($fileData);
    }
    
    /**
     * 获取用户的上传记录
     */
    public function getUserUploadHistory($limit = 10) {
        if ($this->useDatabase) {
            return $this->getUserUploadHistoryFromDB($limit);
        } else {
            return $this->getUserUploadHistoryFromFile($limit);
        }
    }
    
    /**
     * 从数据库获取用户上传记录
     */
    private function getUserUploadHistoryFromDB($limit = 10) {
        $sql = "SELECT * FROM files ORDER BY upload_time DESC LIMIT ?";
        $files = $this->db->fetchAll($sql, [$limit]);
        
        $fileObjects = [];
        foreach ($files as $file) {
            $fileObjects[] = $this->convertToFileInfo($file);
        }
        
        return $fileObjects;
    }
    
    /**
     * 从文件系统获取用户上传记录
     */
    private function getUserUploadHistoryFromFile($limit = 10) {
        $files = [];
        
        foreach (glob(DATA_DIR . '*.json') as $file) {
            if (basename($file) === 'config.json' || strpos(basename($file), 'pickup_') === 0) {
                continue;
            }
            
            $fileInfo = FileInfo::load(basename($file, '.json'));
            if (!$fileInfo) continue;
            
            $files[] = $fileInfo;
        }
        
        // 排序：最新的文件在前
        usort($files, function($a, $b) {
            return $b->upload_time - $a->upload_time;
        });
        
        return array_slice($files, 0, $limit);
    }
    
    /**
     * 获取用户的下载记录
     */
    public function getUserDownloadHistory($limit = 10) {
        if ($this->useDatabase) {
            return $this->getUserDownloadHistoryFromDB($limit);
        } else {
            return $this->getUserDownloadHistoryFromFile($limit);
        }
    }
    
    /**
     * 从数据库获取用户下载记录
     */
    private function getUserDownloadHistoryFromDB($limit = 10) {
        $sql = "SELECT d.*, f.name as file_name, f.size as file_size 
                FROM downloads d 
                JOIN files f ON d.file_id = f.id 
                ORDER BY d.download_time DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * 从文件系统获取用户下载记录
     */
    private function getUserDownloadHistoryFromFile($limit = 10) {
        if (!isset($_SESSION['downloaded_files'])) {
            return [];
        }
        
        $downloads = $_SESSION['downloaded_files'];
        usort($downloads, function($a, $b) {
            return $b['download_time'] - $a['download_time'];
        });
        
        return array_slice($downloads, 0, $limit);
    }
    
    /**
     * 记录下载
     */
    public function recordDownload($fileId) {
        if ($this->useDatabase) {
            $this->recordDownloadToDB($fileId);
        } else {
            $this->recordDownloadToFile($fileId);
        }
    }
    
    /**
     * 记录下载到数据库
     */
    private function recordDownloadToDB($fileId) {
        $data = [
            'file_id' => $fileId,
            'download_time' => time(),
            'downloader_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'session_id' => session_id()
        ];
        
        $this->db->insert('downloads', $data);
    }
    
    /**
     * 记录下载到文件
     */
    private function recordDownloadToFile($fileId) {
        if (!isset($_SESSION['downloaded_files'])) {
            $_SESSION['downloaded_files'] = [];
        }
        
        $_SESSION['downloaded_files'][] = [
            'file_id' => $fileId,
            'download_time' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
    }
    
    /**
     * 获取统计信息
     */
    public function getStatistics() {
        if ($this->useDatabase) {
            return $this->getStatisticsFromDB();
        } else {
            return $this->getStatisticsFromFile();
        }
    }
    
    /**
     * 从数据库获取统计信息
     */
    private function getStatisticsFromDB() {
        $today = strtotime('today');
        
        $sql = "SELECT 
                    COUNT(*) as total_files,
                    COALESCE(SUM(size), 0) as total_size,
                    COALESCE(SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END), 0) as approved_files,
                    COALESCE(SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END), 0) as pending_files,
                    COALESCE(SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END), 0) as blocked_files,
                    COALESCE(SUM(CASE WHEN expire_time < ? THEN 1 ELSE 0 END), 0) as expired_files,
                    COALESCE(SUM(CASE WHEN upload_time >= ? THEN 1 ELSE 0 END), 0) as today_uploads
                FROM files";
        
        $stats = $this->db->fetchRow($sql, [time(), $today]);
        
        // 确保所有统计值都是数字
        $stats = array_map('intval', $stats);
        
        // 获取今日下载次数
        $downloadSql = "SELECT COALESCE(COUNT(*), 0) as today_downloads FROM downloads WHERE download_time >= ?";
        $downloadStats = $this->db->fetchRow($downloadSql, [$today]);
        
        $stats['today_downloads'] = intval($downloadStats['today_downloads']);
        
        return $stats;
    }
    
    /**
     * 从文件系统获取统计信息
     */
    private function getStatisticsFromFile() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'approved_files' => 0,
            'pending_files' => 0,
            'blocked_files' => 0,
            'expired_files' => 0,
            'today_uploads' => 0,
            'today_downloads' => 0
        ];
        
        $today = strtotime('today');
        
        // 检查数据目录是否存在
        if (!is_dir(DATA_DIR)) {
            return $stats;
        }
        
        foreach (glob(DATA_DIR . '*.json') as $file) {
            if (basename($file) === 'config.json' || strpos(basename($file), 'pickup_') === 0) {
                continue;
            }
            
            $fileInfo = FileInfo::load(basename($file, '.json'));
            if (!$fileInfo) continue;
            
            $stats['total_files']++;
            $stats['total_size'] += $fileInfo->size;
            
            if ($fileInfo->status == 1) {
                $stats['approved_files']++;
            } elseif ($fileInfo->status == 2) {
                $stats['blocked_files']++;
            } else {
                $stats['pending_files']++;
            }
            
            if (time() > $fileInfo->expire_time) {
                $stats['expired_files']++;
            }
            
            if ($fileInfo->upload_time >= $today) {
                $stats['today_uploads']++;
            }
        }
        
        // 统计今日下载
        if (isset($_SESSION['downloaded_files']) && is_array($_SESSION['downloaded_files'])) {
            foreach ($_SESSION['downloaded_files'] as $download) {
                if (isset($download['download_time']) && $download['download_time'] >= $today) {
                    $stats['today_downloads']++;
                }
            }
        }
        
        // 确保所有统计值都是整数
        $stats = array_map('intval', $stats);
        
        return $stats;
    }
    
    /**
     * 清理过期文件
     */
    public function cleanupExpiredFiles() {
        if ($this->useDatabase) {
            return $this->cleanupExpiredFilesFromDB();
        } else {
            return $this->cleanupExpiredFilesFromFile();
        }
    }
    
    /**
     * 从数据库清理过期文件
     */
    private function cleanupExpiredFilesFromDB() {
        $cleanedCount = 0;
        
        // 获取过期文件
        $sql = "SELECT * FROM files WHERE expire_time < ?";
        $expiredFiles = $this->db->fetchAll($sql, [time()]);
        
        foreach ($expiredFiles as $file) {
            // 删除物理文件
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }
            
            // 删除数据库记录
            $this->db->delete('files', 'id = ?', [$file['id']]);
            $this->db->delete('pickup_codes', 'pickup_code = ?', [$file['pickup_code']]);
            $this->db->delete('downloads', 'file_id = ?', [$file['id']]);
            
            $cleanedCount++;
        }
        
        return $cleanedCount;
    }
    
    /**
     * 从文件系统清理过期文件
     */
    private function cleanupExpiredFilesFromFile() {
        $cleanedCount = 0;
        
        foreach (glob(DATA_DIR . '*.json') as $file) {
            if (basename($file) === 'config.json' || strpos(basename($file), 'pickup_') === 0) {
                continue;
            }
            
            $fileInfo = FileInfo::load(basename($file, '.json'));
            if (!$fileInfo) continue;
            
            if (time() > $fileInfo->expire_time) {
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
            }
        }
        
        return $cleanedCount;
    }
    
    /**
     * 搜索文件
     */
    public function searchFiles($query, $filter = 'all') {
        if ($this->useDatabase) {
            return $this->searchFilesFromDB($query, $filter);
        } else {
            return $this->searchFilesFromFile($query, $filter);
        }
    }
    
    /**
     * 从数据库搜索文件
     */
    private function searchFilesFromDB($query, $filter = 'all') {
        $where = 'WHERE (name LIKE ? OR pickup_code LIKE ?)';
        $params = ["%{$query}%", "%{$query}%"];
        
        // 应用过滤器
        if ($filter !== 'all') {
            $statusMap = [
                'pending' => 0,
                'approved' => 1,
                'blocked' => 2
            ];
            
            if (isset($statusMap[$filter])) {
                $where .= ' AND status = ?';
                $params[] = $statusMap[$filter];
            }
        }
        
        $sql = "SELECT * FROM files {$where} ORDER BY upload_time DESC";
        $files = $this->db->fetchAll($sql, $params);
        
        $fileObjects = [];
        foreach ($files as $file) {
            $fileObjects[] = $this->convertToFileInfo($file);
        }
        
        return $fileObjects;
    }
    
    /**
     * 从文件系统搜索文件
     */
    private function searchFilesFromFile($query, $filter = 'all') {
        $files = [];
        $query = strtolower($query);
        
        foreach (glob(DATA_DIR . '*.json') as $file) {
            if (basename($file) === 'config.json' || strpos(basename($file), 'pickup_') === 0) {
                continue;
            }
            
            $fileInfo = FileInfo::load(basename($file, '.json'));
            if (!$fileInfo) continue;
            
            // 搜索文件名和取件码
            if (strpos(strtolower($fileInfo->name), $query) !== false || 
                strpos($fileInfo->pickup_code, $query) !== false) {
                
                // 应用过滤器
                if ($filter === 'pending' && $fileInfo->status != 0) continue;
                if ($filter === 'approved' && $fileInfo->status != 1) continue;
                if ($filter === 'blocked' && $fileInfo->status != 2) continue;
                
                $files[] = $fileInfo;
            }
        }
        
        // 排序：最新的文件在前
        usort($files, function($a, $b) {
            return $b->upload_time - $a->upload_time;
        });
        
        return $files;
    }
    
    /**
     * 保存文件信息
     */
    public function saveFileInfo($fileInfo) {
        if ($this->useDatabase) {
            return $this->saveFileInfoToDB($fileInfo);
        } else {
            return $fileInfo->save();
        }
    }
    
    /**
     * 保存文件信息到数据库
     */
    private function saveFileInfoToDB($fileInfo) {
        $data = [
            'id' => $fileInfo->id,
            'name' => $fileInfo->name,
            'type' => $fileInfo->type,
            'size' => $fileInfo->size,
            'mime_type' => $this->getMimeType($fileInfo->path),
            'upload_time' => $fileInfo->upload_time,
            'expire_time' => $fileInfo->expire_time,
            'pickup_code' => $fileInfo->pickup_code,
            'status' => $fileInfo->status,
            'file_path' => $fileInfo->path,
            'orientation' => $fileInfo->orientation,
            'is_indexed' => $fileInfo->is_indexed,
            'uploader_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        return $this->db->insert('files', $data);
    }
    
    /**
     * 保存取件码信息
     */
    public function savePickupCode($pickupCode, $fileIds, $expireTime) {
        if ($this->useDatabase) {
            return $this->savePickupCodeToDB($pickupCode, $fileIds, $expireTime);
        } else {
            return $this->savePickupCodeToFile($pickupCode, $fileIds, $expireTime);
        }
    }
    
    /**
     * 保存取件码信息到数据库
     */
    private function savePickupCodeToDB($pickupCode, $fileIds, $expireTime) {
        $data = [
            'pickup_code' => $pickupCode,
            'file_ids' => json_encode($fileIds),
            'expire_time' => $expireTime,
            'is_used' => false
        ];
        
        return $this->db->insert('pickup_codes', $data);
    }
    
    /**
     * 保存取件码信息到文件
     */
    private function savePickupCodeToFile($pickupCode, $fileIds, $expireTime) {
        $pickupData = [
            'file_ids' => $fileIds,
            'expire_time' => $expireTime,
            'is_used' => false
        ];
        
        return file_put_contents(DATA_DIR . 'pickup_' . $pickupCode . '.json', json_encode($pickupData));
    }
    
    /**
     * 更新文件状态
     */
    public function updateFileStatus($fileId, $status) {
        if ($this->useDatabase) {
            return $this->updateFileStatusInDB($fileId, $status);
        } else {
            return $this->updateFileStatusInFile($fileId, $status);
        }
    }
    
    /**
     * 更新文件状态到数据库
     */
    private function updateFileStatusInDB($fileId, $status) {
        return $this->db->update('files', ['status' => $status], 'id = ?', [$fileId]);
    }
    
    /**
     * 更新文件状态到文件
     */
    private function updateFileStatusInFile($fileId, $status) {
        $fileInfo = FileInfo::load($fileId);
        if (!$fileInfo) {
            return false;
        }
        
        $fileInfo->status = $status;
        return $fileInfo->save();
    }
    
    /**
     * 获取数据库状态
     */
    public function getDatabaseStatus() {
        return [
            'enabled' => $this->useDatabase,
            'tables_exist' => $this->useDatabase,
            'query_stats' => $this->useDatabase ? $this->db->getQueryStats() : null,
            'cache_stats' => $this->useDatabase ? $this->db->getCacheStats() : null
        ];
    }
    
    /**
     * 转换数据库记录为FileInfo对象
     */
    private function convertToFileInfo($fileData) {
        if (!class_exists('FileInfo')) {
            require_once CONFIG_FILE;
        }
        
        $fileInfo = new FileInfo(['name' => '', 'type' => '', 'size' => 0, 'tmp_name' => '']);
        
        foreach ($fileData as $key => $value) {
            if (property_exists($fileInfo, $key)) {
                $fileInfo->$key = $value;
            }
        }
        
        // 确保path属性正确设置
        if (!isset($fileInfo->path) && isset($fileData['file_path'])) {
            $fileInfo->path = $fileData['file_path'];
        }
        
        return $fileInfo;
    }
    
    /**
     * 获取MIME类型
     */
    private function getMimeType($filePath) {
        if (file_exists($filePath)) {
            return mime_content_type($filePath);
        }
        return 'application/octet-stream';
    }
}

// 为了向后兼容，保留原有的DataRepository类
class DataRepository extends EnhancedDataRepository {
    // 继承所有功能
}

?>