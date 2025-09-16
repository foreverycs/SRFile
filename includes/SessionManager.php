<?php
/**
 * 会话管理器
 * 集中管理所有会话相关操作
 */
class SessionManager {
    /**
     * 初始化会话
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // 初始化会话变量
        if (!isset($_SESSION['uploaded_files'])) {
            $_SESSION['uploaded_files'] = [];
        }
        
        if (!isset($_SESSION['downloaded_files'])) {
            $_SESSION['downloaded_files'] = [];
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        if (!isset($_SESSION['user_ip'])) {
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        }
        
        if (!isset($_SESSION['session_start'])) {
            $_SESSION['session_start'] = time();
        }
        
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
        
        // 检查会话固定攻击
        self::checkSessionFixation();
        
        // 检查会话过期
        self::checkSessionExpiration();
    }
    
    /**
     * 检查会话固定攻击
     */
    private static function checkSessionFixation() {
        if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
            self::destroy();
            self::init();
            return;
        }
        
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown')) {
            self::destroy();
            self::init();
            return;
        }
    }
    
    /**
     * 检查会话过期
     */
    private static function checkSessionExpiration() {
        $maxLifetime = ini_get('session.gc_maxlifetime');
        if ($maxLifetime && isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $maxLifetime) {
                self::destroy();
                self::init();
                return;
            }
        }
        
        // 更新最后活动时间
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * 添加上传记录
     */
    public static function addUploadRecord($fileInfo) {
        $_SESSION['uploaded_files'][] = [
            'id' => $fileInfo->id,
            'name' => $fileInfo->name,
            'upload_time' => $fileInfo->upload_time,
            'pickup_code' => $fileInfo->pickup_code,
            'expire_time' => $fileInfo->expire_time,
            'status' => $fileInfo->status
        ];
        
        // 限制记录数量
        if (count($_SESSION['uploaded_files']) > 50) {
            array_shift($_SESSION['uploaded_files']);
        }
        
        // 记录用户行为
        ErrorHandler::logUserAction('file_upload', [
            'file_id' => $fileInfo->id,
            'file_name' => $fileInfo->name,
            'file_size' => $fileInfo->size,
            'pickup_code' => $fileInfo->pickup_code
        ]);
    }
    
    /**
     * 添加下载记录
     */
    public static function addDownloadRecord($fileInfo) {
        $_SESSION['downloaded_files'][] = [
            'id' => $fileInfo->id,
            'name' => $fileInfo->name,
            'download_time' => time(),
            'pickup_code' => $fileInfo->pickup_code
        ];
        
        // 限制记录数量
        if (count($_SESSION['downloaded_files']) > 50) {
            array_shift($_SESSION['downloaded_files']);
        }
        
        // 记录用户行为
        ErrorHandler::logUserAction('file_download', [
            'file_id' => $fileInfo->id,
            'file_name' => $fileInfo->name,
            'file_size' => $fileInfo->size,
            'pickup_code' => $fileInfo->pickup_code
        ]);
    }
    
    /**
     * 获取上传记录
     */
    public static function getUploadRecords($limit = 10) {
        $records = $_SESSION['uploaded_files'] ?? [];
        
        // 按时间倒序排序
        usort($records, function($a, $b) {
            return $b['upload_time'] - $a['upload_time'];
        });
        
        return array_slice($records, 0, $limit);
    }
    
    /**
     * 获取下载记录
     */
    public static function getDownloadRecords($limit = 10) {
        $records = $_SESSION['downloaded_files'] ?? [];
        
        // 按时间倒序排序
        usort($records, function($a, $b) {
            return $b['download_time'] - $a['download_time'];
        });
        
        return array_slice($records, 0, $limit);
    }
    
    /**
     * 获取CSRF令牌
     */
    public static function getCsrfToken() {
        return $_SESSION['csrf_token'] ?? '';
    }
    
    /**
     * 验证CSRF令牌
     */
    public static function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        $result = $token === $_SESSION['csrf_token'];
        
        // 验证后重新生成令牌
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        return $result;
    }
    
    /**
     * 生成CSRF令牌输入字段
     */
    public static function generateCsrfField() {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * 设置闪存消息
     */
    public static function setFlash($key, $message) {
        $_SESSION['flash'][$key] = $message;
    }
    
    /**
     * 获取闪存消息
     */
    public static function getFlash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }
    
    /**
     * 设置临时数据
     */
    public static function setTemp($key, $value, $expire = 3600) {
        $_SESSION['temp'][$key] = [
            'value' => $value,
            'expire' => time() + $expire
        ];
    }
    
    /**
     * 获取临时数据
     */
    public static function getTemp($key) {
        if (isset($_SESSION['temp'][$key])) {
            $data = $_SESSION['temp'][$key];
            
            if ($data['expire'] > time()) {
                return $data['value'];
            } else {
                unset($_SESSION['temp'][$key]);
            }
        }
        return null;
    }
    
    /**
     * 清理过期临时数据
     */
    public static function cleanExpiredTemp() {
        if (isset($_SESSION['temp'])) {
            foreach ($_SESSION['temp'] as $key => $data) {
                if ($data['expire'] <= time()) {
                    unset($_SESSION['temp'][$key]);
                }
            }
        }
    }
    
    /**
     * 设置用户偏好
     */
    public static function setUserPreference($key, $value) {
        $_SESSION['preferences'][$key] = $value;
    }
    
    /**
     * 获取用户偏好
     */
    public static function getUserPreference($key, $default = null) {
        return $_SESSION['preferences'][$key] ?? $default;
    }
    
    /**
     * 获取会话信息
     */
    public static function getSessionInfo() {
        return [
            'session_id' => session_id(),
            'session_start' => $_SESSION['session_start'] ?? time(),
            'last_activity' => $_SESSION['last_activity'] ?? time(),
            'user_ip' => $_SESSION['user_ip'] ?? 'unknown',
            'user_agent' => $_SESSION['user_agent'] ?? 'unknown',
            'upload_count' => count($_SESSION['uploaded_files'] ?? []),
            'download_count' => count($_SESSION['downloaded_files'] ?? [])
        ];
    }
    
    /**
     * 检查是否为管理员
     */
    public static function isAdmin() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    /**
     * 设置管理员登录状态
     */
    public static function setAdminLogin($loggedIn = true) {
        $_SESSION['admin_logged_in'] = $loggedIn;
        
        if ($loggedIn) {
            $_SESSION['admin_login_time'] = time();
            ErrorHandler::logUserAction('admin_login');
        } else {
            ErrorHandler::logUserAction('admin_logout');
        }
    }
    
    /**
     * 获取管理员登录时间
     */
    public static function getAdminLoginTime() {
        return $_SESSION['admin_login_time'] ?? null;
    }
    
    /**
     * 检查频率限制
     */
    public static function checkRateLimit($action, $limit = 10, $timeWindow = 60) {
        $key = 'rate_limit_' . $action;
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        
        $now = time();
        $windowStart = $now - $timeWindow;
        
        // 清理过期的记录
        $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        // 检查是否超过限制
        if (count($_SESSION[$key]) >= $limit) {
            return false;
        }
        
        // 记录当前请求
        $_SESSION[$key][] = $now;
        
        return true;
    }
    
    /**
     * 获取频率限制状态
     */
    public static function getRateLimitStatus($action) {
        $key = 'rate_limit_' . $action;
        
        if (!isset($_SESSION[$key])) {
            return [
                'count' => 0,
                'limit' => 10,
                'reset_time' => time() + 60
            ];
        }
        
        $now = time();
        $windowStart = $now - 60;
        
        // 清理过期的记录
        $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        return [
            'count' => count($_SESSION[$key]),
            'limit' => 10,
            'reset_time' => $windowStart + 60
        ];
    }
    
    /**
     * 销毁会话
     */
    public static function destroy() {
        // 记录会话销毁
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
            ErrorHandler::logUserAction('admin_logout');
        }
        
        session_destroy();
    }
    
    /**
     * 清理会话数据
     */
    public static function cleanup() {
        // 清理过期临时数据
        self::cleanExpiredTemp();
        
        // 限制上传记录数量
        if (isset($_SESSION['uploaded_files']) && count($_SESSION['uploaded_files']) > 50) {
            $_SESSION['uploaded_files'] = array_slice($_SESSION['uploaded_files'], -50);
        }
        
        // 限制下载记录数量
        if (isset($_SESSION['downloaded_files']) && count($_SESSION['downloaded_files']) > 50) {
            $_SESSION['downloaded_files'] = array_slice($_SESSION['downloaded_files'], -50);
        }
    }
}
?>