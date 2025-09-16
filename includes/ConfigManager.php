<?php
/**
 * 配置管理器
 * 集中管理所有配置相关操作
 */
class ConfigManager {
    private static $config = null;
    
    /**
     * 获取配置
     */
    public static function getConfig() {
        if (self::$config === null) {
            self::$config = loadConfig();
        }
        return self::$config;
    }
    
    /**
     * 保存配置
     */
    public static function saveConfig($config) {
        file_put_contents(CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT));
        self::$config = $config;
    }
    
    /**
     * 更新配置项
     */
    public static function updateConfig($key, $value) {
        $config = self::getConfig();
        $config[$key] = $value;
        self::saveConfig($config);
    }
    
    /**
     * 获取站点URL
     */
    public static function getSiteUrl() {
        $protocol = isset($_SERVER['HTTPS']) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        return $protocol . "://" . $host . dirname($script);
    }
    
    /**
     * 获取上传限制
     */
    public static function getUploadLimit() {
        $config = self::getConfig();
        $maxSize = $config['max_upload_size'] ?? 100;
        
        // 转换为字节
        return $maxSize * 1024 * 1024;
    }
    
    /**
     * 获取允许的文件类型
     */
    public static function getAllowedTypes() {
        $config = self::getConfig();
        return $config['allowed_types'] ?? [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska',
            'audio/mpeg', 'audio/wav', 'audio/ogg',
            'text/plain',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar',
            // 代码类型
            'text/x-php', 'text/x-c', 'text/x-c++', 'text/x-python', 'text/x-java',
            'text/x-javascript', 'text/x-html', 'text/x-css', 'text/x-sql',
            'text/x-shellscript', 'text/x-perl', 'text/x-ruby', 'text/x-go',
            'text/x-rust', 'text/x-swift', 'text/x-kotlin', 'text/x-typescript',
            'text/x-markdown', 'text/x-yaml', 'text/x-json', 'text/x-xml'
        ];
    }
    
    /**
     * 检查文件类型是否允许
     */
    public static function isAllowedType($mimeType) {
        $allowedTypes = self::getAllowedTypes();
        return in_array($mimeType, $allowedTypes);
    }
    
    /**
     * 获取默认过期天数
     */
    public static function getDefaultExpireDays() {
        $config = self::getConfig();
        return $config['expire_days'] ?? 7;
    }
    
    /**
     * 检查是否启用审核
     */
    public static function isModerationEnabled() {
        $config = self::getConfig();
        return $config['moderation_enabled'] ?? true;
    }
    
    /**
     * 获取管理员配置
     */
    public static function getAdminConfig() {
        $config = self::getConfig();
        return [
            'username' => $config['admin_username'] ?? 'admin',
            'password' => $config['admin_password'] ?? 'admin123'
        ];
    }
    
    /**
     * 验证管理员凭据
     */
    public static function validateAdminCredentials($username, $password) {
        $adminConfig = self::getAdminConfig();
        return $username === $adminConfig['username'] && $password === $adminConfig['password'];
    }
    
    /**
     * 获取站点名称
     */
    public static function getSiteName() {
        $config = self::getConfig();
        return $config['site_name'] ?? '文件快递柜';
    }
    
    /**
     * 获取站点描述
     */
    public static function getSiteDescription() {
        $config = self::getConfig();
        return $config['site_description'] ?? '安全、便捷的文件传输服务';
    }
    
    /**
     * 获取联系方式
     */
    public static function getContactInfo() {
        $config = self::getConfig();
        return [
            'email' => $config['contact_email'] ?? '',
            'phone' => $config['contact_phone'] ?? '',
            'address' => $config['contact_address'] ?? ''
        ];
    }
    
    /**
     * 获取安全设置
     */
    public static function getSecuritySettings() {
        $config = self::getConfig();
        return [
            'enable_csrf' => $config['enable_csrf'] ?? true,
            'enable_rate_limit' => $config['enable_rate_limit'] ?? true,
            'max_upload_size' => $config['max_upload_size'] ?? 100,
            'allowed_ips' => $config['allowed_ips'] ?? [],
            'blocked_ips' => $config['blocked_ips'] ?? []
        ];
    }
    
    /**
     * 获取缓存设置
     */
    public static function getCacheSettings() {
        $config = self::getConfig();
        return [
            'enable_cache' => $config['enable_cache'] ?? true,
            'cache_time' => $config['cache_time'] ?? 3600,
            'cache_path' => $config['cache_path'] ?? DATA_DIR . 'cache/'
        ];
    }
    
    /**
     * 获取邮件设置
     */
    public static function getEmailSettings() {
        $config = self::getConfig();
        return [
            'enable_email' => $config['enable_email'] ?? false,
            'smtp_host' => $config['smtp_host'] ?? '',
            'smtp_port' => $config['smtp_port'] ?? 587,
            'smtp_username' => $config['smtp_username'] ?? '',
            'smtp_password' => $config['smtp_password'] ?? '',
            'from_email' => $config['from_email'] ?? '',
            'from_name' => $config['from_name'] ?? self::getSiteName()
        ];
    }
    
    /**
     * 检查IP是否被允许
     */
    public static function isIpAllowed($ip) {
        $security = self::getSecuritySettings();
        
        // 如果没有设置允许的IP列表，则允许所有IP
        if (empty($security['allowed_ips'])) {
            return true;
        }
        
        return in_array($ip, $security['allowed_ips']);
    }
    
    /**
     * 检查IP是否被阻止
     */
    public static function isIpBlocked($ip) {
        $security = self::getSecuritySettings();
        return in_array($ip, $security['blocked_ips']);
    }
    
    /**
     * 添加阻止的IP
     */
    public static function addBlockedIp($ip) {
        $config = self::getConfig();
        if (!isset($config['blocked_ips'])) {
            $config['blocked_ips'] = [];
        }
        
        if (!in_array($ip, $config['blocked_ips'])) {
            $config['blocked_ips'][] = $ip;
            self::saveConfig($config);
        }
    }
    
    /**
     * 移除阻止的IP
     */
    public static function removeBlockedIp($ip) {
        $config = self::getConfig();
        if (isset($config['blocked_ips'])) {
            $config['blocked_ips'] = array_diff($config['blocked_ips'], [$ip]);
            self::saveConfig($config);
        }
    }
    
    /**
     * 获取系统状态
     */
    public static function getSystemStatus() {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time'),
            'session_status' => session_status(),
            'data_dir_writable' => is_writable(DATA_DIR),
            'upload_dir_writable' => is_writable(UPLOAD_DIR),
            'config_file_exists' => file_exists(CONFIG_FILE),
            'config_file_writable' => is_writable(CONFIG_FILE)
        ];
    }
    
    /**
     * 重置配置为默认值
     */
    public static function resetToDefaults() {
        $defaultConfig = [
            'site_name' => '文件快递柜',
            'site_description' => '安全、便捷的文件传输服务',
            'admin_username' => 'admin',
            'admin_password' => 'admin123',
            'max_upload_size' => 100,
            'expire_days' => 7,
            'moderation_enabled' => true,
            'allowed_types' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska',
                'audio/mpeg', 'audio/wav', 'audio/ogg',
                'text/plain',
                'application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar',
                // 代码类型
                'text/x-php', 'text/x-c', 'text/x-c++', 'text/x-python', 'text/x-java',
                'text/x-javascript', 'text/x-html', 'text/x-css', 'text/x-sql',
                'text/x-shellscript', 'text/x-perl', 'text/x-ruby', 'text/x-go',
                'text/x-rust', 'text/x-swift', 'text/x-kotlin', 'text/x-typescript',
                'text/x-markdown', 'text/x-yaml', 'text/x-json', 'text/x-xml'
            ],
            'enable_csrf' => true,
            'enable_rate_limit' => true,
            'enable_cache' => true,
            'cache_time' => 3600,
            'enable_email' => false,
            'contact_email' => '',
            'contact_phone' => '',
            'contact_address' => '',
            'allowed_ips' => [],
            'blocked_ips' => []
        ];
        
        self::saveConfig($defaultConfig);
    }
    
    /**
     * 导出配置
     */
    public static function exportConfig() {
        $config = self::getConfig();
        return json_encode($config, JSON_PRETTY_PRINT);
    }
    
    /**
     * 导入配置
     */
    public static function importConfig($configJson) {
        $config = json_decode($configJson, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            self::saveConfig($config);
            return true;
        }
        return false;
    }
    
    /**
     * 验证配置
     */
    public static function validateConfig($config) {
        $errors = [];
        
        // 验证必需字段
        $requiredFields = ['site_name', 'admin_username', 'admin_password'];
        foreach ($requiredFields as $field) {
            if (empty($config[$field])) {
                $errors[] = "字段 {$field} 不能为空";
            }
        }
        
        // 验证上传大小
        if (isset($config['max_upload_size'])) {
            if (!is_numeric($config['max_upload_size']) || $config['max_upload_size'] <= 0) {
                $errors[] = "最大上传大小必须是正数";
            }
        }
        
        // 验证过期天数
        if (isset($config['expire_days'])) {
            if (!is_numeric($config['expire_days']) || $config['expire_days'] <= 0) {
                $errors[] = "过期天数必须是正数";
            }
        }
        
        // 验证邮箱
        if (isset($config['contact_email']) && !empty($config['contact_email'])) {
            if (!filter_var($config['contact_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "联系邮箱格式不正确";
            }
        }
        
        return $errors;
    }
}
?>